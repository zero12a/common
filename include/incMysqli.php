<?php

function getSQL($tmp){
	return str_replace("\'","''",$tmp);
}

function getOrigin($tmp){
	return str_replace("\'","'",$tmp);
}

//
function db_open(){
	global $mysql_host, $mysql_userid, $mysql_passwd, $mysql_db;
	
	$link = new mysqli($mysql_host, $mysql_userid, $mysql_passwd, $mysql_db);
	
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

	return $link;
}


//
function db_close($link){
	$link->close();
}

?>