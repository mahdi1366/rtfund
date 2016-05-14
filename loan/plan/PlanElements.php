<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.02
//-----------------------------

require_once '../header.inc.php';
?>
<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.02
//-----------------------------
	
PlanElements.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PlanElements(){

	this.tree = new Ext.tree.Panel({
		renderTo : this.get("div_tree"),
		store: new Ext.data.TreeStore({
			proxy: {
				type: 'ajax',
				url: this.address_prefix + 'plan.data.php?task=selectGroups&PlanID=0'
			}					
		}),
		root: {id: 'src', text : "سرفصل های اطلاعات"},
		autoScroll : true,
		width : 780,
		height : 200,
		listeners : {
				itemcontextmenu : function(view, record, item, index, e){
					PlanElementsObject.ShowMenu(view, record, item, index, e);
			}
		}
	});	
	
	this.infoWin = new Ext.window.Window({
		applyTo: this.get("NewWIN"),
		modal : true,
		title: "اطلاعات سطح",
		width : 500,
		closeAction : "hide",

		items : new Ext.form.Panel({
			bodyStyle : "text-align:right;padding:5px",
			frame: true,
			items :[{
					xtype : "textfield",
					name : "GroupDesc",
					itemId : "GroupDesc",
					fieldLabel : "عنوان",
					anchor : "100%"
				},{
					xtype : "hidden",
					name : "GroupID",
					itemId : "GroupID"
				},{
					xtype : "hidden",
					name : "ParentID",
					itemId : "ParentID"
				}],
			buttons :[{
					text : "ذخیره",
					handler : function(){PlanElementsObject.SaveGroup();},
					iconCls : "save"
				},{
					text : "انصراف",
					handler : function(){
						PlanElementsObject.infoWin.hide();
					},
					iconCls : "undo"
				}]
		})
	});
}

PlanElementsObject = new PlanElements();

PlanElements.prototype.ShowMenu = function(view, record, item, index, e)
{
	e.stopEvent();
	e.preventDefault();
	view.select(index);

	Menu = new Ext.menu.Menu();
		
	if(record.data.id == "src")
	{
		Menu.add({
			text: 'ایجاد سطح',
			iconCls: 'add',
			handler: function(){ PlanElementsObject.BeforeSaveGroup("new");}
		});		
	}
	else
	{
		Menu.add({
			text: 'ایجاد زیر سطح',
			iconCls: 'add',
			handler: function(){ PlanElementsObject.BeforeSaveGroup("new");}
		},{
			text: 'ویرایش سطح',
			handler: function(){ PlanElementsObject.BeforeSaveGroup("edit");},
			iconCls: 'edit'
		},{
			text: 'حذف سطح',
			handler: function(){ PlanElementsObject.DeleteGroup();},
			iconCls: 'remove'
		});
	}

	var coords = e.getXY();
	Menu.showAt([coords[0]-120, coords[1]]);
}

PlanElements.prototype.BeforeSaveGroup = function(mode)
{
	var record = this.tree.getSelectionModel().getSelection()[0];
	this.infoWin.down('form').getForm().reset();

	this.infoWin.show();
	this.infoWin.down('form').getComponent("ParentID").setValue(record.data.id);
	
	if(mode == "edit")
	{
		this.infoWin.down('form').getComponent("GroupID").setValue(record.data.id);
		this.infoWin.down('form').getComponent("GroupDesc").setValue(record.data.text);
		this.infoWin.down('form').getComponent("ParentID").setValue(record.data.parentId);
	}
	else
		this.infoWin.down('form').getComponent("ParentID").setValue(record.data.id == "src" ? 0 : record.data.id);
}

PlanElements.prototype.SaveGroup = function(){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	this.infoWin.down('form').getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'plan.data.php?task=SaveGroup',
		method : "POST",
		
		success : function(form,action){                
			
			GroupID = PlanElementsObject.infoWin.down('form').getComponent("GroupID").getValue();
			mode = GroupID == "" ? "new" : "edit";

			if(mode == "new")
			{
				ParentID = PlanElementsObject.infoWin.down('form').getComponent("ParentID").getValue();
				if(ParentID == "source")
					Parent = PlanElementsObject.tree.getRootNode()
				else
					Parent = PlanElementsObject.tree.getRootNode().findChild("id",ParentID,true);
				Parent.set('leaf', false);
				Parent.appendChild({
					id : action.result.data,
					text :  PlanElementsObject.infoWin.down('form').getComponent("GroupDesc").getValue(),
					leaf : true
				});  
				Parent.expand();
			}
			else
			{
				node = PlanElementsObject.tree.getRootNode().findChild("id", GroupID, true);
				node.set('text', PlanElementsObject.infoWin.down('form').getComponent("GroupDesc").getValue());
			}

			PlanElementsObject.infoWin.down('form').getForm().reset();
			PlanElementsObject.infoWin.hide();

			mask.hide();

		},
		failure : function(form,action)
		{
			Ext.MessageBox.alert('Error', action.result.data);
			mask.hide();
		}
	});
}

PlanElements.prototype.DeleteGroup = function()
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
		url : this.address_prefix + 'plan.data.php?task=DeleteGroup',
		method : 'POST',
		params :{
			GroupID : record.data.id
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
<center><br>
	<div id="div_tree"></div>
</center>