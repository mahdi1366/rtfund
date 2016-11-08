<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

VOT_Form.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function VOT_Form()
{
	this.ItemTypeCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields: ["id", "name"],
			data: [
				{"id": "numberfield", "name": "عدد"},
				{"id": "currencyfield", "name": "مبلغ"},
				{"id": "textfield", "name": "متن کوتاه"},
				{"id": "textarea", "name": "متن بلند"},
				{"id": "shdatefield", "name": "تاریخ"},
				{"id": "combo", "name": "لیستی"},
				{"id": "displayfield", "name": "نمایشی"},
				{"id": "radio", "name": "گزینه ایی"},
			]
		}),
		emptyText: 'انتخاب ...',
		name: "name",
		valueField: "id",
		displayField: "name",
		allowBlank : false
	});
	
	this.FormGroupCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields: ["GroupID", "GroupDesc"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "vote.data.php?task=SelectGroups",
				reader: {root: 'rows',totalProperty: 'totalCount'}
			}
		}),
		valueField: "GroupID",
		displayField: "GroupDesc",
		allowBlank : false
	});
}

VOT_Form.deleteRender = function(v,p,r)
{
	return "<div align='center' title='حذف ' class='remove' onclick='VOT_FormObject.Deleting();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VOT_Form.previewRender = function(v,p,r)
{
	return "<div align='center' title='پیش نمایش ' class='view' onclick='VOT_FormObject.PreviewForm();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VOT_Form.ItemsRender = function(v,p,r)
{
	return "<div align='center' title='آیتم ها' class='list' onclick='VOT_FormObject.ShowItems();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:16px;height:16'></div>";
}

VOT_Form.prototype.Adding = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		FormID : "",
		FormTitle : ""
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

VOT_Form.prototype.saveData = function(store,record)
{
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveForm',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'vote.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				VOT_FormObject.grid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

VOT_Form.prototype.Deleting = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	
	Ext.MessageBox.confirm("","آيا مايل به حذف مي باشيد؟", function(btn){
		
		if(btn == "no")
			return;
		me = VOT_FormObject;
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخیره سازی ...'});
		mask.show();
	
		Ext.Ajax.request({
		  	url : me.address_prefix + "vote.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteForm",
		  		FormID : record.data.FormID
		  	},
		  	success : function(response,o)
		  	{
				me.hide();
		  		VOT_FormObject.grid.getStore().load();
		  	}
		});
	})

}

VOT_Form.prototype.ShowItems = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	this.ItemsGrid.getStore().proxy.extraParams.FormID = record.data.FormID;
	this.GroupGrid.getStore().proxy.extraParams.FormID = record.data.FormID;
	
	if(!this.ItemsWin)
	{
		this.ItemsWin = new Ext.window.Window({
			width : 800,
			title : "آیتم های فرم",
			bodyStyle : "background-color:white;text-align:-moz-center",
			height : 600,
			modal : true,
			closeAction : "hide",
			items : [this.GroupGrid,this.ItemsGrid],
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.ItemsWin);
	}
	else
		this.ItemsGrid.getStore().load();

	this.ItemsWin.show();
	this.ItemsWin.center();
}

VOT_Form.prototype.PreviewForm = function(){
	
	if(!this.PreviewWin)
	{
		this.PreviewWin = new Ext.window.Window({
			width : 700,
			title : "پیش نمایش",
			height : 500,
			autoScroll : true,
			bodyStyle : "background-color:white",
			modal : true,
			closeAction : "hide",
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.PreviewWin);
		
		this.ItemsStore = new Ext.data.Store({
			fields: ['ItemID','ItemType',"ItemTitle", 'ItemValues', 'GroupID', 'GroupDesc'],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + "vote.data.php?task=SelectItems",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			}
		});
	}
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	this.PreviewWin.show();
	this.ItemsStore.load({
		params : {
			FormID : record.data.FormID
		},
		callback : function(){
			VOT_FormObject.PreviewWin.removeAll();
			
			var CurGroupID = 0;
			var parent = null;
			for(i=0; i<this.getCount(); i++)
			{
				record = this.getAt(i);
				if(CurGroupID != record.data.GroupID)
				{
					VOT_FormObject.PreviewWin.add({
						xtype : "fieldset",
						title : record.data.GroupDesc,
						itemId : "Group_" + record.data.GroupID,
						layout : {
							type : "table",
							columns : 2
						}
					});
					parent = VOT_FormObject.PreviewWin.down("[itemId=Group_" + record.data.GroupID + "]");
					CurGroupID = record.data.GroupID;
				}
				if(record.data.ItemType == "combo")
				{
					arr = record.data.ItemValues.split("#");
					data = [];
					for(j=0;j<arr.length;j++)
						data.push([ arr[j] ]);
					
					parent.add({
						store : new Ext.data.SimpleStore({
							fields : ['value'],
							data : data
						}),
						xtype: record.data.ItemType,
						valueField : "value",
						displayField : "value",
						fieldLabel : record.data.ItemTitle,
						colspan : 2
					});
				}
				else if(record.data.ItemType == "radio")
				{
					parent.add({
						xtype : "displayfield",
						width : 400,
						value : record.data.ItemTitle
					});
					var items = new Array();
					arr = record.data.ItemValues.split("#");
					for(j=0; j<arr.length; j++)
						items.push({
							boxLabel : arr[j],
							name : "radio_" + record.data.ItemID,
							width : 100
						});
					parent.add({
						xtype : "radiogroup",
						items : items
					});
				}
				else
				{
					if(record.data.ItemType == "textarea")
					{
						parent.add({
							xtype : "displayfield",
							value : record.data.ItemTitle,
							colspan : 2,
							width : 650
						});
					}
					parent.add({
						xtype: record.data.ItemType,
						fieldLabel : record.data.ItemTitle,
						hideTrigger : record.data.ItemType == 'numberfield' || 
							record.data.ItemType == 'currencyfield' ? true : false,
						value : record.data.ItemValues,
						colspan : 2,
						width : 650
					});
				}
			}
		}
	});
	
	
}

//----------------------------------------------------------

VOT_Form.deleteItemRender = function(v,p,r)
{
	return "<div align='center' title='حذف ' class='remove' onclick='VOT_FormObject.DeleteItem();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VOT_Form.upRender = function(v,p,r)
{
	if(r.data.ordering == 1)
		return "";
	return "<div align='center' title='up' class='up' onclick='VOT_FormObject.moveItem(-1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VOT_Form.downRender = function(v,p,r)
{
	store = VOT_FormObject.ItemsGrid.getStore();
	record = store.getAt(store.getCount()-1);
	if(r.data.ordering == record.data.ordering)
		return "";
	return "<div align='center' title='down' class='down' onclick='VOT_FormObject.moveItem(1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VOT_Form.prototype.AddItem = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	
	var modelClass = this.ItemsGrid.getStore().model;
	var record = new modelClass({
		FormID : record.data.FormID,
		ItemID : "",
		ItemTitle : ""
	});

	this.ItemsGrid.plugins[0].cancelEdit();
	this.ItemsGrid.getStore().insert(0, record);
	this.ItemsGrid.plugins[0].startEdit(0, 0);
}

VOT_Form.prototype.saveItem = function(store,record)
{
    mask = new Ext.LoadMask(this.ItemsWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveItem',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'vote.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				VOT_FormObject.ItemsGrid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

VOT_Form.prototype.moveItem = function(direction)
{
	var record = this.ItemsGrid.getSelectionModel().getLastSelected();
	
    mask = new Ext.LoadMask(this.ItemsWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'MoveItem',
			FormID : record.data.FormID,
			ItemID : record.data.ItemID,
			ordering : record.data.ordering,
			direction : direction
		},
		url: this.address_prefix + 'vote.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				VOT_FormObject.ItemsGrid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

VOT_Form.prototype.DeleteItem = function()
{
	var record = this.ItemsGrid.getSelectionModel().getLastSelected();
	Ext.MessageBox.confirm("", "آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		me = VOT_FormObject;
		
		Ext.Ajax.request({
		  	url : me.address_prefix + "vote.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteItem",
		  		ItemID : record.data.ItemID
		  	},
		  	success : function(response)
		  	{
				result = Ext.decode(response.responseText);
				if(result.success)
					VOT_FormObject.ItemsGrid.getStore().load();
				else
					Ext.MessageBox.alert("Error", "عملیات مورد نظر با شکست مواجه شد");
					
		  	}
		});
	});
}

//----------------------------------------------------------

VOT_Form.deleteGroupRender = function(v,p,r)
{
	return "<div align='center' title='حذف ' class='remove' onclick='VOT_FormObject.DeleteGroup();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VOT_Form.GroupupRender = function(v,p,r)
{
	if(r.data.ordering == 1)
		return "";
	return "<div align='center' title='up' class='up' onclick='VOT_FormObject.moveGroup(-1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VOT_Form.GroupdownRender = function(v,p,r)
{
	store = VOT_FormObject.GroupGrid.getStore();
	record = store.getAt(store.getCount()-1);
	if(r.data.ordering == record.data.ordering)
		return "";
	return "<div align='center' title='down' class='down' onclick='VOT_FormObject.moveGroup(1);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

VOT_Form.prototype.AddGroup = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	
	var modelClass = this.GroupGrid.getStore().model;
	var record = new modelClass({
		FormID : record.data.FormID,
		GroupID : "",
		GroupDesc : ""
	});

	this.GroupGrid.plugins[0].cancelEdit();
	this.GroupGrid.getStore().insert(0, record);
	this.GroupGrid.plugins[0].startEdit(0, 0);
}

VOT_Form.prototype.saveGroup = function(store,record)
{
    mask = new Ext.LoadMask(this.ItemsWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveGroup',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'vote.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				VOT_FormObject.GroupGrid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

VOT_Form.prototype.moveGroup = function(direction)
{
	var record = this.GroupGrid.getSelectionModel().getLastSelected();
	
    mask = new Ext.LoadMask(this.ItemsWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'MoveGroup',
			FormID : record.data.FormID,
			GroupID : record.data.GroupID,
			ordering : record.data.ordering,
			direction : direction
		},
		url: this.address_prefix + 'vote.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				VOT_FormObject.GroupGrid.getStore().load();
			}
			else
			{
				Ext.MessageBox.alert("Error",st.data);
			}
		},
		failure: function(){}
	});
}

VOT_Form.prototype.DeleteGroup = function()
{
	var record = this.GroupGrid.getSelectionModel().getLastSelected();
	
	Ext.MessageBox.confirm("", "آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		me = VOT_FormObject;
		
		Ext.Ajax.request({
		  	url : me.address_prefix + "vote.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteGroup",
		  		ItemID : record.data.GroupID
		  	},
		  	success : function(response)
		  	{
				result = Ext.decode(response.responseText);
				if(result.success)
					VOT_FormObject.GroupGrid.getStore().load();
				else
					Ext.MessageBox.alert("Error", "عملیات مورد نظر با شکست مواجه شد");
					
		  	}
		});
	});
}

</script>
