<?php



function reqGetNumber($tParam,$tLength){
    //alog("reqGetNumber()................................start : tParam = " . $tParam . ", tLength = " . $tLength);      
	global $_GET;
    return getValidNumber($_GET[$tParam],$tLength);
}

function reqGetString($tParam,$tLength){
    //alog("reqGetString()................................start : tParam = " . $tParam . ", tLength = " . $tLength);      
	global $_GET;
    return getValidString($_GET[$tParam],$tLength);
}

function reqGetDate($tParam,$tLength){
    //alog("reqGetDate()................................start : tParam = " . $tParam . ", tLength = " . $tLength);     
    global $_GET;
    return getValidDate($_GET[$tParam],$tLength);
}

function reqPostNumber($tParam,$tLength){
    //alog("reqPostNumber()................................start : tParam = " . $tParam . ", tLength = " . $tLength);         
	global $_POST;
    return getValidNumber($_POST[$tParam],$tLength);
}

function reqPostString($tParam,$tLength){
    //alog("reqPostString()................................start : tParam = " . $tParam . ", tLength = " . $tLength);          
	global $_POST;
    return getValidString($_POST[$tParam],$tLength);
}

function reqPostDate($tParam,$tLength){
    //alog("reqPostDate()................................start : tParam = " . $tParam . ", tLength = " . $tLength);      
    global $_POST;
    return getValidDate($_POST[$tParam],$tLength);
}

function getValidNumber($tParam,$tLength){
    //alog("getValidNumber()................................start : tParam = " . $tParam . ", tLength = " . $tLength);  

    $tParam .= "";// 0을 ""와 비교하면 true가 되서, 0입력값이 사라지는 버그 존재하여 0 . ""로 처리함.

    $tParam = str_replace(",","",$tParam); //콤마(,)는 제거
    if($tParam  == ""){
        return "";
    }else if(strlen($tParam) > $tLength){

        $printParam = $tParam;
        if(strlen($tParam) > 50)$printParam = substr($printParam,0,48) . "..";

        JsonMsg("500","500","[reqPostNumber] " . $printParam . " is over length (" . strlen($tParam) . " > " . $tLength . ").");
    }else if(is_numeric($tParam)){
        return $tParam;
    }else{
        JsonMsg("500","500","[reqPostNumber] " . $printParam . " is not number.");
    }

}

function getValidString($tParam,$tLength){
    //alog("getValidString()................................start : tParam = " . $tParam . ", tLength = " . $tLength);  

    if( $tParam == ""){
        return "";
    }else if( strlen($tParam) > $tLength ){
        $printParam = $tParam;
        if(strlen($tParam) > 50)$printParam = substr($printParam,0,48) . "..";

        JsonMsg("500","500","[reqPostString] " . $printParam . " is over length (" . strlen($tParam)  . " > " . $tLength .  ").");
	}else{
		return $tParam;
	}
}

function getValidDate($tParam,$tLength){
    //alog("getValidDate()................................start : tParam = " . $tParam . ", tLength = " . $tLength);    
   
    if( $_GET[$tParam] == ""){
        return "";
    }else if(strlen($tParam) > $tLength){
        $printParam = $tParam;
        if(strlen($tParam) > 50)$printParam = substr($printParam,0,48) . "..";

        JsonMsg("500","500","[reqPostDate] " . $printParam . " is over length (" . strlen($tParam)  . " > " . $tLength .  ").");
    }else if(preg_match("/^([0-9]{2,4})([.\-\/]{0,1})([0-9]{2})([.\-\/]{0,1})([0-9]{2})$/",$tParam,$mat)){        
        return $mat[0];
    }else{
        $printParam = $tParam;
        if(strlen($tParam) > 50)$printParam = substr($printParam,0,48) . "..";

        JsonMsg("500","500","[reqPostDate] " . $printParam . " is not date (yyyymmdd, yyyy/mm/dd, yyyy-mm-dd, yyyy.mm.dd).");
    }
}

function getFilter($tInput,$tValidType,$tValidRule){
    //alog("getFilter()................................start : tInput = " . $tInput . ", tValidType = " . $tValidType . ", tValidRule = " . $tValidRule);    
    global $purifier;
    $RtnVal = "";

    $tInput .= "";

    if($tInput . "" == ""){
        return "";
    }else if($tValidType == "REGEXMAT"){
        $RtnVal = filter_var($tInput, FILTER_VALIDATE_REGEXP,array("options" => array("regexp" => $tValidRule ))); 
        if(is_bool($RtnVal) == true && $RtnVal == false)$RtnVal = ""; //filter_var는 정규식 실패시 false를 리턴한다. 0이면 == false가 트루가 됨.
        //alog("getFilter() REGEXMAT " . $tInput . " ---> ". $RtnVal);
    }else if($tValidType == "CLEARTEXT"){
        $RtnVal = strip_tags($tInput);
        //alog("getFilter() CLEARTEXT " . $tInput . " ---> ". $RtnVal);
    }else if($tValidType == "SAFETEXT"){//한글까지 변환함
        $RtnVal = htmlentities($tInput);                        
        //alog("getFilter() SAFETEXT " . $tInput . " ---> ". $RtnVal);
    }else if($tValidType == "SAFEECHO"){//5가지 특수문자만 변환함
        $RtnVal = htmlspecialchars($tInput);                        
        //alog("getFilter() SAFEHTML " . $tInput . " ---> ". $RtnVal);
    }else if($tValidType == "SAFEHTML"){
        $RtnVal = $purifier->purify($tInput);                        
        //alog("getFilter() SAFEHTML " . $tInput . " ---> ". $RtnVal);
    }else{
        $RtnVal = $tInput;
    }

    return $RtnVal;
}


function filterFormviewChk($tStr,$tDataType,$tDataSize,$tValidType,$tValidRule){
    //alog("filterGridChk()................................start : tStr = " . $tStr . ", tDataType = " . $tDataType);
    $RtnVal = "";
    $tArr = explode(",",$tStr);
    for($i=0;$tArr != null && $i<sizeof($tArr);$i++){
        $colBefore = $tArr[$i];

        //valid
        if($tDataType == "DATE"){
            $colAfter1 = getValidDate($colBefore,$tDataSize);
        }else if($tDataType == "STRING"){
            $colAfter1 = getValidString($colBefore,$tDataSize);
        }else if($tDataType == "NUMBER"){
            $colAfter1 = getValidNumber($colBefore,$tDataSize);    
        }else{
            $colAfter1 = $colBefore;
        }
        //alog("      valid  : " . $colBefore . " ===> " . $colAfter1);   

        //FILTER
        $colAfter2 = getFilter($colAfter1,$tValidType,$tValidRule);
        //alog("      filter " . $tValidType . " " . $tValidRule . " : " . $colAfter1 . " ===> " . $colAfter2);           

        //배열에 추가
        $RtnVal .= ($RtnVal == "")? $colAfter2 : "," . $colAfter2;
    }

    return $RtnVal;
}


function filterGridChk($tStr,$tDataType,$tDataSize,$tValidType,$tValidRule){
    //alog("filterGridChk()................................start : tStr = " . $tStr . ", tDataType = " . $tDataType);
    $RtnVal = array();
    $tArr = explode(",",$tStr);
    for($i=0;$tArr != null && $i<sizeof($tArr);$i++){
        $colBefore = $tArr[$i];

        //valid
        if($tDataType == "DATE"){
            $colAfter1 = getValidDate($colBefore,$tDataSize);
        }else if($tDataType == "STRING"){
            $colAfter1 = getValidString($colBefore,$tDataSize);
        }else if($tDataType == "NUMBER"){
            $colAfter1 = getValidNumber($colBefore,$tDataSize);    
        }else{
            $colAfter1 = $colBefore;
        }
        //alog("      valid  : " . $colBefore . " ===> " . $colAfter1);   

        //FILTER
        $colAfter2 = getFilter($colAfter1,$tValidType,$tValidRule);
        //alog("      filter " . $tValidType . " " . $tValidRule . " : " . $colAfter1 . " ===> " . $colAfter2);           

        //배열에 추가
        array_push($RtnVal,$colAfter2);
    }

    return $RtnVal;
}


function filterGridXml($map){
    global $purifier;
    //alog("filterGridXml()......................................start");
    $xml_array_last = null;
    $colord_array = explode(",",$map["COLORD"]);
    $is_assoc = null;

    if(is_assoc($map["XML"]["row"]) == 1) {
        //alog(" Y " );
        $is_assoc = true;
        $xml_array_last[0] = $map["XML"]["row"];
    }else{
        //alog(" N " );
        $is_assoc = false;            
        $xml_array_last = $map["XML"]["row"];
    }
    //var_dump($xml_array_last);

    $RtnVal = $map["XML"];
    $RtnCnt = 0;
    //alog("xml sizeof : " . sizeof($xml_array_last));
    for($i=0;xml_array_last != null && $i<sizeof($xml_array_last);$i++){
        $row = $xml_array_last[$i];
        //alog("        i : " . $i);

        //현재 그리드 line을 bind 배열에 담기
        $to_row = null;
        $to_coltype = null;
        $sql = null;
        for($j=0;$j<sizeof($row["cell"]);$j++){
            //alog("        j : " . $j);                     
            $colBefore = $row["cell"][$j];

            //VALID
            if(is_array($map["VALID"][trim($colord_array[$j])]) && !is_array($colBefore) && strlen($colBefore) > 0){

                if($map["VALID"][trim($colord_array[$j])][0] == "DATE"){
                    $colAfter1 = getValidDate($colBefore,$map["VALID"][trim($colord_array[$j])][1]);
                }else if($map["VALID"][trim($colord_array[$j])][0] == "STRING"){
                    $colAfter1 = getValidString($colBefore,$map["VALID"][trim($colord_array[$j])][1]);
                }else if($map["VALID"][trim($colord_array[$j])][0] == "NUMBER"){
                    $colAfter1 = getValidNumber($colBefore,$map["VALID"][trim($colord_array[$j])][1]);    
                }else{
                    $colAfter1 = $colBefore;
                } 

            }else{
                $colAfter1 = $row["cell"][$j];
            }
            //alog("      valid " . trim($colord_array[$j]) . " : " . $colBefore . " ===> " . $colAfter1);  

            //FILTER
            if(is_array($map["FILTER"][trim($colord_array[$j])]) && !is_array($colAfter1) && strlen($colAfter1) > 0){
                $colAfter2 = getFilter($colAfter1,$map["FILTER"][trim($colord_array[$j])][0], $map["FILTER"][trim($colord_array[$j])][1]);
            }else{
                $colAfter2 = $colAfter1;
            }
            //alog("      filter " . trim($colord_array[$j]) . " : " . $colAfter1 . " ===> " . $colAfter2);              
            
            if($is_assoc == true){
                //다차원이 아니고 그냥 배열
                $RtnVal["row"]["cell"][$j] = $colAfter2;
            }else{
                $RtnVal["row"][$i]["cell"][$j] = $colAfter2;
            }
            
        }

    }
    //alog("filterGridXml()......................................end");
    return $RtnVal;
}


function filterGridJson($map){
    global $purifier;
    //alog("filterGridJson()......................................start");
    $xml_array_last = null;
    $colord_array = explode(",",$map["COLORD"]);
    $is_assoc = null;

    $json_array_last = $map["JSON"];
    //var_dump($xml_array_last);

    $RtnVal = $map["XML"];
    $RtnCnt = 0;
    //alog("xml sizeof : " . sizeof($json_array_last));
    for($i=0;$json_array_last != null && $i<sizeof($json_array_last);$i++){
        $row = $json_array_last[$i];
        //alog("        i : " . $i);

        //현재 그리드 line을 bind 배열에 담기
        $to_row = null;
        $to_coltype = null;
        $sql = null;
        for($j=0;$j<sizeof($colord_array);$j++){
            //alog("        j : " . $j);                     
            $colBefore = $row[$colord_array[$j]];

            //VALID
            if(is_array($map["VALID"][trim($colord_array[$j])]) && !is_array($colBefore) && strlen($colBefore) > 0){

                if($map["VALID"][trim($colord_array[$j])][0] == "DATE"){
                    $colAfter1 = getValidDate($colBefore,$map["VALID"][trim($colord_array[$j])][1]);
                }else if($map["VALID"][trim($colord_array[$j])][0] == "STRING"){
                    $colAfter1 = getValidString($colBefore,$map["VALID"][trim($colord_array[$j])][1]);
                }else if($map["VALID"][trim($colord_array[$j])][0] == "NUMBER"){
                    $colAfter1 = getValidNumber($colBefore,$map["VALID"][trim($colord_array[$j])][1]);    
                }else{
                    $colAfter1 = $colBefore;
                } 

            }else{
                $colAfter1 = $colBefore ;
            }
            alog("      valid " . trim($colord_array[$j]) . " : " . $colBefore . " ===> " . $colAfter1);  

            //FILTER
            if(is_array($map["FILTER"][trim($colord_array[$j])]) && !is_array($colAfter1) && strlen($colAfter1) > 0){
                $colAfter2 = getFilter($colAfter1,$map["FILTER"][trim($colord_array[$j])][0], $map["FILTER"][trim($colord_array[$j])][1]);
            }else{
                $colAfter2 = $colAfter1;
            }
            alog("      filter " . trim($colord_array[$j]) . " : " . $colAfter1 . " ===> " . $colAfter2);              
            

            $RtnVal[$i][$colord_array[$j]] = $colAfter2;
            
        }
        //컬럼 기타 정보
        $RtnVal[$i]["id"] = $row["id"];
        $RtnVal[$i]["changeState"] = $row["changeState"];
        $RtnVal[$i]["changeCud"] = $row["changeCud"];
    }
    //alog("filterGridJson()......................................end");
    return $RtnVal;
}

?>