<?php
//echo "session_status:" . session_status();
//php5.4이상 echo "ession_status : " . session_status();

session_start(); 

if( !isset($_SESSION) ){
	echo "세션이 시작되지 않았습니다.";
}	

function isLogin(){
	global $_SESSION, $CFG;
	alog("session[CG_USR_SEQ] : " . $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_SEQ"]);
	return is_numeric($_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_SEQ"]);
}

function setUserSeq($tSeq){
	global $_SESSION, $CFG;
    $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_SEQ"] = $tSeq;
}

function getUserSeq(){
	global $_SESSION, $CFG;
	return $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_SEQ"];
}

function setTeamSeq($tSeq){
	global $_SESSION, $CFG;
    $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_TEAM_SEQ"] = $tSeq;
}

function getTeamSeq(){
	global $_SESSION, $CFG;
	return $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_TEAM_SEQ"];
}

function getUserId(){
	global $_SESSION, $CFG;
	return $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_ID"];
}

function setUserId($tId){
	global $_SESSION, $CFG;
    $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_ID"] = $tId;
}


function getUserNm(){
	global $_SESSION, $CFG;
	return $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_NM"];
}

function setUserNm($tNm){
	global $_SESSION, $CFG;
    $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_NM"] = $tNm;
}

function getLoginSeq(){
	global $_SESSION, $CFG;
	return $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_LOGIN_SEQ"];
}

function setLoginSeq($tSeq){
	global $_SESSION, $CFG;
    $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_LOGIN_SEQ"] = $tSeq;
}

function getIntroUrl(){
	global $_SESSION, $CFG;
	return $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_INTRO_URL"];
}

function setIntroUrl($tUrl){
	global $_SESSION, $CFG;
    $_SESSION[ $CFG["CFG_SID_PREFIX"] . "_INTRO_URL"] = $tUrl;
}



//세션만 파기 해야하고, 리다이렉트나 exit하면 안됨.
function logOut(){
	global $_SESSION, $CFG;
	$_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_ID"] = null;	
	$_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_SEQ"] = null;
	$_SESSION[ $CFG["CFG_SID_PREFIX"] . "_AUTH"] = null;
	$_SESSION[ $CFG["CFG_SID_PREFIX"] . "_USR_NM"] = null;
	$_SESSION[ $CFG["CFG_SID_PREFIX"] . "_INTRO_URL"] = null;

	session_destroy();
}


?>