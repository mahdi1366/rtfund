<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	92.02
//---------------------------
ProfPost.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
	advanceSearchPanel : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ProfPost()
{
	this.form = this.get("form_SearchPost");
	
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
	
	new Ext.form.TriggerField({
	    triggerCls:'x-form-search-trigger',
	    onTriggerClick : function(){
	    	
            returnVal = LOV_Post();
			if(returnVal != "")
			{
				this.setValue(returnVal);
			}
	    },
	    applyTo : this.get("from_post_id"),
	    width : 120
	});
	
	new Ext.form.TriggerField({
	    triggerCls:'x-form-search-trigger',
	    onTriggerClick : function(){
	    	
            returnVal = LOV_Post("one");
			if(returnVal != "")
			{
				this.setValue(returnVal);
			}
	    },
	    applyTo : this.get("to_post_id"),
	    width : 120
	});
	
	new Ext.form.SHDateField({
		applyTo: this.get('from_start_date'),
		format: 'Y/m/d',
		width:'80px'
	});

	new Ext.form.SHDateField({
		applyTo: this.get('to_start_date'),
		format: 'Y/m/d',
		width:'80px'
	});
	
	new Ext.form.SHDateField({
		applyTo: this.get('from_end_date'),
		format: 'Y/m/d',
		width:'80px'
	});

	new Ext.form.SHDateField({
		applyTo: this.get('to_end_date'),
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
		
	this.advanceSearchPanel = new Ext.Panel({
		applyTo: this.get("AdvanceSearchDIV"),
		contentEl : this.get("searchTBL"),
		title: "گزارش افراد دارای سمت",
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
					handler: function(){ProfPostObject.advance_searching();}
				   },{
					text : "پاک کردن فرم گزارش",
					iconCls : "clear",
					handler : function(){Ext.get(ProfPostObject.form).clear();}
				  }]
	});	
}

var ProfPostObject = new ProfPost();

ProfPost.prototype.advance_searching = function()
{		
	this.form = this.get("form_SearchPost") ;
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "professor_posts_report.php?showRes=1";
	this.form.submit();	
	return;

}

</script>