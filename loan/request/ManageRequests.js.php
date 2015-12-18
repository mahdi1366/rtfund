<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

ManageRequest.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManageRequest(){
	
	this.LoanInfoPanel = new Ext.panel.Panel({
		renderTo : this.get("LoanInfo"),
		border : 0,
		hidden : true,
		loader : {
			url : this.address_prefix + "RequestInfo.php",
			scripts : true
		}
	});
}

ManageRequestObject = new ManageRequest();

ManageRequest.prototype.ManageRequest = function(){
	if(this.get("new_pass").value != this.get("new_pass2").value)
	{
		return;
	}
}

ManageRequest.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='ManageRequestObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

ManageRequest.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	if('<?= $_SESSION["USER"]["UserName"] ?>' == 'admin')
	{
		op_menu.add({text: 'تغییر وضعیت',iconCls: 'refresh', 
		handler : function(){ return ManageRequestObject.SetStatus(); }});
	}
	
	op_menu.add({text: 'جزئیات درخواست',iconCls: 'info2', 
		handler : function(){ return ManageRequestObject.LoanInfo(); }});
	
	if(record.data.StatusID == "1" && record.data.ReqPersonRole == "Staff")
	{
		op_menu.add({text: 'تایید درخواست',iconCls: 'tick', 
		handler : function(){ return ManageRequestObject.beforeChangeStatus(30); }});
		
		op_menu.add({text: 'حذف درخواست',iconCls: 'remove', 
		handler : function(){ return ManageRequestObject.deleteRequest(); }});
	}
	if(record.data.StatusID == "10")
	{
		op_menu.add({text: 'تایید درخواست',iconCls: 'tick', 
		handler : function(){ return ManageRequestObject.beforeChangeStatus(30); }});
	
		op_menu.add({text: 'رد درخواست',iconCls: 'cross',
		handler : function(){ return ManageRequestObject.beforeChangeStatus(20); }});
	}
	if(record.data.StatusID == "30" || record.data.StatusID == "35")
	{
		op_menu.add({text: 'ارسال به مشتری جهت تکمیل مدارک',iconCls: 'send',
		handler : function(){ return ManageRequestObject.beforeChangeStatus(40); }});
	}
	if(record.data.StatusID == "40")
	{
		op_menu.add({text: 'برگشت از مشتری',iconCls: 'back',
		handler : function(){ return ManageRequestObject.ChangeStatus(35, ""); }});
	}
	if(record.data.StatusID == "50")
	{
		op_menu.add({text: 'تایید مدارک مشتری',iconCls: 'tick',
		handler : function(){ return ManageRequestObject.beforeChangeStatus(70); }});
	
		op_menu.add({text: 'عدم تایید مدارک',iconCls: 'cross',
		handler : function(){ return ManageRequestObject.beforeChangeStatus(60); }});
	}
	if(record.data.StatusID == "70")
	{
	}
	if(new Array(50,60,70,80).indexOf(record.data.StatusID*1) != -1)
	{
		op_menu.add({text: 'مدارک وام',iconCls: 'attach', 
			handler : function(){ return ManageRequestObject.LoanDocuments('loan'); }});

		op_menu.add({text: 'مدارک وام گیرنده',iconCls: 'attach', 
			handler : function(){ return ManageRequestObject.LoanDocuments('person'); }});
	}
	
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return ManageRequestObject.ShowHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

ManageRequest.prototype.beforeChangeStatus = function(StatusID){
	
	if(new Array(20,60).indexOf(StatusID) == -1)
	{
		Ext.MessageBox.confirm("","آیا مایل به تایید می باشید؟", function(btn){
			if(btn == "no")
				return;
			
			ManageRequestObject.ChangeStatus (StatusID, "");
		});
		return;
	}
	if(!this.commentWin)
	{
		this.commentWin = new Ext.window.Window({
			width : 412,
			height : 198,
			modal : true,
			title : "دلیل رد مدرک برای درخواست کننده",
			bodyStyle : "background-color:white",
			items : [{
				xtype : "textarea",
				width : 400,
				rows : 8,
				name : "StepComment"
			}],
			closeAction : "hide",
			buttons : [{
				text : "اعمال",				
				iconCls : "save",
				itemId : "btn_save"
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.commentWin);
	}
	this.commentWin.down("[itemId=btn_save]").setHandler(function(){
		ManageRequestObject.ChangeStatus(StatusID, 
			this.up('window').down("[name=StepComment]").getValue());});
	this.commentWin.show();
	this.commentWin.center();
}

ManageRequest.prototype.ChangeStatus = function(StatusID, StepComment){
	
	record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	
	Ext.Ajax.request({
		methos : "post",
		url : this.address_prefix + "request.data.php",
		params : {
			task : "ChangeRequesrStatus",
			RequestID : record.data.RequestID,
			StatusID : StatusID,
			StepComment : StepComment
		},
		
		success : function(){
			mask.hide();
			ManageRequestObject.grid.getStore().load();
			if(ManageRequestObject.commentWin)
				ManageRequestObject.commentWin.hide();
			ManageRequestObject.LoanInfoPanel.hide();
		}
	});
}

ManageRequest.prototype.SetStatus = function(){
	
	if(!this.setStatusWin)
	{
		this.setStatusWin = new Ext.window.Window({
			width : 412,
			height : 198,
			modal : true,
			title : "تغییر وضعیت",
			defaults : {width : 380},
			bodyStyle : "background-color:white",
			items : [{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + "request.data.php?task=selectRequestStatuses",
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields : ['InfoID','InfoDesc'],
					autoLoad : true					
				}),
				fieldLabel : "وضعیت جدید",
				queryMode : 'local',
				allowBlank : false,
				displayField : "InfoDesc",
				valueField : "InfoID",
				itemId : "StatusID"
			},{
				xtype : "textarea",
				itemId : "comment",
				fieldLabel : "توضیحات"
			}],
			closeAction : "hide",
			buttons : [{
				text : "تغییر وضعیت",				
				iconCls : "save",
				itemId : "btn_save",
				handler : function(){
					status = this.up('window').getComponent("StatusID").getValue();
					comment = this.up('window').getComponent("comment").getValue();
					ManageRequestObject.ChangeStatus(status, "[تغییر وضعیت]" + comment);
					this.up('window').hide();
				}
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.setStatusWin);
	}
	this.setStatusWin.show();
	this.setStatusWin.center();
}

ManageRequest.prototype.LoanInfo = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	this.LoanInfoPanel.loader.load({
		params : {
			ExtTabID : this.LoanInfoPanel.getEl().id,
			RequestID : record.data.RequestID}
	});
	this.LoanInfoPanel.show();	
	return;
	
	framework.OpenPage(this.address_prefix + "RequestInfo.php", "اطلاعات درخواست وام" , {
		RequestID : record.data.RequestID
	});
	
	return;
}

ManageRequest.prototype.SaveLoanRequest = function(){
	
	mask = new Ext.LoadMask(this.LoanInfoWin, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	this.LoanInfoWin.down('form').getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'request.data.php?task=SaveLoanRequest' , 
		method: "POST",
		params : {
			RequestID : this.grid.getSelectionModel().getLastSelected().data.RequestID
		},
		
		success : function(form,action){
			mask.hide();
			ManageRequestObject.LoanInfoWin.hide();
			ManageRequestObject.grid.getStore().load();
		},
		failure : function(){
			mask.hide();
			//Ext.thisssageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
		}
	});
}

ManageRequest.prototype.LoanDocuments = function(ObjectType){

	if(!this.documentWin)
	{
		this.documentWin = new Ext.window.Window({
			width : 720,
			height : 440,
			modal : true,
			bodyStyle : "background-color:white;padding: 0 10px 0 10px",
			closeAction : "hide",
			loader : {
				url : "../../dms/documents.php",
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
			ObjectID : ObjectType == "loan" ? record.data.RequestID : record.data.LoanPersonID
		}
	});
}

ManageRequest.prototype.ShowHistory = function(){

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

ManageRequest.prototype.deleteRequest = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف درخواست می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = ManageRequestObject;
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

			success : function(){
				mask.hide();
				ManageRequestObject.grid.getStore().load();
				if(ManageRequestObject.commentWin)
					ManageRequestObject.commentWin.hide();
				ManageRequestObject.LoanInfoPanel.hide();
			}
		});
	});
	
	
}


</script>