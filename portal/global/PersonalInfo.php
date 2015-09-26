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
			name: 'fullname'
		},{
			xtype : "numberfield",
			fieldLabel: 'کد ملی',
			name: 'NationalID',
			maxLength : 10,
			hideTrigger : true
		},{
			xtype : "numberfield",
			fieldLabel: 'کد اقتصادی',
			name: 'EconomicID',
			maxLength : 10,
			hideTrigger : true
		},{
			xtype : "numberfield",
			fieldLabel: 'شماره تلفن',
			name: 'PhoneNo',
			maxLength : 11,
			emptyText : "فرمت صحیح : 05138820405",
			hideTrigger : true
		},{
			xtype : "numberfield",
			fieldLabel: 'تلفن همراه',
			maxLength : 11,
			name: 'mobile',
			emptyText : "فرمت صحیح : 09155001020",
			hideTrigger : true
		},{
			xtype : "textfield",
			vtype : "email",
			emptyText : "فرمت صحیح : user@yahoo.com",
			fieldLabel: 'پست الکترونیک',
			name: 'email'
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
				this.up('form').getForm().submit({
					
				});
				
				if (me.formPanel.form.isValid()) {
					var mask = new Ext.LoadMask(Ext.getCmp(me.TabID),{msg: 'تغییر رمز عبور ...'});
					mask.show();

					Ext.Ajax.request({
						url: me.address_prefix + 'global.data.php' , 
						params: {
							task: "SavePersonalInfo"
						},
						method: "POST",

						success : function(response,options)
						{
							mask.hide();
							if(response.responseText == "CurPassError")
							{
								alert("رمز عبور فعلی اشتباه می باشد");
								return;
							}
							if(response.responseText == "true")
							{
								alert("رمز شما با موفقیت تغییر یافت");
								Ext.getCmp("cur_pass").setValue();
								Ext.getCmp("new_pass").setValue();
								Ext.getCmp("new_pass2").setValue();
							}
							else
								alert("عملیات مورد نظر با شکست مواجه شد");
						},
						failure : function(response)
						{
							alert("عملیات مورد نظر با شکست مواجه شد");
							mask.hide();
						}
					});
				}
			}

		}]
	});

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
    mask.show();    
	
	this.store = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + "global.data.php?task=SelectPeopleInfo",
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["fullname","UserName","NationalID","EconomicID","PhoneNo","mobile","address","email"],
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