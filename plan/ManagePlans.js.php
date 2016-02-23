<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

ManagePlan.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManagePlan(){

}

ManagePlanObject = new ManagePlan();

ManagePlan.prototype.ManagePlan = function(){
	if(this.get("new_pass").value != this.get("new_pass2").value)
	{
		return;
	}
}

ManagePlan.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='ManagePlanObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

ManagePlan.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	if(record.data.StatusID == "1" && record.data.ReqPersonRole == "Staff")
	{
		op_menu.add({text: 'حذف درخواست',iconCls: 'remove', 
		handler : function(){ return ManagePlanObject.deleteRequest(); }});
	}
	
	op_menu.add({text: 'مدارک وام',iconCls: 'attach', 
		handler : function(){ return ManagePlanObject.LoanDocuments('loan'); }});

	op_menu.add({text: 'مدارک وام گیرنده',iconCls: 'attach', 
		handler : function(){ return ManagePlanObject.LoanDocuments('person'); }});
	
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return ManagePlanObject.ShowHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

ManagePlan.prototype.LoanDocuments = function(ObjectType){

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

ManagePlan.prototype.ShowHistory = function(){

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

ManagePlan.prototype.deleteRequest = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به حذف درخواست می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = ManagePlanObject;
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
				ManagePlanObject.grid.getStore().load();
				if(ManagePlanObject.commentWin)
					ManagePlanObject.commentWin.hide();
				ManagePlanObject.LoanInfoPanel.hide();
			}
		});
	});
	
	
}


</script>