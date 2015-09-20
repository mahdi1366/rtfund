<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

Branch.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Branch()
{

}

Branch.deleteRender = function(v,p,r)
{
	if(r.data.IsActive == "NO")
		return "";
	return "<div align='center' title='حذف ' class='remove' onclick='BranchObject.Deleting();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

Branch.prototype.Adding = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		BranchID : "",
		IsActive : 'YES'
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

Branch.prototype.saveData = function(store,record)
{
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveBranch',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'baseinfo.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				BranchObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

Branch.prototype.Deleting = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	if(record && confirm("آيا مايل به حذف مي باشيد؟"))
	{
		Ext.Ajax.request({
		  	url : this.address_prefix + "baseInfo.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteBranch",
		  		BranchID : record.data.BranchID
		  	},
		  	success : function(response,o)
		  	{
		  		BranchObject.grid.getStore().load();
		  	}
		});
	}
}


</script>
