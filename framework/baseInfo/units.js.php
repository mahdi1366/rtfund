<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------
Unit.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
    
	AddMode : <?= $AddMode ? "true" : "false" ?>,
	parent : <?= isset($_REQUEST["parent"]) ? $_REQUEST["parent"] : "null" ?>,
	selectHandler : <?= isset($_REQUEST["selectHandler"]) ? $_REQUEST["selectHandler"] : "function(){}" ?>,
	
	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,
	
    
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
				
				if(!UnitObject.AddMode)
					return;
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
		
		if(record.data.id.indexOf("p_") != -1)
		{
			if(me.AddAccess)
				Menu.add({
					text: 'ویرایش شغل',
					handler: function(){ UnitObject.BeforeSaveJob("edit");},
					iconCls: 'user_edit'
				});
			if(me.RemoveAccess)	
				Menu.add({
					text: 'حذف شغل',
					handler: function(){ UnitObject.DeleteJob();},
					iconCls: 'user_delete'
				});
		}
		else
		{
			if(me.AddAccess)
				Menu.add({
					text: 'ایجاد شغل',
					iconCls: 'user_add',
					handler: function(){ UnitObject.BeforeSaveJob("new");}
				},{
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
				});
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

Unit.prototype.BeforeSaveJob = function(mode)
{
	if(!this.JobWin)
	{
		this.JobWin = new Ext.window.Window({
			applyTo: this.get("NewWIN"),
			modal : true,
			title: "اطلاعات شغل",
			width : 500,
			closeAction : "hide",

			items : new Ext.form.Panel({
				bodyStyle : "text-align:right;padding:5px",
				frame: true,
				items :[{
					xtype : "combo", 
					width : 400,
					store : new Ext.data.SimpleStore({
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + 'baseInfo.data.php?task=selectPosts',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields : ['PostID','PostName'],
						autoLoad : true					
					}),
					name : "PostID",
					itemId : "PostID",
					displayField : "PostName",
					valueField : "PostID",
					queryMode : "local",
					fieldLabel : "پست سازمانی"	
				},{
					xtype : "combo",
					width : 400,
					store: new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + '../person/persons.data.php?task=selectPersons&UserType=IsStaff',
							reader: {root: 'rows',totalProperty: 'totalCount'}
						},
						fields :  ['PersonID','fullname'],
						autoLoad : true
					}),
					queryMode : "local",
					fieldLabel : "کاربر",
					displayField: 'fullname',
					valueField : "PersonID",
					name : "PersonID",
					itemId : "PersonID"
				},{
					xtype : "checkbox",
					boxLabel : "شغل اصلی فرد می باشد",
					name : "IsMain",
					inputValue : "YES",
					itemId : "IsMain"
				},{
					xtype : "hidden",
					name : "UnitID",
					itemId : "UnitID"
				},{
					xtype : "hidden",
					name : "JobID",
					itemId : "JobID"
				}],
				buttons :[{
					text : "ذخیره",
					handler : function(){ UnitObject.SaveJob();},
					iconCls : "save"
				},{
					text : "انصراف",
					handler : function(){UnitObject.JobWin.hide();},
					iconCls : "undo"
				}]
			})
		});
	}
	
	var record = this.tree.getSelectionModel().getSelection()[0];
	this.JobWin.down('form').getForm().reset();

	this.JobWin.show();
	this.JobWin.down('form').getComponent("UnitID").setValue(record.data.id);
	
	if(mode == "edit")
	{
		this.JobWin.down('form').getComponent("JobID").setValue(record.raw.JobID);
		this.JobWin.down('form').getComponent("UnitID").setValue(record.raw.UnitID);
		this.JobWin.down('form').getComponent("PostID").setValue(record.raw.PostID);
		this.JobWin.down('form').getComponent("PersonID").setValue(record.raw.PersonID);
		this.JobWin.down('form').getComponent("IsMain").setValue(record.raw.IsMain);
	}
}

Unit.prototype.SaveJob = function(){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	this.JobWin.down('form').getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'baseInfo.data.php?task=SaveJob',
		method : "POST",

		success : function(form,action){                

			UnitObject.tree.getStore().load({
				callback : function(){
					node = UnitObject.tree.getRootNode().findChild("id","p_" + action.result.data,true);
					UnitObject.tree.getSelectionModel().select(node);
					UnitObject.tree.getView().ensureVisible(node);
				}
			});
			UnitObject.JobWin.down('form').getForm().reset();
			UnitObject.JobWin.hide();
			mask.hide();		
			
		},
		failure : function(form,action)
		{
			Ext.MessageBox.alert('Error', action.result.data);
			mask.hide();
		}
	});
}

Unit.prototype.DeleteJob = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		var record = UnitObject.tree.getSelectionModel().getSelection()[0];
		mask = new Ext.LoadMask(UnitObject.tree, {msg:'در حال ذخيره سازي...'});
		mask.show();
		
		Ext.Ajax.request({
			url : UnitObject.address_prefix + 'baseInfo.data.php?task=DeleteJob',
			method : 'POST',
			params :{
				JobID : record.data.id
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
	});

}

</script>