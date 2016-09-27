<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.12
//-----------------------------

require_once '../header.inc.php';
require_once '../global/ManageReport.class.php';
require_once inc_CurrencyModule;

if(isset($_REQUEST["print"]))
{
	$TafsiliID = !empty($_REQUEST["TafsiliID"]) ? $_REQUEST["TafsiliID"] : "";
	$param = array();
	if($TafsiliID != "")
		$param[] = $TafsiliID;
	
	//-------------- total ------------------
	$query = "select sum(CreditorAmount-DebtorAmount) amount, 
		round(sum(CreditorAmount-DebtorAmount) /" . ShareBaseAmount . ") shareCount
				
	from ACC_DocItems 
		join ACC_docs using(DocID)
		where CostID=" . COSTID_share . " AND CycleID=" . $_SESSION["accounting"]["CycleID"];
	$sumRecord = PdoDataAccess::runquery($query, $param);
	if(count($sumRecord) == 0)
	{
		echo "<center><h2>" . "فاقد اطلاعات" . "</h2></center>";
		die();
	}
	$sumRecord = $sumRecord[0];
	//------------------------------------------
	
	$query = "select sum(CreditorAmount-DebtorAmount) amount, TafsiliDesc ,
			ShareNo,
		round(sum(CreditorAmount-DebtorAmount) /" . ShareBaseAmount . ") shareCount
				
	from ACC_DocItems 
		join ACC_docs using(DocID)
		join ACC_tafsilis using(TafsiliID)
		join BSC_persons on(ObjectID=PersonID)
	where CostID=" . COSTID_share . " AND CycleID=" . $_SESSION["accounting"]["CycleID"] . 
	($TafsiliID != "" ? " AND TafsiliID=?" : "") .
	" group by TafsiliID";

	$query .= " order by amount desc";
	$dataTable = PdoDataAccess::runquery($query, $param);
	
	if(count($dataTable) == 0)
	{
		echo "<center><h2>" . "فاقد اطلاعات" . "</h2></center>";
		die();
	}
?>
<html>
	<head>
		<META http-equiv=Content-Type content="text/html; charset=UTF-8" >
		<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" /></head>
		<style>
		@media print {
			.pageBreak {page-break-before:always;height:1px;}
		}
		.page {
			width: 245mm;
			height: 160mm;
			margin : 2cm 2cm 0 2cm;
		}
		td {
			font-family: IranNastaliq;
			font-size: 28px;
		}
		</style>
	</head>
	<body dir="rtl">
<?
	for($i=0; $i<count($dataTable);$i++)
	{
		echo "<div class=page><table width=100%>
			<tr>
				<td width=160px  style='vertical-align: top'>
					<img style=width:120px src=/framework/icons/logo.jpg />
				</td>
				<td align=center style='font-size:30px !important'>بسمه تعالی
					<br>برگ سهام " . SoftwareName . "(سهامی خاص)" . "
					<span style=font-size:28px!important;><B><br>سرمایه ثبت شده : " . 
						CurrencyModulesclass::CurrencyToString($sumRecord["amount"]) . " ریال که تماما پرداخت گردیده است
						<br>منقسم به " . CurrencyModulesclass::CurrencyToString($sumRecord["shareCount"]) . " سهم " . 
						CurrencyModulesclass::CurrencyToString(ShareBaseAmount) . " ریالی
					</b><br><br>
					</span>
				</td>
				<td width=160px style='font-family:Homa;font-size: 12px;vertical-align: top'>شناسه ملی : " . OWNER_NATIONALID . "
					<br>شماره ثبت : " . OWNER_REGCODE . "
					<br>تاریخ ثبت : " . OWNER_REGDATE . "
					<br><img src='/generalClasses/phpqrcode/showQRcode.php?value=" .
						$dataTable[$i]["TafsiliDesc"] . " با سهام " . 
						number_format($dataTable[$i]["amount"]) . " ریال' >
				</td>
			</tr>
			<tr>
				<td colspan=3 align=center style='text-align: justify;padding: 0 5 0 5'>
				بدینوسیله گواهی می شود تعداد
				<b><u> " . $dataTable[$i]["shareCount"] . " </u></b>( " . CurrencyModulesclass::CurrencyToString($dataTable[$i]["shareCount"]) . " ) 
				سهم با نام از مجموع 
				<b><u>" . $sumRecord["shareCount"] . "</u></b> ( " . 
				CurrencyModulesclass::CurrencyToString($sumRecord["shareCount"]) . " ) 
				سهم 
				" . SoftwareName . " به ارزش اسمی هر سهم " . 
				"<b><u>" . number_format(ShareBaseAmount) . "</u></b> ( " . CurrencyModulesclass::CurrencyToString(ShareBaseAmount) . " ) ریال و به ارزش 
				<b><u>" . number_format($dataTable[$i]["amount"]) . "</u></b> ( " . CurrencyModulesclass::CurrencyToString($dataTable[$i]["amount"]) . " ) 
				ریال، به عنوان سهام عادی و با نام متعلق به 
				<b>" . $dataTable[$i]["TafsiliDesc"] . "</b>
				می باشد و سهام مذکور در دفتر ثبت سهام تحت شماره
				<b><u> &nbsp;&nbsp;" . $dataTable[$i]["ShareNo"] . "</u></b> &nbsp;( " . CurrencyModulesclass::CurrencyToString($dataTable[$i]["ShareNo"]) . " ) &nbsp;&nbsp; 
				ثبت گردیده است.
				<br><br>
				</td>
			</tr>
			<tr>
				<td style=padding-right:40px align=center>رسول عبدالهی<br> مدیر عامل</td>
				<td align=center>مهر صندوق</td>
				<td style=padding-left:40px align=center>دکتر جواد بهارآرا <br> رئیس هیئت مدیره</td>
			</tr>
		</table></div>";
		
		echo "<div style='width:285mm;font-family:Homa;font-size:12px'>
			<center>با صدور این برگ اوراق صادره قبلی باطل اعلام می گردد.( تاریخ صدور : " .
			DateModules::shNow(). " )</center></div>";
		
		if($i != count($dataTable)-1)
			echo Manage_Report::PageBreak();
	}
	echo "</center></body></html>";
	die();
}

?>
<script>
PrintShare.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	DocID : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function PrintShare()
{
	this.mainPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "چاپ برگه سهام",
		width : 400,
		items :[{
			xtype : "combo",
			width : 380,
			store: new Ext.data.Store({
				fields:["TafsiliID","TafsiliDesc"],
				proxy: {
					type: 'jsonp',
					url: '/accounting/baseinfo/baseinfo.data.php?task=GetAllTafsilis&TafsiliType=1&Shareholder=true',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			emptyText:'انتخاب تفصیلی ...',
			typeAhead: false,
			pageSize : 10,
			valueField : "TafsiliID",
			itemId : "TafsiliID",
			displayField : "TafsiliDesc"			
		}],
		buttons : [{
			text : "چاپ برگه سهام",
			iconCls : "print",
			handler : function()
			{
				str = PrintShareObj.mainPanel.down("[itemId=TafsiliID]").getValue() != null ?
					"&TafsiliID=" + PrintShareObj.mainPanel.down("[itemId=TafsiliID]").getValue() : "";
				window.open(PrintShareObj.address_prefix + "PrintShare.php?print=true" + str);
			}
		}]
	});
}

PrintShareObj = new PrintShare();

</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div><br>
		<div><div id="regDocDIV"></div></div>
	</center>
</form>