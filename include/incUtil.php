<?php
libxml_use_internal_errors(true);

function jsonView($tmp){
    return json_encode($tmp,JSON_PRETTY_PRINT);
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function getLoggerSwoole($arrLog){
    alog("getLoggerSwoole()...........................start");
    global $CFG;

    //로그 관련 객체 생성.(채널 : log_svc, log_make, log_cg, log_batch)
    $redisClient = null;
    alog("RedisClient connection make");
    try{

        //tcp://172.17.0.1:1234?1234
        if($CFG["REDIS_PASSWD"] != ""){
            $redisClient = new Predis\Client(//$CFG["CFG_AUTH_REDIS"
                array(
                    'scheme' => 'tcp',
                    'host'   => $CFG["REDIS_HOST"],
                    'port'   => $CFG["REDIS_PORT"],
                    'password' => $CFG["REDIS_PASSWD"],
                    'timeout' => 3
                )
            );
        }else{
            $redisClient = new Predis\Client(//$CFG["CFG_AUTH_REDIS"
                array(
                    'scheme' => 'tcp',
                    'host'   => $CFG["REDIS_HOST"],
                    'port'   => $CFG["REDIS_PORT"],
                    'timeout' => 3
                )
            );
        }

        $redisClient->connect();//연결하기
    }catch(Exception $e) {
        alog("RedisClient connection error : " . $e->getMessage());
    }
    $log = null;

    if($redisClient->isConnected()){
        alog("RedisClient connection new : true ");

        /////////////////////////
        // REDIS_LOG
        /////////////////////////
        if(!is_numeric($arrLog["LOG_LEVEL"])){
            if($CFG["CFG_DEBUG_YN"] == "Y"){
                $arrLog["LOG_LEVEL"] = Monolog\Logger::DEBUG;
            }else{
                $arrLog["LOG_LEVEL"] = Monolog\Logger::INFO;
            }
        } 

        alog("LOG_LEVEL : " . $arrLog["LOG_LEVEL"]);
        $redisHandler = new Monolog\Handler\RedisHandler($redisClient, $arrLog["LIST_NM"], $arrLog["LOG_LEVEL"]); // plog is list name
        $redisHandler->setFormatter(new Monolog\Formatter\JsonFormatter());
         //JsonFormatter(int $batchMode = self::BATCH_MODE_JSON, bool $appendNewline = true)
        $log = new Monolog\Logger($arrLog["PGM_ID"], array($redisHandler)); // 채널
        //$log->addInfo('info', array("session_id"=>$s, "url_path"=>$t));

    }else{
        alog("RedisClient connection new : false ");

        /////////////////////////
        // FILE_LOG
        /////////////////////////
        $s = session_id();
        $t = $_SERVER["PHP_SELF"];

        $dateFormat = "y.m.d H:i:s";
        $output = "\n%datetime% [" . $s . "] %level_name% " . sprintf("%-20s", substr($t,0,strlen($t)-4)) . " : %message% %context% %extra%";
        $formatter = new Monolog\Formatter\LineFormatter($output, $dateFormat);

        // Create the logger
        $log = new Monolog\Logger($arrLog["PGM_ID"]);
        // Now add some handlers
        $stream = new Monolog\Handler\StreamHandler($CFG["CFG_LOG_PATH"], Monolog\Logger::INFO);
        $stream->setFormatter($formatter);
        $log->pushHandler($stream);        
    }
    return $log;
}


function getLoggerStdout($arr){

    $log2 = new Monolog\Logger($arr["LIST_NM"]);
    $stream2 = new Monolog\Handler\StreamHandler('php://stdout', $arr["LOG_LEVEL"]);
    //$stream2->setFormatter(new Monolog\Formatter\LineFormatter("\n%channel%.%level_name% : %message% %context% %extra%"));
    $stream2->setFormatter(new Monolog\Formatter\JsonFormatter());
    $log2->pushHandler($stream2);

    $UID = $arr["UID"];
    $PGMID = $arr["PGM_ID"];
    $REQTOKEN = $arr["REQTOKEN"];
    $RESTOKEN = $arr["RESTOKEN"];

    $log2->pushProcessor( function($record) use($UID,$PGMID,$REQTOKEN,$RESTOKEN){
        $record['extra']['UID'] = $UID;
        $record['extra']['PGMID'] = $PGMID;
        $record['extra']['REQTOKEN'] = $REQTOKEN;
        $record['extra']['RESTOKEN'] = $RESTOKEN;
        return $record;
    });

    return $log2;
}


function getLogger($arrLog){
    alog("getLogger()...........................start");
    global $CFG;

    
    //로거 사용 (부가 LIBS)
    //if(!require_once($CFG["CFG_LIBS_MONO_LOG"]))die("getLogger() CFG_LIBS_MONO_LOG load fail");
    //if(!require_once($CFG["CFG_LIBS_PATH_REDIS"]))die("getLogger() CFG_LIBS_PATH_REDIS load fail");
    
    //로그 관련 객체 생성.(채널 : log_svc, log_make, log_cg, log_batch)
    $redisClient = null;
    alog("RedisClient connection make");
    try{
        $redisClient = new Predis\Client($CFG["CFG_AUTH_REDIS"]);
        $redisClient->connect();//연결하기
    }catch(Exception $e) {
        alog("RedisClient connection error : " . $e->getMessage());
    }
    $log = null;

    if($redisClient->isConnected()){
        alog("RedisClient connection new : true ");

        /////////////////////////
        // REDIS_LOG
        /////////////////////////
        if(!is_numeric($arrLog["LOG_LEVEL"])){
            if($CFG["CFG_DEBUG_YN"] == "Y"){
                $arrLog["LOG_LEVEL"] = Monolog\Logger::DEBUG;
            }else{
                $arrLog["LOG_LEVEL"] = Monolog\Logger::INFO;
            }
        } 

        alog("LOG_LEVEL : " . $arrLog["LOG_LEVEL"]);        
        $redisHandler = new Monolog\Handler\RedisHandler($redisClient, $arrLog["LIST_NM"], $arrLog["LOG_LEVEL"]); // plog is list name
        $redisHandler->setFormatter(new Monolog\Formatter\JsonFormatter());
         //JsonFormatter(int $batchMode = self::BATCH_MODE_JSON, bool $appendNewline = true)
        $log = new Monolog\Logger($arrLog["PGM_ID"], array($redisHandler)); // 채널
        //$log->addInfo('info', array("session_id"=>$s, "url_path"=>$t));

        $log->pushProcessor(function ($record) use (&$arrLog) {
            $s = session_id();
            $t = $_SERVER["PHP_SELF"];

            //$record['extra']['env'] = 'staging';
            //$record['extra']['version'] = '1.1';
            $record['context'] = array(
                'SESSIONID' => $s
                , 'URL' => $t
                , 'USERID' => getUserId()
                , 'USERSEQ' => getUserSeq()
                , 'REQTOKEN' => $arrLog["REQTOKEN"]
                , 'RESTOKEN' => $arrLog["RESTOKEN"]
            );
            return $record;
        });

    }else{
        alog("RedisClient connection new : false ");

        /////////////////////////
        // FILE_LOG
        /////////////////////////
        $s = session_id();
        $t = $_SERVER["PHP_SELF"];

        $dateFormat = "y.m.d H:i:s";
        $output = "\n%datetime% [" . $s . "] %level_name% " . sprintf("%-20s", substr($t,0,strlen($t)-4)) . " : %message% %context% %extra%";
        $formatter = new Monolog\Formatter\LineFormatter($output, $dateFormat);

        // Create the logger
        $log = new Monolog\Logger($arrLog["PGM_ID"]);
        // Now add some handlers
        $stream = new Monolog\Handler\StreamHandler($CFG["CFG_LOG_PATH"], Monolog\Logger::INFO);
        $stream->setFormatter($formatter);
        $log->pushHandler($stream);        
    }
    return $log;
}


function xmlCdataAdd($tmp){
	return "<![CDATA[" . $tmp . "]]>";
}

//에러 핸들러
function xhandler($errno,$string, $file, $line, $context){
	global $xhandler_unique_id;
	alog("xhandler____________________________________________start()");
	$errcd = "";

	if (!(error_reporting() & $errno)) {
		alog("  This error code is not included in error_reporting  ");
		return;
	}

	switch ($errno) {
        case E_NOTICE:
			$errcd = "E_NOTICE";
			break;
        case E_USER_NOTICE:
			$errcd = "E_USER_NOTICE";
			break;
		case E_WARNING:
			$errcd = "E_WARNING";
			break;
        case E_USER_WARNING:
			$errcd = "E_USER_WARNING";
			break;
        case E_ERROR:
			$errcd = "E_ERROR";
			break;
        case E_USER_ERROR:
			$errcd = "E_USER_ERROR";
			break;
        default:
            $errcd = "Unknown Error";
            break;
    }

	alog("	session_id : " . session_id());
	alog("	errno : " . $errno);
	alog("	errcd : " . $errcd);
	alog("	string : " . $string);
	alog("	file : " . $file);
	alog("	line : " . $line);
	alog("	context : " . explode("###",$context));

	$map["SESSIONID"] = session_id();
	$map["REQID"] = $xhandler_unique_id;
	$map["ERRNO"] = $errno;
	$map["ERRCD"] = $errcd;
	$map["ERRSTR"] = $string;
	$map["ERRFILE"] = $file;
	$map["ERRLINE"] = $line;
	$map["ERRCONTEXT"] = explode("###",$context);


	//에러 처리 db는 별도로 오픈
	$err_db = db_m_open();

    $to_coltype = "sssss sss";
    $sql = "
		insert into CG_ERRLOG (
			SESSIONID, REQID, ERRNO, ERRCD, ERRSTR, ERRFILE
			,ERRLINE, ERRCONTEXT, ADDDT
		) values
		(
			#SESSIONID#, #REQID#, #ERRNO#, #ERRCD#, #ERRSTR#
			, #ERRFILE#,#ERRLINE#, #ERRCONTEXT#, date_format(sysdate(),'%Y%m%d%H%i%s')
		)
          ";
    $stmt = make_stmt($err_db,$sql, $to_coltype, $map);
    if(!$stmt)   JsonMsg("500","111","stmt 생성 실패" . $db->errno . " -> " . $db->error);
	if(!$stmt->execute())JsonMsg("500","112","stmt 실행 실패" . $db->errno . " -> " . $db->error);

	$to_affected_rows = $db->affected_rows;
	$stmt->close();
    $err_db->close();

	alog("xhandler____________________________________________end()");
}

//카델 표기 변환
function getCamel($t){
	return ucwords(strtolower($t));
}


//xml에러 출력
function display_xml_error($error, $xml)
{
    $return  = $xml[$error->line - 1] . "\n";
    $return .= str_repeat('-', $error->column) . "^\n";

    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "Warning $error->code: ";
            break;
         case LIBXML_ERR_ERROR:
            $return .= "Error $error->code: ";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "Fatal Error $error->code: ";
            break;
    }

    $return .= trim($error->message) .
               "\n  Line: $error->line" .
               "\n  Column: $error->column";

    if ($error->file) {
        $return .= "\n  File: $error->file";
    }

    return "$return\n\n--------------------------------------------\n\n";
}

//xml을 array 받기
function getXml2Array($tXml){
	$xml_array = null;
	$xml = $tXml;
	
	//$xml = str_replace(array("&amp;", "&"), array("&", "&amp;"), $xml); // &문자 그냥 두면 에러이므로 &amp;로 변경하기

	//alog( "불량문자(&) 위치 : ". strpos($xml,"&") );

    //alog("getXml2Array xml BEFORE : ". $tXml );
    $xml = simplexml_load_string($xml,null,LIBXML_NOCDATA); //CDATA제거하기

    //$xml = simplexml_load_string($xml); //CDATA제거하기

	//alog("getXml2Array xml AFTER : ". $xml );
	//alog("getXml2Array is_object(xml) : ". is_object($xml) );
	if (!$xml) {
		alog("	getXml2Array() xml error : " . $tXml);

		$errors = libxml_get_errors();

		foreach ($errors as $error) {
			alog(display_xml_error($error, $xml));
		}

		libxml_clear_errors();
	}else{
		$xml_json = json_encode($xml);
        $xml_array = (array) json_decode($xml_json,TRUE);
        //alog("xml_json : " . $xml_json);
        //alog("xml_array count : " . count($xml_array));
        //var_dump($xml_array);
	}
	return $xml_array;
}

//
function alog($tStr){
    global $log;
    if($log instanceof Monolog\Logger){
        $log->debug($tStr);
    }else{
        $f=fopen('php://stdout',"w");
        fputs($f,$tStr . "\n");
        fclose($f);
    }
}


//alog 기본 함수 활용
function alogOld($tStr){
    global $CFG;

    $s = session_id();
    $t = $_SERVER["PHP_SELF"];

    if(strlen($CFG["CFG_LOG_PATH"]) < 1)return;
    $logFile = $CFG["CFG_LOG_PATH"] . "cg_" . date("Ymd") . ".log";

    //echo $logFile;
    error_log(PHP_EOL .date("y.m.d H:i:s") . " [" . $s . "]" . sprintf(" %-20s : %s", substr($t,0,strlen($t)-4) , $tStr) , 3, $logFile) || die("alog fail : " . $logFile);
}

function isJSON($string){
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
 }
 
//redis에 쓰기
function alogRedis($tStr){
    //alog($tStr);
    global $redis;
    

    //alogOld($tStr);
    if($redis){
        $tLog = PHP_EOL .date("y.m.d H:i:s") . "[" . $s . "]" . sprintf(" %-20s : %s", $_SERVER["PHP_SELF"] , $tStr) ;
        $redis->rpush( 'alog', $tLog); 
    }else{
        JsonMsg("500","100","[Util] alog redis 객체 없음");
    }

}


//json 파일에 쓰기
function alogCustom($tStr){
    global $CFG,$_SERVER;
    $RtnVal = false;
    if(!($f = fopen($CFG["CFG_LOG_PATH"], "a+"))) {
		echo "fopen fail:" . $CFG["CFG_LOG_PATH"];

        $RtnVal = false;
    }else{
		$s = session_id();

        if(!fwrite($f, PHP_EOL .date("y.m.d H:i:s") . "[" . $s . "]" . sprintf(" %-20s : %s", $_SERVER["PHP_SELF"] , $tStr) )) {
            $RtnVal = false;
        }else{
            $RtnVal = true;
        }
    }
    if($f)fclose($f);

    return $RtnVal;
}

//json 파일에 쓰기
function mlog($tStr){
    global $CFG,$_SERVER;
    $RtnVal = true;

    $logFile = $CFG["CFG_LOG_PATH2"] . "cg2_" . date("Ymd") . ".log";

    error_log(PHP_EOL .date("i:s") . sprintf(" %-20s : %s", $_SERVER["PHP_SELF"] , $tStr) , 3, $logFile);

    return $RtnVal;
}

//로그 화면출력 테이블 
function tlog($tStr){
    echo "<table border=0 width=100% style='background-color:silver;'><tr><td><font color=blue>$tStr</font></td></tr></table>";
	alog($tStr);
}


//로그 화면출력
function blog($tStr){
    echo "<br><font color=blue>$tStr</font>";
	alog($tStr);
}


//로그 콘솔 화면출력
function clog($tStr){
    echo "\n$tStr";
	alog($tStr);
}


//로그 화면출력
function glog($tStr){
    echo "<br><font color=green>$tStr</font>";
	alog($tStr);
}
//로그 화면출력
function rlog($tStr){
    echo "<br><font color=red>$tStr</font>";
	alog($tStr);
}

//배열 보기
function aView($tArr){
	echo "<pre>";
	print_r(array_chunk($tArr, 100, true));
	echo "</pre>";
}

//1차월 배열에서 숫자만 뽑아 오기 (값이 없으면 0)
function getIntArray($inArr,$DefaultValue){
    $RtnArr = Array();
    for($i=0;$i<count($inArr);$i++){
        if(preg_match("/([0-9]+)/",$inArr[$i],$mat)){
            $RtnArr[$i] = $mat[1];
        }else{
            $RtnArr[$i] = $DefaultValue;
        }
    }
    return $RtnArr;
}

//배율로 나누기 해서 결과값 int 리턴
function getModArray($inArr,$mod){
    $RtnArr = Array();
    for($i=0;$i<count($inArr);$i++){
        $RtnArr[$i] = intval($inArr[$i] / $mod);
    }
    return $RtnArr;
}

//배열을 구분자 문자열로
function array2pistr($array,$spt){
    global $CFG ;
    $T=null;
    $tArr = array();
    for($i=0;$i<sizeof($array);$i++){
        $tStr = (strpos($array[$i],"-")>0)?explode("-",$array[$i])[1]:$array[$i];
        if(in_array($tStr,$CFG["CFG_PI_COLIDS"])){
            if(!in_array($tStr,$tArr)){
                array_push($tArr,$tStr);
                $T.= ($T!=null)?$spt:"";            
                $T.= $tStr;
            }
        }
    }
    return $T;
}

//배열을 구분자 문자열로
function array2str($array,$spt){
    $T=null;
    for($i=0;$i<sizeof($array);$i++){
        $T.= ($T==null)?$array[$i]:$spt . $array[$i];
    }
    return $T;
}

//배열을 구분자 문자열로
function array2ddstr($array,$spt){
    $T=null;
    for($i=0;$i<sizeof($array);$i++){
        $T.= ($T!=null)?$spt:"";
        $T.= (strpos($array[$i],"-")>0)?explode("-",$array[$i])[1]:$array[$i];
    }
    return $T;
}

//배열을 해쉬맵으로
function array2hash($array){
    $T=null;
    for($i=0;$i<sizeof($array);$i++){
        $T[$array[$i]["NM"]] = $array[$i]["VAL"];
    }
    return $T;
}

//SQL에 '를 \'로 변환
function addSqlSlashes($from){
    return str_replace("'","\'",$from);
}

//한번만 변환
function str_replace_once($from,$to,$all){
    //alog("str_replace_once..........................start");
    $pos = strpos($all,$from);
    $skiplen = strlen($from);

    $left = substr($all,0,$pos);
    $right = substr($all,$pos+strlen($from),strlen($all)-strlen($left.$from));


    //alog(   "from = " . $from);
    //alog(   "to = " . $left . $to . $right);
    
    return $left . $to . $right;
    //echo "<br>left : [" . $left . "]";
    //echo "<br>right : [" . $right. "]";

}
//str_replace_once("#bbb#","?","select a,b,c, where #aaa# and #bbb# and #ccc#");


//해쉬맵 배열 유무
function is_assoc($var)
{
    return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
}



//json 불러오기
function readJson($tFilePath){
	if( file_exists($tFilePath) ){	
		return json_decode(file_get_contents($tFilePath),true);		
	}else
	{
		return null;
	}

	//true is return Array

}


//json 파일에 쓰기
function makeJsonArray($tFilePath, $tArray){
    $RtnVal = false;
	$tJson = json_encode($tArray);
	
	if(!($f = fopen($tFilePath, "w+"))) { 
      $RtnVal = false; 
    }else{
	    if(!fwrite($f, $tJson)) { 
	      	$RtnVal = false; 
	    }else{
			$RtnVal = true;    
	    } 	    
    } 
    
    if($f)fclose($f); 
    
    return $RtnVal;
}


//config 파일에 쓰기
function makeConfigArray($tFilePath, $tArray){
    $RtnVal = false;
    //$tJson = json_encode($tArray);

    if(!($f = fopen($tFilePath, "w+"))) {
        $RtnVal = false;
    }else{
        //파일 헤더 작성
        if( fwrite($f, sprintf("<?// FILE_PATH : %s\n",$tFilePath))
            && fwrite($f, sprintf("// UPDATE DT : %s\n\n",date("Y-m-d H:i:s")))
           ){
            while (list ($key, $val) = each ($tArray) ) {
                //echo $val;

                if( !fwrite($f, sprintf("\$CFG[\"CFG_%s\"] = \"%s\";\n",$key,$val)) ) {
                    $RtnVal = false;
                }else{
                    $RtnVal = true;
                }
                if(!$RtnVal)break;
            }
        }else{
            $RtnVal = false;
        }

        if( !fwrite($f, sprintf("?>",$tFilePath)) ){
            $RtnVal = false;
        }
    }

    if($f)fclose($f);

    //실행 권한 부여
    //chmod($tFilePath, 0755);


    return $RtnVal;
}


//json 파일에 쓰기
function makeJsonDb($tFilePath, $tSql, $tDb){
    $RtnVal = false;
    
    //db에서 정보 불러오기
    $tResult = $tDb->query($tSql) or ServerMsg("500","110",  "[" . $db->error . "] " . $db->error) ; 

    //$tArray = $tResult->fetch_all(MYSQLI_ASSOC) ;

    $tArray = fetch_all($tResult,MYSQLI_ASSOC) ;

    $tJson = json_encode($tArray);
	
	
	//파일에 쓰기
	if(!($f = fopen($tFilePath, "w+"))) { 
      $RtnVal = false; 
    }else{
	    if(!fwrite($f, $tJson)) { 
	      	$RtnVal = false; 
	    }else{
			$RtnVal = true;    
	    } 	    
    } 
    
    if($f)fclose($f); 
    
    return $RtnVal;
}


//코드 리스트 가져오기
function getCodeArray($tP_CD, $tDb){
    $RtnVal = false;
    
    //db에서 정보 불러오기
    $t_sql = sprintf("select * from SU_CODE where P_CD = '%s' and USE_YN = 'Y' "
    	, $tP_CD
    	);
    
    //echo $t_sql;
    $tResult = $tDb->query($t_sql) or ServerMsg("500","110",  "getCodeArray : [" . $tDb->error . "] " . $tDb->error) ; 

    //$tArray = $tResult->fetch_all(MYSQLI_ASSOC) ;
    $tArray = fetch_all($tResult,MYSQLI_ASSOC) ;

    return $tArray;
}

//날짝 출력하기
function getShortDateCal($tdate){
	if(strlen($tdate) >=8){
		return substr($tdate,0,4) . "-" . substr($tdate,4,2)  . "-" . substr($tdate,6,2) ;
	}	
	else
	{
		return $tdate;	
	}
}

//날짝 출력하기
function getShortDate($tdate){
	if(strlen($tdate) >=8){
		return substr($tdate,2,2) . "." . substr($tdate,4,2)  . "." . substr($tdate,6,2) ;
	}	
	else
	{
		return $tdate;	
	}
}

//시간 출력하기
function getShortTime($tdate){
    if(strlen($tdate) >=8){
        return substr($tdate,8,2) . ":" . substr($tdate,10,2)  . ":" . substr($tdate,12,2) ;
    }
    else
    {
        return $tdate;
    }
}

//날짝 출력하기
function getLongDate($tdate){
	if(strlen($tdate) >=8){
		return substr($tdate,2,2) . "-" . substr($tdate,4,2)  . "-" . substr($tdate,6,2) . " " . substr($tdate,8,2)  . ":" . substr($tdate,10,2)   . ":" . substr($tdate,12,2) ;
	}	
	else
	{
		return $tdate;	
	}
}

//날짝 출력하기
function getFullDate($tdate,$t1,$t2){
	if(strlen($tdate) >=8){
		return substr($tdate,0,4) . $t1 . substr($tdate,4,2)  . $t1 . substr($tdate,6,2) . " " . substr($tdate,8,2)  . $t2 . substr($tdate,10,2)   . $t2 . substr($tdate,12,2) ;
	}	
	else
	{
		return $tdate;	
	}
}


//썸네일 생성
function makeThumb($img, $thumb_width, $newfilename) 
{ 
  $max_width=$thumb_width;

    //Check if GD extension is loaded
    if (!extension_loaded('gd') && !extension_loaded('gd2')) 
    {
        trigger_error("GD is not loaded", E_USER_WARNING);
        return false;
    }

    //Get Image size info
    list($width_orig, $height_orig, $image_type) = getimagesize($img);
    
    switch ($image_type) 
    {
        case 1: $im = imagecreatefromgif($img); break;
        case 2: $im = imagecreatefromjpeg($img);  break;
        case 3: $im = imagecreatefrompng($img); break;
        default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
    }
    
    /*** calculate the aspect ratio ***/
    $aspect_ratio = (float) $height_orig / $width_orig;

    /*** calulate the thumbnail width based on the height ***/
    $thumb_height = round($thumb_width * $aspect_ratio);
    

    while($thumb_height>$max_width)
    {
        $thumb_width-=10;
        $thumb_height = round($thumb_width * $aspect_ratio);
    }
    
    $newImg = imagecreatetruecolor($thumb_width, $thumb_height);
    
    /* Check if this image is PNG or GIF, then set if Transparent*/  
    if(($image_type == 1) OR ($image_type==3))
    {
        imagealphablending($newImg, false);
        imagesavealpha($newImg,true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $thumb_width, $thumb_height, $transparent);
    }
    imagecopyresampled($newImg, $im, 0, 0, 0, 0, $thumb_width, $thumb_height, $width_orig, $height_orig);
    
    //Generate the file, and rename it to $newfilename
    switch ($image_type) 
    {
        case 1: imagegif($newImg,$newfilename); break;
        case 2: imagejpeg($newImg,$newfilename);  break;
        case 3: imagepng($newImg,$newfilename); break;
        default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
    }
 
    return true;
}


function getFileExtension($t_file_name){
	if( strrpos($t_file_name,".") == 0 || strlen($t_file_name) == 0 ){
		return "";
	}
	return substr($t_file_name, strrpos($t_file_name,".") +  1, strlen($t_file_name) - strrpos($t_file_name,".")  - 1 );
}

function isAllowExtension($t_file_name,$t_allow_extension){
	$t_ext = getFileExtension($t_file_name);
	if($t_ext == "") return false;
	
	for($i=0;$i< sizeof($t_allow_extension); $i++){
		if( strtoupper($t_ext) == strtoupper($t_allow_extension[$i]) ) return true;
	}
	return  false;
}

function getFileSvrNm($t_file_name, $t_prefix){
	$rnd_val =  date("ymd") . date("His") . getRndVal(4);
	return $t_prefix . $rnd_val . "." . getFileExtension($t_file_name);
}

function addDirectoryToZip($zip, $dir, $base)
{
    $newFolder = str_replace($base, '', $dir);
    $zip->addEmptyDir($newFolder);
    foreach(glob($dir . '/*') as $file)
    {
        if(is_dir($file))
        {
            $zip = addDirectoryToZip($zip, $file, $base);
        }
        else
        {
            $newFile = str_replace($base, '', $file);

            //echo "<br>$file = $newFile";
            $zip->addFile($file, $newFile);
        }
    }
    return $zip;
}


function getRndVal($t_len){

	$CHARS = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$t_val = "";
	
	for($i=0;$i<$t_len;$i++){
		$t_num = rand( 0 , strlen($CHARS)-1 );
		//echo "<BR> tnum : " . $t_num;
		$t_val .= substr($CHARS, $t_num, 1);
	}
	
	return $t_val;
}


function transDate($tdate){

	return (strlen($tdate)!=14)?"":date("Y-m-d H:i:s", mktime( substr($tdate,8,2),  substr($tdate,10,2), substr($tdate,12,2), substr($tdate,4,2), substr($tdate,6,2), substr($tdate,0,4) ) );
}


function getDateDiff($start, $end)
{
        $sdate = strtotime($start);
        $edate = strtotime($end);

        $time = $edate - $sdate;
        if($time>=0 && $time<=59) {
                // Seconds
                $timeshift = $time.' seconds ';

        } elseif($time>=60 && $time<=3599) {
                // Minutes + Seconds
                $pmin = ($edate - $sdate) / 60;
                $premin = explode('.', $pmin);
                
                $presec = $pmin-$premin[0];
                $sec = $presec*60;
                
                $timeshift = $premin[0].' min '.round($sec,0).' sec ';

        } elseif($time>=3600 && $time<=86399) {
                // Hours + Minutes
                $phour = ($edate - $sdate) / 3600;
                $prehour = explode('.',$phour);
                
                $premin = $phour-$prehour[0];
                $min = explode('.',$premin*60);
                
                $presec = '0.'.$min[1];
                $sec = $presec*60;

                $timeshift = $prehour[0].' hrs '.$min[0].' min '.round($sec,0).' sec ';

        } elseif($time>=86400) {
                // Days + Hours + Minutes
                $pday = ($edate - $sdate) / 86400;
                $preday = explode('.',$pday);

                $phour = $pday-$preday[0];
                $prehour = explode('.',$phour*24); 

                $premin = ($phour*24)-$prehour[0];
                $min = explode('.',$premin*60);
                
                $presec = '0.'.$min[1];
                $sec = $presec*60;
                
                $timeshift = $preday[0].' days '.$prehour[0].' hrs '.$min[0].' min '.round($sec,0).' sec ';

        }
        return $timeshift;
}


function getDateDiffShort($start, $end)
{
        $sdate = strtotime($start);
        $edate = strtotime($end);

        $time = $edate - $sdate;
        if($time>=0 && $time<=59) {
                // Seconds
                $timeshift = $time.' seconds ';

        } elseif($time>=60 && $time<=3599) {
                // Minutes + Seconds
                $pmin = ($edate - $sdate) / 60;
                $premin = explode('.', $pmin);
                
                $presec = $pmin-$premin[0];
                $sec = $presec*60;
                
                $timeshift = $premin[0].' min';

        } elseif($time>=3600 && $time<=86399) {
                // Hours + Minutes
                $phour = ($edate - $sdate) / 3600;
                $prehour = explode('.',$phour);
                
                $premin = $phour-$prehour[0];
                $min = explode('.',$premin*60);
                
                $presec = '0.'.$min[1];
                $sec = $presec*60;

                $timeshift = $prehour[0].' hrs';

        } elseif($time>=86400) {
                // Days + Hours + Minutes
                $pday = ($edate - $sdate) / 86400;
                $preday = explode('.',$pday);

                $phour = $pday-$preday[0];
                $prehour = explode('.',$phour*24); 

                $premin = ($phour*24)-$prehour[0];
                $min = explode('.',$premin*60);
                
                $presec = '0.'.$min[1];
                $sec = $presec*60;
                
                $timeshift = $preday[0].' days';

        }
        return $timeshift;
}


//06.12.19
function substr_han($str, $pos = 0, $len = 0) { 
    if ($len < 1 || $len > strlen($str) || $pos + $len > strlen($str)) { 
        return $str; 
    } 

    $oldtoken = substr($str, $pos, 1); 
    $start = $pos; 
    for ($i = $pos + 1; $i < $len; $i++) { 
        $token = substr($str, $i, 1); 
        if (ord($oldtoken) < 129 && ord($token) > 129) { 
            $start = $i; 
        } 
        $oldtoken = $token; 
    } 

    if (($len - $start) % 2 == 0 || $start == $j) { 
        return substr($str, 0, $len); 
    } else { 
        return substr($str, 0, $len - 1); 
    } 
}

function HtmlEncode($tmp){
    //echo $tmp;
	return (is_null($tmp))?"":htmlspecialchars($tmp);
}

function JsonMsg($rtn_cd, $err_cd,  $rtn_msg)
{
	$json_array = array( "RTN_CD"=>$rtn_cd, "ERR_CD"=>$err_cd, "RTN_MSG" => $rtn_msg);
	
	echo json_encode($json_array);
	exit;
}

function JsonData($rtn_cd, $err_cd,  $rtn_msg, $rtn_data){
    $json_array = array( "RTN_CD"=>$rtn_cd, "ERR_CD"=>$err_cd, "RTN_MSG" => $rtn_msg, "RTN_DATA" => $rtn_data);
	
	echo json_encode($json_array);
	exit;
}

function JsonMsgCallback($rtn_cd, $err_cd,  $rtn_msg, $call_back)
{
	$json_array = array( "RTN_CD"=>$rtn_cd, "ERR_CD"=>$err_cd, "RTN_MSG" => $rtn_msg);
	
	echo $call_back . "(" . json_encode($json_array) . ")";
	exit;
}

function ServerMsg($cd1,$cd2,$msg){
	echo $cd1 . ":" . $cd2 . ":" . $_SERVER["SCRIPT_NAME"] . ":" . $msg . PHP_EOL; 
	exit;
}

function ApiMsg($cd1,$cd2,$msg){
	echo $cd1 . "^" . $cd2 . "^" . $msg; 
	exit;
}

function ServerView(){
    echo "<table border=1 width=100%>";
    echo "<tr><th colspan=2>SESSION</th></tr>";
    foreach($_SESSION as $key => $value) {
        echo "<tr><td bgcolor=silver>$key</td> <td>$value</td></tr>";
    }
    echo "</table>";

	echo "<table border=1 width=100%>";
	echo "<tr><th colspan=2>SERVER</th></tr>";
    foreach($_SERVER as $key => $value) {
		echo "<tr><td bgcolor=silver>$key</td> <td>$value</td></tr>";
	}
	echo "</table>";

	echo "<table border=1 width=100%>";
	echo "<tr><th colspan=2>POST</th></tr>";
    foreach($_POST as $key => $value) {
		echo "<tr><td bgcolor=silver>$key</td> <td>$value</td></tr>";
	}
	echo "</table>";
	
	echo "<table border=1 width=100%>";
	echo "<tr><th colspan=2>GET</th></tr>";
    foreach($_GET as $key => $value) {
		echo "<tr><td bgcolor=silver>$key</td> <td>$value</td></tr>";
	}
	echo "</table>";	
}

function ServerViewTxt($session_yn, $server_yn, $post_yn, $get_yn){
    if($session_yn =="Y"){
        echo "\n";
        echo "\nSESSION";
        foreach($_SESSION as $key => $value) {
            echo "\n$key\t=\t$value";
        }
    }

    if($server_yn =="Y"){
        echo "\n";
        echo "\nSERVER";
        foreach($_SERVER as $key => $value) {
            echo "\n$key\t=\t$value";
        }
    }

    if($post_yn =="Y"){
        echo "\n";
        echo "\nPOST";
        foreach($_POST as $key => $value) {
            echo "\n$key\t=\t$value";
			var_dump($value);
        }
    }

    if($get_yn =="Y"){
        echo "\n";
        echo "\nGET";
        foreach($_GET as $key => $value) {
            echo "\n$key\t=\t$value";
        }
    }
}

function ServerText(){

    $RtnValue = "";

    $RtnValue .= "SERVER \n";
    foreach($_SERVER as $key => $value) {
        $RtnValue .= sprintf("%-30s = %s \n",$key,$value);
    }
    $RtnValue .= "\n";

    $RtnValue .= "POST \n";
    foreach($_POST as $key => $value) {
        $RtnValue .= sprintf("%-30s = %s \n",$key,$value);
    }
    $RtnValue .= "\n";

    $RtnValue .= "GET \n";
    foreach($_GET as $key => $value) {
        $RtnValue .= sprintf("%-30s = %s \n",$key,$value);
    }
    $RtnValue .= "\n";

    return $RtnValue;
}

function Msg($tmp){
	?>
	<script language=javascript>
		alert("<?=$tmp?>");
	</script>
	<?php
	//exit;
}

function MsgExit($tmp){
	?>
	<script language=javascript>
		alert("<?=$tmp?>");
	</script>
	<?php
	exit;
}

function MsgBack($tmp){
	?>
	<script language=javascript>
		alert("<?=$tmp?>");
		history.back();
	</script>
	<?php
	exit;
}

function MsgGo($tmp,$url){
	?>
	<script language=javascript>
		alert("<?=$tmp?>");
		location = "<?=$url?>";
	</script>
	<?php
	exit;
}

function MsgClose($tmp){
	?>
	<script language=javascript>
		alert("<?=$tmp?>");
		window.close();
	</script>
	<?php
	exit;
}

function MsgOpenerReload($tmp){
	?>
	<script language=javascript>
		alert("<?=$tmp?>");
		if(opener){
			opener.location.reload();
		}
		self.close();
	</script>
	<?php
	exit;
}

function MsgOpenerGo($tmp,$url){
	?>
	<script language=javascript>
		alert("<?=$tmp?>");
		if(opener){
			opener.location = "<?=$url?>";
		}
	</script>
	<?php
	exit;
}


$SET_email_footer = "<BR><BR><hr><b>Welcome AddMate</b><BR><BR>";


function AdmMail($t_subject,$t_message){
	$to = "zero12a@naver.com,zero12a@dreamwiz.com,19991237@hanmail.net";
	$headers = "From: AutoPR <hot100@hot100.co.kr>\r\n" .
		   'X-Mailer: PHP/' . phpversion() . "\r\n" .
		   "MIME-Version: 1.0\r\n" .
		   "Content-Type: text/html; charset=euc-kr\r\n" .
		   "Content-Transfer-Encoding: 8bit\r\n\r\n";

	$t_message .= $GLOBALS["SET_email_footer"];

	return mail($to, $t_subject, $t_message, $headers); 
}


function CustMail($t_to_email,$t_subject,$t_message){
	$from = "zero12a@naver.com";
	$headers = "From: AddMate <" . $from . ">\r\n" .
		   'X-Mailer: PHP/' . phpversion() . "\r\n" .
		   "MIME-Version: 1.0\r\n" .
		   "Content-Type: text/html; charset=euc-kr\r\n" .
		   "Content-Transfer-Encoding: 8bit\r\n\r\n";

	$t_message .= $GLOBALS["SET_email_footer"];

	return mail($t_to_email, $t_subject, $t_message, $headers); 
}


function saveLog( $t_user_seq, $t_action_cd, $t_action_desc ){

	//로그 저장
   	$T_SQL = sprintf("insert into AM_USER_LOG (USER_SEQ, ACTION_CD, ACTION_DESC, INSERT_DT, INSERT_TM) values ( '%s','%s','%s','%s','%s')"
			, $t_user_seq
			, $t_action_cd
			, $t_action_desc
			, date("Ymd") 
			, date("His") );
	
	mysql_query($T_SQL);
	
}
?>