<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

MyForm.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function MyForm(){
	
}

MyFormObject = new MyForm();

MyForm.prototype.MyForm = function(){
	if(this.get("new_pass").value != this.get("new_pass2").value)
	{
		return;
	}
}

MyForm.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='MyFormObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

MyForm.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	op_menu.add({text: 'اطلاعات آیتم',iconCls: 'info2', 
		handler : function(){ return MyFormObject.FormInfo(); }});
	
	op_menu.add({text: 'تایید درخواست',iconCls: 'tick', 
	handler : function(){ return MyFormObject.beforeChangeStatus('CONFIRM'); }});

	op_menu.add({text: 'رد درخواست',iconCls: 'cross',
	handler : function(){ return MyFormObject.beforeChangeStatus('REJECT'); }});
	
	op_menu.add({text: 'پیوستها',iconCls: 'attach', 
		handler : function(){ return MyFormObject.ShowAttaches(); }});
	
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return MyFormObject.ShowHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

MyForm.prototype.beforeChangeStatus = function(mode){
	
	if(mode == "CONFIRM")
	{
		Ext.MessageBox.confirm("","آیا مایل به تایید می باشید؟", function(btn){
			if(btn == "no")
				return;
			
			MyFormObject.ChangeStatus(mode, "");
		});
		return;
	}
	if(!this.commentWin)
	{
		this.commentWin = new Ext.window.Window({
			width : 412,
			height : 320,
			modal : true,
			title : "دلیل عدم تایید",
			bodyStyle : "background-color:white",
			items : [{
				xtype : "textarea",
				width : 400,
				rows : 8,
				name : "ActionComment"
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
		MyFormObject.ChangeStatus(mode, this.up('window').down("[name=ActionComment]").getValue());});
	this.commentWin.show();
	this.commentWin.center();
}

MyForm.prototype.ChangeStatus = function(mode, ActionComment){
	
	record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	
	Ext.Ajax.request({
		methos : "post",
		url : this.address_prefix + "wfm.data.php",
		params : {
			task : "ChangeStatus",
			RowID : record.data.RowID,
			mode : mode,
			ActionComment : ActionComment
		},
		
		success : function(response){
			mask.hide();
			
			result = Ext.decode(response.responseText);
			if(!result.success)
			{
				Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
			}
			
			MyFormObject.grid.getStore().load();
			if(MyFormObject.commentWin)
				MyFormObject.commentWin.hide();
		}
	});
}

MyForm.prototype.FormInfo = function(){
	
	if(!this.FormInfoWindow)
	{
		this.FormInfoWindow = new Ext.window.Window({
			width : 800,
			height : 660,
			autoScroll : true,
			modal : true,
			title : "اطلاعات فرم مربوطه",
			bodyStyle : "background-color:white",
			loader : {
				scripts : true
			},
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
	}
	var record = this.grid.getSelectionModel().getLastSelected();
	if(record.data.target == "1")
	{
		window.open(record.data.url + "?" + 
			record.data.parameter + "=" + record.data.ObjectID );
		return;
	}
	this.FormInfoWindow.show();	
	
	eval("param = {ExtTabID : '" + this.FormInfoWindow.getEl().id + "',ReadOnly : true," + 
					record.data.parameter + " : '" + record.data.ObjectID + "'}");
	
	this.FormInfoWindow.loader.load({
		url : record.data.url,
		params : param
	}); 	
}

MyForm.prototype.SaveLoanRequest = function(){
	
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
			MyFormObject.LoanInfoWin.hide();
			MyFormObject.grid.getStore().load();
		},
		failure : function(){
			mask.hide();
			//Ext.thisssageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
		}
	});
}

MyForm.prototype.LoanDocuments = function(ObjectType){

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
			ObjectID : ObjectType == "loan" ? record.data.RequestID : record.data.LoanPersonID
		}
	});
}

MyForm.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش',
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
			RowID : this.grid.getSelectionModel().getLastSelected().data.RowID
		}
	});
}

MyForm.prototype.ShowAttaches = function(){

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
			ObjectType : record.data.param4,
			ObjectID : record.data.ObjectID
		}
	});
}

</script>