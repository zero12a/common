
//캘린더 등 지역화
var getLocalization = function(){
    var localizationobj = {};
    var days = {
        // full day names
        names: ["일", "월", "화", "수", "목", "금", "토"],
        // abbreviated day names
        namesAbbr: ["일", "월", "화", "수", "목", "금", "토"],
        // shortest day names
        namesShort: ["일", "월", "화", "수", "목", "금", "토"]
    };
    var months =  {
        // full month names (13 months for lunar calendards -- 13th month should be "" if not lunar)
        names: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월", ""],
        // abbreviated month names
        namesAbbr: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월", ""]
    };
    localizationobj.loadtext = "로딩중입니다.";
    localizationobj.days = days;
    localizationobj.months = months;
    localizationobj.firstDay = 0;//the first day of the week (0 = Sunday, 1 = Monday, etc)
    localizationobj.currencysymbol = "₩";
    localizationobj.currencysymbolposition = "before";

    return localizationobj;
}

//##################################################################
//##    셀 스타일
//##################################################################
var cellclass = function (rowIndex, columnName, value, data) {
    //alog("cellclass().................start : changeCud=" + data.changeCud);
    //alog("  rowIndex = " + rowIndex);
    //alog("  columnName = " + columnName);
    //alog("  value = " + value);
    //alog("  data = " + JSON.stringify(data));    
    //alog("records=" + dataAdapter.records[rowIndex].ProductName
    //     + ", cachedrecords=" + dataAdapter.cachedrecords[rowIndex].ProductName
    //      + ", originaldata=" + dataAdapter.originaldata[rowIndex].ProductName);

    changeCud = data.changeCud;

    if(changeCud == "updated" || changeCud == "inserted"){
        return "fontBold";   
    }else if(changeCud == "deleted"){
        return "fontLineThrough";   
    }else{
        return "fontNormal";   
    }

};            


//##################################################################
//##    (에디터) textarea
//##################################################################
var fnHtmlCellsrenderer = function (row, columnfield, value, defaulthtml, columnproperties, rowdata) {
    alog("fncHtmlCellsrenderer().....................start");
    //alog(value);
    return '<span style="margin: 4px; margin-top:5px; float: ' + columnproperties.cellsalign + ';">' + _.replace(_.replace(value,/</g,"&lt;"),/>/g,"&gt;") + '</span>';
}

var fnTextAreaCreateeditor = function(row, cellvalue, editor, celltext, cellwidth, cellheight) {
    alog("SRCTXT createeditor()................................start")
    alog(editor);
    alog(cellvalue);
    alog(celltext);

    //var editorElement = $('<textarea id="customTextArea' + row + '"></textarea>').prependTo(editor);
    editor.jqxTextArea({
        height: 80,
        width: 300
    });
}

var fnTextAreaIniteditor = function(row, cellvalue, editor, celltext, pressedChar) {
    alog("SRCTXT initeditor()................................start")
    //editor.find('textarea').val(cellvalue);

    editor.val(cellvalue);
    editor.focus();
}
var fnTextAreaGeteditorvalue = function(row, cellvalue, editor) {
    //return editor.find('textarea').val();
    return editor.val();
}


//##################################################################
//##    (에디터) dropdown
//##################################################################
var fnDropdownGeteditorvalue =  function (row, cellvalue, editor) {
    alog("fnDropdownGeteditorvalue()...................start");
    //alog(cellvalue);
    //alog(editor.find('input').val());
    // return the editor's value.
    return editor.find('input').val();
};

var fnDropdownCellvaluechanging = function (row, datafield, columntype, oldvalue, newvalue) {
    alog("fnDropdownCellvaluechanging()...................start : oldvalue=" + oldvalue + ", newvalue=" + newvalue);
    //alog(newvalue);
    return newvalue;
};

var fnDropdownIniteditor = function (row, cellvalue, editor, celltext){
    alog("fnDropdownIniteditor()...................start");
    //alog(row);
    //alog(cellvalue);
    //alog(editor);
    //alog(celltext);       

    //editor.jqxDropDownList('selectedIndex', 1);
    //editor.jqxDropDownList('selectItem', 'c01');
    var arrVal = cellvalue.split(",");
    editor.jqxDropDownList('uncheckAll');
    for(i=0;i<arrVal.length;i++){
        //alog("체크 "+ i + " = " + arrVal[i]);
        editor.jqxDropDownList('checkItem',arrVal[i]);
    }

    alog("fnDropdownIniteditor...................end");
};

var fnDropdownCreateeditor = function (row, column, editor) {
    alog("fnDropdownCreateeditor()...................start");
    //alog(row);
    //alog(column);
    alog(editor);
    
    editor.jqxDropDownList({
        openDelay: 100,
        closeDelay: 100,
        //dropDownHeight: 250, //펼쳤을때 높이 : autoDropDownHeight true이면 안 먹힘
        autoOpen: true,
        checkboxes: true,
        autoDropDownHeight: true, 
        source: dataAdapterCds.records,   //데이터소스 연결시 3번 호출하는 bug가 있음
        displayMember: "nm", 
        valueMember: "cd",
        placeHolder: "Select :"
    });


    editor.on('checkChange', function (event){
        alog("fnDropdownCreateeditor.checkChange1()....................start");
        if (event.args) {
            var item = event.args.item;
            var value = item.value;
            var label = item.label;
            var checked = item.checked;
        }
        alog("fnDropdownCreateeditor.checkChange()....................end");
    });


    alog("createeditor...................end");
};



//##################################################################
//##    (에디터) combo
//##################################################################
var fnComboGeteditorvalue = function (row, cellvalue, editor) {
    alog("fnComboGeteditorvalue()...................start");
    //alog(cellvalue);
    //alog(editor);
    //alog(editor.find('input').val());
    var item = editor.jqxComboBox('getSelectedItem'); 
    var selVal = "";
    if(item){
        selVal = item.value;
    }
    alog(selVal);

    // return the editor's value.
    return selVal;
};

var fnComboCellvaluechanging = function (row, datafield, columntype, oldvalue, newvalue) {
    alog("fnComboCellvaluechanging()...................start : oldvalue=" + oldvalue + ", newvalue=" + newvalue);
    //alog(newvalue);
    return newvalue;
};

var fnComboIniteditor = function (row, cellvalue, editor, celltext){
    alog("fnComboIniteditor()...................start");
    //alog(row);
    //alog(cellvalue);
    //alog(editor);
    //alog(celltext);       

    editor.jqxComboBox('clearSelection'); //기존 선택 초기화
    editor.jqxComboBox('selectItem',cellvalue);

    alog("initeditor2()...................end");
};

var fnComboCreateeditor = function (row, column, editor) {
    alog("fnComboCreateeditor()...................start");
    //alog(row);
    //alog(column);
    //alog(editor);
    
    editor.jqxComboBox({ 
        openDelay: 100,
        closeDelay: 100,
        //dropDownHeight: 250, //펼쳤을때 높이 : autoDropDownHeight true이면 안 먹힘
        autoOpen: true, 
        source: dataAdapterCds.records,  //데이터소스 연결시 3번 호출하는 bug가 있음
        displayMember: "nm", 
        valueMember: "cd",
        autoComplete: true,
        autoDropDownHeight: true,
        placeHolder: "Select :"
    });
    alog("createeditor2()...................end");
};

//##################################################################
//##    (에디터) date
//##################################################################
var fnDateCellvaluechanging = function (row, datafield, columntype, oldvalue, newvalue) {
    alog("fnDateCellvaluechanging()...................start");
    alog("  oldvalue=" + oldvalue);
    alog("  newvalue=" + newvalue);
    if(_.isDate(oldvalue) && _.isDate(newvalue) ){
        dateDiff = Math.abs(oldvalue - newvalue);
        if(dateDiff == 0){
            return oldvalue;
        }else{
            return newvalue;
        }
    }else if(oldvalue == "" && newvalue == null){
        return oldvalue;
    }else{
        //alog(newvalue);
        return newvalue;
    }
};




//##################################################################
//##    고급 필터 filter
//##################################################################
var fnDropdownCreatefilterwidget = function (column, htmlElement, editor) {
    alog("dropdown.createfilterwidget().............start()");
    alog(column);
    alog(htmlElement);
    alog(editor);
    
    editor.jqxDropDownList({
        source: dataAdapterCdsFilter.records
        ,displayMember: "nm", valueMember: "cd", placeHolder: "Select:" });
}


var fnComboCreatefilterwidget = function (column, htmlElement, editor) {
    alog("combo.createfilterwidget().............start()");
    editor.jqxDropDownList({
        source: dataAdapterCdsFilter.records
        ,displayMember: "nm", valueMember: "cd", checkboxes: false, placeHolder: "Select:" });
}



