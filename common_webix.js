var webixConfig = {
    calendar: {
        monthFull:["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"],
        monthShort:["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"],
        dayFull:["일", "월", "화", "수", "목", "금", "토"],
        dayShort:["일", "월", "화", "수", "목", "금", "토"],
        hours: "시",
        minutes: "분",
        done:"확인",
        clear: "지우기",
        today: "오늘"
    }
    ,dateFormat: "%Y-%m-%d"
    ,popup_text: {
        view:"popup", 
        body:{view:"textarea", width:450, height:150}
    }
};
function logEvent(type, message, args){
    alog("logEvent().................................start")
    //webix.message({ text:message, expire:500 });
    alog(type);
    alog(message);
    alog(args);
};


var fncDataUpdate = function(id, newObj, oldObj){
    alog("onDataUpdate()............................start");
    alog(this);
    alog(id);
    rowId = newObj.id;

    var oldStr = JSON.stringify(oldObj);
    var newStr = JSON.stringify(newObj)
    alog("  oldObj = " + oldStr);
    //alog("  oldObj(new line) = " + oldStr.replace(/\\r\\n/gi,"\\n"));
    alog("  newObj1 = " + newStr);
    //alog("  newObj1(new line) = " + oldStr.replace(/\\n/gi,"\\n"));
    if(oldStr == newStr || oldStr.replace(/\\r\\n/gi,"\\n") == newStr || oldStr.replace(/\\r/gi,"\\n") == newStr)return false; //바뀐거 없으면 그냥 리턴

    //return;
    //체크박스는 fncAfterEditStop없이 바로 fncDataUpdate만 이벤트 발생함.

    //수정하기 했을때 처리
    if(typeof newObj.changeState == "undefined" || newObj.changeState == null){
        $$(this.owner).addRowCss(rowId, "fontStateUpdate");
        newObj.changeState = true;
        newObj.changeCud = "updated";
    }

    //신규입력 서버에서 저장처리 리턴후 처리
    if(typeof newObj.changeState != "undefined" && newObj.changeState == true && newObj.changeCud == "inserted_end" ){
        newObj.changeState = null;
        newObj.changeCud = null;
    }			


    alog("  newObj2=" + JSON.stringify(newObj));
    alog("onDataUpdate()............................end");
};

var fncAfterEditStop = function(state, editor, ignoreUpdate){
    alog("onAfterEditStop()................................start");
    alog(this);
    //alog(state);
    //alog(editor);
    //alog(ignoreUpdate);
    var rowId = editor.row;
    //this.addRowCss(rowId, "fontStateUpdate");
    var rowItem = this.data.getItem(rowId);
    //alog("  rowItem1=" + JSON.stringify(rowItem));
    if(
        typeof rowItem != "undefined" 
        && typeof rowItem.changeState != "undefined" 
        && rowItem.changeState == true 
        && rowItem.changeCud == "updated"){

        //this.addRowCss(rowId, "fontStateUpdate");

    }
    //alog("  rowItem2=" + JSON.stringify(rowItem));
    if(state.value + "" != state.old + ""){
        webix.message("Cell value " + editor.row + " was changed");
        alog("state.value(" + state.value + ")와 state.old(" + state.old + ")가 같지 않습니다.");
    } else{
        alog("state.value(" + state.value + ")와 state.old(" + state.old + ")가 같습니다.");
    }
    alog("onAfterEditStop()...............................end");
};


var fncIdChange = function(oldid, newid){
    alog("onIdChange()............................start");
    alog("  oldid=" + oldid);
    alog("  newid=" + newid);
};


var fncBeforeFilter  = function(id, value, config){
    alog("onBeforeFilter()............................start");
    alog(id);
    alog(value);

    alog(config);

    //alert($$("wixdtG2").getFilter("start").value);
}

var fncTemplateCodesearch = function(obj,common,value,column,index){
    //alog("fncTemplateCodesearch ().............................start");
    //alog(this); //'this' euqal 'column'
    //alog(obj);
    //(common);
    //alog(value);
    //alog(column);
    //alog(index);
    //alog("__grpId = "  + $$("webix_dt").config.__grpId);
    //obj, which is the full data item (is shown as a table row),
    //common, which contains predefined template elements (will be discussed later in this chapter),
    //value, which is the raw field value (based on the column ID),
    //column, which is the column configuration object,
    //index, which is the current index of the row.

    var rtnVal = "";
    if(typeof obj[this.id] != "undefined"){
        t = obj[this.id] + ""; //형식 nm^cd (정렬시 nm이 먼저활용되게 하기 위함)

        if(t.indexOf("^") >= 0){
            tCd = t.split("^")[1];
            tNm = t.split("^")[0];
            tColor = "";
        }else{
            tCd = "";
            tNm = t;
            tColor = "red";
        }

        grpId = column.__GRPID;
        dataId = obj.id;
        colId = this.id;
        rtnVal = "<div style='float:left;color:" + tColor +";' id='" + tCd + "'>" + tNm + "</div>";
        rtnVal += "<div style='float:right;'>";
        rtnVal += "<img onclick=\"goGridPopOpen('" + grpId + "','" + dataId + "','" + colId + "','" +  tNm + "','" + tCd + "',this)\" src='http://localhost:8070/img/search.png' align='absmiddle' style='width:26px;height:26px;'>";
        rtnVal += "</div>";
    }

    return rtnVal;
}

var fncTemplateLink = function(obj){
    //alog("fncTemplateLink().............................start");
    //alog(this);
    //alog(obj);
    var rtnVal = "";
    if(typeof obj[this.id] != "undefined"){
        t = obj[this.id] + ""; //형식 nm^link^target (정렬시 nm이 먼저활용되게 하기 위함)
        if(t.indexOf("^") >= 0){
            tNm = t.split("^")[0];
            tLink = t.split("^")[1];
            tTarget = t.split("^")[2];
            tColor = "";
        }else{
            tNm = t;
            tLink = "";
            tTarget = "_blank";
            tColor = "red";
        }
        var rtnVal = "<div style='float:left;'><a style='color:" + tColor + "' href='" + tLink + "' target='" + tTarget + "'>" + tNm + "</a></div>";
    }
    return rtnVal;
}