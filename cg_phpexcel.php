<?php

function column_char($i) { return chr( 65 + $i ); }
 
$CFG = require_once "./include/incConfig.php";

//include_once('./incConfig.php');//CG CONFIG
require_once("./include/incUtil.php");

//echo $_POST["DATA_HEADERS"];//그리드1


alog("DATA_HEADERS: " . $_POST["DATA_HEADERS"]);
alog("DATA_ROWS: " . $_POST["DATA_ROWS"]);
$DATA_HEADERS = explode(",",$_POST["DATA_HEADERS"]);
$DATA_WIDTHS = explode(",",$_POST["DATA_WIDTHS"]);
$DATA_WIDTHS = getIntArray($DATA_WIDTHS,10); //px제거하고 숫자 아니면 디펄트 10
$DATA_WIDTHS = getModArray($DATA_WIDTHS,5); //3배율로 사이즈 줄이기

$DATA_ROWS = getXml2Array($_POST["DATA_ROWS"]);

//헤더 비율


//print_r($DATA_WIDTHS);
//print_r(getIntArray($DATA_WIDTHS,10));
//exit;
//echo json_encode($DATA_ROWS);


// 자료 생성
//$headers = array('1','2','3','4','5','1','2','3','4','5','1','2');
$headers = $DATA_HEADERS;
$widths = $DATA_WIDTHS; //포멧 ( 10,20,30 )이므로 PX는 제거
$rows = array();
//echo "<BR>sizeof = " . sizeof($DATA_ROWS["row"]);
//echo "<BR>count = " . count($DATA_ROWS["row"]);


$xml_array_last = null;
alog("is_assoc : " . is_assoc($DATA_ROWS["row"]) );
alog("is_assoc row id: " . $DATA_ROWS["row"][0]["row id"] );
if(is_assoc($DATA_ROWS["row"]) == 1) {
	alog(" Y " );
	$xml_array_last[0] = $DATA_ROWS["row"];
}else{
	alog(" N " );

	$xml_array_last = $DATA_ROWS["row"];
}

//echo "count:" . count($xml_array_last);
for($i=0;$i<count($xml_array_last);$i++){

	$cols = array();
	for($t=0;$t<count($xml_array_last[$i]["cell"]);$t++){
		$tCol = $xml_array_last[$i]["cell"][$t];
		if(is_array($tCol)){
			//echo "<br>array";
			array_push($cols,"");
		}else{
			//echo "<br>not array";
			array_push($cols,$tCol);
		}
		//if($t == 4)break;
	}
	//echo "<pre>cols";
	//print_r($cols);
	//echo "</pre>";	
	
	$rows[$i] = $cols;

	//echo "<BR> $i = " . count($DATA_ROWS["row"][$i]["cell"]) ;
	//array_push($rows,$DATA_ROWS["row"][$i]["cell"]);
}

//echo "<pre>rows";
//print_r($rows);
//echo "</pre>";	

//echo json_encode($rows);
//exit;




require_once($CFG["CFG_LIBS_EXCEL"]);



/*
$headers = array('ID','부서ID','이름','이메일','나이');
$rows = array(
	array(1, 1, '한놈', 'maarten@example.com', 24),
	array(2, 1, '두시기', 'paul@example.com', 30),
	array(3, 2, '석삼', 'bill.a@example.com', 29),
	array(4, 3, '석삼', 'bill.g@example.com', 25),
);
*/

$data = array_merge(array($headers), $rows);
 
// 스타일 지정
$header_bgcolor = 'FFABCDEF';
 
// 엑셀 생성
$last_char = column_char( count($headers) - 1 );
 
$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');
$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="web-test.xlsx"');
//header('Cache-Control: max-age=0');
header("Cache-Control:no-cache");
header("Pragma:no-cache");

$writer->save('php://output');
?>