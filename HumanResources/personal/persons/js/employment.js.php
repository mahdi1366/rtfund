<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.03
//---------------------------

PersonEmplyment.prototype = {
	parent : PersonObject,

	grid : "",

	get : function(elementID){
		return findChild(this.form , elementID);
	}
};

function PersonEmplyment()
{
	this.form = this.parent.get("form_PersonEmployment");

	new Ext.form.SHDateField({
		applyTo: this.form.from_date,
		format: 'Y/m/d'	,
		width :'80px'
	});

	new Ext.form.SHDateField({
		applyTo: this.form.to_date,
		format: 'Y/m/d'	,
		width :'80px'
	});

	this.afterLoad();
}

PersonEmplyment.opRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='PersonEmplymentObject.editEmpInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	<?if($accessObj->DeleteAccess()){?>
	st += "<div  title='حذف اطلاعات' class='remove' onclick='PersonEmplymentObject.deleteEmpInfo();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	<?}?>
	return st;
}

PersonEmplyment.prototype.AddEmp = function()
{
  Ext.get(this.get("EmpDIV")).setDisplayed(true);
  this.grid.hide();
  Ext.get(this.get("EmpDIV")).clear();
  this.form.row_no.value = null;
  this.form.duration_year.value = 0 ;
  this.form.duration_month.value = 0 ;
  this.form.duration_day.value = 0 ;
  this.form.retired_duration_year.value = 0 ;
  this.form.retired_duration_month.value = 0 ;
  this.form.retired_duration_day.value = 0 ;
  this.form.group_duration_year.value = 0 ;
  this.form.group_duration_month.value = 0 ;
  this.form.group_duration_day.value = 0 ;
}

PersonEmplyment.prototype.editEmpInfo = function(record)
{
    var record = this.grid.getSelectionModel().getLastSelected(); 
	Ext.get(this.get("EmpDIV")).setVisible(true);
	//this.grid.hide();
	this.form.row_no.value = record.data.row_no;
	this.form.organization.value = record.data.organization;
	this.form.unit.value = record.data.unit;
	this.form.org_type.value = record.data.org_type;
	this.form.person_type.value = record.data.person_type;
	this.form.emp_state.value = record.data.emp_state;
	this.form.emp_mode.value = record.data.emp_mode;
	this.form.from_date.value = MiladiToShamsi(record.data.from_date);
	this.form.to_date.value = MiladiToShamsi(record.data.to_date);
	this.form.title.value = record.data.title;
	this.form.unemp_cause.value = record.data.unemp_cause;
	this.form.duration_year.value = (record.data.duration_year !=null )? record.data.duration_year : 0 ;
	this.form.duration_month.value =(record.data.duration_month !=null )? record.data.duration_month : 0 ;
	this.form.duration_day.value = (record.data.duration_day!=null )? record.data.duration_day : 0  ;
	this.form.retired_duration_year.value = (record.data.retired_duration_year!= null ) ? record.data.retired_duration_year : 0 ;
	this.form.retired_duration_month.value = (record.data.retired_duration_month!= null ) ? record.data.retired_duration_month : 0 ;
	this.form.retired_duration_day.value = (record.data.retired_duration_day!= null ) ? record.data.retired_duration_day : 0 ;
	this.form.group_duration_year.value = (record.data.group_duration_year!= null ) ? record.data.group_duration_year : 0 ;
	this.form.group_duration_month.value = (record.data.group_duration_month!=null) ? record.data.group_duration_month : 0 ;
	this.form.group_duration_day.value = (record.data.group_duration_day!= null) ? record.data.group_duration_day : 0 ;
	this.form.comments.value = record.data.comments;

	<?if(!$accessObj->UpdateAccess()){?>
        Ext.get(this.get("EmpDIV")).readonly(new Array("btn_cancel"));
		
	<?}?>
}

PersonEmplyment.prototype.ValidateEmpForm = function()
{
	if(this.get("organization").value == "")
	{
		alert("ورود سازمان محل خدمت الزامی است.");
		this.get("organization").focus();
		return false;
	}

	if(this.get("org_type").value == "")
	{
		alert("ورود نوع سازمان الزامی است.");
		this.get("org_type").focus();
		return false;
	}

	if(this.get("person_type").value == "")
	{
		alert("ورود نوع خدمت الزامی است.");
		this.get("person_type").focus();
		return false;
	}
	if(this.get("from_date").value == "")
	{
		alert("ورود تاریخ شروع الزامی است.");
		this.get("from_date").focus();
		return false;
	}
	if(this.get("to_date").value == "")
	{
		alert("ورود تاریخ خاتمه الزامی است.");
		this.get("to_date").focus();
		return false;
	}
	if(this.get("title").value == "")
	{
		alert("ورود عنوان شغل الزامی است.");
		this.get("title").focus();
		return false;
	}
	if(this.get("unemp_cause").value == "")
	{
		alert("ورود دلیل خاتمه الزامی است.");
		this.get("unemp_cause").focus();
		return false;
	}

	return true;
}

PersonEmplyment.prototype.saveEmp = function()
{
	if(!this.ValidateEmpForm())
		return;

	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/employment.data.php?task=saveEmployee',
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
				PersonEmplymentObject.get("row_no").value = st.data;
				Ext.get(PersonEmplymentObject.get("EmpDIV")).setDisplayed(false);
				PersonEmplymentObject.grid.show();
				PersonEmplymentObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

PersonEmplyment.prototype.deleteEmpInfo = function()
{
	if(!confirm('?آيا مايل به حذف مي باشيد'))
		return;
    var record = this.grid.getSelectionModel().getLastSelected();  

    mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال حذف...'});
	mask.show();
	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/employment.data.php?task=DelEmployee',
		params:{
			PersonID: this.PersonID ,
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
				alert("حذف با موفقیت انجام شد.");
				PersonEmplymentObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

PersonEmplyment.prototype.empCancle = function()
{
   	Ext.get(this.get("EmpDIV")).setDisplayed(false);
   	this.grid.show();
}

</script>