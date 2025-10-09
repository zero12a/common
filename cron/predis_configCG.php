<?php
//ini_set('default_socket_timeout', 0); //이 옵션 사용시 redis 연결 에러.
set_time_limit(0);

$CFG = require_once(__DIR__ . "/../include/incConfig.php");

//exit;
if(!require_once(__DIR__ . "/../include/incUtil.php"))die("require incUtil fail.");
if(!require_once(__DIR__ . "/../include/incSec.php"))die("require incSec fail.");
if(!require_once(__DIR__ . "/../include/incDB.php"))die("require incDB fail.");

$log = getLoggerStdout(
    array(
    "LIST_NM"=>"log_CG"
    , "PGM_ID"=>"predis_configCG"
    , "REQTOKEN" => $reqToken
    , "RESTOKEN" => uniqid()
    , "LOG_LEVEL" => Monolog\Logger::DEBUG
    )
);


alog("predis_configCG.php__________________________go");

alog("gethostname() =" . gethostname());
alog("SERVER.HOSTNAME =" . $_SERVER["HOSTNAME"]);
alog("SERVER.SCRIPT_NAME =" . $_SERVER["SCRIPT_NAME"]); 

$REQ["HOST_NM"] = gethostname();

//로딩 안해도 됨 기본적으로 infConfig에서 로딩함.
//if(!require_once($CFG_LIBS_PATH_REDIS))die("require redis fail.");

require_once(__DIR__ . "/../../lib/php/vendor/autoload.php");

echo "###########" . $CFG["REDIS_HOST"] . "\n";

//Predis\Autoloader::register();

if($CFG["REDIS_PASSWD"] != ""){
    $pubsubClient = new Predis\Client(
        array(
            'scheme' => 'tcp',
            'host'   => $CFG["REDIS_HOST"],
            'port'   => $CFG["REDIS_PORT"],
            'password'   => $CFG["REDIS_PASSWD"],            
            'timeout' => 0,
            'read_write_timeout' => 0
        )
    );    
}else{
    $pubsubClient = new Predis\Client(
        array(
            'scheme' => 'tcp',
            'host'   => $CFG["REDIS_HOST"],
            'port'   => $CFG["REDIS_PORT"],
            'timeout' => 0,
            'read_write_timeout' => 0
        )
    );    
}


echo "###########" . $CFG["REDIS_PORT"] . "\n";

// Initialize a new pubsub consumer.
$pubsub = $pubsubClient->pubSubLoop();


// Subscribe to your channels
$pubsub->subscribe('config.' . $cfgNm); //cfgNm은 incConfig에서 온다.


// consume messages
// note: this is a blocking call
foreach ($pubsub as $message) {
    switch ($message->kind) {
        case 'subscribe':
            echo "Subscribed to {$message->channel}", PHP_EOL;
            break;
        case 'message':
            if ($message->channel == 'control_channel') {
                if ($message->payload == 'quit_loop') {
                    echo 'Aborting pubsub loop...', PHP_EOL;
                    $pubsub->unsubscribe();
                } else {
                    echo "Received an unrecognized command: {$message->payload}.", PHP_EOL;
                }
            } else {
                echo "Received the following message from {$message->channel}:",
                     PHP_EOL, "  {$message->payload}", PHP_EOL, PHP_EOL;

                $REQ = getConfig($REQ);
                configReload();
            }
            break;
    }
}
$pubsub->unsubscribe();
unset($pubsub);

$pubsubClient->quit();

echo "########### end\n";

$db->close();

if($db)unset($db);

function configReload(){
    global $CFG,$REQ,$_SERVER;
    alog("configReload()...............start");

    $client = new GuzzleHttp\Client();
    $res = $client->request('GET', 'http://localhost/common/include/incConfig.php?reload=YES', [
        'tt' => 'ttt'
    ]);
    alog("res->getStatusCode : " . $res->getStatusCode());
    // "200"
    alog("res->getHeader content-type: " . $res->getHeader('content-type')[0]);
    // 'application/json; charset=utf8'
    alog("res->getBody : " . $res->getBody());

    //처리 결과 DB에 저장
    $REQ["RESULT_MSG"] = $res->getBody();
    if($res->getBody() == "RELOAD_OK"){
        $REQ["RESULT_YN"] = "Y";
    }else{
        $REQ["RESULT_YN"] = "N";
    }

    //공통관련 db연결가져오기
    $db = getDbConn($CFG["CFG_DB"]["OS"]);

    //db에 처리결과 저장하기

    $coltype = "sssss";
    $sql = "insert into CMN_CFG_HISTORY (
            ACT_PGMID,OLD_CFG,NEW_CFG,RESULT_YN,RESULT_MSG
            ,HOST_NM,ADD_DT
        ) values (
            'CONFIG',#{OLD_CFG},#{NEW_CFG},#{RESULT_YN},#{RESULT_MSG}
            ,#{HOST_NM}
            ,date_format(sysdate(),'%Y%m%d%H%i%s')
        )
        ";

    $stmt = makeStmt($db,$sql,$coltype,$REQ);
    if(!$stmt)alog("500/300/SQL makeStmt create fail 실패");
    if(!$stmt->execute())alog("500/100/stmt execute fail 실패" . $db->errno . " -> " . $db->error);

    closeStmt($stmt);
    closeDb($db);
    if($db)unset($db);    
}

function getConfig($REQ){
    global $CFG,$cfgNm;
    alog("getConfig()...............start");

    if($CFG["REDIS_PASSWD"] != ""){
        $redisClient = new Predis\Client(
            array(
                'scheme' => 'tcp',
                'host'   => $CFG["REDIS_HOST"],
                'port'   => $CFG["REDIS_PORT"],
                'password'   => $CFG["REDIS_PASSWD"],
                'timeout' => 0
            )
        );    
    }else{
        $redisClient = new Predis\Client(
            array(
                'scheme' => 'tcp',
                'host'   => $CFG["REDIS_HOST"],
                'port'   => $CFG["REDIS_PORT"],
                'timeout' => 0
            )
        );    
    }
    //$cfgNm = "CONFIG_CG";

    //json
    $jsonStrNew = $redisClient->get($cfgNm);
    $jsonStrOld = $redisClient->get($cfgNm . "." . date("Ymd", time()));

    $jsonStrNew = json_encode(json_decode($jsonStrNew,true),JSON_PRETTY_PRINT);
    $jsonStrOld = json_encode(json_decode($jsonStrOld,true),JSON_PRETTY_PRINT);

    $redisClient->quit();

    $REQ["OLD_CFG"] = aes_encrypt($jsonStrOld,$CFG["CFG_SEC_KEY"]);
    $REQ["NEW_CFG"] = aes_encrypt($jsonStrNew,$CFG["CFG_SEC_KEY"]);
    return $REQ;
}


?>