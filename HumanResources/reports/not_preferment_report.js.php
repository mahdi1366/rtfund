<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	92.02
//---------------------------

Preferment.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
	advanceSearchPanel : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Preferment()
{
	this.form = this.get("form_SearchPrefer");
	
	new Ext.form.TriggerField({
	    triggerCls:'x-form-search-trigger',
	    onTriggerClick : function(){
	    	
            returnVal = LOV_staff();
			if(returnVal != "")
			{
				this.setValue(returnVal);
			}
	    },
	    applyTo : this.get("from_staff_id"),
	    width : 90
	});
	
	new Ext.form.TriggerField({

	    triggerCls:'x-form-search-trigger',
	    onTriggerClick : function(){

            returnVal = LOV_staff();
			if(returnVal != "")
			{
				this.setValue(returnVal);
			}
	    },
	    applyTo : this.get("to_staff_id"),
	    width : 90
	});
	
	new Ext.form.SHDateField({
		applyTo: this.get('from_execute_date'),
		format: 'Y/m/d',
		width:'80px'
	});

	new Ext.form.SHDateField({
		applyTo: this.get('to_execute_date'),
		format: 'Y/m/d',
		width:'80px'
	});
	
	new Ext.form.TriggerField({
        inputId:'ouid' ,
	    triggerCls:'x-form-search-trigger',
	    onTriggerClick : function(){
	    	returnVal = LOV_OrgUnit();
			if(returnVal != "")
			{
				this.setValue(returnVal);
			}
	    },
	    applyTo : this.get("ouid"),
	    width : 90
	});
	
	new Ext.form.FieldSet({
		collapsible: true,
		collapsed : false,
		title : "وضعیت استخدامی",
		renderTo : this.get("FS_emp_state"),
		contentEl : this.get("FS_emp_state2"),
		autoHeight : true,
		style : "background-color: #E9EFFE"
	});
	new Ext.form.FieldSet({
		collapsible: true,
		collapsed : false,
		title : "حالت استخدامی",
		renderTo : this.get("FS_emp_mod"),
		contentEl : this.get("FS_emp_mod2"),
		autoHeight : true,
		style : "background-color: #E9EFFE"
	});
	
	this.advanceSearchPanel = new Ext.Panel({
		applyTo: this.get("AdvanceSearchDIV"),
		contentEl : this.get("searchTBL"),
		title: "گزارش افرادی که ترفیع نگرفته اند",
		autoWidth:true,
		autoHeight: true,
		collapsible : true,
		animCollapse: false,
		frame: true,
		width : 800,
		bodyCfg: {style : "padding-right:10px;background-color:white;"},
			
		buttons : [{
					text:'جستجو',
					iconCls: 'search',
					handler: function(){PrefermentObject.advance_searching();}
				   },{
					text : "پاک کردن فرم گزارش",
					iconCls : "clear",
					handler : function(){Ext.get(PrefermentObject.form).clear();}
				  }]
	});	
}

var PrefermentObject = new Preferment();

Preferment.prototype.advance_searching = function()
{
	this.form = this.get("form_SearchPrefer") ;
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "not_preferment_report.php?showRes=1";
	this.form.submit();	
	return;

}

</script>