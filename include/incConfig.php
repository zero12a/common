<?php
//redis에 모두 넣기
if(!require_once("/data/www/lib/php/vendor/autoload.php"))die("require default vendor load fail.");

//리던 캑체
$rtnArray = null;
$cfgNm = $_SERVER["REDIS_CONFIG_ID"];
//echo 111;

//로컬 캐쉬에 config 가져왔는지 검사합니다.
$localCache = trim(apcu_fetch($cfgNm));

if($localCache == "" || $_GET["reload"] == "YES"){

	//레디스 config
	$miniConfig = array(
		"REDIS_HOST" =>  $_SERVER["REDIS_HOST"]
		,"REDIS_PORT" =>  $_SERVER["REDIS_PORT"]
		,"REDIS_PASSWD" =>  $_SERVER["REDIS_PASSWD"]
	);

	//세션 사용
	if($miniConfig["REDIS_PASSWD"] != ""){
		$redisClient = new Predis\Client(
			array(
				'scheme' => 'tcp',
				'host'   => $miniConfig["REDIS_HOST"],
				'port'   => $miniConfig["REDIS_PORT"],
				'password'   => $miniConfig["REDIS_PASSWD"],
				'timeout' => 1
			));
	}else{
		$redisClient = new Predis\Client(
			array(
				'scheme' => 'tcp',
				'host'   => $miniConfig["REDIS_HOST"],
				'port'   => $miniConfig["REDIS_PORT"],
				'timeout' => 1
			));
	}

	$jsonString = $redisClient->get($cfgNm);
	//echo "<pre><hr>jsonString<BR>" . json_encode(json_decode($jsonString,true),JSON_PRETTY_PRINT);	
	$jsonStringDs = $redisClient->get($dataSourceNm);
	//echo "<hr>jsonStringDs<BR>" . json_encode(json_decode($jsonStringDs,true),JSON_PRETTY_PRINT);		

	$redisClient->quit();

	if($jsonString == null || $jsonString == "" || !is_array(json_decode($jsonString,true)) ){
		$rtnArray = $miniConfig;
	}else{
		$rtnArray = array_merge(json_decode($jsonString,true),$miniConfig);

		if($_GET["reload"] == "YES")echo "RELOAD_OK";

		//처음 로딩시 로컬캐시에 보관
		apcu_store($cfgNm, json_encode($rtnArray));
	}


	$rtnArray["CONFIG_NM"] = $cfgNm;
	$rtnArray["CONFIG_DATA_LOAD_FROM"] = "FIRST_REDIS";
}else{

	$rtnArray = json_decode($localCache,true);

	$rtnArray["CONFIG_NM"] = $cfgNm;
	$rtnArray["CONFIG_DATA_LOAD_FROM"] = "LOCAL_CACHE";
}

return $rtnArray;
?>