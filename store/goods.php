<?php
//---------------------------
// developer:	Sh.Jafarkhani
// Date:		98.06
//---------------------------
require_once '../header.inc.php'; 

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//...................................................

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
				xtype: "textarea",
				name: "GoodName",
				itemId: "GoodName",
				fieldLabel: "عنوان",
				labelWidth: 40,
				rows: 5,
				width: 280
			},{
				xtype: "combo",
				fieldLabel: "مقیاس",
				itemId: 'ScaleID',
				store: new Ext.data.Store({
					proxy: {type: 'jsonp',
						url: this.address_prefix + 'store.data.php?task=SelectGoodScales',
						reader: {root: 'rows', totalProperty: 'totalCount'}
					},
					fields: ['id', 'title'],
					autoLoad: true
				}),
				queryMode: 'local',
				valueField: "id",
				name: "ScaleID",
				displayField: "title"
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

	this.tree.on("itemcontextmenu", function (view, record, item, index, e)
	{
		e.stopEvent();
		e.preventDefault();
		view.select(index);
		me = STO_goodsObject;

		this.Menu = new Ext.menu.Menu();

		if(me.AddAccess)
			this.Menu.add({
				text: 'ایجاد رکورد',
				iconCls: 'add',
				handler: function(){ STO_goodsObject.BeforeSaveBbudgets("new"); }
			});
		if (record.data.id != "source")
		{   
			if(me.EditAccess)
				this.Menu.add({
					text: 'ویرایش رکورد',
					iconCls: 'edit',
					handler: function(){ STO_goodsObject.BeforeSaveBbudgets("edit"); }

				});
			
			if(me.RemoveAccess)
				this.Menu.add({
					text: 'حذف رکورد',
					iconCls: 'remove',
					handler: function(){ STO_goodsObject.DeleteGoods(); }

				});
		}
	
		var coords = e.getXY();
		this.Menu.showAt([coords[0] - 120, coords[1]]);
	});
}

var STO_goodsObject = new STO_goods();

STO_goods.prototype.BeforeSaveBbudgets = function (mode, obj){

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

</script>
<table width="750px" style="margin:10px">
	<tr>
		<td valign="top" width="40%"><div id="tree-div"></div></td>
		<td valign="top" style="padding-right : 20px">
			<div id="NewPnl" class="x-hide-display"></div>
		</td>
	</tr>
</table>
