<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

ManageRequest.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	MenuID : "<?= $_POST["MenuID"] ?>",
	
	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManageRequest(){
	
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
					me = ManageRequestObject;
					me.grid.getStore().proxy.extraParams["IsEnded"] = "";
					me.grid.getStore().loadPage(1);
				}
			},{
				text: "درخواست های جاری",
				group: 'filter',
				checked: true,
				handler : function(){
					me = ManageRequestObject;
					me.grid.getStore().proxy.extraParams["IsEnded"] = "NO";
					me.grid.getStore().loadPage(1);
				}
			},{
				text: "درخواست های خاتمه یافته",
				group: 'filter',
				checked: true,
				handler : function(){
					me = ManageRequestObject;
					me.grid.getStore().proxy.extraParams["IsEnded"] = "YES";
					me.grid.getStore().loadPage(1);
				}
			},{
				text: "درخواست های تایید شده",
				group: 'filter',
				checked: true,
				handler : function(){
					me = ManageRequestObject;
					me.grid.getStore().proxy.extraParams["IsConfirm"] = "YES";
					me.grid.getStore().loadPage(1);
				}
			},{
				text: "درخواست های تایید نشده",
				group: 'filter',
				checked: true,
				handler : function(){
					me = ManageRequestObject;
					me.grid.getStore().proxy.extraParams["IsConfirm"] = "NO";
					me.grid.getStore().loadPage(1);
				}
			}]
		}
	});
}

ManageRequestObject = new ManageRequest();

ManageRequest.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='ManageRequestObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

ManageRequest.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	if(record.data.StatusID == "1")
	{
		if(this.RemoveAccess)
			op_menu.add({text: 'حذف درخواست',iconCls: 'remove', 
			handler : function(){ return ManageRequestObject.deleteRequest(); }});
	}
	
	op_menu.add({text: 'مدارک وام',iconCls: 'attach', 
		handler : function(){ return ManageRequestObject.LoanDocuments('loan'); }});

	op_menu.add({text: 'مدارک وام گیرنده',iconCls: 'attach', 
		handler : function(){ return ManageRequestObject.LoanDocuments('person'); }});
	
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return ManageRequestObject.ShowHistory(); }});
	
	op_menu.add({text: 'رویدادها',iconCls: 'task', 
		handler : function(){ return ManageRequestObject.ShowEvents(); }});
	
	op_menu.add({text: 'چاپ رسید اقساط',iconCls: 'print', 
		handler : function(){ 
			record = ManageRequestObject.grid.getSelectionModel().getLastSelected();
			window.open(ManageRequestObject.address_prefix + "PrintLoanDocs.php?type=checks&RequestID=" +
				record.data.RequestID);	
	}});
	op_menu.add({text: 'چاپ رسید تضامین',iconCls: 'print', 
		handler : function(){ 
			record = ManageRequestObject.grid.getSelectionModel().getLastSelected();
			window.open(ManageRequestObject.address_prefix + "PrintLoanDocs.php?type=tazmin&RequestID=" +
				record.data.RequestID);	
	}});
	
	
	op_menu.showAt(e.pageX-120, e.pageY);
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

			success : function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
				{
					ManageRequestObject.grid.getStore().load();
					if(ManageRequestObject.commentWin)
						ManageRequestObject.commentWin.hide();
				}
				else
					Ext.MessageBox.alert("Error",result.data);
			}
		});
	});
	
	
}

ManageRequest.prototype.Confirm = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
		return;
	Ext.MessageBox.confirm("","آیا مایل به تایید می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = ManageRequestObject;
		
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			params: {
				task: 'ConfirmRequest',
				RequestID : record.data.RequestID
			},
			url: me.address_prefix +'request.data.php',
			method: 'POST',

			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.success)
				{
					ManageRequestObject.grid.getStore().load();
				}
				else
				{
					alert(st.data);
				}
			},
			failure: function(){}
		});
		
	});
}

ManageRequest.prototype.ShowEvents = function(){

	if(!this.EventsWin)
	{
		this.EventsWin = new Ext.window.Window({
			title: 'رویدادهای مرتبط با طرح',
			modal : true,
			autoScroll : true,
			width: 600,
			height : 400,
			bodyStyle : "background-color:white",
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "events.php",
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
		Ext.getCmp(this.TabID).add(this.EventsWin);
	}
	this.EventsWin.show();
	this.EventsWin.center();	
	record = this.grid.getSelectionModel().getLastSelected();
	this.EventsWin.loader.load({
		params : {
			ExtTabID : this.EventsWin.getEl().id,
			RequestID : record.data.RequestID,
			MenuID : this.MenuID
		}
	});
}

</script>