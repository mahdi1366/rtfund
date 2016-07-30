<script>
//---------------------------
// programmer:	Mahdipour
// create Date:	94.12
//---------------------------

SalaryItemType.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function SalaryItemType()
{
	this.form = this.get("form_SalaryItemTypes");
	
	

	this.newItemPanel = new Ext.Panel({
		renderTo: this.get("mainpanel"),
		title: "مشخصات حکم",
		autoHeight: true,
		width: 810,
        frame:true,
		style: "padding-right:10px",
        loader:{
            url: this.address_prefix + "new_writ_type.php",
			params : {
			wtid : 1,
			pt : 3
			},			
            scripts: true			
        },
        buttons : [{
					text : "ذخیره",
					iconCls : "save",
					handler : function(){SalaryItemTypeObject.saveWritType();}
                    },
                    {
                        text : "انصراف",
                        iconCls : "back",
                        handler: function(){SalaryItemTypeObject.newItemPanel.hide();}
                    }
			]

	});

	this.newItemPanel.hide();

	this.afterLoad();
}

SalaryItemType.opRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='SalaryItemTypeObject.editInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	st +=	"<div  title='حذف اطلاعات' class='remove' onclick='SalaryItemTypeObject.deleteDevInfo();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	return st;
}

SalaryItemType.prototype.editInfo = function()
{   
	this.newItemPanel.show();
	var record = this.grid.getSelectionModel().getLastSelected();
   
	this.newItemPanel.loader.load({
		url: this.address_prefix + "new_writ_type.php",
		params : {
			wstid : record.data.writ_subtype_id,
			pt:3,
			wtid:1
			
		},
		scripts: true
	});
}	

SalaryItemType.prototype.AddWrt = function()
{
	this.newItemPanel.show();
	this.newItemPanel.loader.load();
}	

SalaryItemType.prototype.saveWritType = function()
{
	if(!this.ValidateSitForm())
		return;
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/writ_type.data.php?task=WritTypeSave',
		params:{
			pt:3,
			WID:1
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
				SalaryItemTypeObject.newItemPanel.hide();
				SalaryItemTypeObject.grid.getStore().load();
			}
			else
			{
				alert(response.responseText);
			}
		},
		failure: function(){}
	});

}

SalaryItemType.prototype.ValidateSitForm = function()
{
	if(this.get("person_type").value == "")
	{
		alert("ورود نوع نیروی انسانی الزامی است.");
		this.get("person_type").focus();
		return false;
	}

	if(this.get("effect_type").value == "")
	{
		alert("ورود اثر الزامی است.");
		this.get("effect_type").focus();
		return false;
	}

	if(this.get("full_title").value == "")
	{
		alert("ورود عنوان کامل الزامی است.");
		this.get("full_title").focus();
		return false;
	}

	if(this.get("print_title").value == "")
	{
		alert("ورود عنوان چاپی الزامی است.");
		this.get("print_title").focus();
		return false;
	}
	if(this.SystemTitle == 2 ){

		if(this.get("available_for").value == "")
		{
			alert("ورود در دسترس برای الزامی است.");
			this.get("available_for").focus();
			return false;
		}

	}
	if(this.get("validity_start_date").value == "")
	{
		alert("ورود تاریخ شروع اعتبار الزامی است.");
		this.get("validity_start_date").focus();
		return false;
	}

	if(this.get("salary_compute_type").value == "")
	{
		alert("ورود نحوه محاسبه الزامی است.");
		this.get("salary_compute_type").focus();
		return false;
	}

		if(this.Mode != "view" &&
		   this.get("salary_compute_type").value == 3 &&
		   this.get("function_name").value == "")
		{
			alert("ورود نام تابع محاسباتی الزامی است.");
			this.get("salary_compute_type").focus();
		}

	return true;
}

SalaryItemType.prototype.deleteDevInfo = function()
{
	if(!confirm("آیا از حذف مطمئن هستید؟"))
		return;

	var record = this.grid.getSelectionModel().getLastSelected();
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/salary_item_type.data.php?task=deleteSIT',
		params:{
			salary_item_type_id: record.data.salary_item_type_id
		},
		method: 'POST',

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				SalaryItemTypeObject.grid.getStore().load();
				alert("حذف قلم حقوقی با موفقیت انجام شد.");
			}
			else
				alert("عملیات مورد نظر با شکست مواجه شد");
		},
		failure: function(){}
	});
}

</script>












