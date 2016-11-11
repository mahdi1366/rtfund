<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

NTC_Operation.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function NTC_Operation(){
		
	this.MainPanel = new Ext.form.Panel({
		width : 750,
		hidden : true,
		layout : {
			type : "table",
			columns : 2
		},		
		applyTo : this.get("operationInfo"),
		defaults : {
			width : 350
		},
		frame : true,
		items : [{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				fields : ['id','title'],
				data : [
					['SMS', 'SMS'],
					['EMAIL', 'EMAIL'],
					['LETTER', 'LETTER']
				]				
			}),
			displayField : "title",
			valueField : "id",
			fieldLabel : "نوع ارسال",
			name : "SendType",
			listeners : {
				select : function(){
					me = NTC_OperationObject;
					me.MainPanel.down("[itemId=templates]").getStore().proxy.extraParams.SendType = 
						this.getValue();
					me.MainPanel.down("[itemId=templates]").getStore().load();
					me.MainPanel.down("[itemId=AddToTemplates]").enable();
				}
			}
		},{
			xtype : "textfield",
			name : "title",
			fieldLabel : "شرح"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + 'operation.data.php?task=SelectTemplates',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['TemplateID','TemplateTitle','context']
			}),
			queryMode : 'local',
			fieldLabel : "الگو",
			itemId : "templates",
			displayField : "TemplateTitle",
			listeners :{
				select : function(store,records){
					CKEDITOR.instances.NoticeEditor.setData(records[0].data.context);
				}
			}
		},{
			xtype : "filefield",
			name : "PersonFile",
			allowBlank : false,
			fieldLabel : "فایل افراد"
		},{
			xtype : "container",
			colspan : 2,
			width : 730,
			html : "<div id='NoticeEditor'></div>"
		}],
		buttons :[{
			text : "اضافه متن به الگوها",
			iconCls : "add",
			itemId : "AddToTemplates",
			disabled : true,
			handler : function(){ NTC_OperationObject.AddToTemplates() }
		},'->',{
			text : "ارسال",
			iconCls : "send",
			handler : function(){ NTC_OperationObject.Saveoperation(); }
		},{
			text : "انصراف",
			iconCls : "undo",
			handler : function(){ this.up('panel').hide(); }
		}]
	});
	
	if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
		CKEDITOR.tools.enableHtml5Elements( document );

	CKEDITOR.config.width = 'auto';
	CKEDITOR.config.height = 300;
	CKEDITOR.config.width = 710;
	CKEDITOR.config.autoGrow_minHeight = 170;
	CKEDITOR.replace('NoticeEditor');	
	CKEDITOR.add;
}

NTC_Operation.OperationRender = function(value, p, record){
	
	return "<div  title='لیست' class='list' onclick='NTC_OperationObject.ShowPersons();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

NTC_OperationObject = new NTC_Operation();

NTC_Operation.prototype.AddNew = function(){
	
	this.MainPanel.show();
	this.MainPanel.getForm().reset();
}

NTC_Operation.prototype.Saveoperation = function(){
	
	if(!this.MainPanel.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(this.MainPanel, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	this.MainPanel.getForm().submit({
		clientValidation: true,
		url: this.address_prefix +'operation.data.php',
		method: "POST",
		IsUpload : true,
		
		params: {
			task: "SaveOperation",
			context : CKEDITOR.instances.NoticeEditor.getData()
		},
		success: function(form,action){
			mask.hide();
			NTC_OperationObject.grid.getStore().load();
			NTC_OperationObject.MainPanel.hide();
		},
		failure: function(form,action){
			
			Ext.MessageBox.alert("Error", action.result.data);
			mask.hide();
		}
	});
}

NTC_Operation.prototype.deleteoperation = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف درخواست می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = NTC_OperationObject;
		record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخيره سازي...'});
		mask.show();  

		Ext.Ajax.operation({
			methos : "post",
			url : me.address_prefix + "operation.data.php",
			params : {
				task : "DeleteNTC_Operation",
				operationID : record.data.operationID
			},

			success : function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
				{
					NTC_OperationObject.grid.getStore().load();
					if(NTC_OperationObject.commentWin)
						NTC_OperationObject.commentWin.hide();
				}
				else
					Ext.MessageBox.alert("Error",result.data);
			}
		});
	});
}

NTC_Operation.prototype.ShowPersons = function(){

	record = this.grid.getSelectionModel().getLastSelected();
	this.PersonsGrid.getStore().proxy.extraParams.OperationID = record.data.OperationID;
	
	if(!this.PersonsWin)
	{
		this.PersonsWin = new Ext.window.Window({
			title: 'لیست افرادی که ارسال برای آنها انجام شده است',
			modal : true,
			autoScroll : true,
			width: 915,
			height : 565,
			bodyStyle : "background-color:white",
			closeAction : "hide",
			items : [this.PersonsGrid],
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){
					this.up('window').hide();
				}
			}]
		});
		Ext.getCmp(this.TabID).add(this.PersonsWin);
	}
	this.PersonsWin.show();
	this.PersonsWin.center();	
	this.PersonsGrid.getStore().load()
}

NTC_Operation.prototype.AddToTemplates = function(){
	
	if(!this.TemplatesWin)
	{
		this.TemplatesWin = new Ext.window.Window({
			width : 500,			
			height : 100,
			modal : true,
			bodyStyle : "background-color:white;",
			closeAction : "hide",
			items : [{
				xtype : "textfield",
				name : "TemplateTitle",
				emptyText : "عنوان الگو ...",
				width : 480
			}],
			buttons :[{
				text : "اضافه به الگو",
				iconCls  : "add",
				handler : function(){
					me = NTC_OperationObject;
					if(me.TemplatesWin.down("[name=TemplateTitle]").getValue() == "")
						return;
					Ext.Ajax.request({
						url : me.address_prefix + "operation.data.php?task=AddToTemplates",
						method : "post",
						params :{
							context : CKEDITOR.instances.NoticeEditor.getData(),
							TemplateTitle : me.TemplatesWin.down("[name=TemplateTitle]").getValue(),
							SendType : me.MainPanel.down("[name=SendType]").getValue()
						},
						success : function(){
							me.TemplatesWin.hide();
						}
					});
				}
			}]
		});
		Ext.getCmp(this.TabID).add(this.TemplatesWin);
	}

	this.TemplatesWin.show();
	this.TemplatesWin.center();
}

</script>