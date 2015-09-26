<script>
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------

Tafsili.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function Tafsili(){
	
	this.groupPnl = new Ext.form.Panel({
		renderTo: this.get("div_selectGroup"),
		title: "انتخاب گروه",
		width: 400,
		collapsible : true,
		collapsed : false,
		frame: true,
		bodyCfg: {style: "background-color:white"},
		items : [{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					proxy: {type: 'jsonp',
						url: this.address_prefix + 'baseinfo.data.php?task=SelectTafsiliGroups',
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					autoLoad : true,
					fields : ['InfoID','InfoDesc']
				}),
				valueField : "InfoID",
				queryMode : "local",
				name : "TafsiliType",
				displayField : "InfoDesc",
				fieldLabel : "انتخاب گروه"
			},{
				xtype : "fieldset",
				collapsible: true,
				itemId : "newGroup",
				collapsed : true,
				title : "ایجاد گروه جدید",
				width : 350,
				style : "background-color: #F2FCFF",
				items : [{
						xtype : "textfield",
						name : "GroupDesc",
						fieldLabel : "عنوان گروه"
					},{
						xtype : "button",
						text: "ایجاد گروه",
						handler: function(){

							var mask = new Ext.LoadMask(this.up('form'),{msg: 'تغییر اطلاعات ...'});
							mask.show();

							Ext.Ajax.request({
								method : "POST",
								url: TafsiliObject.address_prefix + "baseinfo.data.php",
								params: {
									task: "AddGroup",
									GroupDesc: this.up('form').down("[name=GroupDesc]").getValue()
								},
								success: function(response){
									mask.hide();
									TafsiliObject.groupPnl.down("[name=TafsiliType]").getStore().load({
										callback : function(){
											TafsiliObject.groupPnl.down("[name=TafsiliType]").setValue(
												this.getAt(this.getCount()-1));
											TafsiliObject.LoadTafsilis();
										}});
									TafsiliObject.groupPnl.down('fieldset').collapse();
								}
							});
						}
					}]
			}],
		buttons:[{
				text : "حذف گروه",
				itemId : "cmp_removeGroup",
				iconCls : "remove",
				handler : function(){
					TafsiliObject.DeleteGroup(this.up('form').down('[name=TafsiliType]').getValue());
				}
			},{
				text: "لیست تفصیلی ها",
				iconCls: "refresh",
				handler: function(){ TafsiliObject.LoadTafsilis(); }
			}]
	});	
	
	if(!this.AddAccess)
		this.groupPnl.down("[itemId=newGroup]").hide();
	if(!this.RemoveAccess)
		this.groupPnl.down("[itemId=cmp_removeGroup]").hide();
}

Tafsili.prototype.LoadTafsilis = function(){

	TafsiliObject.TafsiliType = this.groupPnl.down('[name=TafsiliType]').getValue();

	TafsiliObject.grid.getStore().proxy.extraParams.TafsiliType = TafsiliObject.TafsiliType;

	if(TafsiliObject.grid.rendered)
		TafsiliObject.grid.getStore().load();
	else
		TafsiliObject.grid.render(TafsiliObject.get("grid_div"));
	
	TafsiliObject.grid.show();
	TafsiliObject.groupPnl.collapse();
}

Tafsili.DeleteRender = function(v,p,r)
{
	return "<div align='center' title='حذف' class='remove' "+
		"onclick='TafsiliObject.DeleteTafsili();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

Tafsili.prototype.AddTafsili = function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		TafsiliID: null,
		TafsiliType : this.groupPnl.down("[name=TafsiliType]").getValue(),
		TafsiliCode: null
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

Tafsili.prototype.SaveTafsili = function(index){

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'baseinfo.data.php',
		method: "POST",
		params: {
			task: "SaveTafsili",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				TafsiliObject.grid.getStore().load();
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

Tafsili.prototype.DeleteGroup = function(TafsiliType)
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = TafsiliObject;
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'baseinfo.data.php',
			params:{
				task: "DeleteGroup",
				TafsiliType : TafsiliType
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				sd = Ext.decode(response.responseText);

				if(sd.success)
				{
					TafsiliObject.groupPnl.down('[name=TafsiliType]').setValue();
					TafsiliObject.groupPnl.down('[name=TafsiliType]').getStore().load();
					TafsiliObject.grid.hide();
				}	
				else
				{
					Ext.MessageBox.alert("Error","در این گروه وام تعریف شده و قادر به حذف آن نمی باشید");
				}
			},
			failure: function(){}
		});
	});
}

Tafsili.prototype.DeleteTafsili = function()
{
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = TafsiliObject;
		var record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال حذف ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'baseinfo.data.php',
			params:{
				task: "DeleteTafsili",
				TafsiliID : record.data.TafsiliID
			},
			method: 'POST',

			success: function(response,option){
				mask.hide();
				TafsiliObject.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

</script>