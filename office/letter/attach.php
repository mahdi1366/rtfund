<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

if(empty($_POST["LetterID"]))
	die();

$LetterID = $_POST["LetterID"];

$access = true;

//------------------------------------------------------
$dg = new sadaf_datagrid("dg", "../dms/dms.data.php?" .
		"task=SelectAll&ObjectType=letter&ObjectID=" . $LetterID, "grid_div");

$dg->addColumn("", "DocumentID", "", true);
$dg->addColumn("", "ObjectType", "", true);
$dg->addColumn("", "ObjectID", "", true);
$dg->addColumn("", "IsConfirm", "", true);
$dg->addColumn("", "RegPersonID", "", true);
$dg->addColumn("", "param1Title", "", true);
$dg->addColumn("", "DocTypeDesc", "", true);
$dg->addColumn("", "param1", "", true);
$dg->addColumn("", "DocType", "", true);

$col = $dg->addColumn("عنوان پیوست", "DocDesc", "");

$col = $dg->addColumn("فایل", "FileType", "");
$col->renderer = "function(v,p,r){return ManageDocument.FileRender(v,p,r)}";
$col->align = "center";
$col->width = 30;

if($access)
{
	$col = $dg->addColumn("عملیات", "", "");
	$col->renderer = "function(v,p,r){return ManageDocument.OperationRender(v,p,r)}";
	$col->width = 60;
}

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 290;
$dg->width = 690;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "DocTypeDesc";
$dg->autoExpandColumn = "DocDesc";
$grid = $dg->makeGrid_returnObjects();

?>
<script>
	
ManageDocument.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	LetterID : <?= $LetterID ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManageDocument(){
	
	this.formPanel = new Ext.form.Panel({
		renderTo: this.get("MainForm"),      
		width : 690,
		style : "margin-top:10px",
		bodyPadding: '8 10 8 10',
		defaults :{
			labelWidth : 70
		},
		frame: true,
		layout : "hbox",
		items : [{
			xtype : "textfield",
			allowBlank : false,
			width : 300,
			fieldLabel : "شرح پیوست",
			name : "DocDesc"
		},{
			xtype : "filefield",
			width : 230,
			fieldLabel : "فایل مدرک",
			name : "FileType",
			style : "margin-right:20px"
		},{
			xtype : "button",
			text : "ذخیره",
			style : "border-width:1px;",
			iconCls : "save",
			handler : function(){ ManageDocumentObject.SaveDocument(); }
		},{
			xtype : "button",
			text : "پاکن",
			style : "border-width:1px;",
			iconCls : "clear",
			handler : function(){ ManageDocumentObject.formPanel.getForm().reset(); }
			
		},{
			xtype : "hidden",
			name : "DocumentID"
		}]
	});

	this.grid = <?= $grid ?>;
	this.grid.render(this.get("div_grid"));
}

ManageDocumentObject = new ManageDocument();

ManageDocument.FileRender = function(v,p,r){
	
	if(v == "" || v == null)
		return "";
	
	return "<div align='center' title='مشاهده فایل' class='attach' "+
		"onclick='ManageDocument.ShowFile(" + r.data.DocumentID + "," + r.data.ObjectID + ");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16;float:right'></div>";
}

ManageDocument.ShowFile = function(DocumentID, ObjectID){
	
	window.open("../../dms/ShowFile.php?DocumentID=" + DocumentID + "&ObjectID=" + ObjectID);
}

ManageDocument.OperationRender = function(v,p,r){
	
	if(r.data.IsConfirm == "YES" || r.data.RegPersonID != "<?= $_SESSION["USER"]["PersonID"] ?>")
		return "";
	
	return "<div align='center' title='ویرایش' class='edit' "+
		"onclick='ManageDocumentObject.EditDocument();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:20px;height:16;float:right'></div>" +
	
	"<div align='center' title='حذف' class='remove' "+
		"onclick='ManageDocumentObject.DeleteDocument();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:20px;height:16;float:right'></div>" ;		
}

ManageDocument.prototype.EditDocument = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	this.formPanel.getForm().reset();
	this.formPanel.down("[name=DocumentID]").setValue(record.data.DocumentID);
	this.formPanel.loadRecord(record);	
}

ManageDocument.prototype.SaveDocument = function(){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	this.formPanel.getForm().submit({
		clientValidation: true,
		url: '../dms/dms.data.php',
		method: "POST",
		isUpload : true,
		params: {
			task: "SaveDocument",
			param1 : 0,
			DocType : 0,
			ObjectID : this.LetterID,
			ObjectType : 'letter'
		},
		success: function(form,action){
			mask.hide();

			if(action.result.success)
			{   
				ManageDocumentObject.grid.getStore().load();
				ManageDocumentObject.formPanel.getForm().reset();
			}
			else
			{
				if(action.result.data == "")
					alert("خطا در اجرای عملیات");
				else
					alert(action.result.data);
			}
		},
		failure: function(form,action){
			Ext.MessageBox.alert("Error",action.result.data);
			mask.hide();
		}
	});
}

ManageDocument.prototype.DeleteDocument = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = ManageDocumentObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: '../dms/dms.data.php',
			params:{
				task: "DeleteDocument",
				DocumentID : record.data.DocumentID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				ManageDocumentObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

</script>
<center>
	<div id="MainForm"></div>
	<div id="div_grid"><div>
</center>