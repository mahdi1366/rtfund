<?php
//-----------------------------
//	Programmer	: Mokhtari
//	Date		: 99.07
//-----------------------------

require_once '../header.inc.php';

if (!empty($_REQUEST['orgDocID']))
	$orgDocID = $_REQUEST['orgDocID'];
else
	$orgDocID = 0;

$readOnly = isset($_REQUEST["readOnly"]) ? true : false;
var_dump($_REQUEST);
?>
<script type="text/javascript">

NewOrgDoc.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",

    orgDocID : <?= $orgDocID ?>,
	readOnly : <?= $readOnly ? "true" : "false" ?>,
	
	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
}

function NewOrgDoc() {
    readOnly = false;
	this.MainForm = new Ext.form.Panel({
		plain: true,            
		frame: true,
		bodyPadding: 5,
		width: 800,
		autoHeight : true,
		fieldDefaults: {
			labelWidth: 100
		},
		renderTo: this.get("SelectTplComboDIV"),
		layout: {
			type: 'table',                
			columns : 2
		},
		items: [{
            colspan : 2,
            xtype: "hidden",
            itemId: "orgDocID",
            name : "orgDocID"
        },{
            xtype : "combo",
            readOnly : readOnly,
            name : "orgDocType",
            store: new Ext.data.Store({
                proxy:{
                    type: 'jsonp',
                    url: this.address_prefix + 'organDoc.data.php?task=selectOrgDocTypes',
                    reader: {root: 'rows',totalProperty: 'totalCount'}
                },
                fields :  ['InfoID','InfoDesc'],
                autoLoad : true
            }),
            fieldLabel : "نوع سند سازمانی",
            queryMode : "local",
            displayField: 'InfoDesc',
            valueField : "InfoID",
            listeners: {
                change : function() {
                    if(this.getValue() == 128 || this.getValue() == 134)
                    {
                        NewOrgDocObj.MainForm.down("[name=endDate]").disable();
                        NewOrgDocObj.MainForm.down("[name=PersonID2]").disable();
                        NewOrgDocObj.MainForm.down("[name=endDate]").setValue("");
                        NewOrgDocObj.MainForm.down("[name=PersonID2]").setValue("");
                    }
                    else
                    {
                        NewOrgDocObj.MainForm.down("[name=endDate]").enable();
                        NewOrgDocObj.MainForm.down("[name=PersonID2]").enable();
                    }
                }
            },
            allowBlank : false
        },/*{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + 'organDoc.data.php?task=selectOrgDocTypes',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true
			}),
			fieldLabel : "نوع سند سازمانی",
			displayField : "InfoDesc",
			width: 350,

			allowBlank : false,
			valueField : "InfoID",
			name : "orgDocType",
			itemId : "orgDocType"
		},*/{
            xtype : "textfield",
            fieldLabel: 'عنوان سند',
            name : "title",
            itemId: 'title',
            allowBlank : false
        },{
            xtype : "shdatefield",
            fieldLabel: 'تاریخ',
            name : "date",
            itemId: 'date',
            allowBlank : false
        },{
            xtype : "shdatefield",
            fieldLabel: 'تاریخ پایان',
            name : "endDate",
            itemId: 'endDate'
        },{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: '/framework/person/persons.data.php?task=selectPersons',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['PersonID','fullname']
			}),
			fieldLabel : "طرف قرارداد دوم",
			displayField : "fullname",
			pageSize : 20,
			width: 350,
			valueField : "PersonID",
			name : "PersonID2",
			itemId : "PersonID2"
		}],
		buttons: [{
			text: "  ذخیره",
			handler: function () {
				NewOrgDocObj.SaveOrgDoc(false);
			},
			iconCls: "save"
		}]
	});

	
	this.OrgDocStore = new Ext.data.Store({
		fields: ['orgDocID', "orgDocType", 'title', 'date', "endDate","PersonID2"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "organDoc.data.php?task=SelectOrgDocs&orgDocID="+this.orgDocID,
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		}
	});
	
	if(this.orgDocID > 0)
		this.LoadOrgDoc();
}


NewOrgDoc.prototype.LoadOrgDoc = function(){

	/*mask1 = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask1.show();*/
	
	this.OrgDocStore.load({
		params : {
            orgDocID : this.orgDocID,
            orgDocType : this.orgDocType
		},
		callback : function(){
			
			me = NewOrgDocObj;
			record = this.getAt(0);
			/*if (record.data.TemplateID == 23 || record.data.TemplateID == 24 || record.data.TemplateID == 26 )
			me.MainForm.down("[itemId=EndDate]").disable();*/
            /*me.MainForm.down("[name=OrgDocType]").disable();*/

			record.data.date = MiladiToShamsi(record.data.date);
			record.data.endDate = MiladiToShamsi(record.data.endDate);
			/*console.log(record.data.orgDocType);*/
			me.MainForm.loadRecord(record);
			

		}
	});
}

NewOrgDocObj = new NewOrgDoc();

NewOrgDoc.prototype.SaveOrgDoc = function (print) {

	if(!this.MainForm.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	this.MainForm.getForm().submit({
		
		url: this.address_prefix + 'organDoc.data.php?task=SaveOrgDoc',
		method: 'POST',
		params : {
			/*content : CKEDITOR.instances.OrgDocEditor.getData()*/
		},
		
		success: function (form,action) {
			mask.hide();

            /*NewOrgDocObj.grid.getStore().load();
            NewOrgDocObj.MainForm.hide();*/

			NewOrgDocObj.MainForm.getComponent('orgDocID').setValue(action.result.data);
			if (print) 
			{
				var ContractID = NewOrgDocObj.MainForm.getComponent('orgDocID').getValue();
				/*window.open(NewOrgDocObj.address_prefix + 'PrintContract.php?ContractID=' + ContractID);*/
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


NewOrgDoc.prototype.getShdatefield = function (fieldname, ren) {
	return new Ext.form.SHDateField(
			{
				name: fieldname,
				width: 150,
				format: 'Y/m/d',
				renderTo: NewOrgDocObj.get(ren)
			}
	);
};

NewOrgDoc.prototype.ContractDocuments = function(ObjectType){

	if(!this.documentWin)
	{
		this.documentWin = new Ext.window.Window({
			width : 720,
			height : 440,
			modal : true,
			bodyStyle : "background-color:white;padding: 0 10px 0 10px",
			closeAction : "hide",
			loader : {
				url : "/office/dms/documents.php",
				scripts : true
			},
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.documentWin);
	}

	this.documentWin.show();
	this.documentWin.center();

	this.documentWin.loader.load({
		scripts : true,
		params : {
			ExtTabID : this.documentWin.getEl().id,
			ObjectType : ObjectType,
			ObjectID : this.ContractID
		}
	});
}
</script>
<center>
    <div id="SelectTplComboDIV"></div>
    <div id="ContractEditor" style="display:block;"></div>
</center>