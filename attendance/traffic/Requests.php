<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.03
//-------------------------
require_once('../header.inc.php');
require_once inc_dataGrid;

$AdminMode = isset($_REQUEST["admin"]) && $_REQUEST["admin"] == "true" ? true : false;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................
$task = $AdminMode ? "GetAllRequests" : "GetMyRequests";
$dg = new sadaf_datagrid("dg", $js_prefix_address . "traffic.data.php?task=" . $task, "grid_div");

$dg->addColumn("", "FlowID", "", true);
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
$dg->addColumn("", "SurveyDesc", "", true);
$dg->addColumn("", "IsArchive", "", true);
$dg->addColumn("", "RealExtra", "", true);
$dg->addColumn("", "LegalExtra", "", true);
$dg->addColumn("", "ConfirmExtra", "", true);
$dg->addColumn("", "ShiftTitle", "", true);
$dg->addColumn("", "ShiftFromTime", "", true);
$dg->addColumn("", "ShiftToTime", "", true);
$dg->addColumn("", "StartTime", "", true);
$dg->addColumn("", "EndTime", "", true);

if($AdminMode)
{
	$col = $dg->addColumn("درخواست کننده", "fullname");
	$col->renderer = "TrafficRequests.ReqRender";
	$col->width = 120;
}
$col = $dg->addColumn("تاریخ درخواست", "ReqDate", GridColumn::ColumnType_datetime);
$col->width = 120;

$col = $dg->addColumn("نوع درخواست", "ReqType", "");
$col->renderer = "TrafficRequests.ReqTypeRender";
$col->width = 100;

$col = $dg->addColumn("تاریخ مورد نظر", "FromDate");
$col->renderer = "function(v,p,r){return MiladiToShamsi(v) + ' - ' + MiladiToShamsi(r.data.ToDate);  }";
$col->width = 140;

/*$col = $dg->addColumn("ساعت", "StartTime");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("تا ساعت", "EndTime", "");
$col->width = 60;
$col->align = "center";*/

$col = $dg->addColumn("توضیحات", "details", "");
$col->ellipsis = 40;

$col = $dg->addColumn("وضعیت", "StepDesc", "");
$col->ellipsis = 70;

$col = $dg->addColumn("", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return TrafficRequests.InfoRender(v,p,r);}";
$col->width = 40;

$col = $dg->addColumn("عملیات", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return TrafficRequests.OperationRender(v,p,r);}";
$col->width = 60;

if( !$AdminMode && $accessObj->AddFlag)
	$dg->addButton("","ایجاد درخواست جدید", "add", "function(){TrafficRequestsObject.BeforeAddRequest('new');}");

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

TrafficRequests.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function TrafficRequests(){
	
	this.AllReqsObj = Ext.button.Button({
		xtype: "button",
		text : "مشاهده همه درخواست ها", 
		iconCls : "list",
		enableToggle : true,
		handler : function(){
			me = TrafficRequestsObject;
			me.grid.getStore().proxy.extraParams["AllReqs"] = this.pressed ? "true" : "false";
			me.grid.getStore().load();
		}
	});
	
	this.grid = <?= $grid ?>;
	this.grid.getStore().proxy.extraParams["AllReqs"] = "false";
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.ReqStatus == "3")
			return "pinkRow";
		if(record.data.IsArchive == "YES")
			return "yellowRow";
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
						[ "MISSION", "ماموریت ساعتی" ],
						[ "EXTRA", "اضافه کار غیر مجاز" ],
						[ "CHANGE_SHIFT", "تغییر شیفت روزانه" ]
					]
				}),
				fieldLabel: 'نوع درخواست',
				name: 'ReqType',
				valueField : "id",
				displayField : "title",
				allowBlank : false,
				listeners :{
					select : function(combo,records){
						TrafficRequestsObject.SetFormElems(records[0].data.id);
					}
				}
			}]
			},{
				xtype : "combo",
				colspan : 2,
				width : 400,
				hidden : true,
				fieldLabel: "شیفت",
				store: new Ext.data.Store({
					proxy:{
						type: 'jsonp',
						url: this.address_prefix + '../baseinfo/shift.data.php?task=GetAllShifts',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields :  ['ShiftID','ShiftTitle','FromTime','ToTime',
						{name : "fullDesc",	convert : function(value,record){
								return record.data.ShiftTitle + " [" + record.data.ToTime + " - "
									+ record.data.FromTime + "]";
						} }]
				}),
				displayField: 'fullDesc',
				valueField : "ShiftID",
				name : "ShiftID"
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
				handler : function(){ TrafficRequestsObject.SaveRequest();}
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){
					TrafficRequestsObject.formPanel.hide();
				}
			}]
	});
}

TrafficRequests.InfoRender = function(v,p,r){
	
	var info = "نوع درخواست : <b>";
	switch(r.data.ReqType)
	{
		case "CORRECT" :
			info += "فراموشی" + "</b><hr>" + "<table class=infoTbl>"+
				"<tr><td>تاریخ: </td><td><b>" + MiladiToShamsi(r.data.FromDate) + "</b></td></tr>" +
				"<tr><td>از ساعت : </td><td><b>" + r.data.StartTime + "</b></td></tr>" + 
				"</table>";		
			break;
		case "DayOFF" :		
			info += "مرخصی روزانه" + "</b><hr>" + "<table class=infoTbl>"+
				"<tr><td>نوع مرخصی : </td><td><b>" + r.data.OffTypeDesc + "</b></td></tr>" +
				"<tr><td>جایگزین : </td><td><b>" + 
					(!r.data.OffFullname ? "-" : r.data.OffFullname) + "</b>" + 
				"</td></tr></table>";
			break;
		case "OFF" :		
			info += "مرخصی ساعتی" + "</b><hr>" + "<table class=infoTbl>"+
				"<tr><td>از ساعت : </td><td><b>" + r.data.StartTime + "</b></td></tr>" + 
				"<tr><td>تا ساعت : </td><td><b>" + r.data.EndTime + "</b></td></tr>" + 
				"<tr><td>جایگزین : </td><td><b>" + 
					(!r.data.OffFullname ? "-" : r.data.OffFullname) + "</b>" + 
				"</td></tr></table>";
			break;
		case "DayMISSION" :	
			info += "ماموریت روزانه" + "</b><hr>" + "<table class=infoTbl>"+
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
				"</table>";	
			break;	
		case "MISSION" :
			info += "ماموریت ساعتی" + "</b><hr>" + "<table class=infoTbl>"+
				"<tr><td>از ساعت : </td><td><b>" + r.data.StartTime + "</b></td></tr>" + 
				"<tr><td>تا ساعت : </td><td><b>" + r.data.EndTime + "</b></td></tr>" + 
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
				"</table>";	
			break;
		case "EXTRA" :	
			info += "اضافه کار غیر مجاز" + "</b><hr>" + "<table class=infoTbl>"+
				"<tr><td>تاریخ: </td><td><b>" + MiladiToShamsi(r.data.FromDate) + "</b></td></tr>" +
				"<tr><td>اضافه کار واقعی :</td><td><b>" + 
					DateModule.SecondsToTimeString(r.data.RealExtra) + "</b></td></tr>"+
				"<tr><td>اضافه کار مجاز :</td><td><b>" + 
					DateModule.SecondsToTimeString(r.data.LegalExtra) + "</b></td></tr>"+
				"<tr><td>اضافه کار مجوز :</td><td><b>" + 
					DateModule.SecondsToTimeString(r.data.ConfirmExtra) + "</b></td></tr>"+
				"</table>";
			break ;
		case "CHANGE_SHIFT" :	
			info += "تغییر شیفت روزانه" + "</b><hr>" + "<table class=infoTbl>"+
				"<tr><td>تاریخ: </td><td><b>" + MiladiToShamsi(r.data.FromDate) + "</b></td></tr>" +
				"<tr><td>شیفت: </td><td><b>" + r.data.ShiftTitle + "</b></td></tr>" +
				"<tr><td>از ساعت: </td><td><b>" + r.data.ShiftFromTime + "</b></td></tr>" +
				"<tr><td>تا ساعت: </td><td><b>" + r.data.ShiftToTime + "</b></td></tr>" +
				"</table>";
			break;
	}
	if(r.data.SurveyFullname)
		info += "<hr><table class=infoTbl>"+
		"<tr><td>تایید کننده : </td><td><b>" + r.data.SurveyFullname + "</b></td></tr>" +
		"<tr><td>تاریخ تایید : </td><td><b>" + MiladiToShamsi(r.data.SurveyDate) + "</b></td></tr></table>";
	
	p.tdAttr = "data-qtip='" + info + "'";
	
	return "<div align='center' class='info2' "+
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;float:right;height:16'></div>";
}

TrafficRequests.OperationRender = function(v,p,r){
	
	return "<div  title='عملیات' class='setting' onclick='TrafficRequestsObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

TrafficRequests.ReqTypeRender = function(v,p,r){
	
	switch(v)
	{
		case "CORRECT" :	return "فراموشی";
		case "DayOFF" :		return "مرخصی روزانه";
		case "OFF" :		return "مرخصی ساعتی";
		case "DayMISSION" :	return "ماموریت روزانه";
		case "MISSION" :	return "ماموریت ساعتی";
		case "EXTRA" :		return "اضافه کار غیر مجاز";
		case "CHANGE_SHIFT" :		return "تغییر شیفت روزانه";
	}
}

TrafficRequests.prototype.OperationMenu = function(e){
	
	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	if(record.data.ReqStatus == "<?= ATN_STEPID_RAW ?>")
	{
		if(this.EditAccess)
			op_menu.add({text: 'ویرایش درخواست',iconCls: 'edit', 
			handler : function(){ return TrafficRequestsObject.BeforeAddRequest("edit"); }});
	
		if(this.RemoveAccess)
			op_menu.add({text: 'حذف درخواست',iconCls: 'remove', 
			handler : function(){ return TrafficRequestsObject.DeleteRequest(); }});
	
		op_menu.add({text: 'شروع گردش',iconCls: 'refresh',
			handler : function(){ return TrafficRequestsObject.StartFlow(); }});
	}
	if(record.data.ReqStatus == "0")
	{
		op_menu.add({text: 'برگشت فرم',iconCls: 'return',
		handler : function(){ return TrafficRequestsObject.ReturnStartFlow(); }});
	}
	
	if(record.data.ReqStatus == "<?= ATN_STEPID_CONFIRM ?>" && record.data.ReqType == "DayMISSION")
		op_menu.add({text: 'چاپ حکم ماموریت',iconCls: 'print',
			handler : function(){ return TrafficRequestsObject.PrintMission(); }});
		
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return TrafficRequestsObject.ShowHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}
//..................................................

TrafficRequests.prototype.SetFormElems = function(ReqType){

	this.formPanel.down("[itemId=fs_mission]").hide();
	this.formPanel.down("[itemId=fs_off]").hide();
	this.formPanel.down("[name=ShiftID]").hide();

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
	if(ReqType == "EXTRA")
	{
		this.formPanel.down("[name=ToDate]").disable();
		this.formPanel.down("[name=StartTime]").disable();
		this.formPanel.down("[name=EndTime]").disable();
	}
	if(ReqType == "CHANGE_SHIFT")
	{
		this.formPanel.down("[name=ShiftID]").show();
		this.formPanel.down("[name=ToDate]").disable();
		this.formPanel.down("[name=StartTime]").disable();
		this.formPanel.down("[name=EndTime]").disable();
	}
}

TrafficRequests.prototype.BeforeAddRequest = function(mode){
	
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

TrafficRequests.prototype.SaveRequest = function(){
	
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
				TrafficRequestsObject.grid.getStore().load();
			else
				Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد.");

			TrafficRequestsObject.formPanel.hide();
		},
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert("",action.result.data);
		}
	});
}

TrafficRequests.prototype.DeleteRequest = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = TrafficRequestsObject;
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
				TrafficRequestsObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

//..................................................

TrafficRequests.prototype.beforeChangeStatus = function(mode){
	
	if(mode == 2)
	{
		var record = this.grid.getSelectionModel().getLastSelected();
		if(record.data.ReqType == "EXTRA")
		{
			this.BeforeExtraConfirm();
			return;
		}
		
		this.ChangeStatus(mode, "", 0);
		return;
	}
	
	if(!this.commentWin)
	{
		this.commentWin = new Ext.window.Window({
			width : 412,
			height : 218,
			modal : true,
			title : "دلیل رد درخواست",
			bodyStyle : "background-color:white",
			items : [{
				xtype : "textarea",
				width : 400,
				rows : 6,
				name : "comment"
			}],
			closeAction : "hide",
			buttons : [{
				text : "رد درخواست",				
				iconCls : "cross",
				itemId : "btn_reject"
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.commentWin);
	}
	this.commentWin.down("[itemId=btn_reject]").setHandler(function(){
		TrafficRequestsObject.ChangeStatus(mode, this.up('window').down("[name=comment]").getValue(),0);});
	this.commentWin.show();
	this.commentWin.center();
}

TrafficRequests.prototype.BeforeExtraConfirm = function(){
	
	if(!this.extraWin)
	{
		this.extraWin = new Ext.window.Window({
			width : 250,
			height : 130,
			modal : true,
			title : "اضافه کار غیر مجاز",
			bodyStyle : "background-color:white",
			layout : {
				type : "table",
				columns : 3
			},			
			items : [{
				colspan : 3,
				xtype : "displayfield",
				name : "extra",
				fieldCls : "blueText",
				fieldLabel : "اضافه کار واقعی"
			},{
				colspan : 3,
				xtype : "displayfield",
				name : "LegalExtra",
				fieldCls : "blueText",
				fieldLabel : "اضافه کار مجاز"
			},{
				xtype : "numberfield",
				hideTrigger : true,
				width : 150,
				name : "AllowedExtra_min",
				fieldLabel : "اضافه کار مجوز"
			},{
				xtype : "container",
				width : 10,
				html : ":"
			},{
				xtype : "numberfield",
				name : "AllowedExtra_hour",
				width : 50,
				labelWidth : 10,
				hideTrigger : true
				
			}],
			closeAction : "hide",
			buttons : [{
				text : "تایید",				
				iconCls : "tick",
				itemId : "btn_confirm"
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.extraWin);
		
		this.ExtraStore = new Ext.data.Store({
			proxy : {
				type: 'jsonp',
				url: this.address_prefix + "traffic.data.php?task=GetExtraInfo",
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields : ["extra","LegalExtra"]
		});
	}
	
	this.extraWin.show();
	this.extraWin.center();
	
	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(this.extraWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();
	this.ExtraStore.load({
		params : { RequestID : record.data.RequestID},
		callback : function(){
			me = TrafficRequestsObject;
			record = this.getAt(0);
			me.extraWin.down("[name=extra]").setValue(DateModule.SecondsToTimeString(record.data.extra));
			me.extraWin.down("[name=LegalExtra]").setValue(DateModule.SecondsToTimeString(record.data.LegalExtra));
			arr = DateModule.SecondsToTime(record.data.extra);
			me.extraWin.down("[name=AllowedExtra_hour]").setValue(arr[0]);
			me.extraWin.down("[name=AllowedExtra_min]").setValue(arr[1]);
			
			me.extraWin.down("[itemId=btn_confirm]").setHandler(function(){
				TrafficRequestsObject.ChangeStatus(
					2,'',
					DateModule.TimeToSeconds(
						TrafficRequestsObject.extraWin.down("[name=AllowedExtra_hour]").getValue(),
						TrafficRequestsObject.extraWin.down("[name=AllowedExtra_min]").getValue(),0)
				);
			});
			
			mask.hide();
		}
	});
	
}

TrafficRequests.prototype.ChangeStatus = function(mode, comment, ConfirmExtra){
	
	actionDesc = mode == 2 ? "تایید" : "رد";
	
	Ext.MessageBox.confirm("","آیا مایل به " + actionDesc + " می باشید؟", function(btn){
		
		if(btn == "no")
			return;
		
		me = TrafficRequestsObject;
		mask = new Ext.LoadMask(me.formPanel, {msg:'در حال ذخیره سازی ...'});
		mask.show();
		
		var record = me.grid.getSelectionModel().getLastSelected();

		Ext.Ajax.request({
			url : me.address_prefix + 'traffic.data.php?task=ChangeStatus',
			params : {
				RequestID : record.data.RequestID,
				mode : mode,
				SurveyDesc : comment,
				ConfirmExtra : ConfirmExtra
			},
			method : "POST",

			success : function(response){
				mask.hide();
				if(TrafficRequestsObject.commentWin)
					TrafficRequestsObject.commentWin.hide();
				if(TrafficRequestsObject.extraWin)
					TrafficRequestsObject.extraWin.hide();
				result = Ext.decode(response.responseText);
				
				if(result.success)
					TrafficRequestsObject.grid.getStore().load();
				else
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد.");
			}
		});		
	});
}

TrafficRequests.prototype.PrintMission = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	window.open(this.address_prefix + "PrintMission.php?RequestID=" + record.data.RequestID);
}

TrafficRequests.prototype.ArchiveRequest = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		Ext.MessageBox.alert("", "ابتدا ردیف مورد نظر را انتخاب کنید");
		return;
	}
	if(record.data.ReqStatus == "1")
	{
		Ext.MessageBox.alert("", "هنوز اقدامی روی این درخواست صورت نگرفته و قادر به بایگانی آن نمی باشید");
		return;
	}
	
	actionDesc = "آیا مایل به بایگانی این درخواست می باشید؟"
	Ext.MessageBox.confirm("",actionDesc, function(btn){
		
		if(btn == "no")
			return;
		
		me = TrafficRequestsObject;
		mask = new Ext.LoadMask(me.formPanel, {msg:'در حال بایگانی ...'});
		mask.show();
		
		var record = me.grid.getSelectionModel().getLastSelected();

		Ext.Ajax.request({
			url : me.address_prefix + 'traffic.data.php?task=ArchiveRequest',
			params : {
				RequestID : record.data.RequestID				
			},
			method : "POST",

			success : function(response){
				mask.hide();
				TrafficRequestsObject.grid.getStore().load();
			}
		});		
	});
}

//..................................................

TrafficRequests.prototype.StartFlow = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به شروع گردش می باشید؟",function(btn){
		
		if(btn == "no")
			return;
		
		me = TrafficRequestsObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'traffic.data.php',
			method: "POST",
			params: {
				task: "StartFlow",
				RequestID : record.data.RequestID
			},
			success: function(response){
				mask.hide();
				TrafficRequestsObject.grid.getStore().load();
			}
		});
	});
}

TrafficRequests.prototype.ReturnStartFlow = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به برگشت فرم می باشید؟",function(btn){
		
		if(btn == "no")
			return;
		
		me = TrafficRequestsObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: '/office/workflow/wfm.data.php',
			method: "POST",
			params: {
				task: "ReturnStartFlow",
				FlowID : record.data.FlowID,
				ObjectID : record.data.RequestID
			},
			success: function(response){
				mask.hide();
				TrafficRequestsObject.grid.getStore().load();
			}
		});
	});
}

TrafficRequests.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش درخواست',
			modal : true,
			autoScroll : true,
			width: 700,
			height : 500,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "history.php",
				scripts : true
			},
			buttons : [{
					text : "بازگشت",
					iconCls : "undo",
					handler : function(){
						this.up('window').hide();
					}
				}]
		});
		Ext.getCmp(this.TabID).add(this.HistoryWin);
	}
	this.HistoryWin.show();
	this.HistoryWin.center();
	record = this.grid.getSelectionModel().getLastSelected();
	this.HistoryWin.loader.load({
		params : {
			RequestID : record.data.RequestID,
			ReqType : record.data.ReqType
		}
	});
}

var TrafficRequestsObject = new TrafficRequests();	

</script>