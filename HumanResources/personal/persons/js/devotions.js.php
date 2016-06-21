<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.03
//---------------------------

PersonDevotion.prototype = {
	parent : PersonObject,
	grid : "",

	get : function(elementID){
		return findChild(this.form, elementID); 
	}
};

function PersonDevotion()
{
	this.form = this.parent.get("form_personDevotion");
	
	new Ext.form.SHDateField({
        inputId:'dev_from_date' ,
		applyTo: this.get("dev_from_date"),
		format: 'Y/m/d',
		width :'80px'
	});

	new Ext.form.SHDateField({
        inputId:'dev_to_date' ,
		applyTo:  this.get('dev_to_date'),
		format: 'Y/m/d'	,
		width :'80px'
	});

	new Ext.form.SHDateField({
		inputId:'letter_date' ,
		applyTo:  this.get('letter_date'),
		format: 'Y/m/d'	,
		width :'80px'
	});

	this.afterLoad();
}

PersonDevotion.opRender = function(value, p, record)
{
	var st = "";
	
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='PersonDevotionObject.editDevInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	<?if($accessObj->DeleteAccess()){?>
	st += "<div  title='حذف اطلاعات' class='remove' onclick='PersonDevotionObject.deleteDevInfo();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	<?}?>
	return st;
}

PersonDevotion.prototype.AddDev = function()
{
  Ext.get(this.get("devDIV")).setDisplayed(true);
  Ext.get(this.get("devDIV")).clear();
  this.get('devotion_row').value = null;
}

PersonDevotion.prototype.editDevInfo = function(record)
{ 
    var record =  this.grid.getSelectionModel().getLastSelected();
    
	Ext.get(this.get("devDIV")).setVisible(true);
	this.get('devotion_type').value = record.data.devotion_type;
	this.get('devotion_row').value = record.data.devotion_row;
	this.get('personel_relation').value = record.data.personel_relation ;
	this.get('enlisted').checked = (record.data.enlisted ==1)? true : false ;
	this.get('continous').checked = (record.data.continous ==1)? true : false ;
	this.get('dev_from_date').value = MiladiToShamsi(record.data.from_date);
	this.get('dev_to_date').value = MiladiToShamsi(record.data.to_date);
	this.get('amount').value = record.data.amount ;
	this.get('war_place').value = record.data.war_place ;
	this.get('letter_no').value = record.data.letter_no ;
	this.get('letter_date').value = MiladiToShamsi(record.data.letter_date) ;	
	//this.form.letter_date.value =  MiladiToShamsi(record.data.letter_date) ;
	this.get('comments').value = record.data.comments ;

	<?if(!$accessObj->UpdateAccess()){?>
        Ext.get(this.get("devDIV")).readonly(new Array("btn_cancel")); 
		//Ext.get(this.get("btn_cancel")).enable();
	<?}?>
}

PersonDevotion.prototype.ValidateDevForm = function()
{
	if(this.get("letter_no").value == "")
	{
		alert("ورود شماره نامه الزامی است.");
		this.get("letter_no").focus();
		return false;
	}

	if(this.get("letter_date").value == "")
	{
		alert("ورود تاریخ نامه الزامی است.");
		this.get("letter_date").focus();
		return false;
	}

	return true;
}

PersonDevotion.prototype.saveDev = function()
{
	if(!this.ValidateDevForm())
		return;

	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/devotion.data.php?task=saveDevotion',
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
				PersonDevotionObject.get("devotion_row").value = st.data;
				Ext.get(PersonDevotionObject.get("devDIV")).setDisplayed(false);
				PersonDevotionObject.grid.show();
				PersonDevotionObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

PersonDevotion.prototype.deleteDevInfo = function()
{
	if(!confirm('؟آيا مايل به حذف مي باشيد'))
		return;

	var record = this.grid.getSelectionModel().getLastSelected(); 

    mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال حذف...'});
	mask.show();
	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/devotion.data.php?task=DelDevotion',
		params:{
			PersonID: record.data.PersonID ,
			devotion_row: record.data.devotion_row
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

				PersonDevotionObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

PersonDevotion.prototype.DevCancle = function()
{
   	Ext.get(this.get("devDIV")).setDisplayed(false);
   	this.grid.show();

}

</script>