<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// Date:		90.04
//---------------------------

SearchReport.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",
	
	ReportStore : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function SearchReport()
{
	this.form = this.get("form_searchReport");
	this.ReportStore = <?= dataReader::MakeStoreObject($js_prefix_address . "../data/report.data.php?task=select"
	,"'report_id','report_title'") ?>;
			
	this.reportCombo = new Ext.form.ComboBox({
		store: this.ReportStore,
		emptyText:'جستجوي گزارش بر اساس عنوان گزارش ...',
		typeAhead: false,
		loadingText: 'در حال جستجو...',
		pageSize:10,
		width: 591,
		applyTo: this.get('RPT'),
		hiddenName : "report_id",
		valueField : "report_id",
		fieldLabel : "جستجوی فرد"

		,tpl: new Ext.XTemplate(
				'<tpl for=".">',
				'<div class="x-boundlist-item">',
				'[{report_id}] : {report_title}</div>',
				'</tpl>',
				'</td></tr></table>'),
		listeners :{
			select : function(combo, records){
				this.setValue(records[0].data.report_title);
				SearchReportObject.get('report_id').value = records[0].data.report_id;
				this.collapse();
			}
		}
	});
	new Ext.panel.Panel({
		renderTo: "div_select",
		contentEl : "pnl_select",
		title: "انتخاب گزارش",
		width: 650,
		frame: true,
		buttons : [{
			text : "ایجاد گزارش",
			iconCls : "add",
			handler : function(){SearchReportObject.addReport()}
		},{
			text : "ویرایش گزارش",
			iconCls : "edit",
			handler : function(){SearchReportObject.editReport()}
		},{
			text : "حذف گزارش",
			iconCls : "remove",
			handler : function(){SearchReportObject.deleteReport()}
		},{
			text : "مشاهده گزارش",
			iconCls : "view",
			handler : function(){SearchReportObject.setReport()}
		},{
			text : "تعیین فرم گزارش",
			handler : function(){SearchReportObject.setFormReport()}
		}]
	});
}

var SearchReportObject = new SearchReport();

SearchReport.prototype.addReport = function()
{
	framework.OpenPage(this.address_prefix + "buildReport.php","ایجاد گزارش");
}

SearchReport.prototype.editReport = function()
{
	if(this.get("report_id").value == "")
	{
		alert("ابتدا گزارش مورد نظر خود را انتخاب کنید");
		return;
	}
	framework.OpenPage(this.address_prefix + "buildReport.php","ویرایش  گزارش",{Q0 : this.get("report_id").value});
}

SearchReport.prototype.deleteReport = function()
{
	if(this.get("report_id").value == "")
	{
		alert("ابتدا گزارش مورد نظر خود را انتخاب کنید");
		return;
	}

	if(!confirm("آیا مایل به حذف گزارش می باشید؟"))
		return;

	var mask = new Ext.LoadMask(this.TabID,{msg: 'در حال حذف ...'});
	mask.show();
	Ext.Ajax.request({
		url: this.address_prefix + '../data/report.data.php',
		params: {
			task: "delete",
			Q0: this.get("report_id").value,
			desc: this.get("RPT").value
		},
		method: "POST",

		success:function(response, options)
		{
			mask.hide();
			SearchReportObject.reportCombo.setValue();
			SearchReportObject.ReportStore.reload();
		}
	});
}

SearchReport.prototype.setReport = function()
{
	if(this.get("report_id").value == "")
	{
		alert("ابتدا گزارش مورد نظر خود را انتخاب کنید");
		return;
	}

	framework.OpenPage(this.address_prefix + "SetReport.php", "گزارش " + this.get("RPT").value,
		{Q0 : this.get("report_id").value,
		 Q1 : this.get("RPT").value});
}

SearchReport.prototype.setFormReport = function()
{
	if(this.get("report_id").value == "")
	{
		alert("ابتدا گزارش مورد نظر خود را انتخاب کنید");
		return;
	}

	framework.OpenPage("../ReportGenerator/formReport.php?Q0=" + this.get("report_id").value);
}
</script>