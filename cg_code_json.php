<?php
    header("Content-Type: text/html; charset=UTF-8");
    header("Cache-Control:no-cache");
    header("Pragma:no-cache");

    $CFG = include_once("../common/include/incConfig.php");;

    require_once("../common/include/incUtil.php");
    require_once("../common/include/incDB.php");
    require_once("../common/include/incUser.php");


    //alog("cg_clode_json.php...............111");
    //ServerViewTxt("N","N","Y","Y");

    $db=db_m_open();

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