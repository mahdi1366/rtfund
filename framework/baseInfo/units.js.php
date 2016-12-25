<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

<?
$AddMode = isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "adding" ? true : false;
?>

Unit.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
        AddMode : <?= $AddMode ? "true" : "false" ?>,
	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,
        parent : <?= isset($_REQUEST["parent"]) ? $_REQUEST["parent"] : "null" ?>,
        selectHandler : <?= isset($_REQUEST["selectHandler"]) ? $_REQUEST["selectHandler"] : "function(){}" ?>,
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Unit(){
	
	this.tree = new Ext.tree.Panel({
		renderTo : this.get('tree-div'),
		frame: true,
		width: 750,
		height: 600,
		title: "واحد های سازمان",
		plugins: [new Ext.tree.Search()],
		store : new Ext.data.TreeStore({
			root : {
				id : "source",
				text : "واحدها",
				expanded: true
			},
			proxy: {
				type: 'ajax',
				url: this.address_prefix + "baseInfo.data.php?task=GetTreeNodes&AddMode=" +this.AddMode
			}
		}),
                  listeners : {
			itemclick : function(v,record){
				
				UnitObject.selectHandler(record.data.id, record.data.text);
				UnitObject.parent.hide();
			}
		}
	});
if(!this.AddMode)
	{
	 this.tree.getDockedItems('toolbar[dock="top"]')[0].add({
		xtype : "button",
		iconCls : "print",
		text : "چاپ",
		handler : function(){
			Ext.ux.Printer.print(UnitObject.tree);
		}
	});
        }

	this.tree.on("itemcontextmenu", function(view, record, item, index, e)
	{
		e.stopEvent();
		e.preventDefault();
		view.select(index);

		Menu = new Ext.menu.Menu();
		me = UnitObject;
		
		if(record.data.id.indexOf("p_") == -1)
		{
			if(me.AddAccess)
				Menu.add({
					text: 'ایجاد زیر واحد',
					iconCls: 'add',
					handler: function(){ UnitObject.BeforeSaveUnit("new");}
				});
			if(me.EditAccess)
				Menu.add({
					text: 'ویرایش اطلاعات واحد سازمانی',
					handler: function(){ UnitObject.BeforeSaveUnit("edit");},
					iconCls: 'edit'
				});
			if(me.RemoveAccess)
				Menu.add({
					text: 'حذف واحد سازمانی',
					handler: function(){ UnitObject.DeleteUnit();},
					iconCls: 'remove'
				}
			);
		}

		var coords = e.getXY();
		Menu.showAt([coords[0]-120, coords[1]]);
	});
	
	this.infoWin = new Ext.window.Window({
		applyTo: this.get("NewWIN"),
		modal : true,
		title: "اطلاعات واحد",
		width : 500,
		closeAction : "hide",

		items : new Ext.form.Panel({
			bodyStyle : "text-align:right;padding:5px",
			frame: true,
			items :[{
					xtype : "textfield",
					name : "UnitName",
					itemId : "UnitName",
					fieldLabel : "عنوان",
					anchor : "100%"
				},{
					xtype : "hidden",
					name : "UnitID",
					itemId : "UnitID"
				},{
					xtype : "hidden",
					name : "ParentID",
					itemId : "ParentID"
				}],
			buttons :[{
					text : "ذخیره",
					handler : function(){UnitObject.SaveUnit();},
					iconCls : "save"
				},{
					text : "انصراف",
					handler : function(){
						UnitObject.infoWin.hide();
					},
					iconCls : "undo"
				}]
		})
	});
}

var UnitObject = new Unit();

Unit.prototype.BeforeSaveUnit = function(mode)
{
	var record = this.tree.getSelectionModel().getSelection()[0];
	this.infoWin.down('form').getForm().reset();

	this.infoWin.show();
	this.infoWin.down('form').getComponent("ParentID").setValue(record.data.id);
	
	if(mode == "edit")
	{
		this.infoWin.down('form').getComponent("UnitID").setValue(record.data.id);
		this.infoWin.down('form').getComponent("UnitName").setValue(record.data.text);
		this.infoWin.down('form').getComponent("ParentID").setValue(record.data.parentId);
	}
	else
		this.infoWin.down('form').getComponent("ParentID").setValue(record.data.id);
}

Unit.prototype.SaveUnit = function(){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	this.infoWin.down('form').getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'baseInfo.data.php?task=SaveUnit',
		method : "POST",
		
		success : function(form,action){                
			
			UnitID = UnitObject.infoWin.down('form').getComponent("UnitID").getValue();
			mode = UnitID == "" ? "new" : "edit";

			if(mode == "new")
			{
				ParentID = UnitObject.infoWin.down('form').getComponent("ParentID").getValue();
				if(ParentID == "source")
					Parent = UnitObject.tree.getRootNode()
				else
					Parent = UnitObject.tree.getRootNode().findChild("id",ParentID,true);
				Parent.set('leaf', false);
				Parent.appendChild({
					id : action.result.data,
					text :  UnitObject.infoWin.down('form').getComponent("UnitName").getValue(),
					leaf : true
				});  
				Parent.expand();
			}
			else
			{
				node = UnitObject.tree.getRootNode().findChild("id", UnitID, true);
				node.set('text', UnitObject.infoWin.down('form').getComponent("UnitName").getValue());
			}

			UnitObject.infoWin.down('form').getForm().reset();
			UnitObject.infoWin.hide();

			mask.hide();

		},
		failure : function(form,action)
		{
			Ext.MessageBox.alert('Error', action.result.data);
			mask.hide();
		}
	});
}

Unit.prototype.DeleteUnit = function()
{
	var record = this.tree.getSelectionModel().getSelection()[0];
	
	if(record.childNodes.length != 0)
	{
		alert("این واحد شامل واحد فرعی می باشد و تنها زمانی قابل حذف است که هیچ واحد فرعی نداشته باشد");
		return;
	}
	
	if(!confirm("آیا مایل به حذف می باشید؟"))
	{
		return;
	}
	mask = new Ext.LoadMask(this.tree, {msg:'در حال ذخيره سازي...'});
	mask.show();
	Ext.Ajax.request({
		url : this.address_prefix + 'baseInfo.data.php?task=DeleteUnit',
		method : 'POST',
		params :{
			UnitID : record.data.id
		},
		
		success: function(response,option){			
			mask.hide();
			var sd = Ext.decode(response.responseText );
			if(sd.success)
			{
				record.remove();
	            return;
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
			}
		}
	});
}

</script>