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

//db연결 정보로 db 종류 알아내기
function getDbType($db){
    $RtnVal = "";

    if(method_exists($db,'getAttribute')){
        $info = $db->getAttribute(constant("PDO::ATTR_SERVER_INFO"));
        $ver = $db->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
    }else{
        $info = $db->server_info;
        $ver = $db->server_version;
    }

    if(preg_match("/mariadb/i",$info.$ver,$mat)){
        $RtnVal = "mariadb";
    }else if(preg_match("/mariadb/i",$info.$ver,$mat)){
        $RtnVal = "postgresql";
    }else{
        $RtnVal = "mysql";
    }
    return $RtnVal;
}


//mysqli bind_param call_user_func_array 용
function refValues($arr){
    $refs = array();
    foreach($arr as $key => $value)
        $refs[$key] = &$arr[$key];
    return $refs;
} 


function getRowCount(&$tstmt){
    $rowCount = null;
    if($tstmt){
        if(property_exists($tstmt,"num_rows")) {
            //mysqli
            echo "getRowCount() - mysqli";
            //$tstmt->store_result(); //이거 해줘야 num_rowㄴ
            $rowCount = $tstmt->num_rows;
            
        }else if(function_exists($tstmt->rowCount)){
            //pdo_mysql
            echo "getRowCount() - pdo_mysql";            
            //echo "store_result 함수없음.";
            $rowCount = $tstmt->rowCount(); //PDO
            
        }else{
            echo "getRowCount() - stmt funtion null";              
            $rowCount = -999;
        }
    }else{
        $rowCount = -888;
    }

    return $rowCount;

}
function closeDb(&$tdb){
    if($tdb && function_exists($tdb->close)){
        $tdb->close();
    }
    $tdb = null;
}
function closeStmt(&$tstmt){
    if($tstmt){
        if(function_exists($tstmt->close)){
            $tstmt->close();
        }else if(function_exists($tstmt->closeCursor)){
            $tstmt->closeCursor();
        }
    }
    $tstmt = null;
}

function pdoDebug($raw_sql, $parameters)
{
    $keys = array();
    $values = array();

    /*
        * Get longest keys first, sot the regex replacement doesn't
        * cut markers (ex : replace ":username" with "'joe'name"
        * if we have a param name :user )
        */
    $isNamedMarkers = false;
    if (count($parameters) && is_string(key($parameters))) {
        uksort($parameters, function($k1, $k2) {
            return strlen($k2) - strlen($k1);
        });
        $isNamedMarkers = true;
    }
    foreach ($parameters as $key => $value) {

        // check if named parameters (':param') or anonymous parameters ('?') are used
        if (is_string($key)) {
            $keys[] = '/:'.ltrim($key, ':').'/';
        } else {
            $keys[] = '/[?]/';
        }

        // bring parameter into human-readable format
        if (is_string($value)) {
            $values[] = "'" . addslashes($value) . "'";
        } elseif(is_int($value)) {
            $values[] = strval($value);
        } elseif (is_float($value)) {
            $values[] = strval($value);
        } elseif (is_array($value)) {
            $values[] = implode(',', $value);
        } elseif (is_null($value)) {
            $values[] = 'NULL';
        }
    }
    if ($isNamedMarkers) {
        return preg_replace($keys, $values, $raw_sql);
    } else {
        return preg_replace($keys, $values, $raw_sql, 1, $count);
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
            //alog("CFG_SEC_KEY = " . $CFG["CFG_SEC_KEY"]);
            //alog("  DBUSRPW : " . aes_decrypt($arr[0]["DBUSRPW"],$CFG["CFG_SEC_KEY"]));
        
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

function getDbConn($tOBJ_SERVER){
    global $CFG;
    alog("getDbConn().................................start:" . $tOBJ_SERVER["DRIVER"]);


    if($tOBJ_SERVER["PORT"] == "")$tOBJ_SERVER["PORT"] = "3306";
    if($tOBJ_SERVER["DRIVER"] == "")$tOBJ_SERVER["DRIVER"] = "MYSQLI";
    
    if($tOBJ_SERVER["DRIVER"] == "MYSQLI"){
        $db = mysqli_init();
        if (!$db) {
            alog("getDbConn() mysqli_init failed");
            exit();
        }
        if (!$db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1)) {
            alog("getDbConn() Setting MYSQLI_OPT_CONNECT_TIMEOUT failed");
        }        
        if(
            !$db->real_connect(
                $tOBJ_SERVER["HOST"]
                , $tOBJ_SERVER["ID"]
                //, aes_decrypt($tOBJ_SERVER["PW"],$CFG["CFG_SEC_KEY"]) //비밀번호 복호화
                , $tOBJ_SERVER["PW"]
                , $tOBJ_SERVER["DBNM"]
                , $tOBJ_SERVER["PORT"])
        ){
            alog("getDbConn() MYSQL_DRIVER="    . $tOBJ_SERVER["DRIVER"] );
            alog("getDbConn() MYSQL_HOST="    . $tOBJ_SERVER["HOST"] );
            alog("getDbConn() MYSQL_ID="      . $tOBJ_SERVER["ID"] );
            alog("getDbConn() MYSQL_PW="      . $tOBJ_SERVER["PW"]  );              
            alog("getDbConn() KEY="      . $CFG["CFG_SEC_KEY"] );           
            //alog("getDbConn() MYSQL_PW(decrypt)="      . aes_decrypt($tOBJ_SERVER["PW"],$CFG["CFG_SEC_KEY"]) );        
            alog("getDbConn() MYSQL_DBNM="      . $tOBJ_SERVER["DBNM"] );
            alog("getDbConn() MYSQL_PORT="    . $tOBJ_SERVER["PORT"] );
            //alog("db_obj_open() MYSQL_PW="    . $tOBJ_SERVER->MYSQL_PW);
            alog("mysqli error : " . $db->connect_errno . "/" . $db->connect_error);
            JsonMsg("500","999","getDbConn() host(" . $tOBJ_SERVER["HOST"] . ") Connect failed : " .  $db->connect_error);
            //printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
    }else if($tOBJ_SERVER["DRIVER"] == "PDO_MYSQL" || $tOBJ_SERVER["DRIVER"] == "PDO_PGSQL"){
        if($tOBJ_SERVER["DRIVER"] == "PDO_MYSQL")$driverNm = "mysql";
        if($tOBJ_SERVER["DRIVER"] == "PDO_PGSQL")$driverNm = "pgsql";
        $dsn = $driverNm . ":host=" . $tOBJ_SERVER["HOST"] . ";port=" . $tOBJ_SERVER["PORT"] . ";dbname=" . $tOBJ_SERVER["DBNM"] . ";charset=utf8";
        try {
            $db = new PDO(
                $dsn
                , $tOBJ_SERVER["ID"]
                // aes_decrypt($tOBJ_SERVER["PW"] ,$CFG["CFG_SEC_KEY"])
                , $tOBJ_SERVER["PW"]
                , array(
                PDO::ATTR_TIMEOUT => 1
            ));
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $e) {
            //echo $e->getMessage();

            alog("getDbConn() MYSQL_DRIVER="    . $tOBJ_SERVER["DRIVER"] );
            alog("getDbConn() MYSQL_HOST="    . $tOBJ_SERVER["HOST"] );
            alog("getDbConn() MYSQL_ID="      . $tOBJ_SERVER["ID"] );
            alog("getDbConn() MYSQL_PW="      . $tOBJ_SERVER["PW"]  );              
            echo("getDbConn() KEY="      . $CFG["CFG_SEC_KEY"] );           
            //echo("getDbConn() MYSQL_PW(decrypt)="      . aes_decrypt($tOBJ_SERVER["PW"],$CFG["CFG_SEC_KEY"]) );        
            alog("getDbConn() MYSQL_DBNM="      . $tOBJ_SERVER["DBNM"] );
            alog("getDbConn() MYSQL_PORT="    . $tOBJ_SERVER["PORT"] );
            //alog("db_obj_open() MYSQL_PW="    . $tOBJ_SERVER->MYSQL_PW);
            alog("pdo_mysql error : " . $e->getMessage());
            exit();
        }
    }

    //echo "<br>db 연결 성공";
    return $db;
}



function getDbConnPlain($tOBJ_SERVER){
    global $CFG;
    alog("getDbConnPlain().................................start:" . $tOBJ_SERVER["DRIVER"]);


    if($tOBJ_SERVER["PORT"] == "")$tOBJ_SERVER["PORT"] = "3306";
    if($tOBJ_SERVER["DRIVER"] == "")$tOBJ_SERVER["DRIVER"] = "MYSQLI";
    
    if($tOBJ_SERVER["DRIVER"] == "MYSQLI"){
        $db = mysqli_init();
        if (!$db) {
            alog("getDbConnPlain() mysqli_init failed");
            throw new Exception("getDbConnPlain() mysqli_init failed");
            exit();
        }
        if (!$db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1)) {
            alog("getDbConnPlain() Setting MYSQLI_OPT_CONNECT_TIMEOUT failed");
            throw new Exception("getDbConnPlain() Setting MYSQLI_OPT_CONNECT_TIMEOUT failed");
        }        
        if(
            !$db->real_connect(
                $tOBJ_SERVER["HOST"]
                , $tOBJ_SERVER["ID"]
                , $tOBJ_SERVER["PW"] //비밀번호 복호화
                , $tOBJ_SERVER["DBNM"]
                , $tOBJ_SERVER["PORT"])
        ){
            alog("getDbConnPlain() MYSQL_DRIVER="    . $tOBJ_SERVER["DRIVER"] );
            alog("getDbConnPlain() MYSQL_HOST="    . $tOBJ_SERVER["HOST"] );
            alog("getDbConnPlain() MYSQL_ID="      . $tOBJ_SERVER["ID"] );
            alog("getDbConnPlain() MYSQL_PW="      . $tOBJ_SERVER["PW"]  );                      
            alog("getDbConnPlain() MYSQL_DBNM="      . $tOBJ_SERVER["DBNM"] );
            alog("getDbConnPlain() MYSQL_PORT="    . $tOBJ_SERVER["PORT"] );
            //alog("db_obj_open() MYSQL_PW="    . $tOBJ_SERVER->MYSQL_PW);
            alog("mysqli error : " . $db->connect_errno . "/" . $db->connect_error);
            throw new Exception("getDbConnPlain() mysqli error : " . $db->connect_errno . "/" . $db->connect_error);
            JsonMsg("500","999","getDbConnPlain() host(" . $tOBJ_SERVER["HOST"] . ") Connect failed : " .  $db->connect_error);
            //printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
    }else if($tOBJ_SERVER["DRIVER"] == "PDO_MYSQL" || $tOBJ_SERVER["DRIVER"] == "PDO_PGSQL"){
        if($tOBJ_SERVER["DRIVER"] == "PDO_MYSQL")$driverNm = "mysql";
        if($tOBJ_SERVER["DRIVER"] == "PDO_PGSQL")$driverNm = "pgsql";
        $dsn = $driverNm . ":host=" . $tOBJ_SERVER["HOST"] . ";port=" . $tOBJ_SERVER["PORT"] . ";dbname=" . $tOBJ_SERVER["DBNM"] . ";charset=utf8";
        try {
            $db = new PDO(
                $dsn
                , $tOBJ_SERVER["ID"]
                , $tOBJ_SERVER["PW"]
                , array(
                PDO::ATTR_TIMEOUT => 1
            ));
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $e) {
            //echo $e->getMessage();

            alog("getDbConnPlain() MYSQL_DRIVER="    . $tOBJ_SERVER["DRIVER"] );
            alog("getDbConnPlain() MYSQL_HOST="    . $tOBJ_SERVER["HOST"] );
            alog("getDbConnPlain() MYSQL_ID="      . $tOBJ_SERVER["ID"] );    
            alog("getDbConnPlain() MYSQL_PW="      . $tOBJ_SERVER["PW"] );        
            alog("getDbConnPlain() MYSQL_DBNM="      . $tOBJ_SERVER["DBNM"] );
            alog("getDbConnPlain() MYSQL_PORT="    . $tOBJ_SERVER["PORT"] );
            //alog("db_obj_open() MYSQL_PW="    . $tOBJ_SERVER->MYSQL_PW);
            alog("getDbConnPlain() pdo_mysql error : " . $e->getMessage());

            throw new Exception("getDbConnPlain() pdo_mysql error : " . $e->getMessage());
            exit();
        }
    }

    //echo "<br>db 연결 성공";
    return $db;
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

function fetchAll($tresult)
{
    //echo "<br>";
    if($tresult instanceof PDOStatement){
        //pdo
        $res = $tresult->fetchAll(PDO::FETCH_ASSOC);
    }else{
        //mysqli
        if (method_exists('mysqli_result', 'fetch_all')) # Compatibility layer with PHP < 5.3
        {
            //echo "fetch_all method exist";
            $res = $tresult->fetch_all(MYSQLI_ASSOC);
        }
        else{
            //echo "fetch_all method not exist";
            for ($res = array(); $tmp = $tresult->fetch_array(MYSQLI_ASSOC);) $res[] = $tmp;
        }        
    }
    return $res;
}


function fetchAllNum($tresult)
{
    //echo "<br>";
    if($tresult instanceof PDOStatement){
        //pdo
        $res = $tresult->fetchAll(PDO::FETCH_NUM);
    }else{
        //mysqli
        if (method_exists('mysqli_result', 'fetch_all')) # Compatibility layer with PHP < 5.3
        {
            //echo "fetch_all method exist";
            $res = $tresult->fetch_all(MYSQLI_NUM);
        }
        else{
            //echo "fetch_all method not exist";
            for ($res = array(); $tmp = $tresult->fetch_array(MYSQLI_NUM);) $res[] = $tmp;
        }        
    }
    return $res;
}


function getSqlParam($sql,$coltype,$map){
    global $PGM_CFG, $log;
	alog("getSqlParam-----------------------------------start");

    $tParamColids = array(); //G3-COLID를 그대로 저장
    //$tDdColids = ""; //G3-COLID일때 G3은 제거

    $k = 0;//본 sql
    $d = 0;//디버그 sql
    $to_map = array();
    $to_sql = $sql;
    $debug_sql = $sql;
    //$to_coltype = $coltype;

    $to_coltype = str_replace(" ","",$coltype,$count);
    alog("to_coltype before : " . $to_coltype);    
    //echo "\n to_coltype replace:" . $count;
    //LogMaster::log("        to_coltype : " . $to_coltype);


    //파라미터 분해 (정규식에서 .를 검색할때는 []안에 인수 값중에 맨뒤에 가면 동작안함)
    while(preg_match("/(#{)([\.a-zA-Z0-9_-]+)(})/",$to_sql,$mat)){

        alog( "\nsql : " . $sql);
        alog( "\n매칭0 : " . $mat[0]);
        alog( "\n매칭1 : " . $mat[1]);
        alog( "\n매칭2 : " . $mat[2]);
        alog( "\n매칭3 : " . $mat[3]);
        alog( "\n매칭4 : " . $mat[4]);




        //echo( sprintf("%3s %1s - %20s = [%s]", $k, substr($to_coltype,$k,1), $mat[2] , $map[$mat[2]]) );

        $tColtype = substr($to_coltype,$d,1) ;
        
        //var_dump($coltype);
        //exit;

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

    //sequence 컬럼 추출
    $RtnVal = array();
    if(preg_match("/\s+nextval\((\s*|')([a-z0-9A-Z_]+)(\s*|')\)/i",$sql,$mat)){
        //mat[0] : full match
        //mat[2] : only sequence name
        $RtnVal["SEQ_NM"] = $mat[2];
    }


    //최종
    if($log)$log->info("to_coltype after : " . $to_coltype);    
	if($log)$log->info("prepare sql : " . $to_sql);
    if($log)$log->info("full sql : " . $debug_sql);

    $RtnVal["TO_COLTYPE"] = $to_coltype;
    $RtnVal["TO_SQL"] = $to_sql;
    $RtnVal["TO_PARAM"] = $to_map;    
    $RtnVal["DEBUG_SQL"] = $debug_sql;    

	//alog("makeStmt-----------------------------------end");
    return $RtnVal;
}

function makeStmt($db,$sql,$coltype,$map){
    global $PGM_CFG, $log;
	alog("makeStmt-----------------------------------start");

    $tParamColids = array(); //G3-COLID를 그대로 저장
    //$tDdColids = ""; //G3-COLID일때 G3은 제거

    $k = 0;//본 sql
    $d = 0;//디버그 sql
    $to_map = array();
    $to_sql = $sql;
    $debug_sql = $sql;
    //$to_coltype = $coltype;

    $to_coltype = str_replace(" ","",$coltype,$count);
    alog("to_coltype before : " . $to_coltype);    
    //echo "\n to_coltype replace:" . $count;
    //LogMaster::log("        to_coltype : " . $to_coltype);


    //파라미터 분해 (정규식에서 .를 검색할때는 []안에 인수 값중에 맨뒤에 가면 동작안함)
    while(preg_match("/(#{)([\.a-zA-Z0-9_-]+)(})/",$to_sql,$mat)){
        //alog("org : " . HtmlEncode($org));
        //alog("매칭0 : " . $mat[0]);
        //alog("매칭1 : " . $mat[1]);
        //alog("매칭2 : " . $mat[2]);
        //alog("매칭3 : " . $mat[3]);
        //alog("매칭4 : " . $mat[4]);
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
    alog("to_coltype after : " . $to_coltype);    
	alog("prepare sql : " . $to_sql);
    alog("full sql : " . $debug_sql);

    //[로그저장용] 권한변경로그용 SQL더하기 
    if($PGM_CFG["SECTYPE"] == "POWER" || $PGM_CFG["SECTYPE"] == "PI") {
        $tArr = array("PREPARE_SQL"=>$to_sql,"FULL_SQL"=>$debug_sql, "COLIDS"=>$tParamColids);
        array_push($PGM_CFG["SQLTXT"],$tArr);
    }
    
    $stmt = $db->prepare($to_sql);
    //alog("stmt is_object : " . is_object($stmt));
    //$stmt->bind_param($to_coltype, $to_map);

    if(!$stmt){
        alog("stmt error : stmt is " . $stmt->errno . " > " . $stmt->error . ", db is " . $db->errno . " > " . $db->error);
        return false;
    }else if($k > 0 && !function_exists($stmt->getAttribute)){
        //mysqli
        //echo "222";
		//sql문에 bind param이 하나라도 있으면 처리
        //echo "<pre>";
        //var_dump($db);
        //alog("stmt ok");
        alog("function_exists(stmt->bind_param) = " . function_exists($stmt->bind_param) );

        //다시한번
        $bind_names = null;
        $bind_names[] = $to_coltype;
        for ($i=0; $i<count($to_map);$i++)
        {
            $bind_name = 'bind' . $i;
            $$bind_name = $to_map[$i];
            $bind_names[] = &$$bind_name;
        }


        //var_dump($stmt);
        //var_dump($bind_names);

        //바인드 파람 처리
        if(!call_user_func_array(array(&$stmt, 'bind_param'), $bind_names)){
            alog("bind_param error : " . $stmt->errno . " > " . $stmt->error);
            return false;
        }
    }else{
        alog("stmt else 처리가 없음");
    }
    /* Set our params */

	alog("makeStmt-----------------------------------end");

    return $stmt;
}


function make_stmt($db,$sql,$coltype,$map){
    global $log;
	alog("make_stmt-----------------------------------start");

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
        if($log)$log->info("        stmt error : stmt is " . $stmt->errno . " > " . $stmt->error . ", db is " . $db->errno . " > " . $db->error);
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
    $RtnVal = new stdclass();

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



function getStmt(&$db,$sqlMap){
    global $log;
    alog("getStmt()...............................start");
    $rtnStmt = null;
    //echo "<BR>ATTR_CLIENT_VERSION : " . $db->getAttribute("PDO::ATTR_CLIENT_VERSION");

    //echo "<pre>" . json_encode($sqlMap, JSON_PRETTY_PRINT);

    //echo "<BR>server_info=" . $db->server_info;
    //echo "<BR>host_info=" . $db->host_info;

    if($db == null){
        if($log)$log->info("incDB.php getStmt() error : db is null");
        return null;
    }
    if($db->server_info == ""){
        //pdo
        //echo "<BR>pdo---"  . $sqlMap["TO_SQL"];
        try{
            $rtnStmt = $db->prepare($sqlMap["TO_SQL"]);
        }catch (Exception $e) {
            if($log)$log->info("db prepare error:" . $e->getMessage());
        }


        if(!$rtnStmt)JsonMsg("500","101", "(PDO) stmt null error : stmt is " . $rtnStmt->errno . " > " . $rtnStmt->error . ", db is " . $db->errno . " > " . $db->error);
        for($t=0;$t<count($sqlMap["TO_PARAM"]);$t++){
            $value = $sqlMap["TO_PARAM"][$t];
            if(is_int($value))
                $param = PDO::PARAM_INT;
            elseif(is_bool($value))
                $param = PDO::PARAM_BOOL;
            elseif(is_null($value))
                $param = PDO::PARAM_NULL;
            elseif(is_string($value))
                $param = PDO::PARAM_STR;
            else
                $param = FALSE;

            $rtnStmt->bindValue($t+1,$value,$param);
        }
    }else{
        //mysqli
        //echo "<BR>mysqli---" . $sqlMap["TO_SQL"];
        //sql문에 bind param이 하나라도 있으면 처리
        //alog("        stmt ok");
        //echo "111";
        $rtnStmt = $db->prepare($sqlMap["TO_SQL"]);
        //echo "222";

        if(!$rtnStmt)JsonMsg("500","102", "(MysqlI) stmt null error : stmt is " . $rtnStmt->errno . " > " . $rtnStmt->error . ", db is " . $db->errno . " > " . $db->error);
        //echo "333";
        //바인딩 시키기
        if(count($sqlMap["TO_PARAM"]) > 0){
            //echo "<BR>count to_param ok.";
            
            //최신방법 했더니 안됨
            //call_user_func_array(array($rtnStmt, "bind_param"),refValues($sqlMap["TO_PARAM"])); 

            //다시한번
            $to_coltype = $sqlMap["TO_COLTYPE"];
            $to_map = $sqlMap["TO_PARAM"];

            $bind_names = null;
            $bind_names[] = $to_coltype;
            for ($i=0; $i<count($to_map);$i++)
            {
                $bind_name = 'bind' . $i;
                $$bind_name = $to_map[$i];
                $bind_names[] = &$$bind_name;
            }

            //var_dump($bind_names);

            //바인드 파람 처리
            if(!call_user_func_array(array(&$rtnStmt, 'bind_param'), $bind_names)){
                alog("        bind_param error : " . $stmt->errno . " > " . $stmt->error);
                if($log)$log->info("        bind_param error : " . $stmt->errno . " > " . $stmt->error);
                return false;
            }

        }
        //echo "444";

    }

    alog("getStmt()...............................end");
    return $rtnStmt;
}

function getStmtArray(&$stmt){
    alog("getStmtArray()...............................start");    
    $RtnVal = array();

    //var_dump($stmt);
    if($stmt instanceOf PDOStatement){
    //if($stmt->queryString){
        //pdo
        //var_dump($param);
        //exit;
        //if(!$stmt->execute($param))JsonMsg("500","101","getStmtArray() (PDO) stmt execute fail1 -"
        // . $stmt->errno . " -> " . $stmt->error);        

        try{
            if(!$stmt->execute())JsonMsg("500","101","getStmtArray() (PDO) stmt execute fail1 -"
            . $stmt->errno . " -> " . $stmt->error);
        }catch(PDOException $e){
            JsonMsg("500","114","(getStmtArray) PDOException  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }catch(mysqli_sql_exception $e){
            JsonMsg("500","115","(getStmtArray) mysqli_sql_exception  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }catch(Exception $e){
            JsonMsg("500","116","(getStmtArray) Exception  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }

        $RtnVal =  fetchAll($stmt); //해쉬맵
        //var_dump($RtnVal);
        $stmt->closeCursor();
    }else{
        //mysqli
        try{
            if(!$stmt->execute())JsonMsg("500","102","getStmtArray() (MysqlI) stmt execute fail2 -"
            . $stmt->errno . " -> " . $stmt->error);
        }catch(PDOException $e){
            JsonMsg("500","124","(getStmtArray) PDOException  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }catch(mysqli_sql_exception $e){
            JsonMsg("500","125","(getStmtArray) mysqli_sql_exception  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }catch(Exception $e){
            JsonMsg("500","126","(getStmtArray) Exception  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }

        $result = $stmt->get_result();
        //$array = $result->mysqli_fetch_all(MYSQLI_ASSOC);

        $RtnVal = fetchAll($result);
        //echo json_encode($array,JSON_PRETTY_PRINT);
    }

    alog("getStmtArray()...............................end");    
    return  $RtnVal;
}

function getStmtArrayNum(&$stmt){
    alog("getStmtArrayNum()...............................start");    
    $RtnVal = array();

    //var_dump($stmt);
    if($stmt->queryString){
        //pdo
        //var_dump($param);
        //exit;
        //if(!$stmt->execute($param))JsonMsg("500","101","getStmtArrayNum() (PDO) stmt execute fail1 -"
        //. $stmt->errno . " -> " . $stmt->error);
        try{
            if(!$stmt->execute())JsonMsg("500","101","getStmtArrayNum() (PDO) stmt execute fail1 -"
            . $stmt->errno . " -> " . $stmt->error);
        }catch(PDOException $e){
            JsonMsg("500","114","(getStmtArrayNum) PDOException  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }catch(mysqli_sql_exception $e){
            JsonMsg("500","115","(getStmtArrayNum) mysqli_sql_exception  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }catch(Exception $e){
            JsonMsg("500","116","(getStmtArrayNum) Exception stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }

        $RtnVal =  fetchAllNum($stmt); //해쉬맵
        //var_dump($RtnVal);
        //$stmt->closeCursor();
    }else{
        //mysqli
        try{
            if(!$stmt->execute())JsonMsg("500","102","getStmtArrayNum() (MysqlI) stmt execute fail2 -"
            . $stmt->errno . " -> " . $stmt->error);
        }catch(PDOException $e){
            JsonMsg("500","124","(getStmtArrayNum) PDOException  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }catch(mysqli_sql_exception $e){
            JsonMsg("500","125","(getStmtArrayNum) mysqli_sql_exception  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }catch(Exception $e){
            JsonMsg("500","126","(getStmtArrayNum) Exception  stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
        }        


        $result = $stmt->get_result();
        //$array = $result->mysqli_fetch_all(MYSQLI_ASSOC);

        $RtnVal = fetchAllNum($result);
        //echo json_encode($array,JSON_PRETTY_PRINT);
    }

    alog("getStmtArray()...............................end");    
    return  $RtnVal;
}











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
		closeStmt($stmt);;
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
        
		alog("makeGridSearchJson..........................start");

        //mysql
        //var_dump($db[$map["SQL"]["R"]["SVRID"]]);
        if($db[$map["SQL"]["R"]["SVRID"]]->server_info != ""){
            alog("  mysqli dbdriver ok.");
            
            //$stmt = makeStmt($db[$map["SQL"]["R"]["SVRID"]],$map["SQL"]["R"]["SQLTXT"], $map["SQL"]["R"]["BINDTYPE"], $REQ);

            $sqlMap = getSqlParam($map["SQL"]["R"]["SQLTXT"],$map["SQL"]["R"]["BINDTYPE"],$REQ);
            alog(print_r($sqlMap, true));
            $stmt = getStmt($db[$map["SQL"]["R"]["SVRID"]],$sqlMap);

            if(!$stmt)   JsonMsg("500","100","(makeGridSearchJson) stmt 생성 실패 " . $db->errno . " -> " . $db->error);
            if(!$stmt->execute())JsonMsg("500","110","(makeGridSearchJson) stmt 실행 실패 " . $stmt->error);


            
            //$colcrypt_array = explode(",",$map["COLCRYPT"]);   
            $colcrypt_array =$map["COLCRYPT"];  
    
            //$map["SQLTXT"] = "1111";
            //alog("makeGridSearchJson() SQLTXT = " . $map["SQLTXT"]);
    
            
            //$stmt->store_result();
            
            //num_rows는 store_result실행 후에 해야함.
            //alog("stmt.num_rows = " . $stmt->num_rows);
    
            //[로그 저장용]
            //$PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $stmt->num_rows;
    

            $meta = $stmt->result_metadata();
            while($field = $meta->fetch_field())
            {
                $colNms[] = $field->name;
            }

            $arr = getStmtArrayNum($stmt);
            closeStmt($stmt);
            //echo "<pre><BR>" . jsonView($arr);

            //결과 JSON 화면 출력
            $RtnVal = new stdClass();
            $RtnVal->RTN_DATA = new stdClass();

            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
            $RtnVal->RTN_DATA->rows = transDhtmlxLoad($arr,$colNms,$map["COLCRYPT"],$map["KEYCOLIDX"]);


        }else{
            alog("  PDO dbdriver ok.");            
            //PDO
            $sqlMap = getSqlParam($map["SQL"]["R"]["SQLTXT"], $map["SQL"]["R"]["BINDTYPE"], $REQ);

            //echo "<pre>" . json_encode( $sqlMap,JSON_PRETTY_PRINT );
            //echo "<br>222";
        
            $stmt = getStmt($db[$map["SQL"]["R"]["SVRID"]],$sqlMap);
            
            if(!$stmt)JsonMsg("500","102", "makeGridSearchJson (PDO) stmt null error : stmt is " . $stmt->errno . " > " . $stmt->error . ", db is " . $db->errno . " > " . $db->error);
                
            //var_dump($stmt);
            //$stmt2 = makeStmt($db2,$sql,$coltype="i",$REQ);
            
            $arr = getStmtArrayNum($stmt);

            //컬럼 정보 꺼내오기
            for($k=0;$k<$stmt->columnCount();$k++)
            {
                //echo $k . " ";
                $colNms[] = $stmt->getColumnMeta($k)["name"];
            }
            closeStmt($stmt);

            $RtnVal = new stdClass();
            $RtnVal->RTN_DATA = new stdClass();

            $RtnVal->RTN_CD = "200";
            $RtnVal->ERR_CD = "200";
            $RtnVal->RTN_DATA->rows = transDhtmlxLoad($arr,$colNms,$map["COLCRYPT"],$map["KEYCOLIDX"]);

            //echo "<pre>" . jsonView($RtnVal);
            //exit;
            //$RtnVal->RTN_DATA->rows = ;
        }
		//var_dump($RtnVal);
	
		//$RtnVal = json_encode($RtnVal)
		return  $RtnVal;

    }



    //배열 데이터를 dhtmlxLoad 데이터 타입으로 변경하기
    function transDhtmlxLoad($rows,$colNms,$colCrypt,$keyColIdx){
        global $CFG;

        $RtnVal = array();

        for($r=0;$r<count($rows);$r++){
            $one_row = array();
            $j = 0;
            $cols = $rows[$r];
            for($m=0;$m<count($cols);$m++){
                $k = $colNms[$m];
                $v = $cols[$m];

                $transVal = makeParamDec($k,$v,$colCrypt); //복호화 등 처리
                array_push($one_row, $transVal );

                if($m == $keyColIdx)$id_col_value = $v;
            }
            $RtnVal[$r]['id'] = strval($id_col_value);
            $RtnVal[$r]['data']=$one_row;
        }

        return $RtnVal;
    }

    //배열 데이터를 dhtmlxLoad 데이터 타입으로 변경하기
    function transBtgridLoad($rows,$colNms,$colCrypt,$keyColIdx){
        global $CFG;

        $RtnVal = array();

        for($r=0;$r<count($rows);$r++){
            $one_row = array();
            $j = 0;
            $cols = $rows[$r];
            for($m=0;$m<count($cols);$m++){
                $k = $colNms[$m];
                $v = $cols[$m];

                $transVal = makeParamDec($k,$v,$colCrypt); //복호화 등 처리
                $one_row[$k] = $transVal;

                if($m == $keyColIdx)$id_col_value = $v;
            }

            $RtnVal[$r] = $one_row;
        }

        return $RtnVal;
    }


    //배열 데이터를 transWebixLoad 데이터 타입으로 변경하기
    function transWebixLoad($rows,$colNms,$colCrypt,$keyColIdx){
        global $CFG;

        $RtnVal = array();

        //컬럼이 암호화 컬럼이 하나라도 있는지 검사하기
        $targetColNms = array();
        for($t = 0; $t < sizeof($colNms); $t++){
            if($colCrypt[$colNms[$t]] != null) array_push($targetColNms,$colNms[$t]);
        }

        //암호화 컬럼이 1개라도 있으면 변환, 아니면 바로 리턴
        if(sizeof($targetColNms) == 0){
            return $rows;
        }else{
            for($r=0;$r<count($rows);$r++){
                $j = 0;
                $cols = $rows[$r];

                //변환할 컬럼만 루프 돌려 변환시키기
                for($m=0;$m<count($targetColNms);$m++){
                    $colNm = $targetColNms[$m];
                    $oldValue = $cols[$colNm];
    
                    $newValue = makeParamDec($colNm,$oldValue,$colCrypt); //복호화 등 처리
                    $cols[$colNm] = $newValue;
                }
    
                $RtnVal[$r] = $cols;
            }
            return $RtnVal;
        }
    }



	function makeGridSearchJsonArray($map,&$db){
		global $REQ, $CFG, $PGM_CFG;
		alog("makeGridSearchJsonArray..................................start");

        $RtnVal = new stdclass();

        //main/sub sql 갯수 만큼 루프돌면서 처리하기
        alog("  sql sizeof : " . sizeof($map["SQL"]));
        for($s=0;$s<sizeof($map["SQL"]);$s++){
            $tmpSql = $map["SQL"][$s];

            //main/sub 구분에 따라 처리
            alog("  sql[" . $s . "] PSQLSEQ = ". $tmpSql["PSQLSEQ"]);            
            alog("      SVRID = " . $tmpSql["SVRID"]);
            alog("      SQLTXT = " . $tmpSql["SQLTXT"]);
            alog("      BINDTYPE = " . $tmpSql["BINDTYPE"]);
            
            //$stmt = makeStmt($db[$tmpSql["SVRID"]], $tmpSql["SQLTXT"], $tmpSql["BINDTYPE"], $REQ);

            $sqlMap = getSqlParam($tmpSql["SQLTXT"],$tmpSql["BINDTYPE"],$REQ);

            alog("      DEBUG_SQL = " . $sqlMap["DEBUG_SQL"]);

            $stmt = getStmt($db[$tmpSql["SVRID"]],$sqlMap);

            if(!$stmt)   JsonMsg("500","112","(makeGridSearchJsonArray) " . $tmpSql["SQLID"] . " stmt create fail - " . $db->errno . " -> " . $db->error);

            try{
                if(!$stmt->execute())JsonMsg("500","123","(makeGridSearchJsonArray) " . $tmpSql["SQLID"] . " stmt execute fail - " . $stmt->error);
            }catch(PDOException $e){
                JsonMsg("500","114","(makeGridSearchJsonArray) PDOException " . $tmpSql["SQLID"] . " stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
            }catch(mysqli_sql_exception $e){
                JsonMsg("500","115","(makeGridSearchJsonArray) mysqli_sql_exception " . $tmpSql["SQLID"] . " stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
            }catch(Exception $e){
                JsonMsg("500","115","(makeGridSearchJsonArray) Exception " . $tmpSql["SQLID"] . " stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
            }
    
            //main만 처리
            if( $tmpSql["PARENT_FNCTYPE"] == ""){


                //$colcrypt_array = explode(",",$map["COLCRYPT"]);   
                $colcrypt_array =$map["COLCRYPT"];  


                if($stmt instanceOf PDOStatement){
                    //pdo_mysql
                    //컬럼 정보 꺼내오기
                    for($k=0;$k<$stmt->columnCount();$k++)
                    {
                        $colNms[] = $stmt->getColumnMeta($k)["name"];
                    }                    
                }else{
                    //mysqli driver
                    //컬럼 정보 꺼내오기
                    $meta = $stmt->result_metadata();
                    while($field = $meta->fetch_field())
                    {
                        $colNms[] = $field->name;
                    }
                }

 

                $RtnVal->RTN_CD = "200";
                $RtnVal->ERR_CD = "200";
                $RtnVal->RTN_DATA = new stdclass();
                if($map["GRPTYPE"] == "GRID_BOOTSTRAP"){
                    $arr = getStmtArrayNum($stmt);
                    closeStmt($stmt);     
                    $RtnVal->RTN_DATA->rows = transBtgridLoad($arr,$colNms,$map["COLCRYPT"],$map["KEYCOLIDX"]);
                }else if($map["GRPTYPE"] == "GRID_WEBIX"){
                    $arr = getStmtArray($stmt);
                    closeStmt($stmt);     
                    //OLD : $RtnVal->RTN_DATA->rows = $arr;            
                    $RtnVal->RTN_DATA->rows = transWebixLoad($arr,$colNms,$map["COLCRYPT"],$map["KEYCOLIDX"]);
                }else if($map["GRPTYPE"] == "GRID_JQXWIDGETS"){
                    $arr = getStmtArray($stmt);
                    closeStmt($stmt);     
                    $RtnVal->RTN_DATA->rows = $arr;
                }else{
                    $arr = getStmtArrayNum($stmt);
                    closeStmt($stmt);     
                    $RtnVal->RTN_DATA->rows = transDhtmlxLoad($arr,$colNms,$map["COLCRYPT"],$map["KEYCOLIDX"]);
                }                
                
            }

            

        }


		//$result_array = fetch_all($result,MYSQLI_NUM);//indDB.php
		//결과 JSON 화면 출력
		//$RtnVal->RTN_CD = "200";
		//$RtnVal->ERR_CD = "200";
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

        $RtnVal = new stdclass();
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
			   
				if(!$stmt) JsonMsg("500","200","(makeGridChkJson) stmt 생성 실패" . $db[$svrid]->errno . " -> " . $db[$svrid]->error);
			   
				if(!$stmt->execute())JsonMsg("500","210","(makeGridChkJson) stmt 실행 실패 " . $stmt->error);

				//echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
				$to_affected_rows = $db[$svrid]->affected_rows;

                //[로그 저장용]
                $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;

                alog("SEQYN Y : " . $db[$svrid]->insert_id);
                $to_row["COLID"] = $db[$svrid]->insert_id; //insert문인 경우 insert id받기

				closeStmt($stmt);;

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

        $RtnVal = new stdclass();
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
                if(!$stmt) JsonMsg("500","200","(makeGridChkJsonArray) " . $tmpSql["SQLID"] . "  stmt 생성 실패" . $db[$svrid]->errno . " -> " . $db[$svrid]->error);
                if(!$stmt->execute())JsonMsg("500","210","(makeGridChkJsonArray) " . $tmpSql["SQLID"] . "  stmt 실행 실패 " . $stmt->error);

                if($tmpSql["PARENT_FNCTYPE"] == ""){
                    //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
                    $to_affected_rows = $db[$svrid]->affected_rows;

                    //[로그 저장용]
                    $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;

                    alog("SEQYN Y : " . $db[$svrid]->insert_id);
                    $to_row["COLID"] = $db[$svrid]->insert_id; //insert문인 경우 insert id받기

                    closeStmt($stmt);;

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
        $RtnVal = new stdclass();
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
        $RtnVal = new stdclass();
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
			alog(" Y " );
			$xml_array_last[0] = $xml["row"];
		}else{
			alog(" N " );

			$xml_array_last = $xml["row"];
		}
		//var_dump($xml_array_last);


		$RtnCnt = 0;
		alog("xml sizeof : " . sizeof($xml_array_last));
		for($i=0;$i<sizeof($xml_array_last) && $isRequireResult;$i++){

			$row = $xml_array_last[$i];
			alog("        i : " . $i);
			alog("        @attributes : " . $row["@attributes"]["id"]);
			alog("        userdata : " . $row["userdata"]);

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
                alog("        inserted : " );

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
                alog("        updated : " );

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
                alog("        deleted : " );

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



	function requireGridjqxSaveArray($colord,$json,$sql){
        global $REQ,$CFG, $PGM_CFG;
        
        //ar_dump($sql["U"]);
        //exit;
        $RtnVal = new stdclass();
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

		$json_array_last = null;
        alog("requireGrid is_assoc : " . is_assoc($json) );
        alog("requireGrid count : " . count($xml["row"]) );
        alog("requireGrid sizeof : " . sizeof($xml["row"]) );
		if(is_assoc($xml["row"]) == 1) {
			alog(" Y " );
			$json_array_last[0] = $json["row"];
		}else{
			alog(" N " );

			$json_array_last = $json["row"];
		}
		//var_dump($xml_array_last);


		$RtnCnt = 0;
		alog("json sizeof : " . sizeof($json_array_last));
		for($i=0;$i<sizeof($json_array_last) && $isRequireResult;$i++){

			$row = $json_array_last[$i];
			alog("        i : " . $i);
			//alog("        @attributes : " . $row["@attributes"]["id"]);
			alog("        changeCud : " . $row["changeCud"]);

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

			if($row["changeCud"] == "inserted"  ){
                alog("        inserted : " );

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

			}else if($row["changeCud"] == "updated"){
                alog("        updated : " );

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

			}else if($row["changeCud"] == "deleted" ){
                alog("        deleted : " );

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



	function requireGridwixSaveArray($colord,$json,$sql){
        global $REQ,$CFG, $PGM_CFG;
        
        //ar_dump($sql["U"]);
        //exit;
        $RtnVal = new stdclass();
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

		$RtnCnt = 0;
		alog("json sizeof : " . sizeof($json));
		for($i=0;$i<sizeof($json) && $isRequireResult;$i++){

			$row = $json[$i];
			alog("        i : " . $i);
			//alog("        @attributes : " . $row["@attributes"]["id"]);
			alog("        changeCud : " . $row["changeCud"]);

            
            $tArr = array_merge($REQ,$row);

			if($row["changeCud"] == "inserted"  ){
                alog("        inserted : " );

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

			}else if($row["changeCud"] == "updated"){
                alog("        updated : " );

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

			}else if($row["changeCud"] == "deleted" ){
                alog("        deleted : " );

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
        $RtnVal = new stdclass();
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
        $RtnVal = new stdclass();
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

        $RtnVal = new stdclass();
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
                    $to_row[trim($colord_array[$j])] = makeParamEnc($colord_array[$j],$col,$colcrypt_array);

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


                $sqlMap = getSqlParam($sql,$to_coltype,array_merge($REQ,$to_row));
                $stmt = getStmt($db[$svrid],$sqlMap);

				//$stmt = makeStmt($db[$svrid],$sql, $to_coltype, array_merge($REQ,$to_row));
			   
				if(!$stmt) JsonMsg("500","200","(makeGridSaveJson) stmt create fail - " . $db->errno . " -> " . $db->error);
				if(!$stmt->execute())JsonMsg("500","210","(makeGridSaveJson) stmt execute fail " . $stmt->error);

                //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
                if($stmt instanceof PDOStatement){
                    $to_affected_rows = $stmt->rowCount();
                }else{
                    $to_affected_rows = $db[$svrid]->affected_rows;
                }
            
                //[로그 저장용]
                $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"] ?? [])-1]["ROW_CNT"] = $to_affected_rows;                
			
				$to_row["COLID"] = "";
				if($row["userdata"] == "inserted"){
					if($map["SEQYN"] == "Y"){
                        if($stmt instanceof PDOStatement){
                            alog("SEQYN Y : " . $db[$svrid]->lastInsertId());
                            $to_row["COLID"]=$db[$svrid]->lastInsertId(); //insert문인 경우 insert id받기                            
                        }else{
                            alog("SEQYN Y : " . $db[$svrid]->insert_id);
                            $to_row["COLID"]=$db[$svrid]->insert_id; //insert문인 경우 insert id받기
                        }
					}else{
						alog("SEQYN N : " . $to_row[$map["KEYCOLID"]]);
						$to_row["COLID"]=$to_row[$map["KEYCOLID"]]; //사용자 입력 key컬럼을 rowid 로
					}
				}

				closeStmt($stmt);

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
			alog(" is_assoc = Y " );
			$xml_array_last[0] = $map["XML"]["row"];
		}else{
			alog(" is_assoc = N " );

			$xml_array_last = $map["XML"]["row"];
		}
		//var_dump($xml_array_last);

        $RtnVal = new stdclass();
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
                    $to_row[trim($colord_array[$j])] = makeParamEnc($colord_array[$j],$col,$colcrypt_array);
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
                    break;
            }
            alog("        mapLoop size : " . sizeof($mapLoop));   

            if(sizeof($mapLoop)==0)JsonMsg("500","899","(makeGridSaveJsonArray) 명령어를 처리할 SQL이 없습니다.");

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

                //$stmt = makeStmt($db[$svrid], $sql, $to_coltype, array_merge($REQ,$to_row));
                
                $sqlMap = getSqlParam($sql,$to_coltype,array_merge($REQ,$to_row));
                //echo "<pre>" . jsonView($sqlMap);
                $stmt = getStmt($db[$svrid],$sqlMap);

                if(!$stmt) JsonMsg("500","211","(makeGridSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt create fail " . $db[$svrid]->errno . " -> " . $db[$svrid]->error);
                

                try{
                    if(!$stmt->execute())JsonMsg("500","212","(makeGridSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt execute fail " . $stmt->error);
                }catch(PDOException $e){
                    JsonMsg("500","214","(makeGridSaveJsonArray) PDOException " . $tmpSql["SQLID"] . " stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
                }catch(mysqli_sql_exception $e){
                    JsonMsg("500","215","(makeGridSaveJsonArray) mysqli_sql_exception " . $tmpSql["SQLID"] . " stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
                }catch(Exception $e){
                    JsonMsg("500","216","(makeGridSaveJsonArray) Exception " . $tmpSql["SQLID"] . " stmt execute fail - " . $e->getMessage() . "(ErrorCode=" . $e->getCode() . ")" );
                }

                //SUB 쿼리는 리턴정보에 넣지 않음.
                alog("  PARENT_FNCTYPE = " . $tmpSql["PARENT_FNCTYPE"]);                    
                if($tmpSql["PARENT_FNCTYPE"] == ""){
                    //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야

                    if($stmt instanceof PDOStatement){
                        $to_affected_rows = $stmt->rowCount();
                    }else{
                        $to_affected_rows = $db[$svrid]->affected_rows;
                    }
                
                    //[로그 저장용]
                    $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;                
                
                    $to_row["COLID"] = "";
                    if($row["userdata"] == "inserted"){
                        if($map["SEQYN"] == "Y"){
                            //sequence nextval이 sql에 있으면
                            if($sqlMap["SEQ_NM"] != "" ){
                                //현재 db 세션에서 sequence 정보가져오기
                                if(getDbType($db[$svrid]) == "mariadb"){
                                    $sqlSeq = "select lastval(" . $sqlMap["SEQ_NM"] . ") as seq_val ";
                                }else if(getDbType($db[$svrid]) == "postgresql"){
                                    $sqlSeq = "select currval('" . $sqlMap["SEQ_NM"] . "') as seq_val  ";
                                }
                                $sqlMapSeq = getSqlParam($sqlSeq,'','');
                                //var_dump($sqlMapSeq);
                                $stmtSeq = getStmt($db[$svrid],$sqlMapSeq);
                                if(!$stmtSeq) JsonMsg("500","221","(makeGridSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt create fail " . $db[$svrid]->errno . " -> " . $db[$svrid]->error);                
                                $to_row["COLID"] = getStmtArray($stmtSeq)[0]["seq_val"];
                                closeStmt($stmtSeq);

                            }else if($stmt instanceof PDOStatement){
                                alog("SEQYN Y : " . $db[$svrid]->lastInsertId());
                                $to_row["COLID"]=$db[$svrid]->lastInsertId(); //insert문인 경우 insert id받기                            
                            }else{
                                alog("SEQYN Y : " . $db[$svrid]->insert_id);
                                $to_row["COLID"]=$db[$svrid]->insert_id; //insert문인 경우 insert id받기
                            }

                        }else{
                            alog("SEQYN N : " . $to_row[$map["KEYCOLID"]]);
                            $to_row["COLID"]=$to_row[$map["KEYCOLID"]]; //사용자 입력 key컬럼을 rowid 로
                        }
                    }

                    $tarr = array("OLD_ID"=>$row["@attributes"]["id"],"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>$row["userdata"],"AFFECTED_ROWS"=>$to_affected_rows);
    
                    $RtnVal->ROWS[$RtnCnt] = $tarr;
                    $RtnCnt++;
                }
                closeStmt($stmt);
    
 
            }
			


		}

		//결과 JSON 화면 출력
		$RtnVal->GRP_TYPE = "GRID";
	    $RtnVal->SEQ_COLID = ($map["SEQYN"] == "Y")?$map["KEYCOLID"]:"";

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}



	function makeGridjqxSaveJsonArray($map,&$db){
        global $REQ,$CFG, $PGM_CFG;
        
        //alog("^^^ COLORD : " . $map["COLORD"]);
        $colord_array = explode(",",$map["COLORD"]);
        //$colcrypt_array = explode(",",$map["COLCRYPT"]);        
        $colcrypt_array = $map["COLCRYPT"];
        //alog("^^^ colord_array count : " . count($colord_array));

		$json_array_last = $map["JSON"];

        $RtnVal = new stdclass();
		$RtnCnt = 0;
		alog("json sizeof : " . sizeof($json_array_last));
		for($i=0;$i<sizeof($json_array_last);$i++){

			$row = $json_array_last[$i];
			alog("        i : " . $i);
			alog("        changeCud : " . $row["changeCud"]);

			//현재 그리드 line을 bind 배열에 담기
			$to_row = null;
			$to_coltype = null;
			$sql = null;
			for($j=0;$j<sizeof($colord_array);$j++){
                $colNm = $colord_array[$j];
				$colValue = $row[$colord_array[$j]];
				if(is_array($colValue)){
					$to_row[$colNm] = "";
				}else{
                    //암호화 컬럼에 존재 하는지 확인
                    $to_row[$colNm] = makeParamEnc($colNm,$colValue,$colcrypt_array);
				}
            }
            //echo jsonView($to_row);

            //SQL 갯수만큼 루프
            $mapLoop = null;            
            switch($row["changeCud"]){
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
                    alog("         changeCud no match : " . $row["changeCud"]);           
                    break;
            }
            alog("        mapLoop size : " . sizeof($mapLoop));   

            if(sizeof($mapLoop)==0)JsonMsg("500","899","(makeGridjqxSaveJsonArray) 명령어를 처리할 SQL이 없습니다.");

            for($k=0;$k<sizeof($mapLoop);$k++){
                $tmpSql = $mapLoop[$k];
                
                $to_coltype = $tmpSql["BINDTYPE"];
                //LogMaster::log("        to_coltype : " . $to_coltype);
                $svrid = $tmpSql["SVRID"];
                $sql = $tmpSql["SQLTXT"];
                

                if( getParamCnt($sql) != strlen(str_replace(" ","",$to_coltype)) )JsonMsg("500","190","(makeGridjqxSaveJsonArray) " . $tmpSql["SQLID"] . "  sql파라미터와 파라미터타입수가 불일치.");

                alog("svrid : " . $svrid);
                //alog("  REQ.URL : " . $REQ["URL"]);             
                //alog("  to_row.URL : " . $to_row["URL"]);             
                    

                $tArr = array_merge($REQ,$to_row);
                //alog("  array_merge() tArr.URL : " . $tArr["URL"]);

                //$stmt = makeStmt($db[$svrid], $sql, $to_coltype, array_merge($REQ,$to_row));
                
                $sqlMap = getSqlParam($sql,$to_coltype,array_merge($REQ,$to_row));
                //echo "<pre>" . jsonView($sqlMap);
                $stmt = getStmt($db[$svrid],$sqlMap);

                if(!$stmt) JsonMsg("500","211","(makeGridjqxSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt create fail " . $db[$svrid]->errno . " -> " . $db[$svrid]->error);
                
                if(!$stmt->execute())JsonMsg("500","212","(makeGridjqxSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt execute fail " . $stmt->error);

                //SUB 쿼리는 리턴정보에 넣지 않음.
                alog("  PARENT_FNCTYPE = " . $tmpSql["PARENT_FNCTYPE"]);                    
                if($tmpSql["PARENT_FNCTYPE"] == ""){
                    //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야

                    if($stmt instanceof PDOStatement){
                        $to_affected_rows = $stmt->rowCount();
                    }else{
                        $to_affected_rows = $db[$svrid]->affected_rows;
                    }
                
                    //[로그 저장용]
                    $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;                
                
                    $to_row["COLID"] = "";
                    if($row["changeCud"] == "inserted"){
                        if($map["SEQYN"] == "Y"){
                            //sequence nextval이 sql에 있으면
                            if($sqlMap["SEQ_NM"] != "" ){
                                //현재 db 세션에서 sequence 정보가져오기
                                if(getDbType($db[$svrid]) == "mariadb"){
                                    $sqlSeq = "select lastval(" . $sqlMap["SEQ_NM"] . ") as seq_val ";
                                }else if(getDbType($db[$svrid]) == "postgresql"){
                                    $sqlSeq = "select currval('" . $sqlMap["SEQ_NM"] . "') as seq_val  ";
                                }
                                $sqlMapSeq = getSqlParam($sqlSeq,'','');
                                //var_dump($sqlMapSeq);
                                $stmtSeq = getStmt($db[$svrid],$sqlMapSeq);
                                if(!$stmtSeq) JsonMsg("500","221","(makeGridSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt create fail " . $db[$svrid]->errno . " -> " . $db[$svrid]->error);                
                                $to_row["COLID"] = getStmtArray($stmtSeq)[0]["seq_val"];
                                closeStmt($stmtSeq);

                            }else if($stmt instanceof PDOStatement){
                                alog("SEQYN Y : " . $db[$svrid]->lastInsertId());
                                $to_row["COLID"]=$db[$svrid]->lastInsertId(); //insert문인 경우 insert id받기                            
                            }else{
                                alog("SEQYN Y : " . $db[$svrid]->insert_id);
                                $to_row["COLID"]=$db[$svrid]->insert_id; //insert문인 경우 insert id받기
                            }

                        }else{
                            alog("SEQYN N : " . $to_row[$map["KEYCOLID"]]);
                            $to_row["COLID"]=$to_row[$map["KEYCOLID"]]; //사용자 입력 key컬럼을 rowid 로
                        }
                    }

                    $tarr = array("OLD_ID"=>$to_row[$map["KEYCOLID"]],"NEW_ID"=>$to_row["COLID"],"USER_DATA"=>$row["changeCud"],"AFFECTED_ROWS"=>$to_affected_rows);
    
                    $RtnVal->ROWS[$RtnCnt] = $tarr;
                    $RtnCnt++;
                }
                closeStmt($stmt);
    
 
            }
			


		}

		//결과 JSON 화면 출력
		$RtnVal->GRP_TYPE = "GRIDJQX";
	    $RtnVal->SEQ_COLID = ($map["SEQYN"] == "Y")?$map["KEYCOLID"]:"";

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}



	function makeGridwixSaveJsonArray($map,&$db){
        global $REQ,$CFG, $PGM_CFG;
        alog("makeGridwixSaveJsonArray().................................start");
        
        //alog("^^^ COLORD : " . $map["COLORD"]);
        $colord_array = explode(",",$map["COLORD"]);
        //$colcrypt_array = explode(",",$map["COLCRYPT"]);        
        $colcrypt_array = $map["COLCRYPT"];
        //alog("^^^ colord_array count : " . count($colord_array));

		$json_array_last = $map["JSON"];

        $RtnVal = new stdclass();
		$RtnCnt = 0;
		alog("json sizeof : " . sizeof($json_array_last));
		for($i=0;$i<sizeof($json_array_last);$i++){

			$row = $json_array_last[$i];
			alog("        i : " . $i);
			alog("        changeCud : " . $row["changeCud"]);

			//현재 그리드 line을 bind 배열에 담기
			$to_row = null;
			$to_coltype = null;
			$sql = null;
			for($j=0;$j<sizeof($colord_array);$j++){
                $colNm = $colord_array[$j];
				$colValue = $row[$colord_array[$j]];
				if(is_array($colValue)){
					$to_row[$colNm] = "";
				}else{
                    //암호화 컬럼에 존재 하는지 확인
                    $to_row[$colNm] = makeParamEnc($colNm,$colValue,$colcrypt_array);
				}
            }
            //id컬럼은 무조건 받아오기.
            $to_row["id"] = $row["id"];

            //echo jsonView($to_row);

            //SQL 갯수만큼 루프
            $mapLoop = null;            
            switch($row["changeCud"]){
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
                    alog("         changeCud no match : " . $row["changeCud"]);           
                    break;
            }
            alog("        mapLoop size : " . sizeof($mapLoop));   

            if(sizeof($mapLoop)==0)JsonMsg("500","899","(makeGridwixSaveJsonArray) 명령어를 처리할 SQL이 없습니다.");

            for($k=0;$k<sizeof($mapLoop);$k++){
                $tmpSql = $mapLoop[$k];
                
                $to_coltype = $tmpSql["BINDTYPE"];
                //LogMaster::log("        to_coltype : " . $to_coltype);
                $svrid = $tmpSql["SVRID"];
                $sql = $tmpSql["SQLTXT"];
                

                if( getParamCnt($sql) != strlen(str_replace(" ","",$to_coltype)) )JsonMsg("500","190","(makeGridwixSaveJsonArray) " . $tmpSql["SQLID"] . "  sql파라미터와 파라미터타입수가 불일치.");

                alog("svrid : " . $svrid);
                //alog("  REQ.URL : " . $REQ["URL"]);             
                //alog("  to_row.URL : " . $to_row["URL"]);             
                    

                $tArr = array_merge($REQ,$to_row);
                //alog("  array_merge() tArr.URL : " . $tArr["URL"]);

                //$stmt = makeStmt($db[$svrid], $sql, $to_coltype, array_merge($REQ,$to_row));
                
                $sqlMap = getSqlParam($sql,$to_coltype,array_merge($REQ,$to_row));
                alog($sqlMap["DEBUG_SQL"]);
                //echo "<pre>" . jsonView($sqlMap);
                $stmt = getStmt($db[$svrid],$sqlMap);

                if(!$stmt) JsonMsg("500","211","(makeGridwixSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt create fail " . $db[$svrid]->errno . " -> " . $db[$svrid]->error);
                
                if(!$stmt->execute())JsonMsg("500","212","(makeGridwixSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt execute fail " . $stmt->error);

                //SUB 쿼리는 리턴정보에 넣지 않음.
                alog("  PARENT_FNCTYPE = " . $tmpSql["PARENT_FNCTYPE"]);                    
                if($tmpSql["PARENT_FNCTYPE"] == ""){
                    //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야

                    if($stmt instanceof PDOStatement){
                        $to_affected_rows = $stmt->rowCount();
                    }else{
                        $to_affected_rows = $db[$svrid]->affected_rows;
                    }
                
                    //[로그 저장용]
                    $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;                
                
                    $to_row["COLID"] = "";
                    if($row["changeCud"] == "inserted"){
                        if($map["SEQYN"] == "Y"){
                            //sequence nextval이 sql에 있으면
                            if($sqlMap["SEQ_NM"] != "" ){
                                //현재 db 세션에서 sequence 정보가져오기
                                if(getDbType($db[$svrid]) == "mariadb"){
                                    $sqlSeq = "select lastval(" . $sqlMap["SEQ_NM"] . ") as seq_val ";
                                }else if(getDbType($db[$svrid]) == "postgresql"){
                                    $sqlSeq = "select currval('" . $sqlMap["SEQ_NM"] . "') as seq_val  ";
                                }
                                $sqlMapSeq = getSqlParam($sqlSeq,'','');
                                //var_dump($sqlMapSeq);
                                $stmtSeq = getStmt($db[$svrid],$sqlMapSeq);
                                if(!$stmtSeq) JsonMsg("500","221","(makeGridwixSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt create fail " . $db[$svrid]->errno . " -> " . $db[$svrid]->error);                
                                $to_row["COLID"] = getStmtArray($stmtSeq)[0]["seq_val"];
                                closeStmt($stmtSeq);

                            }else if($stmt instanceof PDOStatement){
                                alog("[PDO]SEQYN Y : " . $db[$svrid]->lastInsertId());
                                $to_row["COLID"]=$db[$svrid]->lastInsertId(); //insert문인 경우 insert id받기                            
                            }else{
                                alog("[MysqlI]SEQYN Y : " . $db[$svrid]->insert_id);
                                $to_row["COLID"]=$db[$svrid]->insert_id; //insert문인 경우 insert id받기
                            }

                        }else{
                            alog("SEQYN N : " . $to_row[$map["KEYCOLID"]]);
                            $to_row["COLID"]=$to_row[$map["KEYCOLID"]]; //사용자 입력 key컬럼을 rowid 로
                        }
                    }

                    $tarr = array(
                        "ROW_ID"        =>  $to_row["id"]
                        ,"OLD_ID"       =>  $to_row[$map["KEYCOLID"]]
                        ,"NEW_ID"       =>  $to_row["COLID"]
                        ,"USER_DATA"    =>  $row["changeCud"]
                        ,"AFFECTED_ROWS"=>  $to_affected_rows
                    );
    
                    $RtnVal->ROWS[$RtnCnt] = $tarr;
                    $RtnCnt++;
                }
                closeStmt($stmt);
    
 
            }
			


		}

		//결과 JSON 화면 출력
		$RtnVal->GRP_TYPE = "GRIDWIX";
	    $RtnVal->SEQ_COLID = ($map["SEQYN"] == "Y")?$map["KEYCOLID"]:"";

		//$RtnVal = json_encode($RtnVal);
		return $RtnVal;

	}


    function makeSqlParamEnc($tSql, $tReq, $colcrypt_array){
        global $CFG;
        //alog("makeSqlParamEnc()........................................................start");
        $k = 0;
        $to_sql = $tSql;
        $RtnVal = array();

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

    function makeParamEnc($tKey, $tValue, $colcrypt_array){
        global $CFG;
        //alog("makeParamEnc()........................................................start");
        $RtnVal = null;

        $fullParam = $tKey;
        if(strpos($tKey,"-")>0){
            $colid = explode("-",$tKey)[1];
        }else{
            $colid = $tKey;
        }

        //colid가 암호화 대상이면 암호화 처리
        if( $colcrypt_array[trim($colid)] == "CRYPT" ){
            //양방향 암호화                   
            alog(" CRYPT");
            $RtnVal = aes_encrypt($tValue,$CFG["CFG_SEC_KEY"]);
        }else if( $colcrypt_array[trim($colid)] == "HASH" ){
            //일방향 암호화
            alog(" HASH");                
            $RtnVal = pwd_hash($tValue,$CFG["CFG_SEC_SALT"]);
        }else{
            //평문
            $RtnVal = $tValue;
        }     
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
        }else if( $colCrypt[trim($k)] == "CDATA" ){
            //Tag가 있는 컬럼.(Cdata더하기)
            $RtnVal = xmlCdataAdd($tValue);            
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
        $RtnVal = new stdclass();
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
        $RtnVal = new stdclass();
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
        $RtnVal = new stdclass();
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
        $RtnVal = new stdclass();
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
        $tParamEnc = makeSqlParamEnc($map["SQL"]["R"]["SQLTXT"], $REQ, $colcrypt_array);

        $sqlMap = getSqlParam($map["SQL"]["R"]["SQLTXT"],$map["SQL"]["R"]["BINDTYPE"],$tParamEnc);
        //echo "<pre>" . jsonView($sqlMap);
        $stmt = getStmt($db[$map["SQL"]["R"]["SVRID"]],$sqlMap);

		//$stmt = makeStmt($db[$map["SQL"]["R"]["SVRID"]],$map["SQL"]["R"]["SQLTXT"], $map["SQL"]["R"]["BINDTYPE"], $tParamEnc);
		if(!$stmt)   JsonMsg("500","300","stmt 생성 실패" . $db->errno . " -> " . $db->error);

		//alog("make_detail_read_json-------------------------------start");
		if(!$stmt->execute())JsonMsg("500","310","stmt 실행 실패" . $db->errno . " -> " . $db->error);

        //$stmt->store_result();
        //$colcrypt_array = explode(",",$map["COLCRYPT"]);   

        //$PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $stmt->num_rows;


        if($stmt instanceOf PDOStatement){
            //pdo_mysql
            //컬럼 정보 꺼내오기
            for($k=0;$k<$stmt->columnCount();$k++)
            {
                $colNms[] = $stmt->getColumnMeta($k)["name"];
            }                    
        }else{
            //mysqli driver
            //컬럼 정보 꺼내오기
            $meta = $stmt->result_metadata();
            while($field = $meta->fetch_field())
            {
                $colNms[] = $field->name;
            }
        }                

        $arr = getStmtArrayNum($stmt)[0];
        
        for($t=0;$t<count($arr);$t++){
            //alog("	fetch foreach : $key = $value");
            $RtnVal->RTN_DATA[$colNms[$t]] = makeParamDec($colNms[$t], $arr[$t], $colcrypt_array);		
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

        $RtnVal = new stdclass();
        
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
            $tParamEnc = makeSqlParamEnc($tmpSql["SQLTXT"], $REQ, $colcrypt_array);

            //$stmt = makeStmt($db[$tmpSql["SVRID"]],$tmpSql["SQLTXT"], $tmpSql["BINDTYPE"], $tParamEnc);

            $sqlMap = getSqlParam($tmpSql["SQLTXT"],$tmpSql["BINDTYPE"],$tParamEnc);
            $stmt = getStmt($db[$tmpSql["SVRID"]],$sqlMap);
            if(!$stmt)  JsonMsg("500","300","(makeFormviewSearchJsonArray) " . $tmpSql["SQLID"] . " stmt 생성 실패" . $db->errno . " -> " . $db->error);

            //alog("make_detail_read_json-------------------------------start");
            if(!$stmt->execute())JsonMsg("500","310","(makeFormviewSearchJsonArray) " . $tmpSql["SQLID"] . " stmt 실행 실패" . $db->errno . " -> " . $db->error);

            //main인 경우만
            if( $tmpSql["PARENT_FNCTYPE"] == ""){

                //$stmt->store_result();
                //$colcrypt_array = explode(",",$map["COLCRYPT"]);   

                //$PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $stmt->num_rows;
                if($stmt instanceOf PDOStatement){
                    //pdo_mysql
                    //컬럼 정보 꺼내오기
                    for($k=0;$k<$stmt->columnCount();$k++)
                    {
                        $colNms[] = $stmt->getColumnMeta($k)["name"];
                    }                    
                }else{
                    //mysqli driver
                    //컬럼 정보 꺼내오기
                    $meta = $stmt->result_metadata();
                    while($field = $meta->fetch_field())
                    {
                        $colNms[] = $field->name;
                    }
                }                

                $arr = getStmtArrayNum($stmt)[0];
                
                for($t=0;$t<count($arr);$t++){
                    //alog("	fetch foreach : $key = $value");
                    $RtnVal->RTN_DATA[$colNms[$t]] = makeParamDec($colNms[$t], $arr[$t], $colcrypt_array);		
                }
            }
            closeStmt($stmt);;
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

        alog("svrid =" . $svrid);
        alog("sql =" . $sqltxt);
        alog("bindtype =" . $bindtype);
        

        //폼 입력값에 암호화 컬럼 있는지 검사해서 암호화 처리
        $colcrypt_array = $map["COLCRYPT"];           
        $tParamEnc = makeSqlParamEnc($sqltxt, $REQ, $colcrypt_array);

        $sqlMap = getSqlParam($sqltxt,$bindtype,$tParamEnc);
        $stmt = getStmt($db[$svrid],$sqlMap);

		//$stmt = makeStmt($db[$svrid], $sqltxt, $bindtype, $tParamEnc);
		if(!$stmt)  JsonMsg("500","400","stmt 생성 실패" . $db->errno . " -> " . $db->error);
		
		if(!$stmt->execute())JsonMsg("500","410","stmt 실행 실패" . $stmt->errno . " -> " . $stmt->error);
		
        //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
        if($stmt instanceof PDOStatement){
            $to_affected_rows = $stmt->rowCount();
        }else{
            $to_affected_rows = $db[$svrid]->affected_rows;
        }

        //$to_affected_rows = $db->affected_rows;
        alog("  to_affected_rows = ". $to_affected_rows);

        //$PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;

        $RtnVal = new stdclass();
        if($map["FNCTYPE"] == "C" && $map["SEQYN"] == "Y"){
            if($stmt instanceof PDOStatement){
                alog("SEQYN Y : " . $db->lastInsertId());
                $RtnVal->COLID = $db->lastInsertId(); //insert문인 경우 insert id받기                            
            }else{
                alog("SEQYN Y : " . $db->insert_id);
                $RtnVal->COLID = $db->insert_id;//insert문인 경우 insert id받기
            }            
        }

		closeStmt($stmt);;

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
            $tParamEnc = makeSqlParamEnc($tmpSql["SQLTXT"], $REQ, $colcrypt_array);
    
            //$stmt = makeStmt($db[$tmpSql["SVRID"]], $tmpSql["SQLTXT"], $tmpSql["BINDTYPE"], $tParamEnc);

            $sqlMap = getSqlParam($tmpSql["SQLTXT"],$tmpSql["BINDTYPE"],$tParamEnc);

            $stmt = getStmt($db[$tmpSql["SVRID"]],$sqlMap);

            if(!$stmt)  JsonMsg("500","400","(makeFormviewSaveJsonArray)" . $tmpSql["SQLID"] . " stmt 생성 실패" . $db->errno . " -> " . $db->error);
            
            if(!$stmt->execute())JsonMsg("500","410","(makeFormviewSaveJsonArray)" . $tmpSql["SQLID"] . " stmt 실행 실패" . $stmt->errno . " -> " . $stmt->error);
            
            //main인 경우만
            if( $tmpSql["PARENT_FNCTYPE"] == ""){

                //echo "\n db affected_rows : " .  $db->affected_rows; //stmt를 클로즈 하기 전에 해야
                if($stmt instanceof PDOStatement){
                    $to_affected_rows = $stmt->rowCount();
                }else{
                    $to_affected_rows = $db[$tmpSql["SVRID"]]->affected_rows;
                }
        
                //$to_affected_rows = $db->affected_rows;
                alog("  to_affected_rows = ". $to_affected_rows);
        
                $PGM_CFG["SQLTXT"][sizeof($PGM_CFG["SQLTXT"])-1]["ROW_CNT"] = $to_affected_rows;
        
                //alog("  FNCTYPE = ". $map["FNCTYPE"] );
                //alog("  SEQYN = ". $map["SEQYN"] );
                if($map["FNCTYPE"] == "C" && $map["SEQYN"] == "Y"){

                    //sequence nextval이 sql에 있으면
                    if($sqlMap["SEQ_NM"] !="" ){
                        //현재 db 세션에서 sequence 정보가져오기
                        if(getDbType($db[$tmpSql["SVRID"]]) == "mariadb"){
                            $sqlSeq = "select lastval(" . $sqlMap["SEQ_NM"] . ") as seq_val ";
                        }else if(getDbType($db[$tmpSql["SVRID"]]) == "postgresql"){
                            $sqlSeq = "select currval('" . $sqlMap["SEQ_NM"] . "') as seq_val  ";
                        }
                        $sqlMapSeq = getSqlParam($sqlSeq,'','');
                        //var_dump($sqlMapSeq);
                        $stmtSeq = getStmt($db[$tmpSql["SVRID"]],$sqlMapSeq);
                        if(!$stmtSeq) JsonMsg("500","221","(makeFormviewSaveJsonArray) " . $tmpSql["SQLID"] . "  stmt create fail " . $db[$svrid]->errno . " -> " . $db[$svrid]->error);                
                        $RtnVal->NEW_ID = getStmtArray($stmtSeq)[0]["seq_val"];
                        closeStmt($stmtSeq);
                                            
                    }else if($stmt instanceof PDOStatement){
                        alog("SEQYN Y : " . $db->lastInsertId());
                        $RtnVal->NEW_ID = $db->lastInsertId(); //insert문인 경우 insert id받기                            
                    }else{
                        alog("SEQYN Y : " . $db->insert_id);
                        $RtnVal->NEW_ID = $db->insert_id;//insert문인 경우 insert id받기
                    }            
                }
        
                $RtnVal->RTN_DATA = $to_affected_rows;      
                $RtnVal->GRP_TYPE = "FORMVIEW";
                $RtnVal->SEQ_COLID = ($map["SEQYN"] == "Y")?$map["KEYCOLID"]:"";
            }
            closeStmt($stmt);;
    
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