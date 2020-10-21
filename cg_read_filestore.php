<?php
header("Content-Type: text/html; charset=UTF-8");

//redis에 모두 넣기
$CFG = require_once("./include/incConfig.php");
require_once($CFG["CFG_LIBS_VENDOR"]);

if(!require_once("./include/incUtil.php"))echo "incUtil load fail";
if(!require_once("./include/incFile.php"))echo "incFile load fail";
if(!require_once("./include/incSec.php"))echo "incSec load fail";
if(!require_once("./include/incRequest.php"))echo "incRequest load fail";

$REQ["fileinfo"] = reqGetString("fileinfo",100);

$tArr = explode("|",$REQ["fileinfo"] );   //timestamp yymmddhhmiss|storeid|sever file name|origin file name
$REQ["timestamp"] = $tArr[0];
$REQ["storeid"] = $tArr[1];
$REQ["svrfilenm"] = $tArr[2];
$REQ["orgfilenm"] = $tArr[3];

$storeType = $CFG["CFG_FILESTORE"][$REQ["storeid"]]["STORETYPE"];

switch ($storeType){
    case "S3" :
		readS3($CFG["CFG_FILESTORE"][$REQ["storeid"]], $REQ["svrfilenm"], $REQ["orgfilenm"]);
        break;
    case "LOCAL" :
        readLocal($CFG["CFG_FILESTORE"][$REQ["storeid"]], $REQ["svrfilenm"], $REQ["orgfilenm"]);
        break;        
    default:
        echo "111";
        //MsgExit("storeType 명령을 찾을 수 없습니다. (no search storeType)");
        break;
}
?>