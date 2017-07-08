<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------

PersonDependency.prototype = {
	parent : PersonObject,
	grid : "",
	supportGrid : "",
	showWinInfo : "",
	
	get : function(elementID){
		return findChild(this.form, elementID);
	}
};

function PersonDependency()
{
	this.form = this.parent.get("form_PersonDependency");
	
	new Ext.form.SHDateField({
        inputId:'dep_birth_date',
		applyTo: this.get('dep_birth_date'),
		format: 'Y/m/d',
		width :'60px'
	});

	new Ext.form.SHDateField({
            inputId:'dep_marriage_date',
		applyTo: this.get('dep_marriage_date'),
		format: 'Y/m/d',
		width :'60px'
	});

	new Ext.form.SHDateField({
            inputId:'dep_separation_date',
		applyTo: this.get('dep_separation_date'),
		format: 'Y/m/d',
		width :'60px'
	});
	
	this.afterLoad();

    if(!this.showWinInfo)
	{
		this.showWinInfo = new Ext.Window({
			applyTo : this.get("ShowInfoWindow"),
			layout:'fit',
			modal: true,
			width:750,
			title: "سابقه کفالت فرد" ,
			constrain : true,
			autoHeight: true,
            loader :{
                        url: PersonObject.address_prefix + "show_dependent_supoort_info.php",
                        scripts: true
                     },
			Height: '500px',
			closeAction:'hide'
		});
	} 
}


PersonDependency.prototype.SupportDependencyInfo = function()
{
	
	this.showWinInfo.show();

	this.showWinInfo.loader.load({
		url: PersonObject.address_prefix + "show_dependent_supoort_info.php",
		params: {
			PersonID: <?= $_POST['Q0'] ?>,
			row_no: PersonDependencyObject.get("row_no").value
		},
		scripts: true ,
		callback : function(){	
			
		}
	});


}

PersonDependency.opRender = function(value, p, record)
{
	var st = "";
	
	st +=  "<div  title='ویرایش اطلاعات' class='edit' onclick='PersonDependencyObject.editDepInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
        
	
	st += "<div  title='حذف اطلاعات' class='remove' onclick='PersonDependencyObject.deleteDepInfo();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	return st;
}


PersonDependency.opDelRender = function(value, p, record)
{
	return  "<div  title='حذف اطلاعات' class='remove' onclick='PersonDependencyObject.deleteDepSupport();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
}

PersonDependency.prototype.AddDep = function()
{
	Ext.get(this.get("depDIV")).setDisplayed(true);
	Ext.get(this.get("depDIV")).clear();
	this.grid.hide();

	this.get('dep_lname').value = PersonObject.get('plname').value ;
	this.get('dep_father_name').value = PersonObject.get('pfname').value ;
	this.get('row_no').value = null;
}

PersonDependency.prototype.ValidateDepForm = function()
{
	if(this.form.dep_fname.value == "")
	{
		alert(".ورود نام الزامی است");
		this.form.dep_fname.focus();
		return false;
	}

	if(this.form.dep_lname.value == "")
	{
		alert(".ورود نام خانوادگی الزامی است");
		this.form.dep_lname.focus();
		return false;
	}

	if(this.form.dependency.value == "")
	{
		alert(".ورود نوع وابستگی الزامی است");
		this.form.dependency.focus();
		return false;
	}
   
	if(this.form.dep_birth_date.value == "")
	{
		alert(".ورود تاریخ تولد الزامی است");
		this.form.dep_birth_date.focus();
		return false;
	}
	return true;
}

PersonDependency.prototype.saveDep = function()
{
	if(!this.ValidateDepForm())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/dependent.data.php?task=saveDependent',
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
				/*if(PersonDependencyObject.get("row_no").value == "")
					alert("ایجاد فرد وابسته با موفقیت انجام شد.");
				else
					alert("ویرایش فرد وابسته با موفقیت انجام شد.");*/

				PersonDependencyObject.get("row_no").value = st.data;
				Ext.get(PersonDependencyObject.get("depDIV")).setDisplayed(false);
				Ext.get(PersonDependencyObject.get("dephistoryGRID")).setDisplayed(false);
				PersonDependencyObject.grid.show();
				PersonDependencyObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

PersonDependency.prototype.editDepInfo = function(record)
{
    var record = this.grid.getSelectionModel().getLastSelected();	
	 
	Ext.get(this.get("depDIV")).setVisible(true);
	Ext.get(this.get("SupportCompleteInfo")).setVisible(true);
	this.grid.hide();
			
	this.supportGrid.getStore().proxy.extraParams["master_row_no"] =  record.data.row_no;
	this.supportGrid.getStore().proxy.extraParams["personid"] =  record.data.PersonID;
 

	Ext.get(PersonDependencyObject.get("dephistoryGRID")).setDisplayed(true);

    if( this.supportGrid.rendered == true )
        this.supportGrid.getStore().load();
    
    else
        this.supportGrid.render(this.get("dephistoryGRID"));
	
	this.supportGrid.show();

	this.get('row_no').value = record.data.row_no;
	this.get('PersonID').value = record.data.PersonID;
	this.get('dep_fname').value = record.data.fname ;
	this.get('dep_lname').value = record.data.lname ;
	this.get('dep_father_name').value = record.data.father_name ;
	this.get('dependency').value = record.data.dependency ;
	this.get('dep_idcard_no').value = record.data.idcard_no ;
	this.get('dep_birth_date').value = MiladiToShamsi(record.data.birth_date);
 
	this.get('dep_idcard_location').value = record.data.idcard_location ;
        
	this.get('dep_marriage_date').value = MiladiToShamsi(record.data.marriage_date) ;
         
	this.get('dep_separation_date').value = MiladiToShamsi(record.data.separation_date) ;
        
	this.get('insure_no').value = record.data.insure_no ;
	this.get('comments').value = record.data.comments ;
			
		
}

PersonDependency.prototype.deleteDepInfo = function()
{

	if(confirm('?آيا مايل به حذف مي باشيد'))
	{

    var record = this.grid.getSelectionModel().getLastSelected();

    mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال حذف...'});
	mask.show();
	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/dependent.data.php?task=DelDependent',
		params:{
			PersonID: record.data.PersonID ,
			row_no: record.data.row_no
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				PersonDependencyObject.grid.getStore().load();
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

PersonDependency.prototype.DepCancle = function()
{
   	Ext.get(this.get("depDIV")).setDisplayed(false);
	Ext.get(this.get("SupportCompleteInfo")).setDisplayed(false);
   	this.supportGrid.hide();
   	this.grid.show();

}

PersonDependency.prototype.SaveSupport = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/dependent.data.php?task=saveDepSupport',
		params:{
			record: Ext.encode(record.data)
		},
		method: 'POST',


		success: function(response,option){
			mask.hide();
			PersonDependencyObject.supportGrid.getStore().load();
		},
		failure: function(){}
	});
}

PersonDependency.prototype.AddDepSupport = function()
{
	var modelClass = this.supportGrid.getStore().model;
	var record = new modelClass({
		PersonID: this.supportGrid.getStore().proxy.extraParams.personid,
		master_row_no: this.supportGrid.getStore().proxy.extraParams.master_row_no,
		row_no: null,
		support_cause: null,
		insure_type: null,
		from_date: null,
		to_date: null,
		status_title: null
		
	});
	this.supportGrid.plugins[0].cancelEdit();
	this.supportGrid.getStore().insert(0, record);
	this.supportGrid.plugins[0].startEdit(0, 0);
}

PersonDependency.prototype.deleteDepSupport = function()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	
	var record = this.supportGrid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(PersonObject.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: PersonObject.address_prefix + '../data/dependent.data.php',
		params:{
			task: "removeDepSupport",
			record: Ext.encode(record.data)
		},
		method: 'POST',


		success: function(response,option){
			mask.hide();
			PersonDependencyObject.supportGrid.getStore().load();
		},
		failure: function(){}
	});
}


</script>