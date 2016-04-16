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
$dg = new sadaf_datagrid("dg", $js_prefix_address . "Shift.data.php?task=GetAllShifts", "grid_div");

$dg->addColumn("", "ShiftID", "", true);
$dg->addColumn("", "GroupID", "", true);
$dg->addColumn("", "IsActive", "", true);

$col = $dg->addColumn("عنوان شیفت", "title", "");
$col->editor = ColumnEditor::TextField();


$col = $dg->addColumn("از ساعت", "FromTime");
$col->editor = ColumnEditor::TimeField();
$col->width = 80;
$col->align = "center";

$col = $dg->addColumn("تا ساعت", "ToTime");
$col->editor = ColumnEditor::TimeField();
$col->width = 80;
$col->align = "center";

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){ShiftObject.ShiftInfo('new');}";
	
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(){return ShiftObject.SaveShift();}";

}
$dg->title = "لیست شیفت ها";
$dg->height = 500;
$dg->width = 750;
$dg->EnablePaging = false;
$dg->DefaultSortField = "ShiftDesc";

if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return Shift.OperationRender(v,p,r);}";
	$col->width = 40;
}
$grid = $dg->makeGrid_returnObjects();

?>
<script>

Shift.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

Shift.OperationRender = function(v,p,r)
{
	if(ShiftObject.RemoveAccess)	
		return "<div align='center' title='حذف وام' class='remove' "+
		"onclick='ShiftObject.DeleteShift();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

function Shift(){
	
	this.grid = <?= $grid ?>;
	
	this.groupPnl = new Ext.form.Panel({
		renderTo: this.get("div_selectGroup"),
		title: "انتخاب گروه",
		width: 400,
		collapsible : true,
		collapsed : false,
		frame: true,
		bodyCfg: {style: "background-color:white"},
		items : [{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					proxy: {type: 'jsonp',
						url: this.address_prefix + 'Shift.data.php?task=SelectShiftGroups',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					autoLoad : true,
					fields : ['InfoID','InfoDesc']
				}),
				valueField : "InfoID",
				queryMode : "local",
				name : "GroupID",
				displayField : "InfoDesc",
				fieldLabel : "انتخاب گروه"
			},{
				xtype : "fieldset",
				collapsible: true,
				collapsed : true,
				title : "ایجاد گروه جدید",
				width : 350,
				style : "background-color: #F2FCFF",
				items : [{
						xtype : "textfield",
						name : "GroupDesc",
						fieldLabel : "عنوان گروه"
					},{
						xtype : "button",
						text: "ایجاد گروه",
						handler: function(){

							var mask = new Ext.LoadMask(this.up('form'),{msg: 'تغییر اطلاعات ...'});
							mask.show();

							Ext.Ajax.request({
								method : "POST",
								url: ShiftObject.address_prefix + "Shift.data.php",
								params: {
									task: "AddGroup",
									GroupDesc: this.up('form').down("[name=GroupDesc]").getValue()
								},
								success: function(response){
									mask.hide();
									ShiftObject.groupPnl.down("[name=GroupID]").getStore().load({
										callback : function(){
											ShiftObject.groupPnl.down("[name=GroupID]").setValue(
												this.getAt(this.getCount()-1));
											ShiftObject.LoadShifts();
										}});
									ShiftObject.groupPnl.down('fieldset').collapse();
								}
							});
						}
					}]
			}],
		buttons:[{
				text : "حذف گروه",
				iconCls : "remove",
				handler : function(){
					ShiftObject.DeleteGroup(this.up('form').down('[name=GroupID]').getValue());
				}
			},{
				text: "لیست وام ها",
				iconCls: "refresh",
				handler: function(){ ShiftObject.LoadShifts(); }
			}]
	});	
}

var ShiftObject = new Shift();	

Shift.prototype.LoadShifts = function(){

	ShiftObject.GroupID = this.groupPnl.down('[name=GroupID]').getValue();

	ShiftObject.grid.getStore().proxy.extraParams.GroupID = ShiftObject.GroupID;

	if(ShiftObject.grid.rendered)
		ShiftObject.grid.getStore().load();
	else
		ShiftObject.grid.render(ShiftObject.get("grid_div"));
	
	ShiftObject.grid.show();
	ShiftObject.groupPnl.collapse();
}

Shift.prototype.DeleteGroup = function(GroupID)
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = ShiftObject;
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'Shift.data.php',
			params:{
				task: "DeleteGroup",
				GroupID : GroupID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				sd = Ext.decode(response.responseText);

				if(sd.success)
				{
					ShiftObject.groupPnl.down('[name=GroupID]').setValue();
					ShiftObject.groupPnl.down('[name=GroupID]').getStore().load();
					ShiftObject.grid.hide();
				}	
				else
				{
					Ext.MessageBox.alert("Error","در این گروه وام تعریف شده و قادر به حذف آن نمی باشید");
				}
			},
			failure: function(){}
		});
	});
}

Shift.prototype.DeleteShift = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = ShiftObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'shift.data.php',
			params:{
				task: "DeleteShift",
				ShiftID : record.data.ShiftID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				ShiftObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

Shift.prototype.SaveShift = function(){

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'shift.data.php',
		method: "POST",
		params: {
			task: "SaveShift",
			record: Ext.encode(record.data),
			GroupID : ShiftObject.groupPnl.down("[name=GroupID]").getValue()
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				ShiftObject.grid.getStore().load();
			}
			else
			{
				if(st.data == "")
					alert("خطا در اجرای عملیات");
				else
					alert(st.data);
			}
		},
		failure: function(){}
	});
}

</script>
<center>
    <form id="mainForm">
        <br>
        <div id="div_selectGroup"></div>
        <br>
		<div id="newDiv"></div>
        <div id="grid_div"></div>
    </form>
</center>
