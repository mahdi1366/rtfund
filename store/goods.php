<?php
//---------------------------
// developer:	Sh.Jafarkhani
// Date:		98.06
//---------------------------
require_once '../header.inc.php'; 
require_once inc_dataGrid;
//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

$VariableType_ComboData = array(
    array("type" => "textfield", "name" => "متنی"),
    array("type" => "numberfield", "name" => "عددی"),
    array("type" => "currencyfield", "name" => "مبلغ"), // todo
    array("type" => "checkbox", "name" => "انتخابی"),
    array("type" => "combobox", "name" => "لیستی"),
    array("type" => "filefield", "name" => "فایل"),
    array("type" => "textarea", "name" => "متن طولاتی"),
    array("type" => "timefield", "name" => "ساعت"),
    array("type" => "shdatefield", "name" => "تاریخ"));

$dg = new sadaf_datagrid("atts_grid", $js_prefix_address . "store.data.php?task=SelectProperties");

$col = $dg->addColumn("", "PropertyID", "", true);
$col = $dg->addColumn("", "GoodID", "", true);
$col = $dg->addColumn("", "IsActive", "", true);

$col = $dg->addColumn("نام ویژگی", "PropertyTitle", "");
$col->editor = ColumnEditor::TextField();

$col = $dg->addColumn(" نوع", "PropertyType", "");
$col->editor = ColumnEditor::ComboBox( $VariableType_ComboData, 'type','name' );
$col->width = 100;

$col = $dg->addColumn("مقادیر", "PropertyValues");
$col->editor = ColumnEditor::TextField(true);
$col->width = 120;

$dg->addButton = true;
$dg->addHandler = "function(){STO_goodsObject.AddProperty();}";

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store,record){STO_goodsObject.SaveProperty(store,record);}";

// $dg->deleteButton = true;

$col = $dg->addColumn("حذف", "", "string");
$col->renderer = "function(v,p,r){return STO_goods.DeleteRender(v,p,r);}";

$dg->autoExpandColumn = "PropertyTitle";
$col->width = 70;
$dg->height = 400;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">
STO_goods.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
};

function STO_goods() {

	this.infoPanel = new Ext.form.Panel({
		applyTo: this.get("NewPnl"),
		title: "اطلاعات کالاها",
		bodyStyle: "text-align:right;padding:5px",
		frame: true,
		hidden: true,
		width: 500,
		items: [{
				xtype: "hidden",
				name: "GoodID",
				itemId: "GoodID",
				fieldLabel: "کد",
				labelWidth: 40,
				hideTrigger: true
			},{
				xtype: "textfield",
				name: "GoodName",
				itemId: "GoodName",
				fieldLabel: "عنوان",
				width: 480
			},{
				xtype: "combo",
				width: 480,
				fieldLabel: "مقیاس",
				itemId: 'ScaleID',
				store: new Ext.data.Store({
					proxy: {type: 'jsonp',
						url: this.address_prefix + 'store.data.php?task=SelectGoodScales',
						reader: {root: 'rows', totalProperty: 'totalCount'}
					},
					fields: ['InfoID', 'InfoDesc'],
					autoLoad: true
				}),
				queryMode: 'local',
				valueField: "InfoID",
				name: "ScaleID",
				displayField: "InfoDesc"
			},{
				xtype: "fieldset",
				title: "اطلاعات استهلاک",
				style: "color : #5896E8",
				html: "در صورتی که روش محاسبه استهلاک مستقیم است، نرخ استهلاک باید بر حساب ماه باشد " +
						"و اگر روش محاسبه استهلاک نزولی باشد نرخ استهلاک بر حسب درصد می باشد.",
				items: [{
						xtype: "combo",
						labelWidth: 150,
						fieldLabel: "روش محاسبه استهلاک",
						store: new Ext.data.Store({
							data: [{id: "1", title: 'مستقیم'}, {id: "2", title: 'نزولی'}],
							fields: ['id', 'title']
						}),
						queryMode: 'local',
						valueField: "id",
						name: "depreciateType",
						displayField: "title"
					}, {
						xtype: "numberfield",
						labelWidth: 150,
						hideTrigger: true,
						fieldLabel: "نرخ استهلاک",
						name: "depreciateRatio"
					}]
			},{
				xtype: "hidden",
				itemId: "ParentID",
				name: "ParentID"
			}, {
				xtype: "hidden",
				itemId: "old_GoodID",
				name: "old_GoodID"
			}],
		buttons: [{
				text: "ذخیره",
				handler: function () {
					STO_goodsObject.SaveGood();
				},
				iconCls: "save"
			}, {
				text: "انصراف",
				handler: function () {
					STO_goodsObject.infoPanel.hide();
				},
				iconCls: "undo"
			}]
	});

	this.tree = new Ext.tree.Panel({
		renderTo: this.get('tree-div'),
		frame: true,
		width: 600,
		height: 600,
		plugins: [new Ext.tree.Search()],
		store: new Ext.data.TreeStore({
			root: {
				id: "source",
				text: "گروه های کالاهای اموالی",
				expanded: true
			},
			proxy: {
				type: 'ajax',
				url: this.address_prefix + "store.data.php?task=GetGoodsTree"
			}
		})
	});

	this.tree.getDockedItems('toolbar[dock="top"]')[0].add({
			xtype: "button",
			iconCls: "print",
			text: "چاپ",
			handler: function () {
				Ext.ux.Printer.print(STO_goodsObject.tree);
			}
		}, '-', {
			xtype: "button",
			iconCls: "refresh",
			text: "بازگذاری مجدد",
			handler: function () {
				STO_goodsObject.tree.getStore().load();
			}
		}
	);

	this.tree.on("itemcontextmenu", function (view, record, item, index, e){
		e.stopEvent();
		e.preventDefault();
		view.select(index);
		me = STO_goodsObject;

		this.Menu = new Ext.menu.Menu();

		if(me.AddAccess)
			this.Menu.add({
				text: 'ایجاد رکورد',
				iconCls: 'add',
				handler: function(){ STO_goodsObject.BeforeSave("new"); }
			});
		if (record.data.id != "source")
		{   
			if(me.EditAccess)
				this.Menu.add({
					text: 'ویرایش رکورد',
					iconCls: 'edit',
					handler: function(){ STO_goodsObject.BeforeSave("edit"); }

				});
			
			if(me.RemoveAccess)
				this.Menu.add({
					text: 'حذف رکورد',
					iconCls: 'remove',
					handler: function(){ STO_goodsObject.DeleteGoods(); }

				});
			
			if(me.EditAccess)
				this.Menu.add({
					text: 'مدیریت مشخصه ها',
					iconCls: 'list',
					handler: function(){ STO_goodsObject.ShowProperties(); }

				});
		}
	
		var coords = e.getXY();
		this.Menu.showAt([coords[0] - 120, coords[1]]);
	});
	
	this.grid = <?= $grid ?>;
	this.PropertiesWin = new Ext.window.Window({
		width : 600,
		title : "آیتم های فرم",
		bodyStyle : "background-color:white;text-align:-moz-center",
		height : 450,
		modal : true,
		closeAction : "hide",
		items : this.grid,
		buttons :[{
			text : "بازگشت",
			iconCls : "undo",
			handler : function(){this.up('window').hide();}
		}]
	});
	Ext.getCmp(this.TabID).add(this.PropertiesWin);
}

var STO_goodsObject = new STO_goods();

STO_goods.prototype.BeforeSave = function (mode, obj){

	var record = this.tree.getSelectionModel().getSelection()[0];

	this.infoPanel.getForm().reset();
	this.infoPanel.show();

	if (mode == "edit")
	{
		this.infoPanel.getComponent("ParentID").setValue(record.raw.ParentID);
		this.infoPanel.getComponent("GoodName").setValue(record.raw.GoodName);
		this.infoPanel.getComponent("GoodID").setValue(record.data.id);
	} 
	else 
	{
		this.infoPanel.getComponent("ParentID").setValue(record.data.id == "source" ? 0 : record.data.id);
	}
}

STO_goods.prototype.DeleteGoods = function () {

	var record = this.tree.getSelectionModel().getSelection()[0];

	if (record.childNodes.length != 0)
	{
		Ext.MessageBox.alert("Error","ابتدا رکوردهای این گروه را حذف کنید");
		return;
	}
	if (!confirm("آیا مایل به حذف می باشید؟"))
		return;

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg: 'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'DeleteGood', 
			GoodID: record.data.id
		},
		url: this.address_prefix + 'store.data.php',
		method: 'POST',
		success: function (response) {
			mask.hide();
			record.remove();
		},
		failure: function () {}
	});

}

STO_goods.prototype.SaveGood = function () {

	this.infoPanel.getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'store.data.php?task=SaveGood',
		method: "POST",

		success: function (form, action) {

			mode = STO_goodsObject.infoPanel.getComponent("GoodID").getValue() == "" ? "new" : "edit";

			GoodID = STO_goodsObject.infoPanel.getComponent("GoodID").getValue();
			ParentID = STO_goodsObject.infoPanel.getComponent("ParentID").getValue();
			if (ParentID != "0")
				Parent = STO_goodsObject.tree.getRootNode().findChild("id", ParentID, true);
			else
				Parent = STO_goodsObject.tree.getRootNode();

			if (mode == "new")
			{
				Parent.set('leaf', false);
				Parent.appendChild({
					id: action.result.data,
					text: STO_goodsObject.infoPanel.getComponent("GoodName").getValue(),
					leaf: true
				});
			} else
			{
				STO_goodsObject.tree.getRootNode().findChild("id", GoodID, true).
						set('text', STO_goodsObject.infoPanel.getComponent("GoodName").getValue());
			}

			STO_goodsObject.infoPanel.getForm().reset();
			STO_goodsObject.infoPanel.hide();

		},
		failure: function (form, action)
		{
		}
	});
}

STO_goods.prototype.ShowProperties = function(){
	
	var record = this.tree.getSelectionModel().getSelection()[0];
	this.grid.getStore().proxy.extraParams.GoodID = record.data.id;
	this.grid.getStore().load();    
	this.PropertiesWin.show();      
}

STO_goods.DeleteRender = function(v,p,r){
	
	return "<div align='center' title='حذف ' class='remove' onclick='STO_goodsObject.DeleteProperty();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

STO_goods.prototype.AddProperty = function(){
	
	var record = this.tree.getSelectionModel().getSelection()[0];	
	
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		GoodID : record.data.id,
		PropertyID : "",
		PropertyTitle : "",
		PropertyType : ""
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

STO_goods.prototype.SaveProperty = function(store,record){
	
    mask = new Ext.LoadMask(this.PropertiesWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveProperty',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'store.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				STO_goodsObject.grid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

STO_goods.prototype.DeleteProperty = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	Ext.MessageBox.confirm("", "آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		me = STO_goodsObject;
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال حذف ...'});
		mask.show();
		
		Ext.Ajax.request({
		  	url : me.address_prefix + "store.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteProperty",
		  		PropertyID : record.data.PropertyID
		  	},
		  	success : function(response)
		  	{
				mask.hide();
				result = Ext.decode(response.responseText);
				if(result.success)
					STO_goodsObject.grid.getStore().load();
				else
				{
					if(result.data == "")
						Ext.MessageBox.alert("ERROR", "عملیات مورد نظر با شکست مواجه شد");
					else
						Ext.MessageBox.alert("ERROR", result.data);
				}
					
		  	}
		});
	});
}

</script>
<table width="750px" style="margin:10px">
	<tr>
		<td valign="top" width="40%"><div id="tree-div"></div></td>
		<td valign="top" style="padding-right : 20px">
			<div id="NewPnl" class="x-hide-display"></div>
		</td>
	</tr>
</table>
