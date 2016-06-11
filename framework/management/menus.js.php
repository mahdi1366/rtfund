<script type="text/javascript">
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------

Menu.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	GroupRecord : null,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Menu()
{
	this.SystemPanel = new Ext.form.Panel({
		title: "انتخاب سیستم",
		width: 500,
		renderTo : this.get("div_systems"),
		frame: true,
		items : [{
			xtype : "combo",
			store: new Ext.data.Store({
				autoLoad : true,
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'framework.data.php?task=selectSystems',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['SystemID','SysName']
			}),
			displayField: 'SysName',
			valueField : "SystemID",
			queryMode: "local",
			width : 400,
			itemId : "SystemID",
			listeners :{
				select : function(){
					MenuObject.grid.getStore().proxy.extraParams = {
						SystemID : this.getValue()
					};
					if(MenuObject.grid.rendered)
						MenuObject.grid.getStore().load();
					else
						MenuObject.grid.render(MenuObject.get("div_grid"));
				}
			}
		}]
	});
	
	this.MenuWin = new Ext.window.Window({
		autoScroll : true,
		width : 400,
		modal : true,
		
		title : "ایجاد منو",
		closeAction : "hide",
		items : [{
			xtype : "form",
			items : [{
				xtype : "textfield",
				name : "MenuDesc",
				anchor : "95%",
				fieldLabel : "عنوان منو"
			},{
				xtype : "textfield",
				name : "MenuPath",
				anchor : "95%",
				fieldStyle : "direction:ltr",
				fieldLabel : "مسیر"
			},{
				xtype : "textfield",
				name : "icon",
				fieldStyle : "direction:ltr",
				fieldLabel : "آیکون"
			},{
				xtype : "numberfield",
				name : "ordering",
				hideTrigger : true,
				fieldLabel : "ترتیب"
			},{
				xtype : "combo",
				fieldLabel : "وضعیت منو",
				store: new Ext.data.SimpleStore({
					fields : ['id','title'],
					data : [ 
						['YES', "فعال"],
						["NO", "غیر فعال"] 
					]
				}),   
				displayField: 'title',
				valueField: 'id',
				value : "YES",
				name : "IsActive",
				allowBlank: false
			},{
				xtype : "hidden",
				name : "SystemID"
			},{
				xtype : "hidden",
				name : "ParentID"
			}]
		}],
		buttons :[{
			text : "ذخیره",
			iconCls : "save",
			handler : function(){
				this.up('window').down('form').getForm().submit({
					method : "post",
					url : MenuObject.address_prefix + "framework.data.php?task=SaveMenu",
					
					success : function(form,action){
						MenuObject.MenuWin.hide();
						MenuObject.grid.getStore().load();
					}
				});
			}
		},{
			text : "انصراف",
			iconCls : "undo",
			handler : function(){
				this.up('window').hide();
			}
		}]
	});
	Ext.getCmp(this.TabID).add(this.MenuWin);
	
}

var MenuObject = new Menu();

Menu.deleteRender = function(v,p,r)
{
	if(r.data.MenuID == null || r.data.MenuID == "")
		return;

	return "<div align='center' title='حذف منو' class='remove' "+
		"onclick='MenuObject.DeleteMenu(0,null);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

Menu.prototype.AddMenu = function(e, GroupID, GroupSystemID)
{
	this.MenuWin.down('form').getForm().reset();
	this.MenuWin.down("[name=ParentID]").setValue(GroupID);
	this.MenuWin.down("[name=SystemID]").setValue(GroupSystemID);
	this.MenuWin.show();
	e.stopImmediatePropagation();	
}

Menu.prototype.EditMenu = function(e, GroupID)
{
	if(GroupID == null)
	{
		this.grid.down("[itemId=GroupDesc]").setValue();
		this.grid.down("[itemId=GroupOrder]").setValue();
		this.grid.down("[itemId=GroupIcon]").setValue();
		this.grid.down("[itemId=GroupID]").setValue();
		return;
	}
	record = this.grid.getStore().getAt(this.grid.getStore().find("GroupID", GroupID));
	this.grid.down("[itemId=GroupDesc]").setValue(record.data.GroupDesc);
	this.grid.down("[itemId=GroupOrder]").setValue(record.data.GroupOrder);
	this.grid.down("[itemId=GroupIcon]").setValue(record.data.GroupIcon);
	this.grid.down("[itemId=GroupID]").setValue(GroupID);
	e.stopImmediatePropagation();	
}

Menu.prototype.SaveGroup = function()
{
	var mask = new Ext.LoadMask(this.grid,{msg: 'تغییر اطلاعات ...'});
	mask.show();

	Ext.Ajax.request({
		url : this.address_prefix + "framework.data.php?task=SaveMenu",
		method : "POST",
		params : {
			SystemID : this.SystemPanel.getComponent("SystemID").getValue(),
			ParentID : 0,
			MenuID :  this.grid.down("[itemId=GroupID]").getValue(),
			MenuDesc : this.grid.down("[itemId=GroupDesc]").getValue(),
			ordering : this.grid.down("[itemId=GroupOrder]").getValue(),
			icon : this.grid.down("[itemId=GroupIcon]").getValue()
		},

		success : function(response)
		{
			mask.hide();
			MenuObject.grid.getStore().load();
			MenuObject.grid.down("[itemId=GroupDesc]").setValue();
			MenuObject.grid.down("[itemId=GroupID]").setValue();
			MenuObject.grid.down("[itemId=GroupOrder]").setValue();
			MenuObject.grid.down("[itemId=GroupIcon]").setValue();
		}
	});
}

Menu.prototype.SaveMenu = function(store,record,option)
{
	var mask = new Ext.LoadMask(this.grid,{msg: 'تغییر اطلاعات ...'});
	mask.show();

	Ext.Ajax.request({
		url : this.address_prefix + "framework.data.php?task=SaveMenu",
		method : "POST",
		params : {
			record : Ext.encode(record.data)
		},

		success : function(response)
		{
			mask.hide();
			MenuObject.grid.getStore().load();
		}
	});
}

Menu.prototype.DeleteMenu = function(GroupID, event)
{
	if(event != null)
		event.stopImmediatePropagation();	
		
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟" , function(btn){
		
		if(btn == "no")
			return;
		
		MenuID = GroupID  > 0 ? GroupID : MenuObject.grid.getSelectionModel().getLastSelected().data.MenuID;

		var mask = new Ext.LoadMask(MenuObject.grid,{msg: 'تغییر اطلاعات ...'});
		mask.show();

		Ext.Ajax.request({
			url : MenuObject.address_prefix + "framework.data.php?task=DeleteMenu",
			method : "POST",
			params : {
				MenuID : MenuID
			},

			success : function(response)
			{
				mask.hide();
				
				var sd = Ext.decode(response.responseText);
				if(!sd.success)
				{
					Ext.MessageBox.alert("Error",sd.data);
					return;
				}
				
				MenuObject.grid.getStore().load();
			}
		});
		
	});
	
}
</script>