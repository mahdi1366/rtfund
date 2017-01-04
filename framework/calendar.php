<?php
//---------------------------
// programmer:	Sh.Jafarkhani
// create Date:	95.09
//---------------------------
/*DROP TABLE IF EXISTS `krrtfir_rtfund`.`FRW_CalenderEvents`;
CREATE TABLE  `krrtfir_rtfund`.`FRW_CalenderEvents` (
  `EventID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PersonID` int(10) unsigned NOT NULL,
  `EventTitle` varchar(200) NOT NULL,
  `EventDesc` varchar(2000) DEFAULT NULL,
  `ColorID` smallint(5) unsigned NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `IsAllDay` enum('YES','NO') NOT NULL,
  `reminder` enum('YES','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`EventID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
 */
include('header.inc.php');
?>
<link rel="stylesheet" type="text/css" href="/generalUI/ext4/ux/calendar/resources/css/calendar.css" />
<link rel="stylesheet" type="text/css" href="/generalUI/ext4/ux/calendar/resources/css/examples.css" />

<script type="text/javascript">
		
calendar.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	PersonID : '<?= $_SESSION["USER"]["PersonID"] ?>',
	
	EventID : 1,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function calendar()
{
	this.colorsStore = new Ext.data.ArrayStore({
		fields :['ColorID',"title"],
		data : [
			[1,'آبی'],
			[2,'سبز'],
			[3,'قهوه ای']
		]
	});

	// A sample event store that loads static JSON from a local file. Obviously a real
	// implementation would likely be loading remote data via an HttpProxy, but the
	// underlying store functionality is the same.
	this.eventStore = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + 'management/framework.data.php?task=SelectCalenderEvents',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields :  ['EventID',"ColorID","EventTitle","StartDate","EndDate","IsAllDay","reminder"],
		autoLoad : true
	});

	this.calendarPanel = new Ext.calendar.CalendarPanel({
		width : 800,
		height : 600,
		renderTo : this.get("calendar_container"),
		eventStore: this.eventStore,
		calendarStore: this.calendarStore,
		activeItem: 3, // month view

		monthViewCfg: {
			showHeader: true,
			showWeekLinks: true,
			showWeekNumbers: true
		},

		listeners: {
			'eventclick': {
				fn: function(vw, record, el){
					calendarObj.showEditWindow(record);
				},
				scope: this
			},
			'dayclick': {
				fn: function(vw, dt, ad, el){
					var modelClass = calendarObj.eventStore.model;
					var record = new modelClass({
						ColorID : 1,
						StartDate :	dt.format("Y/m/d"),
						EndDate :	dt.format("Y/m/d"),
						AllDay :	ad
						
					});
					calendarObj.showEditWindow(record);
				},
				scope: this
			},
			'eventdelete': {
				fn: function(win, rec){
					this.eventStore.remove(rec);
				},
				scope: this
			}
		}
	});
	// update the header logo date:
	//document.getElementById('logo-body').innerHTML = new Date().getDate();
}

calendar.prototype.showEditWindow = function(record){
	
	if(!this.InfoWin){
		
		this.InfoWin = new Ext.window.Window({
			width : 600,
			autoHeight : true,
			
			items : new Ext.form.Panel({
				layout :{
					type : "table",
					columns : 4
				},
				items :[{
					xtype : "textfield",
					colspan : 3,
					width : 400,
					labelWidth : 80,
					name : "EventTitle",
					allowBlank : false,
					fieldLabel : "عنوان رویداد"
				},{
					xtype : "combo",
					store : this.colorsStore,
					valueField : "ColorID",
					displayField : "title",
					name : "ColorID",
					tpl: new Ext.XTemplate(
						'<tpl for=".">',
							'<div class="x-boundlist-item ext-color-{ColorID}">',
								'<div class="ext-cal-picker-icon">&#160;</div>{title}',
							'</div>',
						'</tpl>'
					)
				},{
					xtype : "shdatefield",
					name : "StartDate",
					labelWidth : 80,
					width : 180,
					allowBlank : false,
					fieldLabel : "تاریخ"
				},{
					xtype : "timefield",
					name : "FromTime",
					width : 100
				},{
					xtype : "timefield",
					name : "ToTime",
					fieldLabel : "تا",
					labelWidth : 20,
					width : 120
				},{
					xtype : "shdatefield",
					name : "EndDate",
					width : 100,
					allowBlank : false
				},{
					xtype : "checkbox",
					name : "IsAllDay",
					inputValue : "YES",
					width : 85,
					labelWidth : 50,
					boxLabel : "کل روز",
					listeners : {
						change : function(){
							if(this.checked)
							{
								this.up('form').down("[name=FromTime]").disable();
								this.up('form').down("[name=ToTime]").disable();
							}
							else
							{
								this.up('form').down("[name=FromTime]").enable();
								this.up('form').down("[name=ToTime]").enable();
							}
						}
					}
				},{
					xtype : "hidden",
					name : "EventID"
				}]
			}),
			buttons :[{
				iconCls : "save",
				text : "ذخیره",
				handler : function(){
					calendarObj.SaveEvent();
				}
			},{
				iconCls : "remove",
				text : "حذف",
				handler : function(){
					calendarObj.DeleteEvent();
				}
			},{
				iconCls : "undo",
				text : "بازگشت",
				handler : function(){
					this.up("window").hide();
				}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.InfoWin);		
	}
	if(record)
		this.InfoWin.down('form').loadRecord(record);
	else
		this.up('window').down('form').down("[name=EventID]").setValue(this.EventID++);
		
	this.InfoWin.show();
	this.InfoWin.center();
};
        
    // The CalendarPanel itself supports the standard Panel title config, but that title
    // only spans the calendar views.  For a title that spans the entire width of the app
    // we added a title to the layout's outer center region that is app-specific. This code
    // updates that outer title based on the currently-selected view range anytime the view changes.
calendar.prototype.updateTitle = function(startDt, endDt){

	var p = this.calendarPanel,
		fmt = Ext.SHDate.format;

	if(Ext.Date.clearTime(startDt).getTime() == Ext.Date.clearTime(endDt).getTime()){
		p.setTitle(fmt(startDt, 'F j, Y'));
	}
	else if(startDt.getFullYear() == endDt.getFullYear()){
		if(startDt.getMonth() == endDt.getMonth()){
			p.setTitle(fmt(startDt, 'F j') + ' - ' + fmt(endDt, 'j, Y'));
		}
		else{
			p.setTitle(fmt(startDt, 'F j') + ' - ' + fmt(endDt, 'F j, Y'));
		}
	}
	else{
		p.setTitle(fmt(startDt, 'F j, Y') + ' - ' + fmt(endDt, 'F j, Y'));
	}
};

Ext.onReady(function(){
	calendarObj = new calendar();
});

calendar.prototype.SaveEvent = function(){

	if(!this.InfoWin.down('form').form.isValid())
		return;
			
	mask = new Ext.LoadMask(calendarObj.InfoWin, {msg:'در حال ذخیره ...'});
	mask.show();
	
	this.InfoWin.down('form').form.submit({
		clientValidation: true,
		url : this.address_prefix + 'management/framework.data.php?task=saveCalenderEvent',
		method : "POST",
		
		success : function(form,action){
			mask.hide();
			if(action.result.success)
				calendarObj.eventStore.load();
			else
				Ext.MessageBox.alert("Error","عملیات مورد نظر با شکست مواجه شد.");

			calendarObj.InfoWin.hide();
		},
		failure : function(){
			mask.hide();
		}
	});
}


	
</script>
<div style="margin:4px" id="calendar_container"></div>
