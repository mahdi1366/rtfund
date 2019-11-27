<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.01
//-------------------------
require_once('../header.inc.php');
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

?>
<script>

ImportTraffic.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function ImportTraffic(){
	
	this.MainForm = new Ext.form.Panel({
		renderTo : this.get("div_form"),
		width : 500,
		title : "ورود اطلاعات از طریق فایل excel",
		frame : true,
		items : [{
			xtype : "filefield",
			name : "attach",
			width : 300
		},{
			xtype : "combo",
			name : "device",
			store : new Ext.data.SimpleStore({
				data : [
					['park' , "فایل دستگاه شعبه پارک علم و فناوری" ],
					["um" , "فایل دستگاه شعبه دانشگاه فردوسی" ]
				],
				fields : ['id','value']
			}),
			displayField : "value",
			valueField : "id",
			width : 300
		},{
			text : "container",
			html : "فایل باید حتما به فرمت xls باشد"			
		},{
			xtype : "container",
			itemId : "cmp_errors"
		}],
		buttons :[{
			text : "انتقال از فایل excel",
			iconCls : "excel",
			handler : function(){

				mask = new Ext.LoadMask(ImportTrafficObject.MainForm, {msg:'در حال انتقال ...'});
				mask.show();

				ImportTrafficObject.MainForm.getForm().submit({
					url : ImportTrafficObject.address_prefix + "traffic.data.php?task=ImportTrafficsFromExcel",
					method : "post",
					isUpload : true,

					success : function(){
						mask.hide();
						ImportTrafficObject.MainForm.down("[itemId]").update("<hr>" + "انتقال اطلاعات با موفقیت انجام شد");
					},

					failure: function(form,action){
						
						ImportTrafficObject.MainForm.down("[itemId]").update("<hr>" + action.result.data);
						mask.hide();
					}
				});
			}	
		}]
	});
	
}

var ImportTrafficObject = new ImportTraffic();	


</script>
<center>
    <form id="mainForm">
        <br>
        <div id="div_form"></div>
    </form>
</center>
