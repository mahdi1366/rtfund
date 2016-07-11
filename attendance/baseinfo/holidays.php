<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.01
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................
$dg = new sadaf_datagrid("dg", $js_prefix_address . "shift.data.php?task=GetAllHolidays", "grid_div");

$dg->addColumn("", "HolidayID", "", true);

$col = $dg->addColumn("تاریخ", "TheDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 120;

$col = $dg->addColumn("توضیحات", "details");
$col->editor = ColumnEditor::TextField(true);

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){HolidayObject.AddHoliday();}";
	
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(){return HolidayObject.SaveHoliday();}";
}
if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return Holiday.DeleteRender(v,p,r);}";
	$col->width = 40;
}
$dg->title = "تعطیلات رسمی";
$dg->height = 500;
$dg->width = 750;
$dg->EnablePaging = false;
$dg->DefaultSortField = "TheDate";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "details";
$dg->EnableSearch = false;
$dg->EnablePaging = false;

$grid = $dg->makeGrid_returnObjects();

?>
<script>

Holiday.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

Holiday.DeleteRender = function(v,p,r)
{
	if(HolidayObject.RemoveAccess)	
		return "<div align='center' title='حذف' class='remove' "+
		"onclick='HolidayObject.DeleteHoliday();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

function Holiday(){
	
	this.grid = <?= $grid ?>;
	
	this.YearFieldSet = new Ext.form.FieldSet({
		title: "انتخاب سال",
		width: 400,
		renderTo : this.get("div_Years"),
		frame: true,
		items : [{
			xtype : "combo",
			store: YearStore,   
			labelWidth : 50,
			width : 220,
			fieldLabel : "سال",
			displayField: 'title',
			name : "year",
			valueField : "id",
			value : '<?= substr(DateModules::shNow(),0,4) ?>',
			listeners : {
				select : function(){
					me = HolidayObject;
					me.grid.getStore().proxy.extraParams = {
						Year : this.getValue()
					};
					me.grid.getStore().load();
				}
			}
		},{
			xtype : "form",
			title : "ورود اطلاعات از طریق فایل excel",
			itemId : "excelForm",
			collapsed : true,
			collapsible : true,
			frame : true,
			items : [{
				xtype : "container",
				html : "فایل اکسل باید شامل دو ستون باشد <br> ستون اول تاریخ شمسی( فرمت : 1394/02/08 ) و ستون دوم توضیحات"
					+ "<br>&nbsp;"
			},{
				xtype : "filefield",
				name : "attach",
				width : 300
			}],
			buttons :[{
				text : "انتقال از فایل excel",
				iconCls : "excel",
				handler : function(){
					
					mask = new Ext.LoadMask(HolidayObject.grid, {msg:'در حال انتقال ...'});
					mask.show();
					
					HolidayObject.YearFieldSet.down("[itemId=excelForm]").getForm().submit({
						url : HolidayObject.address_prefix + "shift.data.php?task=ImportHolidaysFromExcel",
						method : "post",
						isUpload : true,

						success : function(){
							mask.hide();
							HolidayObject.grid.getStore().load();							
						},
						
						failure: function(){
							mask.hide();
							HolidayObject.grid.getStore().load();							
						}
					});
				}	
			}]
		}]
	});
	
	this.grid.getStore().proxy.extraParams.Year = this.YearFieldSet.down("[name=year]").getValue();
	this.grid.render(this.get("grid_div"));	
}

var HolidayObject = new Holiday();	

Holiday.prototype.AddHoliday = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		HolidayID : null
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

Holiday.prototype.DeleteHoliday = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = HolidayObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'shift.data.php',
			params:{
				task: "DeleteHoliday",
				HolidayID : record.data.HolidayID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				HolidayObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

Holiday.prototype.SaveHoliday = function(){

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'shift.data.php',
		method: "POST",
		params: {
			task: "SaveHoliday",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				HolidayObject.grid.getStore().load();
			}
			else
			{
				if(st.data == "")
					Ext.MessageBox.alert("","خطا در اجرای عملیات");
				else
					Ext.MessageBox.alert("",st.data);
			}
		},
		failure: function(){}
	});
}

</script>
<center>
    <form id="mainForm">
        <br>
        <div id="div_Years"></div>
        <br>
        <div id="grid_div"></div>
    </form>
</center>
