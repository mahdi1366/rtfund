<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';


?>
<script>
	
PersonalInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PersonalInfo()
{
	this.mainPanel = new Ext.form.FormPanel({
		renderTo : this.get("mainForm"),
		frame: true,
		hidden : true,
		title: 'اطلاعات شخصی',
		width: 400,
		defaults: {
			anchor : "98%"
		},
		items: [{
			xtype : "textfield",
			fieldLabel: 'نام',
			name: 'fname'
		},{
			xtype : "textfield",
			fieldLabel: 'نام خانوادگی',
			name: 'lname'
		},{
			xtype : "textfield",
			regex: /^\d{10}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'کد ملی',
			name: 'NationalID'
		},{
			xtype : "textfield",
			regex: /^\d{10}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'کد اقتصادی',
			name: 'EconomicID'
		},{
			xtype : "textfield",
			regex: /^\d{11}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'شماره تلفن',
			name: 'PhoneNo'
		},{
			xtype : "textfield",
			regex: /^\d{11}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'تلفن همراه',
			name: 'mobile'
		},{
			xtype : "textfield",
			vtype : "email",
			fieldLabel: 'پست الکترونیک',
			name: 'email',
			fieldStyle : "direction:ltr"
		},{
			xtype : "textarea",
			fieldLabel: 'آدرس',
			name: 'address'
		}],

		buttons : [{
			text : "ذخیره",
			iconCls: 'save',
			handler: function() {
				
				me = PersonalInfoObject;
				mask = new Ext.LoadMask(me.mainPanel, {msg:'در حال ذخيره سازي...'});
				mask.show();  
				me.mainPanel.getForm().submit({
					clientValidation: true,
					url: me.address_prefix + 'global.data.php?task=SavePersonalInfo' , 
					method: "POST",
					
					success : function(form,result){
						mask.hide();
						Ext.MessageBox.alert("","اطلاعات با موفقیت ذخیره شد");
					},
					failure : function(){
						mask.hide();
						Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
					}
				});
			}

		}]
	});

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
    mask.show();    
	
	this.store = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + "global.data.php?task=SelectPersonInfo",
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["fname","lname","UserName","NationalID","EconomicID","PhoneNo","mobile","address","email"],
		autoLoad : true,
		listeners :{
			load : function(){
				PersonalInfoObject.mainPanel.loadRecord(this.getAt(0));
				PersonalInfoObject.mainPanel.show();
				PersonalInfoObject.mainPanel.center();
				mask.hide();    
			}
		}
	});	
	
}

PersonalInfoObject = new PersonalInfo();

PersonalInfo.prototype.PersonalInfo = function()
{
	if(this.get("new_pass").value != this.get("new_pass2").value)
	{
		return;
	}
}

</script>

<div id="mainForm"></div>