<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// Date:		90.02
//---------------------------

GroupIssueWrit.prototype = {

	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	mainPanel : "",
	issue_date : "",
	execute_date : "",
	unitExtCombo : "",
	writTypeMasterCombo : "",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

GroupIssueWrit.prototype.makeCombos = function()
{
	this.personTypeCombo = new Ext.form.field.ComboBox({
		transform :this.get("pt"),
		width : 200,
		typeAhead: false,
		queryMode : "local",
		hiddenName : "person_type",
		listeners : {
			select : function(){
				groupIssueObject.writTypeCombo.reset();
				groupIssueObject.store1.load({
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
			url : this.address_prefix + "../../../global/domain.data.php?task=searchWritTypes",
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
			url : this.address_prefix + "../../../global/domain.data.php?task=searchWritSubTypes",
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
				groupIssueObject.writSubTypeCombo.reset();
				groupIssueObject.store2.load({
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

function GroupIssueWrit()
{
	this.form = this.get("form_groupIssueWrit");
	
	this.makeCombos();

	this.fs = new Ext.form.FieldSet({
		collapsible: true,
		//collapsed : true,
		title : "وضعیت استخدامی",
		renderTo : this.get("FS_empState"),
		contentEl : this.get("DIV_empState"),
		autoHeight : true,
		style : "background-color: #E9EFFE"
	});

	new Ext.form.TriggerField({
		triggerCls:'x-form-search-trigger',
		onTriggerClick : function(){
			this.setValue(LOV_PersonID());
		},
		applyTo : this.get("from_PersonID"),
		width : 90
	});

	new Ext.form.TriggerField({
		triggerCls:'x-form-search-trigger',
		onTriggerClick : function(){
			this.setValue(LOV_PersonID());
		},
		applyTo : this.get("to_PersonID"),
		width : 90
	});

	this.mainPanel = new Ext.Panel({
		applyTo: this.get("newWrit_DIV"),
		bodyPadding : 10,
		items:[{
			xtype :"container",
			contentEl :this.get("newWrit_TBL")
		}],
		title: "صدور حکم گروهی",
		width: 750,
		collapsible : true,
		frame:true,
		buttons : [
			{
				text : "صدور حکم",
				iconCls : "app",
				handler : function(){ groupIssueObject.groupIssueAction(); }
			}
		]
	});

	this.issue_date = new Ext.form.SHDateField({
		applyTo: this.get('issue_date'),
		format: 'Y/m/d'
	});

	this.execute_date = new Ext.form.SHDateField({
		applyTo: this.get('execute_date'),
		format: 'Y/m/d'
	});

    new Ext.form.TriggerField({
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

	this.mainPanel.doLayout();
}

var groupIssueObject = new GroupIssueWrit();

GroupIssueWrit.prototype.groupIssueAction = function()
{
	if(!this.validateForm())
		return;
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال صدور...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/writ.data.php',
		params:{
			task : "GroupIssueWrit"
		},
		method: 'POST',
		form: this.form,

		success: function(response,option){
			mask.hide();
			groupIssueObject.mainPanel.collapse();
			groupIssueObject.get("result").innerHTML = response.responseText;
		},
		failure: function(){
			mask.hide();
		}
	});
}

GroupIssueWrit.prototype.validateForm = function()
{
	if(this.form.person_type.value == "-1")
	{
		alert("انتخاب نوع فرد الزامی است");
		this.form.person_type.focus();
		return false;
	}
	if(this.writTypeCombo.getValue() == "")
	{
		alert("وارد کردن نوع حکم الزامی است.");
		this.writTypeCombo.focus();
		return false;
	}

	if(this.form.execute_date.value == "")
	{
		alert("ورود تاریخ اجرا الزامی می باشد.");
		this.form.execute_date.focus();
		return false;
	}

	return true;
}

</script>