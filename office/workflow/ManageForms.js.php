<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.07
//-----------------------------

ManageForm.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManageForm(){
	
}

ManageFormObject = new ManageForm();

ManageForm.OperationRender = function(value, p, record){
	
	return "<div  title='عملیات' class='setting' onclick='ManageFormObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

ManageForm.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();
	
	op_menu.add({text: 'اطلاعات آیتم',iconCls: 'info2', 
		handler : function(){ return ManageFormObject.FormInfo(); }});
	
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return ManageFormObject.ShowHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

ManageForm.prototype.FormInfo = function(){
	
	if(!this.FormInfoWindow)
	{
		this.FormInfoWindow = new Ext.window.Window({
			width : 800,
			height : 400,
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
	
	this.FormInfoWindow.show();	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	eval("param = {ExtTabID : '" + this.FormInfoWindow.getEl().id + "'," + 
					record.data.parameter + " : '" + record.data.ObjectID + "'}");
	
	this.FormInfoWindow.loader.load({
		url : record.data.url,
		params : param
	}); 	
}

ManageForm.prototype.ShowHistory = function(){

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

</script>