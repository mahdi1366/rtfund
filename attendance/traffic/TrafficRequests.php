<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.03
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$dg = new sadaf_datagrid("dg", $js_prefix_address . "traffic.data.php?task=GetMyRequests", "grid_div");

$dg->addColumn("", "RequestID", "", true);
$dg->addColumn("", "ToDate", "", true);
$dg->addColumn("", "ReqStatus", "", true);
$dg->addColumn("", "MissionPlace", "", true);
$dg->addColumn("", "MissionSubject", "", true);
$dg->addColumn("", "MissionStay", "", true);
$dg->addColumn("", "GoMean", "", true);
$dg->addColumn("", "ReturnMean", "", true);
$dg->addColumn("", "OffType", "", true);
$dg->addColumn("", "OffPersonID", "", true);
$dg->addColumn("", "SurveyDesc", "", true);

$col = $dg->addColumn("تاریخ درخواست", "ReqDate", GridColumn::ColumnType_datetime);
$col->width = 120;

$col = $dg->addColumn("درخواست", "ReqType", "");
$col->width = 100;
$col->renderer = "TrafficReq.ReqTypeRender";

$col = $dg->addColumn("تاریخ مورد نظر", "FromDate");
$col->renderer = "function(v,p,r){return MiladiToShamsi(v) + ' - ' + MiladiToShamsi(r.data.ToDate);  }";
$col->width = 140;

$col = $dg->addColumn("ساعت", "StartTime");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("تا ساعت", "EndTime", "");
$col->width = 60;
$col->align = "center";

$dg->addColumn("توضیحات", "details", "");

if($accessObj->AddFlag)
	$dg->addButton("","ایجاد درخواست جدید", "add", "function(){TrafficReqObject.BeforeAddRequest('new');}");

$dg->height = 500;
$dg->width = 750;
$dg->DefaultSortField = "ReqDate";
$dg->autoExpandColumn = "details";
$dg->emptyTextOfHiddenColumns = true;

$col = $dg->addColumn("عملیات", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return TrafficReq.OperationRender(v,p,r);}";
$col->width = 50;

$grid = $dg->makeGrid_returnObjects();

?>
<center>
    <form id="mainForm">
		<div id="newDiv"></div> <br>
        <div id="grid_div"></div>
    </form>
</center>
<script>

TrafficReq.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function TrafficReq(){
	
	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.ReqStatus == "3")
			return "pinkRow";
		return "";
	}	
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
						[ "DayOFF", "مرخصی روزانه" ],
						[ "OFF", "مرخصی ساعتی" ],
						[ "DayMISSION", "ماموریت روزانه" ],
						[ "MISSION", "ماموریت ساعتی" ]
					]
				}),
				fieldLabel: 'نوع درخواست',
				name: 'ReqType',
				valueField : "id",
				displayField : "title",
				allowBlank : false,
				listeners :{
					select : function(combo,records){
						TrafficReqObject.SetFormElems(records[0].data.id);
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
				format : "H:i",
				hideTrigger : true,
				submitFormat : "H:i:s"
			},{
				xtype:'timefield',
				fieldLabel: 'تا ساعت',
				name: 'EndTime',
				hideTrigger : true,
				format : "H:i",
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
				handler : function(){ TrafficReqObject.SaveRequest();}
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){
					TrafficReqObject.formPanel.hide();
				}
			}]
	});
}

TrafficReq.prototype.SetFormElems = function(ReqType){

	this.formPanel.down("[itemId=fs_mission]").hide();
	this.formPanel.down("[itemId=fs_off]").hide();

	this.formPanel.down("[name=ToDate]").enable();
	this.formPanel.down("[name=StartTime]").enable();
	this.formPanel.down("[name=EndTime]").enable();

	if(ReqType == "CORRECT")
	{
		this.formPanel.down("[name=ToDate]").disable();
		this.formPanel.down("[name=EndTime]").disable();
	}
	if(ReqType == "DayOFF")
	{
		this.formPanel.down("[name=OffType]").enable();
		this.formPanel.down("[name=StartTime]").disable();
		this.formPanel.down("[name=EndTime]").disable();
		this.formPanel.down("[itemId=fs_off]").show();
		this.formPanel.down("[name=StartTime]").setValue();
		this.formPanel.down("[name=EndTime]").setValue();
	}
	if(ReqType == "OFF")
	{
		this.formPanel.down("[name=OffType]").setValue("2");
		this.formPanel.down("[name=OffType]").disable();
		this.formPanel.down("[name=ToDate]").disable();
		this.formPanel.down("[itemId=fs_off]").show();
	}
	if(ReqType == "DayMISSION")
	{
		this.formPanel.down("[name=StartTime]").disable();
		this.formPanel.down("[name=EndTime]").disable();
		this.formPanel.down("[itemId=fs_mission]").show();
		this.formPanel.down("[name=StartTime]").setValue();
		this.formPanel.down("[name=EndTime]").setValue();
	}
	if(ReqType == "MISSION")
	{
		this.formPanel.down("[name=ToDate]").disable();
		this.formPanel.down("[itemId=fs_mission]").show();
	}
}

TrafficReq.prototype.BeforeAddRequest = function(mode)
{
	this.formPanel.getForm().reset();
	if(mode == "edit")
	{
		var record = this.grid.getSelectionModel().getLastSelected();
		this.formPanel.getForm().loadRecord(record);
		this.formPanel.down("[name=FromDate]").setValue(MiladiToShamsi(record.data.FromDate));
		this.formPanel.down("[name=ToDate]").setValue(MiladiToShamsi(record.data.ToDate));
		
		this.SetFormElems(record.data.ReqType);
		
		if(record.data.ReqType == "OFF" || record.data.ReqType == "DayOFF")
			this.formPanel.down("[name=OffPersonID]").getStore().load({
					params : { PersonID : record.data.OffPersonID}
				});
	}
	this.formPanel.show();
}

TrafficReq.prototype.SaveRequest = function(){
	
	ReqType = this.formPanel.down("[name=ReqType]").getValue();
	switch(ReqType)
	{
		case "CORRECT":
			if(this.formPanel.down("[name=StartTime]").getValue() == null)
			{
				Ext.MessageBox.alert("","ورود ساعت مربوطه الزامی است");
				return;
			}
			break;
		case "DayOFF":
		case "DayMISSION":
			if(ReqType == "DayOFF" && this.formPanel.down("[name=OffType]").getValue() == null)
			{
				Ext.MessageBox.alert("","انتخاب نوع مرخصی الزامی است");
				return;
			}
			if(this.formPanel.down("[name=ToDate]").getValue() == null)
			{
				Ext.MessageBox.alert("","ورود تاریخ انتها الزامی است");
				return;
			}
			if(this.formPanel.down("[name=ToDate]").getValue().format("Y-m-d") < 
				this.formPanel.down("[name=FromDate]").getValue().format("Y-m-d"))
			{
				Ext.MessageBox.alert("","تاریخ انتها نمی تواند کمتر از تاریخ ابتدا باشد");
				return;
			}
			break;
		case "OFF":
		case "MISSION":
			if(this.formPanel.down("[name=StartTime]").getValue() == null ||
				this.formPanel.down("[name=EndTime]").getValue() == null)
			{
				Ext.MessageBox.alert("","ورود بازه زمانی مرخصی/ماموریت ساعتی الزامی است");
				return;
			}	
	}
	
	mask = new Ext.LoadMask(this.formPanel, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	this.formPanel.getForm().submit({
		clientValidation: true,
		url : this.address_prefix + 'traffic.data.php?task=SaveRequest',
		method : "POST",

		success : function(form,action){
			mask.hide();
			if(action.result.success)
				TrafficReqObject.grid.getStore().load();
			else
				Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد.");

			TrafficReqObject.formPanel.hide();
		},
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert("",action.result.data);
		}
	});
}

TrafficReq.OperationRender = function(v,p,r)
{
	if(r.data.ReqStatus == "1")
	{
		st = "";
		if(TrafficReqObject.EditAccess)
			st += "<div align='center' title='ویرایش' class='edit' "+
			"onclick='TrafficReqObject.BeforeAddRequest(\"edit\");' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:16px;float:right;height:16'></div>&nbsp;&nbsp;";
		if(TrafficReqObject.RemoveAccess)
			st += "<div align='center' title='حذف' class='remove' "+
			"onclick='TrafficReqObject.DeleteRequest();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:16px;float:left;height:16'></div>";
		
		return st;
	}	
	if(r.data.ReqStatus == "2" && r.data.ReqType == "DayMISSION")
		return "<div align='center' title='چاپ حکم ماموریت' class='print' "+
		"onclick='TrafficReqObject.PrintMission();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:right;height:16'></div>";
	
	if(r.data.ReqStatus == "3")
		return "<div align='center' class='comment' "+
		"data-qtip='دلیل رد درخواست : <b>" + r.data.SurveyDesc + "</b>' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:right;height:16'></div>";
}

TrafficReq.ReqTypeRender = function(v,p,r){
	
	switch(v)
	{
		case "CORRECT" :	return "فراموشی";
		case "DayOFF" :		return "مرخصی روزانه";
		case "OFF" :		return "مرخصی ساعتی";
		case "DayMISSION" :	return "ماموریت روزانه";
		case "MISSION" :	return "ماموریت ساعتی";
	}
}

TrafficReq.prototype.DeleteRequest = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = TrafficReqObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'traffic.data.php',
			params:{
				task: "DeleteRequest",
				RequestID : record.data.RequestID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				TrafficReqObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

TrafficReq.prototype.PrintMission = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	window.open(this.address_prefix + "PrintMission.php?RequestID=" + record.data.RequestID);
}

var TrafficReqObject = new TrafficReq();	

</script>