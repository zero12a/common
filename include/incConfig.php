<?php
//redis에 모두 넣기
if(!require_once("/data/www/lib/php/predis/autoload.php"))die("require predis load fail.");

//레디스 config
$miniConfig = array(
	"REDIS_HOST" => "172.17.0.1"
	,"REDIS_PORT" => 1234
);

//세션 사용
$redisClient = new Predis\Client(
	array(
		'scheme' => 'tcp',
		'host'   => $miniConfig["REDIS_HOST"],
		'port'   => $miniConfig["REDIS_PORT"],
		'timeout' => 1
	));
$jsonString = $redisClient->get("CONFIG_CG");
$redisClient->quit();

$rtnArray = null;
if($jsonString == null || $jsonString == "" || !is_array(json_decode($jsonString,true)) ){
	$rtnArray = $miniConfig;
}else{
	$rtnArray = array_merge(json_decode($jsonString,true),$miniConfig);
}

return $rtnArray;
?>