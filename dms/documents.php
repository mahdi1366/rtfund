<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;

$ObjectType = $_POST["ObjectType"];
$ObjectID = isset($_POST["ObjectID"]) ? $_POST["ObjectID"] : "";

//---------------- RECOGNIZE ACCESS --------------------
$access = false;
switch($ObjectType)
{
	case "person" : 
		if($ObjectID == "")
			$ObjectID = $_SESSION["USER"]["PersonID"];
		if($_SESSION["USER"]["PersonID"] == $ObjectID)
			$access = true;
		break;
	case "loan":
		require_once '../loan/request/request.class.php';
		$obj = new LON_requests($ObjectID);
		if($_SESSION["USER"]["IsCustomer"] == "YES" && in_array($obj->StatusID, array("40","60")) )
			$access = true;
		if($_SESSION["USER"]["IsStaff"] == "YES" && $obj->StatusID == "50")
			$access = true;
		break;
}
//------------------------------------------------------
$dg = new sadaf_datagrid("dg", $js_prefix_address . "dms.data.php?" .
		"task=SelectAll&ObjectType=" . $ObjectType . "&ObjectID=" . $ObjectID, "grid_div");

$dg->addColumn("", "DocumentID", "", true);
$dg->addColumn("", "ObjectType", "", true);
$dg->addColumn("", "ObjectID", "", true);
$dg->addColumn("", "IsConfirm", "", true);
$dg->addColumn("", "RegPersonID", "", true);
$dg->addColumn("", "param1Title", "", true);
$dg->addColumn("", "DocTypeDesc", "", true);
	
$col = $dg->addColumn("گروه مدرک", "param1", "");
if($access)
	$col->editor = "this.DocTypeGroupCombo";
$col->renderer = "function(v,p,r){return r.data.param1Title;}";
$col->width = 120;

$col = $dg->addColumn("مدرک", "DocType", "");
$col->renderer = "function(v,p,r){return r.data.DocTypeDesc;}";
if($access)
	$col->editor = "this.DocTypeCombo";
$col->width = 140;

$col = $dg->addColumn("سریال سند", "DocSerial", "");
if($access)
	$col->editor = ColumnEditor::TextField(true);
$col->width = 80;

$col = $dg->addColumn("عنوان مدرک ارسالی", "DocDesc", "");
if($access)
	$col->editor = ColumnEditor::TextField(true);

$col = $dg->addColumn("فایل", "FileType", "");
$col->renderer = "function(v,p,r){return ManageDocument.FileRender(v,p,r)}";
if($access)
	$col->editor = "this.FileCmp";
$col->align = "center";
$col->width = 100;

$col = $dg->addColumn("توضیحات کارشناس", "RejectDesc", "");
$col->renderer = "function(v,p,r){return ManageDocument.commentRender(v,p,r)}";
$col->align = "center";
$col->width = 60;

if($access)
{
	$col = $dg->addColumn("حذف", "", "");
	$col->renderer = "function(v,p,r){return ManageDocument.OperationRender(v,p,r)}";
	$col->width = 40;

	$dg->addButton("", "اضافه مدرک", "add", "function(){ManageDocumentObject.AddDocument();}");
	
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(){return ManageDocumentObject.SaveDocument();}";
}

if($_SESSION["USER"]["framework"])
{
	$col = $dg->addColumn("تایید/رد", "", "");
	$col->renderer = "function(v,p,r){return ManageDocument.ConfirmRender(v,p,r)}";
	$col->width = 60;
}

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 330;
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

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManageDocument(){
	
	this.FileCmp = new Ext.form.File({
		name : "FileType"
	});

	this.DocTypeGroupCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields:["InfoID","InfoDesc"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + 'dms.data.php?task=selectDocTypeGroups',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			autoLoad : true
		}),
		typeAhead: false,
		queryMode : "local",
		valueField : "InfoID",
		displayField : "InfoDesc",
		listeners : {
			select : function(combo,records){
				ManageDocumentObject.DocTypeCombo.setValue();
				ManageDocumentObject.DocTypeCombo.getStore().proxy.extraParams["GroupID"] = this.getValue();
				ManageDocumentObject.DocTypeCombo.getStore().load();
			}
		}
	});
	
	this.DocTypeCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields:["InfoID","InfoDesc"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + 'dms.data.php?task=selectDocTypes',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			listeners : {
				beforeload : function(store){
					if(!store.proxy.extraParams.GroupID)
					{
						group = ManageDocumentObject.DocTypeGroupCombo.getValue();
						if(group == "")
							return false;
						this.proxy.extraParams["GroupID"] = group;
					}
				}
			}
		}),
		typeAhead: false,
		pageSize : 10,
		valueField : "InfoID",
		displayField : "InfoDesc"
	});

	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsConfirm == "YES")
			return "greenRow";
		if(record.data.IsConfirm == "NO")
			return "pinkRow";
		return "";
	}
	if(this.grid.plugins[0])
		this.grid.plugins[0].on("beforeedit", function(editor,e){
			if(e.record.data.IsConfirm == "YES")
				return false;
			if(e.record.data.DocumentID > 0 &&
				e.record.data.RegPersonID != "<?= $_SESSION["USER"]["PersonID"] ?>")
				return false;	
			return true;
		});
		
	this.grid.render(this.get("div_grid"));
}

ManageDocumentObject = new ManageDocument();

ManageDocument.FileRender = function(v,p,r){
	
	if(v == "" || v == null)
		return "";
	
	return "<div align='center' title='مشاهده فایل' class='attach' "+
		"onclick='ManageDocument.ShowFile(" + r.data.DocumentID + ");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16;float:right'></div>";
}

ManageDocument.ShowFile = function(DocumentID){
	
	window.open("../../dms/ShowFile.php?DocumentID=" + DocumentID);
}

ManageDocument.OperationRender = function(v,p,r){
	
	if(r.data.IsConfirm == "YES" || r.data.RegPersonID != "<?= $_SESSION["USER"]["PersonID"] ?>")
		return "";
	
	return  "<div align='center' title='حذف' class='remove' "+
		"onclick='ManageDocumentObject.DeleteDocument();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16;float:right'></div>";		
}

ManageDocument.commentRender = function(v,p,r){
		
	if(v == "" || v == null)
		return "";
	return "<div align='center' title='توضیحات کارشناس' class='comment' " +
		" onclick='ManageDocumentObject.ShowComment()' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16;float:right'></div>";
}

ManageDocument.prototype.ShowComment = function(){
	
	if(!this.commentWin)
	{
		this.commentWin = new Ext.window.Window({
			width : 400,
			height : 200,
			bodyStyle : "background-color:white;padding:10px",
			html : "",
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.commentWin);
	}
	
	var record = this.grid.getSelectionModel().getLastSelected();
	this.commentWin.update(record.data.RejectDesc);
	this.commentWin.show();
	this.commentWin.center();
}

ManageDocument.prototype.AddDocument = function(){
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		DocumentID: null,
		DocDesc: null
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

ManageDocument.prototype.SaveDocument = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'dms.data.php',
		method: "POST",
		isUpload : true,
		form : this.get("MainForm"),
		params: {
			task: "SaveDocument",
			record: Ext.encode(record.data),
			ObjectID : '<?= $ObjectID ?>',
			ObjectType : '<?= $ObjectType ?>'
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				ManageDocumentObject.grid.getStore().load();
				ManageDocumentObject.FileCmp.reset();
			}
			else
			{
				if(st.data == "")
					alert("خطا در اجرای عملیات");
				else
					alert(st.data);
			}
		},
		failure: function(){}
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
			url: me.address_prefix + 'dms.data.php',
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

<?if($_SESSION["USER"]["framework"]){?>

ManageDocument.ConfirmRender = function(v,p,r){
	
	if(r.data.IsConfirm == "YES")
		return "";
	
	st = "<div align='center' title='تایید' class='tick' "+
		"onclick='ManageDocumentObject.beforeConfirmDocument(\"YES\");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:24px;height:16;float:right'></div>";
	if(r.data.IsConfirm == "NOTSET")
		st += "<div align='center' title='رد' class='cross' "+
		"onclick='ManageDocumentObject.beforeConfirmDocument(\"NO\");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16;float:right'></div>";
	
	return st;
}

ManageDocument.prototype.beforeConfirmDocument = function(mode){
	if(mode == "YES")
	{
		Ext.MessageBox.confirm("","فایل با اصل مدرک مطابق می باشد؟", function(btn){
			if(btn == "no")
				return;
			
			ManageDocumentObject.ConfirmDocument("YES");
		});
		return;
	}
	if(!this.confirmWin)
	{
		this.confirmWin = new Ext.window.Window({
			width : 412,
			height : 198,
			modal : true,
			title : "دلیل رد مدرک برای مشتری",
			bodyStyle : "background-color:white",
			items : [{
				xtype : "textarea",
				width : 400,
				rows : 8,
				name : "RejectDesc"
			}],
			closeAction : "hide",
			buttons : [{
				text : "رد مدرک",
				iconCls : "cross",
				handler : function(){ManageDocumentObject.ConfirmDocument('NO');}
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.confirmWin);
	}
	this.confirmWin.show();
	this.confirmWin.center();
}

ManageDocument.prototype.ConfirmDocument = function(mode){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(this.grid,{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'../../dms/dms.data.php',
		method: "POST",
		params: {
			task: "ConfirmDocument",
			DocumentID : record.data.DocumentID,
			mode : mode,
			RejectDesc : mode == "NO" ? this.confirmWin.down("[name=RejectDesc]").getValue() : ""
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				ManageDocumentObject.grid.getStore().load();
				if(ManageDocumentObject.confirmWin)
					ManageDocumentObject.confirmWin.hide();
			}
			else
			{
				if(st.data == "")
					alert("خطا در اجرای عملیات");
				else
					alert(st.data);
			}
		},
		failure: function(){}
	});
}

<?}?>
</script>
<form id="MainForm" enctype="multipart/form-data">
	<table width=100%>
		<tr>
			<td class="blueText" style="line-height: 21px">
	ردیف های سبز رنگ ردیف های تایید شده و برابر اصل شده توسط صندوق بوده و قابل تغییر نمی باشند
	<br>
	ردیف های قرمز ردیف های رد شده توسط صندوق می باشند
	</div></td>
			<td width="55px" align='left' style='cursor:pointer' data-qtip='برای ویرایش ردیف روی ردیف دبل کلیک کنید'>
				راهنما<div align='center' class='help' style='background-repeat:no-repeat;
					 background-position:center;width:24px;height:16;float:right'></div>
			</td>
		</tr>
	</table>
	
	<div>
		<div id="div_grid"><div>
	</div>
</form>