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


    $log = getLogger(
        array(
        "LIST_NM"=>"log_CG"
        , "PGM_ID"=>"CODE_JSON"
        , "REQTOKEN" => $reqToken
        , "RESTOKEN" => $resToken
        , "LOG_LEVEL" => Monolog\Logger::DEBUG
        )
    );




    //alog("cg_clode_json.php...............222");

    //그룹ID받기
    $REQ["PJTSEQ"] = $_GET['PJTSEQ'];
    $REQ["PGMSEQ"] = $_GET['PGMSEQ'];
    $REQ["PCD"] = $_GET['PCD'];
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
            $stmt2 = makeStmt($db2,$sql,$coltype="i",$REQ);
            $pjtInfo = getStmtArray($stmt2)[0];
            $stmt2->close();
            $db2->close();
            //var_dump($pjtInfo);
            if($pjtInfo["DSNM"] == "")JsonMsg("500","100","해당 프로젝트의 데이터소스 정보가 없습니다.");

            //프로젝트의 데이터소스 정보 가져오기
            alog("데이터소스 : " . $pjtInfo["DSNM"]);
            $db=getDbConn($CFG["CFG_DB"][$pjtInfo["DSNM"]]);
            break;
        default:
            alog("데이터소스 : CGCORE");
            $db=getDbConn($CFG["CFG_DB"]["CGCORE"]);
            break;
    }

    //PCD가 SVRSEQ이면 서버 목록 가져오기
    if($REQ["PCD"] =="VALIDSEQ" ){
        $to_coltype = "i";
        $sql = " select VALIDSEQ as CD, concat(SUBSTRING(DATATYPE,1,1),' ', VALIDNM) as NM from CG_VALID where PJTSEQ = #PJTSEQ# order by DATATYPE, VALIDORD asc";

    }else if($REQ["PCD"] =="SVRSEQ" ){
        $to_coltype = "i";
        $sql = sprintf("
             select SVRSEQ as CD, SVRNM as NM from CG_SVR where USERSEQ = %d order by SVRSEQ asc
             "
             , addSqlSlashes($userSeq)
            );
        alog($sql);
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
            where PJTSEQ = #PJTSEQ# and PGMSEQ = #PGMSEQ# order by SQLORD asc
            "
            );

    }else if($REQ["FNCSEQ"] !="" || $REQ["GRPSEQ"] !="" ){
        //SVC에서 사용할 GRP목록 가져오기
        $to_coltype = "ii";
        $sql = " select GRPID as CD,GRPID as NM from CG_PGMGRP where PJTSEQ = #PJTSEQ# and PGMSEQ = #PGMSEQ#  ORDER BY GRPORD ASC   ";
    
    }else if($REQ["SVCSEQ"] !="" ){
        //SQLR에서 사용할 SQL목록 가져오기
        $to_coltype = "ii";
        $sql = " select SQLSEQ as CD,SQLID as NM from CG_PGMSQL where PJTSEQ = #PJTSEQ# and PGMSEQ = #PGMSEQ# and (PSQLSEQ is null or PSQLSEQ = 0) ORDER BY SQLORD ASC   ";

    }else{
        //일반 코드가져오기
        $to_coltype = "s";
        $sql = " select CD,NM from CG_CODED where  PCD = #PCD# and DELYN = 'N' and USEYN='Y' ORDER BY   ORD ASC   ";
    } 

    alog("cg_code_json.php...............333");

    $stmt = make_stmt($db,$sql, $to_coltype, $REQ);

    //alog("cg_clode_json.php...............444");

    if(!$stmt)JsonMsg("500","100","stmt 생성 실패" . $stmt->errno . " -> " . $stmt->error);

    echo make_grid_read_json($stmt,1);

    //alog("cg_clode_json.php...............555");

    $db->close();


?>