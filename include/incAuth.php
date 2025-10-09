<?php

//redis에 모두 넣기
//require_once($CFG["CFG_LIBS_PATH_REDIS"]);

class authObject
{
	private $REDIS;
    private $DB;
    public $LAUTH_SEQ;
    private $PREFIX_SESSION_ID;

	//생성자
	function __construct(){
        global $CFG;
        //$CFG_AUTH_LOG, $CFG_AUTH_REDIS, $CFG_SID_PREFIX;

		alog("authLog-__construct");

        $this->PREFIX_SESSION_ID = "SID_" . $CFG["CFG_SID_PREFIX"] . "_";//세션유저 프리픽스

        if($CFG["CFG_AUTH_LOG"] == "DB"){
            $this->DB = db_obj_open(getDbSvrInfo("DATING"));
        }else if($CFG["CFG_AUTH_LOG"] == "REDIS"){
            Predis\Autoloader::register();
            
            //OLD
            //$this->REDIS = new Predis\Client($CFG["CFG_AUTH_REDIS"]);    

            //NEW
            if($CFG["REDIS_PASSWD"] != ""){
                $this->REDIS = new Predis\Client(
                    array(
                        'scheme' => 'tcp',
                        'host'   => $CFG["REDIS_HOST"],
                        'port'   => $CFG["REDIS_PORT"],
                        'password'   => $CFG["REDIS_PASSWD"],
                        'timeout' => 1
                    ));
            }else{
                $this->REDIS = new Predis\Client(
                    array(
                        'scheme' => 'tcp',
                        'host'   => $CFG["REDIS_HOST"],
                        'port'   => $CFG["REDIS_PORT"],
                        'timeout' => 1
                    ));
            }

        }else{
            JsonMsg("500","100","[authLog] __construct() .......................CFG_AUTH_LOG 정의가 잘못되었습니다.");
        }
	}
	//파괴자
	function __destruct(){
		alog("authLog-__destruct");

        //갱신 내용 알리기
        //if($this->REDIS)$this->REDIS->publish("PUBSUB_AUTH_LOG","NEWMSG");   

        //메모리 비우기
        if($this->REDIS)$this->REDIS->disconnect();
        unset($this->REDIS);
        
        //메모리 비우기
		if($this->DB)$this->DB->close();
		unset($this->DB);
	}
	function __toString(){
		alog("authLog-__toString");
    }
    
    //마지막 로그인 세션 세팅
    function setLastSession($userSeq, $sessionId){
        alog("setLastSession() " . $this->PREFIX_SESSION_ID . strval($userSeq) . " = " . $sessionId);
        if($this->REDIS)$this->REDIS->set($this->PREFIX_SESSION_ID . strval($userSeq),$sessionId);
    }

    //마지막 로그인 세션 가져오기
    function getLastSession($userSeq){
        if($this->REDIS){
            return $this->REDIS->get($this->PREFIX_SESSION_ID . strval($userSeq));
        }else{
            return null;
        }
        
    }

    //중복로그인 정상 유무 검사
    function isOneConnection(){
        if($this->REDIS){
            return ( $this->getLastSession(getUserSeq()) == session_id() )?true:false;
        }else{
            return true;
        }
    }


    function setUserAuth($tAuth){
        global $_SESSION;
        $_SESSION['CG_AUTH'] = $tAuth;
    }

    function getUserAuth(){
        /*
        array(
            "PGMID1" -> array("GRP1_FNC1", "GRP1_FNC2")
            ,"PGMID2" -> array("GRP1_FNC1", "GRP1_FNC2")
        )
        */
        global $_SESSION;
        return $_SESSION['CG_AUTH'];
    }
    
    function isAuth($tPgmid, $tAuthid){
        global $_SESSION;
        if($_SESSION['CG_AUTH'][$tPgmid] == null){
            return false;
        }else{
            return in_array($tAuthid,$_SESSION['CG_AUTH'][$tPgmid]);
        }

    }

    
    //로그 저장
    function logUsrAuth($reqToken,$resToken,$tPgmid,$tAuth,$tSuccessYn){
        global $_SESSION, $CFG;
        //$CFG_AUTH_LOG;
        alog("##### logUsrAuth()...........................start : " . $CFG["CFG_AUTH_LOG"]);        
        $RtnVal = "";
    
        $tMap["SVR_DT"] = date("YmdHis");
        $tMap["REQ_TOKEN"] = $reqToken;
        $tMap["RES_TOKEN"] = $resToken;
        $tMap["USR_SEQ"] = $_SESSION['CG_USR_SEQ'];
        $tMap["USR_ID"] = $_SESSION['CG_USR_ID'];
        $tMap["PGMID"] = $tPgmid;
        $tMap["SUCCESS_YN"] = $tSuccessYn;
        $tMap["AUTH_ID"] = $tAuth;

        if($CFG["CFG_AUTH_LOG"] == "DB"){

            $coltype = "ssiss ss";
    
            $sql = "
                insert into CMN_LOG_AUTH (
                    REQ_TOKEN, RES_TOKEN, USR_SEQ, USR_ID, PGMID, AUTH_ID
                    , SUCCESS_YN
                    ,ADD_DT
                    ) values (
                    #{REQ_TOKEN}, #{RES_TOKEN}, #{USR_SEQ}, ifnull(#{USR_ID},0), #{PGMID}
                    , #{AUTH_ID}, #{SUCCESS_YN}
                    ,date_format(sysdate(),'%Y%m%d%H%i%s')
                    )
            ";

            $stmt = makeStmt($this->DB,$sql,$coltype,$tMap);
    
            if(!$stmt)JsonMsg("500","140","[logUsrAuth] SQL makeStmt 생성 실패 했습니다.");
        
            if(!$stmt->execute())JsonMsg("500","150","[logUsrAuth] stmt 실행 실패" . $stmt->error);
                    
            $RtnVal = $this->DB->insert_id;
        
            alog("logUsrAuth() insert_id = " . $RtnVal);
            $stmt->close();
        }else if($CFG["CFG_AUTH_LOG"] == "REDIS"){
            $redisMap = array();
            //$redisMap["SQL"] = $sql;
            //$redisMap["COLTYPE"] = $coltype;
            $redisMap["MAP"] = $tMap;
        
            //redis에 넣기
            $this->REDIS->rpush( 'log_auth', json_encode($redisMap) );  // 'fruit' LIST의 끝에 'apple'추가.
        }else{
            JsonMsg("500","200","[authLog] logUsrAuth() .......................CFG_AUTH_LOG 정의가 잘못되었습니다.");
        }
       

        return $RtnVal;
    }
    
    
    
    //로그 상세 저장
    function logUsrAuthD($reqToken,$resToken){
        global $_SESSION, $PGM_CFG, $CFG;
        //$CFG_AUTH_LOG;
        alog("##### logUsrAuthD()...........................start : " . $CFG["CFG_AUTH_LOG"]);        
        $RtnVal = "";
    
        $tMap["LAUTH_SEQ"] = $this->LAUTH_SEQ;
        alog("LAUTH_SEQ 2 = " . $this->LAUTH_SEQ);

        $tArr = $PGM_CFG['SQLTXT'];
    
        $tMap["SVR_DT"] = date("YmdHis");
        $tMap["REQ_TOKEN"] = $reqToken;
        $tMap["RES_TOKEN"] = $resToken;        
        alog("^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^count(tArr) = ". count($tArr));
    
        $startSqlNo = ($CFG["CFG_AUTH_LOG"] == "REDIS")? 0 : 1; //DB저장 방식이면 첫번째 SQL(log_auth)무시 

        for($j=$startSqlNo;$j<count($tArr);$j++){
            alog("^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^logUsrAuthD() j = ". $j);
    
            $tMap["PREPARE_SQL"] = $tArr[$j]["PREPARE_SQL"];
            alog("PREPARE_SQL = " . $tMap["PREPARE_SQL"]);
            $tMap["FULL_SQL"] = $tArr[$j]["FULL_SQL"];
            alog("FULL_SQL = " . $tMap["FULL_SQL"]);
            $tMap["ROW_CNT"] = $tArr[$j]["ROW_CNT"];
    
            $tMap["COLIDS"] = $tArr[$j]["COLIDS"];

            $tMap["PARAM_COLIDS"] = array2str($tMap["COLIDS"],", "); //중복허용

        

            if($CFG["CFG_AUTH_LOG"] == "DB"){      
                
                $tMap["DD_COLIDS"] = array2ddstr($tMap["COLIDS"],", "); //중복허용
                $tMap["PI_IN_COLIDS"] = array2pistr($tMap["COLIDS"],", "); //중복제거
                $tMap["PI_OUT_COLIDS"] = array2pistr(getSqlSelect2Array($tMap["PREPARE_SQL"]),", "); //SQL에서 SELECT 컬럼 추출하기
        
                $coltype = "ssiss ssssi";
                $sql = "
                    insert into CMN_LOG_AUTHD (
                        REQ_TOKEN, RES_TOKEN, LAUTH_SEQ, PREPARE_SQL, FULL_SQL
                        , PARAM_COLIDS, DD_COLIDS, PI_IN_COLIDS, PI_OUT_COLIDS, ROW_CNT
                        ,ADD_DT
                        ) values (
                        #{REQ_TOKEN}, #{RES_TOKEN}, #{LAUTH_SEQ}, #{PREPARE_SQL}, #{FULL_SQL}
                        , #{PARAM_COLIDS}, #{DD_COLIDS}, #{PI_IN_COLIDS}, #{PI_OUT_COLIDS}, #{ROW_CNT}
                        , date_format(sysdate(),'%Y%m%d%H%i%s')
                        )
                ";                

                $stmt = makeStmt($this->DB,$sql,$coltype,$tMap);
        
                if(!$stmt)JsonMsg("500","160","[logUsrAuthD] SQL makeStmt 생성 실패 했습니다.");
            
                if(!$stmt->execute())JsonMsg("500","170","[logUsrAuthD] stmt 실행 실패 " . $stmt->error);
                        
                //$RtnVal = $db->insert_id;
                $stmt->close();
            }else if($CFG["CFG_AUTH_LOG"] == "REDIS"){
                $redisMap = array();
                //$redisMap["SQL"] = $sql;
                //$redisMap["COLTYPE"] = $coltype;
                $redisMap["MAP"] = $tMap;
            
                $this->REDIS->rpush( 'log_authd', json_encode($redisMap) );  // 'fruit' LIST의 끝에 'apple'추가.
            }else{
                JsonMsg("500","200","[authLog] logUsrAuthD() .......................CFG_AUTH_LOG 정의가 잘못되었습니다.");
            }


        }
    
            
        return $RtnVal;
    }
    
    

}//class