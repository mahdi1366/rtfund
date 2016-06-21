<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.07.20
//---------------------------

advanceSearch.prototype = {
	parent : "",

	get : function(elementID){
		return findChild(this.parent, elementID);
	}
};

function advanceSearch(parent)
{
	this.parent = parent;

	new Ext.form.TriggerField({
	    triggerCls:'x-form-search-trigger',
	    onTriggerClick : function(){
	    	
            returnVal = LOV_PersonID();
			if(returnVal != "")
			{
				this.setValue(returnVal);
			}
	    },
	    applyTo : this.get("from_PersonID"),
	    width : 90
	});

	this.field = new Ext.form.TriggerField({

	    triggerCls:'x-form-search-trigger',
	    onTriggerClick : function(){

            returnVal = LOV_PersonID();
			if(returnVal != "")
			{
				this.setValue(returnVal);
			}
	    },
	    applyTo : this.get("to_PersonID"),
	    width : 90
	});

	new Ext.form.FieldSet({
		collapsible: true,
		collapsed : true,
		title : "وضعیت استخدامی",
		renderTo : this.get("FS_emp_state"),
		contentEl : this.get("FS_emp_state2"),
		autoHeight : true,
		style : "background-color: #E9EFFE"
	});
	new Ext.form.FieldSet({
		collapsible: true,
		collapsed : true,
		title : "حالت استخدامی",
		renderTo : this.get("FS_emp_mod"),
		contentEl : this.get("FS_emp_mod2"),
		autoHeight : true,
		style : "background-color: #E9EFFE"
	});
	new Ext.form.SHDateField({
		applyTo: this.get('from_ref_letter_date'),
		format: 'Y/m/d',
		width:'80px'
	});

	new Ext.form.SHDateField({
		applyTo: this.get('to_ref_letter_date'),
		format: 'Y/m/d',
		width:'80px'
	});

	new Ext.form.SHDateField({
		applyTo: this.get('from_send_letter_date'),
		format: 'Y/m/d',
		width:'80px'
	});

	new Ext.form.SHDateField({
		applyTo: this.get('to_send_letter_date'),
		format: 'Y/m/d',
		width:'80px'
	});
	new Ext.form.SHDateField({
		applyTo: this.get('from_issue_date'),
		format: 'Y/m/d',
		width:'80px'
	});

	new Ext.form.SHDateField({
		applyTo: this.get('to_issue_date'),
		format: 'Y/m/d',
		width:'80px'
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

	new Ext.form.SHDateField({
		applyTo: this.get('from_pay_date'),
		format: 'Y/m/d',
		width:'80px'
	});

	new Ext.form.SHDateField({
		applyTo: this.get('to_pay_date'),
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

	this.personTypeCombo = new Ext.form.field.ComboBox({
		transform :this.get("pt"),
		width : 200,
		typeAhead: false,
		queryMode : "local",
		hiddenName : "person_type",
		listeners : {
			select : function(){
				advanceSearchObject.writTypeCombo.reset();
				advanceSearchObject.store1.load({
					params : {person_type : this.getValue()}
				})
			}
		}
	});
	this.personTypeCombo.setValue();
	
	this.store1 = new Ext.data.Store({
		fields : ["writ_type_id","title","person_type"],
		proxy : {
			type: 'jsonp',
			url : this.address_prefix + "../../global/domain.data.php?task=searchWritTypes",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		}
	});
	this.store2 = new Ext.data.Store({
		fields : ["writ_type_id","writ_subtype_id","title"],
		proxy : {
			type: 'jsonp',
			url : this.address_prefix + "../../global/domain.data.php?task=searchWritSubTypes",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		}
	});
	this.writTypeCombo = new Ext.form.field.ComboBox({
		store : this.store1,
		width : 300,
		typeAhead: false,
		queryMode : "local",
		displayField : "title",
		valueField : "writ_type_id",
		hiddenName : "writ_type_id",
		applyTo : this.get("writ_type_id"),
		listeners : {
			select : function(combo, records){
				advanceSearchObject.writSubTypeCombo.reset();
				advanceSearchObject.store2.load({
					params : {writ_type_id : records[0].data.writ_type_id,
                              person_type : records[0].data.person_type }
				})
			}
		}
	});

	this.writSubTypeCombo = new Ext.form.field.ComboBox({
		store : this.store2,
		width : 300,
		typeAhead: false,
		queryMode : "local",
		displayField : "title",
		valueField : "writ_subtype_id",
		hiddenName : "writ_subtype_id",
		applyTo : this.get("writ_subtype_id")
	});
	
	
}

</script>