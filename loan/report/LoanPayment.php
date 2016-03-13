<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.12
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";
require_once '../request/request.data.php';

//echo DateModules::GDateMinusGDate(DateModules::shamsi_to_miladi("1393-03-12"),
//		DateModules::shamsi_to_miladi("1392-12-29"));

if(isset($_REQUEST["show"]))
{
	$PartID = $_REQUEST["PartID"];
	
	$dt = LON_installments::SelectAll("PartID=?" , array($PartID));
	$returnArr = ComputePayments($PartID, $dt);
	
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	function dateRender($row, $val){
		return DateModules::miladi_to_shamsi($val);
	}	
	
	function amountRender($row, $val){
		return number_format($val);
	}
	
	$col = $rpg->addColumn("تاریخ قسط", "InstallmentDate","dateRender");
	$col->rowspaning = true;
	
	$col = $rpg->addColumn("مبلغ قسط", "InstallmentAmount","amountRender");
	$col->rowspaning = true;
	$col->rowspanByFields = array("InstallmentDate");
	
	$rpg->addColumn("تاریخ پرداخت", "PayDate","dateRender");
	
	$col = $rpg->addColumn("مبلغ پرداخت", "PayAmount","amountRender");
	$col->EnableSummary();
	
	$rpg->addColumn("تعداد روز تاخیر", "ForfeitDays");
	$col = $rpg->addColumn("جریمه", "ForfeitAmount","amountRender");
	$col->EnableSummary();
	
	$rpg->addColumn("مانده قسط", "remainder","amountRender");
	$rpg->addColumn("مانده کل", "TotalRemainder","amountRender");
	
	$rpg->mysql_resource = $returnArr;
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
	echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
			<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
			<td align='center' style='height:100px;vertical-align:middle;font-family:b titr;font-size:15px'>
				گزارش پرداخت وام
			</td>
			<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		. DateModules::shNow() . "<br>";
	
	echo "</td></tr></table>";
	$rpg->generateReport();
	die();
}
?>
<script>
LoanReport_payments.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

LoanReport_payments.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "LoanPayment.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function LoanReport_payments()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش اسناد",
		defaults : {
			labelWidth :120
		},
		width : 650,
		items :[{
			xtype : "combo",
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + '../request/request.data.php?task=selectParts',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['loanFullname','PartAmount','PartDesc',"RequestID","PartDate", "PartID",{
					name : "fullTitle",
					convert : function(value,record){
						return "[ " + record.data.RequestID + " ] " + record.data.loanFullname + 
							+ " " + record.data.PartDesc + " به مبلغ " + 
							Ext.util.Format.Money(record.data.PartAmount) + " مورخ " + 
							MiladiToShamsi(record.data.PartDate);
					}
				}]
			}),
			displayField: 'fullTitle',
			valueField : "PartID",
			hiddenName : "PartID",
			width : 600,
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct" style="height: 23px;">',
				'<td style="padding:7px">کد وام</td>',
				'<td style="padding:7px">وام گیرنده</td>',
				'<td style="padding:7px">فاز وام</td>',
				'<td style="padding:7px">مبلغ وام</td>',
				'<td style="padding:7px">تاریخ پرداخت</td> </tr>',
				'<tpl for=".">',
					'<tr class="x-boundlist-item" style="border-left:0;border-right:0">',
					'<td style="border-left:0;border-right:0" class="search-item">{RequestID}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{loanFullname}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{PartDesc}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">',
						'{[Ext.util.Format.Money(values.PartAmount)]}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{[MiladiToShamsi(values.PartDate)]}</td> </tr>',
				'</tpl>',
				'</table>'
			),
			itemId : "PartID"
		}],
		buttons : [{
			text : "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls : "report"
		}]
	});
}

LoanReport_paymentsObj = new LoanReport_payments();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>