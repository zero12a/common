<?php
header("Content-Type: text/html; charset=UTF-8");

//redis에 모두 넣기
$CFG = require_once("./include/incConfig.php");

require_once("./include/incUtil.php");

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

$fileNm = $_FILES["files"]["name"][0];
//$_FILES["files"]["type"];
//$_FILES["files"]["size"];
$tmpPath = $_FILES["files"]["tmp_name"][0];

$saveFileNm = getFileSvrNm($fileNm,"JODIT_");
$savePath = $CFG["CFG_UPLOAD_DIR"] . $saveFileNm;

//$_FILES["files"]["error"];
if(isAllowExtension($fileNm,$CFG["CFG_IMG_EXT"])){
        
    if(move_uploaded_file($tmpPath, $savePath)){
        //echo "/up/" . $saveFileNm;      
        $RtnVal["success"] = true;
        $RtnVal["data"]["files"] = array($saveFileNm);
        $RtnVal["data"]["baseurl"] = "http://localhost:8040/up/";

        $RtnVal["data"]["messages"] = array("(msg)Upload success.");
        $RtnVal["data"]["isImages"] = array(true);
        $RtnVal["data"]["code"] = "220";
    }else{
        $RtnVal["success"] = false;
        $RtnVal["data"]["files"] = array();
        $RtnVal["data"]["baseurl"] = "";

        $RtnVal["data"]["messages"] = array("(msg)Upload error.");
        $RtnVal["data"]["isImages"] = array();
        $RtnVal["data"]["error"] ="File dont move to upload folder. tmpPath=" . $tmpPath . ", savepath=" . $savePath;
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
