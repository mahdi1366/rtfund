<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.07
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;

//-------------------------------------
require_once 'letter/letter.class.php';
$dt = OFC_LetterNotes::GetRemindNotes();
if($dt->rowCount() == 0)
	die();
//-------------------------------------

$dg = new sadaf_datagrid("dg", $js_prefix_address . "letter/letter.data.php?task=GetRemindNotes", "grid_div");

$dg->addColumn("", "NoteID", "", true);

$col = $dg->addColumn("شماره نامه", "LetterID", "");
$col->width = 70;

$col = $dg->addColumn("عنوان یادداشت", "NoteTitle");
$col->width = 200;

$col = $dg->addColumn("شرح", "NoteDesc");

$col = $dg->addColumn("یادآوری", "ReminderDate", GridColumn::ColumnType_date);

$col = $dg->addColumn('', '', 'string');
$col->renderer = "NotesStartPage.SeeRender";
$col->width = 40;
$col->align = "center";

$dg->emptyTextOfHiddenColumns = true;
$dg->EnablePaging = false;
$dg->height = 150;
$dg->width = 790;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->autoExpandColumn = "NoteDesc";
$grid = $dg->makeGrid_returnObjects();

?>
<script>

NotesStartPage.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function NotesStartPage(){
	
	this.grid = <?= $grid ?>;
	this.grid.on("itemdblclick", function(view, record){
		framework.OpenPage("/office/letter/LetterInfo.php", "مشخصات نامه", 
		{
			LetterID : record.data.LetterID
		});
	});
	
	this.grid.render(this.get("div_gridnotes"));
}

NotesStartPage.SeeRender = function(value, p, record){
	
	if(record.data.StatusDesc == "RAW")
		return;
	return "<div  title='' class='tick' onclick='NotesStartPageObject.SeeExpert();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

NotesStartPageObject = new NotesStartPage();

NotesStartPage.prototype.SeeExpert = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(this.grid, {msg:'در حال تایید...'});
	mask.show();

	Ext.Ajax.request({
		url : this.address_prefix + "letter/letter.data.php?task=SeeNote",
		method : "POST",
		params : {
			NoteID : record.data.NoteID
		},

		success : function(response){
			var result = Ext.decode(response.responseText);
			mask.hide();
			NotesStartPageObject.grid.getStore().load();
		}
	});
}
</script>
<center>
	<div id="div_gridnotes"></div>
</center>