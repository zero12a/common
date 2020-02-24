<?php
//ini_set('default_socket_timeout', 0); //이 옵션 사용시 redis 연결 에러.
set_time_limit(0);

$CFG = require_once(__DIR__ . "/../include/incConfig.php");

//exit;
if(!require_once(__DIR__ . "/../include/incUtil.php"))die("require incUtil fail.");
if(!require_once(__DIR__ . "/../include/incSec.php"))die("require incSec fail.");
if(!require_once(__DIR__ . "/../include/incDB.php"))die("require incDB fail.");

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


$pubsubClient = new Predis\Client(
    array(
        'scheme' => 'tcp',
        'host'   => $CFG["REDIS_HOST"],
        'port'   => $CFG["REDIS_PORT"],
        'timeout' => 0,
        'read_write_timeout' => 0
    )
);    

echo "###########" . $CFG["REDIS_PORT"] . "\n";

// Initialize a new pubsub consumer.
$pubsub = $pubsubClient->pubSubLoop();


// Subscribe to your channels
$pubsub->subscribe('config.DATASOURCE_CG');


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

                
                dataSourceSaveRedisFromDB();

                datasourceReload();
            }
            break;
    }
}
$pubsub->unsubscribe();
unset($pubsub);

$pubsubClient->quit();

echo "########### end\n";


function datasourceReload(){
    global $CFG,$REQ,$_SERVER;
    alog("configReload()...............start");

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
            'DATASOURCE',#{OLD_CFG},#{NEW_CFG},#{RESULT_YN},#{RESULT_MSG}
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

}

function dataSourceSaveRedisFromDB(){
    global $CFG,$REQ,$cfgNm; //cfgNm은 incConfig.php에서 옴
    alog("configSave()...............start");

    //Get datasource list
    var_dump($CFG["CFG_DB"]["CGCORE"]);
    $db = getDbConn($CFG["CFG_DB"]["CGCORE"]);

    $coltype = "";
    $sql = "
    select 
        SVRSEQ
        , SVRID
        , DBHOST as HOST
        , DBPORT as PORT
        , DBNAME as DBNM
        , DBUSRID as ID
        , DBUSRPW as PW
    from CG_SVR where USEYN='Y'";
    
    $stmt = makeStmt($db,$sql,$coltype,$REQ);
    if(!$stmt)JsonMsg("500","300","SQL makeStmt 생성 실패 했습니다.");
    $svrArray = getStmtArray($stmt);
    $stmt->close();
    $db->close();
    if($db)unset($db);

    /*
    아래 구조로 변경하기
    "CFG_DB": {
        "CGCORE": {
            "HOST": "172.17.0.1",
            "PORT": "",
            "DBNM": "",
            "ID": "",
            "PW": ""
        },
        "CGPJT1": {
            "HOST": "172.17.0.1",
            "PORT": "",
            "DBNM": "",
            "ID": "",
            "PW": ""
        }
    }
    */
    $rtnArr = array();
    for($t=0;$t<sizeof($svrArray);$t++){
        $rtnArr[$svrArray[$t]["SVRID"]] = $svrArray[$t];
    }
    $newDataSourceJson = json_encode($rtnArr);


    //Save to redis
    //$cfgNm = "CONFIG_CG";
    $redisClient = new Predis\Client(
        array(
            'scheme' => 'tcp',
            'host'   => $CFG["REDIS_HOST"],
            'port'   => $CFG["REDIS_PORT"],
            'timeout' => 0
        )
    );   

    $oldConfigJson = $redisClient->get($cfgNm);
    $REQ["OLD_CFG"] = aes_encrypt($oldConfigJson,$CFG["CFG_SEC_KEY"]);

    $oldConfigArray = json_decode($oldConfigJson,true);
    echo "\nView old json..........\n". json_encode($oldConfigArray,JSON_PRETTY_PRINT);

    $oldDataSourceArray = $oldConfigArray["CFG_DB"];
    $oldDataSourceJson = json_encode($oldDataSourceArray);

    if($oldDataSourceJson != $newDataSourceJson){
        $newConfigArray = $oldConfigArray;
        $newConfigArray["CFG_DB"] = json_decode($newDataSourceJson,true);
        $newConfigJson = json_encode($newConfigArray);
        echo "\nSave new json..........\n". json_encode($newConfigArray,JSON_PRETTY_PRINT);
        $redisClient->set($cfgNm,$newConfigJson);
        $REQ["NEW_CFG"] = aes_encrypt($newConfigJson,$CFG["CFG_SEC_KEY"]);
    }else{
        $REQ["NEW_CFG"] = aes_encrypt("",$CFG["CFG_SEC_KEY"]);
    }
    $redisClient->quit();

    //처음 로딩시 로컬캐시에 보관
    //echo "CONFIG=========================================\n" . $jsonStr . "\n=========================================\n";
    echo $cfgNm . " redis datasource 반영완료 ";
}


?>