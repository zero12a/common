<?php
header("Content-Type: text/html; charset=UTF-8");

//redis에 모두 넣기
$CFG = require_once("./include/incConfig.php");

require_once("./include/incUtil.php");

$fileNm = $_FILES["file"]["name"];
$tmpPath = $_FILES["file"]["tmp_name"];

$check = getimagesize($tmpPath);
//var_dump($_FILES["file"]);





if($check !== false) {
    //echo "File is an image - " . $check["mime"] . ".";
    if(isAllowExtension($fileNm,$CFG["CFG_IMG_EXT"])){

        $saveFileNm = getFileSvrNm($fileNm,"WE_");

        $savePath = $CFG["CFG_UPLOAD_DIR"] . $saveFileNm;
        if(move_uploaded_file($tmpPath, $savePath)){
            $uploadOk = 1;  
            echo "/up/" . $saveFileNm;      
        }else{
            $uploadOk = 0;
            echo "File dont move to upload folder. tmpPath=" . $tmpPath . ", savepath=" . $savePath;
        }
    }else{
        echo "File is not allow extension.";
        $uploadOk = 0;
    }

} else {
    echo "File is not an image.";
    $uploadOk = 0;
}


?>
