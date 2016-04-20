<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../header.inc.php';
require_once '../global/CNTconfig.class.php';

if (!empty($_REQUEST['ContractID'])) 
	$ContractID = $_REQUEST['ContractID'];
else
	$ContractID = 0;
?>
<script type="text/javascript">

NewContract.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",
	TplItemSeperator: "<?= CNTconfig::TplItemSeperator ?>",
	
	ContractID : <?= $ContractID ?>,
	
	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
}

function NewContract() {
	
	this.MainForm = new Ext.form.Panel({
		plain: true,            
		frame: true,
		bodyPadding: 5,
		width: 800,
		title : "اطلاعات کلی قرارداد",
		fieldDefaults: {
			labelWidth: 100
		},
		renderTo: this.get("SelectTplComboDIV"),
		layout: {
			type: 'table',                
			columns : 2
		},
		items: [{
			xtype: 'combo',
			fieldLabel: 'انتخاب الگو',
			itemId: 'TemplateID',
			store: new Ext.data.Store({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../templates/templates.data.php?task=SelectTemplates',
					reader: {root: 'rows', totalProperty: 'totalCount'}
				},
				fields: ['TemplateID', 'TemplateTitle', 'TplContent'],
				autoLoad : true
			}),
			displayField: 'TemplateTitle',
			valueField: "TemplateID",
			name : "TemplateID",
			
			queryMode : "local",
			allowBlank : false,
			listConfig: {
				loadingText: 'در حال جستجو...',
				emptyText: 'فاقد اطلاعات',
				itemCls: "search-item"
			},
			width: 350,
			listeners: {
				select: function (combo, records) {
					this.collapse();
					NewContractObj.ShowTplItemsForm(records[0].data.TemplateID);
				}
			}
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/person/persons.data.php?task=selectPersons',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['PersonID','fullname']
			}),
			fieldLabel : "مشتری",
			displayField : "fullname",
			pageSize : 20,
			width: 350,
			allowBlank : false,
			valueField : "PersonID",
			name : "PersonID",
			itemId : "PersonID"
		},{
			xtype : "shdatefield",
			fieldLabel: 'تاریخ شروع',
			name : "StartDate",
			itemId: 'StartDate',
			allowBlank : false
		},{
			xtype : "shdatefield",
			fieldLabel: 'تاریخ پایان',
			name : "EndDate",
			itemId: 'EndDate',
			allowBlank : false
		},{
			xtype: 'textarea',
			fieldLabel: 'توضیحات',
			itemId: 'description',
			name : "description",
			width: 740,
			rows : 2,
			colspan : 2
		},{
			xtype: "fieldset",
			title : "جزئیات قرارداد",
			itemId: "templateItems",
			width : 780,
			colspan : 2,
			layout: {
				type: 'table',                
				columns : 2
			},
			defaults: {
				labelWidth: 160,
				width : 370
			}
		},{
			colspan : 2,
			xtype: "hidden",
			itemId: "ContractID",
			name : "ContractID"
		}],
		buttons: [{
			text: "  ذخیره",
			handler: function () {
				NewContractObj.SaveContract(false);
			},
			iconCls: "save"
		}, {
			text: "  مشاهده",
			handler: function () {
				NewContractObj.SaveContract(true);
			},
			iconCls: "print"
		}]
	});
	
	this.TplItemsStore = new Ext.data.Store({
		fields: ['TemplateItemID', 'ItemName', 'ItemType'],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "../templates/templates.data.php?task=selectTemplateItems&All=true",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		},
		autoLoad : true
	});
	
	this.ContractStore = new Ext.data.Store({
		fields: ['ContractID', "TemplateID", 'description', 'StartDate', "EndDate", "PersonID"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "contract.data.php?task=SelectContracts",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		}
	});
	
	if(this.ContractID > 0)
		this.LoadContract();
}

NewContract.prototype.LoadContract = function(){

	mask1 = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask1.show();
	
	this.ContractStore.load({
		params : {
			ContractID : this.ContractID
		},
		callback : function(){
			
			me = NewContractObj;
			record = this.getAt(0);
			
			record.data.StartDate = MiladiToShamsi(record.data.StartDate);
			record.data.EndDate = MiladiToShamsi(record.data.EndDate);
			
			me.MainForm.loadRecord(record);
			
			if(record.data.PersonID != null)
				me.MainForm.getComponent("PersonID").getStore().load({
					params :{PersonID : record.data.PersonID}
				});
			
			me.ShowTplItemsForm(record.data.TemplateID);			
			mask1.hide();
		}
	});
}

NewContractObj = new NewContract();

NewContract.prototype.SaveContract = function (print) {

	if(!this.MainForm.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	this.MainForm.getForm().submit({
		
		url: this.address_prefix + 'contract.data.php?task=SaveContract',
		method: 'POST',
		
		success: function (form,action) {
			mask.hide();
			
			NewContractObj.MainForm.getComponent('ContractID').setValue(action.result.data);
			if (print) 
			{
				var ContractID = NewContractObj.MainForm.getComponent('ContractID').getValue();
				window.open(NewContractObj.address_prefix + 'PrintContract.php?ContractID=' + ContractID);
			}
			else
				Ext.MessageBox.alert('', 'با موفقیت ذخیره شد');
		},
		failure : function(form,action){
			mask.hide();
			Ext.MessageBox.alert('', 'خطا در اجرای عملیات');
		}
	});
}

NewContract.prototype.ShowTplItemsForm = function (TemplateID) {

	if (arguments.length > 1)
		NewContractObj.LoadValues = 1;
	else
		NewContractObj.LoadValues = 0;
	
	this.MainForm.getComponent("templateItems").removeAll();

	mask = new Ext.LoadMask(this.MainForm.getComponent("templateItems"), {msg:'در حال ذخيره سازي...'});
	mask.show();
	          
	Ext.Ajax.request({
		url: NewContractObj.address_prefix + '../templates/templates.data.php?task=GetTemplateContent',
		params: {
			TemplateID: TemplateID
		},
		method: 'POST',
		success: function (response) {
			me = NewContractObj;
			var TplContent = response.responseText;
			var regex = new RegExp(me.TplItemSeperator);
			var res = TplContent.split(regex);

			if (TplContent.substring(0, 3) !== me.TplItemSeperator) {
				var temp = [];
				res = temp.concat(res);
			}

			for (var i = 0; i < res.length; i++)
			{
				if (i % 2 != 0) {
					var num = me.TplItemsStore.find('TemplateItemID', res[i]);
					var fieldname = me.TplItemsStore.getAt(num).data.ItemName;

					var TheTplItemType = me.TplItemsStore.getAt(num).data.ItemType;
					me.MainForm.getComponent("templateItems").add({
						xtype: TheTplItemType,
						itemId: 'TplItem_' + res[i],
						name: 'TplItem_' + res[i],
						fieldLabel : fieldname,
						hideTrigger : TheTplItemType == 'numberfield' || TheTplItemType == 'currencyfield' ? true : false
					});
					if (me.LoadValues > 0) {
						var num = ValuesStore.find('TemplateItemID', res[i]);                                    
						if (ValuesStore.getAt(num)){
							switch(TheTplItemType){
								case "shdatefield" :
									me.ResultPanel.getComponent("templateItems").getComponent('TplItem_' + res[i]).setValue(
										MiladiToShamsi(ValuesStore.getAt(num).data.ItemValue));
									break;
								default : 
									me.ResultPanel.getComponent("templateItems").getComponent('TplItem_' + res[i]).setValue(ValuesStore.getAt(num).data.ItemValue);                                    
							}
						}
					}                                
				} 
			}    
			mask.hide();                    
		},
		failure: function () {
		}
	});
}

NewContract.prototype.getShdatefield = function (fieldname, ren) {
	return new Ext.form.SHDateField(
			{
				name: fieldname,
				width: 150,
				format: 'Y/m/d',
				renderTo: NewContractObj.get(ren)
			}
	);
};

NewContract.prototype.getShdatefieldBtn = function (fieldname, ren) {
	var b = Ext.create('Ext.Button', {
		text: 'Click me',
		iconCls: 'add',
		renderTo: NewContractObj.get(ren),
		handler: function () {
			alert('You clicked the button!');
		}
	});
	returnb;
};

NewContract.prototype.LoadContractItems = function () {
	
        if (!this.ContractItemsStore) {
            this.ContractItemsStore = new Ext.data.Store({
                proxy: {
                    type: 'jsonp',
                    url: this.address_prefix + 'contract.data.php?task=GetContractItems',
                    reader: {root: 'rows', totalProperty: 'totalCount'}
                },
                fields: ['ContractItemID', 'ContractID', 'TemplateItemID', 'ItemValue']
            })
        }
        this.MainForm.getComponent('TemplateID').getStore().load({
            callback: function (records) {
                NewContractObj.MainForm.getComponent('TemplateID').setValue(
					NewContractObj.MainForm.getComponent('TemplateID').getStore().getAt(0).data.TemplateID);
            }
        });

        this.ContractItemsStore.load({
            params: {
                ContractID: NewContractObj.ResultPanel.getComponent('ContractID').getValue()
            },
            callback: function () {
            }
        });
    }
</script>
<br>
<center>
    <div id="SelectTplComboDIV"></div>
    <form id="TplContentForm">
        <div id="TplContentDIV"></div>
    </form>
</center>