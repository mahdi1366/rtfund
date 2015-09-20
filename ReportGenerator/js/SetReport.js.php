<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// Date:		90.04
//---------------------------

SetReport.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	report_id : "<?= $report_id?>",
	report_name : "<?= $report_name?>",


	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function SetReport()
{
	this.form = this.get("form_SetReport");
	this.setReportPanel = new Ext.Panel({
		applyTo: this.get("SetReportDIV"),
		frame : true,
		bodyStyle : "padding:5px",
		style : "margin-top:5px",
		contentEl : this.get("SetReportPNL"),
		title : "تنظیم گزارش",
		width : 900,
		buttons:[{
			text : "مشاهده گزارش",
			iconCls : "list",
			handler : function(){SetReportObject.showResult('show');}
		},{
			text : "خروجی excel",
			iconCls : "excel",
			handler : function(){SetReportObject.showResult('excel');}
		}]
	});

	Ext.get(this.get("SetReportPNL")).addKeyListener(13, function(){SetReportObject.showResult('show');});

	<?=$triggerComponents?>
}

var SetReportObject = new SetReport();

SetReport.prototype.showResult = function(type)
{

	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "reportResult.php?Q0=" + this.report_id + "&Q1=" + this.report_name;
	this.form.action += type == "excel" ? "&excel=true" : "";
    this.form.action += "<?= (isset($_REQUEST['Param1'])) ? "&Param1=".$_REQUEST['Param1'] : ""  ?>"  ;
	this.form.submit();
	return;
	/*var mask = new Ext.LoadMask(this.TabID, {msg:'در حال تهیه گزارش...'});
	mask.show();
	this.get("result").style.display = "";

	Ext.Ajax.request({
		url : this.address_prefix + "reportResult.php",
		method : "POST",
		params : {
			Q0 : this.report_id
			//reportType
		},
		form : this.form,

		success : function(response){

			SetReportObject.get("result").innerHTML = response.responseText;
			mask.hide();
			SetReportObject.setReportPanel.collapse();
		}
	});*/
	/*framework.OpenPage(this.address_prefix + "reportResult.php", "نتیجه گزارش " + this.report_name ,
	{
		Q0 : this.report_id
	});
	var qs = Ext.Ajax.serializeForm(document.getElementById("MainForm"));
	if(type == 1)
		window.open("../ReportGenerator/reportResult.php?Q0=" + "< ?= $_POST["Q0"] ?>&" + qs);
	else if(type == 2)
		window.open("../ReportGenerator/reportResult.php?pp= " + document.getElementById("recordCount").value +
			"&Q0=" + "< ?= $_POST["Q0"] ?>&" + qs);
	else
		window.open("../ReportGenerator/reportResult.php?form=true&Q0=< ?= $_POST["Q0"] ?>&" + qs);*/
}

</script>