<script type="text/javascript">
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 95.01
//-----------------------------

ManageContracts.prototype = {
	TabID: '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix: "<?= $js_prefix_address ?>",
	ContractStatus_Raw: <?= CNTconfig::ContractStatus_Raw ?>,
	get: function (elementID) {
		return findChild(this.TabID, elementID);
	}
}

function ManageContracts() {

}

ManageContracts.prototype.OperationRender = function () {

	return  "<div title='عملیات' class='setting' onclick='ManageContractsObj.OperationMenu(event);' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;height:16'></div>";
}

ManageContracts.prototype.OperationMenu = function (e)
{
	var record = this.grid.getSelectionModel().getLastSelected();
	var op_menu = new Ext.menu.Menu();

	if(record.data.IsStarted == "NO" && record.data.IsEnded == "NO")
	{
		op_menu.add({text: 'شروع گردش فرم',iconCls: 'refresh',
		handler : function(){ return ManageContractsObj.StartFlow(); }});
		
		op_menu.add({text: ' ویرایش', iconCls: 'edit',
			handler: function () {ManageContractsObj.Edit(record.data.ContractID, record.data.TemplateID); }});

		op_menu.add({text: ' حذف', iconCls: 'remove',
			handler: function () {	ManageContractsObj.RemoveContract(record.data.ContractID);	}});
				
	}	
	
	op_menu.add({text: ' چاپ', iconCls: 'print',
		handler: function () {
			window.open(ManageContractsObj.address_prefix + 'PrintContract.php?ContractID=' + record.data.ContractID);
		}});

	op_menu.add({text: 'پیوست های قرارداد', iconCls: 'attach',
		handler: function () {
			ManageContractsObj.ContractDocuments('contract');
		}});
	
	op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return ManageContractsObj.ShowHistory(); }});

	op_menu.showAt(e.pageX - 120, e.pageY);
}

ManageContracts.prototype.Edit = function (ContractID, TemplateID)
{        
	framework.OpenPage(this.address_prefix + 'NewContract.php?ContractID=' + ContractID + '&TemplateID=' + TemplateID, 'ویرایش قرارداد');
}

ManageContracts.prototype.AddContract = function () {

	framework.OpenPage(this.address_prefix + "NewContract.php", "ثبت قرارداد");
}

ManageContracts.prototype.RemoveContract = function () {

	mask = new Ext.LoadMask(this.grid, {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
	url: this.address_prefix + 'contract.data.php?task=DeleteContract',
	params: {                
		ContractID: this.grid.getSelectionModel().getLastSelected().data.ContractID            
	},
	method: 'POST',
	success: function (res) {
		mask.hide();
		var sd = Ext.decode(res.responseText);
		if (!sd.success) {
			if (sd.data != '')
				Ext.MessageBox.alert('', sd.data); 
			else
				Ext.MessageBox.alert('', 'خطا در اجرای عملیات');
			return;
		}
		ManageContractsObj.grid.getStore().load();
	}
});
}

ManageContracts.prototype.ContractDocuments = function(ObjectType){

	if(!this.documentWin)
	{
		this.documentWin = new Ext.window.Window({
			width : 720,
			height : 440,
			modal : true,
			bodyStyle : "background-color:white;padding: 0 10px 0 10px",
			closeAction : "hide",
			loader : {
				url : "/office/dms/documents.php",
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
			ObjectID : record.data.ContractID
		}
	});
}

ManageContracts.prototype.StartFlow = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به شروع گردش قرارداد می باشید؟",function(btn){
		
		if(btn == "no")
			return;
		
		me = ManageContractsObj;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: '/office/workflow/wfm.data.php',
			method: "POST",
			params: {
				task: "StartFlow",
				FlowID : 2,
				ObjectID : record.data.ContractID
			},
			success: function(response){
				mask.hide();
				ManageContractsObj.grid.getStore().load();
			}
		});
	});
}

ManageContracts.prototype.ShowHistory = function(){

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
				url : "/office/workflow/history.php",
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
			FlowID : 2,
			ObjectID : this.grid.getSelectionModel().getLastSelected().data.ContractID
		}
	});
}

</script>