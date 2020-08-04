<?php
//-----------------------------
//	Date		: 1395.06
//-----------------------------
 
require_once '../header.inc.php';
require_once inc_dataGrid;

$LetterID = $_REQUEST["LetterID"];
if(empty($LetterID))
	die();

$editable = true;
$ReadOnly = isset($_REQUEST["ReadOnly"]) && $_REQUEST["ReadOnly"] == "true" ? true : false;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "letter.data.php?task=GetLetterNotes&LetterID=" . $LetterID, "grid_div");

$dg->addColumn("", "NoteID","", true);
$dg->addColumn("", "LetterID","", true);

// new added
$col = $dg->addColumn("ثبت کننده", "PersonID");
$col->width = 100;

$col = $dg->addColumn("تاریخ ثبت", "createDate", GridColumn::ColumnType_date);
$col->width = 100;
// end new added

$col = $dg->addColumn("عنوان یادداشت", "NoteTitle");
$col->editor = ColumnEditor::TextField();
$col->width = 200;

$col = $dg->addColumn("شرح", "NoteDesc");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("یادآوری", "ReminderDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField(true);
$col->width = 110;

if($editable && !$ReadOnly)
{
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(store,record){return LetterNoteObject.SaveLetterNote(record);}";

	$dg->addButton("AddBtn", "ایجاد یادداشت جدید", "add", "function(){LetterNoteObject.Add();}");

	$col = $dg->addColumn("حذف", "");
	$col->sortable = false;
	$col->renderer = "function(v,p,r){return LetterNote.DeleteRender(v,p,r);}";
	$col->width = 35;
}
$dg->autoExpandColumn = "NoteDesc";
$dg->emptyTextOfHiddenColumns = true;
$dg->height = 465;
$dg->width = 600;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "NoteDesc";
$dg->DefaultSortDir = "ASC";

$grid = $dg->makeGrid_returnObjects();

?>
<script>
LetterNote.prototype = {
	TabID : "<?= $_REQUEST["ExtTabID"] ?>",
	address_prefix : "<?= $js_prefix_address?>",

	LetterID : "<?= $LetterID ?>",
	editable : <?= $editable ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LetterNote()
{
	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record)
	{
		if(record.data.IsSeen == "NO")
			return "yellowRow";
		return "";
	}
	this.grid.render(this.get("div_grid"));
	
}

LetterNote.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف' class='remove' "+
		"onclick='LetterNoteObject.Delete();' " +
		"style='float:right;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16'></div>";
}

LetterNoteObject = new LetterNote();
	
LetterNote.prototype.Add = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		NoteID : null,
		LetterID : this.LetterID
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}
	
LetterNote.prototype.SaveLetterNote = function(record){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'letter.data.php',
		method: "POST",
		params: {
			task: "SaveLetterNote",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(!st.success)
			{
				if(st.data == "")
					Ext.MessageBox.alert("Error","عملیات مورد نظر با شکست مواجه شد");
				else
					Ext.MessageBox.alert("Error",st.data);
			}
			else
				LetterNoteObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

LetterNote.prototype.Delete = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = LetterNoteObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'letter.data.php',
			params:{
				task: "DeleteLetterNote",
				NoteID : record.data.NoteID
			},
			method: 'POST',

			success: function(response,option){
				result = Ext.decode(response.responseText);
				if(result.success)
					LetterNoteObject.grid.getStore().load();
				else if(result.data == "")
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
				else
					Ext.MessageBox.alert("",result.data);
				mask.hide();
				
			},
			failure: function(){}
		});
	});
}

</script>
<center>
	<div id="div_grid"></div>
</center>