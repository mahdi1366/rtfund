<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	88.06.15
//---------------------------

PersonMiscDoc.prototype = {
	parent : PersonObject,

	grid : "",

	get : function(elementID){
		return findChild(this.form , elementID);
	}
};

function PersonMiscDoc()
{
	this.form = this.parent.get("form_PersonMiscDoc");
	
	 new Ext.form.SHDateField({
		applyTo: this.get("doc_date"),
		format: 'Y/m/d',
		width :'80px'
	});

	Ext.get(this.get("MiscDocTBL")).addKeyListener(13, function(){PersonMiscDocObject.saveMiscDoc();});

	this.afterLoad();
}

PersonMiscDoc.prototype.saveMiscDoc = function()
{ 
	if(this.form.doc_no.value == "" ||
		this.form.doc_date.value == "" ||
		this.form.title.value == "")
	{
		alert("تکمیل کلیه اطلاعات فرم الزامی است");
		return;
	}
	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/misc_doc.data.php',
		params: {
			task: "saveMiscDoc",
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
				PersonMiscDocObject.get("row_no").value = st.data;
				Ext.get(PersonMiscDocObject.get("MiscDocDIV")).setDisplayed(false);
				PersonMiscDocObject.grid.show();
				PersonMiscDocObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

PersonMiscDoc.prototype.deleteMiscDocInfo = function()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;

	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال حذف...'});
	mask.show();
	
	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/misc_doc.data.php',
		params: {
			task: "DeleteMiscDoc",
			PersonID: this.PersonID,
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
				PersonMiscDocObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

PersonMiscDoc.opRender = function(value, p, record)
{
	var st = "";
	<?if($accessObj->UpdateAccess()){?>
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='PersonMiscDocObject.editMiscDocInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	<?}if($accessObj->DeleteAccess()){?>
	st += "<div  title='حذف اطلاعات' class='remove' onclick='PersonMiscDocObject.deleteMiscDocInfo();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	<?}?>
	return st;
}

PersonMiscDoc.prototype.AddMiscDoc = function()
{
  Ext.get(this.get("MiscDocDIV")).setDisplayed(true);
  Ext.get(this.get("MiscDocDIV")).clear();
  this.grid.hide();
  this.form.row_no.value = null ;
  this.form.doc_no.focus();
}

PersonMiscDoc.prototype.editMiscDocInfo = function(record)
{ 
    var record = this.grid.getSelectionModel().getLastSelected();

    Ext.get(this.get("MiscDocDIV")).setVisible(true);
	this.grid.hide();
	this.form.row_no.value = record.data.row_no;
	this.form.doc_no.value = record.data.doc_no;
	this.form.doc_date.value = MiladiToShamsi(record.data.doc_date);
	this.form.title.value = record.data.title;
	this.form.comments.value = record.data.comments;
	this.form.doc_no.focus();
}

PersonMiscDoc.prototype.cancel = function(record)
{
	Ext.get(this.get('MiscDocDIV')).setDisplayed(false);
	this.grid.show();
}
</script>