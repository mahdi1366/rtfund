<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.07
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;



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

$dg->title = "یادآوری های یادداشت های نامه";
$dg->emptyTextOfHiddenColumns = true;
$dg->EnablePaging = false;
$dg->disableFooter = true;
$dg->height = 150;
$dg->width = 790;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->autoExpandColumn = "NoteDesc";
$grid = $dg->makeGrid_returnObjects();

//----------------------------------------------------------------

$dg = new sadaf_datagrid("dg", $js_prefix_address . "letter/letter.data.php?task=SelectTodayResponseLetters", "");

$col = $dg->addColumn("شماره نامه", "LetterID", "");
$col->width = 100;

$col = $dg->addColumn("عنوان نامه", "LetterTitle");

$col = $dg->addColumn("فرستنده نامه", "FromPersonName");
$col->width = 200;

$dg->title = "نامه هایی که مهلت پاسخ به آنها تا امروز می باشد";
$dg->emptyTextOfHiddenColumns = true;
$dg->EnablePaging = false;
$dg->height = 150;
$dg->width = 790;
$dg->EnablePaging = false;
$dg->disableFooter = true;
$dg->EnableSearch = false;
$dg->autoExpandColumn = "LetterTitle";
$grid2 = $dg->makeGrid_returnObjects();
//----------------------------------------------------------------

$dg = new sadaf_datagrid("dg", $js_prefix_address . "letter/letter.data.php?task=SelectTodayFollowUpLetters", "");

$col = $dg->addColumn("شماره نامه", "LetterID", "");
$col->width = 100;

$col = $dg->addColumn("عنوان نامه", "LetterTitle");

$col = $dg->addColumn("گیرنده نامه", "ToPersonName");
$col->width = 200;

$dg->title = "نامه هایی که امروز باید پیگیری کنید";
$dg->emptyTextOfHiddenColumns = true;
$dg->EnablePaging = false;
$dg->height = 150;
$dg->width = 790;
$dg->EnablePaging = false;
$dg->disableFooter = true;
$dg->EnableSearch = false;
$dg->autoExpandColumn = "LetterTitle";
$grid3 = $dg->makeGrid_returnObjects();
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
	this.grid.getStore().on("load", function(){
		if(this.totalCount != 0 && !NotesStartPageObject.grid.rendered)
			NotesStartPageObject.grid.render(NotesStartPageObject.get("div_gridnotes"));
	})
	this.grid.getStore().load();
	//-----------------------------------------------------------
	this.responseGrid = <?= $grid2 ?>;
	this.responseGrid.on("itemdblclick", function(view, record){
		framework.OpenPage("/office/letter/LetterInfo.php", "مشخصات نامه", 
		{
			LetterID : record.data.LetterID
		});
	});
	this.responseGrid.getStore().on("load", function(){
		if(this.totalCount != 0 && !NotesStartPageObject.responseGrid.rendered)
			NotesStartPageObject.responseGrid.render(NotesStartPageObject.get("div_responseGrid"));
	})
	this.responseGrid.getStore().load();
	//-----------------------------------------------------------
	this.followupGrid = <?= $grid3 ?>;
	this.followupGrid.on("itemdblclick", function(view, record){
		framework.OpenPage("/office/letter/LetterInfo.php", "مشخصات نامه", 
		{
			LetterID : record.data.LetterID
		});
	});
	this.followupGrid.getStore().on("load", function(){
		if(this.totalCount != 0 && !NotesStartPageObject.followupGrid.rendered)
			NotesStartPageObject.followupGrid.render(NotesStartPageObject.get("div_followupGrid"));
	})
	this.followupGrid.getStore().load();
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
	<div id="div_responseGrid"></div>
	<div id="div_followupGrid"></div>
	
</center>