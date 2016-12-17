<?php
//---------------------------
// programmer:	Sh.Jafarkhani
// create Date:	95.09
//---------------------------
include('header.inc.php');

require_once inc_dataGrid;
require_once inc_dataReader;
require_once inc_response;

//------------------------- DATA BLOCK ----------------------------
$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {

	case "SelectAll":
		SelectAll();
		
	case "Save":
		Save();
		
	case "remove":
		remove();
}

function SelectAll(){
	
	$where = " AND PersonID=?";
	$param = array($_SESSION["USER"]["PersonID"]);
	
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) {
		$fld = $_REQUEST['fields'];
		$where .= " AND " . $fld . ' like ?';
		$param[] = '%' . $_REQUEST['query'] . '%';
	}
	
	$res = FRW_phonebook::Get($where . dataReader::makeOrder(), $param);
	$cnt = $res->rowCount();
	$res = PdoDataAccess::fetchAll($res, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($res, $cnt, $_GET["callback"]);
	die();
}

function Save(){
	
	$obj = new FRW_phonebook();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->RowID != "")
		$result = $obj->Edit();
	else
	{
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
		$result = $obj->Add();
	}
	
	Response::createObjectiveResponse($result, "");
	die();
}

function remove(){
	
	$obj = new FRW_phonebook($_POST["RowID"]);
	$result = $obj->Remove();
	Response::createObjectiveResponse($result, "");
	die();
}
//-----------------------------------------------------------------

$dgh = new sadaf_datagrid("dg",$js_prefix_address . "phonebook.php?task=SelectAll","div_dg");

$dgh->addColumn("", "RowID","",true);

$col = $dgh->addColumn("نام", "fullname");
$col->editor = ColumnEditor::TextField();
$col->width = 200;

$col = $dgh->addColumn("تلفن", "phone");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 110;

$col = $dgh->addColumn("موبایل", "mobile");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 110;

$col = $dgh->addColumn("آدرس", "address");
$col->editor = ColumnEditor::TextField(true);

$col = $dgh->addColumn("توضیحات", "details");
$col->editor = ColumnEditor::TextField(true);
$col->width = 200;

$col = $dgh->addColumn("حذف", "");
$col->renderer = "function(v,p,r){ return phonebookObj.DeleteRender(v,p,r);}";
$col->width = 40;

$dgh->addButton("", "ایجاد آیتم جدید", "add", "function(){phonebookObj.Add();}");

$dgh->enableRowEdit = true;
$dgh->rowEditOkHandler = "function(store,record){return phonebookObj.save(store,record);}";

$dgh->title = "دفترچه تلفن";
$dgh->width = 850;
$dgh->DefaultSortField = "fullname";
$dgh->DefaultSortDir = "ASC";
$dgh->autoExpandColumn = "address";
$dgh->height = 450;
$dgh->pageSize = 15;
$grid = $dgh->makeGrid_returnObjects();

?>
<script>
phonebook.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function phonebook()
{
	this.grid = <?=$grid?>;
	this.grid.render(this.get("div_dg"));
}

phonebookObj = new phonebook();

phonebook.prototype.Add = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		RowID : ""
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

phonebook.prototype.save = function(store,record){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'Save',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'phonebook.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				phonebookObj.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

phonebook.prototype.Remove = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return; 
		
		me = phonebookObj;
		var record = me.grid.getSelectionModel().getLastSelected();
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'phonebook.php?task=remove',
			params:{
				RowID: record.data.RowID
			},
			method: 'POST',

			success: function(response){
				mask.hide();
				phonebookObj.grid.getStore().load();
			},
			failure: function(){}
		});
	});
	
}

phonebook.prototype.DeleteRender = function(v,p,r){

		return "<div style='background-repeat:no-repeat;background-position:center;cursor:pointer;"+
			"height:16;width:20px;float:left' "+
			" onclick=phonebookObj.Remove() class=remove></div>";
}

</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
		<br>
		<div id="div_dg"></div>
		<br>
		<div id="div_dg2"></div>
	</center>
</form>
