<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------

PersonEducation.prototype = {
	parent : PersonObject,

	grid : "",

	get : function(elementID){
		return findChild(this.form , elementID);
	}
};

function PersonEducation()
{
	this.form = this.parent.get("form_PersonEducation");

	this.dDate = new Ext.form.SHDateField({
		applyTo: this.get('doc_date'),
		format: 'Y/m/d',
		width :'80px' ,
		listeners: {blur: function(){
			if(this.getValue() != "")
				PersonEducationObject.dDate2.setValue(JtoG(this.getValue()));
			}}
	});

   this.dDate2 = new Ext.form.DateField({
        inputId:'georgian_doc_date',
		applyTo: this.get('georgian_doc_date'),
		format: 'Y/m/d',
		width :'80px',
		listeners: {blur: function(){
			if(this.getValue() != "")
				PersonEducationObject.dDate.setValue(GtoJ(this.getValue()));
			}}
	});

	this.store1 = new Ext.data.Store({
		
		fields : ["sfid","ptitle"],
		proxy : {
			type: 'jsonp',
			url : PersonObject.address_prefix + "../../../global/domain.data.php?task=searchStudyField",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		},
		autoLoad:true
	});


	this.store2 = new Ext.data.Store({
		fields : ["sfid","sbid","ptitle"],
		proxy : {
			type: 'jsonp',
			url : PersonObject.address_prefix + "../../../global/domain.data.php?task=searchStudyBranches",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		}
	});


	this.StudyFieldCombo = new Ext.form.field.ComboBox({
		store : this.store1,
		width : 200,
		typeAhead: false,
		queryMode : "local",
		displayField : "ptitle",
		valueField : "sfid",
		hiddenName : "sfid",
		applyTo : this.get("sfid"),
		listeners : {
			select : function(combo, records){
				PersonEducationObject.StudyBranchCombo.reset();
				PersonEducationObject.store2.load({
					params : {sfid : records[0].data.sfid}
				})
			}
		}
	});

	this.StudyBranchCombo = new Ext.form.field.ComboBox({
		store : this.store2,
		width : 100,
		typeAhead: false,
		queryMode : "local",
		displayField : "ptitle",
		valueField : "sbid",
		hiddenName : "sbid",
		applyTo : this.get("sbid")
	});

//.................................................................

	this.store3 = new Ext.data.Store({

		fields : ["country_id","ptitle"],
		proxy : {
			type: 'jsonp',
			url : PersonObject.address_prefix + "../../../global/domain.data.php?task=searchCountries",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		},
		autoLoad:true
	});


	this.store4 = new Ext.data.Store({
		fields : ["country_id","university_id","ptitle"],
		proxy : {
			type: 'jsonp',
			url : PersonObject.address_prefix + "../../../global/domain.data.php?task=searchuniversities",
			reader: {
				root: 'rows',
				totalProperty: 'totalCount'
			}
		}
	});


	this.CountryCombo = new Ext.form.field.ComboBox({
		store : this.store3,
		width : 200,
		typeAhead: false,
		queryMode : "local",
		displayField : "ptitle",
		valueField : "country_id",
		hiddenName : "country_id",
		applyTo : this.get("country_id"),
		listeners : {
			select : function(combo, records){
				PersonEducationObject.UniversityCombo.reset();
				PersonEducationObject.store4.load({
					params : {country_id : records[0].data.country_id }
				})
			}
		}
	});

	this.UniversityCombo = new Ext.form.field.ComboBox({
		store : this.store4,
		width : 300,
		typeAhead: false,
		queryMode : "local",
		displayField : "ptitle",
		valueField : "university_id",
		hiddenName : "university_id",
		applyTo : this.get("university_id")
	});

	this.afterLoad();
}

PersonEducation.opRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='PersonEducationObject.editEducInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	st += "<div  title='حذف اطلاعات' class='remove' onclick='PersonEducationObject.deleteEducInfo();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	return st;
}

PersonEducation.prototype.AddEduc = function()
{
  Ext.get(this.get("EducDIV")).setDisplayed(true);
  Ext.get(this.get("EducDIV")).clear();
  this.get('row_no').value = null;
}

PersonEducation.prototype.editEducInfo = function(record)
{
    var record = this.grid.getSelectionModel().getLastSelected();
   
	Ext.get(this.get("EducDIV")).setVisible(true);
	//this.grid.hide();
	this.form.row_no.value = record.data.row_no;
	this.form.education_level.value = record.data.education_level;
	this.form.doc_date.value = MiladiToShamsi(record.data.doc_date);
	this.form.georgian_doc_date.value = record.data.doc_date;
	this.form.certificated.checked = (record.data.certificated ==1)? true : false ;
	this.StudyFieldCombo.setValue(record.data.sfid);
	this.store2.load({
				params:{sfid:PersonEducationObject.StudyFieldCombo.getValue()},
				callback:function(){
					PersonEducationObject.StudyBranchCombo.setValue(record.data.sbid);
				}
			});
	this.CountryCombo.setValue(record.data.country_id);
    this.store4.load({
				params:{country_id:PersonEducationObject.CountryCombo.getValue()},
				callback:function(){
					PersonEducationObject.UniversityCombo.setValue(record.data.university_id);
				}
			});

	this.form.grade.value = record.data.grade;
	this.form.burse.checked = (record.data.burse ==1)? true : false ;
	this.form.thesis_ptitle.value = record.data.thesis_ptitle;
	this.form.thesis_etitle.value = record.data.thesis_etitle;
	this.form.comments.value = record.data.comments;
	    
	
}

PersonEducation.prototype.saveEduc = function()
{
	if(!this.ValidateEducForm())
		return;

	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/education.data.php?task=saveEducation',
		params:{
			PersonID: this.PersonID
		},
		method: 'POST',
		form: this.form,

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
				PersonEducationObject.get("row_no").value = st.data;
				Ext.get(PersonEducationObject.get("EducDIV")).setDisplayed(false);
				PersonEducationObject.grid.show();
				PersonEducationObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

PersonEducation.prototype.deleteEducInfo = function()
{

 if(confirm('?آيا مايل به حذف مي باشيد'))
   {

    var record = this.grid.getSelectionModel().getLastSelected();

    mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال حذف...'});
	mask.show();
	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/education.data.php?task=DelEduc',
		params:{
			PersonID: record.data.PersonID ,
			row_no: record.data.row_no
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			if(response.responseText.indexOf("DelError") != -1 )
			{
				alert("عملیات مورد نظر با شکست مواجه شد");
				return;
			}
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				PersonEducationObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
   }
}

PersonEducation.prototype.ValidateEducForm = function()
{
	if(this.get("education_level").value == "")
	{
		alert("ورود مقطع الزامی است.");
		this.get("education_level").focus();
		return false;
	}

	return true;
}

PersonEducation.prototype.cancel = function()
{
	Ext.get(this.get('EducDIV')).setDisplayed(false);
	this.grid.show();
}


</script>