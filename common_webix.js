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
    },
    dateFormat: "%Y-%m-%d"
};
function logEvent(type, message, args){
    webix.message({ text:message, expire:500 });
    console.log(type);
    console.log(args);
};


var fncDataUpdate = function(id, newObj, oldObj){
    alog("onDataUpdate()............................start");
    alog(id);
    alog(newObj);
    rowId = newObj.id;

    //수정하기 했을때 처리
    if(typeof newObj.changeState == "undefined" || newObj.changeState == null){
        $$("wixdtG2").addRowCss(rowId, "fontStateUpdate");
        newObj.changeState = true;
        newObj.changeCud = "updated";
    }

    //신규입력 서버에서 저장처리 리턴후 처리
    if(typeof newObj.changeState != "undefined" && newObj.changeState == true && newObj.changeCud == "inserted_end" ){
        newObj.changeState = null;
        newObj.changeCud = null;
    }			


    alog(oldObj);
    alog("onDataUpdate()............................end");
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
