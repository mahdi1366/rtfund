<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

Account.prototype={
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",
	mainTab : "",
	grid : "",
	AccGrid : "",
	CheqGrid : "",		

	AddAccess : <?= $accessObj->AddFlag ? "true" : "false" ?>,
	EditAccess : <?= $accessObj->EditFlag ? "true" : "false" ?>,
	RemoveAccess : <?= $accessObj->RemoveFlag ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}				
}

function Account(){

	this.mainTab = new Ext.TabPanel({

		renderTo: this.get("mainTab"),
		activeTab: 0,
		plain:true,
		width: "100%",
		height: "100%",
		defaults:{autoHeight: true, autoWidth : true}
	});
}

var AccountObj = new Account();
//------------------------------------------------------------------------------

Account.prototype.NewRowBank=function(){

	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		BankID: null,
		BankName: null
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

Account.prototype.SaveBankData=function(store,record){

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'baseinfo.data.php',
		method: "POST",
		params: {
			task: "SaveBankData",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				AccountObj.grid.getStore().load();
			}
			else
			{
				alert("خطا در اجرای عملیات");
			}
		},
		failure: function(){}
	});
}

Account.RemoveBank = function(value, p, record)
{
	return  "<div  title='حذف اطلاعات' class='remove' onclick='AccountObj.RemoveBank();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:50%;height:16'></div>";
}

Account.listRender = function(value, p, record)
{
	return  "<div  title='حساب ها' class='arrow_left' onclick='AccountObj.sendIdBank();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

Account.prototype.sendIdBank = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	this.AccGrid.getStore().proxy.extraParams["BankID"] = record.data.BankID;
	this.mainTab.getComponent('accounts').enable();
	this.mainTab.setActiveTab('accounts');
	this.AccGrid.getStore().load();
}

Account.prototype.RemoveBank = function(){

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;

		me = AccountObj;
		var record = me.grid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'baseinfo.data.php',
			method: "POST",
			params: {
				task: "DeleteBank",
				BId: record.data.BankID
			},
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.data == "false")
					alert('این آیتم در جای دیگری استفاده شده و قابل حذف نمی باشد.');
				else
					AccountObj.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

//------------------------------------------------------------------------------

Account.prototype.NewRowAcc = function(){

	var modelClass = this.AccGrid.getStore().model;
	var record = new modelClass({
		AccountID : null,
		BranchID: this.AccGrid.getStore().proxy.extraParams["BankID"],
		AccountDesc :null ,
		AccountCode: null,
		ShabaNo: null,
		AccountType: null,
		IsActive: 'YES'
	});
	this.AccGrid.plugins[0].cancelEdit();
	this.AccGrid.getStore().insert(0, record);
	this.AccGrid.plugins[0].startEdit(0, 0);
}

Account.prototype.SaveAccData = function(store,record){

	var record = this.AccGrid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'baseinfo.data.php',
		method: "POST",
		params: {
			task: "SaveAccount",
			BankID : this.AccGrid.getStore().proxy.extraParams.BankID ,
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success)
			{   
				AccountObj.AccGrid.getStore().load();
			}
			else
			{
				if(st.data == "")
					alert("خطا در اجرای عملیات");
				else
					alert(st.data);
			}
		},
		failure: function(){}
	});
}

Account.sendIdAcc = function(value, p, record)
{
	st = "<div  title='لیست چک ها' class='arrow_left' onclick='AccountObj.sendIdAcc();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:33%;height:16'></div>";

	if(record.data.IsActive == "NO")
		return st;

	if(AccountObj.AddAccess)
	st += "<div  title='کپی تنظیمات' class='copy' onclick='AccountObj.CopySetting();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:33%;height:16'></div>" ;

	if(AccountObj.RemoveAccess)
		st += "<div  title='حذف اطلاعات' class='remove' onclick='AccountObj.RemoveAcc();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:33%;height:16'></div>" ;
	return st;
}

Account.prototype.sendIdAcc = function()
{
	var record = this.AccGrid.getSelectionModel().getLastSelected();
	this.CheqGrid.getStore().proxy.extraParams["BAccId"]=record.data.AccountID;
	this.mainTab.getComponent('cheque').enable();
	this.mainTab.setActiveTab('cheque');
	this.CheqGrid.getStore().load();
}

Account.prototype.RemoveAcc=function(){

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;

		me = AccountObj;
		var record = me.AccGrid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'baseinfo.data.php',
			method: "POST",
			params: {
				task: "DeleteAccount",
				AccId: record.data.AccountID
			},
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.data == "false")
					alert('این آیتم در جای دیگری استفاده شده و قابل حذف نمی باشد.');
				else
					AccountObj.AccGrid.getStore().load();
			},
			failure: function(){}
		});
		});
}

Account.prototype.CopySetting = function(){

	var record = this.AccGrid.getSelectionModel().getLastSelected();
	Ext.Ajax.request({
		url: AccountObj.address_prefix  +'baseinfo.data.php?task=CopySetting',
		method : "POST",
		params : {
			AccountID : record.data.AccountID
		},
		success : function(form,action){
			alert('عملیات با موفقیت انجام شد');
			AccountObj.AccGrid.getStore().load();
		},
		failure : function(form,action){
			alert(action.result.data);
		}
	});
}

//------------------------------------------------------------------------------
Account.prototype.NewRowCheque=function(){

	var modelClass = this.CheqGrid.getStore().model;
	var record = new modelClass({
		ChequeID: null,
		AccountID: this.CheqGrid.getStore().proxy.extraParams["BAccId"],
		SerialNo: null,
		MinNo: null,
		MaxNo: null
	});
	this.CheqGrid.plugins[0].cancelEdit();
	this.CheqGrid.getStore().insert(0, record);
	this.CheqGrid.plugins[0].startEdit(0, 0);
}

Account.prototype.SaveChequeData = function(store,record){

	var record = this.CheqGrid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'baseinfo.data.php',
		method: "POST",
		params: {
			task: "SaveCheque",
			record: Ext.encode(record.data)
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);	                                            
			if(st.success=="true")
			{                                           
				AccountObj.CheqGrid.getStore().load();
			}
			if(st.success=="false")
			{                                    
				alert(st.data);
			}
		},
		failure: function(){alert(st.data);}
	});
}

Account.RemoveCheque = function(value, p, record)
{
	if(record.data.IsActive == "NO" && AccountObj.EditAccess)
		return  "<div  title='فعال کردن دسته چک' class='tick' onclick='AccountObj.EnableCheque();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:50%;height:16'></div>";
	if(AccountObj.RemoveAccess)
		return  "<div  title='حذف اطلاعات' class='remove' onclick='AccountObj.RemoveCheque();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:50%;height:16'></div>";
}

Account.prototype.EnableCheque = function(){

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;

		me = AccountObj;
		var record = me.CheqGrid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'baseinfo.data.php',
			method: "POST",
			params: {
				task: "EnableChequeBook",
				CBId: record.data.ChequeID
			},
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.data == "false")
					alert('این دسته چک قادر به فعال شدن نیست');
				else
					AccountObj.CheqGrid.getStore().load();
			},
			failure: function(){}
		});
	});
}

Account.prototype.RemoveCheque=function(){

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;

		me = AccountObj;
		var record = me.CheqGrid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'baseinfo.data.php',
			method: "POST",
			params: {
				task: "DeleteCheque",
				CBId: record.data.ChequeID
			},
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.data == "false")
					alert('این آیتم در جای دیگری استفاده شده و قابل حذف نمی باشد.');
				else
					AccountObj.CheqGrid.getStore().load();
			},
			failure: function(){}
		});
	});
}

Account.prototype.SetChequePrint = function(){

	var record = this.CheqGrid.getSelectionModel().getLastSelected();
	window.open("checkBuilder/index.php?chequeID=" + record.data.ChequeID);
}

//------------------------------------------------------------------------------

</script>
