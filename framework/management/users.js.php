<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

User.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function User()
{

}

User.deleteRender = function(v,p,r)
{
	if(r.data.IsActive == "NO")
		return "";
	return "<div align='center' title='حذف کاربر' class='remove' onclick='UserObject.Deleting();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

User.resetPassRender = function(v,p,r)
{
	if(r.data.IsActive == "NO")
		return "";
	return "<div align='center' title='حذف رمز عبور' class='undo' onclick='UserObject.ResetPass();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

User.prototype.Adding = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		personID : ""
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

User.prototype.Deleting = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	if(record && confirm("آيا مايل به حذف مي باشيد؟"))
	{
		Ext.Ajax.request({
		  	url : this.address_prefix + "framework.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeletePerson",
		  		PersonID : record.data.PersonID
		  	},
		  	success : function(response,o)
		  	{
		  		UserObject.grid.getStore().load();
		  	}
		});
	}
}

User.prototype.saveData = function(store,record)
{
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SavePerson',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'framework.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				UserObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

User.prototype.ResetPass = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
		
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'ResetPass',
			PersonID : record.data.PersonID
		},
		url: this.address_prefix +'framework.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				Ext.MessageBox.alert("Warning","رمز عبور با موفقیت حذف گردید. بعد از اولین بار ورود به  سیستم رمز عبور تنظیم خواهد شد.");
				UserObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

</script>
