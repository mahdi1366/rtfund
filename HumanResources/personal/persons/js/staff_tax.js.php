<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	90.08
//---------------------------

StaffTax.prototype = {
	parent : PersonObject,
	grid : "",
	staffForm : "",
	taxHistoryForm : "" ,
	IncludeTaxGrid : "" ,
	sid : "" 
	
};

function StaffTax()
{  
	this.staffForm = this.parent.get("form_StaffTax");
	this.taxHistoryForm = this.parent.get("form_TaxHistory");

	this.start_date = new Ext.form.SHDateField({
		applyTo:this.taxHistoryForm.start_date,
		format: 'Y/m/d'
	});

	this.end_date = new Ext.form.SHDateField({
		applyTo: this.taxHistoryForm.end_date,
		format: 'Y/m/d'
	});
    
    this.afterLoad();
}


StaffTax.prototype.saveTaxAction = function()
{
	
	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/staff_tax.data.php?task=saveTax',
		params:{
			PersonID: this.PersonID
		},
		method: 'POST',
		form: this.staffForm,

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
				
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

StaffTax.prototype.saveTaxHis = function()
{

	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/staff_tax.data.php?task=saveTaxHis',
		params:{
			PersonID: this.PersonID
		},
		method: 'POST',
		form: this.taxHistoryForm,

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

			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

<? if( $accessTaxObj->InsertAccess() && $accessTaxObj->UpdateAccess() ){ ?>

StaffTax.prototype.opDelRender = function(store,record,op)
{
	return  "<div  title='حذف اطلاعات' class='remove' onclick='StaffTaxObject.deleteIncHis();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
}

StaffTax.prototype.SaveHistory = function(store,record,op)
{ 
	mask = new Ext.LoadMask(Ext.getCmp(this.parent.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.parent.address_prefix + '../data/staff_tax.data.php?task=saveTaxHisGrid',
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
				StaffTaxObject.IncludeTaxGrid.getStore().load();

			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

StaffTax.prototype.AddIncludeHistory = function()
{
	var modelClass = this.IncludeTaxGrid.getStore().model;
	var record = new modelClass({
		staff_id : this.sid ,
		tax_table_type_id : null ,
		start_date : null ,
		end_date : null ,
		payed_tax_value : null
		
	});
	this.IncludeTaxGrid.plugins[0].cancelEdit();
	this.IncludeTaxGrid.getStore().insert(0, record);
	this.IncludeTaxGrid.plugins[0].startEdit(0, 0);

}

StaffTax.prototype.deleteIncHis = function()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

	var record = this.IncludeTaxGrid.getSelectionModel().getLastSelected();

	mask = new Ext.LoadMask(Ext.getCmp(this.parent.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.parent.address_prefix + '../data/staff_tax.data.php',
		params:{
			task: "removeTaxHistory",
			record: Ext.encode(record.data)
		},
		method: 'POST',


		success: function(response,option){
			mask.hide();
				StaffTaxObject.IncludeTaxGrid.getStore().load();
		},
		failure: function(){}
	});
}

<?	}	?>



</script>