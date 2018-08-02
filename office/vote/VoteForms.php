<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg", $js_prefix_address . "vote.data.php".
		"?task=SelectMyFilledForms" , "grid_div");

$dg->addColumn("", "FormID", "", true);
$dg->addColumn("", "PersonID", "", true);
$dg->addColumn("", "RegDate", "", true);

$col = $dg->addColumn("عنوان فرم", "FormTitle", "");

$col = $dg->addColumn("تاریخ ثبت", "RegDate", GridColumn::ColumnType_datetime);
$col->width = 130;

$col = $dg->addColumn("مشاهده فرم", "", "");
$col->renderer = "function(v,p,r){return VoteForms.viewRender(v,p,r)}";
$col->editor = "this.FileCmp";
$col->align = "center";
$col->width = 100;

$dg->height = 330;
$dg->width = 690;
$dg->title = "فرم های نظر سنجی تکمیل شده";
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "RegDate";
$dg->autoExpandColumn = "FormTitle";
$grid = $dg->makeGrid_returnObjects();

?>
<script>

VoteForms.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function VoteForms()
{
	this.mainPanel = new Ext.form.FormPanel({
		frame: true,
		bodyStyle : "padding:10px",
		renderTo : this.get("mainForm"),
		title: 'فرم های نظرسنجی',
		width: 500,
		layout : "vbox"
	});	
		
	this.NewVoteFormsStore = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + "../../office/vote/vote.data.php?task=SelectNewVoteForms",
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["FormID","FormTitle","EndDate"],
		autoLoad : true,
		listeners :{
			load : function(){
				
				VoteFormsObject.mainPanel.removeAll();
				for(i=0; i<this.totalCount; i++)
				{
					VoteFormsObject.mainPanel.add({
						xtype : "button",
						style : "margin-bottom:10px ",
						scale : "medium",
						itemId : this.getAt(i).data.FormID,
						border : true,
						iconCls : "list",
						text : this.getAt(i).data.FormTitle,
						handler : function(){ 
							VoteFormsObject.FillForm(this.itemId);
						}
					});
				}
			}
		}
	});	
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("div_grid"));
	
	this.FormWin = new Ext.window.Window({
		width : 800,
		title : "تکمیل فرم نظر سنجی",
		height : 500,
		loader : {
			url : this.address_prefix + "FormInfo.php",
			scripts : true
		},
		autoScroll : true,
		bodyStyle : "background-color:white",
		modal : true,
		closeAction : "hide",
		buttons :[{
			text : "بازگشت",
			iconCls : "undo",
			handler : function(){this.up('window').hide();}
		}]
	});
	Ext.getCmp(this.TabID).add(this.FormWin);

}

VoteForms.viewRender = function(v,p,r){
	
	return "<div align='center' title='نمایش' class='view' onclick='VoteFormsObject.PreviewForm();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VoteFormsObject = new VoteForms();

VoteForms.prototype.FillForm = function(FormID){
	
	this.FormWin.show();
	this.FormWin.loader.load({
		params : {
			ExtTabID : this.FormWin.getEl().id,
			parentObj : "VoteFormsObject",
			FormID : FormID
		}
	});
}

VoteForms.prototype.PreviewForm = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	this.FormWin.show();
	this.FormWin.loader.load({
		params : {
			ExtTabID : this.FormWin.getEl().id,
			parentObj : "VoteFormsObject",
			FormID : record.data.FormID
		}
	});
}

</script>
<center>
<div id="mainForm"></div>
<br>
<div id="div_grid"></div>
</center>