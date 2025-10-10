<?php
    header("Content-Type: text/html; charset=UTF-8");
    header("Cache-Control:no-cache");
    header("Pragma:no-cache");

    $CFG = include_once("../common/include/incConfig.php");;

    require_once($CFG["CFG_LIBS_VENDOR"]);

    require_once("../common/include/incUtil.php");
    require_once("../common/include/incDB.php");
    require_once("../common/include/incUser.php");
    require_once("../common/include/incSec.php");

    //alog("cg_clode_json.php...............111");
    //ServerViewTxt("N","N","Y","Y");

    $resToken = uniqid();


    $log = getLoggerStdout(
        array(
        "LIST_NM"=>"log_CG"
        , "PGM_ID"=>"CODE_JSON"
        , "REQTOKEN" => $reqToken
        , "RESTOKEN" => $resToken
        , "LOG_LEVEL" => Monolog\Logger::INFO
        )
    );




    $log->info("cg_clode_json.php...............222");

    //그룹ID받기
    $REQ["PJTSEQ"] = $_GET['PJTSEQ'];
    $REQ["PGMSEQ"] = $_GET['PGMSEQ'];
    $REQ["PCD"] = $_GET['PCD'];
    $REQ["CD"] = $_GET['CD'];
    $REQ["GRPSEQ"] = $_GET['GRPSEQ']; //GRP선택시 INHERIT        
    $REQ["FNCSEQ"] = $_GET['FNCSEQ']; //FNC선택시 SVC
    $REQ["SVCSEQ"] = $_GET['SVCSEQ']; //SVC선택시 IO

    //로그인 정보 받기
    $userSeq = getUserSeq();


    switch($REQ["PCD"]){
        case "PGMSEQ_POPUP":
        case "VALIDSEQ":
        case "PSQLSEQ":
        case "GETGRPLIST":
        case "GETSVCSQLLIST":

            //프로젝트의 데이터소스 정보 가져오기 
            //프로젝트 정보에서 데이터소스 이름 가져오기
            $db2 = getDbConn($CFG["CFG_DB"]["CGCORE"]);
            //var_dump($CFG["CFG_DB"]["CGCORE"]);    
            $sql = "select * from CG_PJTINFO where PJTSEQ = #{PJTSEQ}";
            //echo $sql;

            $sqlMap = getSqlParam($sql,$coltype="i",$REQ);
            //echo "<pre>" . json_encode($sqlMap,JSON_PRETTY_PRINT);
            //$stmt2 = $db2->prepare($sqlMap["TO_SQL"]);

            $stmt2 = getStmt($db2,$sqlMap);

            //$stmt2 = makeStmt($db2,$sql,$coltype="i",$REQ);
            $pjtInfo = getStmtArray($stmt2)[0];
            closeStmt($stmt2);
            closeDb($db2);
            //var_dump($pjtInfo);
            if($pjtInfo["DSNM"] == "")JsonMsg("500","100","해당 프로젝트의 데이터소스 정보가 없습니다.");

            //프로젝트의 데이터소스 정보 가져오기
            $log->debug("데이터소스 : " . $pjtInfo["DSNM"]);
            $db=getDbConn($CFG["CFG_DB"][$pjtInfo["DSNM"]]);
            $log->debug("데이터소스 드라이버 : " . $CFG["CFG_DB"][$pjtInfo["DSNM"]]["DRIVER"]);
            break;
        default:
            $log->debug("데이터소스 : CGCORE");
            $db=getDbConn($CFG["CFG_DB"]["CGCORE"]);
            $log->debug("데이터소스 드라이버 : " . $CFG["CFG_DB"]["CGCORE"]["DRIVER"]);            
            break;
    }

    //PCD가 SVRSEQ이면 서버 목록 가져오기
    if($REQ["PCD"] =="VALIDSEQ" ){
        $to_coltype = "i";
        $sql = " select VALIDSEQ as CD, concat(SUBSTRING(DATATYPE,1,1),' ', VALIDNM) as NM from CG_VALID where PJTSEQ = #{PJTSEQ} order by DATATYPE, VALIDORD asc";

    }else if($REQ["PCD"] =="SVRSEQ" ){
        $to_coltype = "";
        $sql = sprintf("
             select SVRSEQ as CD, SVRNM as NM from CG_SVR  order by SVRSEQ asc
             ");
            // /addSqlSlashes($userSeq)
        //alog($sql);
    }else if($REQ["PCD"] =="PGMSEQ_POPUP" ){
        $to_coltype = "";
        $sql = sprintf("
            select PGMID as CD, concat(PGMNM,'(',PGMID,')') as NM from CG_PGMINFO where PGMTYPE='POPUP' order by PGMNM desc
            "
            );

    }else if($REQ["PCD"] =="PSQLSEQ" ){
        $to_coltype = "ii";
        $sql = sprintf("
            select SQLSEQ as CD, SQLID as NM from CG_PGMSQL 
            where PJTSEQ = #{PJTSEQ} and PGMSEQ = #{PGMSEQ} order by SQLORD asc
            "
            );

    }else if($REQ["FNCSEQ"] !="" || $REQ["GRPSEQ"] !="" ){
        //SVC에서 사용할 GRP목록 가져오기
        $to_coltype = "ii";
        $sql = " select GRPID as CD,GRPID as NM from CG_PGMGRP where PJTSEQ = #{PJTSEQ} and PGMSEQ = #{PGMSEQ}  ORDER BY GRPORD ASC   ";
    
    }else if($REQ["PCD"] =="GETSVCSQLLIST" ){
        //SQLR에서 사용할 SQL목록 가져오기
        $to_coltype = "ii";
        $sql = " select SQLSEQ as CD,SQLID as NM from CG_PGMSQL where PJTSEQ = #{PJTSEQ} and PGMSEQ = #{PGMSEQ} and (PSQLSEQ is null or PSQLSEQ = 0) ORDER BY SQLORD ASC   ";

    }else if($REQ["PCD"] =="FILESTORE" ){
        //SQLR에서 사용할 SQL목록 가져오기
        $to_coltype = "";
        $sql = " select STOREID as CD, STORENM as NM from CG_FILESTORE where USEYN = 'Y' and DELYN = 'N'  ";
    }else if($REQ["CD"] != "" ){
        //일반 코드가져오기
        $to_coltype = "ss";
        $sql = " select * from CG_CODED where  PCD = #{PCD} and CD = #{CD} and DELYN = 'N' and USEYN='Y' ORDER BY   ORD ASC   ";
    }else{
        //일반 코드가져오기
        $to_coltype = "s";
        $sql = " select CD,NM from CG_CODED where  PCD = #{PCD} and DELYN = 'N' and USEYN='Y' ORDER BY   ORD ASC   ";
    } 

    alog("cg_code_json.php...............333");

    //$stmt = make_stmt($db,$sql, $to_coltype, $REQ);

    //alog("cg_clode_json.php...............444");

    //if(!$stmt)JsonMsg("500","101","stmt 생성 실패 stmt " . $stmt->errno . " -> " . $stmt->error . ", db " . $db->errno . " -> " . $db->error);

    //echo make_grid_read_json($stmt,1);

    //echo "<br>111";

    //echo "<BR>server_info=" . $db->server_info;
    //echo "<BR>host_info=" . $db->host_info;

    //$log->info("111");
    $sqlMap = getSqlParam($sql,$to_coltype, $REQ);

    //echo "<pre>" . json_encode( $sqlMap,JSON_PRETTY_PRINT );
    //$log->info("222");

    $stmt = getStmt($db,$sqlMap);
    //$log->info("333");
    if(!$stmt)JsonMsg("500","102", "cd_code_json.php (MysqlI) stmt null error : stmt is " . $stmt->errno . " > " . $stmt->error . ", db is " . $db->errno . " > " . $db->error);
    //$log->info("444");

    //var_dump($stmt);
    //$stmt2 = makeStmt($db2,$sql,$coltype="i",$REQ);
    $RtnVal = new stdClass();
    $RtnVal->RTN_CD = "200";
    $RtnVal->ERR_CD = "200";
    if($REQ["CD"] != ""){
        $RtnVal->RTN_DATA = getStmtArray($stmt)[0];
    }else{
        $RtnVal->RTN_DATA = new stdClass();
        $RtnVal->RTN_DATA->rows = getStmtArrayNum($stmt);
    }
    echo json_encode($RtnVal);

    //alog("cg_clode_json.php...............555");

    closeDb($db);
?>