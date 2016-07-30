/*-------------------------
 * programmer: Jafarkhani
 * CreateDate: 	89.04
 *------------------------- */
Ext.QuickTips.init();
Ext.namespace('BasisData');

Ext.onReady(function(){
		    
	new Ext.form.SHDateField({id: 'from_date',applyTo: 'from_date',format: 'Y/m/d'});
	new Ext.form.SHDateField({id: 'to_date',applyTo: 'to_date',format: 'Y/m/d'});
	//new Ext.form.CurrencyField({applyTo: "value"});
	new Ext.Panel({
		id: "newForm",
		applyTo: "newForm", 
		contentEl: "pnl", 
		title: "اطلاعات " + param_title,
		autoHeight: true
	});
	Ext.getCmp("newForm").hide();
});

function GridEdit()
{
	return "<div align='center' title='مشاهده اطلاعات' class='edit' onclick='showInfo();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:hand;width:100%;height:16'></div>";
}

function showInfo()
{
	var record = dg_grid.selModel.getSelected();
	
	document.getElementById("from_date").value = MiladiToShamsi(record.data.from_date);
	document.getElementById("to_date").value = MiladiToShamsi(record.data.to_date);
	document.getElementById("value").value = record.data.value;
	document.getElementById("param_id").value = record.data.param_id;
	
	if(record.data.dim2_id)
	{
		document.getElementById("dim2_id").value = record.data.dim2_id;
		if(param_type == 10) //SPT_FACILITY_PRIVATION_COEF
			Ext.getCmp("ext_dim2_id").setValue(record.data.dim2_id);
	}
		
	if(record.data.dim1_id)
		document.getElementById("dim1_id").value = record.data.dim1_id;
		
	if(record.data.dim3_id)
		document.getElementById("dim3_id").value = record.data.dim3_id;
	
	Ext.getCmp("newForm").show();
}

function Adding()
{
	Ext.get("pnl").clear();
	document.getElementById("param_id").value = "";
	Ext.getCmp("newForm").show();
}

function Deleting()
{
	var record = dg_grid.selModel.getSelected();
	if(!record)
	{
		alert("ابتدا ردیف مورد نظر را انتخاب کنید");
		return;
	}
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
		
	mask = new Ext.LoadMask(document.body, {msg:'در حال حذف...'});
	mask.show();
	
	Ext.Ajax.request({
		url: '../data/salary_param.data.php',
		params:{
			task: "deleteParam",
			param_id : record.data.param_id
		},
		method: 'POST',
		
		success: function(response,option){
			mask.hide();				
			if(response.responseText == "true")
			{
				dg_store.reload();
				return;
			}
			else
			   alert(response.responseText);
		},
		failure: function(){}
	});
}

function saveInfo()
{  
	if(!ValidateForm())
		return;
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: '../data/salary_param.data.php',
		params:{
			task: "saveParam",
			param_type : param_type
		},
		method: 'POST',
		form: document.getElementById('mainForm'),
		
		success: function(response,option){
			mask.hide();				
			if(response.responseText == "true")
			{
				dg_store.reload();
				Ext.getCmp("newForm").hide();
				return;
			}
			else
			   alert(response.responseText);
		},
		failure: function(){}
	});
}

function ValidateForm()
{
	if(document.getElementById("from_date").value == "")
	{
		alert("تکمیل تاریخ شروع الزامی است");
		document.getElementById("from_date").focus();
		return false;
	}
	
	if(document.getElementById("to_date").value == "")
	{
		alert("تکمیل تاریخ پایان الزامی است");
		document.getElementById("to_date").focus();
		return false;
	}
	
	if(document.getElementById("value").value == "")
	{
		alert("تکمیل ضریب یا مقدار الزامی است");
		document.getElementById("value").focus();
		return false;
	}
}

