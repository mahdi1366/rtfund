<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.04
//---------------------------

buildReport.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	report_id : '<?= $report_id?>',

	groupColumns : "",
	orderColumns : "",
	separationColumns : "",
	filterColumns : "",
	conditionColumn : <?= $conditionJsArray?>,
	
	formulaColumns : new Array(),

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

buildReport.prototype.AddFieldToColumns = function(selectName, place)
{
	var node = this.tree.getSelectionModel().getSelection()[0];
	if(!node.isLeaf())
	{
		alert("گره انتخابی آیتم معتبری نمی باشد");
		return;
	}
	elem = this.get(selectName);

	var text = node.data.text;
	var id = node.data.id;

	// check the duplicate ----------------------------------
	for(i=0; i<elem.options.length; i++)
		if(elem.options[i].value == id && elem.options[i].text == text)
			return;
	//add to related select ---------------------------------
	elem.options.add(new Option(text, id));

	// fill up the related variable -------------------------
	switch(place)
	{
		case "group" :
			this.groupColumns += id + ",";
			break;
		case "order" :
			this.orderColumns += id + ",";
			break;
		case "separation" :
			this.separationColumns += id + ",";
			break;
		case "filter" :
			this.filterColumns += id + ",";
			break;
	}
}

buildReport.prototype.AddReportColumn = function()
{
	var node = this.tree.getSelectionModel().getSelection()[0];
	if(!node.isLeaf())
	{
		alert("گره انتخابی آیتم معتبری نمی باشد");
		return;
	}
	var id = node.data.id;
	var tmp = id.split('_');
	var column_id = tmp[tmp.length-1];
	var alias = "tbl" + tmp[tmp.length-2];
	
	Ext.Ajax.request({
		url : this.address_prefix + "../data/report.data.php",
		method : "POST",
		params : {
			task : "addReportColumn",
			report_id : this.report_id,
			parent_path : id,
			column_id : column_id,
			alias : alias,
			used_type : "basic"
		},
		success : function(response)
		{
			if(response.responseText == "Duplicate")
			{
				alert("این آیتم قبلا به ستون های گزارش اضافه شده است");
				return;
			}
			if(response.responseText == "true")
			{
				buildReportObject.col_dg.getStore().load();
			}
		}
	});
}

buildReport.prototype.SPColumnAdd = function(summeryType, summaryTitle)
{
	var node = this.tree.getSelectionModel().getSelection()[0];
	if(!node.isLeaf())
	{
		alert("گره انتخابی آیتم معتبری نمی باشد");
		return;
	}
	var id = node.data.id;
	var tmp = id.split('_');
	var column_id = tmp[tmp.length-1];
	var alias = "tbl" + tmp[tmp.length-2];

	Ext.Ajax.request({
		url : this.address_prefix + "../data/report.data.php",
		method : "POST",
		params : {
			task : "addReportColumn",
			report_id : this.report_id,
			parent_path : id,
			column_id : column_id,
			alias : alias,
			used_type : "summary",
			summary_type : summeryType,
			field_title : summaryTitle + " " + node.data.text
		},
		success : function(response)
		{
			if(response.responseText == "Duplicate")
			{
				alert("این آیتم قبلا به ستون های گزارش اضافه شده است");
				return;
			}
			if(response.responseText == "true")
			{
				buildReportObject.col_dg.getStore().load();
			}
		}
	});
}

buildReport.prototype.formulaAdd = function()
{
	var node = this.tree.getSelectionModel().getSelection()[0];
	if(!node.isLeaf())
	{
		alert("گره انتخابی آیتم معتبری نمی باشد");
		return;
	}
	var index = this.formulaColumns.length;
	this.formulaColumns[index] = new Array("[" + index + "-" + node.data.text + "]", "[" + node.data.id + "]");
	this.get("txt_formula").value += "[" + index + "-" + node.data.text + "]";
}

function buildReport()
{
	this.form = this.get("form_buildReport");

	this.col_dg = <?= $grid?>;
	this.col_dg.render("columns_grid");
	//this.col_dg.getBottomToolbar().hide();
	//this.col_dg.getTopToolbar().hide();
	
	this.tree = new Ext.tree.Panel({
		renderTo : this.get('tree-div'),
		frame : true,
		width: 450,
		height: 700,
		title: "کلیه آیتم های موجود در سیستم",
		
		store : new Ext.data.TreeStore({
			root : {
				id : "source",
				text: 'آیتم ها',
				expanded: true
			},
			proxy: {
				type: 'ajax',
				url: this.address_prefix + "../data/report.data.php?task=getColumns"
			}
		})
	});

	this.menu = new Ext.menu.Menu({
		items: [
			{
				text: 'اضافه آیتم به ستون های ساده گزارش',
				handler: Ext.bind(this.AddReportColumn,this),
				iconCls: 'add'
			},{
				text: 'اضافه آیتم به ستون های خاص گزارش',
				iconCls: '',
				menu: {
					items: [
						{
							text: 'مجموع',
							handler: Ext.bind(this.SPColumnAdd,this,["sum", "مجموع"])
						}, {
							text: 'تعداد',
							handler: Ext.bind(this.SPColumnAdd,this,["count", "تعداد"])
						}, {
							text: 'میانگین',
							handler: Ext.bind(this.SPColumnAdd,this,["avg", "میانگین"])
						}, {
							text: 'ماکزیمم',
							handler: Ext.bind(this.SPColumnAdd,this,["max", "ماکزیمم"])
						}, {
							text: 'مینیمم',
							handler: Ext.bind(this.SPColumnAdd,this,["min", "مینیمم"])
						}
					]
				}
			},{
				text: 'اضافه آیتم به ستون های گروهبندی',
				handler: Ext.bind(this.AddFieldToColumns,this,["lst_reportGroups", "group"])
			},{
				text: 'اضافه آیتم به ستون های مرتب سازی',
				handler: Ext.bind(this.AddFieldToColumns,this,["lst_reportOrder", "order"])
			},{
				text: 'اضافه آیتم به ستون تفکیک',
				handler: Ext.bind(this.AddFieldToColumns,this,["lst_reportSeparation", "separation"])
			},{
				text: 'اضافه آیتم به ستون فیلتر',
				handler: Ext.bind(this.AddFieldToColumns,this,["lst_reportFilter", "filter"])
			},{
				text: 'اضافه آیتم به فرمول',
				handler: Ext.bind(this.formulaAdd,this)
			},{
				text : "اضافه به شرط گزارش",
				handler: Ext.bind(function(){

					var node = this.tree.getSelectionModel().getSelection()[0];
					if(!node.isLeaf())
					{
						alert("گره انتخابی آیتم معتبری نمی باشد");
						return;
					}
					var index = this.conditionColumn.length;
					this.conditionColumn[index] = new Array("[" + index + "-" + node.data.text + "]", "[" + node.data.id + "]");
					this.get("txt_condition").value += "[" + index + "-" + node.data.text + "]";

				},this)
			}
		]
	});

	this.tree.on("itemcontextmenu", function(view, record, item, index, e){

		var coords = e.getXY();

		e.stopEvent();
		e.preventDefault();
		view.select(index);
		buildReportObject.menu.showAt([coords[0]-200, coords[1]]);

	});

	new Ext.panel.Panel({
		renderTo: this.get("reportColumnsDIV"),
		contentEl : this.get("reportColumnsPNL"),
		title : "ستون های ساده گزارش",
		width : 143,
		autoHeight : true,
		bodyBorder: false
	});

	new Ext.panel.Panel({
		renderTo: this.get("reportSPColumnsDIV"),
		contentEl : this.get("reportSPColumnsPNL"),
		title : "ستون های خاص گزارش",
		width : 143,
		autoHeight : true,
		bodyBorder: false
	});

	new Ext.panel.Panel({
		renderTo: this.get("reportGroupsDIV"),
		contentEl : this.get("reportGroupsPNL"),
		title : "گروهبندی بر اساس",
		width : 143,
		autoHeight : true,
		bodyBorder: false
	});

	new Ext.panel.Panel({
		renderTo: this.get("reportOrderDIV"),
		contentEl : this.get("reportOrderPNL"),
		title : "مرتب سازی بر اساس",
		width : 143,
		autoHeight : true,
		bodyBorder: false
	});

	new Ext.panel.Panel({
		renderTo: this.get("reportSeparationDIV"),
		contentEl : this.get("reportSeparationPNL"),
		title : "تفکیک بر اساس",
		width : 143,
		autoHeight : true,
		bodyBorder: false
	});

	new Ext.panel.Panel({
		renderTo: this.get("reportFilterDIV"),
		contentEl : this.get("reportFilterPNL"),
		title : "فیلتر بر اساس",
		width : 143,
		autoHeight : true,
		bodyBorder: false
	});

	new Ext.form.FieldSet({
		title :  "ساخت ستون های فرمولی",
		renderTo : this.get("reportMakeFormulaDIV"),
		contentEl : this.get("reportMakeFormulaPNL"),
		autoHeight : true,
		width : 650
	});

	new Ext.form.FieldSet({
		title : "ساخت شرط گزارش",
		renderTo : this.get("reportWhereDIV"),
		contentEl : this.get("reportWherePNL"),
		autoHeight : true,
		width : 650
	});
	

	//-------------- fill up data members for editing reports ------------------

	var elem = this.get('lst_reportGroups');
	for(i=0; i < elem.options.length; i++)
		this.groupColumns += elem.options[i].value + ",";

	var elem = this.get('lst_reportOrder');
	for(i=0; i < elem.options.length; i++)
		this.orderColumns += elem.options[i].value + ",";

	var elem = this.get('lst_reportSeparation');
	for(i=0; i < elem.options.length; i++)
		this.separationColumns += elem.options[i].value + ",";

	var elem = this.get('lst_reportFilter');
	for(i=0; i < elem.options.length; i++)
		this.filterColumns += elem.options[i].value + ",";
}

var buildReportObject = new buildReport();

//------------------------- Formula Modules ----------------------------------

buildReport.prototype.addFormula = function()
{
	var txt_fldName = this.get("mf_title");
	var txt_formula = this.get("txt_formula");

	if(txt_fldName.value == "" )
	{
		alert("ورود عنوان فيلد الزامي است");
		txt_fldName.focus();
		return false;
	}
	if(txt_formula.value == "")
	{
		txt_formula.focus();
		alert("ورود فرمول الزامي است");
		return false;
	}

	for(i=0; i < this.formulaColumns.length; i++)
	{
		var arr = this.formulaColumns[i];
		txt_formula.value = txt_formula.value.replace(arr[0], arr[1]);
	}
		
	Ext.Ajax.request({
		url : this.address_prefix + "../data/report.data.php",
		method : "POST",
		params : {
			task : "addReportColumn",
			report_id : this.report_id,
			parent_path : txt_formula.value,
			column_id : "",
			field_title : txt_fldName.value,
			used_type : "formula",
			base_evaluate : this.get("base_evaluate").checked ? "1" : "0"
		},
		success : function(response)
		{
			if(response.responseText == "true")
			{
				buildReportObject.col_dg.getStore().load();
				txt_formula.value = "";
				txt_fldName.value = "";
				buildReportObject.formulaColumns = new Array();
			}
		}
	});
}

//------------------------------------------------------------------------------

buildReport.prototype.deleteItem = function(e, elem)
{
	var c = (e.keyCode)? e.keyCode: (e.charCode)? e.charCode: e.which;

	if(e.keyCode == 46) // press delete on an item
	{
		var LST = this.get(elem);
		if(LST.selectedIndex != -1)
		{
			var relatedDataMember;
			switch(elem.id)
			{
				case "lst_reportColumns" : relatedDataMember = "basicColumns"; break;
				case "lst_reportSummary" : relatedDataMember = "summaryColumns"; break;
				case "lst_reportGroups" : relatedDataMember = "groupColumns"; break;
				case "lst_reportOrder" : relatedDataMember = "orderColumns"; break;
				case "lst_reportSeparation" : relatedDataMember = "separationColumns"; break;
				case "lst_reportFilter" : relatedDataMember = "filterColumns"; break;
			}
			eval("var st = this." + relatedDataMember + ".split(',');");

			var HDN = "";
	        for(i=0; i < st.length-1; i++)
	            if(i != LST.selectedIndex)
	                HDN += st[i] + ",";

	        LST.remove(LST.selectedIndex);
			eval("this." + relatedDataMember + " = HDN;");
		}
	}
}

buildReport.prototype.CreateReport = function()
{
	if(this.get("report_title").value == "")
	{
		alert("ورود عنوان گزارش الزامی است");
		return;
	}

	var conditions = this.get("txt_condition").value;
	for(i=0; i < this.conditionColumn.length; i++)
	{
		var arr = this.conditionColumn[i];
		while(conditions.indexOf(arr[0]) != -1)
			conditions = conditions.replace(arr[0], arr[1]);
	}

	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg: 'در حال ساخت گزارش...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/report.data.php',
		form: this.form,
		params : {
			task: "save",
			basicColumns : this.basicColumns,
			summaryColumns : this.summaryColumns,
			groupColumns :	this.groupColumns,
			orderColumns : this.orderColumns,
			separationColumns : this.separationColumns,
			filterColumns : this.filterColumns,

			formulas : this.formulas,
			conditions : conditions
		},
		method: "POST",

		success:function(response,options)
		{
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("گزارش با موفقیت ساخته شد");
				buildReportObject.get("report_id").value = st.data;
				return;
			}
			if(st.data == "designError")
			{
				alert("فیلدهای که انتخاب کرده اید با یکدیگر رابطه ایی ندارند و گزارش نتیجه مطلوب نخواهد داشت.\n" +
				"برای نتیجه صحیح فیلدهای انتخابی را اصلاح کنید و مجدد گزارش را بسازید");
				return;
			}
			else
				alert("عملیات با شکست مواجه شد");
		}
	});

}

buildReport.prototype.preview = function()
{
	if(this.get("report_id").value == "")
	{
		alert("برای پیش نمایش گزارش ابتدا باید گزارش را ایجاد کنید");
		return;
	}
	framework.OpenPage(this.address_prefix + "reportResult.php","پیش نمایش " + this.get("report_title").value,
			{
				Q0 : this.get("report_id").value,
				preview : "true"
			});
}

//------------------------------------------------------------------------------
buildReport.prototype.UPRender = function(v,p,r)
{
	if(this.col_dg.getStore().indexOf(r) == 0)
		return "";
	return "<div align='center' class='up' onclick='buildReportObject.changeOrder(\"up\");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

buildReport.prototype.DOWNRender = function(v,p,r)
{
	if(this.col_dg.getStore().getAt(this.col_dg.getStore().getCount()-1).data.row_id == r.data.row_id)
		return "";
	return "<div align='center' class='down' onclick='buildReportObject.changeOrder(\"down\");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

buildReport.prototype.changeOrder = function(direction)
{
	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال انجام عملیات...'});
	mask.show();

	var record = this.col_dg.getSelectionModel().getLastSelected();

	Ext.Ajax.request({
	  	url : this.address_prefix + "../data/report.data.php",
	  	method : "POST",
	  	params : {
	  		task : "ChangeColumnOrder",
	  		cur_row_id : record.data.row_id,
	  		sec_row_id : (direction == "up") ? this.col_dg.getStore().getAt(this.col_dg.getStore().indexOf(record)-1).data.row_id :
	  										  this.col_dg.getStore().getAt(this.col_dg.getStore().indexOf(record)+1).data.row_id
	  	},
	  	success : function()
	  	{
	  		buildReportObject.col_dg.getStore().load();
			mask.hide();
	  	}
	});
}

buildReport.prototype.deleteRender = function()
{
	return "<div align='center' title='حذف' class='remove' onclick='buildReportObject.DeleteColumn();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

buildReport.prototype.DeleteColumn = function()
{
	var record = this.col_dg.getSelectionModel().getLastSelected();
	if(record && confirm("آیا مایل به حذف می باشید؟"))
	{
		Ext.Ajax.request({
		  	url : this.address_prefix + "../data/report.data.php",
		  	method : "POST",
		  	params : {
		  		task : "deleteColumn",
		  		row_id : record.data.row_id
		  	},
		  	success : function()
		  	{
		  		buildReportObject.col_dg.getStore().load();
		  	}
		});
	}
}

buildReport.prototype.editRender = function(v,p,r)
{
	if(r.data.used_type != 'formula')
		return "";
	
	return "<div align='center' title='ویرایش' class='edit' onclick='buildReportObject.EditFormula();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

buildReport.prototype.EditFormula = function()
{
	if(!this.editFormulaWin)
	{
		this.editFormulaWin = new Ext.Window({
			applyTo:'efWin',
			title: "تغییر فرمول",
			contentEl : "efPanel",
			layout:'fit',
			modal: true,
			width : 400,
			autoHeight : true,
			closeAction:'hide',
			buttons : [{
				text : "ذخیره",
				iconCls : "save",
				handler : function(){
					var record = this.col_dg.getSelectionModel().getLastSelected();
					Ext.Ajax.request({
						url : this.address_prefix + "../data/report.data.php",
						method : "POST",
						params : {
							task : "editFormula",
							row_id : record.data.row_id,
							
						},
						success : function()
						{
							buildReportObject.col_dg.getStore().load();
						}
					});
				}
			},{
				text : "انصراف",
				handler: function(){ByLawObject.newItemWin.hide();}
			}]
		});
	}
	
}

</script>