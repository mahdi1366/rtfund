<script type="text/javascript">
/*-------------------------
 * programmer: Mahdipour
 * CreateDate: 	90.09
 *------------------------- */

TaxTableTypes.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function TaxTableTypes()
{
	this.form = this.get("form_Tax_Types");
	
	this.afterLoad();
}

TaxTableTypes.opRender = function(value,p,record)
{
	var st = "";
	
		st += "<div  title='حذف اطلاعات' class='remove' onclick='TaxTableTypeObject.deleteTaxType();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	st += "<div  title='مشاهده' class='view' onclick='TaxTableTypeObject.ShowDetail();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>" ;
	return st;
}

TaxTableTypes.prototype.deleteTaxType = function()
{
   var record = this.grid.getSelectionModel().getLastSelected();

   if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

   mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف...'});
   mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/tax_table_types.data.php',
		params:{
			task: "deleteTax",
			tax_table_type_id : record.data.tax_table_type_id ,
            person_type : record.data.person_type
		},
		method: 'POST',

		success: function(response,op){
			mask.hide();
			var st = Ext.decode(response.responseText);
			
			if(st.success === "true" )
			{ 
				alert("حذف با موفقیت انجام شد.");
				TaxTableTypeObject.grid.getStore().load();
				return;
			}
			else
			{  
				ShowExceptions("ErrorDiv",st.data);
			}
			
		},
		failure: function(){}
	});
}

TaxTableTypes.prototype.ShowDetail = function()
{
    try {
        this.grid.plugins[0].stopEditing();
        }
   catch(e){

   }

   var record = this.grid.getSelectionModel().getLastSelected();

   this.ttgrid.getStore().proxy.extraParams["tax_table_type_id"] = record.data.tax_table_type_id ;
   this.tax_table_type_id = record.data.tax_table_type_id ;
   this.ttgrid.setTitle(record.data.title) ;

   this.grid.collapse();

    if(this.ttgrid.rendered == true)
        this.ttgrid.getStore().load();
    else
        this.ttgrid.render("dgDiv");
}

TaxTableTypes.prototype.AddSPT = function()
{
    var modelClass = this.grid.getStore().model;
	var record = new modelClass({
                                    tax_table_type_id:null,
                                    title: null,
                                    person_type: 3

                                });
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);

}

TaxTableTypes.prototype.editPST = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/tax_table_types.data.php?task=saveTax',
		params:{
			record: Ext.encode(record.data)
		},
		method: 'POST',
		success: function(response,option){
			mask.hide();
			TaxTableTypeObject.grid.getStore().load();
		},
		failure: function(){}
	});
}


TaxTableTypes.prototype.Adding = function()
{

    var modelClass = this.ttgrid.getStore().model;
	var record = new modelClass({   tax_table_id : null ,
                                    tax_table_type_id: this.tax_table_type_id ,
                                    from_date: null,
                                    to_date: null
                                });
	this.ttgrid.plugins[0].cancelEdit();
	this.ttgrid.getStore().insert(0, record);
	this.ttgrid.plugins[0].startEdit(0, 0);

}

TaxTableTypes.prototype.editTax = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(TaxTableTypeObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: TaxTableTypeObject.address_prefix + '../data/tax_tables.data.php?task=saveTax',
		params:{
			record: Ext.encode(record.data) ,
			tax_table_type_id:  this.tax_table_type_id
		},
		method: 'POST',

		success: function(response,op){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success === "true" )
			{
				alert("عملیات با موفقیت انجام شد .");
				TaxTableTypeObject.ttgrid.getStore().load();
				return;
			}
			else
			{
				ShowExceptions("ErrorDiv",st.data);
				TaxTableTypeObject.ttgrid.getStore().load();
			}
		},
		failure: function(){}
	});
}

TaxTableTypes.prototype.Deleting = function()
{
	var record = this.ttgrid.getSelectionModel().getLastSelected();

	if(!record)
	{
		alert("ابتدا ردیف مورد نظر را انتخاب کنید");
		return;
	}

	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

	mask = new Ext.LoadMask(Ext.getCmp(TaxTableTypeObject.TabID), {msg:'در حال حذف...'});
	mask.show();

	Ext.Ajax.request({
		url: TaxTableTypeObject.address_prefix + '../data/tax_tables.data.php',
		params:{
			task: "deleteTax",
			tax_table_id : record.data.tax_table_id,
            tax_table_type_id:  this.tax_table_type_id

		},
		method: 'POST',

		success: function(response,op){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success === "true" )
			{
				alert("حذف با موفقیت انجام شد.");
				TaxTableTypeObject.ttgrid.getStore().load();
				return;
			}
			else
			{
				ShowExceptions("ErrorDiv",st.data);
			}

		},
		failure: function(){}
	});
}

TaxTableTypes.opDelRender = function(value, p, record)
{
	var st = "";

	st +=  "<div  title='حذف اطلاعات' class='remove' onclick='TaxTableTypeObject.Deleting();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	st += "<div  title='مشاهده' class='view' onclick='TaxTableTypeObject.ShowTaxDetail();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>" ;

	return st ;
}

TaxTableTypes.prototype.ShowTaxDetail = function()
{
    try {
        this.grid.plugins[0].stopEditing();
        }
   catch(e){

   }

   var record = this.ttgrid.getSelectionModel().getLastSelected();

   this.cgrid.getStore().proxy.extraParams["tax_table_type_id"] = record.data.tax_table_type_id ;
   this.cgrid.getStore().proxy.extraParams["tax_table_id"] = record.data.tax_table_id ;
   this.tax_table_id = record.data.tax_table_id ;
   this.cgrid.setTitle(MiladiToShamsi(record.data.from_date) + "  الی " + MiladiToShamsi(record.data.to_date) ) ;

   this.ttgrid.collapse();

    if( this.cgrid.rendered == true )
        this.cgrid.getStore().load();
    else
        this.cgrid.render("tidgDiv");

    this.grid.collapse();

}

//..............................................................................

TaxTableTypes.prototype.TaxItemAdding = function()
{
    var modelClass = this.cgrid.getStore().model;
    
	var record = new modelClass({
                                   tax_table_id: this.tax_table_id ,
                                   row_no: null ,
                                   from_value: null,
                                   to_value: null,
                                   coeficient: null
                                });
                                
	this.cgrid.plugins[0].cancelEdit();
	this.cgrid.getStore().insert(0, record);
	this.cgrid.plugins[0].startEdit(0, 0);
    
}

TaxTableTypes.prototype.editTaxItem = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(TaxTableTypeObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: TaxTableTypeObject.address_prefix + '../data/tax_table_items.data.php?task=saveTaxItem',
		params:{
			record: Ext.encode(record.data) ,
			tax_table_id:  this.tax_table_id 
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			TaxTableTypeObject.cgrid.getStore().load();

		},
		failure: function(){}
	});
}

TaxTableTypes.opDelItemRender = function(value, p, record)
{
	
	return "<div  title='حذف اطلاعات' class='remove' onclick='TaxTableTypeObject.ItemDeleting();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
		
}

TaxTableTypes.prototype.ItemDeleting = function()
{
	var record = this.cgrid.getSelectionModel().getLastSelected();

	if(!record)
	{
		alert("ابتدا ردیف مورد نظر را انتخاب کنید");
		return;
	}

	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

	mask = new Ext.LoadMask(Ext.getCmp(TaxTableTypeObject.TabID), {msg:'در حال حذف...'});
	mask.show();

	Ext.Ajax.request({
		url: TaxTableTypeObject.address_prefix + '../data/tax_table_items.data.php',
		params:{
			task: "deleteTaxItem",
			tax_table_id : record.data.tax_table_id,
			row_no : record.data.row_no
		},
		method: 'POST',

		success: function(response,op){
			mask.hide();
			var st = Ext.decode(response.responseText);

			if(st.success === "true" )
			{
				alert("حذف با موفقیت انجام شد.");
				TaxTableTypeObject.cgrid.getStore().load();
				return;
			}
			else
			{
				ShowExceptions("ErrorDiv",st.data);
			}

		},
		failure: function(){}
	});
}

</script>