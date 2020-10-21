<?php
/*
CFG_FILESTORE : {
    "LOCAL_1" : {
        "TYPE" : "LOCAL"
        ,"UPLOAD_DIR" : "/data/www/up/"
        ,"READ_URL" : "/up/"
    }
    ,"S3_1" : {
        "TYPE" : "S3"
        ,"CRE_KEY" : "..."
        ,"CRE_SECRET" : "..."
        ,"REGION" : "ap-northeast-2"
        ,"BUCKET" : "code-gen-mdm"
        ,"ACL" : 
    }
}

S3 ACL : https://docs.aws.amazon.com/ko_kr/AmazonS3/latest/dev/acl-overview.html
 - private [default], public-read, public-read-write, aws-exec-read, authenticated-read, bucket-owner-read, bucket-owner-full-control

*/

function moveFileStore($fileStoreCfg, $localTempFileFullName, $remoteFileName){
    alog("moveFileStore().............................start");
    if($fileStoreCfg["STORETYPE"] == "S3"){
        return uploadS3($fileStoreCfg, $localTempFileFullName, $remoteFileName);
    }else if($fileStoreCfg["STORETYPE"] == "LOCAL"){
        return move_uploaded_file($localTempFileFullName, $fileStoreCfg["UPLOADDIR"] . $remoteFileName);
    }else{
        return false;
    }
}


function uploadS3($fileStoreCfg, $localTempFileFullName, $remoteFileName){
    global $log, $CFG;
    alog("uploadS3().............................start");
    //alog(" CREKEY = " . aes_decrypt($fileStoreCfg["CREKEY"],$CFG["CFG_SEC_KEY"]));
    //alog(" CRESECRET = " . aes_decrypt($fileStoreCfg["CRESECRET"],$CFG["CFG_SEC_KEY"]));
    //alog(" BUCKET = " .$fileStoreCfg["BUCKET"]);
    alog(" ACL = " .$fileStoreCfg["ACL"]);
    alog(" localTempFileFullName = " . $localTempFileFullName);
    alog(" remoteFileName = " . $remoteFileName);

    $rtnVal = false;
    try {
        $client = Aws\S3\S3Client::factory(
            array(
            'credentials' => array('key' => aes_decrypt($fileStoreCfg["CREKEY"],$CFG["CFG_SEC_KEY"]),'secret' => aes_decrypt($fileStoreCfg["CRESECRET"],$CFG["CFG_SEC_KEY"]) ),
            'region' => $fileStoreCfg["REGION"],
            'version' => 'latest'
            )
        );   
        //echo 222;
        $rtnVal = true;
    }catch (Aws\S3\Exception\S3Exception $e) {
        alog("uploadS3() S3Client::factory S3Exception : " . $e->getMessage());
        if($log)$log->info("uploadS3() S3Client::factory S3Exception : " . $e->getMessage()); 
        $rtnVal = false;
    }catch (Aws\Exception\AwsException $e) {
        alog("uploadS3() S3Client::factory AwsException : " . $e->getMessage());
        if($log)$log->info("uploadS3() S3Client::factory AwsException : " . $e->getMessage()); 
        $rtnVal = false;
    }
    
    if($rtnVal){
        $rtnVal = false;

        $objSize = getimagesize($localTempFileFullName);



        try{

            if($objSize){
                alog("objSize[mime] = ". $objSize["mime"]);
                // https://docs.aws.amazon.com/AmazonS3/latest/API/API_PutObject.html 내용의 붉은색 글씨가 파라미터임.
                $result = $client->putObject(array(
                    'Bucket'        => $fileStoreCfg["BUCKET"],
                    'SourceFile'    => $localTempFileFullName,
                    'Key'           => $remoteFileName,
                    'ACL'           => $fileStoreCfg["ACL"],
                    'ContentType'  => $objSize["mime"]  //Content-Type 이라고 공식 문서는 나와있는데, 실제로는 ContentType으로 업로드 해줘야함.
                ));
            }else{
                $result = $client->putObject(array(
                    'Bucket'        => $fileStoreCfg["BUCKET"],
                    'SourceFile'    => $localTempFileFullName,
                    'Key'           => $remoteFileName,
                    'ACL'           => $fileStoreCfg["ACL"]
                ));
            }

            //로컬 임시 파일 삭제하기
            unlink($localTempFileFullName);
        
            //echo 333;
            $rtnVal = true;
        }catch (Aws\S3\Exception\S3Exception $e) {
            alog("uploadS3() putObject S3Exception : " . $e->getMessage());   
            if($log)$log->info("uploadS3() putObject S3Exception : " . $e->getMessage());         
            $rtnVal = false;
        }catch (Aws\Exception\AwsException $e) {
            alog("uploadS3() putObject AwsException : " . $e->getMessage());
            if($log)$log->info("uploadS3() putObject AwsException : " . $e->getMessage());     
            $rtnVal = false;
        }

        return $rtnVal;
    }else{
        return $rtnVal;
    }

}



function readLocal($fileStoreCfg, $svrFileName, $orgFileName){
    global $log, $CFG;
    alog("readLocal().............................start");
    
    $rtnVal = false;

    $svrFullPath = $fileStoreCfg["UPLOADDIR"] . $svrFileName;
    $readFullUrl =  $fileStoreCfg["READURL"] . $svrFileName;

    if(!file_exists($svrFullPath))MsgExit("서버에 존재하지 않는 파일입니다.(file_exists)");

    //퍼블릭 오픈 이면 해당 객체 바로 접근
    if( strtolower($fileStoreCfg["ACL"]) == "public-read" || strtolower($fileStoreCfg["ACL"]) == "public-read-write" ){
        header('Location: ' . $readFullUrl);
        exit;
    }else{

        $size = getimagesize($svrFullPath);

        if($size){
            //이미지 출력
            header("Content-type: {$size['mime']}");
            $fp = fopen($svrFullPath,"r");
            fpassthru($fp);
            fclose($fp);
            exit;
        }else{
            //일반파일 출력
            header("Content-Type:application/octet-stream");
            header("Content-Disposition:attachment;filename=" . $orgFileName);
            header("Content-Transfer-Encoding:binary");
            header("Content-Length:".filesize($svrFullPath));
            header("Cache-Control:cache,must-revalidate");
            header("Pragma:no-cache");
            header("Expires:0");
            if(is_file($svrFullPath)){
                $fp = fopen($svrFullPath,"r");
                while(!feof($fp)){
                    $buf = fread($fp,8096);
                    $read = strlen($buf);
                    print($buf);
                    flush();
                }
                fclose($fp);
                exit;
            }else{
                MsgExit("해당 경로는 파일이 아닙니다.(is_file)");
            }
        }
    }
}

function readS3($fileStoreCfg, $remoteFileName){
    global $log, $CFG;
    alog("readS3().............................start");
    
    $rtnVal = false;

    //퍼블릭 오픈 이면 해당 객체 바로 접근
    if( strtolower($fileStoreCfg["ACL"]) == "public-read" || strtolower($fileStoreCfg["ACL"]) == "public-read-write" ){
        //형식 : https://codegen-test-bucket.s3.ap-northeast-2.amazonaws.com/img_bomb.jpg
        header('Location: https://' . $fileStoreCfg["BUCKET"] . '.s3.' . $fileStoreCfg["REGION"] . '.amazonaws.com/' . $remoteFileName);
        exit;
    }else{
        //S3에서 내려받기
        try {
            $client = Aws\S3\S3Client::factory(
                array(
                'credentials' => array('key' => aes_decrypt($fileStoreCfg["CREKEY"],$CFG["CFG_SEC_KEY"]),'secret' => aes_decrypt($fileStoreCfg["CRESECRET"],$CFG["CFG_SEC_KEY"]) ),
                'region' => $fileStoreCfg["REGION"],
                'version' => 'latest'
                )
            );   
            //echo 222;
            $rtnVal = true;
        }catch (Aws\S3\Exception\S3Exception $e) {
            //echo $e->getMessage() . "\n";
            if($log)$log->info("readS3() S3Client::factory S3Exception : " . $e->getMessage()); 
            $rtnVal = false;
        }catch (Aws\Exception\AwsException $e) {
            //echo $e->getMessage() . "\n";
            if($log)$log->info("readS3() S3Client::factory AwsException : " . $e->getMessage()); 
            $rtnVal = false;
        }

        try{
            $result = $client->getObject(array(
                'Bucket'     => $fileStoreCfg["BUCKET"],
                'Key'        => $remoteFileName
            ));
        
            // Display the object in the browser.
            header("Content-Type: {$result['ContentType']}");  //이미지를 업로드 해도 불러올떄 "application/octet-stream" 타입으로 리턴함.
            echo $result['Body'];
        }catch (Aws\S3\Exception\S3Exception $e) {
            //echo $e->getMessage() . "\n";
            if($log)$log->info("readS3() getObject S3Exception : " . $e->getMessage()); 
            $rtnVal = false;
        }catch (Aws\Exception\AwsException $e) {
            //echo $e->getMessage() . "\n";
            if($log)$log->info("readS3() getObject AwsException : " . $e->getMessage()); 
            $rtnVal = false;
        }
    }
}
?>