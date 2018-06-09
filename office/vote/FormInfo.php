<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1397.02
//-----------------------------

require_once '../header.inc.php';

$FormID = (int)$_REQUEST["FormID"];
$parentObj = !empty($_REQUEST["parentObj"]) ? $_REQUEST["parentObj"] : "x";
$PersonID = !empty($_REQUEST["PersonID"]) ? (int)$_REQUEST["PersonID"] : $_SESSION["USER"]["PersonID"];

$dt = PdoDataAccess::runquery("select * from VOT_FilledForms where FormID=? AND PersonID=?", array(
	$FormID, $PersonID));
$readOnly = count($dt) > 0 ? true : false;
	
?>
<script>
var x;	
VoteFormInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	parentObj : <?= $parentObj ?>,
	address_prefix : "<?= $js_prefix_address?>",

	FormID : "<?= $FormID ?>",
	readOnly : <?= $readOnly ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function VoteFormInfo()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("formDIV"),
		itemId : "form",
		border : false,
		buttons :[{
			text : "تایید نهایی نظر سنجی",
			itemId : "saveBtn",
			hidden : this.readOnly,
			iconCls : "tick",
			handler : function(){
				VoteFormInfoObject.SaveFilledForm();
			}
		}]
	});
	if(this.readOnly)
	{
		this.ItemsStore = new Ext.data.Store({
			fields: ['ItemID','ItemValue','ItemType',"ItemTitle", 'ItemValues', 'GroupID', 'GroupDesc'],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + "../../office/vote/vote.data.php?task=FilledItemsValues&PersonID=" + 
						<?= $PersonID ?>,
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			}
		});
	}
	else
	{
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
	this.FormWinMask = new Ext.LoadMask(this.formPanel, {msg:'در حال بارگذاری ...'});
	this.FormWinMask.show();
	
	this.ItemsStore.load({
		params : {
			FormID : this.FormID
		},
		callback : function(){
			VoteFormInfoObject.MakeForm(this);
			VoteFormInfoObject.FormWinMask.hide();
		}
	});	
}

VoteFormInfoObject = new VoteFormInfo();

VoteFormInfo.prototype.MakeForm = function(store){
	
	parent = this.formPanel;

	var CurGroupID = 0;
	var Index = 1;
	for(i=0; i<store.getCount(); i++)
	{
		record = store.getAt(i);
		if(CurGroupID !== record.data.GroupID)
		{
			parent.add({
				xtype : "fieldset",
				title : record.data.GroupDesc,
				itemId : "Group_" + record.data.GroupID,
				layout : "vbox",
				style : "margin:2px"
			});
			fsparent = parent.down("[itemId=Group_" + record.data.GroupID + "]");
			CurGroupID = record.data.GroupID;
			Index = 1;
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
				readOnly : this.readOnly, 
				name : "elem_" + record.data.ItemID,
				fieldLabel : Index + ") " + record.data.ItemTitle
			});
		}
		else if(record.data.ItemType === "radio")
		{
			fsparent.add({
				xtype : "displayfield",
				anchor : "100%",
				value : Index + ") " + record.data.ItemTitle
			});
			var items = new Array();
			arr = record.data.ItemValues.split("#");
			for(j=0; j<arr.length; j++)
				items.push({
					boxLabel : arr[j],
					inputValue : arr[j],
					readOnly : this.readOnly,
					width : Math.round(750/arr.length),
					name : "elem_" + record.data.ItemID,
					checked : arr[j] == record.data.ItemValue ? true : false
				}); 
			fsparent.add({
				xtype : "radiogroup",
				items : items			
			});
		}
		else
		{
			titleInLine = false;
			if(record.data.ItemType === "textarea" || record.data.ItemTitle.length > 30)
			{
				fsparent.add({
					xtype : "displayfield",
					readOnly : this.readOnly,
					value : Index + ") " + record.data.ItemTitle,
					anchor : "100%"
				});
				titleInLine = true;
			}
			fsparent.add({
				xtype: record.data.ItemType,
				fieldLabel : titleInLine ? "" : Index + ") " + record.data.ItemTitle,
				style : record.data.ItemType == 'displayfield' ? "line-height: 30px" : "",
				name : "elem_" + record.data.ItemID,
				hideTrigger : record.data.ItemType == 'numberfield' || record.data.ItemType == 'currencyfield' ? true : false,
				value : record.data.ItemValue,
				width : 700
			});
		}
		Index++;
	}
}

VoteFormInfo.prototype.SaveFilledForm = function(){

	mask = new Ext.LoadMask(this.formPanel, {msg:'در حال ذخیره سازی ...'});
	mask.show();
	
	this.formPanel.getForm().submit({
		url : this.address_prefix + "../../office/vote/vote.data.php",
		method : "post",
		params : {
			task : "SaveFilledForm",
			FormID : this.FormID
		},
		
		success : function(){
			mask.hide();
			VoteFormInfoObject.parentObj.FormWin.hide();
			VoteFormInfoObject.parentObj.NewVoteFormsStore.load();
			VoteFormInfoObject.parentObj.grid.getStore().load();
		},
		
		failure : function(){
			mask.hide();
		}
	});

}

VoteFormInfo.prototype.PreviewForm = function(){
	
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
			
			VoteFormInfo.MakeForm(this,true);
		}
	});
}

</script>
<center>
<div id="formDIV"></div>
</center>