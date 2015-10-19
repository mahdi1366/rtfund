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
	
	if(record.data.StatusID == "10")
	{
		op_menu.add({text: 'جزئیات درخواست',iconCls: 'info2', 
		handler : function(){ return ManageRequestObject.LoanInfo(); }});
	
		op_menu.add({text: 'تایید درخواست',iconCls: 'tick', 
		handler : function(){ return ManageRequestObject.ChangeStatus("confirm"); }});
	
		op_menu.add({text: 'رد درخواست',iconCls: 'undo',
		handler : function(){ return ManageRequestObject.ChangeStatus("reject"); }});
	}
	op_menu.showAt(e.pageX-120, e.pageY);
}

ManageRequest.prototype.ChangeStatus = function(mode){
	
	
}

ManageRequest.prototype.LoanInfo = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	framework.OpenPage(this.address_prefix + "RequestInfo.php", "اطلاعات درخواست وام" , {
		RequestID : record.data.RequestID
	});
	
	return;
	
	
	
	var record = this.grid.getSelectionModel().getLastSelected();
	this.LoanInfoWin.down('form').loadRecord(record);
	this.LoanInfoWin.show();
	this.LoanInfoWin.center();
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

</script>