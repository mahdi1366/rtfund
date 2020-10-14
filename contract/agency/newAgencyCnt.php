<?php
//-----------------------------
//	Programmer	: Mokhtari
//	Date		: 99.07
//-----------------------------

require_once '../header.inc.php';

if (!empty($_REQUEST['agencyCntID']))
	$agencyCntID = $_REQUEST['agencyCntID'];
else
	$agencyCntID = 0;

$readOnly = isset($_REQUEST["readOnly"]) ? true : false;
/*var_dump($_REQUEST);
var_dump($readOnly);*/
?>
<script type="text/javascript">

    NewAgencyCnt.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",

    agencyCntID : <?= $agencyCntID ?>,
	readOnly : <?= $readOnly ? "true" : "false" ?>,
	
	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
}
/*console.log(NewAgencyCntObj.readOnly);*/
function NewAgencyCnt() {
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
            itemId: "agencyCntID",
            name : "agencyCntID"
        },{
            xtype : "combo",
            readOnly : this.readOnly,
            name : "AgencyID",
            store: new Ext.data.Store({
                proxy:{
                    type: 'jsonp',
                    url: this.address_prefix + 'AgencyCnt.data.php?task=selectReceiptTypes',
                    reader: {root: 'rows',totalProperty: 'totalCount'}
                },
                fields :  ['InfoID','InfoDesc'],
                autoLoad : true
            }),
            fieldLabel : "نوع عامليت",
            queryMode : "local",
            displayField: 'InfoDesc',
            valueField : "InfoID",
            width: 350,
            /*listeners: {
                change : function() {
                    console.log(this.getValue());
                    if(this.getValue() == 128 || this.getValue() == 134)
                    {
                        console.log('Truinggggggggg');
                        NewOrgDocObj.MainForm.down("[name=endDate]").disable();
                        NewOrgDocObj.MainForm.down("[name=PersonID2]").disable();
                        NewOrgDocObj.MainForm.down("[name=endDate]").setValue("");
                        NewOrgDocObj.MainForm.down("[name=PersonID2]").setValue("");
                    }
                    else
                    {
                        console.log('Falsingggggggg');
                        NewOrgDocObj.MainForm.down("[name=endDate]").enable();
                        NewOrgDocObj.MainForm.down("[name=PersonID2]").enable();
                    }
                }
            },*/
            allowBlank : false
        },{
            xtype : "textfield",
            readOnly : this.readOnly,
            fieldLabel: 'عنوان قرارداد',
            name : "title",
            itemId: 'title',
            width: 350,
            allowBlank : false
        },{
            xtype : "shdatefield",
            readOnly : this.readOnly,
            fieldLabel: 'تاریخ شروع',
            name : "startDate",
            itemId: 'startDate',
            width: 350,
            allowBlank : false
        },{
            xtype : "shdatefield",
            readOnly : this.readOnly,
            fieldLabel: 'تاریخ پایان',
            name : "endDate",
            itemId: 'endDate',
            width: 350,
            allowBlank : false
        },{
            xtype : "textfield",
            readOnly : this.readOnly,
            fieldLabel: 'كارمزد عامليت',
            name : "agencyWage",
            itemId: 'agencyWage',
            width: 350,
            allowBlank : false
        },{
            xtype : "textfield",
            readOnly : this.readOnly,
            fieldLabel: 'كارمزد صندوق',
            name : "selfWage",
            itemId: 'selfWage',
            width: 350,
            allowBlank : false
        },{
            xtype : "textfield",
            readOnly : this.readOnly,
            fieldLabel: 'وجه التزام تاخير دين',
            name : "commitAmount",
            itemId: 'commitAmount',
            width: 350,
            allowBlank : false
        },{
            xtype : "combo",
            readOnly : this.readOnly,
            store: new Ext.data.SimpleStore({
                fields : ['id','title'],
                data : [
                    ["1" , "صندوق"],
                    ["2" , "کارگزار"]
                ]
            }),
            displayField : "title",
            readOnly : this.readOnly,
            valueField : "id",
            fieldLabel: 'اختیار وصول مطالبات',
            allowBlank : false,
            width: 350,
            /*beforeLabelTextTpl: required,*/
            name: 'receiptOption'
        },{
            xtype : "textfield",
            readOnly : this.readOnly,
            fieldLabel: 'زمان تسويه حساب',
            name : "defrayTime",
            itemId: 'defrayTime',
            width: 350,
            allowBlank : false
        }],
		buttons: [{
			text: "  ذخیره",
			handler: function () {
                NewAgencyCntObj.SaveAgencyCnt(false);
			},
			iconCls: "save"
		}]
	});

	
	this.AgencyCntStore = new Ext.data.Store({
		fields: ['agencyCntID', "AgencyID", 'title', 'startDate', "endDate", "agencyWage", "selfWage", "commitAmount", "receiptOption", "defrayTime"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + "AgencyCnt.data.php?task=SelectAgencyCnt&agencyCntID="+this.agencyCntID,
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		}
	});
	
	if(this.agencyCntID > 0)
		this.LoadOrgDoc();
}


    NewAgencyCnt.prototype.LoadOrgDoc = function(){

	/*mask1 = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask1.show();*/
	
	this.AgencyCntStore.load({
		params : {
            agencyCntID : this.agencyCntID,
            AgencyID : this.AgencyID
		},
		callback : function(){
			
			me = NewAgencyCntObj;
			record = this.getAt(0);
			/*if (record.data.TemplateID == 23 || record.data.TemplateID == 24 || record.data.TemplateID == 26 )
			me.MainForm.down("[itemId=EndDate]").disable();*/
            /*me.MainForm.down("[name=OrgDocType]").disable();*/
            console.log(this);
			record.data.startDate = MiladiToShamsi(record.data.startDate);
			record.data.endDate = MiladiToShamsi(record.data.endDate);

			me.MainForm.loadRecord(record);
			

		}
	});
}

    NewAgencyCntObj = new NewAgencyCnt();

    NewAgencyCnt.prototype.SaveAgencyCnt = function (print) {

	if(!this.MainForm.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	this.MainForm.getForm().submit({
		
		url: this.address_prefix + 'AgencyCnt.data.php?task=SaveAgencyCnt',
		method: 'POST',
		params : {
			/*content : CKEDITOR.instances.OrgDocEditor.getData()*/
		},
		
		success: function (form,action) {
			mask.hide();

            NewAgencyCntObj.MainForm.getComponent('agencyCntID').setValue(action.result.data);
			if (print) 
			{
				var ContractID = NewAgencyCntObj.MainForm.getComponent('agencyCntID').getValue();
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


    NewAgencyCnt.prototype.getShdatefield = function (fieldname, ren) {
	return new Ext.form.SHDateField(
			{
				name: fieldname,
				width: 150,
				format: 'Y/m/d',
				renderTo: NewAgencyCntObj.get(ren)
			}
	);
};

    NewAgencyCnt.prototype.ContractDocuments = function(ObjectType){

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