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
		width : 700,
		title : "تکمیل فرم نظر سنجی",
		height : 500,
		items : [ new Ext.form.Panel({
			itemId : "form",
			border : false
		}),{
			xtype : "hidden",
			name : "FormID"
		}],
		autoScroll : true,
		bodyStyle : "background-color:white",
		modal : true,
		closeAction : "hide",
		buttons :[{
			text : "تایید نهایی نظر سنجی",
			itemId : "saveBtn",
			iconCls : "tick",
			handler : function(){
				VoteFormsObject.SaveFilledForm();
			}
		},{
			text : "بازگشت",
			iconCls : "undo",
			handler : function(){this.up('window').hide();}
		}]
	});
	Ext.getCmp(this.TabID).add(this.FormWin);
	
	this.ItemsStore = new Ext.data.Store({
		fields: ['FormID','ItemID','ItemType',"ItemTitle", 'ItemValues', 'GroupID', 'GroupDesc'],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "../../office/vote/vote.data.php?task=SelectItems",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		}
	});
}

VoteForms.viewRender = function(v,p,r){
	
	return "<div align='center' title='نمایش' class='view' onclick='VoteFormsObject.PreviewForm();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VoteFormsObject = new VoteForms();

VoteForms.MakeForm = function(store, readOnly){
	
	VoteFormsObject.FormWin.down("[name=FormID]").setValue(store.getAt(0).data.FormID);
	parent = VoteFormsObject.FormWin.down('[itemId=form]');
	parent.removeAll();

	var CurGroupID = 0;
	for(i=0; i<store.getCount(); i++)
	{
		record = store.getAt(i);
		if(CurGroupID !== record.data.GroupID)
		{
			parent.add({
				xtype : "fieldset",
				title : record.data.GroupDesc,
				itemId : "Group_" + record.data.GroupID,
				layout : {
					type : "table",
					columns : 2
				}
			});
			fsparent = parent.down("[itemId=Group_" + record.data.GroupID + "]");
			CurGroupID = record.data.GroupID;
		}

		if(record.data.ItemType === "combo")
		{
			arr = record.data.ItemValues.split("#");
			data = [];
			for(j=0;j<arr.length;j++)
				data.push([ arr[j] ]);

			fsparent.add({
				store : new Ext.data.SimpleStore({
					fields : ['value'],
					data : data
				}),
				xtype: record.data.ItemType,
				value : record.data.ItemValue,
				valueField : "value",
				displayField : "value",
				readOnly : readOnly,
				name : "elem_" + record.data.ItemID,
				fieldLabel : record.data.ItemTitle,
				colspan : 2
			});
		}
		else if(record.data.ItemType === "radio")
		{
			fsparent.add({
				xtype : "displayfield",
				width : 400,
				value : record.data.ItemTitle
			});
			var items = new Array();
			arr = record.data.ItemValues.split("#");
			for(j=0; j<arr.length; j++)
				items.push({
					boxLabel : arr[j],
					inputValue : arr[j],
					readOnly : readOnly,
					name : "elem_" + record.data.ItemID,
					checked : arr[j] == record.data.ItemValue ? true : false,
					width : 100
				});
			fsparent.add({
				xtype : "radiogroup",
				items : items
			});
		}
		else
		{
			if(record.data.ItemType === "textarea")
			{
				fsparent.add({
					xtype : "displayfield",
					readOnly : readOnly,
					value : record.data.ItemTitle,
					colspan : 2,
					width : 650
				});
			}
			fsparent.add({
				xtype: record.data.ItemType,
				fieldLabel : record.data.ItemTitle,
				style : record.data.ItemType == 'displayfield' ? "line-height: 30px;" : "",
				name : "elem_" + record.data.ItemID,
				hideTrigger : record.data.ItemType == 'numberfield' || record.data.ItemType == 'currencyfield' ? true : false,
				value : record.data.ItemValue,
				colspan : 2,
				width : 650
			});
		}
	}
}

VoteForms.prototype.FillForm = function(FormID){
	
	this.FormWin.down("[itemId=saveBtn]").show();
	this.FormWin.show();
	this.ItemsStore.load({
		params : {
			FormID : FormID
		},
		callback : function(){
			VoteForms.MakeForm(this, false);
		}
	});	
}

VoteForms.prototype.SaveFilledForm = function(){

	mask = new Ext.LoadMask(this.FormWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();
	
	this.FormWin.down('[itemId=form]').getForm().submit({
		url : this.address_prefix + "../../office/vote/vote.data.php",
		method : "post",
		params : {
			task : "SaveFilledForm",
			FormID : this.FormWin.down('[name=FormID]').getValue()
		},
		
		success : function(){
			mask.hide();
			VoteFormsObject.FormWin.hide();
			VoteFormsObject.NewVoteFormsStore.load();
			VoteFormsObject.grid.getStore().load();
		},
		
		failure : function(){
			mask.hide();
		}
	});

}

VoteForms.prototype.PreviewForm = function(){
	
	if(!this.ValuesStore)
	{
		this.ValuesStore = new Ext.data.Store({
			fields: ['ItemID','ItemValue','ItemType',"ItemTitle", 'ItemValues', 'GroupID', 'GroupDesc'],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + "../../office/vote/vote.data.php?task=FilledItemsValues",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			}
		});
	}
	
	var record = this.grid.getSelectionModel().getLastSelected();
	this.FormWin.down("[itemId=saveBtn]").hide();
	this.FormWin.show();
	
	this.ValuesStore.load({
		params : {
			FormID : record.data.FormID
		},
		callback : function(){
			
			VoteForms.MakeForm(this,true);
		}
	});
}

</script>
<center>
<div id="mainForm"></div>
<br>
<div id="div_grid"></div>
</center>