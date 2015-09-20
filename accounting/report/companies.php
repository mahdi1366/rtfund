<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.02
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	$query = "
		select c.*,	
			sum(total) - sum(TotalDiscount) totalSum,
			sum(factorTax) factorTax,
			sum(sumTaxInclude) sumTaxInclude,
			sum(sumTaxFree) sumTaxFree
		from store_docs s 
		join (
			select s.docID, round(sum(if(doc_type=20,1,-1)*(itemCount*(buyPrice - buyPrice * ifnull(si.discount,0)/100)))) total,
				case taxAmount
					when 0 then 0
					when 1 then round(sum(if(s.doc_type=23,-1,1)*if(PayTax=1,
						(buyPrice - buyPrice * if(discount is null,0,discount)/100) * itemCount * 0.08,0)))
					when 2 then round(sum(if(s.doc_type=23,-1,1)*if(PayTax=1, buyPrice* itemCount * 0.08, 0)))
				end - ifnull(TaxDiscount,0) factorTax,

				round(sum( if(taxInclude=1 AND PayTax=1,itemCount*(buyPrice - buyPrice * ifnull(si.discount,0)/100) ,0) ) ) sumTaxInclude,
				round(sum( if(taxInclude=1 AND PayTax=1, 0, itemCount*(buyPrice - buyPrice * ifnull(si.discount,0)/100) ) ) ) sumTaxFree
			from store_doc_items si
			join items i using(itemID)
			join store_docs s using(docID)
			group by s.docID
			
		) si using(docID)
		join companies c using(companyID)
		join acc_docs ad on(ad.docType='BUY' AND ad.docID=s.AccDocID)

		where doc_type in (20,23) AND s.cycleID=" . $_SESSION["CYCLE"];

	$whereParam = array();
	if(!empty($_POST["companyID"]))
	{
		$query .= " AND s.companyID=:c";
		$whereParam[":c"] = $_POST["companyID"];
	}
	if(!empty($_POST["fromDate"]))
	{
		$query .= " AND ad.docDate >= :q1 ";
		$whereParam[":q1"] = DateModules::shamsi_to_miladi($_POST["fromDate"], "-");
	}
	if(!empty($_POST["toDate"]))
	{
		$query .= " AND ad.docDate <= :q2 ";
		$whereParam[":q2"] = DateModules::shamsi_to_miladi($_POST["toDate"], "-");
	}

	$query .= " 
		group by s.companyID
		order by c.title";
	$dataTable = PdoDataAccess::runquery($query, $whereParam);

	echo "<div style=display:none>";
	echo PdoDataAccess::GetLatestQueryString();
	echo "</div>";
	
	function moneyRender($row, $value)
	{
		return number_format($value, 0, '.', ',');
	}
	
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	
	$rpg->addColumn("شرکت", "title");
	$rpg->addColumn("کد اقتصادی", "uniCode");
	$rpg->addColumn("آدرس", "address");
	$rpg->addColumn("تلفن", "tel");
	$col = $rpg->addColumn("جمع کل مبلغ", "totalSum", "moneyRender");
	$col->EnableSummary();
	$col = $rpg->addColumn("مالیات فاکتور خرید", "factorTax", "moneyRender");
	$col->EnableSummary();
	$col = $rpg->addColumn("جمع مشمول مالیات", "sumTaxInclude", "moneyRender");
	$col->EnableSummary();
	$col = $rpg->addColumn("جمع معاف از مالیات", "sumTaxFree", "moneyRender");
	$col->EnableSummary();
	
	$rpg->mysql_resource = $dataTable;
	if(!$rpg->excel)
	{
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='../img/logo3.png'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>اعتماد شما سرلوحه خدمت ماست<br>
					گزارش خلاصه خرید از شرکت ها بر اساس اسناد حسابداری
				</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			echo "<br>گزارش از تاریخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
		echo "</td></tr></table>";
	}
	$rpg->generateReport();
	die();
}
?>
<script>
report_companies.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

report_companies.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "companies.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function report_companies()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش خلاصه خرید از شرکت ها",
		defaults : {
			labelWidth :150
		},
		width : 500,
		items :[{
			xtype : "combo",
			anchor : "100%",
			//typeAhead: false,
			emptyText:'انتخاب شرکت ...',
			displayField : "title",
			fieldLabel : "انتخاب شرکت",
			valueField : "companyID",
			hiddenName : "companyID",
			pageSize:10,
			store : new Ext.data.Store({
				fields:["companyID","title"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../store/data/items.data.php?task=selectCompany',
					reader: {
						root: 'rows',
						totalProperty: 'totalCount'
					}
				}
			})
		},{
			xtype : "shdatefield",
			name : "fromDate",
			fieldLabel : "از تاریخ"
		},{
			xtype : "shdatefield",
			name : "toDate",
			fieldLabel : "تا تاریخ"
		}],
		buttons : [{
			text : "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls : "report"
		},{
			text : "خروجی excel",
			handler : Ext.bind(this.showReport,this),
			listeners : {
				click : function(){
					report_companiesObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		}]
	});
}

report_companiesObj = new report_companies();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>