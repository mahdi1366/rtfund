<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

Unit.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

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
				url: this.address_prefix + "baseInfo.data.php?task=GetTreeNodes"
			}
		})
	});
	 this.tree.getDockedItems('toolbar[dock="top"]')[0].add({
		xtype : "button",
		iconCls : "print",
		text : "چاپ",
		handler : function(){
			Ext.ux.Printer.print(UnitObject.tree);
		}
	});

	this.tree.on("itemcontextmenu", function(view, record, item, index, e)
	{
		e.stopEvent();
		e.preventDefault();
		view.select(index);

		Menu = new Ext.menu.Menu();
		
		if(record.data.id.indexOf("p_") != -1)
		{
			Menu.add({
					text: 'ویرایش پست سازمانی',
					handler: function(){ UnitObject.BeforeSavePost("edit");},
					iconCls: 'user_edit'
				},{
					text: 'حذف پست سازمانی',
					handler: function(){ UnitObject.DeletePost();},
					iconCls: 'user_delete'
				});
		}
		else
		{
			Menu.add({
					text: 'ایجاد پست سازمانی',
					iconCls: 'user_add',
					handler: function(){ UnitObject.BeforeSavePost("new");}
				},
				{
					text: 'ایجاد زیر واحد',
					iconCls: 'add',
					handler: function(){ UnitObject.BeforeSaveUnit("new");}
				},{
					text: 'ویرایش اطلاعات واحد سازمانی',
					handler: function(){ UnitObject.BeforeSaveUnit("edit");},
					iconCls: 'edit'
				},{
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

Unit.prototype.BeforeSavePost = function(mode)
{
	if(!this.postWin)
	{
		this.postWin = new Ext.window.Window({
			applyTo: this.get("NewWIN"),
			modal : true,
			title: "اطلاعات پست",
			width : 500,
			closeAction : "hide",

			items : new Ext.form.Panel({
				bodyStyle : "text-align:right;padding:5px",
				frame: true,
				items :[{
					xtype : "textfield",
					name : "PostName",
					itemId : "PostName",
					fieldLabel : "عنوان",
					anchor : "100%"
				},{
					xtype : "hidden",
					name : "UnitID",
					itemId : "UnitID"
				},{
					xtype : "hidden",
					name : "PostID",
					itemId : "PostID"
				}],
				buttons :[{
					text : "ذخیره",
					handler : function(){
						mask = new Ext.LoadMask(Ext.getCmp(UnitObject.TabID), {msg:'در حال ذخيره سازي...'});
						mask.show();
						UnitObject.postWin.down('form').getForm().submit({
							clientValidation: true,
							url: UnitObject.address_prefix + 'baseInfo.data.php?task=SavePost',
							method : "POST",

							success : function(form,action){                

								PostID = UnitObject.postWin.down('form').getComponent("PostID").getValue();
								mode = PostID == "" ? "new" : "edit";

								if(mode == "new")
								{
									UnitID = UnitObject.postWin.down('form').getComponent("UnitID").getValue();
									Parent = UnitObject.tree.getRootNode().findChild("id",UnitID,true);
									Parent.set('leaf', false);
									Parent.appendChild({
										id : action.result.data,
										text :  UnitObject.postWin.down('form').getComponent("PostName").getValue(),
										leaf : true,
										iconCls : "user"
									});  
									Parent.expand();
								}
			 					else
								{
									node = UnitObject.tree.getRootNode().findChild("id", PostID, true);
									node.set('text', UnitObject.postWin.down('form').getComponent("PostName").getValue());
								}

								UnitObject.postWin.down('form').getForm().reset();
								UnitObject.postWin.hide();
								mask.hide();
							},
							failure : function(form,action)
							{
								Ext.MessageBox.alert('Error', action.result.data);
								mask.hide();
							}
						});
					},
					iconCls : "save"
				},{
					text : "انصراف",
					handler : function(){
						UnitObject.postWin.hide();
					},
					iconCls : "undo"
				}]
			})
		});
	}
	
	var record = this.tree.getSelectionModel().getSelection()[0];
	this.postWin.down('form').getForm().reset();

	this.postWin.show();
	this.postWin.down('form').getComponent("UnitID").setValue(record.data.id);
	
	if(mode == "edit")
	{
		this.postWin.down('form').getComponent("PostID").setValue(record.data.id);
		this.postWin.down('form').getComponent("PostName").setValue(record.data.text);
		this.postWin.down('form').getComponent("UnitID").setValue(record.data.parentId);
	}
}

Unit.prototype.DeletePost = function()
{
	var record = this.tree.getSelectionModel().getSelection()[0];
	
	if(!confirm("آیا مایل به حذف می باشید؟"))
	{
		return;
	}
	mask = new Ext.LoadMask(this.tree, {msg:'در حال ذخيره سازي...'});
	mask.show();
	Ext.Ajax.request({
		url : this.address_prefix + 'baseInfo.data.php?task=DeletePost',
		method : 'POST',
		params :{
			PostID : record.data.id
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