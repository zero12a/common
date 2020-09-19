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
    }
}

*/


function uploadS3($fileStoreCfg, $localTempFileFullName, $remoteFileName){
    global $log;

    $rtnVal = false;
    try {
        $client = Aws\S3\S3Client::factory(
            array(
            'credentials' => array('key' => $fileStoreCfg["CRE_KEY"],'secret' => $fileStoreCfg["CRE_SECRET"]),
            'region' => $fileStoreCfg["REGION"],
            'version' => 'latest'
            )
        );   
        //echo 222;
        $rtnVal = true;
    }catch (Aws\S3\Exception\S3Exception $e) {
        //echo $e->getMessage() . "\n";
        if($log)$log->info("uploadS3() S3Client::factory S3Exception : " . $e->getMessage()); 
        $rtnVal = false;
    }catch (Aws\Exception\AwsException $e) {
        //echo $e->getMessage() . "\n";
        if($log)$log->info("uploadS3() S3Client::factory AwsException : " . $e->getMessage()); 
        $rtnVal = false;
    }
    
    if($rtnVal){
        $rtnVal = false;

        try{
            $result = $client->putObject(array(
                'Bucket'     => $fileStoreCfg["BUCKET"],
                'SourceFile' => $localTempFileFullName,
                'Key'        => $remoteFileName
            ));
        
            //echo 333;
            $rtnVal = true;
        }catch (Aws\S3\Exception\S3Exception $e) {
            //echo $e->getMessage() . "\n";
            if($log)$log->info("uploadS3() putObject S3Exception : " . $e->getMessage());         
            $rtnVal = false;
        }catch (Aws\Exception\AwsException $e) {
            //echo $e->getMessage() . "\n";
            if($log)$log->info("uploadS3() putObject S3Exception : " . $e->getMessage());     
            $rtnVal = false;
        }

        return $rtnVal;
    }else{
        return $rtnVal;
    }

}

function readS3($fileStoreCfg, $remoteFileName){
    global $log;

    $rtnVal = false;
    try {
        $client = Aws\S3\S3Client::factory(
            array(
            'credentials' => array('key' => $fileStoreCfg["CRE_KEY"],'secret' => $fileStoreCfg["CRE_SECRET"]),
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
        header("Content-Type: {$result['ContentType']}");
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
?>