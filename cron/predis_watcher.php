<?php
$CFG = require_once("../include/incConfig.php");

echo "\n" . 1;

if(!include_once "../include/incUtil.php")die("(die) incUtil not include");

echo "\n" .  2;

//search nm, execute sh
$watcherTarget = array(
	array(
			"SEARCH_NM"=>"predis_configCG.php"
			,"EXECUTE_SH"=> $CFG["CFG_COMMON_DIR"] . "cron/predis_configCG.sh"
	),
	array(
		"SEARCH_NM"=>"predis_datasourceCG.php"
		,"EXECUTE_SH"=> $CFG["CFG_COMMON_DIR"] . "cron/predis_datasourceCG.sh"
	),
	array(
		"SEARCH_NM"=>"predis_filestoreCG.php"
		,"EXECUTE_SH"=> $CFG["CFG_COMMON_DIR"] . "cron/predis_filestoreCG.sh"
	)
);

echo "\n" .  sizeof($watcherTarget);
for($i=0;$i<sizeof($watcherTarget);$i++){
	$tmp = $watcherTarget[$i];

	echo "\n" .  3;


	$searchCmd = "ps -ef|grep " . $tmp["SEARCH_NM"]. "|grep -v grep";
	echo "\n" . $searchCmd;

	$output = shell_exec($searchCmd);
	echo "\n" .  4;

	echo $output;
	if(strpos($output, $tmp["SEARCH_NM"]) > 0){
		echo "\n" .  5;
		alog($tmp["SEARCH_NM"] . " process live.");
	}else{
		echo "\n" .  6;		
		alog($tmp["SEARCH_NM"] . " process not live.");
		//alog(" exec = " . $tmp["EXECUTE_SH"]);

		$runCmd = $tmp["EXECUTE_SH"] . " > /dev/null 2>&1 &";
		//$runCmd = $tmp["EXECUTE_SH"] . " &";
		alog("cmd : " . $runCmd);

		$result = shell_exec( $runCmd );

		alog("cmd result : " . $result);
	}
}
echo "\n" . "\n" ;
?>
