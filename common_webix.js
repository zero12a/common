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
    alog("  newObj1 = " + newStr);
    if(oldStr == newStr)return false; //바뀐거 없으면 그냥 리턴


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
    alog("  rowItem1=" + JSON.stringify(rowItem));
    if(
        typeof rowItem != "undefined" 
        && typeof rowItem.changeState != "undefined" 
        && rowItem.changeState == true 
        && rowItem.changeCud == "updated"){

        //this.addRowCss(rowId, "fontStateUpdate");

    }
    alog("  rowItem2=" + JSON.stringify(rowItem));
    if(state.value != state.old){
        webix.message("Cell value " + editor.row + " was changed");

    }  
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
