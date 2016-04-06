<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.12
//-----------------------------
include_once("../header.inc.php");
require_once inc_dataGrid;

$PersonID = $_REQUEST["PersonID"];

$dg = new sadaf_datagrid("dg",$js_prefix_address . 
		"persons.data.php?task=SelectSigners&PersonID=" . $PersonID,"div_grid_user");

$dg->addColumn("","RowID","string", true);
$dg->addColumn("","PersonID","string", true);

$col = $dg->addColumn("سمت","PostDesc","string");
$col->editor = ColumnEditor::TextField();
$col->width = 100;

$col = $dg->addColumn("نام و نام خانوادگی ","fullname","string");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn("جنسیت", "sex", "string");
$col->editor = ColumnEditor::ComboBox(array(array("id"=>"MALE", "title"=>'مرد'),
		array("id"=>'FEMALE', "title"=>'زن')), "id", "title");
$col->width = 50;
$col->align = "center";

$col = $dg->addColumn("کد ملی","NationalID","string");
$col->editor = ColumnEditor::NumberField();
$col->width = 100;

$col = $dg->addColumn("تلفن","telephone","string");
$col->editor = ColumnEditor::NumberField();
$col->width = 100;

$col = $dg->addColumn("موبایل","mobile","string");
$col->editor = ColumnEditor::NumberField();
$col->width = 100;

$col = $dg->addColumn("ایمیل","email","string");
$col->editor = ColumnEditor::TextField(true);
$col->width = 100;

$col = $dg->addColumn("حذف","","");
$col->renderer = "OrgSigner.deleteRender";
$col->sortable = false;
$col->width = 40;

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){return OrgSignerObject.saveData(v,p,r);}";

$dg->addButton = true;
$dg->addHandler = "function(){OrgSignerObject.Adding();}";

$dg->height = 350;
$dg->width = 730;
$dg->DefaultSortField = "RowID";
$dg->autoExpandColumn = "fullname";
$dg->editorGrid = true;
$dg->title = "صاحبان امضاء";
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$grid = $dg->makeGrid_returnObjects();
?>
<style type="text/css">
.pinkRow, .pinkRow td,.pinkRow div{ background-color:#FFB8C9 !important;}
</style>
<script type="text/javascript">

OrgSigner.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	PersonID : <?= $PersonID ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

OrgSigner.deleteRender = function(v,p,r)
{
	return "<div align='center' title='حذف ' class='remove' onclick='OrgSignerObject.Deleting();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

function OrgSigner()
{
	this.grid = <?= $grid?>;
	this.grid.render(this.get("div_grid"));
}

var OrgSignerObject = new OrgSigner();

OrgSigner.prototype.Adding = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		RowID : "",
		PersonID : this.PersonID
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

OrgSigner.prototype.saveData = function(store,record)
{
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveSigner',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'persons.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				OrgSignerObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

OrgSigner.prototype.Deleting = function()
{
	Ext.MessageBox.confirm("","آيا مايل به حذف مي باشيد؟", function(btn){
		
		if(btn == "no")
			return;
		
		me = OrgSignerObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		Ext.Ajax.request({
		  	url : me.address_prefix + "persons.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteSigner",
		  		RowID : record.data.RowID
		  	},
		  	success : function(response,o)
		  	{
		  		OrgSignerObject.grid.getStore().load();
		  	}
		});
		
	});

}

</script>
<center>
	<div id="div_grid"></div>
</center>