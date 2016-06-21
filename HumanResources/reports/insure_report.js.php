<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	92.05
//---------------------------
InsurePerson.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
	advanceSearchPanel : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function InsurePerson()
{
	this.form = this.get("form_SearchPost");
	
	new Ext.form.TriggerField({
	    triggerCls:'x-form-search-trigger',
	    onTriggerClick : function(){
	    	
			returnVal = LOV_PersonID() ; 
			
			if(returnVal != "")
			{
				this.setValue(returnVal);
			}
	    },
	    applyTo : this.get("from_person_id"),
	    width : 90
	});
	
	new Ext.form.TriggerField({

	    triggerCls:'x-form-search-trigger',
	    onTriggerClick : function(){

            returnVal = LOV_PersonID();
			if(returnVal != "")
			{
				this.setValue(returnVal);
			}
	    },
	    applyTo : this.get("to_person_id"),
	    width : 90
	});
		
			
	this.advanceSearchPanel = new Ext.Panel({
		applyTo: this.get("AdvanceSearchDIV"),
		contentEl : this.get("searchTBL"),
		title: "گزارش افراد  تحت تکفل",
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
					handler: function(){InsurePersonObject.advance_searching();}
				   },{
					text : "پاک کردن فرم گزارش",
					iconCls : "clear",
					handler : function(){Ext.get(InsurePersonObject.form).clear();}
				  }]
	});	
}

var InsurePersonObject = new InsurePerson();

InsurePerson.prototype.advance_searching = function()
{ 
	
	if( this.form.from_person_id.value == "" ) {
			alert(" لطفا فرد مورد نظر را انتخاب نمایید .");
			return false;
	}
	this.form = this.get("form_SearchPost") ;
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "insure_report.php?showRes=1";
	this.form.submit();	
	return;

}

</script>