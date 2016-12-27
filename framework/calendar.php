<?php
//---------------------------
// programmer:	Sh.Jafarkhani
// create Date:	95.09
//---------------------------
include('header.inc.php');
?>
<link rel="stylesheet" type="text/css" href="/generalUI/ext4/ux/calendar/resources/css/calendar.css" />
<link rel="stylesheet" type="text/css" href="/generalUI/ext4/ux/calendar/resources/css/examples.css" />

<script type="text/javascript">


		
calendar.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	PersonID : '<?= $_SESSION["USER"]["PersonID"] ?>',

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function calendar()
{
	this.calendarStore = Ext.create('Ext.calendar.data.MemoryCalendarStore', {
		data: Ext.create('Ext.calendar.data.Calendars')
	});

	// A sample event store that loads static JSON from a local file. Obviously a real
	// implementation would likely be loading remote data via an HttpProxy, but the
	// underlying store functionality is the same.
	this.eventStore = Ext.create('Ext.calendar.data.MemoryEventStore', {
		data: Ext.create('Ext.calendar.data.Events')
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
				fn: function(vw, rec, el){
					calendarObj.showEditWindow(rec, el);
				},
				scope: this
			},
			'eventover': function(vw, rec, el){
				alert(1);
			},
			'eventout': function(vw, rec, el){
			},
			'viewchange': {
				fn: function(p, vw, dateInfo){
					if(calendarObj.editWin){
						calendarObj.editWin.hide();
					};
					if(dateInfo){
						calendarObj.updateTitle(dateInfo.viewStart, dateInfo.viewEnd);
					}
				},
				scope: this
			},
			'dayclick': {
				fn: function(vw, dt, ad, el){
					calendarObj.showEditWindow({
						StartDate: dt,
						IsAllDay: ad
					}, el);
				},
				scope: this
			},
			'rangeselect': {
				fn: function(win, dates, onComplete){
					calendarObj.showEditWindow(dates);
					calendarObj.editWin.on('hide', onComplete, this, {single:true});
				},
				scope: this
			},
			'eventmove': {
				fn: function(vw, rec){
					var mappings = Ext.calendar.data.EventMappings,
						time = rec.data[mappings.IsAllDay.name] ? '' : ' \\a\\t g:i a';

					rec.commit();
				},
				scope: this
			},
			'eventresize': {
				fn: function(vw, rec){
					rec.commit();
				},
				scope: this
			},
			'eventdelete': {
				fn: function(win, rec){
					this.eventStore.remove(rec);
				},
				scope: this
			},
			'initdrag': {
				fn: function(vw){
					if(calendarObj.editWin && calendarObj.editWin.isVisible()){
						calendarObj.editWin.hide();
					}
				},
				scope: this
			}
		}
	});
	// update the header logo date:
	//document.getElementById('logo-body').innerHTML = new Date().getDate();
}

calendar.prototype.showEditWindow = function(rec, animateTarget){
	if(!this.editWin){
		this.editWin = Ext.create('Ext.calendar.form.EventWindow', {
			calendarStore: this.calendarStore,
			listeners: {
				'eventadd': {
					fn: function(win, rec){
						win.hide();
						rec.data.IsNew = false;
						this.eventStore.add(rec);
						this.eventStore.sync();
					},
					scope: this
				},
				'eventupdate': {
					fn: function(win, rec){
						win.hide();
						rec.commit();
						this.eventStore.sync();
					},
					scope: this
				},
				'eventdelete': {
					fn: function(win, rec){
						this.eventStore.remove(rec);
						this.eventStore.sync();
						win.hide();
					},
					scope: this
				},
				'editdetails': {
					fn: function(win, rec){
						win.hide();
						calendarObj.calendarPanel.showEditForm(rec);
					}
				}
			}
		});
	}
	this.editWin.show(rec, animateTarget);
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


	
</script>
<div style="margin:4px" id="calendar_container"></div>
