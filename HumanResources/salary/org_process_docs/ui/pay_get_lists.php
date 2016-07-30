<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.01.22
//---------------------------
require_once '../../../header.inc.php';
require_once inc_dataGrid;

require_once '../js/pay_get_lists.js.php';

if($_POST['list_type']==EXTRA_WORK_LIST){   
   $task = "EXTRA_WORK_LIST" ; 
   $title = "لسیت اضافه کار" ; 
   }

if($_POST['list_type']==MISSION_LIST){
   $task = "MISSION_LIST" ;  
   $title = "لیست ماموریت" ;
   }
else if($_POST['list_type']==PAY_GET_LIST){    
   $task = "PAY_GET_LIST" ;  
   $title = "لیست مزایای موردی" ;
   $ItemTypTitle = "نوع مزایا"  ; 
   $ItemTyp = "1" ;
   
   }
 else if($_POST['list_type']==DEC_PAY_GET_LIST){    
   $task = "DEC_PAY_GET_LIST" ;  
   $title = "لیست کسورات موردی" ;
   $ItemTypTitle = "نوع کسور";
   $ItemTyp = "2" ;
   }
   
$dg = new sadaf_datagrid("PGGrid", $js_prefix_address . "../data/pay_get_lists.data.php?task=".$task , "PGDIV");

$col = $dg->addColumn("کد مرکز ", "cost_center_title" , "string" , true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("نوع لیست", "list_type" , "int" , true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("نوع لیست", "list_title" , "string" , true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("شماره لیست", "list_id", "int");
$col->width = 100;

$col = $dg->addColumn("تاریخ", "list_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->editor = ColumnEditor::SHDateField();
$col->width = 90;

$col = $dg->addColumn("مرکز هزینه", "cost_center_id", "int");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_costCenter(), "cost_center_id", "title");

$col = $dg->addColumn("وضعیت", "doc_state", "int");
$col->editor = ColumnEditor::ComboBox(manage_domains::DRP_Doc_State(), "value", "caption");
$col->width = 90;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return PGList.opRender(v,p,r);}";
$col->width = 50;

$dg->addButton = true;
$dg->addHandler = "function(){PGListObject.AddPGList();}";

$dg->pageSize = "20";
$dg->EnableSearch = false ;
$dg->width = 600;
$dg->height = 630;
$dg->title = $title ; 
$dg->autoExpandColumn = "cost_center_id";
$dg->DefaultSortField = "list_date";

    $dg->enableRowEdit = true ;
    $dg->rowEditOkHandler = "function(v,p,r){ return PGListObject.editPGList(v,p,r);}";

$grid = $dg->makeGrid_returnObjects();
//..............................................................................
if($_POST['list_type']==EXTRA_WORK_LIST)  {
    $mdg = new sadaf_datagrid("MemberPGGrid", $js_prefix_address . "../data/pay_get_lists.data.php?task=MemberPGList", "MemberPGDIV");

    $col = $mdg->addColumn("شناسه", "list_row_no", "int",true);
    $col->renderer = "function(v){ return ' '; }" ;

    $col = $mdg->addColumn("نام ", "pfname", "string",true);
    $col->renderer = "function(v){ return ' '; }" ;

    $col = $mdg->addColumn("نام خانوادگی", "plname", "string",true);
    $col->renderer = "function(v){ return ' '; }" ;
    
    $col = $mdg->addColumn("نام و نام خانوادگی", "staff_id", "int");
    $col->renderer = "function(v,p,r){return r.data.pfname + '  ' + r.data.plname }";
    $col->editor = "PGListObject.personCombo";
       

    $col = $mdg->addColumn("ساعت عادی", "approved_amount", "int");
    $col->editor = ColumnEditor::NumberField();
    $col->width = 80;

    $col = $mdg->addColumn("ساعت تشویقی", "initial_amount", "int");
    $col->editor = ColumnEditor::NumberField(true);
    $col->width = 100;           

    $col = $mdg->addColumn("توضیحات", "comments", "int");
    $col->editor = ColumnEditor::TextField(true);
    $col->width = 250;

    $col = $mdg->addColumn("عملیات", "", "string");
    $col->renderer = "function(v,p,r){return PGList.opRenderMembers(v,p,r);}";
    $col->width = 50;

    $mdg->addButton = true;
    $mdg->addHandler = "function(){PGListObject.AddMember();}";
    $mdg->addButton("", "حذف لیست", "user_delete", "function(){PGListObject.DelPGListItems();}"); 
    
    $mdg->enableRowEdit = true ;
    $mdg->rowEditOkHandler = "function(v,p,r){ return PGListObject.editMember(v,p,r);}";  
    
    $mdg->pageSize = "20";
    $mdg->EnableSearch = false ;
    $mdg->width = 760;
    $mdg->height = 630;
    $mdg->title = "لیست افراد" ;
    $mdg->autoExpandColumn = "staff_id";
    $mdg->DefaultSortField = "plname";
    $mdg->DefaultSortDir = "ASC" ; 
    $mdg->notRender = true ; 

    $mgrid = $mdg->makeGrid_returnObjects();    
}
else if($_POST['list_type']==MISSION_LIST) {   

    $mdg = new sadaf_datagrid("MemberPGGrid", $js_prefix_address . "../data/pay_get_lists.data.php?task=MemberPGList", "MemberPGDIV");

    $col = $mdg->addColumn("شناسه", "list_row_no", "int",true);
    $col->renderer = "function(v){ return ' '; }" ;
    
    $col = $mdg->addColumn("شناسه", "list_id", "int",true);
    $col->renderer = "function(v){ return ' '; }" ;

    $col = $mdg->addColumn("نام ", "pfname", "string",true);
    $col->renderer = "function(v){ return ' '; }" ;

    $col = $mdg->addColumn("نام خانوادگی", "plname", "string",true);
    $col->renderer = "function(v){ return ' '; }" ;
    
    $col = $mdg->addColumn("تاریخ برگه", "doc_date", "int",true);
    $col->renderer = "function(v){ return ' '; }" ;
    
    $col = $mdg->addColumn("شماره برگه", "doc_no", "int",true);
    $col->renderer = "function(v){ return ' '; }" ;
    
    $col = $mdg->addColumn("توضیحات", "comments", "int",true);
    $col->renderer = "function(v){ return ' '; }" ;

    $col = $mdg->addColumn("نام و نام خانوادگی", "staff_id", "int");
    $col->renderer = "function(v,p,r){return r.data.pfname + '  ' + r.data.plname }";
    //$col->editor = "PGListObject.personCombo";

    $col = $mdg->addColumn("از تاریخ", "from_date", "int");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 80;
    
    $col = $mdg->addColumn("تا تاریخ", "to_date", "int");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 80;           

    $col = $mdg->addColumn("مدت به روز", "duration", "int");
    $col->width = 80;
    
    $col = $mdg->addColumn("ضریب منطقه", "region_coef", "int");
    $col->width = 80;
    
    $col = $mdg->addColumn("هزینه سفر", "travel_cost", "int");
    $col->width = 80;
    
    $col = $mdg->addColumn("مقصد", "destination", "int");
    $col->width = 80;
    
    $col = $mdg->addColumn("تسهیلات", "using_facilities", "int");
    $col->width = 80;
      

    $col = $mdg->addColumn("عملیات", "", "string");
    $col->renderer = "function(v,p,r){return PGList.opRenderMission(v,p,r);}";
    $col->width = 50;

    $mdg->addButton = true;
    $mdg->addHandler = "function(){PGListObject.AddMissionMember();}";    
         
    $mdg->pageSize = "20";
    $mdg->EnableSearch = false ;
    $mdg->width = 760;
    $mdg->height = 630;
    $mdg->title = "لیست افراد";
    $mdg->autoExpandColumn = "staff_id";
    $mdg->DefaultSortField = "staff_id";
    $mdg->notRender = true ; 

    $mgrid = $mdg->makeGrid_returnObjects();
    
}
else if($_POST['list_type']==PAY_GET_LIST || $_POST['list_type']==DEC_PAY_GET_LIST )  {
        
    $mdg = new sadaf_datagrid("MemberPGGrid", $js_prefix_address . "../data/pay_get_lists.data.php?task=MemberPGList", "MemberPGDIV");

    $col = $mdg->addColumn("شناسه", "list_row_no", "int",true);
    $col->renderer = "function(v){ return ' '; }" ;

    $col = $mdg->addColumn("نام ", "pfname", "string",true);
    $col->renderer = "function(v){ return ' '; }" ;

    $col = $mdg->addColumn("نام خانوادگی", "plname", "string",true);
    $col->renderer = "function(v){ return ' '; }" ;
    
    $col = $mdg->addColumn("نام و نام خانوادگی", "staff_id", "int");
    $col->renderer = "function(v,p,r){return r.data.pfname + '  ' + r.data.plname }";
    $col->editor = "PGListObject.personCombo";
    
    $col = $mdg->addColumn($ItemTypTitle, "salary_item_type_id", "string");
    $col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_salaryItem($ItemTyp), "salary_item_type_id", "full_title");
    $col->width = 200;

    $col = $mdg->addColumn("مقدار کارکرد ", "approved_amount", "int");
    $col->editor = ColumnEditor::NumberField(true);
    $col->width = 80; 
    
    $col = $mdg->addColumn("مبلغ", "value", "int");
    $col->editor = ColumnEditor::NumberField(true);
    $col->width = 80; 

    $col = $mdg->addColumn("توضیحات", "comments", "int");
    $col->editor = ColumnEditor::TextField(true);
    $col->width = 200;

    $col = $mdg->addColumn("عملیات", "", "string");
    $col->renderer = "function(v,p,r){return PGList.opRenderMembers(v,p,r);}";
    $col->width = 50;

    $mdg->addButton = true;
    $mdg->addHandler = "function(){PGListObject.AddMember();}";
        
    $mdg->enableRowEdit = true ;
    $mdg->rowEditOkHandler = "function(v,p,r){ return PGListObject.editMember(v,p,r);}";  
    
    $mdg->pageSize = "20";
    $mdg->EnableSearch = false ;
    $mdg->width = 760;
    $mdg->height = 630;
    $mdg->title = "لیست افراد" ;
    $mdg->autoExpandColumn = "staff_id";
    $mdg->DefaultSortField = "staff_id";
    $mdg->notRender = true ; 

    $mgrid = $mdg->makeGrid_returnObjects();    
    
    $drp_SalaryItm = manage_domains::DRP_SalaryItems("salary_item_type_id","","","","",$ItemTyp);
}
?>
<script>
    PGListObject.list_type = <?=$_POST['list_type']?>; 
    PGListObject.grid = <?=$grid?>;       
    PGListObject.grid.render("PGDIV");
    
    PGList.prototype.LoadPersonInfo = function(){
	
	this.personCombo = new Ext.form.ComboBox({
                store: new Ext.data.Store({
                pageSize: 10,
                model: Ext.define(Ext.id(), {
                extend: 'Ext.data.Model',
                fields:['PersonID','pfname','plname','unit_name','person_type','staff_id','personTypeName',{
                        name : "fullname",
                        convert : function(v,record){return record.data.pfname+" "+record.data.plname;}
                }]
                }),
                remoteSort: true,
                proxy:{
                type: 'jsonp',
                url: '/HumanResources/personal/persons/data/person.data.php?task=searchPerson&cid=' + PGListObject.cost_center_id ,
                reader: {
                root: 'rows',
                totalProperty: 'totalCount'
                }
                }
                }),
                emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
                typeAhead: false,
                listConfig : {
                    loadingText: 'در حال جستجو...'
                },
                pageSize:10,
                width: 550,
                hiddenName : "staff_id",
                valueField : "staff_id",
                displayField : "fullname" ,

                tpl: new Ext.XTemplate(
                        '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
                            ,'<td>کد شخص</td>'
                            ,'<td>نام</td>'
                            ,'<td>نام خانوادگی</td>'
                            ,'<td>نوع شخص</td></tr>',
                        '<tpl for=".">',
                        '<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{personTypeName}</td>'
                            ,'</tr>'
                            ,'</tpl>'
                            ,'</table>')

            });
	
	PGListObject.mgrid = <?=$mgrid?>;
    } 
    
</script>
<center>
<form id="form_PGList" >
	<div id="mainpanelDIV">
    <table id="PGTBL" width="500">   
        <tr>
            <td>شماره لیست :</td><td class="blueText" id="list_id"></td>
        </tr>
	<tr>
            <td>مرکز هزینه:</td><td class="blueText" id="costTitle"></td>
            <td>تاریخ:</td><td class="blueText" id="list_date"></td>
        </tr>
        <tr>
            <td> نوع لیست :</td><td class="blueText" id="listType"></td>
            <td>وضعیت :</td><td class="blueText" id="status"></td>
        </tr>
     <br><br>
    </table>
    </div>
</form>
	<br>
    <div id="MemberPGDIV" style="width:100%"></div>
	<div id="PGDIV" style="width:100%"></div>
	<div> <div id="MissionPanel"></div> </div>    <br>
	
    <div id="SelectItemWindow" class="x-hidden">
    <div id="SelectItemPanel" style="background-color:white">
	<input type="hidden" id="GROUPE_LISTID">
	<input type="hidden" id="GROUPE_LIST_TYPE">
	<input type="hidden" id="GROUPE_COST_CENTER">	
	<span style="color:blue"><br>
	    لطفا قلم مربوط به مزایای موردی گروهی را انتخاب نمایید .<br></span>
	<br>
	انتخاب قلم:<?= $drp_SalaryItm?>
	<br><br>&nbsp;
    </div>
    </div>
</center>
