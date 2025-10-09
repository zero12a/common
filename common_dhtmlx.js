//DHTMLX 코드서치팝업 타입 정의
function eXcell_codesearch(cell){ //the eXcell name is defined here
	if (cell){                // the default pattern, just copy it
		this.cell = cell;
		this.grid = this.cell.parentNode.grid;
	}
	this.edit = function(){}  //read-only cell doesn't have edit method
	// the cell is read-only, so it's always in the disabled state
	this.isDisabled = function(){ return true; }
	this.getValue=function(){
		if(this.cell.firstChild == null  || this.cell.firstChild.firstChild == null){
			return "";
		}else{
			return this.cell.firstChild.firstChild.id; // get button label
		}
	}	
	this.setValue=function(val){
		//alog(val);
		var tarr = val.toString().split("^");//CD^NM^GRPID
		if(tarr.length==3){
			var tStr = "";
			var tValue = tarr[0];
			var tText = tarr[1];
			var tGrpId = tarr[2];

			//alog("this.grid = " + this.grid);	
			rowId = this.cell.parentNode.idd;
			colIndex = this.cell.cellIndex;
			//alog("parentNode.idd = " + this.cell.parentNode.idd);
			alog("cell.cellIndex = " + colIndex);
			//alog("this.grid.getUserData(GRPID) = " + this.grid.getUserData("","GRPID"));	
			tStr += "<div style='text-align:right;' ><span id=\"" + tValue + "\" >" + tText + "</span>";
			tStr += "<input type=\"image\" src=\"" + CFG_URL_LIBS_ROOT + "img/search.png\" height=20 style=\"vertical-align:middle;\" onclick=\"goGridPopOpen('" + tGrpId + "','" + rowId + "','" + colIndex + "','" +  tValue + "','" + tText + "',this)\"></div>";
			
			this.setCValue(tStr,tValue);//NM,CD
		}else{
			alog("eXcell_button 배열이 잘못되었습니다." + val);
		}
	}
}



function eXcell_dropdown(a) {
    try {
        this.cell = a;
        this.grid = this.cell.parentNode.grid
    } catch (c) {}
    this.edit = function() {
		//alog("eXcell_dropdown2() edit...................start");
		this.val = this.getValue();
		this.text = this.getText()._dhx_trim();
		//alog("	this.val=" + this.val);
		//alog("	this.text=" + this.text);
        var g = (this.cell._combo || this.grid.clists[this.cell._cellIndex]);
        if (!g) {
            return
        }
		this.obj = document.createElement("DIV");
        var e = this.val.toString().split(",");
        var o = "";
        for (var m = 0; m < g.length; m++) {

            var n = false;
            for (var h = 0; h < e.length; h++) {
				//alog(m + ", " + h + " [" + g[m].cd._dhx_trim() + " = " + e[h] + "]");
                if (g[m].cd._dhx_trim() == e[h]) {
                    n = true
                }
            }
            if (n) {
                o += "<div class='dropDownChkDiv'><input type='checkbox' id='dhx_clist_" + m + "' checked='true' value='" + g[m].cd + "'/><label for='dhx_clist_" + m + "'>" + g[m].nm + "</label></div>"
            } else {
                o += "<div class='dropDownChkDiv'><input type='checkbox' id='dhx_clist_" + m + "'  value='" + g[m].cd + "' /><label for='dhx_clist_" + m + "'>" + g[m].nm + "</label></div>"
            }
        }
        o += "<div><input type='button' value='" + (this.grid.applyButtonText || "Apply") + "' style='width:100%; font-size:8pt;' onclick='this.parentNode.parentNode.editor.grid.editStop();'/></div>";
        this.obj.editor = this;
        this.obj.innerHTML = o;
        document.body.appendChild(this.obj);
        this.obj.style.position = "absolute";
        this.obj.className = "dhx_clist";
        this.obj.onclick = function(r) {
            (r || event).cancelBubble = true;
            return true
        };
        var l = this.grid.getPosition(this.cell);
        this.obj.style.left = l[0] + "px";
        this.obj.style.top = l[1] + this.cell.offsetHeight + "px";
        this.obj.getValue = function() {
            var s = "";
            for (var r = 0; r < this.childNodes.length - 1; r++) {
                if (this.childNodes[r].childNodes[0].checked) {
                    if (s) {
                        s += ","
					}
					s += this.childNodes[r].childNodes[0].value;
                    //s += this.childNodes[r].childNodes[1].innerHTML
                }
            }
            return s.replace(/&amp;/g, "&")
        }
	};
	this.getValue=function(){
        return this.cell.firstChild.getAttribute("value"); // get button label
    };
	this.setValue=function(val){
		//alog("eXcell_dropdown2() setValue................................start");		
		rowId = this.cell.parentNode.idd;
		colIndex = this.cell.cellIndex;

		var g = this.grid.clists[this.cell.cellIndex];
        if (!g) {
            g = new Array();
		}
		var e = val.toString().split(",");
		nm = "";
		for(i=0;i<e.length;i++){
			for (m = 0; m < g.length; m++) {
				//alog(i + "," + m + " [" + e[i]._dhx_trim() + "=" + g[m].cd + "]");
				if(e[i]._dhx_trim() == g[m].cd){
					if(nm != "")nm+=", ";
					nm += g[m].nm;
				}
			}
		}
		if(nm =="")nm=val;
		//alog("nm=" + nm);
		//alog("cd=" + val);
		
		this.setCValue("<span value='" + val + "'>" + nm + "</span>",val);//NM,CD
	};
	this.getText = function() {
		//alog("eXcell_dropdown() getText...................start");
		return this.cell.childNodes[0].innerHTML; // gets the value;
	};

    this.detach = function(e) {
		//alog("eXcell_dropdown() detach...................start");
        if (this.obj) {
            this.setValue(this.obj.getValue());
            this.obj.editor = null;
            this.obj.parentNode.removeChild(this.obj);
            this.obj = null
        }
        return this.val != this.getValue()
    }
}

if(typeof eXcell !== 'undefined'){
	eXcell_codesearch.prototype = new eXcell;// nests all other methods from the base class
	eXcell_dropdown.prototype = new eXcell;// nests all other methods from the base class
}

//체크 필터 정의
if(typeof dhtmlXGridObject !== 'undefined'){



	dhtmlXGridObject.prototype._in_header_dropdown_filter=function(a,b) {
		alog("_in_header_dropdown_filter.......................start")
		alog(a);
		alog(b);
		var self=this;

		a.innerHTML="<div style='align: center'><input type='hidden' id='ckFilter' value=''><div id ='comboContainer'></div></div>";
		//a.innerHTML = "<select style='width: 100%;'><option value></option> <option value='1'>Yes</option><option value='0'>No</option></select>"
		container = a.firstChild;
		ckFilter = a.firstChild.childNodes[0];
		comboBox = a.firstChild.childNodes[1];
		alog(comboBox);

		myCombo = new dhtmlXCombo(comboBox,"comboFilterId");
		//myCombo.addOption(null,"");//addOption(value,label,css,img_src);
		//myCombo.addOption(1, "Yes");
		//myCombo.addOption(0, "No");
		
		a.onselectstart=function(){
			return event.cancelBubble=!0;
		};

		a.onclick=a.onmousedown=function(a){
			return(a||event).cancelBubble=!0;
		};
		
		this.makeFilter(ckFilter, b);
		
		ckFilter._filter=function(){
			var a = this.value;
			return (a == "") ? "" : function(b){
				return a == b;
			}
		}
		
		this._filters_ready();
		
		myCombo.attachEvent("onChange", function(){
			ckFilter.value = myCombo.getSelectedValue();
			self.filterByAll();
		});

		alog("_in_header_dropdown_filter.......................end")
	}

	//alert(1);
	dhtmlXGridObject.prototype._in_header_ck2_filter=function(a,b) {

		var self=this;

		a.innerHTML="<div style='align: center'><input type='hidden' id='ckFilter' value=''><div id ='comboContainer'></div></div>";
		//a.innerHTML = "<select style='width: 100%;'><option value></option> <option value='1'>Yes</option><option value='0'>No</option></select>"
		container = a.firstChild;
		ckFilter = a.firstChild.childNodes[0];
		comboBox = a.firstChild.childNodes[1];

		myCombo = new dhtmlXCombo(comboBox,"comboFilterId");
		myCombo.addOption(null,"");//addOption(value,label,css,img_src);
		myCombo.addOption(1, "Yes");
		myCombo.addOption(0, "No");
		
		a.onselectstart=function(){
			return event.cancelBubble=!0;
		};

		a.onclick=a.onmousedown=function(a){
			return(a||event).cancelBubble=!0;
		};
		
		this.makeFilter(ckFilter, b);
		
		ckFilter._filter=function(){
			var a = this.value;
			return (a == "") ? "" : function(b){
				return a == b;
			}
		}
		
		this._filters_ready();
		
		myCombo.attachEvent("onChange", function(){
			ckFilter.value = myCombo.getSelectedValue();
			self.filterByAll();
		});
	}

	//alert(1);
	dhtmlXGridObject.prototype._in_header_ck_filter_Default=function(a,b) {

		var self=this;

		a.innerHTML="<div style='align: center'><input type='hidden' id='ckFilter' value=''><input type=checkbox id='chkFilter' value='' style='width:18px;height:18px;border: 1px solid #bcbcbc;'></div>";
		//a.innerHTML = "<select style='width: 100%;'><option value></option> <option value='1'>Yes</option><option value='0'>No</option></select>"
		container = a.firstChild;
		ckFilter = a.firstChild.childNodes[0];
		myCheckBox = a.firstChild.childNodes[1];
		//alog(myCheckBox);
	
		this.makeFilter(ckFilter, b);
		
		ckFilter._filter=function(){
			var a = this.value;
			return (a == "") ? "" : function(b){
				return a == b;
			}
		}
		
		this._filters_ready();
		
		myCheckBox.addEventListener( 'change', function() {
			if(this.checked) {
				// Checkbox is checked..
				ckFilter.value = 1;
			} else {
				// Checkbox is not checked..
				ckFilter.value = 0;
			}
			self.filterByAll();
		});

	}

	//best checkbox filter
	dhtmlXGridObject.prototype._in_header_check_filter=function(a,b) {
		alog("_in_header_check_filter().................................start");

		var self=this;

		a.innerHTML="<div style='align: center;'><input type='hidden' id='ckFilter' value=''><div status='empty' style='margin-top:2px;display: inline-block;width:19px;height:19px;text-align:top;line-height:19px;border: 1px solid #bcbcbc;'></div></div>";
		//a.innerHTML = "<select style='width: 100%;'><option value></option> <option value='1'>Yes</option><option value='0'>No</option></select>"
		container = a.firstChild;
		ckFilter = a.firstChild.childNodes[0];
		myDiv = a.firstChild.childNodes[1];
		//alog(myDiv);
	
		this.makeFilter(ckFilter, b);
		
		ckFilter._filter=function(){
			var a = this.value;
			return (a == "") ? "" : function(b){
				return a == b;
			}
		}
		
		this._filters_ready();

		//디펄트
		myDiv.style.backgroundColor="silver";
		myDiv.style.fontSize="8pt";
		myDiv.style.fontWeight="bold";
		myDiv.innerHTML="";
		
		//이벤트
		myDiv.addEventListener( 'mousedown', function() {
			alog(this.getAttribute("status"));
			//step : empty, check, uncheck
			//alert(this.getAttribute("status"));
			if(this.getAttribute("status") == "empty") {
				// Checkbox is checked..
				ckFilter.value = 1;
				this.setAttribute("status","check");
				this.style.backgroundColor="white";
				//this.innerHTML="V";
				var path = CFG_URL_LIBS_ROOT + "lib/dhtmlxSuite/codebase/imgs/dhxgrid_skyblue/item_chk1.gif";
				//alog(path);
				this.style.backgroundImage = "url('" + path + "')";
			}else if(this.getAttribute("status") == "check") {
				// Checkbox is not checked..
				ckFilter.value = 0;
				this.setAttribute("status","uncheck");
				this.style.backgroundColor="white";
				this.innerHTML="";
				var path = CFG_URL_LIBS_ROOT + "lib/dhtmlxSuite/codebase/imgs/dhxgrid_skyblue/item_chk0.gif";
				//alog(path);
				this.style.backgroundImage = "url('" + path + "')";
			}else if(this.getAttribute("status") == "uncheck") {
				// Checkbox is not checked..
				ckFilter.value = "";
				this.setAttribute("status","empty");
				this.style.backgroundColor="silver";
				this.innerHTML="";
				this.style.backgroundImage = "";
			}
			self.filterByAll();
		});

	}

}

//달력 타입 정의
if(typeof dhtmlXCalendarObject !== 'undefined'){
	dhtmlXCalendarObject.prototype.langData["kr"] = {
		// date format
		dateformat: "%Y-%m-%d",

		/// header format
		hdrformat: "%Y년 %F월",
	
		// full names of months
		monthesFNames: [
			"1","2","3","4","5","6","7",
			"8","9","10","11","12"
		],
		// short names of months
		monthesSNames: [
			"1","2","3","4","5","6","7",
			"8","9","10","11","12"
		],
		// full names of days
		daysFNames: [
			"일","월","화","수",
			"목","금","토"
		],
		// short names of days
		daysSNames: [
			"일","월","화","수",
			"목","금","토"
		],
		// starting day of a week. Number from 1(Monday) to 7(Sunday)
		weekstart: 7, 
		// the title of the week number column
		weekname: "w" 
	};
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



function delRow(dGrid){
	alog("(common) delRow--------------------------------------start");
	var tname = dGrid.getUserData("","gridTitle");
	alog("delRow(" + tname + ")------------start");

	alog(1);
	rid = dGrid.getSelectedRowId();
	alog("	target rowrid : " + rid);
	if(rid != null && rid != ""){
		arrRid = rid.split(",");
		for(var i=0;i<arrRid.length;i++){
			alog("	delete rowid : " + arrRid[i]);
			dGrid.setUserData(arrRid[i],"!nativeeditor_status","deleted");
			dGrid.setRowTextBold(arrRid[i]);
			dGrid.cells(arrRid[i],0).cell.wasChanged=true;
		}
	}
	alog("(common) delRow(" + tname + ")------------------------end");
}

function addRow(tGrid,tCols){
	var tname = tGrid.getUserData("","gridTitle");

	alog("(common) addRow(" + tname + ")------------start");

	var id=tGrid.uid();
	alog("	row id : " + id);

	tGrid.addRow(id,tCols,0);
	tGrid.showRow(id);
	tGrid.selectRow(0);
	tGrid.cells(id,0).cell.wasChanged = true;
	tGrid.setUserData(id,"!nativeeditor_status","inserted");
	tGrid.setRowTextBold(id);
	alog("(common) addRow(" + tname + ")------------end");
}

function addRowLast(tGrid,tCols){
	var tname = tGrid.getUserData("","gridTitle");

	alog("(common) addRow(" + tname + ")------------start");

	var id=tGrid.uid();
	alog("	row id : " + id);

	tGrid.addRow(id,tCols);
	tGrid.showRow(id);
	tGrid.selectRow(0);
	tGrid.cells(id,0).cell.wasChanged = true;
	tGrid.setUserData(id,"!nativeeditor_status","inserted");
	tGrid.setRowTextBold(id);
	alog("(common) addRow(" + tname + ")------------end");
}
