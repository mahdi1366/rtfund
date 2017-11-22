<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.07
//-------------------------
require_once('../header.inc.php');
require_once inc_dataGrid;

$ContractID = $_REQUEST["ContractID"];

$dg = new sadaf_datagrid("dg",$js_prefix_address . "contract.data.php?task=GetSigns&ContractID=" . 
		$ContractID,"grid_div");
 
$dg->addColumn("", "SignID","", true);
$dg->addColumn("", "ContractID","", true);
$dg->addColumn("", "fullname","", true);

$col = $dg->addColumn("گروه فرد", "description");
$col->editor = ColumnEditor::TextField(true);
$col->width = 100;

$col = $dg->addColumn("امضاء کننده داخلی", "PersonID");
$col->renderer = "function(v,p,r){return r.data.fullname;}";
$col->editor = "this.SignerCombo";

$col = $dg->addColumn("امضاء کننده خارجی", "SignerName");
$col->editor = ColumnEditor::TextField(true);
$col->width = 150;

$col = $dg->addColumn("پست", "SignerPost");
$col->editor = ColumnEditor::TextField(true);
$col->width = 150;

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store,record){return ContractSignObject.SaveSign(record);}";

$dg->addButton("AddBtn", "ایجاد ردیف", "add", "function(){ContractSignObject.AddSign();}");

$col = $dg->addColumn("حذف", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return ContractSign.DeleteRender(v,p,r);}";
$col->width = 35;

$dg->height = 336;
$dg->width = 685;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->HeaderMenu = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "SignID";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "PersonID";

$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">

ContractSign.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	ContractID : <?= $ContractID ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ContractSign()
{
	this.SignerCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields :  ['PersonID','fullname']
		}),
		allowBlank : true,
		displayField: 'fullname',
		valueField : "PersonID"			
	});
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("div_grid"));	
}

ContractSign.DeleteRender = function(v,p,r){
	
	if(r.data.DocID != null &&  r.data.DocID != "")
		return "";

	return "<div align='center' title='حذف' class='remove' "+
		"onclick='ContractSignObject.DeleteSign();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}
	
ContractSign.prototype.SaveSign = function(record){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'contract.data.php',
		method: "POST",
		params: {
			task: "SaveSign",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				ContractSignObject.grid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
			}
		},
		failure: function(){}
	});
}

ContractSign.prototype.AddSign = function(){


	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		SignID: null,
		ContractID : this.ContractID
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

ContractSign.prototype.DeleteSign = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = ContractSignObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'contract.data.php',
			params:{
				task: "DeleteSign",
				SignID : record.data.SignID
			},
			method: 'POST',

			success: function(response,option){
				result = Ext.decode(response.responseText);
				if(result.success)
					ContractSignObject.grid.getStore().load();
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

var ContractSignObject = new ContractSign();

</script>
<center>
	<div id="div_grid"></div>
</center>