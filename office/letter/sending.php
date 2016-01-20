<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------
require_once '../header.inc.php';

if(empty($_POST["LetterID"]))
	die();

$LetterID = $_POST["LetterID"];

$SendID = !empty($_POST["SendID"]) ? $_POST["SendID"] : "0";
?>
<script>

SendLetter.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	parent : <?= $_REQUEST["parent"] ?>,
		
	AfterSendHandler : <?= $_REQUEST["AfterSendHandler"] ?>,
	
	LetterID : <?= $LetterID?>,
	SendID : '<?= $SendID ?>',
	index : 1,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function SendLetter(){
	
	this.mainPanel = new Ext.form.FormPanel({
		frame : true,
		height : 375,
		autoScroll : true,
		defaults : {
			anchor : "100%",
			style : "margin:3px"			
		},
		buttons : [{
			text : "ارجاع جدید",
			iconCls : "add",
			handler : function(){
				SendLetterObject.AddSendingFieldSet();
			}
		},'->',{
			text : "ارسال نامه",
			iconCls : "sendLetter",
			handler : function(){
				SendLetterObject.SendingLetter();
			}
		},{
			text : "بازگشت",
			iconCls : "undo",
			handler : function(){
				SendLetterObject.parent.hide();
			}
		}]
	});
	
	this.tabPanel = new Ext.tab.Panel({
		renderTo : this.get("div_panel"),
		plain: true,
		height : 400,
		autoScroll : true,
		width : 610,
		
		items : [{
			title : "ارجاع نامه",
			items : this.mainPanel
		}, {
			title : "درج پیوست",
			hidden : this.SendID == 0 ? true : false,
			loader : {
				url : this.address_prefix + "attach.php",
				method: "POST",
				text: "در حال بار گذاری...",
				scripts : true
			},
			listeners : {
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						params : {
							LetterID : SendLetterObject.LetterID,
							SendID : SendLetterObject.SendID,
							ExtTabID : this.getEl().id
						}
					});
				}
			}
		}]
	});
	
	this.AddSendingFieldSet();
}

SendLetter.prototype.AddSendingFieldSet = function(){
	
	this.mainPanel.add({
		xtype : "fieldset",
		layout : {
			type : "table",
			columns : 2
		},
		defaults : {
			width : 200
		},
		items : [{
			xtype : "combo",
			name : this.index + "_ToPersonID",
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: '/framework/person/persons.data.php?task=selectPersons&UserType=IsStaff',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['PersonID','fullname']
			}),
			emptyText : "ارجاع به",
			displayField: 'fullname',
			valueField : "PersonID"				
		},{
			xtype : "textarea",
			emptyText : "شرح ارجاع",
			name : this.index + "_SendComment",
			value : this.index > 1 ? this.mainPanel.down("[name=1_SendComment]").getValue() : "",
			rowspan : 3,
			width : 340
		},{
			xtype : "combo",
			name : this.index + "_SendType",
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'letter.data.php?task=selectSendTypes',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['InfoID','InfoDesc'],
				autoLoad : true
			}),
			queryMode : "local",
			emptyText : "نوع ارجاع",
			displayField: 'InfoDesc',
			valueField : "InfoID",
			value : this.index > 1 ? this.mainPanel.down("[name=1_SendType]").getValue() : "1"
		},{
			xtype : "combo",
			name : this.index + "_IsUrgent",
			store: new Ext.data.SimpleStore({
				fields : ['id','title'],
				data : [ 
					['NO', "عادی"],
					["YES", "فوری"] 
				]
			}),  
			value : this.index > 1 ? this.mainPanel.down("[name=1_IsUrgent]").getValue() : "NO",
			emptyText : "فوریت",
			displayField: 'title',
			valueField : "id"	
		}]
	});
	this.index++;
}

SendLetter.prototype.SendingLetter = function(){

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	this.mainPanel.getForm().submit({
		url: this.address_prefix + 'letter.data.php',
		method: "POST",
		params: {
			task: "SendLetter",
			LetterID : this.LetterID,
			SendID : this.SendID
		},
		success: function(form,action){
			mask.hide();
			SendLetterObject.parent.hide();
			eval("SendLetterObject.AfterSendHandler();");
		}
	});
}

SendLetterObject = new SendLetter();

</script>
<center>
	<div id="div_panel"></div>
</center>