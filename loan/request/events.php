<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.07
//-------------------------
include('../header.inc.php');
include_once inc_dataGrid;

$RequestID = $_REQUEST["RequestID"];

$dg = new sadaf_datagrid("dg",$js_prefix_address . "request.data.php?task=GetEvents&RequestID=" .$RequestID,"grid_div");

$dg->addColumn("", "EventID","", true);
$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "EventTypeDesc","", true);

$col = $dg->addColumn("شرح رویداد", "EventTitle");
$col->editor = ColumnEditor::TextField();
	
$col = $dg->addColumn("تاریخ رویداد", "EventDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 100;

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store,record){return LoanEventObject.SaveEvent(record);}";

$dg->addButton("AddBtn", "ایجاد رویداد", "add", "function(){LoanEventObject.AddEvent();}");

$col = $dg->addColumn("حذف", "");
$col->sortable = false;
$col->renderer = "function(v,p,r){return LoanEvent.DeleteRender(v,p,r);}";
$col->width = 35;

$dg->height = 336;
$dg->width = 585;
$dg->emptyTextOfHiddenColumns = true;
$dg->EnableSearch = false;
$dg->HeaderMenu = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "EventDate";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "EventTitle";

$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">

LoanEvent.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	RequestID : <?= $RequestID ?>,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function LoanEvent()
{
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("div_grid"));	
}

LoanEvent.DeleteRender = function(v,p,r){
	
	if(r.data.EventRefNo != null &&  r.data.EventRefNo != "")
		return "";
	
	if(r.data.EventType == "9" && r.data.ChequeStatus != "1")
		return "";
	
	return "<div align='center' title='حذف' class='remove' "+
		"onclick='LoanEventObject.DeleteEvent();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

var LoanEventObject = new LoanEvent();
	
LoanEvent.prototype.SaveEvent = function(record){

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: "SaveEvents",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				LoanEventObject.grid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
			}
		},
		failure: function(){}
	});
}

LoanEvent.prototype.AddEvent = function(){


	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		EventID: null,
		RequestID : this.RequestID
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

LoanEvent.prototype.DeleteEvent = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = LoanEventObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'request.data.php',
			params:{
				task: "DeleteEvents",
				EventID : record.data.EventID
			},
			method: 'POST',

			success: function(response,option){
				result = Ext.decode(response.responseText);
				if(result.success)
					LoanEventObject.grid.getStore().load();
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