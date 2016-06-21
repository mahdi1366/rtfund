<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.07
//---------------------------

StudyFields.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function StudyFields()
{
	
	this.afterLoad();
}

StudyFields.opRender = function(value, p, record)
{
    
    return   "<div  title='حذف اطلاعات' class='remove' onclick='StudyFieldsObject.deleteField();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>" +
             "<div  title='مشاهده' class='view' onclick='StudyFieldsObject.ShowBranchs();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>"    ;
}

StudyFields.opDelRender = function(value, p, record)
{

    return   "<div  title='حذف اطلاعات' class='remove' onclick='StudyFieldsObject.deleteBranch();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>" ;
}


StudyFields.prototype.AddStudyField = function()
{
    var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		sfid: "",
		ptitle: null

	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
    
}

StudyFields.prototype.SaveField = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/study_fields.data.php?task=SaveField',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				StudyFieldsObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

StudyFields.prototype.AddStudyBranch = function()
{
    var modelClass = this.branchGrid.getStore().model;
	var record = new modelClass({
        sfid:this.branchGrid.getStore().proxy.extraParams["sfid"],
		sbid: null,
		ptitle: null

	});
    
	this.branchGrid.plugins[0].cancelEdit();
	this.branchGrid.getStore().insert(0, record);
	this.branchGrid.plugins[0].startEdit(0, 0);
}

StudyFields.prototype.SaveBranch = function(store,record)
{   
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/study_fields.data.php?task=SaveBranch',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				StudyFieldsObject.branchGrid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

StudyFields.prototype.deleteField = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/study_fields.data.php?task=removeField',
		params:{
			sfid: record.data.sfid
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				StudyFieldsObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}		
	});
}

StudyFields.prototype.ShowBranchs = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
    this.grid.collapse();
    this.branchGrid.getStore().proxy.extraParams["sfid"] =  record.data.sfid;
    this.branchGrid.getStore().proxy.extraParams["ptitle"] =  record.data.ptitle;
    if(this.branchGrid.rendered == true)
        this.branchGrid.getStore().load();
    else
        this.branchGrid.render("div_branch");

}

StudyFields.prototype.deleteBranch = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;

	var record = this.branchGrid.getSelectionModel().getLastSelected();

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/study_fields.data.php?task=removeBranch',
		params:{
			sfid: record.data.sfid,
            sbid: record.data.sbid
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				StudyFieldsObject.branchGrid.getStore().load();
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