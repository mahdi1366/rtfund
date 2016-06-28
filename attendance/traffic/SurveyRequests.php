<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.03
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "traffic.data.php?task=GetAllRequests", "grid_div");

$dg->addColumn("", "RequestID", "", true);
$dg->addColumn("", "ToDate", "", true);
$dg->addColumn("", "ReqStatus", "", true);
$dg->addColumn("", "MissionPlace", "", true);
$dg->addColumn("", "MissionSubject", "", true);
$dg->addColumn("", "MissionStay", "", true);
$dg->addColumn("", "GoMean", "", true);
$dg->addColumn("", "ReturnMean", "", true);
$dg->addColumn("", "GoMeanDesc", "", true);
$dg->addColumn("", "ReturnMeanDesc", "", true);
$dg->addColumn("", "OffType", "", true);
$dg->addColumn("", "OffPersonID", "", true);
$dg->addColumn("", "OffTypeDesc", "", true);
$dg->addColumn("", "OffFullname", "", true);
$dg->addColumn("", "SurveyFullname", "", true);
$dg->addColumn("", "SurveyDate", "", true);

$col = $dg->addColumn("درخواست کننده", "fullname");
$col->renderer = "SurveyRequests.ReqRender";
$col->width = 120;

$col = $dg->addColumn("تاریخ درخواست", "ReqDate", GridColumn::ColumnType_datetime);
$col->width = 120;

$col = $dg->addColumn("درخواست", "ReqType", "");
$col->width = 60;
$col->renderer = "SurveyRequests.ReqTypeRender";

$col = $dg->addColumn("تاریخ مورد نظر", "FromDate");
$col->renderer = "function(v,p,r){return MiladiToShamsi(v) + ' - ' + MiladiToShamsi(r.data.ToDate);  }";
$col->width = 140;

$col = $dg->addColumn("ساعت", "StartTime");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("تا ساعت", "EndTime", "");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("توضیحات", "details", "");
$col->ellipsis = 40;

$col = $dg->addColumn("عملیات", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return SurveyRequests.OperationRender(v,p,r);}";
$col->width = 60;

$dg->height = 500;
$dg->width = 750;
$dg->autoExpandColumn = "details";
$dg->DefaultSortField = "ReqDate";
$dg->emptyTextOfHiddenColumns = true;

$grid = $dg->makeGrid_returnObjects();

?>
<style>
	.infoTbl td{
		padding : 4px;
	}
</style>
<center>
    <form id="mainForm">
		<div id="newDiv"></div> <br>
        <div id="grid_div"></div>
    </form>
</center>
<script>

SurveyRequests.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function SurveyRequests(){
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("grid_div"));
	
	this.formPanel = new Ext.form.Panel({
		renderTo: this.get("newDiv"),                  
		frame: true,
		title: 'اطلاعات درخواست',
		width:600,
		hidden : true,
		layout : "column",
		columns :2,
		items: [{
			xtype : "container",
			width : 500,
			style : "text-align:right",
			items :[{
				xtype:'combo',
				store : new Ext.data.SimpleStore({
					fields : ["id","title"],
					data : [
						[ "CORRECT", "فراموشی" ],
						[ "OFF", "مرخصی" ],
						[ "MISSION", "ماموریت" ]
					]
				}),
				fieldLabel: 'نوع درخواست',
				name: 'ReqType',
				valueField : "id",
				displayField : "title",
				allowBlank : false,
				listeners :{
					select : function(combo,records){
						
						SurveyRequestsObject.formPanel.down("[itemId=fs_mission]").hide();
						SurveyRequestsObject.formPanel.down("[itemId=fs_off]").hide();
						
						if(records[0].data.id == "CORRECT")
						{
							SurveyRequestsObject.formPanel.down("[name=ToDate]").disable();
							SurveyRequestsObject.formPanel.down("[name=EndTime]").disable();
						}
						else
						{
							SurveyRequestsObject.formPanel.down("[name=ToDate]").enable();
							SurveyRequestsObject.formPanel.down("[name=EndTime]").enable();
							
							if(records[0].data.id == "MISSION")
								SurveyRequestsObject.formPanel.down("[itemId=fs_mission]").show();
							else
								SurveyRequestsObject.formPanel.down("[itemId=fs_off]").show();
						}
					}
				}
			}]
			},{
				xtype:'shdatefield',
				fieldLabel: 'تاریخ مورد نظر',
				name: 'FromDate',
				allowBlank : false	
			},{
				xtype:'shdatefield',
				fieldLabel: 'تا تاریخ',
				name: 'ToDate'
			},{
				xtype:'timefield',
				fieldLabel: 'ساعت',
				name: 'StartTime',
				format : "H:i:s",
				hideTrigger : true,
				submitFormat : "H:i:s"
			},{
				xtype:'timefield',
				fieldLabel: 'تا ساعت',
				name: 'EndTime',
				hideTrigger : true,
				format : "H:i:s",
				submitFormat : "H:i:s"
			},{
				xtype:'textarea',
				fieldLabel: 'توضیحات',
				name: 'details',
				colspan : 2,
				rows : 3,
				width : 530
			},{
				xtype : "fieldset",
				title : "اطلاعات ماموریت",
				colspan : 2,
				itemId : "fs_mission",
				hidden : true,
				layout :{
					type : "table",
					columns :2,
					width : 550
				},
				items : [{
					xtype : "textfield",
					name : "MissionPlace",
					fieldLabel : "محل ماموریت"
				},{
					xtype : "textfield",
					name : "MissionStay",
					fieldLabel : "محل اقامت"
				},{
					xtype : "textfield",
					name : "MissionSubject",
					colspan : 2,
					fieldLabel : "موضوع ماموریت"
				},{
					xtype : "combo",
					store : new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + 'traffic.data.php?task=selectMeans',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields : ['InfoID',"InfoDesc"],
						autoLoad : true
					}),
					queryMode : "local",
					valueField : "InfoID",
					displayField : "InfoDesc",
					name : "GoMean",
					fieldLabel : "وسیله رفت"
				},{
					xtype : "combo",
					store : new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + 'traffic.data.php?task=selectMeans',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields : ['InfoID',"InfoDesc"],
						autoLoad : true
					}),
					queryMode : "local",
					valueField : "InfoID",
					displayField : "InfoDesc",
					name : "ReturnMean",
					fieldLabel : "وسیله برگشت"
				}]
			},{
				xtype : "fieldset",
				title : "اطلاعات مرخصی",
				itemId : "fs_off",
				colspan : 2,
				hidden : true,
				layout :{
					type : "table",
					columns :2,
					width : 550
				},
				items : [{
					xtype : "combo",
					fieldLabel : "نوع مرخصی",
					name : "OffType",
					store : new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + 'traffic.data.php?task=selectOffTypes',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields : ['InfoID',"InfoDesc"],
						autoLoad : true
					}),
					queryMode : "local",
					valueField : "InfoID",
					displayField : "InfoDesc"
				},{
					xtype : "combo",
					fieldLabel : "فرد جایگزین",
					name : "OffPersonID",
					store: new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields :  ['PersonID','fullname']
					}),
					displayField: 'fullname',
					valueField : "PersonID"
				}]
			},{
				xtype : "hidden",
				name : "RequestID"
			}],		
		buttons: [{
				text : "ذخیره",
				iconCls : "save",
				handler : function(){ SurveyRequestsObject.SaveRequest();}
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){
					SurveyRequestsObject.formPanel.hide();
				}
			}]
	});
}

SurveyRequests.OperationRender = function(v,p,r)
{
	if(r.data.ReqStatus == "1")
	{
		return "<div align='center' title='تایید' class='tick' "+
		"onclick='SurveyRequestsObject.ChangeStatus(2);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:right;height:16'></div>&nbsp;&nbsp;&nbsp;&nbsp;" +
	
		"<div align='center' title='رد' class='cross' "+
		"onclick='SurveyRequestsObject.ChangeStatus(3);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:left;height:16'></div>";
	}	
	if(r.data.ReqType == "MISSION" && r.data.ToDate)
		return "<div align='center' title='چاپ حکم ماموریت' class='print' "+
		"onclick='SurveyRequestsObject.PrintMission();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:right;height:16'></div>";
}

SurveyRequests.ReqRender = function(v,p,r)
{
	if(r.data.SurveyFullname)
		p.tdAttr = "data-qtip='<table class=infoTbl>"+
		"<tr><td>تایید کننده : </td><td><b>" + r.data.SurveyFullname + "</b></td></tr>" +
		"<tr><td>تاریخ تایید : </td><td><b>" + MiladiToShamsi(r.data.SurveyDate) + "</b></td></tr></table>'";
	return v;
}

SurveyRequests.prototype.ChangeStatus = function(mode){
	
	actionDesc = mode == 2 ? "تایید" : "رد";
	Ext.MessageBox.confirm("","آیا مایل به " + actionDesc + " می باشید؟", function(btn){
		
		if(btn == "no")
			return;
		
		me = SurveyRequestsObject;
		mask = new Ext.LoadMask(me.formPanel, {msg:'در حال ذخیره سازی ...'});
		mask.show();
		
		var record = me.grid.getSelectionModel().getLastSelected();

		Ext.Ajax.request({
			url : me.address_prefix + 'traffic.data.php?task=ChangeStatus',
			params : {
				RequestID : record.data.RequestID,
				mode : mode
			},
			method : "POST",

			success : function(response){
				mask.hide();
				result = Ext.decode(response.responseText);
				
				if(result.success)
					SurveyRequestsObject.grid.getStore().load();
				else
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد.");
			}
		});
		
	});
}

SurveyRequests.ReqTypeRender = function(v,p,r){
	
	switch(v)
	{
		case "CORRECT" :	return "فراموشی";
		case "DayOFF" :		
			p.tdAttr = "data-qtip='<table class=infoTbl>"+
				"<tr><td>نوع مرخصی : </td><td><b>" + r.data.OffTypeDesc + "</b></td></tr>" +
				"<tr><td>جایگزین : </td><td><b>" + 
					(!r.data.OffFullname ? "-" : r.data.OffFullname) + "</b>" + 
				"</td></tr></table>'";
			return "مرخصی روزانه";
		case "OFF" :		
			p.tdAttr = "data-qtip='<table class=infoTbl>"+
				"<tr><td>نوع مرخصی : </td><td><b>" + r.data.OffTypeDesc + "</b></td></tr>" +
				"<tr><td>جایگزین : </td><td><b>" + 
					(!r.data.OffFullname ? "-" : r.data.OffFullname) + "</b>" + 
				"</td></tr></table>'";
			return "مرخصی ساعتی";
		case "DayMISSION" :	
			p.tdAttr = "data-qtip='<table class=infoTbl>"+
				"<tr><td>محل ماموریت :</td><td><b>" + 
					(!r.data.MissionPlace ? "-" : r.data.MissionPlace) + "</b></td></tr>"+
				"<tr><td>موضوع ماموریت :</td><td><b>" + 
					(!r.data.MissionSubject ? "-" : r.data.MissionSubject ) + "</b></td></tr>"+
				"<tr><td>محل اقامت :</td><td><b>" + 
					(!r.data.MissionStay ? "-" : r.data.MissionStay) + "</b></td></tr>"+
				"<tr><td>وسیله رفت :</td><td><b>" + 
					(!r.data.GoMeanDesc ? "-" : r.data.MissionStay) + "</b></td></tr>"+
				"<tr><td>وسیله برگشت :</td><td><b>" + 
					(!r.data.ReturnMeanDesc ? "-" : r.data.ReturnMeanDesc) + "</b></td></tr>"+
				"</table>'";	
			return "ماموریت روزانه";	
		case "MISSION" :
			p.tdAttr = "data-qtip='<table class=infoTbl>"+
				"<tr><td>محل ماموریت :</td><td><b>" + 
					(!r.data.MissionPlace ? "-" : r.data.MissionPlace) + "</b></td></tr>"+
				"<tr><td>موضوع ماموریت :</td><td><b>" + 
					(!r.data.MissionSubject ? "-" : r.data.MissionSubject ) + "</b></td></tr>"+
				"<tr><td>محل اقامت :</td><td><b>" + 
					(!r.data.MissionStay ? "-" : r.data.MissionStay) + "</b></td></tr>"+
				"<tr><td>وسیله رفت :</td><td><b>" + 
					(!r.data.GoMeanDesc ? "-" : r.data.MissionStay) + "</b></td></tr>"+
				"<tr><td>وسیله برگشت :</td><td><b>" + 
					(!r.data.ReturnMeanDesc ? "-" : r.data.ReturnMeanDesc) + "</b></td></tr>"+
				"</table>'";	
			return "ماموریت ساعتی";
	}
	
	
}

SurveyRequests.prototype.PrintMission = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	window.open(this.address_prefix + "PrintMission.php?RequestID=" + record.data.RequestID);
}

var SurveyRequestsObject = new SurveyRequests();	

</script>