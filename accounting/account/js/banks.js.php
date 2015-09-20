<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

Bank.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Bank()
{
	this.form = this.get("mainForm");
}

var BankObject = new Bank();

Bank.deleteRender = function()
{
	return  "<div title='حذف اطلاعات' class='remove' onclick='BankObject.remove();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;height:16'></div>";
}

Bank.prototype.Add = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		BankID: "",
		title: null
		
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

Bank.prototype.Save = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/banks.data.php?task=saveBank',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},
		form : this.get("BankForm"),

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				BankObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

Bank.prototype.remove = function()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/banks.data.php?task=removeBank',
		params:{
			bankID: record.data.bankID
		},
		method: 'POST',

		success: function(response){
			mask.hide();
			if(response.responseText == "conflict")
				alert('این آیتم در جای دیگری استفاده شده و قابل حذف نمی باشد.');
			else
				BankObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

</script>