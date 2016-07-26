<?php
//-----------------------------
//	Programmer	: b.Mahdipour
//	Date		: 91.06
//-----------------------------

require_once '../../../header.inc.php';

?>
<script>
UploadFiles.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function UploadFiles()
{
        var types = Ext.create('Ext.data.ArrayStore', {
			fields: ['val', 'title'],
			data : [ ['1','اضافه کار'],
                                 ['9','ماموریت'],
								 ['11','وام/کسور']
							
                            
                        ]
                             });
    
    
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "درج اطلاعات فایل اکسل",
		defaults : {
			labelWidth :150
		},
		width : 500,
		items :[{
			xtype : "combo",
			fieldLabel : "فایل مربوط به ",
			store: types ,
			displayField : "title",
			name : "FileType",
			valueField : "val"                        
                        
		},{
			xtype : "filefield",
			name : "attach",
			fieldLabel : "فایل Excel ",
			anchor : "100%"
		}],
		buttons : [{
			text : "بارگزاری فایل اکسل",
			iconCls : "excel",
			handler : function()
			{	mask = new Ext.LoadMask(Ext.getCmp(UploadFilesObj.TabID), {msg:'در حال انجام عملیات...'});
				mask.show();
				this.up('form').getForm().submit({				     
					clientValidation: true,
					url: UploadFilesObj.address_prefix + '../data/upload_excel.data.php?task=InsertData',
					method : "POST",                                                                                
					success : function(form,action){
						mask.hide();
						UploadFilesObj.get("result").innerHTML = action.result.data;
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

UploadFilesObj = new UploadFiles();

UploadFiles.prototype.expand = function()
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