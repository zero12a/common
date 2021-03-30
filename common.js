/**
 * Created by zero12a on 2014. 7. 10..
 */

//날짜포멧 정의
var dateFormatJson = {
	dateFormat: 'yy-mm-dd',
	prevText: '이전 달',
	nextText: '다음 달',
	monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
	monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
	dayNames: ['일','월','화','수','목','금','토'],
	dayNamesShort: ['일','월','화','수','목','금','토'],
	dayNamesMin: ['일','월','화','수','목','금','토'],
	showMonthAfterYear: true,
	changeMonth: true,
	changeYear: true,
	yearSuffix: '년',
	showOn: "button",
	buttonImage: CFG_URL_LIBS_ROOT + "img/calendar4-200.png",
	buttonImageOnly: true,
	buttonText: "Select date"
	};


function alog(tLog){
	if(typeof console == "object")console.log(tLog);
}

function xmlCdataAdd(tmp){
	return "<![CDATA[" + tmp + "]]>";
}

function xmlCdataRemove(tmp){
	var returnValue = "";
	//alert(tmp.substring(0,9));
	//alert(tmp.length);
	//alert(tmp.substring(tmp.length-3,tmp.length));

	returnValue = tmp;

	//alert(tmp);
	if(tmp.substring(0,9) == "<![CDATA["){
		returnValue = tmp.substring(9,tmp.length);
	}
	if(returnValue.substring(returnValue.length-3,returnValue.length) == "]]>"){
		returnValue = returnValue.substring(0,returnValue.length-3);
	}
	//alert(returnValue);
	return returnValue;
}

function goFullScreen(){
	alog("goFullScreen()...............................start");
	//alert(1)
	var b = $(document.body);	
	var elem = document.body;
	var isFullScreen = b.prop( "fullscreen" );

	if(typeof isFullScreen === 'undefined' || isFullScreen  == "N"){
		//var elem = $("#vmain");
		if (elem.requestFullscreen) {
		alog("requestFullscreen");
		elem.requestFullscreen();
		} else if (elem.mozRequestFullScreen) { /* Firefox */
		alog("mozRequestFullScreen");
		elem.mozRequestFullScreen();
		} else if (elem.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
		alog("webkitRequestFullscreen");
		elem.webkitRequestFullscreen();
		} else if (elem.msRequestFullscreen) { /* IE/Edge */
		alog("msRequestFullscreen");
		elem.msRequestFullscreen();
		}

		b.prop( "fullscreen","Y");
	}else{
		if (document.exitFullscreen) {
		document.exitFullscreen();
		} else if (document.mozCancelFullScreen) { /* Firefox */
		document.mozCancelFullScreen();
		} else if (document.webkitExitFullscreen) { /* Chrome, Safari and Opera */
		document.webkitExitFullscreen();
		} else if (document.msExitFullscreen) { /* IE/Edge */
		document.msExitFullscreen();
		}

		b.prop( "fullscreen","N");
	}
	alog("goFullScreen()...............................end");	
}

function msgNotice(tMsg,tSecond){
	alog("(common) msgNotice : " + tMsg + ", tSecond=" + tSecond);
	
	//dhtmlx.message({
	//	type: "Notice",
	//	text: tMsg,
	//	expire: tSecond * 1000
	//});

	toastr.info(tMsg,null,{timeOut: tSecond * 1000});
}
function msgError(tMsg,tSecond){
	alog("(common) msgError : " + tMsg + ", tSecond=" + tSecond);

	//dhtmlx.message({
	//	type: "Error",
	//	text: tMsg,
	//	expire: tSecond * 1000
	//});
	toastr.error(tMsg,null,{timeOut: tSecond * 1000});
}



function setCodeYN(tGrptype, tCombo, tPcd){
	//alog("(common)   setCodeYN----------------------start");
	//alog("		tPcd = " + tPcd);
	
	//alert(tCombo);

	if(!tCombo)return;

	


	var data = {
			"RTN_DATA":
				{"rows":
					[
						{"data":["Y","Y"]}
						,{"data":["N","N"]}
					]
				}
			};

	if(tGrptype == "GRID"){
		if(!data.RTN_DATA)return;
		//alog("	코드수 : " + data.RTN_DATA.rows.length);
		
		tCombo.clear(); //비우기
		tCombo.put("","");

		for(var i=0;i<data.RTN_DATA.rows.length;i++){
			//alog(data.RTN_DATA.rows[i].data[0] + "=" + data.RTN_DATA.rows[i].data[1]);

			tCombo.put(data.RTN_DATA.rows[i].data[0],data.RTN_DATA.rows[i].data[1]);
		}
	}else if(tGrptype == "CONDITION"){
		if(!data.RTN_DATA)return;
		//alog("	코드수 : " + data.RTN_DATA.rows.length);
		
		tCombo.empty(); //비우기
		tCombo.append("<option value=''></option>"); //빈라인 추가

		for(var i=0;i<data.RTN_DATA.rows.length;i++){
			//alog(data.RTN_DATA.rows[i].data[1] + "=" + data.RTN_DATA.rows[i].data[2]);

			tCombo.append("<option value='" + data.RTN_DATA.rows[i].data[0] + "'>" + data.RTN_DATA.rows[i].data[1] + "</option>");
		}
	}else if(tGrptype == "FORMVIEW"){
		if(!data.RTN_DATA)return;
		//alog("	코드수 : " + data.RTN_DATA.rows.length);
		
		tCombo.empty(); //비우기
		tCombo.append("<option value=''></option>"); //빈라인 추가

		for(var i=0;i<data.RTN_DATA.rows.length;i++){
			//alog(data.RTN_DATA.rows[i].data[1] + "=" + data.RTN_DATA.rows[i].data[2]);

			tCombo.append("<option value='" + data.RTN_DATA.rows[i].data[0] + "'>" + data.RTN_DATA.rows[i].data[1] + "</option>");
		}
	}else{
		alog("	그룹 타입이 없습니다");
	}

		
	//alog("   setGridCombo----------------------end");

}

function apiCodeDropDown(tGrpId, tColId, tJsonParam, tDefaultValue){
	alog("   apiCodeDropDown----------------------start : tGrpId=" + tGrpId + ", tColId=" + tColId);

	$.ajax({
		type : "GET",
		url : CFG_URL_CODE_API,
		data : tJsonParam,
		privateGrpId : tGrpId,
		privateColId : tColId,
		privateDefaultValue : tDefaultValue,
		dataType: "json",
		async: true,
		success: function(data){
			alog("   apiCodeDropDown json return----------------------");
			//alog("   json data : " + JSON.stringify(data.RTN_DATA));
			//alog("   json RTN_CD : " + data.RTN_CD);
			//alog("   json ERR_CD : " + data.ERR_CD);
			//alog("   json RTN_MSG length : " + data.RTN_MSG.length);

			//그리드에 데이터 반영
			alog("	GRPTYPE = " + grpInfo.get(this.privateGrpId).GRPTYPE);
			if(data.RTN_CD == "200"){
				if(grpInfo.get(this.privateGrpId).GRPTYPE == "GRID"){
					if(!data.RTN_DATA)return;
					//alog("	코드수 : " + data.RTN_DATA.rows.length);
					
					this.tGrid = eval("mygrid"+this.privateGrpId); //그리드 오브젝트 얻기
					
					//make arr (cd:cd1,nm:nm1)
					tarr = [];
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][0] + "=" + data.RTN_DATA.rows[i][1]);
						cd = data.RTN_DATA.rows[i].data[0];
						nm = data.RTN_DATA.rows[i].data[1];
						tarr[i] = {"cd": cd, "nm": nm};
					}

					this.tGrid.registerCList(this.tGrid.getColIndexById(this.privateColId),tarr);//값세팅하기


				}else if(grpInfo.get(this.privateGrpId).GRPTYPE == "CONDITION"){
					tarr = [];
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][0] + "=" + data.RTN_DATA.rows[i][1]);
						value = data.RTN_DATA.rows[i].data[0];
						name = data.RTN_DATA.rows[i].data[1];
						tarr[i] = {"value": value, "name": name};
					}

					$("#" + this.privateGrpId + "-" + this.privateColId).multiselect( 'loadOptions', tarr);//값세팅하기
				}else if(grpInfo.get(this.privateGrpId).GRPTYPE == "FORMVIEW"){

					//alog(grpInfo.get(this.privateGrpId));
					//alog(grpInfo.get(this.privateGrpId).COLS);

					var colObj = _.find(grpInfo.get(this.privateGrpId).COLS,{'COLID': this.privateColId});

					//alog(colObj);

					alog("OBJTYPE = " + colObj.OBJTYPE);
					if(colObj != null && colObj.OBJTYPE == "DROPDOWN"){

						tarr = [];
						for(var i=0;i<data.RTN_DATA.rows.length;i++){
							//alog("		" + data.RTN_DATA.rows[i].data[0] + "=" + data.RTN_DATA.rows[i].data[1]);
							value = data.RTN_DATA.rows[i].data[0];
							name = data.RTN_DATA.rows[i].data[1];
							tarr[i] = {"value": value, "name": name};
						}

						//alog(tarr);
						$("#" + this.privateGrpId + "-" + this.privateColId).multiselect( 'loadOptions', tarr);//값세팅하기= "DROPDOWN"){

					}else if(colObj != null && colObj.OBJTYPE == "SELECT2"){

						tarr = [];
						for(var i=0;i<data.RTN_DATA.rows.length;i++){
							id = data.RTN_DATA.rows[i].data[0];
							text = data.RTN_DATA.rows[i].data[1];
							tarr[i] = {"id": id, "text": text};
						}
						//alog(tarr);

						//var obj = eval("select2_" + this.privateGrpId + "_" + this.privateColId);
						$("#" + this.privateGrpId + "-" + this.privateColId).select2({
							placeholder: "Select options",
							closeOnSelect: false,
							data: tarr,
							allowClear: true,
							tags: true
						});

					}else if(colObj != null && colObj.OBJTYPE == "ANYSELECT"){

						tarr = [];
						for(var i=0;i<data.RTN_DATA.rows.length;i++){
							id = data.RTN_DATA.rows[i].data[0];
							text = data.RTN_DATA.rows[i].data[1];
							tarr[i] = {"cd": id, "nm": text};
						}
						//alog(tarr);

						//var obj = eval("select2_" + this.privateGrpId + "_" + this.privateColId);
						eval("anyselect_" + this.privateGrpId + "_" + this.privateColId).loadData(tarr);

					}else{
						alert(this.privateColId + "정보를 찾을수 없습니다.");
					}


				}else{
					alog("	그룹 타입이 없습니다");
				}

			}else{
				alert("서버 조회중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG);
			}
		},
		error: function(error){
			alert("Error:" + error);
		}
	});

	alog("   apiCodeDropDown----------------------end");
}

function apiCodeCombo(tGrpId, tColId, tJsonParam, tDefaultValue){
	alog("   apiCodeCombo----------------------start : tGrpId=" + tGrpId + ", tColId=" + tColId);
	//alog("		tPcd = " + tPcd);

	//if(!tCombo)return;

	//불러오기
	//alert(CFG_URL_CODE_API);
	$.ajax({
		type : "GET",
		url : CFG_URL_CODE_API,
		data : tJsonParam,
		privateGrpId : tGrpId,
		privateColId : tColId,
		privateDefaultValue : tDefaultValue,
		dataType: "json",
		async: true,
		success: function(res){
			alog("   apiCodeCombo json return----------------------" + JSON.stringify(this.data));
			//alog("   json data : " + JSON.stringify(data.RTN_DATA));
			//alog("   json RTN_CD : " + data.RTN_CD);
			//alog("   json ERR_CD : " + data.ERR_CD);
			//alog("   json RTN_MSG length : " + data.RTN_MSG.length);

			//그리드에 데이터 반영
			if(res.RTN_CD == "200"){
				if(grpInfo.get(this.privateGrpId).GRPTYPE == "GRID"){
					if(!res.RTN_DATA)return;
					//alog("	코드수 : " + res.RTN_DATA.rows.length);
					
					this.tGrid = eval("mygrid"+this.privateGrpId); //그리드 오브젝트 얻기
					this.privateCombo = this.tGrid.getCombo(this.tGrid.getColIndexById(this.privateColId)); //콤보 얻기

					this.privateCombo.clear(); //비우기
					this.privateCombo.put("","");

					for(var i=0;i<res.RTN_DATA.rows.length;i++){
						//alog(res.RTN_DATA.rows[i][0] + "=" + res.RTN_DATA.rows[i][1]);
						cd = res.RTN_DATA.rows[i].data[0];
						nm = res.RTN_DATA.rows[i].data[1];
						this.privateCombo.put(cd,nm);
					}
				}else if(grpInfo.get(this.privateGrpId).GRPTYPE == "GRIDWIX"){
						alog("	GRIDWIX - 코드수 : " + res.RTN_DATA.rows.length);
						if(!res.RTN_DATA)return;

						var tArr = [];

						for(var i=0;i<res.RTN_DATA.rows.length;i++){
							//alog(data.RTN_DATA.rows[i][0] + "=" + data.RTN_DATA.rows[i][1]);
							tArr[i] = {
								"id": res.RTN_DATA.rows[i].CD
								,"value": res.RTN_DATA.rows[i].NM
							};
						}	
						$$("wixdt" + this.privateGrpId).getColumnConfig(this.privateColId).options = tArr;
						$$("wixdt" + this.privateGrpId).refreshColumns(); //필수호출해야함.

				}else if(grpInfo.get(this.privateGrpId).GRPTYPE == "CONDITION"){
					if(!res.RTN_DATA)return;
					//alog("	코드수 : " + data.RTN_DATA.rows.length);
					
					var privateCombo = $("#" + this.privateGrpId + "-" + this.privateColId); //오브젝트 얻기
					alog(privateCombo);

					privateCombo.empty(); //비우기
					privateCombo.append("<option value=''></option>"); //빈라인 추가

					for(var i=0;i<res.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][1] + "=" + data.RTN_DATA.rows[i][2]);
						cd = res.RTN_DATA.rows[i].CD;
						nm = res.RTN_DATA.rows[i].NM;

						chkText ="";
						if(this.privateDefaultValue == cd)chkText = " selected";

						privateCombo.append("<option value='" + cd + "'" + chkText + ">" + nm + "</option>");
					}
					//선택하기
					//$("#" + this.privateGrpId + "-" + this.privateColId + " > option[@value=" + this.privateDefaultValue + "]").attr("selected","true"); //선택하기

				}else if(grpInfo.get(this.privateGrpId).GRPTYPE == "FORMVIEW"){
					if(!res.RTN_DATA)return;
					//alog("	코드수 : " + res.RTN_DATA.rows.length);
					
					this.privateCombo = $("#" + this.privateGrpId + "-" + this.privateColId); //오브젝트 얻기

					this.privateCombo.empty(); //비우기
					this.privateCombo.append("<option value=''></option>"); //빈라인 추가

					for(var i=0;i<res.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][1] + "=" + data.RTN_DATA.rows[i][2]);
						cd = res.RTN_DATA.rows[i].CD;
						nm = res.RTN_DATA.rows[i].NM;

						chkText ="";
						if(this.privateDefaultValue == cd)chkText = " selected";

						this.privateCombo.append("<option value='" + cd + "'" + chkText + ">" + nm + "</option>");
					}
					//선택하기
					//$("#" + this.privateGrpId + "-" + this.privateColId + " > option[@value=" + this.privateDefaultValue + "]").attr("selected","true");

				}else{
					alog("	그룹 타입이 없습니다");
				}

			}else{
				alert("서버 조회중 에러가 발생했습니다.\nRTN_CD : " + res.RTN_CD + "\nERR_CD : " + res.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG);
			}
			alog("combo add end");
		},
		error: function(error){
			alert("Error:" + error);
		}
	});

	alog("   setCodeCombo----------------------end");

}




function setCodeCombo(tGrptype, tCombo, tPcd){
	alog("   setCodeCombo----------------------start : tGrptype = " + tGrptype + ", tPcd = " + tPcd);
	//alog("		tPcd = " + tPcd);

	if(!tCombo)return;

	//불러오기
	$.ajax({
		type : "GET",
		url : "/common/cg_code_json.php",
		data : {PJTSEQ : 3,PCD : tPcd},
		privateCombo : tCombo,
		dataType: "json",
		async: true,
		success: function(data){
			//alog("   getCodeJson json return----------------------");
			//alog("   json data : " + JSON.stringify(data.RTN_DATA));
			//alog("   json RTN_CD : " + data.RTN_CD);
			//alog("   json ERR_CD : " + data.ERR_CD);
			//alog("   json RTN_MSG length : " + data.RTN_MSG.length);

			//그리드에 데이터 반영
			if(data.RTN_CD == "200"){
				if(tGrptype == "GRID"){
					if(!data.RTN_DATA)return;
					//alog("	코드수 : " + data.RTN_DATA.rows.length);
					
					this.privateCombo.clear(); //비우기
					this.privateCombo.put("","");

					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][0] + "=" + data.RTN_DATA.rows[i][1]);

						this.privateCombo.put(data.RTN_DATA.rows[i][0],data.RTN_DATA.rows[i][1]);
					}
				}else if(tGrptype == "CONDITION"){
					if(!data.RTN_DATA)return;
					//alog("	코드수 : " + data.RTN_DATA.rows.length);
					
					this.privateCombo.empty(); //비우기
					this.privateCombo.append("<option value=''></option>"); //빈라인 추가

					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][1] + "=" + data.RTN_DATA.rows[i][2]);

						this.privateCombo.append("<option value='" + data.RTN_DATA.rows[i][0] + "'>" + data.RTN_DATA.rows[i][1] + "</option>");
					}
				}else if(tGrptype == "FORMVIEW"){
					if(!data.RTN_DATA)return;
					//alog("	코드수 : " + data.RTN_DATA.rows.length);
					
					this.privateCombo.empty(); //비우기
					this.privateCombo.append("<option value=''></option>"); //빈라인 추가

					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][1] + "=" + data.RTN_DATA.rows[i][2]);

						this.privateCombo.append("<option value='" + data.RTN_DATA.rows[i][0] + "'>" + data.RTN_DATA.rows[i][1] + "</option>");
					}
				}else{
					alog("	그룹 타입이 없습니다");
				}

			}else{
				alert("서버 조회중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG);
			}
		},
		error: function(error){
			alert("Error:" + error);
		}
	});

	//alog("   setCodeCombo----------------------end");

}




function apiCodeCheck(tGrpId, tColId, tJsonParam, tDefaultValue){
	alog("   apiCodeCheck----------------------start : tGrpId=" + tGrpId + ", tColId=" + tColId);
	//alog("		tGrpId = " + tGrpId);		
	//alog("		tColId = " + tColId);	
	//alog("		tPcd = " + tPcd);

	if(tColId == "")return;

	//alert(tCheckVal);


	//alert(arrCheckVal.length);

	//불러오기
	$.ajax({
		type : "GET",
		url : CFG_URL_CODE_API,
		data : tJsonParam,
		privateGrpId : tGrpId,
		privateColId : tColId,
		privateDefaultValue : tDefaultValue,
		dataType: "json",
		async: false,
		success: function(data){
			alog("   apiCodeCheck json return----------------------");
			//alog("   json data : " + JSON.stringify(data.RTN_DATA));
			//alog("   json RTN_CD : " + data.RTN_CD);
			//alog("   json ERR_CD : " + data.ERR_CD);
			//alog("   json RTN_MSG length : " + data.RTN_MSG.length);
			var arrCheckVal;
			if(this.privateDefaultValue == ""){
				 arrCheckVal = new Array();
			}else{
				 arrCheckVal = this.privateDefaultValue.split(",");
			}

			tCheckNm = this.privateGrpId + "-" + this.privateColId;
			 
			//그리드에 데이터 반영
			if(data.RTN_CD == "200"){
				if(grpInfo.get(this.privateGrpId).GRPTYPE == "GRID"){
					alert("GRID는 지원하지 않는 타입입니다.")
				}else if(grpInfo.get(this.privateGrpId).GRPTYPE == "CONDITION"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tCheckNm + "-HOLDER").html(""); //비우기

					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						cd = data.RTN_DATA.rows[i].data[0];
						nm = data.RTN_DATA.rows[i].data[1];
						//alog(cd + "=" + nm);
						var chkText = "";
						for(var k=0;k<arrCheckVal.length;k++){
							if(arrCheckVal[k] == val)chkText = "checked";
						}

						if(i>0)strSpace = "&nbsp;";						
						$("#" + tCheckNm + "-HOLDER").append(strSpace + "<input type=checkbox name='" + tCheckNm + "' id='" + tCheckNm + "' value='" + cd + "' " + chkText + ">" + nm);
					}
				}else if(grpInfo.get(this.privateGrpId).GRPTYPE == "FORMVIEW"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tCheckNm + "-HOLDER").html(""); //비우기

					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						cd = data.RTN_DATA.rows[i].data[0];
						nm = data.RTN_DATA.rows[i].data[1];
						//alog(cd + "=" + nm);

						var chkText = "";
						for(var k=0;k<arrCheckVal.length;k++){
							if(arrCheckVal[k] == val)chkText = "checked";
						}
								
						if(i>0)strSpace = "&nbsp;";								
						$("#" + tCheckNm + "-HOLDER").append(strSpace + "<input type=checkbox name='" + tCheckNm + "' id='" + tCheckNm + "' value='" + cd + "' " + chkText + ">" + nm);
					}
				}else{
					alog("	그룹 타입이 없습니다");
				}

			}else{
				alert("서버 조회중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG);
			}
		},
		error: function(error){
			alert("Error:" + error);
		}
	});

	//alog("   setGridCombo----------------------end");

}


function setCodeCheck(tGrptype, tCheckNm, tPcd, tCheckVal){
	alog("(common) setCodeCheck----------------------start : tGrptype=" + tGrptype + ", tCheckNm=" + tCheckNm + ", tPcd=" + tPcd);
	//alog("		tGrptype = " + tGrptype);		
	//alog("		tCheckNm = " + tCheckNm);	
	//alog("		tPcd = " + tPcd);

	if(tCheckNm == "")return;

	//alert(tCheckVal);
	var arrCheckVal;
	if(tCheckVal == ""){
		 arrCheckVal = new Array();
	}else{
		 arrCheckVal = tCheckVal.split(",");
	}

	//alert(arrCheckVal.length);

	//불러오기
	$.ajax({
		type : "GET",
		url : "/common/cg_code_json.php",
		data : {PJTSEQ : 3,PCD : tPcd},
		dataType: "json",
		async: false,
		success: function(data){
			alog("(common) setCodeCheck json return----------------------");
			//alog("   json data : " + JSON.stringify(data.RTN_DATA));
			//alog("   json RTN_CD : " + data.RTN_CD);
			//alog("   json ERR_CD : " + data.ERR_CD);
			//alog("   json RTN_MSG length : " + data.RTN_MSG.length);

			//그리드에 데이터 반영
			if(data.RTN_CD == "200"){
				if(tGrptype == "GRID"){
					alert("GRID는 지원하지 않는 타입입니다.")
				}else if(tGrptype == "CONDITION"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tCheckNm + "-HOLDER").html(""); //비우기

					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][0] + "=" + data.RTN_DATA.rows[i][1]);
						var chkText = "";
						for(var k=0;k<arrCheckVal.length;k++){
							if(arrCheckVal[k] == data.RTN_DATA.rows[i][0])chkText = "checked";
						}

						if(i>0)strSpace = "&nbsp;";						
						$("#" + tCheckNm + "-HOLDER").append(strSpace + "<input type=checkbox name='" + tCheckNm + "' id='" + tCheckNm + "' value='" + data.RTN_DATA.rows[i][0] + "' " + chkText + ">" + data.RTN_DATA.rows[i][1]);
					}
				}else if(tGrptype == "FORMVIEW"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tCheckNm + "-HOLDER").html(""); //비우기

					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][0] + "=" + data.RTN_DATA.rows[i][1]);

						var chkText = "";
						for(var k=0;k<arrCheckVal.length;k++){
							if(arrCheckVal[k] == data.RTN_DATA.rows[i][0])chkText = "checked";
						}
								
						if(i>0)strSpace = "&nbsp;";								
						$("#" + tCheckNm + "-HOLDER").append(strSpace + "<input type=checkbox name='" + tCheckNm + "' id='" + tCheckNm + "' value='" + data.RTN_DATA.rows[i][0] + "' " + chkText + ">" + data.RTN_DATA.rows[i][1]);
					}
				}else{
					alog("	그룹 타입이 없습니다");
				}

			}else{
				alert("서버 조회중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG);
			}
		},
		error: function(error){
			alert("Error:" + error);
		}
	});

	//alog("   setGridCombo----------------------end");

}


function apiCodeRadio(tGrpId, tColId, tJsonParam, tDefaultValue){
	alog("(common) apiCodeRadio()----------------------start : tGrpId=" + tGrpId + ", tColId=" + tColId);
	//alog("		tGrpId = " + tGrpId);		
	//alog("		tColId = " + tColId);	
	//alog("		tPcd = " + tPcd);

	if(tColId == "")return;
	if(typeof tColId == 'object'){
		alert("apiCodeRadio는 라디오 오브젝트를 처리할수 없습니다.(라디오 오브젝트 이름으로 호출필요)");
		return;
	}

	//불러오기
	$.ajax({
		type : "GET",
		url : CFG_URL_CODE_API,
		data : tJsonParam,
		privateGrpId : tGrpId,
		privateColId : tColId,
		privateDefaultValue : tDefaultValue,
		dataType: "json",
		async: false,
		success: function(data){
			alog("(common) apiCodeRadio() json return----------------------");
			//alog("   json data : " + JSON.stringify(data.RTN_DATA));
			//alog("   json RTN_CD : " + data.RTN_CD);
			//alog("   json ERR_CD : " + data.ERR_CD);
			//alog("   json RTN_MSG length : " + data.RTN_MSG.length);


			tRadioNm = this.privateGrpId + "-" + this.privateColId;

			//그리드에 데이터 반영
			if(data.RTN_CD == "200"){
				if(grpInfo.get(this.privateGrpId).GRPTYPE == "GRID"){
					alert("(apiCodeRadio) GRID는 지원하지 않는 타입입니다.")
				}else if(grpInfo.get(this.privateGrpId).GRPTYPE == "CONDITION"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tRadioNm + "-HOLDER").html(""); //비우기
					
					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						cd = data.RTN_DATA.rows[i].data[0];
						nm = data.RTN_DATA.rows[i].data[1];
						//alog(cd + "=" + nm);

						var chkText = "";
						if(this.privateDefaultValue == cd)chkText = "checked";

						if(i>0)strSpace = "&nbsp;";
						$("#" + tRadioNm + "-HOLDER").append(strSpace + "<input type=radio name='" + tRadioNm + "' id='" + tRadioNm + "' value='" + cd + "' " + chkText + ">" + nm);
					}
				}else if(grpInfo.get(this.privateGrpId).GRPTYPE == "FORMVIEW"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tRadioNm + "-HOLDER").html(""); //비우기

					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						cd = data.RTN_DATA.rows[i].data[0];
						nm = data.RTN_DATA.rows[i].data[1];
						//alog(cd + "=" + nm);

						var chkText = "";
						if(this.privateDefaultValue == cd)chkText = "checked";

						if(i>0)strSpace = "&nbsp;";
						$("#" + tRadioNm + "-HOLDER").append(strSpace + "<input type=radio name='" + tRadioNm + "' id='" + tRadioNm + "' value='" + cd + "' " + chkText + ">" + nm);
					}
				}else{
					alog("	그룹 타입이 없습니다");
				}

			}else{
				alert("서버 조회중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG);
			}
		},
		error: function(error){
			alert("Error:" + error);
		}
	});

	//alog("   setGridCombo----------------------end");

}


function setCodeRadio(tGrptype, tRadioNm, tPcd, tCheckVal){
	alog("(common) setCodeRadio()----------------------start ");
	//alog("		tGrptype = " + tGrptype);		
	//alog("		tRadioNm = " + typeof tRadioNm);	
	//alog("		tPcd = " + tPcd);

	if(tRadioNm == "")return;
	if(typeof tRadioNm == 'object'){
		alert("setCodeRadio는 라디오 오브젝트를 처리할수 없습니다.(라디오 오브젝트 이름으로 호출필요)");
		return;
	}

	//불러오기
	$.ajax({
		type : "GET",
		url : "/common/cg_code_json.php",
		data : {PJTSEQ : 3,PCD : tPcd},
		dataType: "json",
		async: false,
		success: function(data){
			alog("(common) setCodeRadio() json return----------------------");
			//alog("   json data : " + JSON.stringify(data.RTN_DATA));
			//alog("   json RTN_CD : " + data.RTN_CD);
			//alog("   json ERR_CD : " + data.ERR_CD);
			//alog("   json RTN_MSG length : " + data.RTN_MSG.length);

			//그리드에 데이터 반영
			if(data.RTN_CD == "200"){
				if(tGrptype == "GRID"){
					alert("GRID는 지원하지 않는 타입입니다.")
				}else if(tGrptype == "CONDITION"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tRadioNm + "-HOLDER").html(""); //비우기
					
					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][0] + "=" + data.RTN_DATA.rows[i][1]);

						var chkText = "";
						if(tCheckVal == data.RTN_DATA.rows[i][0])chkText = "checked";

						if(i>0)strSpace = "&nbsp;";
						$("#" + tRadioNm + "-HOLDER").append(strSpace + "<input type=radio name='" + tRadioNm + "' id='" + tRadioNm + "' value='" + data.RTN_DATA.rows[i][0] + "' " + chkText + ">" + data.RTN_DATA.rows[i][1]);
					}
				}else if(tGrptype == "FORMVIEW"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tRadioNm + "-HOLDER").html(""); //비우기

					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i][0] + "=" + data.RTN_DATA.rows[i][1]);

						var chkText = "";
						if(tCheckVal == data.RTN_DATA.rows[i][0])chkText = "checked";

						if(i>0)strSpace = "&nbsp;";
						$("#" + tRadioNm + "-HOLDER").append(strSpace + "<input type=radio name='" + tRadioNm + "' id='" + tRadioNm + "' value='" + data.RTN_DATA.rows[i][0] + "' " + chkText + ">" + data.RTN_DATA.rows[i][1]);
					}
				}else{
					alog("	그룹 타입이 없습니다");
				}

			}else{
				alert("서버 조회중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG);
			}
		},
		error: function(error){
			alert("Error:" + error);
		}
	});

	//alog("   setGridCombo----------------------end");

}


function setCodeCheckSvc(tGrptype, tCheckNm, tPcd, tCheckVal){
	alog("(common) setCodeCheckSvc()----------------------start");
	alog("		tGrptype = " + tGrptype);		
	alog("		tCheckNm = " + tCheckNm);	
	alog("		tPcd = " + tPcd);

	if(tCheckNm == "")return;

	//alert(tCheckVal);
	var arrCheckVal;
	if(tCheckVal == ""){
		 arrCheckVal = new Array();
	}else{
		 arrCheckVal = tCheckVal.split(",");
	}

	//alert(arrCheckVal.length);

	//불러오기
	$.ajax({
		type : "GET",
		url : "/r.d/rd_code_json.php",
		data : {PJTSEQ : 3,PCD : tPcd},
		dataType: "json",
		async: false,
		success: function(data){
			alog("(common) setCodeCheckSvc() json return----------------------");
			//alog("   json data : " + JSON.stringify(data.RTN_DATA));
			//alog("   json RTN_CD : " + data.RTN_CD);
			//alog("   json ERR_CD : " + data.ERR_CD);
			//alog("   json RTN_MSG length : " + data.RTN_MSG.length);

			//그리드에 데이터 반영
			if(data.RTN_CD == "200"){
				if(tGrptype == "GRID"){
					alert("GRID는 지원하지 않는 타입입니다.")
				}else if(tGrptype == "CONDITION"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tCheckNm + "-HOLDER").html(""); //비우기

					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i].data[0] + "=" + data.RTN_DATA.rows[i].data[1]);
						var chkText = "";
						for(var k=0;k<arrCheckVal.length;k++){
							if(arrCheckVal[k] == data.RTN_DATA.rows[i].data[0])chkText = "checked";
						}
						if(i>0)strSpace = "&nbsp;";								
						$("#" + tCheckNm + "-HOLDER").append(strSpace + "<input type=checkbox name='" + tCheckNm + "' id='" + tCheckNm + "' value='" + data.RTN_DATA.rows[i].data[0] + "' " + chkText + ">" + data.RTN_DATA.rows[i].data[1]);
					}
				}else if(tGrptype == "FORMVIEW"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tCheckNm + "-HOLDER").html(""); //비우기

					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i].data[0] + "=" + data.RTN_DATA.rows[i].data[1]);

						var chkText = "";
						for(var k=0;k<arrCheckVal.length;k++){
							if(arrCheckVal[k] == data.RTN_DATA.rows[i].data[0])chkText = "checked";
						}
						if(i>0)strSpace = "&nbsp;";							
						$("#" + tCheckNm + "-HOLDER").append(strSpace + "<input type=checkbox name='" + tCheckNm + "' id='" + tCheckNm + "' value='" + data.RTN_DATA.rows[i].data[0] + "' " + chkText + ">" + data.RTN_DATA.rows[i].data[1]);
					}
				}else{
					alog("	그룹 타입이 없습니다");
				}

			}else{
				alert("서버 조회중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG);
			}
		},
		error: function(error){
			alert("Error:" + error);
		}
	});

	//alog("   setGridCombo----------------------end");

}




function setCodeRadioSvc(tGrptype, tRadioNm, tPcd, tCheckVal){
	alog("(common) setCodeRadioSvc()----------------------start");
	alog("		tGrptype = " + tGrptype);		
	alog("		tRadioNm = " + tRadioNm);	
	alog("		tPcd = " + tPcd);

	if(tRadioNm == "")return;

	//불러오기
	$.ajax({
		type : "GET",
		url : "/r.d/rd_code_json.php",
		data : {PJTSEQ : 3,PCD : tPcd},
		dataType: "json",
		async: false,
		success: function(data){
			alog("(common) setCodeRadioSvc() json return----------------------");
			//alog("   json data : " + JSON.stringify(data.RTN_DATA));
			//alog("   json RTN_CD : " + data.RTN_CD);
			//alog("   json ERR_CD : " + data.ERR_CD);
			//alog("   json RTN_MSG length : " + data.RTN_MSG.length);

			//그리드에 데이터 반영
			if(data.RTN_CD == "200"){
				if(tGrptype == "GRID"){
					alert("GRID는 지원하지 않는 타입입니다.")
				}else if(tGrptype == "CONDITION"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tRadioNm + "-HOLDER").html(""); //비우기

					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i].data[0] + "=" + data.RTN_DATA.rows[i].data[1]);

						var chkText = "";
						if(tCheckVal == data.RTN_DATA.rows[i].data[0])chkText = "checked";

						if(i>0)strSpace = "&nbsp;";
						$("#" + tRadioNm + "-HOLDER").append(strSpace + "<input type=radio name='" + tRadioNm + "' id='" + tRadioNm + "' value='" + data.RTN_DATA.rows[i].data[0] + "' " + chkText + ">" + data.RTN_DATA.rows[i].data[1]);
					}
				}else if(tGrptype == "FORMVIEW"){
					if(!data.RTN_DATA)return;
					alog("	코드수 : " + data.RTN_DATA.rows.length);
					//$("#" + tRadioNm + "-HOLDER").html(""); //비우기

					strSpace = "";
					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i].data[0] + "=" + data.RTN_DATA.rows[i].data[1]);
						
						var chkText = "";
						if(tCheckVal == data.RTN_DATA.rows[i].data[0])chkText = "checked";

						if(i>0)strSpace = "&nbsp;";
						$("#" + tRadioNm + "-HOLDER").append(strSpace + "<input type=radio name='" + tRadioNm + "' id='" + tRadioNm + "' value='" + data.RTN_DATA.rows[i].data[0] + "' " + chkText + ">" + data.RTN_DATA.rows[i].data[1]);
					}
				}else{
					alog("	그룹 타입이 없습니다");
				}

			}else{
				alert("서버 조회중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG);
			}
		},
		error: function(error){
			alert("Error:" + error);
		}
	});

	//alog("   setGridCombo----------------------end");

}




function setCodeComboSvc(tGrptype, tCombo, tPcd, tFristNm){
	alog("(common) setCodeComboSvc()----------------------start");
	//alog("		tPcd = " + tPcd);

	if(!tCombo)return;

	//불러오기
	$.ajax({
		type : "GET",
		url : "/r.d/rd_code_json.php",
		data : {PJTSEQ : 3,PCD : tPcd},
		dataType: "json",
		async: false,
		success: function(data){
			alog("(common) setCodeComboSvc() json return----------------------");
			//alog("   json data : " + JSON.stringify(data.RTN_DATA));
			//alog("   json RTN_CD : " + data.RTN_CD);
			//alog("   json ERR_CD : " + data.ERR_CD);
			//alog("   json RTN_MSG length : " + data.RTN_MSG.length);

			//그리드에 데이터 반영
			if(data.RTN_CD == "200"){
				if(tGrptype == "GRID"){
					if(!data.RTN_DATA)return;
					//alog("	코드수 : " + data.RTN_DATA.rows.length);
					
					tCombo.clear(); //비우기
					tCombo.put("",tFristNm);

					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i].data[0] + "=" + data.RTN_DATA.rows[i].data[1]);

						tCombo.put(data.RTN_DATA.rows[i].data[0],data.RTN_DATA.rows[i].data[1]);
					}
				}else if(tGrptype == "CONDITION"){
					if(!data.RTN_DATA)return;
					//alog("	코드수 : " + data.RTN_DATA.rows.length);
					
					tCombo.empty(); //비우기
					tCombo.append("<option value=''>" + tFristNm  + "</option>"); //빈라인 추가

					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i].data[1] + "=" + data.RTN_DATA.rows[i].data[2]);

						tCombo.append("<option value='" + data.RTN_DATA.rows[i].data[0] + "'>" + data.RTN_DATA.rows[i].data[1] + "</option>");
					}
				}else if(tGrptype == "FORMVIEW"){
					if(!data.RTN_DATA)return;
					//alog("	코드수 : " + data.RTN_DATA.rows.length);
					
					tCombo.empty(); //비우기
					tCombo.append("<option value=''>" + tFristNm + "</option>"); //빈라인 추가

					for(var i=0;i<data.RTN_DATA.rows.length;i++){
						//alog(data.RTN_DATA.rows[i].data[1] + "=" + data.RTN_DATA.rows[i].data[2]);

						tCombo.append("<option value='" + data.RTN_DATA.rows[i].data[0] + "'>" + data.RTN_DATA.rows[i].data[1] + "</option>");
					}
				}else{
					alog("	그룹 타입이 없습니다");
				}

			}else{
				alert("서버 조회중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG);
			}
		},
		error: function(error){
			alert("Error:" + error);
		}
	});

	//alog("   setGridCombo----------------------end");

}





function initTEST5(tobjid){
    if($("#" + tobjid )){
        $("#" + tobjid ).append("<option value=''>- select one-</option>");
        $("#" + tobjid ).append("<option value='1'>Apples</option>");
        $("#" + tobjid ).append("<option value='2'>Mongo</option>");
    }

}


function myErrorHandler(type, desc, erData){
    alog("----------myErrorHandler------------start");
    alog("   type : " + type);
    alog("   desc : " + desc);
    alog("   status : " + erData[0].status);
    alog("   responseText : " + erData[0].responseText);
    alog("----------myErrorHandler------------end");
    return false;
}
//dhtmlxError.catchError("LoadXML", myErrorHandler);
//dhtmlxError.catchError("DataStructure",myErrorHandler);



function jsonFormValid(jsonobj, inputid, inputnm, inputval){
    alog("(common) jsonFormValid()------------start");
	if(!jsonobj)return true;
    alog("   jsonobj.REQUARED : " + jsonobj.REQUARED);
    alog("   jsonobj.MIN : " + jsonobj.MIN);
    alog("   jsonobj.MAX : " + jsonobj.MAX);
    alog("   jsonobj.DATASIZE : " + jsonobj.DATASIZE);
    alog("   jsonobj.DATATYPE : " + jsonobj.DATATYPE);
    alog("   inputid : " + inputid);
    alog("   inputnm : " + inputnm);
    alog("   inputval : " + inputval);


    if(jsonobj.REQUARED == "Y" && inputval == ""){   alert(getMsg(validmsg.REQUARED,new Array(inputid, inputnm)) );return false;   }

    alog("(common) jsonFormValid()------------end");
    return true;
}

function getMsg(msg,tarray){
    var RtnVal;
    RtnVal = msg;
    if(isArray(tarray)){
        for(i=0;i<tarray.length;i++){
            RtnVal += tarray[i];
        }
    }
    return RtnVal;
}
function isArray(myArray) {
    return Object.prototype.toString.call(myArray) === "[object Array]";
}


// 따움표 처리
function q(t){
    return t.replace(new RegExp("\"","g"),"\\\"");
}


//정적-특정row의 모든 컬럼 값 가져오기
function getRowsArray(tgrid,trowid){
    //alog("getRowsArray()------------start");

    var RtnVal="";
    var colNum=tgrid.getColumnsNum();
    for(i=0;i<colNum;i++){
        RtnVal += "&c" + i + "=" + tgrid.cells(trowid,i).getValue();
    }
    //alog("getRowsArray()------------end");
    return RtnVal;
}

//정적-특정row의 모든 컬럼 값 가져오기
function getRowsColid(tgrid,trowid,tgrpid, tcols){
    //alog("getRowsColid()------------start");
    //alog("        tgrpid tt : " + tgrpid);

    var RtnVal="";
    var colNum=tgrid.getColumnsNum();
    //alog("   colNum : " + colNum);

    for(i=0;i<colNum;i++){
        //alog("   " + i + " = " + tgrid.getColumnId(i));
		if(tcols != null){
			//alog("   aaa");
			for(j=0;j<tcols.length;j++){
				//alog("   bbb");
				if( tcols[j] == tgrid.getColumnId(i) ){
					//alog("   ccc");
			        RtnVal += "&" + tgrpid + "-" + tgrid.getColumnId(i) + "=" + tgrid.cells(trowid,i).getValue();
				}
			}
		}else{
			//컬럼 정의가 없으면 모든 컬럼 리턴
			RtnVal += "&" + tgrpid + "-" + tgrid.getColumnId(i) + "=" + tgrid.cells(trowid,i).getValue();

		}
    }
    //alog("getRowsColid()------------end");
    return RtnVal;
}

//정적-특정row의 모든 컬럼 값 Map 형태로 가져오기 가져오기
function getRowsColidMap(tgrid,trowid,tgrpid){
    //alog("getRowsColidMap()------------start");
    alog("        tgrpid : " + tgrpid);

    var RtnVal = new Map();
    var colNum=tgrid.getColumnsNum();
    for(i=0;i<colNum;i++){
        alog("   " + i + " = " + tgrid.getColumnId(i));
        RtnVal.put(tgrpid + "_" + tgrid.getColumnId(i), tgrid.cells(trowid,i).getValue());
        //RtnVal += "&" + tgrpid + "_" + tgrid.getColumnId(i) + "=" + tgrid.cells(trowid,i).getValue();
    }
    //alog("       map.size : " + RtnVal.size());
    //alog("getRowsColidMap()------------end");
    return RtnVal;
}

//정적-특정row의 모든 컬럼 값 Map 형태로 가져오기 가져오기
function setRowsColidMap(tmap, tgrid,trowid,tgrpid){
    alog("(common) getRowsColidMap()------------start");
    alog("        tgrpid : " + tgrpid);

    //var RtnVal = new Map();
    var colNum=tgrid.getColumnsNum();
    for(i=0;i<colNum;i++){
        alog("   " + i + " = " + tgrid.getColumnId(i));
        tmap.put(tgrpid + "_" + tgrid.getColumnId(i), tgrid.cells(trowid,i).getValue());
        //RtnVal += "&" + tgrpid + "_" + tgrid.getColumnId(i) + "=" + tgrid.cells(trowid,i).getValue();
    }
    alog("       map.size : " + tmap.size());
    alog("(common) getRowsColidMap()------------end");
    return RtnVal;
}


function clearRowChanged(tGrid3,trid){
    alog("(common) clearRowChanged----------------------------start");
    alog("       tgrid.getColumnCount : " + tGrid3.getColumnCount());
    alog("       trid : " + trid);
	tGrid3.setUserData(trid,"!nativeeditor_status","");
	tGrid3.setRowTextNormal(trid);
    //tGrid3.setRowTextStyle(trid, "font-weight:normal;text-decoration:none;");
    for(var i=0;i<tGrid3.getColumnCount();i++){
        tGrid3.cells(trid,i).cell.wasChanged=false;
    }
}

function saveToGroup(data){
    alog("(common) saveToGroup----------------------------start");
	alog( "      data RTN_CD : " + data.RTN_CD);
	alog( "      data ERR_CD : " + data.ERR_CD);
    if(data.RTN_CD == "200"){
      
        for(var i=0;i<data.GRP_DATA.length;i++){
			alog(i + " GRP_TYPE = " + data.GRP_DATA[i].GRP_TYPE);
			if(data.GRP_DATA[i].GRP_TYPE == "GRID"){
				alog("i[" + i + "] is GRID");
				tGrid = eval("mygrid"+data.GRP_DATA[i].GRPID);
				if(tGrid){
					alog("	is object ");
					saveToGrid(tGrid,data.GRP_DATA[i]);
				}else{
					alog("	is not object ");
				}
			}else if(data.GRP_DATA[i].GRP_TYPE == "GRIDJQX"){
				alog("i[" + i + "] is GRID");
				//tDataAdapter = eval("dataAdapter"+data.GRP_DATA[i].GRPID);

				saveToGridjqx(data.GRP_DATA[i].GRPID,data.GRP_DATA[i]);

			}else if(data.GRP_DATA[i].GRP_TYPE == "GRIDWIX"){
				alog("i[" + i + "] is GRID");
				//tDataAdapter = eval("dataAdapter"+data.GRP_DATA[i].GRPID);

				saveToGridwix(data.GRP_DATA[i].GRPID,data.GRP_DATA[i]);

			
			}else if(data.GRP_DATA[i].GRP_TYPE == "FORMVIEW"){
				alog("i[" + i + "] is FORMVIEW");

				//SEQ_COLID가 있는 경우 해당 입력에 값넣기
				if(data.GRP_DATA[i].SEQ_COLID != ""){
					$("#" + data.GRP_DATA[i].GRPID + "-" + data.GRP_DATA[i].SEQ_COLID).val(data.GRP_DATA[i].NEW_ID);
				}

				msgNotice("[" + data.GRP_DATA[i].GRPID + "]성공적으로 저장되었습니다.[영향받은건수:" + data.GRP_DATA[i].RTN_DATA + "]",3);
			}else{
				alog("i[" + i + "] is not GRID/FORMVIEW");
			}
		}

    }else{
        msgError("서버 저장중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG,3);
    }
}

function saveToGridwix(tGrpId,data){
	alog("(common) saveToGridwix----------------------------start");
	
	alog( "      GRP_TYPE : " + data.GRP_TYPE);
	alog( "      GRPID : " + data.GRPID);
	if(!data.ROWS){
		alog("		ROWS is null");
		return;
	}
	var affectedRows = 0;

	for(var i=0;i<data.ROWS.length;i++){
		alog( "   i : " + i);
		alog( "      OLD_ID : " + data.ROWS[i].OLD_ID);
		alog( "      NEW_ID : " + data.ROWS[i].NEW_ID);
		alog( "      USER_DATA : " + data.ROWS[i].USER_DATA);
		alog( "      AFFECTED_ROWS : " + data.ROWS[i].AFFECTED_ROWS);

		affectedRows = affectedRows + data.ROWS[i].AFFECTED_ROWS;

		if(data.ROWS[i].AFFECTED_ROWS=="-1"){
	        msgError("["+data.GRPID+"] " + data.ROWS[i].NEW_ID + "는 저장 실패",3);
		}else{
			//rid = mygrid.getRowId(j);
			rowId =  data.ROWS[i].ROW_ID;
			rid = data.ROWS[i].OLD_ID;
			if( data.ROWS[i].USER_DATA == "inserted" ){


				$$("wixdt"+data.GRPID).removeRowCss(rowId, "fontStateInsert");

				if(data.ROWS[i].NEW_ID != ""){
					//해당 행의 데이터 가져오기
					rowItem = $$("wixdt"+data.GRPID).data.getItem(rowId);

					//키 컬럼의 값 교체하기
					//alog(grpInfo.get(data.GRPID));
					//alog(grpInfo.get(data.GRPID).KEYCOLID);
					//alog(rowItem[grpInfo.get(data.GRPID).KEYCOLID]);
					//SEQYN이 Y일때만 변경된 값 업데이트
					if(grpInfo.get(data.GRPID).SEQYN == "Y") rowItem[grpInfo.get(data.GRPID).KEYCOLID] = data.ROWS[i].NEW_ID;

					//rowItem.changeState = null;
					rowItem.changeCud = "inserted_end";
					$$("wixdt"+data.GRPID).data.updateItem(rowId, rowItem);

					//$$("wixdt"+data.GRPID).changeId(data.ROWS[i].OLD_ID, data.ROWS[i].NEW_ID);
				}

				alog("	rid [" + rowId + "], rid [" + rid + "] is [inserted]");
			}
			if( data.ROWS[i].USER_DATA == "updated" ){
				//렌더링 비용을 줄이기 위에 배열 한번에 담아놨다가, 일괄 update 렌더링

				$$("wixdt"+data.GRPID).removeRowCss(rowId, "fontStateUpdate");

				//fncDataUpdate 이 함수가 호출되지 않기 때문에 수동처리
				rowItem = $$("wixdt"+data.GRPID).data.getItem(rowId);
				rowItem.changeCud = null;
				rowItem.changeState = null;
				alog(rowItem);
				
				alog("	rid [" + rowId + "], rid [" + rid + "]  is [updated]");
			}
			if( data.ROWS[i].USER_DATA == "deleted" ){

				$$("wixdt"+data.GRPID).removeRowCss(rowId, "fontStateUpdate");
				$$("wixdt"+data.GRPID).remove(rowId); // removes the item with ID=1

				alog("	rid [" + rowId + "], rid [" + rid + "]  is [deleted]");
			}
		}
	}


	//변경 상태 모두 초기화
	//tGrid2.clearChangedState();
	msgNotice("["+data.GRPID+"]성공적으로 저장되었습니다.[처리:" + data.ROWS.length + "건, 영향받은건수:" + affectedRows + "]",3);
}


function saveToGridjqx(tGrpId,data){
    alog("(common) saveToGridjqx----------------------------start");

	alog( "      GRP_TYPE : " + data.GRP_TYPE);
	alog( "      GRPID : " + data.GRPID);
	if(!data.ROWS){
		alog("		ROWS is null");
		return;
	}
	var affectedRows = 0;
	var updateIds = [];
	var deleteIds = [];
	var updateDatas = [];

	for(var i=0;i<data.ROWS.length;i++){
		alog( "   i : " + i);
		alog( "      OLD_ID : " + data.ROWS[i].OLD_ID);
		alog( "      NEW_ID : " + data.ROWS[i].NEW_ID);
		alog( "      USER_DATA : " + data.ROWS[i].USER_DATA);
		alog( "      AFFECTED_ROWS : " + data.ROWS[i].AFFECTED_ROWS);

		affectedRows = affectedRows + data.ROWS[i].AFFECTED_ROWS;

		if(data.ROWS[i].AFFECTED_ROWS=="-1"){
	        msgError("["+data.GRPID+"] " + data.ROWS[i].NEW_ID + "는 저장 실패",3);
		}else{
			//rid = mygrid.getRowId(j);
			rid = data.ROWS[i].OLD_ID;
			if( data.ROWS[i].USER_DATA == "inserted" ){
				updateIds[updateIds.length] = rid;

				oldData = $('#jqxgrid' + tGrpId).jqxGrid('getrowdatabyid', rid);
				alog(oldData);

				oldData.PGMSEQ = data.ROWS[i].NEW_ID;

				_.set(oldData,data.ROWS[i].SEQ_COLID , data.ROWS[i].NEW_ID); //lodash 특정 컬럼 값 바꾸기.

				oldData.changeState = false; //변경 상태 초기화
				oldData.changeCud = ""; //변경 상태 초기화
				updateDatas[updateDatas.length] = oldData;

				alog(oldData);
				alog("	rid [" + rid + "] is [inserted]");
			}
			if( data.ROWS[i].USER_DATA == "updated" ){
				//렌더링 비용을 줄이기 위에 배열 한번에 담아놨다가, 일괄 update 렌더링

				updateIds[updateIds.length] = rid;

				oldData = $('#jqxgrid' + tGrpId).jqxGrid('getrowdatabyid', rid);
				oldData.changeState = false; //변경 상태 초기화
				oldData.changeCud = ""; //변경 상태 초기화
				updateDatas[updateDatas.length] = oldData;
				


				alog("	rid [" + rid + "] is [updated]");
			}
			if( data.ROWS[i].USER_DATA == "deleted" ){
				deleteIds[deleteIds.length] = rid;

				alog("	rid [" + rid + "] is [deleted]");
			}
		}
	}

	//변경 내용 일괄 업데이트.
	if(updateIds.length > 0){
		$('#jqxgrid' + tGrpId).jqxGrid('updaterow', updateIds, updateDatas);
		alog(updateIds.length + " data is [updated]");
	}

	//삭제 내용 일괄 화면에서 삭제.
	if(deleteIds.length > 0){
		$('#jqxgrid' + tGrpId).jqxGrid('deleterow', deleteIds);
		alog(deleteIds.length + " data is [deleted]");
	}




	//변경 상태 모두 초기화
	//tGrid2.clearChangedState();
	msgNotice("["+data.GRPID+"]성공적으로 저장되었습니다.[처리:" + data.ROWS.length + "건, 영향받은건수:" + affectedRows + "]",3);

}

function saveToGrid(tGrid2,data){
    alog("(common) saveToGrid----------------------------start");

	alog( "      GRP_TYPE : " + data.GRP_TYPE);
	alog( "      GRPID : " + data.GRPID);
	if(!data.ROWS){
		alog("		ROWS is null");
		return;
	}
	var affectedRows = 0;
	for(var i=0;i<data.ROWS.length;i++){
		alog( "   i : " + i);
		alog( "      OLD_ID : " + data.ROWS[i].OLD_ID);
		alog( "      NEW_ID : " + data.ROWS[i].NEW_ID);
		alog( "      USER_DATA : " + data.ROWS[i].USER_DATA);
		alog( "      AFFECTED_ROWS : " + data.ROWS[i].AFFECTED_ROWS);

		affectedRows = affectedRows + data.ROWS[i].AFFECTED_ROWS;

		if(data.ROWS[i].AFFECTED_ROWS=="-1"){
	        msgError("["+data.GRPID+"] " + data.ROWS[i].NEW_ID + "는 저장 실패",3);
		}else{
			//rid = mygrid.getRowId(j);
			rid = data.ROWS[i].OLD_ID;
			if( data.ROWS[i].USER_DATA == "inserted" ){
				clearRowChanged(tGrid2,rid);
				
				if(data.ROWS[i].NEW_ID != ""){
					alog("SEQ_COLID : " + data.ROWS[i].NEW_ID);						
					tGrid2.changeRowId(data.ROWS[i].OLD_ID,data.ROWS[i].NEW_ID); //j+10은 서버에서 전달 받은 서버에 저장된 id값
				}

				//SEQ인 경우 SEQ컬럼을 업데이트 (SEQ_COLID)
				if(data.SEQ_COLID != ""){
					alog("SEQ_COLID : " + data.SEQ_COLID);
					tGrid2.cells(data.ROWS[i].NEW_ID,tGrid2.getColIndexById(data.SEQ_COLID)).setValue(data.ROWS[i].NEW_ID);
				}

				alog("	rid [" + rid + "] is [inserted]");
			}
			if( data.ROWS[i].USER_DATA == "updated" ){
				clearRowChanged(tGrid2,rid);

				alog("	rid [" + rid + "] is [updated]");
			}
			if( data.ROWS[i].USER_DATA == "deleted" ){
				tGrid2.deleteRow(rid);

				alog("	rid [" + rid + "] is [deleted]");
			}
		}
	}

	//변경 상태 모두 초기화
	tGrid2.clearChangedState();
	msgNotice("["+data.GRPID+"]성공적으로 저장되었습니다.[처리:" + data.ROWS.length + "건, 영향받은건수:" + affectedRows + "]",3);

}


//그리드 저장 처리
function saveToGridOld(tgrid,data){
    if(data.RTN_CD == "200"){
		if(!data.RTN_MSG){
			msgError("서버 전송후 서버에서 처리 결과를 전송받지 못했습니다.",1);
			return;
		}
        for(var i=0;i<data.RTN_MSG.length;i++){
            alog( "   i : " + i);
            alog( "      OLD_ID : " + data.RTN_MSG[i].OLD_ID);
            alog( "      NEW_ID : " + data.RTN_MSG[i].NEW_ID);
            alog( "      USER_DATA : " + data.RTN_MSG[i].USER_DATA);
            alog( "      AFFECTED_ROWS : " + data.RTN_MSG[i].AFFECTED_ROWS);


            if(data.RTN_MSG[i].AFFECTED_ROWS=="-1"){
                alert(data.RTN_MSG[i].NEW_ID + "는 저장 실패");
            }else{
                //rid = mygrid.getRowId(j);
                rid = data.RTN_MSG[i].OLD_ID;
                if( data.RTN_MSG[i].USER_DATA == "inserted" ){
                    clearRowChanged(tgrid,rid);
					
					if(data.RTN_MSG[i].NEW_ID != ""){
						alog("SEQ_COLID : " + data.RTN_MSG[i].NEW_ID);						
						tgrid.changeRowId(data.RTN_MSG[i].OLD_ID,data.RTN_MSG[i].NEW_ID); //j+10은 서버에서 전달 받은 서버에 저장된 id값
					}

					//SEQ인 경우 SEQ컬럼을 업데이트 (SEQ_COLID)
					if(data.RTN_DATA && data.RTN_DATA.SEQ_COLID != ""){
						alog("SEQ_COLID : " + data.RTN_DATA.SEQ_COLID);
						tgrid.cells(data.RTN_MSG[i].NEW_ID,tgrid.getColIndexById(data.RTN_DATA.SEQ_COLID)).setValue(data.RTN_MSG[i].NEW_ID);
					}

                    alog("	rid [" + rid + "] is [inserted]");
                }
                if( data.RTN_MSG[i].USER_DATA == "updated" ){
                    clearRowChanged(tgrid,rid);

                    alog("	rid [" + rid + "] is [updated]");
                }
                if( data.RTN_MSG[i].USER_DATA == "deleted" ){
                    tgrid.deleteRow(rid);

                    alog("	rid [" + rid + "] is [deleted]");
                }
            }
        }

        //변경 상태 모두 초기화
        tgrid.clearChangedState();
		msgNotice("성공적으로 저장되었습니다.",3);
    }else{
        msgError("서버 저장중 에러가 발생했습니다.\nRTN_CD : " + data.RTN_CD + "\nERR_CD : " + data.ERR_CD + "\nRTN_MSG :" + data.RTN_MSG,3);
    }
}


//메시지
var validmsg = jQuery.parseJSON('{"REQUARED":"[0]는 반드시 입력바랍니다.", "MIN":"this는 [0]이상 입력바랍니다."}');



//var map = new Map();
//map.put("user_id", "atspeed");
//map.get("user_id");
//https://stackoverflow.com/questions/105034/create-guid-uuid-in-javascript
function uuidv4() {
	return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
	  (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
	)
  }
  
//Chart.js
var color;
if(typeof Chart !== 'undefined'){
	color = Chart.helpers.color;
}

//2018.08.29
function formatNumber(a){
	return String(a).replace(/(\d)(?=(?:\d{3})+(?!\d))/g,'$1,');
}

//2018.09.19
function boolen2yn(tmp){
	if(tmp){
		return "Y";
	}else{
		return "N";
	}
}

//2018.09.19
function yn2boolen(tmp){
	if(tmp == "Y"){
		return true;
	}else{
		return false;
	}
}

//2019.08.23
function bt4TableLinkFormatter(value, row) {
	var tarr = value.toString().split("^");//LINK^NM^TARGET
	if(tarr.length==3){
		var target = tarr[2];
		if(tarr[2] == "")target = "_self";
		return '<a href="' + tarr[0] + '" target="' + target + '">' + tarr[1] + '</a>';
	}else{
		return '배열오류';
	}
}

//2019.08.23
function bt4TableMultiLinkFormatter(value, row) {
	var tlinks = value.toString().split(",");//LINK^NM^TARGET,LINK2^NM2^TARGET2
	var rtnVal = "";
	for(i=0;i<tlinks.length;i++){
		var tarr = tlinks[i].toString().split("^");//LINK^NM^TARGET
		if(tarr.length==3){
			var target = tarr[2];
			if(tarr[2] == "")target = "_self";
			if(i>0)rtnVal += "&nbsp;";
			rtnVal += '<a href="' + tarr[0] + '" target="' + target + '">' + tarr[1] + '</a>';
		}else{
			rtnVal += '배열오류';
		}
	}
	return rtnVal;
}

//2020.02.20
function sendFileSummernote(file, el) {
	var form_data = new FormData();
	form_data.append('file', file);
	$.ajax({
	  data: form_data,
	  type: "POST",
	  url: '/common/cg_upload_summernote.php',
	  cache: false,
	  contentType: false,
	  enctype: 'multipart/form-data',
	  processData: false,
	  success: function(url) {
		$(el).summernote('editor.insertImage', url);
		$('#imageBoard > ul').append('<li><img src="'+url+'" width="480" height="auto"/></li>');
	  }
	});
}

