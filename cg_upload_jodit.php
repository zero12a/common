<?php
header("Content-Type: text/html; charset=UTF-8");

//redis에 모두 넣기
$CFG = require_once("./include/incConfig.php");

require_once("./include/incUtil.php");
require_once("./include/incFile.php");
require_once("./include/incUser.php");
require_once("./include/incRequest.php");
require_once("./include/incSec.php");

//$RtnVal = array();
//$RtnVal2 = array();
$RtnVal = array();
/*

{
    "success":true,
    "time":"2020-08-04 13:04:51",
        "data":
        {
            "baseurl":"https:\/\/xdsoft.net\/jodit\/files\/",
            "messages":[],
            "files":["calendar3-200.png"],
            "isImages":[true],
            "code":220
        }
}

*/

//var_dump($_FILES);

$REQ["storeid"] = reqGetString("storeid",100);

$storeType = $CFG["CFG_FILESTORE"][$REQ["storeid"]]["STORETYPE"];
$acl = $CFG["CFG_FILESTORE"][$REQ["storeid"]]["ACL"]; //private, public-read, public-read-write


//프라이빗이면 로그인 검사하기.
if( ($acl == "" || strtolower($acl) == "private") && !isLogin())MsgExit("로그인 후 파일 접근이 가능합니다. (ACL private)");

//입력값 검증
if( $REQ["storeid"] == "" )MsgExit("storeid 정보가 입력되지 않았습니다.");
if( $CFG["CFG_FILESTORE"][$REQ["storeid"]]["STORETYPE"] == "" )MsgExit("storeid의 STORETYPE 정보를 찾을수 없습니다.");



$fileNm = $_FILES["files"]["name"][0];
//$_FILES["files"]["type"];
//$_FILES["files"]["size"];
$tmpPath = $_FILES["files"]["tmp_name"][0];

$saveFileNm = getFileSvrNm($fileNm,"JODIT_");
$savePath = $CFG["CFG_UPLOAD_DIR"] . $saveFileNm;

//$_FILES["files"]["error"];
if(isAllowExtension($fileNm,$CFG["CFG_IMG_EXT"])){
        
    //if(move_uploaded_file($tmpPath, $savePath)){

    if(moveFileStore($CFG["CFG_FILESTORE"][$REQ["storeid"]], $tmpPath, $saveFileNm)){
        //echo "/up/" . $saveFileNm;      
        $RtnVal["success"] = true;
        $RtnVal["data"]["files"] = array($fileNm);
        $RtnVal["data"]["baseurl"] = "/common/cg_read_filestore.php?fileinfo=0|" . $REQ["storeid"] . "|" . $saveFileNm . "|";   ////timestamp yymmddhhmiss|storeid|sever file name|origin file name

        $RtnVal["data"]["messages"] = array("(msg)Upload success.");
        $RtnVal["data"]["isImages"] = array(true);
        $RtnVal["data"]["code"] = "220";
    }else{
        $RtnVal["success"] = false;
        $RtnVal["data"]["files"] = array();
        $RtnVal["data"]["baseurl"] = "";

        $RtnVal["data"]["messages"] = array("(msg)Upload error.");
        $RtnVal["data"]["isImages"] = array();
        $RtnVal["data"]["error"] ="File dont move to upload folder. storeid=" . $REQ["storeid"] . ", tmpPath=" . $tmpPath . ", svrNm=" . $saveFileNm;
        $RtnVal["data"]["code"] = "500";
    }
}else{
    $RtnVal["success"] = false;
    $RtnVal["data"]["files"] = array();
    $RtnVal["data"]["baseurl"] = "";

    $RtnVal["data"]["messages"] = array("(msg)Image extension error.");
    $RtnVal["data"]["isImages"] = array();
    $RtnVal["data"]["error"] ="Not allow file extension.";
    $RtnVal["data"]["code"] = "500";
}
$RtnVal["time"] = date("Y-m-d H:i:s");


echo json_encode($RtnVal);
?>
