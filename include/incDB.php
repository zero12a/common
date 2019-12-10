<?php
class dbServer
{
    // 프로퍼티 정의
    public $MYSQL_HOST = "";
    public $MYSQL_ID = "";
    public $MYSQL_PW = "";
    public $MYSQL_DB = "";

	function __construct() {
		alog("dbServer class 생성됨 ");
	}
}

function getSQL($tmp){
	return str_replace("\'","''",$tmp);
}

function getOrigin($tmp){
	return str_replace("\'","'",$tmp);
}

//
function db_open(){
	global $mysql_host, $mysql_userid, $mysql_passwd, $mysql_db;
	
	$link = mysql_connect($mysql_host, $mysql_userid, $mysql_passwd)
  	 or die("<br> ". $mysql_host . "db 연결 실패 : " . mysql_error());

    //echo "<br>db 연결 성공";

	mysql_select_db($mysql_db) or die("<br>db 선택 실패.");
	return $link;
}

//
function db_open2(){
	global $mysql_host, $mysql_userid, $mysql_passwd, $mysql_db;
	
	$link = mysql_connect($mysql_host, $mysql_userid, $mysql_passwd,false,65536)
  	 or die("<br> ". $mysql_host . "db 연결 실패 : " . mysql_error());

    //echo "<br>db 연결 성공";

	mysql_select_db($mysql_db) or die("<br>db 선택 실패.");
	return $link;
}

function db_open3(){
	global $mysql_host, $mysql_userid, $mysql_passwd, $mysql_db;
	
	$link = new mysqli($mysql_host, $mysql_userid, $mysql_passwd, $mysql_db)	
  	 or die("<br> ". $mysql_host . "db 연결 실패 : " . $link->connect_error );

    //echo "<br>db 연결 성공";
	return $link;
}

function db_m_open(){
    global $CFG;
    //$mysql_m_host, $mysql_m_userid, $mysql_m_passwd, $mysql_m_db, $mysql_m_port;

    $db = mysqli_init(); 
    if (!$db) {
        die('mysqli_init failed');
    }

    if (!$db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1)) {
        JsonMsg("500","997","db_m_open() Setting MYSQLI_OPT_CONNECT_TIMEOUT failed");
    }
    if($CFG["mysql_m_port"] == "")$CFG["mysql_m_port"] = "3306";

    if(!$db->real_connect($CFG["mysql_m_host"],$CFG["mysql_m_userid"], $CFG["mysql_m_passwd"]
        ,$CFG["mysql_m_db"], $CFG["mysql_m_port"])){

        alog("db_m_open() MYSQL_HOST="    . $CFG["mysql_m_host"]);
        alog("db_m_open() MYSQL_ID="      . $CFG["mysql_m_userid"]);
        alog("db_m_open() MYSQL_DB="      . $CFG["mysql_m_db"]);
        alog("db_m_open() MYSQL_PORT="    . $CFG["mysql_m_port"]);

        JsonMsg("500","998","db_m_open() ". $CFG["mysql_m_host"] . "db 연결 실패 : " . $db->connect_error);
    } 

    //$link = new mysqli($mysql_m_host, $mysql_m_userid, $mysql_m_passwd, $mysql_m_db)
	if (mysqli_connect_errno()) {
		JsonMsg("500","999","db_m_open() Connect failed : " .  mysqli_connect_error());
	    //printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

    //echo "<br>db 연결 성공";
    return $db;
}



function db_s_open(){
    global $mysql_s_host, $mysql_s_userid, $mysql_s_passwd, $mysql_s_db;

    $link = new mysqli($mysql_s_host, $mysql_s_userid, $mysql_s_passwd, $mysql_s_db)
    or die("<br> ". $mysql_s_host . "db 연결 실패 : " . $link->connect_error );

	if (mysqli_connect_errno()) {
		JsonMsg("500","999","db_s_open() Connect failed : " .  mysqli_connect_error());
	    //printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

    //echo "<br>db 연결 성공";
    return $link;
}


function db_b_open(){
    global $mysql_b_host, $mysql_b_userid, $mysql_b_passwd, $mysql_b_db;

    $link = new mysqli($mysql_b_host, $mysql_b_userid, $mysql_b_passwd, $mysql_b_db)
    or die("<br> ". $mysql_b_host . "db 연결 실패 : " . $link->connect_error );
	if (mysqli_connect_errno()) {
		JsonMsg("500","999","db_b_open() Connect failed : " .  mysqli_connect_error());
	    //printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
    //echo "<br>db 연결 성공";
    return $link;
}


function getDbSvrInfo($tSvrId){
    global $CFG;
    alog("getDbSvrInfo()_________________________Start");
    //alog("  CFG_MODE : " . $CFG["CFG_MODE"]);
    //alog("  tSvrId : " . $tSvrId);

    $RtnVal = new stdClass();

    switch ($CFG["CFG_MODE"]){
        case "DEV" :
            $db = db_m_open();

            $T_SQL  = sprintf("select DBHOST, DBPORT, DBNAME, DBUSRID, DBUSRPW from CG_SVR where SVRID = '%s'", addSqlSlashes($tSvrId));
            $result = $db->query($T_SQL) or ServerMsg("500","300", "[" . $db->errno . "] " . $db->error) ;

            //$line2 = null;
            $arr = fetch_all($result,MYSQLI_ASSOC);
            //alog("  SVRID : " . $tSvrId);
            //alog("  DBHOST : " . $arr[0]["DBHOST"]);
            //alog("  DBNAME : " . $arr[0]["DBNAME"]);
            //alog("  DBUSRID : " . $arr[0]["DBUSRID"]);
            alog("CFG_SEC_KEY = " . $CFG["CFG_SEC_KEY"]);
            alog("  DBUSRPW : " . aes_decrypt($arr[0]["DBUSRPW"],$CFG["CFG_SEC_KEY"]));
        
            $RtnVal->MYSQL_HOST =  $arr[0]["DBHOST"];
            $RtnVal->MYSQL_DB =  $arr[0]["DBNAME"];
            $RtnVal->MYSQL_ID =  $arr[0]["DBUSRID"];
            $RtnVal->MYSQL_PW =  aes_decrypt($arr[0]["DBUSRPW"],$CFG["CFG_SEC_KEY"]); //비번 복호화             
            $RtnVal->MYSQL_PORT =  ($arr[0]["DBPORT"] == "")?"3306":$arr[0]["DBPORT"];
            break;

        case "REAL" :
            $RtnVal =   $CFG["CFG_DB"][$tSvrId];
            break;

        case "LOCAL" :
            $RtnVal =   $CFG["CFG_DB"][$tSvrId];
            break;

        default:
            $RtnVal =   $CFG["FG_DB"][$tSvrId];        
            return "CFG_MODE 없음(".$CFG["CFG_MODE"].")";
    }



    return $RtnVal;
}

function db_obj_open($tOBJ_SERVER){
    $db = mysqli_init();
    if (!$db) {
        alog("db_obj_open() mysqli_init failed");
        die('mysqli_init failed');
    }
    if (!$db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1)) {
        alog("db_obj_open() Setting MYSQLI_OPT_CONNECT_TIMEOUT failed");
    }

    if($tOBJ_SERVER->MYSQL_PORT == "")$tOBJ_SERVER->MYSQL_PORT = "3306";

    if(!$db->real_connect($tOBJ_SERVER->MYSQL_HOST, $tOBJ_SERVER->MYSQL_ID, $tOBJ_SERVER->MYSQL_PW, $tOBJ_SERVER->MYSQL_DB, $tOBJ_SERVER->MYSQL_PORT)){
        alog("db_obj_open() MYSQL_HOST=["    . $tOBJ_SERVER->MYSQL_HOST . "]");
        alog("db_obj_open() MYSQL_ID="      . $tOBJ_SERVER->MYSQL_ID);
        alog("db_obj_open() MYSQL_DB="      . $tOBJ_SERVER->MYSQL_DB);
        alog("db_obj_open() MYSQL_PORT="    . $tOBJ_SERVER->MYSQL_PORT);
        //alog("db_obj_open() MYSQL_PW="    . $tOBJ_SERVER->MYSQL_PW);
        alog("mysqli error : " . $db->connect_errno . "/" . $db->connect_error);
        JsonMsg("500","999","db_obj_open() Connect failed : " .  $db->connect_error);
        //printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    //echo "<br>db 연결 성공";
    return $db;
}

 

function db_session_open(){
    global $_SESSION;

    //echo "MYSQL_DB:" . $_SESSION["MYSQL_DB"];
    $link = new mysqli($_SESSION["MYSQL_HOST"], $_SESSION["MYSQL_ID"], $_SESSION["MYSQL_PW"], $_SESSION["MYSQL_DB"])
    or die("<br> ". $_SESSION["MYSQL_HOST"] . "db 연결 실패 : " . $link->connect_error );

	if (mysqli_connect_errno()) {
		JsonMsg("500","999","db_session_open() Connect failed : " .  mysqli_connect_error());
	    //printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
    //echo "<br>db 연결 성공";
    return $link;
}


//
function db_close($link){
	mysql_close($link);
}

function db_close3($link){
    if($link)$link->close();
}


//쿼리에서 값 가져오기
function getVal($tdb,$tsql){
    $RtnVal = "";
    $result = $tdb->query($tsql) or ServerMsg("500","110", "incDB getVal [" . $tdb->errno . "] " . $tdb->error) ;
    if( $line =  $result->fetch_array(MYSQLI_NUM) ){
        $RtnVal = $line[0];
    }
    $result->free();

    return $RtnVal;
}

//쿼리에서 값 가져오기
function getCnt($tdb,$tsql){
    $RtnVal = getVal($tdb,$tsql);
    if(!is_numeric($RtnVal)) $RtnVal = 0;
    return $RtnVal;
}

//쿼리에서 배열 가져오기
function getRows($tdb,$tsql){
    $RtnVal = "";
    //echo $tsql;
    $result = $tdb->query($tsql) or ServerMsg("500","120", "incDB getRows [" . $tdb->errno . "] " . $tdb->error) ;
    $RtnVal = fetch_all($result,MYSQLI_ASSOC);
    $result->free();

    return $RtnVal;
}

//쿼리에서 line 가져오기
function getLine($tdb,$tsql){
    $RtnVal = "";
    //echo "GETLINE SQL : ". $tsql;
    $result = $tdb->query($tsql) or ServerMsg("500","130", "incDB getLine [" . $tdb->errno . "] " . $tdb->error) ;
    $RtnVal =  $result->fetch_assoc();
    $result->free();

    return $RtnVal;
}


function mysql_fetch_all($r, $db){
	 if( mysql_num_rows($r) )
	 	while($row=mysql_fetch_array($r)) $result[] = $row;
	 return $result;
}

function fetch_all($tresult,$resulttype)
{
    //echo "fetch_all:";
    if (method_exists('mysqli_result', 'fetch_all')) # Compatibility layer with PHP < 5.3
    {
        //echo "fetch_all method exist";
        $res = $tresult->fetch_all($resulttype);
    }
    else{
        //echo "fetch_all method not exist";
        for ($res = array(); $tmp = $tresult->fetch_array($resulttype);) $res[] = $tmp;
    }

    return $res;
}


function makeStmt($db,$sql,$coltype,$map){
    global $PGM_CFG, $log;
	//alog("makeStmt-----------------------------------start");

    $tParamColids = array(); //G3-COLID를 그대로 저장
    //$tDdColids = ""; //G3-COLID일때 G3은 제거

    $k = 0;//본 sql
    $d = 0;//디버그 sql
    $to_map = array();
    $to_sql = $sql;
    $debug_sql = $sql;
    //$to_coltype = $coltype;

    $to_coltype = str_replace(" ","",$coltype,$count);
    if($log)$log->info("to_coltype before : " . $to_coltype);    
    //echo "\n to_coltype replace:" . $count;
    //LogMaster::log("        to_coltype : " . $to_coltype);


    //파라미터 분해 (정규식에서 .를 검색할때는 []안에 인수 값중에 맨뒤에 가면 동작안함)
    while(preg_match("/(#{)([\.a-zA-Z0-9_-]+)(})/",$to_sql,$mat)){
        //echo "<br>org : " . HtmlEncode($org);
        //echo "\n<br>매칭0 : " . $mat[0];
        //alog("매칭1 : " . $mat[1]);
        //alog("매칭2 : " . $mat[2]);
        //echo "\n<br>매칭3 : " . $mat[3];
        //echo "<br>매칭4 : " . $mat[4];
        //alog( sprintf("%3s %1s - %20s = [%s]", $k, substr($to_coltype,$k,1), $mat[2] , $map[$mat[2]]) );

        $tColtype = substr($to_coltype,$d,1) ;
        if(is_array($map[$mat[2]])){
            //멀티 값 처리
            if(sizeof($map[$mat[2]]) >= 1){
                //배열인데 값이 1개 이상일때
                $inSql = "";
                $add_coltype = "";
                for($j=0;$j<sizeof($map[$mat[2]]);$j++){
                    $tVal = $map[$mat[2]][$j];

                    if($tColtype == "i" || $tColtype == "d"){
                        $inSql .= ($inSql != "")? ", " . addSqlSlashes( $tVal ) : addSqlSlashes( $tVal ); 
                    }else if($tColtype == "t"){
                        if( isDate($tVal) ){
                            $inSql .= ($inSql != "")? ", '" . addSqlSlashes( $tVal ) . "'" : "'". addSqlSlashes( $tVal ) . "'";                             
                        }else{
                            $inSql .= ($inSql != "")? ", null" : "null";                             
                        }
                    }else{
                        $inSql .= ($inSql != "")? ", '" . addSqlSlashes( $tVal ) . "'" : "'". addSqlSlashes( $tVal ) . "'"; 
                    }
                    $add_coltype .= ($j>0)? $tColtype : "";
                }

                //1보다 큰 경우 타입 자동으로 복제 추가
                //alog( " coltype left = " . substr($to_coltype,0,$d+1));                            
                //alog( " add_coltype = " . $add_coltype);
                //alog( " coltype right = " . substr($to_coltype,$d+1,strlen($to_coltype)));     
                $to_coltype = substr($to_coltype,0,$d+1) . $add_coltype . substr($to_coltype,$d+1,strlen($to_coltype)); 

                $inSql = "(" . $inSql . ")";

                $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], $inSql ,$debug_sql);
            }else{
                //배열인데, 값이 전혀 없을때
                if($tColtype == "i" || $tColtype == "d"){
                   $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], "(null)" ,$debug_sql);
                }else if($tColtype == "t"){
                   if( isDate($map[$mat[2]]) ){
                        $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], "('')",$debug_sql);
                    }else{
                        $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], "null" ,$debug_sql);
                    }
                }else{
                    $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3],"('')",$debug_sql);
                }
            }

        }else{
            //단일 입력값일때 
            if($tColtype == "i" || $tColtype == "d"){
                if($map[$mat[2]] == ""){
                    $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], "null" ,$debug_sql);
                }else{
                    $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], addSqlSlashes( $map[$mat[2]] ) ,$debug_sql);
                }

            }else if($tColtype == "t"){
                if( isDate($map[$mat[2]]) ){
                    $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], "'" . addSqlSlashes( $map[$mat[2]] )  . "'",$debug_sql);
                }else{
                    $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], "null" ,$debug_sql);
                }
            }else{
                $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3],"'".  addSqlSlashes( $map[$mat[2]] ) ."'",$debug_sql);
            }
        }

        $d++;


        //값이 배열이면 " in ? " ==>  " in ( a, b, c ) "로 만든기
        //alog($mat[2] . " sizeof = " . sizeof($map[$mat[2]]));

        if(is_array($map[$mat[2]])){
            //멀티 값 처리
            if(sizeof($map[$mat[2]]) >= 1){
                //배열인데 값이 1개 이상일때
                $inSql = "";
                for($j=0;$j<sizeof($map[$mat[2]]);$j++){
                    $tVal = $map[$mat[2]][$j];
                    $inSql .= ($inSql != "")? ", ?" : "?"; 
    
                    //[로그 저장용]
                    array_push($tParamColids,$mat[2]);
    
                    $to_map[$k] = $tVal;
                    $k++;
                }
                $inSql = "(" . $inSql . ")";
            }else{
                //배열인데, 값이 전혀 없을때
                $to_map[$k] = "";
                $k++;                
                $inSql = "(?)";

                //[로그 저장용]
                array_push($tParamColids,"");                
            }

            $to_sql = str_replace_once($mat[1].$mat[2].$mat[3],$inSql,$to_sql);
        }else{
            //단일 값 처리
            //$to_sql = str_replace($mat[1].$mat[2].$mat[3],"?",$to_sql);
            $to_sql = str_replace_once($mat[1].$mat[2].$mat[3],"?",$to_sql);

            //[로그 저장용]
            array_push($tParamColids,$mat[2]);
            
            //$tDdColids .= ($tDdColids != "")? "," : "";
            //$tDdColids .=(strpos($mat[2],"-")>0)?explode("-",$mat[2])[1]:$mat[2];

            //타입이 number타입인데 값이 없으면  null을 입력해주기.
            //alog("***   to_coltype = " . substr($to_coltype,$k,1));
            //alog("***   value = " . $mat[2]);                  
            //alog("***   value = " . $map[$mat[2]]);            
            if(
                ( substr($to_coltype,$k,1) == "i" || substr($to_coltype,$k,1) == "d" )
                && strval($map[$mat[2]]) == ""
                ){
                //숫자 타입인데 값이 없으면 null 문자열 넣어주기.
                //alog("***   set null");
                $to_map[$k] = null;
            }else{
                //alog("***   set not null");                
                $to_map[$k] = $map[$mat[2]];
            }
            $k++;
        }

        
        //echo "\ntosql : " . $tosql;
        //exit;
    }

    //최종
    if($log)$log->info("to_coltype after : " . $to_coltype);    
	if($log)$log->info("prepare sql : " . $to_sql);
    if($log)$log->info("full sql : " . $debug_sql);

    //[로그저장용] 권한변경로그용 SQL더하기 
    if($PGM_CFG["SECTYPE"] == "POWER" || $PGM_CFG["SECTYPE"] == "PI") {
        $tArr = array("PREPARE_SQL"=>$to_sql,"FULL_SQL"=>$debug_sql, "COLIDS"=>$tParamColids);
        array_push($PGM_CFG["SQLTXT"],$tArr);
    }

    $stmt = $db->prepare($to_sql);
    //echo "\n stmt is_object : " . is_object($stmt);
    //$stmt->bind_param($to_coltype, $to_map);

    if(!$stmt){
        if($log)$log->info("        stmt error : " . $stmt->errno . " > " . $stmt->error);
        return false;
    }else if($k > 0){
		//sql문에 bind param이 하나라도 있으면 처리
        //alog("        stmt ok");

        //다시한번
        $bind_names = null;
        $bind_names[] = $to_coltype;
        for ($i=0; $i<count($to_map);$i++)
        {
            $bind_name = 'bind' . $i;
            $$bind_name = $to_map[$i];
            $bind_names[] = &$$bind_name;
        }

        //바인드 파람 처리
        if(!call_user_func_array(array(&$stmt, 'bind_param'), $bind_names)){
            if($log)$log->info("        bind_param error : " . $stmt->errno . " > " . $stmt->error);
            return false;
        }
    }
    /* Set our params */

	//alog("makeStmt-----------------------------------end");

    return $stmt;
}


function make_stmt($db,$sql,$coltype,$map){
	//alog("make_stmt-----------------------------------start");

    $k = 0;
    $to_map = array();
    $to_sql = $sql;
    $debug_sql = $sql;
    //$to_coltype = $coltype;
    //LogMaster::log("        coltype : " . $coltype);
    $to_coltype = str_replace(" ","",$coltype,$count);
    //echo "\n to_coltype replace:" . $count;
    //alog("        to_coltype : " . $to_coltype);


    //파라미터 분해
    while(preg_match("/(#)([\.a-zA-Z0-9_-]+)(#)/",$to_sql,$mat)){
        //alog("<br>org : " . HtmlEncode($to_sql));
        //echo "\n<br>매칭0 : " . $mat[0];
        //echo "\n<br>매칭1 : " . $mat[1];
        //echo "\n<br>매칭2 : " . $mat[2];
        //echo "\n<br>매칭3 : " . $mat[3];
        //echo "<br>매칭4 : " . $mat[4];
        //alog( sprintf("%3s %1s - %20s = [%s]", $k, substr($to_coltype,$k,1), $mat[2] , $map[$mat[2]]) );
        if(substr($to_coltype,$k,1) == "i" || substr($to_coltype,$k,1) == "d"){
            //alog( "a       $k ". substr($to_coltype,$k,1). " " . $mat[1].$mat[2].$mat[3] . " = " . $map[$mat[2]]);
            $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], addSqlSlashes( $map[$mat[2]] ) ,$debug_sql);
        }else if(substr($to_coltype,$k,1) == "t"){
			//alog( "b isDate " . isDate($map[$mat[2]]) );
			if( isDate($map[$mat[2]]) ){
				//echo "<br>       $k ". substr($to_coltype,$k,1). " " . $mat[1].$mat[2].$mat[3] . " = " . $map[$mat[2]];
				$debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], "'" . addSqlSlashes( $map[$mat[2]] )  . "'",$debug_sql);
			}else{
				$debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], "null" ,$to_sql);
			}
        }else{
            //alog("c       $k ". substr($to_coltype,$k,1) . " " .  $mat[1].$mat[2].$mat[3] . " = '" . $map[$mat[2]] . "'");
            $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3],"'".  addSqlSlashes( $map[$mat[2]] ) ."'",$debug_sql);
        }


        //alog("dddd");

        if(substr($to_coltype,$k,1) == "i" || substr($to_coltype,$k,1) == "d"){
            ///LogMaster::log("       $k ". substr($to_coltype,$k,1). " " . $mat[1].$mat[2].$mat[3] . " = " . $map[$mat[2]]);
        }else{
            //LogMaster::log("       $k ". substr($to_coltype,$k,1) . " " .  $mat[1].$mat[2].$mat[3] . " = '" . $map[$mat[2]] . "'");
        }
        //$to_sql = str_replace($mat[1].$mat[2].$mat[3],"?",$to_sql);
        $to_sql = str_replace_once($mat[1].$mat[2].$mat[3],"?",$to_sql);

        $to_map[$k] = $map[$mat[2]];
        $k++;
        //alog("eeee");
        //echo "\ntosql : " . $tosql;
        //exit;
    }

    //최종
	alog("prepare sql : " . $to_sql);
	alog("full sql : " . $debug_sql);
    $stmt = $db->prepare($to_sql);
    //alog("        stmt error 1: " . $db->errno . " > " . $db->error);
    //alog("\n stmt prepare pass");
    //alog("\n stmt is_object : " . is_object($stmt));
    //$stmt->bind_param($to_coltype, $to_map);

    if(!$stmt){
        //alog("        stmt error 2: " . $db->errno . " > " . $db->error);
        return false;
    }else if($k > 0){
		//sql문에 bind param이 하나라도 있으면 처리
        //alog("        stmt ok");

        //다시한번
        $bind_names = null;
        $bind_names[] = $to_coltype;
        for ($i=0; $i<count($to_map);$i++)
        {
            $bind_name = 'bind' . $i;
            $$bind_name = $to_map[$i];
            $bind_names[] = &$$bind_name;
        }

        //바인드 파람 처리
        if(!call_user_func_array(array(&$stmt, 'bind_param'), $bind_names)){
            alog("        bind_param error : " . $db->errno . " > " . $db->error);
            return false;
        }
    }
    /* Set our params */

	//alog("make_stmt-----------------------------------end");

    return $stmt;
}




function getParamCnt($sql){
	//alog("getParamCnt-----------------------------------start");

    $k = 0;
    $to_sql = $sql;
    $debug_sql = $sql;
    $to_coltype = str_replace(" ","",$coltype,$count);


    //파라미터 분해
    while(preg_match("/(#{)([\.a-zA-Z0-9_-]+)(})/",$to_sql,$mat)){
        if(substr($to_coltype,$k,1) == "i" || substr($to_coltype,$k,1) == "d"){
            $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], addSqlSlashes( $map[$mat[2]] ) ,$debug_sql);
        }else if(substr($to_coltype,$k,1) == "t"){
			if( isDate($map[$mat[2]]) ){
				$debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], "'" . addSqlSlashes( $map[$mat[2]] )  . "'",$debug_sql);
			}else{
				$debug_sql = str_replace_once($mat[1].$mat[2].$mat[3], "null" ,$to_sql);
			}
        }else{
            $debug_sql = str_replace_once($mat[1].$mat[2].$mat[3],"'".  addSqlSlashes( $map[$mat[2]] ) ."'",$debug_sql);
        }
        $to_sql = str_replace_once($mat[1].$mat[2].$mat[3],"?",$to_sql);

        $k++;
    }
	//alog("	sql param cnt : " . $k);

	//alog("getParamCnt-----------------------------------end");

    return $k;
}





function make_stmt_debug($db,$sql,$coltype,$map){
    //global $db;
    //i d s b
    //i integer, d double, s string, b blob
    $k = 0;
    $to_map = array();
    $to_sql = $sql;
    $to_coltype = $coltype;

    //$to_coltype = str_replace(",","",$coltype,$count);
    //echo "\n to_coltype replace:" . $count;


    //파라미터 분해
    while(preg_match("/(#)([\.a-zA-Z0-9_-]+)(#)/",$to_sql,$mat)){
        //echo "<br>org : " . HtmlEncode($org);
        //echo "\n<br>매칭0 : " . $mat[0];
        //echo "\n<br>매칭1 : " . $mat[1];
        //echo "\n<br>매칭2 : " . $mat[2];
        //echo "\n<br>매칭3 : " . $mat[3];
        //echo "<br>매칭4 : " . $mat[4];
        if(substr($to_coltype,$k,1) == "i" || substr($to_coltype,$k,1) == "d"){
            $to_sql = str_replace($mat[1].$mat[2].$mat[3],$map[$mat[2]],$to_sql);
        }else{
            $to_sql = str_replace($mat[1].$mat[2].$mat[3],"'". $map[$mat[2]] ."'",$to_sql);
        }


        $to_map[$k] = $map[$mat[2]];
        $k++;
        //echo "\ntosql : " . $tosql;
        //exit;
    }
    //echo "\nto_sql : " . $to_sql;
    //LogMaster::log("        sql : " . $to_sql);
    //echo "\nto_coltype size : " . strlen($to_coltype);
    //echo "\nto_map sizeof : " . sizeof($to_map);
    //var_dump($to_map);

    //최종
    $stmt = $db->prepare($to_sql);
    //$stmt->bind_param($to_coltype, $to_map);

    /* Set our params */



    //$ret = call_user_func_array (array($stmt,'bind_param'),$to_map);

    return $stmt;
}


function make_detail_read_json($stmt){
	//alog("make_detail_read_json-------------------------------start");
	if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패" . $db->errno . " -> " . $db->error);

	$cols = null; //결과 
    $meta = $stmt->result_metadata(); 
    while ($field = $meta->fetch_field()) 
    { 
        $params[] = &$row[$field->name]; 
    } 
    call_user_func_array(array($stmt, 'bind_result'), $params); 


	//alog("	fetch out");
	if($stmt->fetch()) {
		//alog("	fetch in");
		foreach( $row as $key=>$value )
		{
			//alog("	fetch foreach : $key = $value");
			$RtnVal->RTN_DATA[$key]=$value;
		}
	} 

	//$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
    //결과 JSON 화면 출력
    $RtnVal->RTN_CD = "200";
    $RtnVal->ERR_CD = "200";
	//alog("	result json : " . json_encode($RtnVal));
    return  json_encode($RtnVal);
    //var_dump($RtnVal);
}

function make_detail_read_json2($stmt){

	if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패" . $db->errno . " -> " . $db->error);

    $result = $stmt->get_result();


    //LogMaster::log(" result num rows : " . $result->num_rows);

    //$RtnVal["rows"] = null;
    //$RtnVal->sql = $sql;
    //$RtnVal->cnt = $result->num_rows;
    $RtnVal = null;

    if($row = $result->fetch_assoc()){
        //LogMaster::log(" row sizeof : " . sizeof($row));
        //LogMaster::log(" id : " . $row[0]);

        $finfo = $result->fetch_fields();
        $one_row = array();
        $j = 0;
        foreach ($finfo as $val) {
            //LogMaster::log(" $j  " . $val->name . "=" . $row[$val->name]);

            $RtnVal->RTN_DATA[$val->name]=$row[$val->name];

        }

        //$tarr = array("id"=>$row[0],"data"=>$row);
        //$RtnVal["rows"] = array_push($RtnVal["rows"],$tarr);
    }
    $result->free();

    //$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
    //결과 JSON 화면 출력
    $RtnVal->RTN_CD = "200";
    $RtnVal->ERR_CD = "200";
    return  json_encode($RtnVal);
    //var_dump($RtnVal);
}



function getStmtArray($stmt){
	if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패" . $db->errno . " -> " . $db->error);


    $RtnVal = array();
       
    

	$stmt->store_result();
	$variables = array();
	$data = array();
	$meta = $stmt->result_metadata();
	while($field = $meta->fetch_field())
	{
		$variables[] = &$data[$field->name];
	}
	call_user_func_array(array($stmt, 'bind_result'), $variables);
    $i=0;
    
	while($stmt->fetch())
	{
			$array[$i] = array();
			$one_row = array();
			$j = 0;
			foreach($data as $k=>$v){
				$one_row[$k]=$v;
			}
			$RtnVal[$i]=$one_row;

			$i++;
	}
	$stmt->close();


    return  $RtnVal;

}


function make_grid_read_array($stmt){
	if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패" . $db->errno . " -> " . $db->error);

    //$RtnVal = new \stdClass();
    if (!isset($RtnVal)){
        $RtnVal = new stdClass();
    }
       
    

	$stmt->store_result();
	$variables = array();
	$data = array();
	$meta = $stmt->result_metadata();
	while($field = $meta->fetch_field())
	{
		$variables[] = &$data[$field->name];
	}
	call_user_func_array(array($stmt, 'bind_result'), $variables);
    $i=0;
    
    $RtnVal->RTN_DATA = new stdClass();
	$RtnVal->RTN_DATA->data = array();
	while($stmt->fetch())
	{
			$array[$i] = array();
			$one_row = array();
			$j = 0;
			foreach($data as $k=>$v){
				$one_row[$k]=$v;
			}
			$RtnVal->RTN_DATA->data[$i]=$one_row;

			$i++;
	}
	$stmt->close();

	//$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
	//결과 JSON 화면 출력
	$RtnVal->RTN_CD = "200";
	$RtnVal->ERR_CD = "200";
	//var_dump($RtnVal);

    return  $RtnVal;

}



function make_grid_read_json($stmt,$keycolidx){
    alog("make_grid_read_json.........................................start");
	if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패" . $db->errno . " -> " . $db->error);


	$stmt->store_result();
	$variables = array();
	$data = array();
	$meta = $stmt->result_metadata();
	while($field = $meta->fetch_field())
	{
		$variables[] = &$data[$field->name];
	}
	call_user_func_array(array($stmt, 'bind_result'), $variables);
	$i=0;
	while($stmt->fetch())
	{
			$array[$i] = array();
			$one_row = array();
			$j = 0;
			foreach($data as $k=>$v){

				$array[$i][$k] = $v;
				array_push($one_row, $v );
				if($j == $keycolidx)$id_col_value = $v;
				$j++;
			}
			$RtnVal->RTN_DATA->rows[$i]['id']=$id_col_value;
			$RtnVal->RTN_DATA->rows[$i]['data']=$one_row;

			$i++;
	}
	$stmt->close();

	//$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
	//결과 JSON 화면 출력
	$RtnVal->RTN_CD = "200";
	$RtnVal->ERR_CD = "200";
	//var_dump($RtnVal);

    return  json_encode($RtnVal);

}

function make_grid_read_json4($stmt,$keycolidx){
	if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패" . $db->errno . " -> " . $db->error);


	$stmt->store_result();
	$variables = array();
	$data = array();
	$meta = $stmt->result_metadata();
	while($field = $meta->fetch_field())
	{
		$variables[] = &$data[$field->name];
	}
	call_user_func_array(array($stmt, 'bind_result'), $variables);
	$i=0;
	while($stmt->fetch())
	{
			$array[$i] = array();
			$one_row = array();
			$j = 0;
			foreach($data as $k=>$v){
				$one_row[$k]=$v;
			}
			$RtnVal->RTN_DATA->data[$i]=$one_row;

			$i++;
	}
	$stmt->close();

	//$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
	//결과 JSON 화면 출력
	$RtnVal->RTN_CD = "200";
	$RtnVal->ERR_CD = "200";
	//var_dump($RtnVal);

    return  json_encode($RtnVal);

}

function make_grid_read_json3($stmt,$keycolidx){
	if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패" . $db->errno . " -> " . $db->error);


	$stmt->store_result();
	$variables = array();
	$data = array();
	$meta = $stmt->result_metadata();
	while($field = $meta->fetch_field())
	{
		$variables[] = &$data[$field->name];
	}
	call_user_func_array(array($stmt, 'bind_result'), $variables);
	$i=0;
	while($stmt->fetch())
	{
			$array[$i] = array();
			$one_row = array();
			$j = 0;
			foreach($data as $k=>$v){

				$array[$i][$k] = $v;
				array_push($one_row, $v );
				if($j == $keycolidx)$id_col_value = $v;
				$j++;
			}
			$RtnVal->RTN_DATA->data[$i]['id']=$id_col_value;
			$RtnVal->RTN_DATA->data[$i]['data']=$one_row;

			$i++;
	}
	$stmt->close();

	//$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
	//결과 JSON 화면 출력
	$RtnVal->RTN_CD = "200";
	$RtnVal->ERR_CD = "200";
	//var_dump($RtnVal);

    return  json_encode($RtnVal);

}

function make_grid_read_json2($stmt,$keycolidx){

	if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패" . $db->errno . " -> " . $db->error);

    $result = $stmt->get_result();


    //LogMaster::log(" result num rows : " . $result->num_rows);

    //$RtnVal["rows"] = null;
    $i=0;

    //$RtnVal->sql = $sql;
    //$RtnVal->cnt = $result->num_rows;
    $RtnVal = null;
    while($row = $result->fetch_assoc()){
        //LogMaster::log(" row sizeof : " . sizeof($row));
        //LogMaster::log(" id : " . $row[0]);

        $finfo = $result->fetch_fields();
        $one_row = array();
        $j = 0;
        foreach ($finfo as $val) {
            ///LogMaster::log(" $j  " . $val->name . "=" . $row[$val->name]);
            array_push($one_row, $row[$val->name] );
            if($j == $keycolidx)$id_col_name = $val->name;
            $j++;
        }
        $RtnVal->RTN_DATA->rows[$i]['id']=$row[$id_col_name];
        $RtnVal->RTN_DATA->rows[$i]['data']=$one_row;
        $i++;

        //$tarr = array("id"=>$row[0],"data"=>$row);
        //$RtnVal["rows"] = array_push($RtnVal["rows"],$tarr);
    }
    $result->free();

    //$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
    //결과 JSON 화면 출력
    $RtnVal->RTN_CD = "200";
    $RtnVal->ERR_CD = "200";
    //var_dump($RtnVal);
    return  json_encode($RtnVal);

}


function make_detail_save_json($db,$REQ,$sql,$coltype){
	alog("make_detail_save_json----------------------------------------start");

    if($sql != null && $coltype != null){

        $stmt = make_stmt($db, $sql, $coltype, $REQ);
        if(!$stmt)  JsonMsg("500","100","stmt 생성 실패" . $db->errno . " -> " . $db->error);
        
        if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패" . $db->errno . " -> " . $db->error);
		
        //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
        $to_affected_rows = $db->affected_rows;
        $stmt->close();

        //$tarr = array("OLD_ID"=>$row["@attributes"]["id"],"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>$row["userdata"],"AFFECTED_ROWS"=>$to_affected_rows);

        $RtnVal["RTN_DATA"] = $to_affected_rows;

        //결과 JSON 화면 출력
        $RtnVal["RTN_CD"] = "200";
        $RtnVal["ERR_CD"] = "200";

    }else{
        //결과 JSON 화면 출력
        $RtnVal["RTN_CD"] = "500";
        $RtnVal["ERR_CD"] = "510";
        $RtnVal["RTN_MSG"] = "처리 할 SQL 없습니다.";

    }


    $RtnVal = json_encode($RtnVal);

    return $RtnVal;
}


function make_grid_save_json_new($db,$REQ,$cols,$rows,$sql_inserted,$sql_inserted_coltype,$sql_deleted,$sql_deleted_coltype,$sql_updated,$sql_updated_coltype,$ai_yn,$key_colid){
    alog("make_grid_save_json_new()___________________________start");
    //global $REQ;
    //$colord_array = explode(",",$colord);
    //$coltype_array = explode(",",$coltype);
    //$coltype_map = null;
    //for($i=0;$i<sizeof($colord_array);$i++){
    //    $coltype_map[$colord_array[$i]] = $coltype_array[$i];
    //echo "\ncolord $i=" . $coltype_array[$i];
    //}

    $xml_array_last = null;
    alog("is_assoc : " . is_assoc($rows) );
    alog("is_assoc row id: " . $rows[0]["row id"] );
    if(is_assoc($rows) == 1) {
		alog(" Y " );
        $xml_array_last[0] = $rows;
    }else{
		alog(" N " );

        $xml_array_last = $rows;
    }
    //var_dump($xml_array_last);

    $RtnVal = null;
    $RtnCnt = 0;
	alog("xml sizeof : " . sizeof($xml_array_last));
    for($i=0;$i<sizeof($xml_array_last);$i++){

        $row = $xml_array_last[$i];
        alog("        i : " . $i);
        alog("        row id : " . $row["row id"]);
        alog("        !nativeeditor_status : " . $row["!nativeeditor_status"]);

        //현재 그리드 line을 bind 배열에 담기
        $to_row = null;
        $to_coltype = null;
        $sql = null;
        for($j=0;$j<sizeof($row["cell"]);$j++){
            $col = $row["cell"][$j];
            if(is_array($col)){
                $to_row[trim($cols[$j])] = "";
            }else{
                $to_row[trim($cols[$j])] = $col;
            }
        }

        if($row["!nativeeditor_status"] == "inserted"){
            $to_coltype = $sql_inserted_coltype;
            //LogMaster::log("        to_coltype : " . $to_coltype);
            $sql = $sql_inserted;
            alog("        inserted : " );
        }
        if($row["!nativeeditor_status"] == "updated"){
            $to_coltype = $sql_updated_coltype;
            $sql = $sql_updated;
            alog("        updated : " );
        }
        if($row["!nativeeditor_status"] == "deleted"){
            $to_coltype = $sql_deleted_coltype;
            $sql = $sql_deleted;
            alog("        deleted : " );
        }


        if($sql != null ){
            //LogMaster::log("        to_coltype : " . $to_coltype);
            $stmt = make_stmt($db,$sql, $to_coltype, array_merge($REQ,$to_row));
           
			if(!$stmt) JsonMsg("500","100","stmt 생성 실패" . $db->errno . " -> " . $db->error);
           
			if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패" . $db->errno . " -> " . $db->error);

            //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
            $to_affected_rows = $db->affected_rows;
			if($row["!nativeeditor_status"] == "inserted"){
				if($ai_yn == "Y"){
					$to_row["COLID"]=$db->insert_id; //insert문인 경우 insert id받기
				}else{
					$to_row["COLID"]=$to_row[$key_colid]; //사용자 입력 key컬럼을 rowid 로
				}
			}
            $stmt->close();

            $tarr = array("OLD_ID"=>$row["row id"],"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>$row["!nativeeditor_status"],"AFFECTED_ROWS"=>$to_affected_rows);

            $RtnVal["RTN_MSG"][$RtnCnt] = $tarr;
            $RtnCnt++;

        }


    }

    //결과 JSON 화면 출력
    $RtnVal["RTN_CD"] = "200";
    $RtnVal["ERR_CD"] = "200";
    $RtnVal["RTN_DATA"]["SEQ_COLID"] = ($ai_yn == "Y")?$key_colid:"";
    $RtnVal = json_encode($RtnVal);
    return $RtnVal;

}


function make_grid_save_json($db,$REQ,$colord,$xml_array,$sql_inserted,$sql_inserted_coltype,$sql_deleted,$sql_deleted_coltype,$sql_updated,$sql_updated_coltype,$ai_yn,$key_colid){
	global $rtnArr;

    //global $REQ;
    $colord_array = explode(",",$colord);
    //$coltype_array = explode(",",$coltype);
    //$coltype_map = null;
    //for($i=0;$i<sizeof($colord_array);$i++){
    //    $coltype_map[$colord_array[$i]] = $coltype_array[$i];
    //echo "\ncolord $i=" . $coltype_array[$i];
    //}

    $xml_array_last = null;
	alog("is_assoc : " . is_assoc($xml_array["row"]) );
    if(is_assoc($xml_array["row"]) == 1) {
		alog(" Y " );
        $xml_array_last[0] = $xml_array["row"];
    }else{
		alog(" N " );

        $xml_array_last = $xml_array["row"];
    }
    //var_dump($xml_array_last);

    $RtnVal = null;
    $RtnCnt = 0;
	alog("xml sizeof : " . sizeof($xml_array_last));
    for($i=0;$i<sizeof($xml_array_last);$i++){

        $row = $xml_array_last[$i];
        alog("        i : " . $i);
        alog("        @attributes : " . $row["@attributes"]["id"]);
        alog("        userdata : " . $row["userdata"]);

        //현재 그리드 line을 bind 배열에 담기
        $to_row = null;
        $to_coltype = null;
        $sql = null;
        for($j=0;$j<sizeof($row["cell"]);$j++){
            $col = $row["cell"][$j];
            if(is_array($col)){
                $to_row[trim($colord_array[$j])] = "";
            }else{
                $to_row[trim($colord_array[$j])] = $col;
            }
        }

        if($row["userdata"] == "inserted"){
            $to_coltype = $sql_inserted_coltype;
            //LogMaster::log("        to_coltype : " . $to_coltype);
            $sql = $sql_inserted;
            alog("        inserted : " );
        }
        if($row["userdata"] == "updated"){
            $to_coltype = $sql_updated_coltype;
            $sql = $sql_updated;
            alog("        updated : " );
        }
        if($row["userdata"] == "deleted"){
            $to_coltype = $sql_deleted_coltype;
            $sql = $sql_deleted;
            alog("        deleted : " );
        }


        if($sql != null ){
            //LogMaster::log("        to_coltype : " . $to_coltype);
            $stmt = make_stmt($db,$sql, $to_coltype, array_merge($REQ,$to_row));
           
			if(!$stmt) JsonMsg("500","100","stmt 생성 실패1" . $db->errno . " -> " . $db->error);
           
			if(!$stmt->execute())JsonMsg("500","100","stmt 실행 실패2" . $db->errno . " -> " . $db->error);


            $to_affected_rows = $db->affected_rows;
            alog("   db affected_rows : " .  $to_affected_rows  ); //stmt를 클로즈 하기 전에 해야            
			if($row["userdata"] == "inserted"){
				if($ai_yn == "Y"){
					$to_row["COLID"]=$db->insert_id; //insert문인 경우 insert id받기
					$rtnArr[$i] = $to_row["COLID"]; //insert문인 경우 seq를 저장해서 리턴하고 SQLD등록할때 사용하게 처리
				}else{
					$to_row["COLID"]=$to_row[$key_colid]; //사용자 입력 key컬럼을 rowid 로
				}
			}
            $stmt->close();

            $tarr = array("OLD_ID"=>$row["@attributes"]["id"],"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>$row["userdata"],"AFFECTED_ROWS"=>$to_affected_rows);

            $RtnVal["RTN_MSG"][$RtnCnt] = $tarr;
            $RtnCnt++;

        }


    }

    //결과 JSON 화면 출력
    $RtnVal["RTN_CD"] = "200";
    $RtnVal["ERR_CD"] = "200";
    $RtnVal["RTN_DATA"]["SEQ_COLID"] = ($ai_yn == "Y")?$key_colid:"";
    $RtnVal = json_encode($RtnVal);
    return $RtnVal;

}

// 날짜 계산
/*

CREATE FUNCTION FN_DATEDIFF
(
	DB_DATE varchar(14),
	NOW_DATE varchar(14)
)  returns varchar(255)
begin
	declare RtnValue varchar(30);
	
	set RtnValue = case 
		when TIMESTAMPDIFF(SECOND,DB_DATE,NOW_DATE) <= 60
			then concat(TIMESTAMPDIFF(SECOND,DB_DATE,NOW_DATE), ' sec')
		when TIMESTAMPDIFF(MINUTE,DB_DATE,NOW_DATE) >= 1 
				and TIMESTAMPDIFF(MINUTE,DB_DATE,NOW_DATE) < 60 
			then concat(TIMESTAMPDIFF(MINUTE,DB_DATE,NOW_DATE), ' min')
		when TIMESTAMPDIFF(HOUR,DB_DATE,NOW_DATE) >= 1 
				and TIMESTAMPDIFF(HOUR,DB_DATE,NOW_DATE) < 24 
			then concat(TIMESTAMPDIFF(HOUR,DB_DATE,NOW_DATE), ' hrs')
		when TIMESTAMPDIFF(DAY,DB_DATE,NOW_DATE) >= 1 
			then concat(TIMESTAMPDIFF(DAY,DB_DATE,NOW_DATE), ' days')
		else '-'
	end;

	return  RtnValue;
end
*/








	function makeDataviewSearchJson($map,&$db){
		global $REQ;
		alog("StdService-makeDataviewSearchJson");

	    $stmt = makeStmt($db[$map["SQL"]["R"]["SVRID"]],$map["SQL"]["R"]["SQLTXT"], $map["SQL"]["R"]["BINDTYPE"], $REQ);
		if(!$stmt)   JsonMsg("500","100","(makeDataviewSearchJson) stmt 생성 실패" . $db->errno . " -> " . $db->error);

		if(!$stmt->execute())JsonMsg("500","110","(makeDataviewSearchJson) stmt 실행 실패" . $db->errno . " -> " . $db->error);


		$stmt->store_result();
		$variables = array();
		$data = array();
		$meta = $stmt->result_metadata();
		while($field = $meta->fetch_field())
		{
			$variables[] = &$data[$field->name];
		}
		call_user_func_array(array($stmt, 'bind_result'), $variables);
		$i=0;

		//alog("	fetch out");
		while($stmt->fetch()) {
			//alog("	fetch in");

			foreach( $data as $key=>$value )
			{
				//alog(" $i	fetch foreach : $key = $value");
				$RtnVal[$i][$key]=$value;
			}
			$i++;
		} 
		$stmt->close();
		//var_dump($RtnVal);

		return  $RtnVal;

	}


    /*
    ###################################################################
    ##  Grid
    ###################################################################
    */
	function makeGridSearchJson($map,&$db){
        global $REQ, $CFG, $PGM_CFG;
        
		alog("StdService-makeGridSearchJson");

	    $stmt = makeStmt($db[$map["SQL"]["R"]["SVRID"]],$map["SQL"]["R"]["SQLTXT"], $map["SQL"]["R"]["BINDTYPE"], $REQ);
		if(!$stmt)   JsonMsg("500","100","(makeGridSearchJson) stmt 생성 실패 " . $db->errno . " -> " . $db->error);

		if(!$stmt->execute())JsonMsg("500","110","(makeGridSearchJson) stmt 실행 실패 " . $stmt->error);




        //$colcrypt_array = explode(",",$map["COLCRYPT"]);   
        $colcrypt_array =$map["COLCRYPT"];  

        //$map["SQLTXT"] = "1111";
        //alog("makeGridSearchJson() SQLTXT = " . $map["SQLTXT"]);

        $stmt->store_result();
        
        //num_rows는 store_result실행 후에 해야함.
        alog("stmt.num_rows = " . $stmt->num_rows);

        //[로그 저장용]
        $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $stmt->num_rows;

        $variables = array();
		$data = array();
		$meta = $stmt->result_metadata();
		while($field = $meta->fetch_field())
		{
			$variables[] = &$data[$field->name];
		}
		call_user_func_array(array($stmt, 'bind_result'), $variables);
		$i=0;
		while($stmt->fetch())
		{
				$array[$i] = array();
				$one_row = array();
				$j = 0;
				foreach($data as $k=>$v){
                    //암호화 컬럼에 존재 하는지 확인
                    if( $colcrypt_array[trim($k)] == "CRYPT" ){
                        //양방향 암호화
                        alog("  crypt 전 col/key: " . $v . "/" . $CFG["CFG_SEC_KEY"]);
                        alog("  cyrpt 후 : [" .  aes_decrypt($v,$CFG["CFG_SEC_KEY"]) . "]");                        

                        //$array[$i][$k] = aes_decrypt($v,$CFG["CFG_SEC_KEY"]);

                        array_push($one_row, aes_decrypt($v,$CFG["CFG_SEC_KEY"]) );
                    }else if( $colcrypt_array[trim($k)] == "HASH" ){
                        //일방향 암호화
                        alog("  hash 전 col/key: " . $v . "/" . $CFG["CFG_SEC_SALT"]);
                        //$array[$i][$k] = aes_decrypt($v,$CFG["CFG_SEC_KEY"]);

                        array_push($one_row, $v );
                    }else if( $colcrypt_array[trim($k)] == "CDATA" ){
                        //Tag가 있는 컬럼.(Cdata더하기)
                        array_push($one_row, xmlCdataAdd($v) );
                    }else{
                        //평문
                        array_push($one_row, $v );
                    }
					
					
					if($j == $map["KEYCOLIDX"])$id_col_value = $v;
					$j++;
				}
				$RtnVal->RTN_DATA->rows[$i]['id']=$id_col_value;
				$RtnVal->RTN_DATA->rows[$i]['data']=$one_row;
				//alog($i);
				$i++;
		}
		$stmt->close();

		//$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
		//결과 JSON 화면 출력
		$RtnVal->RTN_CD = "200";
		$RtnVal->ERR_CD = "200";
		//var_dump($RtnVal);
	
		//$RtnVal = json_encode($RtnVal)
		return  $RtnVal;

	}


	function makeGridSearchJsonArray($map,&$db){
		global $REQ, $CFG, $PGM_CFG;
		alog("makeGridSearchJsonArray..................................start");

        //main/sub sql 갯수 만큼 루프돌면서 처리하기
        alog("  sql sizeof : " . sizeof($map["SQL"]));
        for($s=0;$s<sizeof($map["SQL"]);$s++){
            $tmpSql = $map["SQL"][$s];

            //main/sub 구분에 따라 처리
            alog("  sql[" . $s . "] PSQLSEQ = ". $tmpSql["PSQLSEQ"]);            
            alog("      SVRID = " . $tmpSql["SVRID"]);
            alog("      SQLTXT = " . $tmpSql["SQLTXT"]);
            alog("      BINDTYPE = " . $tmpSql["BINDTYPE"]);
            
            $stmt = makeStmt($db[$tmpSql["SVRID"]], $tmpSql["SQLTXT"], $tmpSql["BINDTYPE"], $REQ);
            if(!$stmt)   JsonMsg("500","112","(makeGridSearchJsonArray) " . $tmpSql["SQLID"] . " stmt 생성 실패 " . $db->errno . " -> " . $db->error);

            if(!$stmt->execute())JsonMsg("500","123","(makeGridSearchJsonArray) " . $tmpSql["SQLID"] . " stmt 실행 실패 " . $stmt->error);
    
            //main만 처리
            if( $tmpSql["PARENT_FNCTYPE"] == ""){
                //$colcrypt_array = explode(",",$map["COLCRYPT"]);   
                $colcrypt_array =$map["COLCRYPT"];  
    
                //$map["SQLTXT"] = "1111";
                //alog("makeGridSearchJson() SQLTXT = " . $map["SQLTXT"]);
    
                $stmt->store_result();
                
                //num_rows는 store_result실행 후에 해야함.
                alog("stmt.num_rows = " . $stmt->num_rows);
    
                //[로그 저장용]
                $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $stmt->num_rows;
    
                $variables = array();
                $data = array();
                $meta = $stmt->result_metadata();
                while($field = $meta->fetch_field())
                {
                    $variables[] = &$data[$field->name];
                }
                call_user_func_array(array($stmt, 'bind_result'), $variables);
                $i=0;
                while($stmt->fetch())
                {
                        $array[$i] = array();
                        $one_row = array();
                        $j = 0;
                        foreach($data as $k=>$v){
                            $tMap = array();
                            //암호화 컬럼에 존재 하는지 확인
                            if( $colcrypt_array[trim($k)] == "CRYPT" ){
                                //양방향 암호화
                                alog("  crypt 전 col/key: " . $v . "/" . $CFG["CFG_SEC_KEY"]);
                                alog("  cyrpt 후 : [" .  aes_decrypt($v,$CFG["CFG_SEC_KEY"]) . "]");                        
    
                                //$array[$i][$k] = aes_decrypt($v,$CFG["CFG_SEC_KEY"]);
                                if($map["GRPTYPE"] == "GRID_BOOTSTRAP"){
                                    $one_row[$k] = aes_decrypt($v,$CFG["CFG_SEC_KEY"]);
                                }else{
                                    array_push($one_row, aes_decrypt($v,$CFG["CFG_SEC_KEY"]) );
                                }
                                
                            }else if( $colcrypt_array[trim($k)] == "HASH" ){
                                //일방향 암호화
                                alog("  hash 전 col/key: " . $v . "/" . $CFG["CFG_SEC_SALT"]);
                                //$array[$i][$k] = aes_decrypt($v,$CFG["CFG_SEC_KEY"]);
                                if($map["GRPTYPE"] == "GRID_BOOTSTRAP"){
                                    $one_row[$k] = $v;
                                }else{
                                    array_push($one_row, $v );
                                }
                            }else{
                                //평문
                                if($map["GRPTYPE"] == "GRID_BOOTSTRAP"){
                                    $one_row[$k] = $v;
                                }else{
                                    array_push($one_row, $v );
                                }                                
                            }
                            
                            
                            if($j == $map["KEYCOLIDX"])$id_col_value = $v;
                            $j++;
                        }
                        
                        if($map["GRPTYPE"] == "GRID_BOOTSTRAP"){
                            $RtnVal->RTN_DATA->rows[$i]=$one_row;                            
                        }else{
                            $RtnVal->RTN_DATA->rows[$i]['id']=$id_col_value;
                            $RtnVal->RTN_DATA->rows[$i]['data']=$one_row;
                        }

                        //alog($i);
                        $i++;
                }
                $stmt->close();
            }

            

        }


		//$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
		//결과 JSON 화면 출력
		$RtnVal->RTN_CD = "200";
		$RtnVal->ERR_CD = "200";
		//var_dump($RtnVal);
	
        //$RtnVal = json_encode($RtnVal)
        
		alog("makeGridSearchJsonArray..................................end");        
		return  $RtnVal;

	}



	function makeGridChkJson($map,&$db){
        global $REQ;
        
        alog("  makeGridChkJson() REQ.G2_GRP_SEQ ". $REQ["G2_GRP_SEQ"]);
        
        //alog("^^^ COLORD : " . $map["COLORD"]);
        $colord_array = explode(",",$map["COLORD"]);
        //alog("^^^ colord_array count : " . count($colord_array));

		$xml_array_last = null;
        alog("makeGridChkJson ----------------------------------------------------- count : " . count($map["CHK"]) );
		//var_dump($xml_array_last);

		$RtnVal = null;
		$RtnCnt = 0;
		for($i=0;$i<count($map["CHK"]);$i++){

			$row = $map["CHK"][$i];
			alog("        i : " . $i);

			//현재 그리드 line을 bind 배열에 담기
			$to_row = null;
			$to_coltype = null;
            $sql = null;
            
            $to_row[$map["KEYCOLID"]] = $row;

            $svrid = $map["SQL"]["SVRID"];
            $to_coltype = $map["SQL"]["BINDTYPE"];
            $sql = $map["SQL"]["SQLTXT"];

			if($sql != null ){
				//LogMaster::log("        to_coltype : " . $to_coltype);
			
				if( getParamCnt($sql) != strlen(str_replace(" ","",$to_coltype)) )JsonMsg("500","190","(makeGridChkJson) sql파라미터와 파라미터타입수가 불일치.");


				$stmt = makeStmt($db[$svrid],$sql, $to_coltype, array_merge($REQ,$to_row));
			   
				if(!$stmt) JsonMsg("500","200","(makeGridChkJson) stmt 생성 실패" . $db[$svrid]->errno . " -> " . [$svrid]->error);
			   
				if(!$stmt->execute())JsonMsg("500","210","(makeGridChkJson) stmt 실행 실패 " . $stmt->error);

				//echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
				$to_affected_rows = $db[$svrid]->affected_rows;

                //[로그 저장용]
                $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;

                alog("SEQYN Y : " . $db[$svrid]->insert_id);
                $to_row["COLID"] = $db[$svrid]->insert_id; //insert문인 경우 insert id받기

				$stmt->close();

				$tarr = array("OLD_ID"=>$row,"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>"","AFFECTED_ROWS"=>$to_affected_rows);

				$RtnVal->ROWS[$RtnCnt] = $tarr;
				$RtnCnt++;

			}else{
                JsonMsg("500","220","(makeGridChkJson) sql문이 없습니다.");
            }


		}

		//결과 JSON 화면 출력
		$RtnVal->GRP_TYPE = "GRID";
	    $RtnVal->SEQ_COLID = ($map["SEQYN"] == "Y")?$map["KEYCOLID"]:"";

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}



	function makeGridChkJsonArray($map,&$db){
        global $REQ;
        
        alog("  makeGridChkJsonArray() REQ.G2_GRP_SEQ ". $REQ["G2_GRP_SEQ"]);
        
        //alog("^^^ COLORD : " . $map["COLORD"]);
        $colord_array = explode(",",$map["COLORD"]);
        //alog("^^^ colord_array count : " . count($colord_array));

		$xml_array_last = null;
        alog("makeGridChkJsonArray ----------------------------------------------------- count : " . count($map["CHK"]) );
		//var_dump($xml_array_last);

		$RtnVal = null;
		$RtnCnt = 0;
		for($i=0;$i<count($map["CHK"]);$i++){

			$row = $map["CHK"][$i];
			alog("        i : " . $i);

			//현재 그리드 line을 bind 배열에 담기
			$to_row = null;
			$to_coltype = null;
            $sql = null;
            
            $to_row[$map["KEYCOLID"]] = $row;


            for($s=0;$s<sizeof($map["SQL"]);$s++){
                $tmpSql = $map["SQL"][$s];
                $svrid = $tmpSql["SVRID"];
                $to_coltype = $tmpSql["BINDTYPE"];
                $sql = $tmpSql["SQLTXT"];
    

                if( getParamCnt($sql) != strlen(str_replace(" ","",$to_coltype)) )JsonMsg("500","190","(makeGridChkJsonArray) " . $tmpSql["SQLID"] . " sql파라미터와 파라미터타입수가 불일치.");

                $stmt = makeStmt($db[$svrid],$sql, $to_coltype, array_merge($REQ,$to_row));
                if(!$stmt) JsonMsg("500","200","(makeGridChkJsonArray) " . $tmpSql["SQLID"] . "  stmt 생성 실패" . $db[$svrid]->errno . " -> " . [$svrid]->error);
                if(!$stmt->execute())JsonMsg("500","210","(makeGridChkJsonArray) " . $tmpSql["SQLID"] . "  stmt 실행 실패 " . $stmt->error);

                if($tmpSql["PARENT_FNCTYPE"] == ""){
                    //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
                    $to_affected_rows = $db[$svrid]->affected_rows;

                    //[로그 저장용]
                    $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;

                    alog("SEQYN Y : " . $db[$svrid]->insert_id);
                    $to_row["COLID"] = $db[$svrid]->insert_id; //insert문인 경우 insert id받기

                    $stmt->close();

                    $tarr = array("OLD_ID"=>$row,"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>"","AFFECTED_ROWS"=>$to_affected_rows);

                    $RtnVal->ROWS[$RtnCnt] = $tarr;
                    $RtnCnt++;
                }


            }

		}

		//결과 JSON 화면 출력
		$RtnVal->GRP_TYPE = "GRID";
	    $RtnVal->SEQ_COLID = ($map["SEQYN"] == "Y")?$map["KEYCOLID"]:"";

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}




	function requireGridSave($colord,$xml,$sql){
        global $REQ,$CFG, $PGM_CFG;
        
        //ar_dump($sql["U"]);
        //exit;
        $RtnVal = null;
        $RtnVal->GRP_TYPE = "GRID";
        if(!(
              ( is_array($sql["C"]["REQUIRE"]) && sizeof($sql["C"]["REQUIRE"]) > 0 )
                || ( is_array($sql["U"]["REQUIRE"]) && sizeof($sql["U"]["REQUIRE"]) > 0 )
                || ( is_array($sql["D"]["REQUIRE"]) && sizeof($sql["D"]["REQUIRE"]) > 0 )
            )
        ){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
            return $RtnVal;
        }

        $isRequireResult = true;

        $colord_array = explode(",",$colord);

		$xml_array_last = null;
        alog("requireGrid is_assoc : " . is_assoc($xml) );
        alog("requireGrid count : " . count($xml["row"]) );
        alog("requireGrid sizeof : " . sizeof($xml["row"]) );
		if(is_assoc($xml["row"]) == 1) {
			//alog(" Y " );
			$xml_array_last[0] = $xml["row"];
		}else{
			//alog(" N " );

			$xml_array_last = $xml["row"];
		}
		//var_dump($xml_array_last);


		$RtnCnt = 0;
		//alog("xml sizeof : " . sizeof($xml_array_last));
		for($i=0;$i<sizeof($xml_array_last) && $isRequireResult;$i++){

			$row = $xml_array_last[$i];
			//alog("        i : " . $i);
			//alog("        @attributes : " . $row["@attributes"]["id"]);
			//alog("        userdata : " . $row["userdata"]);

			//현재 그리드 line을 bind 배열에 담기
			$to_row = null;
			$to_coltype = null;
			for($j=0;$j<sizeof($row["cell"]);$j++){
				$col = $row["cell"][$j];
				if(is_array($col)){
					$to_row[trim($colord_array[$j])] = "";
				}else{
                    $to_row[trim($colord_array[$j])] = $col;
				}
			}

            $tArr = array_merge($REQ,$to_row);

			if($row["userdata"] == "inserted"  ){
                //alog("        inserted : " );
                if ( is_array($sql["C"]["REQUIRE"]) && sizeof($sql["C"]["REQUIRE"]) > 0 ){
                    //require 필드 갯수 만큼 루프 돌면서 검사
                    for($k=0;$k<sizeof($sql["C"]["REQUIRE"]);$k++){
                        $requireCol = $sql["C"]["REQUIRE"][$k];
                        if($tArr[$requireCol] == ""){
                            $isRequireResult = false; //필수값이 비여있음
                            $RtnVal->RTN_MSG = $requireCol . " DB insert시 필수 값입니다.";
                            break;
                        }
                    }
                }

			}else if($row["userdata"] == "updated"){
                //alog("        updated : " );

                if( is_array($sql["U"]["REQUIRE"]) && sizeof($sql["U"]["REQUIRE"]) > 0 ){
                    //require 필드 갯수 만큼 루프 돌면서 검사   
                    for($k=0;$k<sizeof($sql["U"]["REQUIRE"]);$k++){
                        $requireCol = $sql["U"]["REQUIRE"][$k];
                        if($tArr[$requireCol] == ""){
                            $isRequireResult = false; //필수값이 비여있음
                            $RtnVal->RTN_MSG = $requireCol . " DB update시 필수 값입니다.";                        
                            break;
                        }
                    }
                }

			}else if($row["userdata"] == "deleted" ){
                //alog("        deleted : " );
                if( is_array($sql["D"]["REQUIRE"]) && sizeof($sql["D"]["REQUIRE"]) > 0  ){
                    //require 필드 갯수 만큼 루프 돌면서 검사
                    for($k=0;$k<sizeof($sql["D"]["REQUIRE"]);$k++){
                        $requireCol = $sql["D"]["REQUIRE"][$k];
                        if($tArr[$requireCol] == ""){
                            $isRequireResult = false; //필수값이 비여있음
                            $RtnVal->RTN_MSG = $requireCol . " DB delete시 필수 값입니다.";                        
                            break;
                        }
                    }
                }
            }else{
                alog("         userdata no match : " . $row["userdata"]);
            }

		}

		//결과 JSON 화면 출력

        if($isRequireResult){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
        }else{
            $RtnVal->RTN_CD = "500";
            $RtnVal->ERR_CD = "333";            
        }

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}



	function requireGridSaveArray($colord,$xml,$sql){
        global $REQ,$CFG, $PGM_CFG;
        
        //ar_dump($sql["U"]);
        //exit;
        $RtnVal = null;
        $RtnVal->GRP_TYPE = "GRID";

        //관련 모든 SQL에 REQUIRE이 하나도 없으면 검사 패스
        $isRequireOverOne = false;
        for($s = 0; $s < sizeof($sql["C"]) ; $s++){
            $tmpSql = $sql["C"][$s];
            if(sizeof($tmpSql["REQUIRE"]) > 0) $isRequireOverOne = true;
        }
        for($s = 0; $s < sizeof($sql["U"]) && $isRequireOverOne == false; $s++){
            $tmpSql = $sql["U"][$s];
            if(sizeof($tmpSql["REQUIRE"]) > 0) $isRequireOverOne = true;
        }
        for($s = 0; $s < sizeof($sql["D"]) && $isRequireOverOne == false ; $s++){
            $tmpSql = $sql["D"][$s];
            if(sizeof($tmpSql["REQUIRE"]) > 0) $isRequireOverOne = true;
        }                
        if(!$isRequireOverOne){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
            return $RtnVal;
        }

        $isRequireResult = true;

        $colord_array = explode(",",$colord);

		$xml_array_last = null;
        alog("requireGrid is_assoc : " . is_assoc($xml) );
        alog("requireGrid count : " . count($xml["row"]) );
        alog("requireGrid sizeof : " . sizeof($xml["row"]) );
		if(is_assoc($xml["row"]) == 1) {
			//alog(" Y " );
			$xml_array_last[0] = $xml["row"];
		}else{
			//alog(" N " );

			$xml_array_last = $xml["row"];
		}
		//var_dump($xml_array_last);


		$RtnCnt = 0;
		//alog("xml sizeof : " . sizeof($xml_array_last));
		for($i=0;$i<sizeof($xml_array_last) && $isRequireResult;$i++){

			$row = $xml_array_last[$i];
			//alog("        i : " . $i);
			//alog("        @attributes : " . $row["@attributes"]["id"]);
			//alog("        userdata : " . $row["userdata"]);

			//현재 그리드 line을 bind 배열에 담기
			$to_row = null;
			$to_coltype = null;
			for($j=0;$j<sizeof($row["cell"]);$j++){
				$col = $row["cell"][$j];
				if(is_array($col)){
					$to_row[trim($colord_array[$j])] = "";
				}else{
                    $to_row[trim($colord_array[$j])] = $col;
				}
			}

            $tArr = array_merge($REQ,$to_row);

			if($row["userdata"] == "inserted"  ){
                //alog("        inserted : " );

                //require 필드 갯수 만큼 루프 돌면서 검사
                for($k=0;$k<sizeof($sql["C"]);$k++){
                    $tmpSql = $sql["C"][$k];

 
                    for($r=0;$r<sizeof($tmpSql["REQUIRE"]);$r++){
                        $requireCol = $tmpSql["REQUIRE"][$r];
                        if($tArr[$requireCol] == ""){
                            $isRequireResult = false; //필수값이 비여있음
                            $RtnVal->RTN_MSG = $requireCol . "는 DB insert시 필수 값입니다.";
                            break;
                        }
                    }
                }

			}else if($row["userdata"] == "updated"){
                //alog("        updated : " );

                //require 필드 갯수 만큼 루프 돌면서 검사
                for($k=0;$k<sizeof($sql["U"]);$k++){
                    $tmpSql = $sql["U"][$k];

                    for($r=0;$r<sizeof($tmpSql["REQUIRE"]);$r++){
                        $requireCol = $tmpSql["REQUIRE"][$r];
                        if($tArr[$requireCol] == ""){
                            $isRequireResult = false; //필수값이 비여있음
                            $RtnVal->RTN_MSG = $requireCol . "는 DB update시 필수 값입니다.";
                            break;
                        }
                    }

                }

			}else if($row["userdata"] == "deleted" ){
                //alog("        deleted : " );

                for($k=0;$k<sizeof($sql["D"]);$k++){
                    $tmpSql = $sql["D"][$k];

                    for($r=0;$r<sizeof($tmpSql["REQUIRE"]);$r++){
                        $requireCol = $tmpSql["REQUIRE"][$r];
                        if($tArr[$requireCol] == ""){
                            $isRequireResult = false; //필수값이 비여있음
                            $RtnVal->RTN_MSG = $requireCol . "는 DB delete시 필수 값입니다.";
                            break;
                        }
                    }
                }

            }else{
                alog("         userdata no match : " . $row["userdata"]);
            }

		}

		//결과 JSON 화면 출력

        if($isRequireResult){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
        }else{
            $RtnVal->RTN_CD = "500";
            $RtnVal->ERR_CD = "333";            
        }

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}




	function requireGridSearch($colord,$xml,$sql){
        global $REQ,$CFG, $PGM_CFG;
        alog("requireGridSearch ");
        //ar_dump($sql["U"]);
        //exit;
        $RtnVal = null;
        $RtnVal->GRP_TYPE = "GRID";
        if(!( is_array($sql["R"]["REQUIRE"]) && sizeof($sql["R"]["REQUIRE"]) > 0 ) ){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
            return $RtnVal;
        }

        $isRequireResult = true;

        //SQL에서 입력값 추출하기
        for($k=0;$k<sizeof($sql["R"]["REQUIRE"]);$k++){
            $requireCol = $sql["R"]["REQUIRE"][$k];
            alog(" $k  " . $requireCol . " = " . $REQ[$requireCol]);
            if($REQ[$requireCol] == ""){
                $isRequireResult = false; //필수값이 비여있음
                $RtnVal->RTN_MSG = $requireCol . " DB조회시 필수 값입니다.";                        
                break;
            }
        }

		//결과 JSON 화면 출력
        if($isRequireResult){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
        }else{
            $RtnVal->RTN_CD = "500";
            $RtnVal->ERR_CD = "333";            
        }

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}


	function requireGridSearchArray($colord,$xml,$sql){
        global $REQ,$CFG, $PGM_CFG;
        alog("requireGridSearchArray ");
        //ar_dump($sql["U"]);
        //exit;
        $RtnVal = null;
        $RtnVal->GRP_TYPE = "GRID";

        $isRequireResult = true;


        //main/sub sql 갯수만큼 루프 돌기
        for($i=0;$i<sizeof($sql);$i++){
            $tmpSql = $sql[$i];

            //SQL에서 입력값 추출하기
            for($k=0;$k<sizeof($tmpSql["REQUIRE"]);$k++){
                $requireCol = $tmpSql["REQUIRE"][$k];
                alog(" $k  " . $requireCol . " = " . $REQ[$requireCol]);
                if($REQ[$requireCol] == ""){
                    $isRequireResult = false; //필수값이 비여있음
                    $RtnVal->RTN_MSG = $requireCol . "는 DB조회시 필수 값입니다.";                        
                    break;
                }
            }            
        }


		//결과 JSON 화면 출력
        if($isRequireResult){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
        }else{
            $RtnVal->RTN_CD = "500";
            $RtnVal->ERR_CD = "333";            
        }

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}



	function makeGridSaveJson($map,&$db){
        global $REQ,$CFG, $PGM_CFG;
        
        //alog("^^^ COLORD : " . $map["COLORD"]);
        $colord_array = explode(",",$map["COLORD"]);
        //$colcrypt_array = explode(",",$map["COLCRYPT"]);        
        $colcrypt_array = $map["COLCRYPT"];
        //alog("^^^ colord_array count : " . count($colord_array));

		$xml_array_last = null;
        alog("makeGridSaveJson is_assoc : " . is_assoc($map["XML"]) );
        alog("makeGridSaveJson count : " . count($map["XML"]["row"]) );
        alog("makeGridSaveJson sizeof : " . sizeof($map["XML"]["row"]) );
		if(is_assoc($map["XML"]["row"]) == 1) {
			alog(" Y " );
			$xml_array_last[0] = $map["XML"]["row"];
		}else{
			alog(" N " );

			$xml_array_last = $map["XML"]["row"];
		}
		//var_dump($xml_array_last);

		$RtnVal = null;
		$RtnCnt = 0;
		alog("xml sizeof : " . sizeof($xml_array_last));
		for($i=0;$i<sizeof($xml_array_last);$i++){

			$row = $xml_array_last[$i];
			alog("        i : " . $i);
			alog("        @attributes : " . $row["@attributes"]["id"]);
			alog("        userdata : " . $row["userdata"]);

			//현재 그리드 line을 bind 배열에 담기
			$to_row = null;
			$to_coltype = null;
			$sql = null;
			for($j=0;$j<sizeof($row["cell"]);$j++){
				$col = $row["cell"][$j];
				if(is_array($col)){
					$to_row[trim($colord_array[$j])] = "";
				}else{
                    //암호화 컬럼에 존재 하는지 확인
                    if($colcrypt_array[trim($colord_array[$j])] == "CRYPT" ){
                        //양방향 암호화
                        alog("  crypt 전 col/key: [" . $col . "]/" . $CFG["CFG_SEC_KEY"]);
                        alog("  crypt 후 : [" .  aes_encrypt($col,$CFG["CFG_SEC_KEY"]) . "]");                        
                        $to_row[trim($colord_array[$j])] = aes_encrypt($col,$CFG["CFG_SEC_KEY"]);
                    }else if($colcrypt_array[trim($colord_array[$j])] == "HASH" ){
                        //일방향 암호화
                        alog("  hash 전 col/salt: [" . $col . "]/" . $CFG["CFG_SEC_SALT"]);
                        alog("  hash 후 : [" .  pwd_hash($col,$CFG["CFG_SEC_SALT"]) . "]");                        
                        $to_row[trim($colord_array[$j])] = pwd_hash($col,$CFG["CFG_SEC_SALT"]);
                    }else{
                        //평문
                        //alog("  [평문] " . trim($colord_array[$j]) . " = " . $col);
                        $to_row[trim($colord_array[$j])] = $col;
                    }
				}
			}

			if($row["userdata"] == "inserted"){
				$to_coltype = $map["SQL"]["C"]["BINDTYPE"];
                //LogMaster::log("        to_coltype : " . $to_coltype);
                $svrid = $map["SQL"]["C"]["SVRID"];
				$sql = $map["SQL"]["C"]["SQLTXT"];
				alog("        inserted : " );
			}else if($row["userdata"] == "updated"){
                $to_coltype = $map["SQL"]["U"]["BINDTYPE"];
                $svrid = $map["SQL"]["U"]["SVRID"];
				$sql = $map["SQL"]["U"]["SQLTXT"];
				alog("        updated : " );
			}else if($row["userdata"] == "deleted"){
                $to_coltype = $map["SQL"]["D"]["BINDTYPE"];
                $svrid = $map["SQL"]["D"]["SVRID"];
				$sql = $map["SQL"]["D"]["SQLTXT"];
				alog("        deleted : " );
            }else{
                alog("         userdata no match : " . $row["userdata"]);
            }


			if($sql != null ){
				//LogMaster::log("        to_coltype : " . $to_coltype);
			
				if( getParamCnt($sql) != strlen(str_replace(" ","",$to_coltype)) )JsonMsg("500","190","(makeGridSaveJson) sql파라미터와 파라미터타입수가 불일치.");

                alog("svrid : " . $svrid);
                //alog("  REQ.URL : " . $REQ["URL"]);             
                //alog("  to_row.URL : " . $to_row["URL"]);             
                 

                $tArr = array_merge($REQ,$to_row);
                //alog("  array_merge() tArr.URL : " . $tArr["URL"]);

				$stmt = makeStmt($db[$svrid],$sql, $to_coltype, array_merge($REQ,$to_row));
			   
				if(!$stmt) JsonMsg("500","200","(makeGridSaveJson) stmt 생성 실패 " . $db->errno . " -> " . $db->error);
			   
				if(!$stmt->execute())JsonMsg("500","210","(makeGridSaveJson) stmt 실행 실패 " . $stmt->error);

				//echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
				$to_affected_rows = $db[$svrid]->affected_rows;
            
                //[로그 저장용]
                $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;                
			
				$to_row["COLID"] = "";
				if($row["userdata"] == "inserted"){
					if($map["SEQYN"] == "Y"){
						alog("SEQYN Y : " . $db[$svrid]->insert_id);
						$to_row["COLID"]=$db[$svrid]->insert_id; //insert문인 경우 insert id받기
					}else{
						alog("SEQYN N : " . $to_row[$map["KEYCOLID"]]);
						$to_row["COLID"]=$to_row[$map["KEYCOLID"]]; //사용자 입력 key컬럼을 rowid 로
					}
				}

				$stmt->close();

				$tarr = array("OLD_ID"=>$row["@attributes"]["id"],"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>$row["userdata"],"AFFECTED_ROWS"=>$to_affected_rows);

				$RtnVal->ROWS[$RtnCnt] = $tarr;
				$RtnCnt++;

			}else{
                JsonMsg("500","220","(makeGridSaveJson) 데이터 처리 요청 " . $row["userdata"] . "에  해당하는 sql문이 없습니다.");
            }


		}

		//결과 JSON 화면 출력
		$RtnVal->GRP_TYPE = "GRID";
	    $RtnVal->SEQ_COLID = ($map["SEQYN"] == "Y")?$map["KEYCOLID"]:"";

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}




	function makeGridSaveJsonArray($map,&$db){
        global $REQ,$CFG, $PGM_CFG;
        
        //alog("^^^ COLORD : " . $map["COLORD"]);
        $colord_array = explode(",",$map["COLORD"]);
        //$colcrypt_array = explode(",",$map["COLCRYPT"]);        
        $colcrypt_array = $map["COLCRYPT"];
        //alog("^^^ colord_array count : " . count($colord_array));

		$xml_array_last = null;
        alog("makeGridSaveJsonArray is_assoc : " . is_assoc($map["XML"]) );
        alog("makeGridSaveJsonArray count : " . count($map["XML"]["row"]) );
        alog("makeGridSaveJsonArray sizeof : " . sizeof($map["XML"]["row"]) );
		if(is_assoc($map["XML"]["row"]) == 1) {
			alog(" Y " );
			$xml_array_last[0] = $map["XML"]["row"];
		}else{
			alog(" N " );

			$xml_array_last = $map["XML"]["row"];
		}
		//var_dump($xml_array_last);

		$RtnVal = null;
		$RtnCnt = 0;
		alog("xml sizeof : " . sizeof($xml_array_last));
		for($i=0;$i<sizeof($xml_array_last);$i++){

			$row = $xml_array_last[$i];
			alog("        i : " . $i);
			alog("        @attributes : " . $row["@attributes"]["id"]);
			alog("        userdata : " . $row["userdata"]);

			//현재 그리드 line을 bind 배열에 담기
			$to_row = null;
			$to_coltype = null;
			$sql = null;
			for($j=0;$j<sizeof($row["cell"]);$j++){
				$col = $row["cell"][$j];
				if(is_array($col)){
					$to_row[trim($colord_array[$j])] = "";
				}else{
                    //암호화 컬럼에 존재 하는지 확인
                    if($colcrypt_array[trim($colord_array[$j])] == "CRYPT" ){
                        //양방향 암호화
                        alog("  crypt 전 col/key: [" . $col . "]/" . $CFG["CFG_SEC_KEY"]);
                        alog("  crypt 후 : [" .  aes_encrypt($col,$CFG["CFG_SEC_KEY"]) . "]");                        
                        $to_row[trim($colord_array[$j])] = aes_encrypt($col,$CFG["CFG_SEC_KEY"]);
                    }else if($colcrypt_array[trim($colord_array[$j])] == "HASH" ){
                        //일방향 암호화
                        alog("  hash 전 col/salt: [" . $col . "]/" . $CFG["CFG_SEC_SALT"]);
                        alog("  hash 후 : [" .  pwd_hash($col,$CFG["CFG_SEC_SALT"]) . "]");                        
                        $to_row[trim($colord_array[$j])] = pwd_hash($col,$CFG["CFG_SEC_SALT"]);
                    }else{
                        //평문
                        //alog("  [평문] " . trim($colord_array[$j]) . " = " . $col);
                        $to_row[trim($colord_array[$j])] = $col;
                    }
				}
			}

            //SQL 갯수만큼 루프
            $mapLoop = null;            
            switch($row["userdata"]){
                case "inserted":
                    alog("        inserted : " );                
                    $mapLoop = $map["SQL"]["C"];
                    break;
                case "updated":
                    alog("        updated : " );                      
                    $mapLoop = $map["SQL"]["U"];
                    break;
                case "deleted":
                    alog("        deleted : " );                      
                    $mapLoop = $map["SQL"]["D"];
                    break;    
                default:
                    alog("         userdata no match : " . $row["userdata"]);
                    continue;                
                break;
            }

            for($k=0;$k<sizeof($mapLoop);$k++){
                $tmpSql = $mapLoop[$k];
                
                $to_coltype = $tmpSql["BINDTYPE"];
                //LogMaster::log("        to_coltype : " . $to_coltype);
                $svrid = $tmpSql["SVRID"];
                $sql = $tmpSql["SQLTXT"];
                

                if( getParamCnt($sql) != strlen(str_replace(" ","",$to_coltype)) )JsonMsg("500","190","(makeGridSaveJsonArray) " . $tmpSql["SQLID"] . "  sql파라미터와 파라미터타입수가 불일치.");

                alog("svrid : " . $svrid);
                //alog("  REQ.URL : " . $REQ["URL"]);             
                //alog("  to_row.URL : " . $to_row["URL"]);             
                    

                $tArr = array_merge($REQ,$to_row);
                //alog("  array_merge() tArr.URL : " . $tArr["URL"]);

                $stmt = makeStmt($db[$svrid], $sql, $to_coltype, array_merge($REQ,$to_row));
                
                if(!$stmt) JsonMsg("500","200","(makeGridSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt 생성 실패 " . $db->errno . " -> " . $db->error);
                
                if(!$stmt->execute())JsonMsg("500","210","(makeGridSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt 실행 실패 " . $stmt->error);

                //SUB 쿼리는 리턴정보에 넣지 않음.
                alog("  PARENT_FNCTYPE = " . $tmpSql["PARENT_FNCTYPE"]);                    
                if($tmpSql["PARENT_FNCTYPE"] == ""){
                    //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
                    $to_affected_rows = $db[$svrid]->affected_rows;
                
                    //[로그 저장용]
                    $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;                
                
                    $to_row["COLID"] = "";
                    if($row["userdata"] == "inserted"){
                        if($map["SEQYN"] == "Y"){
                            alog("SEQYN Y : " . $db[$svrid]->insert_id);
                            $to_row["COLID"]=$db[$svrid]->insert_id; //insert문인 경우 insert id받기
                        }else{
                            alog("SEQYN N : " . $to_row[$map["KEYCOLID"]]);
                            $to_row["COLID"]=$to_row[$map["KEYCOLID"]]; //사용자 입력 key컬럼을 rowid 로
                        }
                    }

                    $tarr = array("OLD_ID"=>$row["@attributes"]["id"],"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>$row["userdata"],"AFFECTED_ROWS"=>$to_affected_rows);
    
                    $RtnVal->ROWS[$RtnCnt] = $tarr;
                    $RtnCnt++;
                }
                $stmt->close();
    
 
            }
			


		}

		//결과 JSON 화면 출력
		$RtnVal->GRP_TYPE = "GRID";
	    $RtnVal->SEQ_COLID = ($map["SEQYN"] == "Y")?$map["KEYCOLID"]:"";

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}




    function makeParamEnc($tSql, $tReq, $colcrypt_array){
        global $CFG;
        //alog("makeParamEnc()........................................................start");
        $k = 0;
        $to_sql = $tSql;
        $RtnVal = null;

        //파라미터 분해
        while(preg_match("/(#{)([\.a-zA-Z0-9_-]+)(})/",$to_sql,$mat)){

            $matStr = $mat[1].$mat[2].$mat[3];  // #{AA-BB_CC}

            $fullParam = $mat[2];
            if(strpos($mat[2],"-")>0){
                $colid = explode("-",$mat[2])[1];
            }else{
                $colid = $mat[2];
            }

            //colid가 암호화 대상이면 암호화 처리
            if( $colcrypt_array[trim($colid)] == "CRYPT" ){
                //양방향 암호화                   
                alog(" CRYPT");
                $RtnVal[$fullParam] = aes_encrypt($tReq[$fullParam],$CFG["CFG_SEC_KEY"]);
            }else if( $colcrypt_array[trim($colid)] == "HASH" ){
                //일방향 암호화
                alog(" HASH");                
                $RtnVal[$fullParam] = pwd_hash($tReq[$fullParam],$CFG["CFG_SEC_SALT"]);
            }else{
                //평문
                $RtnVal[$fullParam] = $tReq[$fullParam];
            }     

            alog($fullParam . "/" . trim($colid) . "/" . $colcrypt_array[trim($colid)] . " = " . $tReq[$fullParam] . " -> " . $RtnVal[$fullParam] );

            $to_sql = str_replace_once($matStr,"?",$to_sql);

            $k++;
        }

        //최종
        //alog("prepare sql : " . $to_sql);

        return $RtnVal;
    }


    function makeParamDec($tKey, $tValue, $colcrypt_array){
        global $CFG;
        //alog("makeParamDec()........................................................start");

        $RtnVal = null;

        //colid가 암호화 대상이면 암호화 처리
        if( $colcrypt_array[trim($tKey)] == "CRYPT" ){
            //양방향 암호화                   
            $RtnVal = aes_decrypt($tValue,$CFG["CFG_SEC_KEY"]);
        }else if( $colcrypt_array[trim($tKey)] == "HASH" ){
            //일방향 암호화
            $RtnVal = $tValue;
        }else{
            //평문
            $RtnVal = $tValue;
        }     

        return $RtnVal;
    }

    /*
    ###################################################################
    ##  FormView
    ###################################################################
    */
	function requireFormviewSearch($sql){
        global $REQ,$CFG, $PGM_CFG;
        alog("requireFormviewSearch ");
        //ar_dump($sql["U"]);
        //exit;
        $RtnVal = null;
        $RtnVal->GRP_TYPE = "FORMVIEW";
        if(!( is_array($sql["R"]["REQUIRE"]) && sizeof($sql["R"]["REQUIRE"]) > 0 ) ){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
            return $RtnVal;
        }

        $isRequireResult = true;

        //SQL에서 입력값 추출하기
        for($k=0;$k<sizeof($sql["R"]["REQUIRE"]);$k++){
            $requireCol = $sql["R"]["REQUIRE"][$k];
            alog(" $k  " . $requireCol . " = " . $REQ[$requireCol]);
            if($REQ[$requireCol] == ""){
                $isRequireResult = false; //필수값이 비여있음
                $RtnVal->RTN_MSG = $requireCol . " DB조회시 필수 값입니다.";                        
                break;
            }
        }

		//결과 JSON 화면 출력
        if($isRequireResult){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
        }else{
            $RtnVal->RTN_CD = "500";
            $RtnVal->ERR_CD = "333";            
        }

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}


	function requireFormviewSearchArray($sql){
        global $REQ,$CFG, $PGM_CFG;
        alog("requireFormviewSearchArray...............................start");
        //ar_dump($sql["U"]);
        //exit;
        $RtnVal = null;
        $RtnVal->GRP_TYPE = "FORMVIEW";

        $isRequireResult = true;

        //main/sub sql 갯수만큼 루프 돌기
        alog("################## sql sizeof = " . sizeof($sql));
        for($i=0;$i<sizeof($sql);$i++){
            $tmpSql = $sql[$i];

            //SQL에서 입력값 추출하기
            for($k=0;$k<sizeof($tmpSql["REQUIRE"]);$k++){
                $requireCol = $tmpSql["REQUIRE"][$k];
                alog(" $k  " . $requireCol . " = " . $REQ[$requireCol]);
                if($REQ[$requireCol] == ""){
                    $isRequireResult = false; //필수값이 비여있음
                    $RtnVal->RTN_MSG = $requireCol . "는 DB조회시 필수 값입니다.";                       
                    alog("##################" . $RtnVal->RTN_MSG);
                    break;
                }
            }            
        }

		//결과 JSON 화면 출력
        if($isRequireResult){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
        }else{
            $RtnVal->RTN_CD = "500";
            $RtnVal->ERR_CD = "333";            
        }

        //$RtnVal = json_encode($RtnVal);
        
        alog("requireFormviewSearchArray...............................end");
		return $RtnVal;

	}


	function requireFormviewSave($sql,$fnctype){
        global $REQ,$CFG, $PGM_CFG;
        alog("requireFormviewSave ");
        //ar_dump($sql["U"]);
        //exit;
        $RtnVal = null;
        $RtnVal->GRP_TYPE = "FORMVIEW";
        if($fnctype == "C" && (!is_array($sql["C"]["REQUIRE"]) || sizeof($sql["C"]["REQUIRE"]) < 1) ){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
            return $RtnVal;
        }
        if($fnctype == "U" && (!is_array($sql["U"]["REQUIRE"]) || sizeof($sql["U"]["REQUIRE"]) < 1) ){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
            return $RtnVal;
        }
        if($fnctype == "D" && (!is_array($sql["D"]["REQUIRE"]) || sizeof($sql["D"]["REQUIRE"]) < 1) ){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
            return $RtnVal;
        }

        $isRequireResult = true;

        $require = null;
		switch ($fnctype){
            case "C" :
                $require = $sql["C"]["REQUIRE"];
				break;

            case "U" :
                $require = $sql["U"]["REQUIRE"];
				break;

            case "D" :
                $require = $sql["D"]["REQUIRE"];
				break;

			default:
				return "FNCTYPE 없음(".$map["FNCTYPE"].")";
		}

        //SQL에서 입력값 추출하기
        for($k=0;$k<sizeof($require);$k++){
            $requireCol = $require[$k];
            alog(" $k  " . $requireCol . " = " . $REQ[$requireCol]);
            if($REQ[$requireCol] == ""){
                $isRequireResult = false; //필수값이 비여있음
                $RtnVal->RTN_MSG = $requireCol . " DB저장시 필수 값입니다.";                        
                break;
            }
        }

		//결과 JSON 화면 출력
        if($isRequireResult){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
        }else{
            $RtnVal->RTN_CD = "500";
            $RtnVal->ERR_CD = "333";            
        }

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

    }



	function requireFormviewSaveArray($sql,$fnctype){
        global $REQ,$CFG, $PGM_CFG;
        alog("requireFormviewSaveArray ..................................start");
        //ar_dump($sql["U"]);
        //exit;
        $RtnVal = null;
        $RtnVal->GRP_TYPE = "FORMVIEW";



        //관련 모든 SQL에 REQUIRE이 하나도 없으면 검사 패스
        $isRequireOverOne = false;
        for($s = 0; $s < sizeof($sql) ; $s++){
            $tmpSql = $sql[$s];
            if(sizeof($tmpSql["REQUIRE"]) > 0) $isRequireOverOne = true;
        }
        if(!$isRequireOverOne){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
            return $RtnVal;
        }


        $isRequireResult = true;

        $require = null;

        //main/sub sql 갯수만큼 루프 돌기
        for($i=0;$i<sizeof($sql);$i++){
            $tmpSql = $sql[$i];

            for($k=0;$k<sizeof($tmpSql["REQUIRE"]);$k++){
                $requireCol = $tmpSql["REQUIRE"][$k];
                alog(" $k  " . $requireCol . " = " . $REQ[$requireCol]);
                if($REQ[$requireCol] == ""){
                    $isRequireResult = false; //필수값이 비여있음
                    $RtnVal->RTN_MSG = $requireCol . "는 DB조회시 필수 값입니다.";                        
                    break;
                }
            }     
      
        }

		//결과 JSON 화면 출력
        if($isRequireResult){
            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
        }else{
            $RtnVal->RTN_CD = "500";
            $RtnVal->ERR_CD = "333";            
        }

        //$RtnVal = json_encode($RtnVal);
        alog("requireFormviewSaveArray ..................................end");        
		return $RtnVal;

    }    
    
	function makeFormviewSearchJson($map,&$db){
        global $REQ, $CFG, $PGM_CFG;
        
        //암호화컬럼
        $colcrypt_array = $map["COLCRYPT"];   

        //폼 입력값에 암호화 컬럼 있는지 검사해서 암호화 처리
        $tParamEnc = makeParamEnc($map["SQL"]["R"]["SQLTXT"], $REQ, $colcrypt_array);


		$stmt = makeStmt($db[$map["SQL"]["R"]["SVRID"]],$map["SQL"]["R"]["SQLTXT"], $map["SQL"]["R"]["BINDTYPE"], $tParamEnc);
		if(!$stmt)   JsonMsg("500","300","stmt 생성 실패" . $db->errno . " -> " . $db->error);

		//alog("make_detail_read_json-------------------------------start");
		if(!$stmt->execute())JsonMsg("500","310","stmt 실행 실패" . $db->errno . " -> " . $db->error);

        $stmt->store_result();
        //$colcrypt_array = explode(",",$map["COLCRYPT"]);   

        $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $stmt->num_rows;

		$cols = null; //결과 
		$meta = $stmt->result_metadata(); 
		while ($field = $meta->fetch_field()) 
		{ 
			$params[] = &$row[$field->name]; 
		} 
		call_user_func_array(array($stmt, 'bind_result'), $params); 


		//alog("	fetch out");
		if($stmt->fetch()) {
			//alog("	fetch in");
			foreach( $row as $key=>$value )
			{
                //alog("	fetch foreach : $key = $value");
                $RtnVal->RTN_DATA[$key] = makeParamDec($key, $value, $colcrypt_array);		
			}
		} 

		//$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
		//결과 JSON 화면 출력
		$RtnVal->RTN_CD = "200";
		$RtnVal->ERR_CD = "200";
		//alog("	result json : " . json_encode($RtnVal));
		return  $RtnVal;
		//var_dump($RtnVal);
	}


	function makeFormviewSearchJsonArray($map,&$db){
        global $REQ, $CFG, $PGM_CFG;
        alog("makeFormviewSearchJsonArray...........................start");

        //암호화컬럼
        $colcrypt_array = $map["COLCRYPT"];   


        //main/sub sql 갯수 만큼 루프돌면서 처리하기
        alog("  sql sizeof : " . sizeof($map["SQL"]));
        for($s=0;$s<sizeof($map["SQL"]);$s++){
             $tmpSql = $map["SQL"][$s];
 
             //main/sub 구분에 따라 처리
             alog("  sql[" . $s . "] PARENT_FNCTYPE = ". $tmpSql["PARENT_FNCTYPE"]);            
             alog("      SVRID = " . $tmpSql["SVRID"]);
             alog("      SQLTXT = " . $tmpSql["SQLTXT"]);
             alog("      BINDTYPE = " . $tmpSql["BINDTYPE"]);

            //폼 입력값에 암호화 컬럼 있는지 검사해서 암호화 처리
            $tParamEnc = makeParamEnc($tmpSql["SQLTXT"], $REQ, $colcrypt_array);

            $stmt = makeStmt($db[$tmpSql["SVRID"]],$tmpSql["SQLTXT"], $tmpSql["BINDTYPE"], $tParamEnc);
            if(!$stmt)  JsonMsg("500","300","(makeFormviewSearchJsonArray) " . $tmpSql["SQLID"] . " stmt 생성 실패" . $db->errno . " -> " . $db->error);

            //alog("make_detail_read_json-------------------------------start");
            if(!$stmt->execute())JsonMsg("500","310","(makeFormviewSearchJsonArray) " . $tmpSql["SQLID"] . " stmt 실행 실패" . $db->errno . " -> " . $db->error);

            //main인 경우만
            if( $tmpSql["PARENT_FNCTYPE"] == ""){

                $stmt->store_result();
                //$colcrypt_array = explode(",",$map["COLCRYPT"]);   

                $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $stmt->num_rows;

                $cols = null; //결과 
                $meta = $stmt->result_metadata(); 
                while ($field = $meta->fetch_field()) 
                { 
                    $params[] = &$row[$field->name]; 
                } 
                call_user_func_array(array($stmt, 'bind_result'), $params); 


                //alog("	fetch out");
                if($stmt->fetch()) {
                    //alog("	fetch in");
                    foreach( $row as $key=>$value )
                    {
                        //alog("	fetch foreach : $key = $value");
                        $RtnVal->RTN_DATA[$key] = makeParamDec($key, $value, $colcrypt_array);		
                    }
                } 
            }
            $stmt->close();
        }

		//$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
		//결과 JSON 화면 출력
		$RtnVal->RTN_CD = "200";
		$RtnVal->ERR_CD = "200";
        //alog("	result json : " . json_encode($RtnVal));
        
        alog("makeFormviewSearchJsonArray...........................end");        
		return  $RtnVal;
		//var_dump($RtnVal);
	}



	function makeFormviewSaveJson($map,&$db){
		global $REQ, $PGM_CFG;

		alog("makeFormviewSaveJson----------------------------------------start");
		alog("	FNCTYPE : " . $map["FNCTYPE"]);

        $svrid = "";
		$sqltxt = "";
		$bindtype = "";
		switch ($map["FNCTYPE"]){
            case "C" :
                    $svrid		= $map["SQL"]["C"]["SVRID"];
					$sqltxt		= $map["SQL"]["C"]["SQLTXT"];
					$bindtype	= $map["SQL"]["C"]["BINDTYPE"];
				break;

            case "U" :
                    $svrid		= $map["SQL"]["U"]["SVRID"];            
					$sqltxt		= $map["SQL"]["U"]["SQLTXT"];
					$bindtype	= $map["SQL"]["U"]["BINDTYPE"];
				break;

            case "D" :
                    $svrid		= $map["SQL"]["D"]["SVRID"];            
					$sqltxt		= $map["SQL"]["D"]["SQLTXT"];
					$bindtype	= $map["SQL"]["D"]["BINDTYPE"];
				break;

			default:
				return "FNCTYPE 없음(".$map["FNCTYPE"].")";
		}

        alog("sql ---------------------------\n" . $sqltxt);
        alog("bindtype ---------------------------\n" . $bindtype);
        

        //폼 입력값에 암호화 컬럼 있는지 검사해서 암호화 처리
        $colcrypt_array = $map["COLCRYPT"];           
        $tParamEnc = makeParamEnc($sqltxt, $REQ, $colcrypt_array);

		$stmt = makeStmt($db[$svrid], $sqltxt, $bindtype, $tParamEnc);
		if(!$stmt)  JsonMsg("500","400","stmt 생성 실패" . $db->errno . " -> " . $db->error);
		
		if(!$stmt->execute())JsonMsg("500","410","stmt 실행 실패" . $stmt->errno . " -> " . $stmt->error);
		
        //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야

        $to_affected_rows = $db->affected_rows;
        alog("  to_affected_rows = ". $to_affected_rows);

        $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;

        if($map["FNCTYPE"] == "C" && $map["SEQYN"] == "Y"){
            alog("SEQYN Y : " . $db->insert_id);
            $RtnVal->COLID = $db->insert_id;//insert문인 경우 insert id받기
        }

		$stmt->close();

		//$tarr = array("OLD_ID"=>$row["@attributes"]["id"],"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>$row["userdata"],"AFFECTED_ROWS"=>$to_affected_rows);

		$RtnVal->RTN_DATA = $to_affected_rows;

		//결과 JSON 화면 출력
		//$RtnVal["RTN_CD"] = "200";
		//$RtnVal["ERR_CD"] = "200";

		$RtnVal->GRP_TYPE = "FORMVIEW";
	    $RtnVal->SEQ_COLID = ($map["SEQYN"] == "Y")?$map["KEYCOLID"]:"";

		//$RtnVal = json_encode($RtnVal);

		return $RtnVal;
    }
    

	function makeFormviewSaveJsonArray($map,&$db){
		global $REQ, $PGM_CFG;

		alog("makeFormviewSaveJsonArray----------------------------------------start");
		alog("	FNCTYPE : " . $map["FNCTYPE"]);


        
        for($k=0;$k<sizeof($map["SQL"]);$k++){
            $tmpSql = $map["SQL"][$k];

            //alog("sql ---------------------------\n" . $sqltxt);
            //alog("bindtype ---------------------------\n" . $bindtype);
        
            //폼 입력값에 암호화 컬럼 있는지 검사해서 암호화 처리
            $colcrypt_array = $map["COLCRYPT"];           
            $tParamEnc = makeParamEnc($tmpSql["SQLTXT"], $REQ, $colcrypt_array);
    
            $stmt = makeStmt($db[$tmpSql["SVRID"]], $tmpSql["SQLTXT"], $tmpSql["BINDTYPE"], $tParamEnc);
            if(!$stmt)  JsonMsg("500","400","(makeFormviewSaveJsonArray)" . $tmpSql["SQLID"] . " stmt 생성 실패" . $db->errno . " -> " . $db->error);
            
            if(!$stmt->execute())JsonMsg("500","410","(makeFormviewSaveJsonArray)" . $tmpSql["SQLID"] . " stmt 실행 실패" . $stmt->errno . " -> " . $stmt->error);
            
            //main인 경우만
            if( $tmpSql["PARENT_FNCTYPE"] == ""){

                //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
        
                $to_affected_rows = $db->affected_rows;
                alog("  to_affected_rows = ". $to_affected_rows);
        
                $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;
        
                if($map["FNCTYPE"] == "C" && $map["SEQYN"] == "Y"){
                    alog("SEQYN Y : " . $db->insert_id);
                    $RtnVal->COLID = $db->insert_id;//insert문인 경우 insert id받기
                }

                $RtnVal->RTN_DATA = $to_affected_rows;      
                $RtnVal->GRP_TYPE = "FORMVIEW";
                $RtnVal->SEQ_COLID = ($map["SEQYN"] == "Y")?$map["KEYCOLID"]:"";
            }
            $stmt->close();
    
            //$tarr = array("OLD_ID"=>$row["@attributes"]["id"],"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>$row["userdata"],"AFFECTED_ROWS"=>$to_affected_rows);
    

    
            //결과 JSON 화면 출력
            //$RtnVal["RTN_CD"] = "200";
            //$RtnVal["ERR_CD"] = "200";
        }
		

		//$RtnVal = json_encode($RtnVal);
		alog("makeFormviewSaveJsonArray----------------------------------------end");
		return $RtnVal;
    }


    function getSqlSelect2Array($tSql){
        global $CFG;
        include_once($CFG["CFG_LIBS_SQL_PARSER"]);
        $RtnVal = array();

        $parser = new PHPSQLParser($tSql);

        //alog("        SELECT절 sizeof : " . sizeof($parser->parsed["SELECT"]) );
        //alog("        WHERE절 sizeof : " . sizeof($parser->parsed["WHERE"]) );

        //SELECT절이 있을 경우에만
        for($s=0;$s<sizeof($parser->parsed["SELECT"]); $s++){
            //alog("  s : " . $s);
            //alog("      alias : " . $parser->parsed["SELECT"][$s]["alias"]);
            //alog("      alias.name : " . $parser->parsed["SELECT"][$s]["alias"]["name"]);
            //alog("      expr_type : " . $parser->parsed["SELECT"][$s]["expr_type"]);            
            //alog("      base_expr before : " . $parser->parsed["SELECT"][$s]["base_expr"]);

            // A.COLID를 COLID로 변경
            $base_expr = $parser->parsed["SELECT"][$s]["base_expr"];
            $base_expr = strpos($base_expr,".")>0?explode(".",$base_expr)[1]:$base_expr;

            //alog("      base_expr after : " . $base_expr);
            $tColid = is_array($parser->parsed["SELECT"][$s]["alias"])?$parser->parsed["SELECT"][$s]["alias"]["name"]:$base_expr;
            array_push($RtnVal,$tColid);
        }

        return $RtnVal;
    }
?>