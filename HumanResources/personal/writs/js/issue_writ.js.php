<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------

IssueWrit.prototype = {

	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	mainPanel : "",
	personCombo : "",
	issue_date : "",
	execute_date : "",
	contract_start_date : "",
	contract_end_date : "",
	writType_masterExtCombo : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function IssueWrit(){
	
	this.form = this.get("form_issueWrit");

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
				IssueWritObject.writSubTypeCombo.reset();
				IssueWritObject.store2.load({
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
	
	this.personCombo = new Ext.form.ComboBox({
		store: personStore,
		emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
		typeAhead: false,
		listConfig :{
			loadingText: 'در حال جستجو...'
		},
		pageSize:10,
		width: 450,
		valueField : "staff_id",
		applyTo: this.get("issueWrit_PID")

		,tpl: new Ext.XTemplate(
			'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
				,'<td height="23px">کد پرسنلی</td>'
				,'<td>کد شخص</td>'
				,'<td>نام</td>'
				,'<td>نام خانوادگی</td>'
				,'<td>نوع شخص</td>'
				,'<td>واحد محل خدمت</td></tr>',
			'<tpl for=".">',
			'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{PersonID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{personTypeName}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{unit_name}&nbsp;</td></tr>',
			'</tpl>'
			,'</table>')

		,listeners : {
			Select: function(combo, records){
				var record = records[0];
				IssueWritObject.writTypeCombo.reset();
				IssueWritObject.store1.load({
					params : {person_type : record.data.person_type}
				})
				
				IssueWritObject.form.staff_id.value = record.data.staff_id;
				IssueWritObject.form.person_type.value = record.data.person_type;
				this.setValue(record.data.pfname + ' ' + record.data.plname);
					
				
				IssueWritObject.mainPanel.doLayout();
				this.collapse();
			}
		}
	});

	this.issue_date = new Ext.form.SHDateField({
		applyTo: this.form.issue_date,
		format: 'Y/m/d'
	});

	this.execute_date = new Ext.form.SHDateField({
		applyTo: this.form.execute_date,
		format: 'Y/m/d'
	});

	this.contract_start_date = new Ext.form.SHDateField({
		applyTo: this.form.contract_start_date,
		width:'80',
		format: 'Y/m/d'
	});

	this.contract_end_date = new Ext.form.SHDateField({
		applyTo: this.form.contract_end_date,
		width:'80',
		format: 'Y/m/d'
	});
	this.mainPanel = new Ext.Panel({
		applyTo: this.get("newWrit_DIV"),
		contentEl : this.get("newWrit_TBL"),
		title: "صدور حکم",
		width: 600,
		frame:true,
		buttons : [
			{
				text : "صدور حکم",
				iconCls : "app",
				handler : function(){ IssueWritObject.IssuAction(); }
			}
		]
	});
}

var IssueWritObject = new IssueWrit();

IssueWrit.prototype.ChkForm = function()
{
	if(this.form.writ_type_id.value == "")
	{
		alert("وارد کردن نوع حکم الزامی است.");
		this.form.writ_type_id.focus();
		return false;
	}
	if(this.form.execute_date.value == "")
	{
		alert("ورود تاریخ اجرا الزامی می باشد.");
		this.form.execute_date.focus();
		return false;
	}
	if(this.form.contract_start_date.value == "")
	{
		alert("ورود تاریخ شروع قرارداد الزامی است .");
		this.form.contract_start_date.focus();
		return false;
	}
	if(this.form.contract_end_date.value == "")
	{
		alert("ورود تاریخ خاتمه قرارداد الزامی می باشد .");
		this.form.contract_end_date.focus();
		return false;
	}

	return true;
}

IssueWrit.prototype.IssuAction = function(){

	/*if(!this.ChkForm())
		return false; */ 

	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url : this.address_prefix + "../data/writ.data.php?task=IssueWrit",
		method : "POST",
		form : this.form,
		success : function(response)
		{
			mask.hide();

			var ret = Ext.decode(response.responseText);
			if(ret.success == true )
			{
				framework.CloseTab(IssueWritObject.TabID);
				framework.OpenPage(IssueWritObject.address_prefix + "../ui/view_writ.php", "حکم صادره جدید",
					{ WID : ret.data.WID,
					  WVER : ret.data.WVER,
					  STID : ret.data.STID,
                      FacilID : IssueWritObject.FacilID });
                
			}
			else
			{
				ShowExceptions(IssueWritObject.get("errordiv_issueWrit"), ret.data);
			}
		}
	});
}


</script>