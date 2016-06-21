<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	89.11
//---------------------------

CorrectiveIssueWrit.prototype = {

	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	personCombo : "",
	mainPanel  : "",
	corrective_date : "",
	base_execute_date : "",
	base_issue_date : "",
	execute_date : "",
	issue_date : "",

	writType_masterExtCombo : "",
	baseWritType_masterExtCombo : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function CorrectiveIssueWrit()
{
	this.form = this.get("form_correctiveIssueWrit");
	
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
	this.store3 = new Ext.data.Store({
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
	//--------------------------------------------------------------------------
	this.writTypeCombo = new Ext.form.field.ComboBox({
		store : this.store1,
		width : 300,
		typeAhead: false,
		queryMode : "local",
		displayField : "title",
		valueField : "writ_type_id",
		hiddenName : "base_writ_type_id",
		applyTo : this.get("base_writ_type_id"),
		listeners : {
			select : function(combo, records){
				correctiveObject.writSubTypeCombo.reset();
				correctiveObject.store2.load({
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
		hiddenName : "base_writ_subtype_id",
		applyTo : this.get("base_writ_subtype_id")
	});
	//--------------------------------------------------------------------------
	this.writTypeCombo2 = new Ext.form.field.ComboBox({
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
				correctiveObject.writSubTypeCombo2.reset();
				correctiveObject.store3.load({
					params : {writ_type_id : records[0].data.writ_type_id,
                              person_type : records[0].data.person_type }
				})
			}
		}
	});

	this.writSubTypeCombo2 = new Ext.form.field.ComboBox({
		store : this.store3,
		width : 300,
		typeAhead: false,
		queryMode : "local",
		displayField : "title",
		valueField : "writ_subtype_id",
		hiddenName : "writ_subtype_id",
		applyTo : this.get("writ_subtype_id")
	});
	//--------------------------------------------------------------------------
	this.personCombo = new Ext.form.ComboBox({
		store: personStore,
		emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
		typeAhead: false,
		listConfig :{
			loadingText: 'در حال جستجو...'
		},
		pageSize:10,
		width: 450,
		applyTo: this.get("PID")

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

				correctiveObject.writTypeCombo.reset();
				correctiveObject.writTypeCombo2.reset();

				correctiveObject.store1.load({
					params : {person_type : record.data.person_type}
				})

				this.setValue(record.data.staff_id + ':' + record.data.pfname + ' ' + record.data.plname);
				correctiveObject.form.person_type.value = record.data.person_type;
				correctiveObject.form.staff_id.value = record.data.staff_id;

				if(record.data.person_type == "1" )
				{
					correctiveObject.get("prof1").style.display = "";
					correctiveObject.get("prof2").style.display = "";
				}
				else
				{
					correctiveObject.get("prof1").style.display = "none";
					correctiveObject.get("prof2").style.display = "none";
				}
				correctiveObject.mainPanel.doLayout();
				this.collapse();
			}
		}
	});

	this.corrective_date = new Ext.form.SHDateField({
		applyTo: this.get('corrective_date'),
		format: 'Y/m/d'
	});

	this.base_execute_date = new Ext.form.SHDateField({
		applyTo: this.get('base_execute_date'),
		format: 'Y/m/d'
	});
	this.base_issue_date = new Ext.form.SHDateField({
		applyTo: this.get('base_issue_date'),
		format: 'Y/m/d'
	});
	this.execute_date = new Ext.form.SHDateField({
		applyTo: this.form.execute_date,
		format: 'Y/m/d'
	});
	this.issue_date = new Ext.form.SHDateField({
		applyTo: this.form.issue_date,
		format: 'Y/m/d'
	});

	new Ext.form.FieldSet({
		renderTo : this.get("CWritSet"),
		title : "مشخصات حکم اصلاحي",
		contentEl : this.get("CWritTbl"),
		width : 650
	});
	new Ext.form.FieldSet({
		renderTo : this.get("BaseWritSet"),
		title : "مشخصات حکم پايه",
		contentEl : this.get("basewritTbl"),
		width : 650
	});

	this.mainPanel = new Ext.Panel({
		applyTo: this.get("CorrectiveWrit_DIV"),
		contentEl : this.get("CorrectiveWrit_TBL"),
		title: "صدور حكم اصلاحي",
		frame:true,
		width: 700,
		buttons : [
			{
				text : "صدور حکم",
				iconCls : "app",
				handler : function(){ correctiveObject.issueCorrectiveWrit(); }
			}
		]
	});
}

var correctiveObject = new CorrectiveIssueWrit();

CorrectiveIssueWrit.prototype.checkValidity = function()
{
	if(this.form.staff_id.value == "")
		{
			alert("انتخاب ردیف الزامی است.");
			this.get("PID").focus();
			return false;
		}

	if(this.form.base_writ_issue.checked != true  &&
	  this.form.corrective_date.value == "")
		{
			alert("ورود تاریخ شروع اصلاح الزامی است.");
			this.form.corrective_date.focus();
			return false;
		}

	if(this.form.base_writ_issue.checked == true  &&
	   this.form.base_execute_date.value == "")
		{
			alert("ورود تاریخ اجرای حکم پایه الزامی است.");
			this.form.base_execute_date.focus();
			return false;
		}

	if(this.form.base_writ_issue.checked == true  &&
	   this.writTypeCombo.getValue() == null || this.writTypeCombo.getValue() == "")
		{
			alert("ورود نوع حکم پایه الزامی است.");
			this.writTypeCombo.focus();
			return false;
		}

	if(this.writTypeCombo2.getValue() == null || this.writTypeCombo2.getValue() == "")
		{
			alert("ورود نوع حکم الزامی است.");
			this.writTypeCombo2.focus();
			return false;
		}
	if(this.form.execute_date.value == "")
		{
			alert("ورود تاریخ اجرا الزامی است.");
			this.form.execute_date.focus();
			return false;
		}


	 if(DateModule.IsDateGreater(ShamsiToMiladi(this.form.execute_date.value),
	                             ShamsiToMiladi(this.form.corrective_date.value))== false &&
	    DateModule.IsDateEqual(ShamsiToMiladi(this.form.execute_date.value),
	                             ShamsiToMiladi(this.form.corrective_date.value))== false  )
	{
	  		alert("تاريخ اجرا بايد بزرگتر مساوي از تاريخ شروع اصلاح باشد .");
	  		return false ;
	}

	if(this.form.base_writ_issue.checked == true &&
	    DateModule.IsDateGreater(ShamsiToMiladi(this.form.execute_date.value),
	                             ShamsiToMiladi(this.form.base_execute_date.value))== false &&
	    DateModule.IsDateEqual(this.form.execute_date.value,
	                           this.form.base_execute_date.value)== false )
	{
	  		alert("تاريخ اجراي حکم پايه بايد قبل از تاريخ خاتمه اصلاح باشد .");
	  		return false ;
	}

	return true ;
};

CorrectiveIssueWrit.prototype.issueCorrectiveWrit = function()
{
	if(!this.checkValidity())
		return;

	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url : this.address_prefix + "../data/writ.data.php?task=IssueCorrectiveWrit",
		method : "POST",
		form : this.form,
        
		success : function(response)
		{
			mask.hide();

			var ret = Ext.decode(response.responseText);
          
            if( ret.success == true )
			{     
                framework.CloseTab(correctiveObject.TabID);
				framework.OpenPage(correctiveObject.address_prefix + "../ui/view_writ.php", "حکم اصلاح شده",
                            { WID : ret.data.WID,
                              WVER : ret.data.WVER,
                              STID : ret.data.STID });
			}
            else
            {
               ShowExceptions(correctiveObject.get("errorDiv_correctiveWrit"), ret.data);
            }

		}
	});
}

</script>