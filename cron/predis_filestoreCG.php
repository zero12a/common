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
    , "PGM_ID"=>"predis_filestoreCG"
    , "REQTOKEN" => $reqToken
    , "RESTOKEN" => uniqid()
    , "LOG_LEVEL" => Monolog\Logger::DEBUG
    )
);



alog("predis_datasourceCG.php__________________________go");

alog("gethostname() =" . gethostname());
alog("SERVER.HOSTNAME =" . $_SERVER["HOSTNAME"]); //동작 잘 안함.
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
$pubsub->subscribe('config.FILESTORE_CG');


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

                
                fileStoreSaveRedisFromDB();

                fileStoreReload();
            }
            break;
    }
}
$pubsub->unsubscribe();
unset($pubsub);

$pubsubClient->quit();

echo "########### end\n";


function fileStoreReload(){
    global $CFG,$REQ,$_SERVER;
    alog("fileStoreReload()...............start");

    $client = new GuzzleHttp\Client();
    $res = $client->request('GET', 'http://localhost/common/include/incConfig.php?reload=YES', [
        'postparam1' => 'YES'
    ]);
    alog("res->getStatusCode :" . $res->getStatusCode());
    // "200"
    alog("res->content-type :" .  $res->getHeader('content-type')[0]);
    // 'application/json; charset=utf8'
    alog("res->getBody :" .  $res->getBody());

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
            'FILESTORE',#{OLD_CFG},#{NEW_CFG},#{RESULT_YN},#{RESULT_MSG}
            ,#{HOST_NM}
            ,date_format(sysdate(),'%Y%m%d%H%i%s')
        )
        ";
    $stmt = makeStmt($db,$sql,$coltype,$REQ);
    if(!$stmt)alog("500/300/SQL makeStmt create fail 실패");
    if(!$stmt->execute())alog("500/100/stmt execute fail 실패" . $db->errno . " -> " . $db->error);

    $stmt->close();
    $db->close();
    if($db)unset($db);


    alog("fileStoreReload()...............end");
}

function fileStoreSaveRedisFromDB(){
    global $CFG,$REQ,$cfgNm; //cfgNm은 incConfig.php에서 옴
    alog("fileStoreSaveRedisFromDB()...............start");

    //Get datasource list
    var_dump($CFG["CFG_DB"]["CGCORE"]);
    $db = getDbConn($CFG["CFG_DB"]["CGCORE"]);

    $coltype = "";
    $sql = "
    select 
        FILESTORESEQ
        , STOREID
        , STORETYPE
        , UPLOADDIR
        , READURL
        , CREKEY
        , CRESECRET
        , REGION
        , BUCKET
        , ACL
    from CG_FILESTORE
    where DELYN = 'N' and USEYN = 'Y' ";
    
    $stmt = makeStmt($db,$sql,$coltype,$REQ);
    if(!$stmt)JsonMsg("500","300","SQL makeStmt 생성 실패 했습니다.");
    $filestoreArray = getStmtArray($stmt);
    closeStmt($stmt);
    closeDb($db);
    if($db)unset($db);

    /*
    아래 구조로 변경하기

CFG_FILESTORE : {
    "LOCAL_1" : {
        "TYPE" : "LOCAL"
        ,"UPLOAD_DIR" : "/data/www/up/"
        ,"READ_URL" : "/up/"
    }
    ,"S3_1" : {
        "TYPE" : "S3"
        ,"CRE_KEY" : "..."
        ,"CRE_SECRET" : "..."
        ,"REGION" : "ap-northeast-2"
        ,"BUCKET" : "code-gen-mdm"
        ,"ACL" : 
    }
}

    */
    $rtnArr = array();
    for($t=0;$t<sizeof($filestoreArray);$t++){
        $rtnArr[$filestoreArray[$t]["STOREID"]] = $filestoreArray[$t];
    }
    $newFileStoreJson = json_encode($rtnArr);

    //echo "newFileStoreJson = " . json_encode($rtnArr,JSON_PRETTY_PRINT);
    //Save to redis
    //$cfgNm = "CONFIG_CG";
    if($CFG["REDIS_PASSWD"] != ""){
        $redisClient = new Predis\Client(
            array(
                'scheme' => 'tcp',
                'host'   => $CFG["REDIS_HOST"],
                'port'   => $CFG["REDIS_PORT"],
                'password' => $CFG["REDIS_PASSWD"],
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

    $oldConfigJson = $redisClient->get($cfgNm);

    $oldConfigJson = json_encode(json_decode($oldConfigJson,true),JSON_PRETTY_PRINT); //예쁘게 보이기

    $REQ["OLD_CFG"] = aes_encrypt($oldConfigJson,$CFG["CFG_SEC_KEY"]);

    $oldConfigArray = json_decode($oldConfigJson,true);
    alog("View old json..........\n". json_encode($oldConfigArray,JSON_PRETTY_PRINT));

    $oldFileStoreArray = $oldConfigArray["CFG_FILESTORE"];
    $oldFileStoreJson = json_encode($oldFileStoreArray);

    if($oldFileStoreJson != $newFileStoreJson){
        $newConfigArray = $oldConfigArray;
        $newConfigArray["CFG_FILESTORE"] = json_decode($newFileStoreJson,true);
        $newConfigJson = json_encode($newConfigArray);
        //echo json_encode($newConfigArray,JSON_PRETTY_PRINT);
        alog("Save new json..........\n". json_encode($newConfigArray,JSON_PRETTY_PRINT));
        $redisClient->set($cfgNm,$newConfigJson);

        $newConfigJson = json_encode(json_decode($newConfigJson,true),JSON_PRETTY_PRINT); //예쁘게 보이기
        $REQ["NEW_CFG"] = aes_encrypt($newConfigJson,$CFG["CFG_SEC_KEY"]);
    }else{
        $REQ["NEW_CFG"] = aes_encrypt("",$CFG["CFG_SEC_KEY"]);
    }
    $redisClient->quit();

    alog("fileStoreSaveRedisFromDB()...............end");
}


?>