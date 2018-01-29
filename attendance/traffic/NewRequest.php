<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	96.10
//-------------------------
require_once('../header.inc.php');

$RequestID = !empty($_POST["RequestID"]) ? $_POST["RequestID"] : 0;

if(!empty($_REQUEST["TheDate"]))
{
	$TheDate = DateModules::miladi_to_shamsi($_REQUEST["TheDate"]);
	$type = $_REQUEST["type"];
	$dt = PdoDataAccess::runquery("
		select * from (
			select TrafficTime,s.FromTime,s.ToTime,ExceptFromTime,ExceptToTime
			from ATN_PersonShifts ps join ATN_shifts s on(ps.ShiftID=s.ShiftID)
			join ATN_traffic t on(t.IsActive='YES' AND ps.PersonID=t.PersonID AND t.TrafficDate=:d)
			where ps.IsActive='YES' AND ps.PersonID=:p AND :d between FromDate AND ToDate

			union all

			select StartTime,s.FromTime,s.ToTime,ExceptFromTime,ExceptToTime from ATN_requests r
			join ATN_PersonShifts ps on(ps.IsActive='YES' AND r.PersonID=ps.PersonID
								AND r.FromDate between ps.FromDate AND ps.ToDate)
			join ATN_shifts s on(ps.ShiftID=s.ShiftID)
			where ReqType='CORRECT' AND ReqStatus=".ATN_STEPID_CONFIRM." AND r.PersonID=:p AND r.FromDate=:d
			)t
		order by TrafficTime", array(":p" => $_SESSION["USER"]["PersonID"],":d" => $_REQUEST["TheDate"]));
	//echo PdoDataAccess::GetLatestQueryString();
	if(count($dt) > 0)
	{
		$FromTime = substr($dt[0]["FromTime"],0,5);
		$ToTime = substr($dt[0]["ToTime"],0,5);
		if( DateModules::GetWeekDay($_REQUEST["TheDate"], "l") == "Thursday")
		{
			$FromTime = substr($dt[0]["ExceptFromTime"],0,5);
			$ToTime = substr($dt[0]["ExceptToTime"],0,5);
		}
		$ReqType = "OFF";
		if($_REQUEST["type"] == "Absence")
		{
			if(count($dt) > 3)
			{
				$ST = substr($dt[1]["TrafficTime"],0,5);
				$ET = substr($dt[2]["TrafficTime"],0,5);
			}
			else
			{
				if($dt[0]["TrafficTime"] > $FromTime)
				{
					$ST = substr($dt[0]["FromTime"],0,5);
					$ET = substr($dt[0]["TrafficTime"],0,5);
				}
				if($dt[count($dt)-1]["TrafficTime"] < $ToTime)
				{
					$ST = substr($dt[count($dt)-1]["TrafficTime"],0,5);
					$ET = substr($dt[0]["ToTime"],0,5);
				}
			}
		}
		if($_REQUEST["type"] == "firstAbsence")
		{
			$ST = substr($FromTime,0,5);
			$ET = substr($dt[0]["TrafficTime"],0,5);
			if(count($dt) % 2 == 1)
			{
				$ReqType = "CORRECT";
				$ST = $FromTime;
				$ET = '';
			}
		}
		if($_REQUEST["type"] == "lastAbsence")
		{
			$ST = substr($dt[count($dt)-1]["TrafficTime"],0,5);
			$ET = substr($ToTime,0,5);

			if(count($dt) % 2 == 1)
			{
				$ReqType = "CORRECT";
				$ST = $ToTime;
				$ET = '';
			}
		}
	}
	else
	{
		$ReqType = "DayOFF";
		$ST = '';
		$ET = '';
	}
}
else
{
	$TheDate = '';
	$type = "";
	$ReqType = "DayOFF";
	$ST = '';
	$ET = '';
}
?>
<center>
	<div id="newDiv"></div>
</center>
<script>

NewTrafficRequest.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	TheDate : '<?= $TheDate ?>',
	type : '<?= $type ?>',
	
	RequestID : <?= $RequestID ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function NewTrafficRequest(){
	
	this.formPanel = new Ext.form.Panel({
		renderTo: this.get("newDiv"),                  
		width:588,
		border : false,
		frame : this.RequestID>0 ? false : true,
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
						NewTrafficRequestObject.SetFormElems(records[0].data.id);
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
				value : this.TheDate,
				disabled : this.RequestID>0 ? false : true,
				allowBlank : false	
			},{
				xtype:'shdatefield',
				fieldLabel: 'تا تاریخ',
				value : this.TheDate,
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
			},{
				xtype : "hidden",
				name : "FromDate",
				value : this.TheDate
			}],		
		buttons: [{
				text : "ذخیره",
				itemId : "cmp_save",
				iconCls : "save",
				handler : function(){ NewTrafficRequestObject.SaveRequest();}
			}]
	});
	
	if(this.RequestID > 0)
	{
		new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: this.address_prefix + "traffic.data.php?task=GetAllRequests&RequestID=" + this.RequestID,
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields : ["RequestID","ToDate","ReqStatus","MissionPlace","MissionSubject","MissionStay","GoMean",
						"ReturnMean","GoMeanDesc","ReturnMeanDesc","OffType","OffPersonID","OffTypeDesc",
						"OffFullname","ShiftTitle","ShiftFromTime","ShiftToTime","StartTime",
						"EndTime","details","fullname","ReqDate","ReqType","FromDate","StartTime","EndTime"],
			autoLoad : true,
			listeners :{
				load : function(){
					me = NewTrafficRequestObject;
					me.formPanel.down("[itemId=cmp_save]").hide();
					var record = this.getAt(0);
					me.formPanel.getForm().loadRecord(record);
					me.formPanel.down("[name=FromDate]").setValue(MiladiToShamsi(record.data.FromDate));
					me.formPanel.down("[name=ToDate]").setValue(MiladiToShamsi(record.data.ToDate));

					me.SetFormElems(record.data.ReqType);

					R1 = null;
					if(record.data.ReqType == "OFF" || record.data.ReqType == "DayOFF")
						R1 = me.formPanel.down("[name=OffPersonID]").getStore().load({
							params : { PersonID : record.data.OffPersonID}
						});
					
					var t = setInterval(function(){
						if(R1 == null || !R1.isLoading())
						{
							clearInterval(t);
							NewTrafficRequestObject.formPanel.getEl().readonly();
						}
					}, 100);
				}
			}
		});
		return;
		
	}
	
	this.formPanel.down("[name=ReqType]").setValue('<?= $ReqType ?>');
	this.SetFormElems('<?= $ReqType ?>');
	this.formPanel.down("[name=StartTime]").setValue('<?= $ST ?>');
	this.formPanel.down("[name=EndTime]").setValue('<?= $ET ?>');
}

NewTrafficRequest.prototype.SetFormElems = function(ReqType){

	this.formPanel.down("[itemId=fs_mission]").hide();
	this.formPanel.down("[itemId=fs_off]").hide();
	this.formPanel.down("[name=ShiftID]").hide();

	this.formPanel.down("[name=ToDate]").enable();
	this.formPanel.down("[name=StartTime]").enable();
	this.formPanel.down("[name=EndTime]").enable();

	if(ReqType == "CORRECT" && this.RequestID == 0)
	{
		this.formPanel.down("[name=ToDate]").disable();
		this.formPanel.down("[name=EndTime]").disable();
	}
	if(ReqType == "DayOFF")
	{
		if(this.RequestID == 0)
		{
			this.formPanel.down("[name=OffType]").enable();
			this.formPanel.down("[name=StartTime]").disable();
			this.formPanel.down("[name=EndTime]").disable();
		}
		this.formPanel.down("[itemId=fs_off]").show();
		this.formPanel.down("[name=StartTime]").setValue();
		this.formPanel.down("[name=EndTime]").setValue();
	}
	if(ReqType == "OFF")
	{
		if(this.RequestID == 0)
		{
			this.formPanel.down("[name=OffType]").disable();
			this.formPanel.down("[name=ToDate]").disable();
		}
		this.formPanel.down("[name=OffType]").setValue("2");
		this.formPanel.down("[itemId=fs_off]").show();
	}
	if(ReqType == "DayMISSION")
	{
		if(this.RequestID == 0)
		{
			this.formPanel.down("[name=StartTime]").disable();
			this.formPanel.down("[name=EndTime]").disable();
		}
		this.formPanel.down("[itemId=fs_mission]").show();
		this.formPanel.down("[name=StartTime]").setValue();
		this.formPanel.down("[name=EndTime]").setValue();
	}
	if(ReqType == "MISSION")
	{
		if(this.RequestID == 0)
			this.formPanel.down("[name=ToDate]").disable();
		this.formPanel.down("[itemId=fs_mission]").show();
	}
	if(ReqType == "EXTRA" && this.RequestID == 0)
	{
		this.formPanel.down("[name=ToDate]").disable();
		this.formPanel.down("[name=StartTime]").disable();
		this.formPanel.down("[name=EndTime]").disable();
	}
	if(ReqType == "CHANGE_SHIFT")
	{
		this.formPanel.down("[name=ShiftID]").show();
		if(this.RequestID == 0)
		{
			this.formPanel.down("[name=ToDate]").disable();
			this.formPanel.down("[name=StartTime]").disable();
			this.formPanel.down("[name=EndTime]").disable();
		}
	}
}

NewTrafficRequest.prototype.BeforeAddRequest = function(mode){
	
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

NewTrafficRequest.prototype.SaveRequest = function(){
	
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
			Ext.getCmp(NewTrafficRequestObject.TabID).hide();
		},
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert("",action.result.data);
		}
	});
}

var NewTrafficRequestObject = new NewTrafficRequest();	

</script>