<?php
header("Content-Type: text/html; charset=UTF-8");

//redis에 모두 넣기
$CFG = require_once("./include/incConfig.php");
require_once $CFG["CFG_LIBS_VENDOR"];
require_once "./include/incUtil.php";
require_once "./include/incRequest.php";

//var_dump($CFG);
if(trim($CFG["REDIS_HOST"]) == "")die("Config에 REDIS_HOST 값이 없습니다.");

//echo "aaa";
if($CFG["REDIS_PASSWD"] != ""){
    $redisClient = new Predis\Client(
        array(
            'scheme' => 'tcp',
            'host'   => $CFG["REDIS_HOST"],
            'port'   => $CFG["REDIS_PORT"],
            'password' => $CFG["REDIS_PASSWD"],
            'timeout' => 1
        )
    );    
}else{
    $redisClient = new Predis\Client(
        array(
            'scheme' => 'tcp',
            'host'   => $CFG["REDIS_HOST"],
            'port'   => $CFG["REDIS_PORT"],
            'timeout' => 1
        )
    );    
}


//외부 입력값 받아오기
$PUBSUB = reqGetString("PUBSUB",30);
$MSG = reqGetString("MSG",30);

if($PUBSUB =="")JsonMsg("500","100","input PUBSUB param.");
if($MSG =="")JsonMsg("500","100","input MSG param.");

//가입 채널에 변경 통보하기
$redisClient->publish($PUBSUB,$MSG);

$redisClient->quit();
JsonMsg("200","200","Send pubsub msg");
?>