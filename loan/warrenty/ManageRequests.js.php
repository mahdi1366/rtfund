<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

WarrentyRequest.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function WarrentyRequest(){
	
	this.FilterObj = Ext.button.Button({
		text: 'فیلتر لیست',
		iconCls: 'list',
		menu: {
			xtype: 'menu',
			plain: true,
			showSeparator : true,
			items: [{
				text: "کلیه درخواست ها",
				checked: true,
				group: 'filter',
				handler : function(){
					me = WarrentyRequestObject;
					me.grid.getStore().proxy.extraParams["IsEnded"] = "";
					me.grid.getStore().loadPage(1);
				}
			},{
				text: "درخواست های جاری",
				group: 'filter',
				checked: true,
				handler : function(){
					me = WarrentyRequestObject;
					me.grid.getStore().proxy.extraParams["IsEnded"] = "NO";
					me.grid.getStore().loadPage(1);
				}
			},{
				text: "درخواست های خاتمه یافته",
				group: 'filter',
				checked: true,
				handler : function(){
					me = WarrentyRequestObject;
					me.grid.getStore().proxy.extraParams["IsEnded"] = "YES";
					me.grid.getStore().loadPage(1);
				}
			}]
		}
	});
	
	this.MainPanel = new Ext.form.Panel({
		width : 400,
		hidden : true,
		applyTo : this.get("RequestInfo"),
		defaults : {
			width : 300
		},
		frame : true,
		items : [{
			xtype : "combo",
			store : new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'request.data.php?task=GetWarrentyTypes',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ["InfoID", "InfoDesc"]
			}),
			displayField: 'InfoDesc',
			valueField : "InfoID",
			name : "TypeID",
			allowBlank : false,
			fieldLabel : "نوع ضمانت نامه"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/person/persons.data.php?' +
						"task=selectPersons&UserType=IsCustomer",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['PersonID','fullname']
			}),
			fieldLabel : "مشتری",
			displayField : "fullname",
			pageSize : 20,
			allowBlank : false,
			valueField : "PersonID",
			name : "PersonID"
		},{
			xtype : "textfield",
			name : "organization",
			allowBlank : false,
			fieldLabel : "سازمان مربوطه"
		},{
			xtype : "currencyfield",
			name : "amount",
			hideTrigger : true,
			allowBlank : false,
			fieldLabel : "مبلغ ضمانت نامه"
		},{
			xtype : "hidden",
			name : "RequestID"
		}],
		buttons :[{
			text : "ذخیره",
			iconCls : "save",
			handler : function(){ WarrentyRequestObject.SaveRequest(); }
		},{
			text : "انصراف",
			iconCls : "undo",
			handler : function(){ this.up('panel').hide(); }
		}]
	});
}

WarrentyRequestObject = new WarrentyRequest();

WarrentyRequest.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='WarrentyRequestObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

WarrentyRequest.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	if(record.data.StatusID == "1")
	{
		op_menu.add({text: 'حذف درخواست',iconCls: 'remove', 
		handler : function(){ return WarrentyRequestObject.deleteRequest(); }});
	}
	
	op_menu.add({text: 'دوره های زمانی ضمانت نامه',iconCls: 'delay', 
		handler : function(){ return WarrentyRequestObject.WarrentyPeriods(); }});
	
	op_menu.add({text: 'مدارک تضمین',iconCls: 'attach', 
		handler : function(){ return WarrentyRequestObject.WarrentyDocuments('warrenty'); }});

	op_menu.add({text: 'مدارک تضمین خواه',iconCls: 'attach', 
		handler : function(){ return WarrentyRequestObject.WarrentyDocuments('person'); }});
	
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return WarrentyRequestObject.ShowHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

WarrentyRequest.prototype.AddNew = function(){
	
	this.MainPanel.show();
	this.MainPanel.getForm().reset();
}

WarrentyRequest.prototype.SaveRequest = function(){
	
	if(!this.MainPanel.getForm().isValid())
		return;
	
	mask = new Ext.LoadMask(this.MainPanel, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	this.MainPanel.getForm().submit({
		clientValidation: true,
		url: this.address_prefix +'request.data.php',
		method: "POST",
		params: {
			task: "SaveRequest"
		},
		success: function(form,action){
			mask.hide();
			WarrentyRequestObject.grid.getStore().load();
			WarrentyRequest.MainPanel.hide();
		},
		failure: function(){
			mask.hide();
		}
	});
}

WarrentyRequest.prototype.WarrentyDocuments = function(ObjectType){

	if(!this.documentWin)
	{
		this.documentWin = new Ext.window.Window({
			width : 720,
			height : 440,
			modal : true,
			bodyStyle : "background-color:white;padding: 0 10px 0 10px",
			closeAction : "hide",
			loader : {
				url : "../../office/dms/documents.php",
				scripts : true
			},
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.documentWin);
	}

	this.documentWin.show();
	this.documentWin.center();
	
	var record = this.grid.getSelectionModel().getLastSelected();
	this.documentWin.loader.load({
		scripts : true,
		params : {
			ExtTabID : this.documentWin.getEl().id,
			ObjectType : ObjectType,
			ObjectID : record.data.RequestID
		}
	});
}

WarrentyRequest.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش درخواست',
			modal : true,
			autoScroll : true,
			width: 700,
			height : 500,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "history.php",
				scripts : true
			},
			buttons : [{
					text : "بازگشت",
					iconCls : "undo",
					handler : function(){
						this.up('window').hide();
					}
				}]
		});
		Ext.getCmp(this.TabID).add(this.HistoryWin);
	}
	this.HistoryWin.show();
	this.HistoryWin.center();
	this.HistoryWin.loader.load({
		params : {
			RequestID : this.grid.getSelectionModel().getLastSelected().data.RequestID
		}
	});
}

WarrentyRequest.prototype.deleteRequest = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف درخواست می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = WarrentyRequestObject;
		record = me.grid.getSelectionModel().getLastSelected();
		
		mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخيره سازي...'});
		mask.show();  

		Ext.Ajax.request({
			methos : "post",
			url : me.address_prefix + "request.data.php",
			params : {
				task : "DeleteRequest",
				RequestID : record.data.RequestID
			},

			success : function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
				{
					WarrentyRequestObject.grid.getStore().load();
					if(WarrentyRequestObject.commentWin)
						WarrentyRequestObject.commentWin.hide();
				}
				else
					Ext.MessageBox.alert("Error",result.data);
			}
		});
	});
}

WarrentyRequest.prototype.WarrentyPeriods = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		Ext.MessageBox.alert("","ابتدا فاز مورد نظر خود را انتخاب کنید");
		return;
	}
	
	if(!this.PeriodWin)
	{
		this.PeriodWin = new Ext.window.Window({
			width : 600,
			title : "دوره های زمانی ضمانت نامه",
			height : 300,
			modal : true,
			loader : {
				url : this.address_prefix + "periods.php",
				method : "post",
				scripts : true
			},
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){ this.up('window').hide(); }
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.PeriodWin);
	}
	this.PeriodWin.show();
	this.PeriodWin.center();
	
	this.PeriodWin.loader.load({
		params : {
			ExtTabID : this.PeriodWin.getEl().id,
			RequestID : record.data.RequestID
		}
	});
}

</script>