<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	90.08
//---------------------------

StaffCostCode.prototype = {
	parent : PersonObject,
	grid : "",
	IncludeCostGrid : "" ,
	sid : "" 
	
};

function StaffCostCode()
{  

    this.afterLoad();
}


StaffCostCode.prototype.opDelRender = function(store,record,op)
{
	return  "<div  title='حذف اطلاعات' class='remove' onclick='StaffCostCodeObject.deleteIncHis();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
}

StaffCostCode.prototype.AddIncludeHistory = function()
{
	var modelClass = this.IncludeCostGrid.getStore().model;
	var record = new modelClass({
		StaffID : this.sid ,
		CostID : null ,
		StartDate : null ,
		EndDate : null 
		
	});
	this.IncludeCostGrid.plugins[0].cancelEdit();
	this.IncludeCostGrid.getStore().insert(0, record);
	this.IncludeCostGrid.plugins[0].startEdit(0, 0);

}

StaffCostCode.prototype.SaveHistory = function(store,record,op)
{ 
	mask = new Ext.LoadMask(Ext.getCmp(this.parent.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.parent.address_prefix + '../data/staff_tax.data.php?task=saveCostCodeGrid',
		params:{
            PersonID: this.PersonID ,
			record: Ext.encode(record.data)
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			if(response.responseText.indexOf("InsertError") != -1 ||
				response.responseText.indexOf("UpdateError") != -1)
			{
				alert("عملیات مورد نظر با شکست مواجه شد");
				return;
			}
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("عملیات ذخیره سازی با موفقیت انجام گردید.");
				StaffCostCodeObject.IncludeCostGrid.getStore().load();

			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

StaffCostCode.prototype.deleteIncHis = function()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

	var record = this.IncludeCostGrid.getSelectionModel().getLastSelected();

	mask = new Ext.LoadMask(Ext.getCmp(this.parent.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.parent.address_prefix + '../data/staff_tax.data.php',
		params:{
			task: "removeCostCode",
			record: Ext.encode(record.data)
		},
		method: 'POST',


		success: function(response,option){
			mask.hide();
				StaffCostCodeObject.IncludeCostGrid.getStore().load();
		},
		failure: function(){}
	});
}

</script>