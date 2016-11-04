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
		require_once '../../loan/request/request.class.php';
		$obj = new LON_requests($ObjectID);
		if($_SESSION["USER"]["IsCustomer"] == "YES" && in_array($obj->StatusID, array("40","60")) )
			$access = true;
		if($_SESSION["USER"]["IsStaff"] == "YES" /*&& $obj->StatusID == "50"*/)
			$access = true;
		break;
	case "plan":
		require_once '../../plan/plan/plan.class.php';
		require_once '../../plan/PLNconfig.inc.php';
		$obj = new PLN_plans($ObjectID);
		if($_SESSION["USER"]["IsCustomer"] == "YES" && $_SESSION["USER"]["PersonID"] == $obj->PersonID 
				&& in_array($obj->StepID, array(STEPID_RAW,STEPID_RETURN_TO_CUSTOMER)) )
			$access = true;
		if($_SESSION["USER"]["IsExpert"] == "YES")
			$access = true;
		if($_SESSION["USER"]["IsStaff"] == "YES")
			$access = true;
		break;
	case "contract":
		require_once '../../contract/contract/contract.class.php';
		$obj = new CNT_contracts($ObjectID);
		if($_SESSION["USER"]["IsStaff"] == "YES")
			$access = true;
		break;
	case "warrenty":
		require_once '../../loan/warrenty/request.class.php';
		require_once '../../loan/warrenty/config.inc.php';
		$obj = new WAR_requests($ObjectID);
		if($_SESSION["USER"]["IsStaff"] == "YES")
			$access = true;
		if($_SESSION["USER"]["IsCustomer"] == "YES" && $_SESSION["USER"]["PersonID"] == $obj->PersonID 
				&& in_array($obj->StatusID, array(STEPID_RAW,STEPID_RETURN_TO_CUSTOMER)) )
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
$dg->addColumn("", "param1", "", true);
$dg->addColumn("", "DocType", "", true);

$col = $dg->addColumn("گروه مدرک", "param1Title", "");
$col->width = 100;

$col = $dg->addColumn("مدرک", "DocTypeDesc", "");
$col->width = 120;

$col = $dg->addColumn("اطلاعات مدرک", "paramValues", "");
$col->width = 150;

$col = $dg->addColumn("عنوان مدرک ارسالی", "DocDesc", "");

$col = $dg->addColumn("فایل", "HaveFile", "");
$col->renderer = "function(v,p,r){return ManageDocument.FileRender(v,p,r)}";
$col->align = "center";
$col->width = 30;

$col = $dg->addColumn("توضیحات کارشناس", "RejectDesc", "");
$col->renderer = "function(v,p,r){return ManageDocument.commentRender(v,p,r)}";
$col->align = "center";
$col->width = 60;

if($access)
{
	$col = $dg->addColumn("عملیات", "", "");
	$col->renderer = "function(v,p,r){return ManageDocument.OperationRender(v,p,r)}";
	$col->width = 60;

	$dg->addButton("", "اضافه مدرک", "add", "function(){ManageDocumentObject.AddDocument();}");
	
	$dg->enableRowEdit = true;
	$dg->rowEditOkHandler = "function(){return ManageDocumentObject.SaveDocument();}";
}

if(isset($_SESSION["USER"]["framework"]))
{
	$col = $dg->addColumn("تایید/رد", "", "");
	$col->renderer = "function(v,p,r){return ManageDocument.ConfirmRender(v,p,r)}";
	$col->width = 60;
	
	$dg->addButton("", "برگشت از تایید", "return", "function(){ManageDocumentObject.UnConfirm();}");
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

	pageIndex : 1,
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManageDocument(){
	
	this.ParamsStore = new Ext.data.Store({
		fields:["DocType","ParamID","ParamDesc","ParamType"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + 'dms.data.php?task=selectAllParams',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		autoLoad : true
	});
	
	this.paramValuesStore = new Ext.data.Store({
		fields:["ParamID","ParamValue"],
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + 'dms.data.php?task=selectParamValues',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		}
	});
	
	this.formPanel = new Ext.form.Panel({
		renderTo: this.get("MainForm"),      
		width : 650,
		style : "margin-top:10px",
		bodyPadding: ' 10 10 12 10',
		frame: true,
		layout :{
			type : "table",
			columns :2
		},
		title: 'اطلاعات مدرک',
		hidden : true,
		items : [{
			xtype : "combo",
			fieldLabel : "گروه مدرک",
			allowBlank : false,
			name : "param1",
			itemId : "DocTypeGroupCombo",
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
				change : function(combo,records){
					me = ManageDocumentObject;
					me.formPanel.getComponent("DocTypeCombo").setValue();
					me.formPanel.getComponent("DocTypeCombo").getStore().proxy.extraParams["GroupID"] = 
						this.getValue();
					me.formPanel.getComponent("DocTypeCombo").getStore().load();
				}
			}
		},{
			xtype : "combo",
			fieldLabel : "مدرک",
			allowBlank : false,
			itemId : "DocTypeCombo",
			name : "DocType",
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
							me = ManageDocumentObject;							
							group = me.formPanel.getComponent("DocTypeGroupCombo").getValue();
							if(group == "")
								return false;
							this.proxy.extraParams["GroupID"] = group;
						}
					}				
				}
			}),
			listeners : {
				change : function(combo,value){
					//-------------- make params -----------------	
					DocType = value;

					me = ManageDocumentObject;
					ParamsFS = me.formPanel.getComponent("ParamsFS");
					ParamsFS.removeAll();
					
					me.ParamsStore.each(function(record){
						if(DocType == record.data.DocType)
						{
							ParamsFS.add({
								xtype : record.data.ParamType,
								name : "Param" + record.data.ParamID,
								fieldLabel : record.data.ParamDesc,
								hideTrigger : (record.data.ParamType == "numberfield" || 
									record.data.ParamType == "currencyfield" ? true : false)
							});
						}
					});
					
					if(ParamsFS.items.length > 0)
						ParamsFS.setTitle("اطلاعات " + combo.getRawValue());
					
					//------------- fill params -------------------
					DocumentID = me.formPanel.down("[name=DocumentID]").getValue();
					if(DocumentID > 0)
					{
						me.paramValuesStore.load({
							params : {
								DocumentID : DocumentID
							},
							callback : function(){
								store = ManageDocumentObject.paramValuesStore;
								store.each(function(record){
									me.formPanel.down("[name=Param" + 
										record.data.ParamID + "]").setValue(record.data.ParamValue);
								});
							}
						});
					}					
				}
			},
			typeAhead: false,
			valueField : "InfoID",
			displayField : "InfoDesc"
		},{
			xtype : "textfield",
			width : 564,
			fieldLabel : "شرح مدرک",
			colspan : 2,
			name : "DocDesc"
		},{
			xtype : "fieldset",
			colspan : 2,
			title : "اطلاعات مدرک",
			itemId : "ParamsFS",
			layout : "column",
			columns : 2
		},{
			xtype : "fieldset",
			title : "فایل های مدرک",
			layout : "column",
			columns : 2,
			colspan : 2,
			items:[{
				xtype : "displayfield",
				hideTrigger : true,
				labelWidth : 50,
				width : 80,
				fieldCls : "blueText",
				value : "صفحه [ 1 ]"
			},{
				xtype : "filefield",
				width : 450,
				fieldLabel : "فایل مدرک",
				name : "FileType_1"
			},{
				xtype : "button",
				text : "اضافه صفحه",
				colspan : 2,
				iconCls : "add",
				handler : function(){
					me = ManageDocumentObject;
					me.pageIndex++;
					fs = this.up("fieldset");
					fs.insert(fs.items.length-1, [{
						xtype : "displayfield",
						hideTrigger : true,
						labelWidth : 50,
						width : 80,
						fieldCls : "blueText",
						value : "صفحه [ " + me.pageIndex + " ]"
					},{
						xtype : "filefield",
						width : 450,
						fieldLabel : "فایل مدرک",
						name : "FileType_" + me.pageIndex
					}]);
					
				}
			}]
		},{
			xtype : "hidden",
			name : "DocumentID"
		}],
		buttons : [{
			text : "ذخیره",
			iconCls : "save",
			handler : function(){ ManageDocumentObject.SaveDocument(); }
		},{
			text : "انصراف",
			iconCls : "undo",
			handler : function(){
				ManageDocumentObject.formPanel.hide();
				ManageDocumentObject.grid.show();
			}
		}]
	});

	this.grid = <?= $grid ?>;
	this.grid.addDocked({
		xtype: 'toolbar',
		dock: 'bottom',
		items: [{ 
			xtype: 'container', 
			width : 680,
			html : "ردیف های سبز رنگ ردیف های تایید شده و برابر اصل شده توسط صندوق بوده و قابل تغییر نمی باشند"+
				"<br>ردیف های قرمز ردیف های رد شده توسط صندوق می باشند"
		}
    ]});
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
	
	if(v == "false")
		return "";
	
	return "<div align='center' title='مشاهده فایل' class='attach' "+
		"onclick='ManageDocument.ShowFile(" + r.data.DocumentID + "," + r.data.ObjectID + ");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16;float:right'></div>";
}

ManageDocument.ShowFile = function(DocumentID, ObjectID){
	
	window.open("/office/dms/ShowFile.php?DocumentID=" + DocumentID + "&ObjectID=" + ObjectID);
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
	
	this.formPanel.show();
	this.formPanel.getForm().reset();
	this.formPanel.getComponent("ParamsFS").removeAll();
	this.grid.hide();
}

ManageDocument.prototype.EditDocument = function(){
	
	this.formPanel.show();
	this.grid.hide();
	this.formPanel.getComponent("ParamsFS").removeAll();
	
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
		url: this.address_prefix +'dms.data.php',
		method: "POST",
		isUpload : true,
		params: {
			task: "SaveDocument",
			ObjectID : '<?= $ObjectID ?>',
			ObjectType : '<?= $ObjectType ?>'
		},
		success: function(form,action){
			mask.hide();

			if(action.result.success)
			{   
				ManageDocumentObject.grid.getStore().load();
				ManageDocumentObject.formPanel.hide();
				ManageDocumentObject.grid.show();
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

<?if(isset($_SESSION["USER"]["framework"])){?>

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
		url: this.address_prefix +'dms.data.php',
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

ManageDocument.prototype.UnConfirm = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
		return;
	mask = new Ext.LoadMask(this.grid,{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'dms.data.php',
		method: "POST",
		params: {
			task: "UnConfirmDocument",
			DocumentID : record.data.DocumentID
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
				ManageDocumentObject.grid.getStore().load();
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
<center>
	<div id="MainForm"></div>
</center>
<div>
	<div id="div_grid"><div>
</div>
