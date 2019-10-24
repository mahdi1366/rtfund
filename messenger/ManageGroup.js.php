<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.07
//---------------------------

ManageGroup.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ManageGroup()
{
	
	this.afterLoad();
}

ManageGroup.opRender = function(value, p, record)
{
    
    return   "<div  title='حذف اطلاعات' class='remove' onclick='ManageGroupObject.deleteGrp();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>" +
             "<div  title='مشاهده' class='view' onclick='ManageGroupObject.ShowMember();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>"    ;
}

ManageGroup.opDelRender = function(value, p, record)
{

    return   "<div  title='حذف اطلاعات' class='remove' onclick='ManageGroupObject.deleteMember();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>" ;
}


ManageGroup.prototype.AddGrp = function()
{
    var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		GID: "",
		GroupTitle: null

	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
    
}

ManageGroup.prototype.SaveGrp = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + 'ManageGroup.data.php?task=SaveGrp',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				ManageGroupObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

ManageGroup.prototype.AddMember = function()
{
    var modelClass = this.branchGrid.getStore().model;
	var record = new modelClass({
        GID:this.branchGrid.getStore().proxy.extraParams["GID"],
		MID: null,
		PersonID: null

	});
    
	this.branchGrid.plugins[0].cancelEdit();
	this.branchGrid.getStore().insert(0, record);
	this.branchGrid.plugins[0].startEdit(0, 0);
}

ManageGroup.prototype.SaveMember = function(store,record)
{   
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + 'ManageGroup.data.php?task=SaveMember',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				ManageGroupObject.branchGrid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

ManageGroup.prototype.deleteGrp = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + 'ManageGroup.data.php?task=removeGrp',
		params:{
			GID: record.data.GID
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				ManageGroupObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}		
	});
}

ManageGroup.prototype.ShowMember = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
    this.grid.collapse();
    
    this.LoadPersonInfo() ; 
    
    this.branchGrid.getStore().proxy.extraParams["GID"] =  record.data.GID;
    this.branchGrid.getStore().proxy.extraParams["GroupTitle"] =  record.data.GroupTitle;
    ManageGroupObject.branchGrid.title = "اعضای گروه&nbsp;" + record.data.GroupTitle ; 
    if(this.branchGrid.rendered == true)
        this.branchGrid.getStore().load();
    else
        this.branchGrid.render("div_branch");

}

ManageGroup.prototype.deleteMember = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;

	var record = this.branchGrid.getSelectionModel().getLastSelected();

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + 'ManageGroup.data.php?task=removeMember',
		params:{
			MID: record.data.MID,
            GID: record.data.GID
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				ManageGroupObject.branchGrid.getStore().load();
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