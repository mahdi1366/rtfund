<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.02
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................
$dg = new sadaf_datagrid("dg", $js_prefix_address . "shift.data.php?task=GetAllPersonShifts", "grid_div");

$dg->addColumn("", "RowID", "", true);
$dg->addColumn("", "fullname", "", true);
$dg->addColumn("", "ShiftTitle", "", true);

$col = $dg->addColumn("نام و نام خانوادگی", "PersonID", "");
$col->editor = "this.PersonCombo";
$col->renderer = "function(v,p,r){return r.data.fullname;}";

$col = $dg->addColumn("شیفت کاری", "ShiftID");
$col->renderer = "function(v,p,r){return r.data.ShiftTitle;}";
$col->editor = "this.ShiftCombo";
$col->width = 150;

$col = $dg->addColumn("از تاریخ", "FromDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 120;
$col->align = "center";

$col = $dg->addColumn("تا تاریخ", "ToDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField(true);
$col->width = 120;
$col->align = "center";

if($accessObj->AddFlag)
{
	$dg->addButton = true;
	$dg->addHandler = "function(){PersonShiftObject.AddPersonShift();}";
	
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(){return PersonShiftObject.SavePersonShift();}";
}
if($accessObj->RemoveFlag)
{
	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return PersonShift.OperationRender(v,p,r);}";
	$col->width = 40;
}
$dg->title = "شیفت کاری پرسنل";
$dg->height = 500;
$dg->width = 750;
$dg->DefaultSortField = "PersonID";
$dg->autoExpandColumn = "PersonID";
$dg->emptyTextOfHiddenColumns = true;
$grid = $dg->makeGrid_returnObjects();

?>
<script>

PersonShift.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

PersonShift.OperationRender = function(v,p,r)
{
	if(PersonShiftObject.RemoveAccess)	
		return "<div align='center' title='حذف ' class='remove' "+
		"onclick='PersonShiftObject.DeletePersonShift();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

function PersonShift(){
	
	this.PersonCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields :  ['PersonID','fullname']
		}),
		displayField: 'fullname',
		valueField : "PersonID",
		name : "PersonID"
	});
	
	this.ShiftCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: this.address_prefix + 'shift.data.php?task=GetAllShifts',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields :  ['ShiftID','ShiftTitle']
		}),
		displayField: 'ShiftTitle',
		valueField : "ShiftID",
		name : "ShiftID"
	});
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("grid_div"));
}

var PersonShiftObject = new PersonShift();	

PersonShift.prototype.AddPersonShift = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		PersonID : null,
		ShiftID : null,
		RowID : null
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

PersonShift.prototype.SavePersonShift = function(){

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'shift.data.php',
		method: "POST",
		params: {
			task: "SavePersonShift",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(!st.success)
			{
				if(st.data == "")
					Ext.MessageBox.alert("","خطا در اجرای عملیات");
				else
					Ext.MessageBox.alert("",st.data);
			}
			
			PersonShiftObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

PersonShift.prototype.DeletePersonShift = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = PersonShiftObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'shift.data.php',
			params:{
				task: "DeletePersonShift",
				RowID : record.data.RowID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				PersonShiftObject.grid.getStore().load();
			},
			failure: function(){}
		});
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
