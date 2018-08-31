<?php
//-----------------------------
//	Programmer	: b.Mahdipour
//	Date		: 92.09
//-----------------------------

require_once '../../../header.inc.php';

?>
<script>
UploadPayFiles.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function UploadPayFiles()
{            
    
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "پرداخت متفرقه",
		defaults : {
			labelWidth :150
		},
		width : 500,
		items :[{
					xtype:"numberfield" ,
					fieldLabel: 'سال',
					inputId:'pay_year',
					name: 'pay_year',
					width:250,
					hideTrigger:true
				},
				{
					xtype:"numberfield" ,
					fieldLabel: 'ماه',
					inputId:'pay_month',
					name: 'pay_month',
					width:250,
					hideTrigger:true
				},
				{
					xtype : "combo",
					store :  new Ext.data.Store({
					fields : ["InfoID","InfoDesc"], 
					proxy : {
								type: 'jsonp',
								url : this.address_prefix + "../../../global/domain.data.php?task=searchPayType",
								reader: {
									root: 'rows',
									totalProperty: 'totalCount'
								}
							}
											}),
					valueField : "InfoID",
					displayField : "InfoDesc",
					hiddenName : "PayType",
					inputId: "PayType" , 
					fieldLabel : "نوع پرداخت",
					listConfig: {
					loadingText: 'در حال جستجو...',
					emptyText: 'فاقد اطلاعات',
					itemCls : "search-item"
					},
					width:300
				},
				/*{
					xtype : "trigger",
					name : "sid",
					inputId:"sid",
					fieldLabel : "قلم حقوقی",
					onTriggerClick : function(){

						var retVal = showLOV("/HumanResources/global/LOV/SalaryItemLOV.php", 900, 550);
						if(retVal != '')
						{
							this.setValue(retVal);
						}
					} ,											
					width:230,
					triggerCls:'x-form-search-trigger'
				},*/
                                {
					xtype : "filefield",
					name : "attach",
					fieldLabel : "فایل Excel ",
					anchor : "100%"
				 }],
		buttons : [{
			text : "ثبت جهت پرداخت",
			iconCls : "excel",
			handler : function()
			{	mask = new Ext.LoadMask(Ext.getCmp(UploadPayFilesObj.TabID), {msg:'در حال انجام عملیات...'});
				mask.show();


                                /* Ext.Ajax.request({
					form:UploadPayFilesObj.get('mainForm'),
					url: UploadPayFilesObj.address_prefix + '../data/non_salary_import.data.php?task=InsertData',
					method : "POST",                                                                                
					success : function(response){
						alert(response.responseText);
						mask.hide();
						UploadPayFilesObj.get("result").innerHTML = action.result.data;
					},
					failure : function(form,action)
					{	mask.hide();
						alert("عملیات مورد نظر با شکست مواجه شد");
					}
					
				});
				
				return ; */


				this.up('form').getForm().submit({				     
					clientValidation: true,
					url: UploadPayFilesObj.address_prefix + '../data/non_salary_import.data.php?task=InsertData',
					method : "POST",                                                                                
					success : function(form,action){
						mask.hide();
						UploadPayFilesObj.get("result").innerHTML = action.result.data;
					},
					failure : function(form,action)
					{	mask.hide();
						alert("عملیات مورد نظر با شکست مواجه شد");
					}
				}); 
			}
		}]
	});
       
}

UploadPayFilesObj = new UploadPayFiles();

UploadPayFiles.prototype.expand = function()
{
    this.get("cancel_usuccess").style.display = "block";
}

</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div><br><br>
		<div id="result" style="width:800px"></div>
	</center>
</form>