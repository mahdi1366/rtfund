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
			}]
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
	
	if(record.data.StatusID == "1")
	{
		op_menu.add({text: 'حذف درخواست',iconCls: 'remove', 
		handler : function(){ return ManageRequestObject.deleteRequest(); }});
	}
	
	op_menu.add({text: 'مدارک وام',iconCls: 'attach', 
		handler : function(){ return ManageRequestObject.LoanDocuments('loan'); }});

	op_menu.add({text: 'مدارک وام گیرنده',iconCls: 'attach', 
		handler : function(){ return ManageRequestObject.LoanDocuments('person'); }});
	
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return ManageRequestObject.ShowHistory(); }});
	
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
			}
		});
	});
	
	
}


</script>