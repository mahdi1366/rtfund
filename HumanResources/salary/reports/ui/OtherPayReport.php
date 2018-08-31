<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	97.05
//---------------------------
require_once("../../../header.inc.php");
if (!isset($_REQUEST["showRes"]))
	require_once '../js/OtherPayReport.js.php';
require_once "ReportGenerator.class.php";
ini_set("display_errors", "On");

if (isset($_GET['showRes']) && $_GET['showRes'] == 1)
{	
	//.................. secure section .....................
        InputValidation::validate($_REQUEST['pay_month'], InputValidation::Pattern_Num);
        InputValidation::validate($_REQUEST['pay_year'], InputValidation::Pattern_Num);

	$whereParam = array();
	$whr = "";
	if (!empty($_POST["pay_month"]))
	{
		$whr .= " AND pit.pay_month = :PM ";
		$whereParam[":PM"] = $_POST["pay_month"];
	}
	if (!empty($_POST["pay_year"]))
	{
		$whr .= " AND pit.pay_year=:PY";
		$whereParam[":PY"] = $_POST["pay_year"];
	}
	if (!empty($_POST["PayType"]))
	{
		$whereParam[":PT"] = $_POST["PayType"];
	}

	if ($_POST["pay_month"] < 7)
		$dayNo = 31;
	elseif ($_POST["pay_month"] > 6 && $_POST["pay_month"] < 12)
		$dayNo = 30;
	else
		$dayNo = 29;

	$qry = " select InfoID,InfoDesc month_title 
                        from  Basic_Info bi 
                                where  bi.typeid = 78 AND InfoID = ? ";
	$res = PdoDataAccess::runquery($qry, array($_POST["pay_month"]));
	$month = $res[0]['month_title'];

	$query = "  select  p.staff_id,pr.pfname  , pr.plname , 
                            pr.national_code,s.account_no ,
                            SUM(pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) payVal ,
                            SUM(pit.get_value + pit.diff_get_value * pit.diff_value_coef) getVal ,
                            SUM(( pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) - pit.get_value + pit.diff_get_value * pit.diff_value_coef ) PurePay
		          "
                . " from HRM_payments p "
                . "      inner join HRM_payment_items pit "
                . "                     on p.pay_year = pit.pay_year and "
                . "                        p.pay_month = pit.pay_month and "
                . "                        p.staff_id = pit.staff_id and "
                . "                        p.payment_type = pit.payment_type
                      inner join HRM_staff s on s.staff_id = p.staff_id 
                      inner join HRM_persons pr on s.PersonID = pr.PersonID 
                    where p.pay_year = :PY and p.pay_month = :PM and p.payment_type = :PT and p.payment_type <> 1 "
                . " group by p.staff_id " ; 

		$dataTable = PdoDataAccess::runquery($query, $whereParam);


		if (!empty($_GET['excel']) && $_GET['excel'] == 'true')
		{


			require_once 'excel.php';
			require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
			require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";

			$workbook = &new writeexcel_workbook("/tmp/temp.xls");
			$worksheet = & $workbook->addworksheet("Sheet1");
			$heading = & $workbook->addformat(array('align' => 'center', 'bold' => 1, 'bg_color' => 'blue', 'color' => 'white'));

			$worksheet->write(0, 0, "ردیف", $heading);
			$header = array("شماره شناسایی ",
				"نام",
				"نام خانوادگی",
				" کدملی",
				"اصل مبلغ",
				" مالیات",
				"خالص پرداختی"
			);

			for ($i = 0; $i < count($header); $i++)
			{
				$worksheet->write(0, $i + 1, $header[$i], $heading);
			}

			$content = array("staff_id", "pfname", "plname", "national_code", "payVal", "getVal", "PurePay");

			for ($index = 0; $index < count($dataTable); $index++)
			{
				$row = $dataTable[$index];

				$worksheet->write($index + 1, 0, ($index + 1));

				for ($i = 0; $i < count($content); $i++)
				{
					$val = "";
					/* if(!empty($this->columns[$i]->renderFunction))
					  eval("\$val = " . $this->columns[$i]->renderFunction . "(\$row,\$row[\$this->columns[\$i]->field]);");
					  else */
					$val = $row[$content[$i]];
					$val = ( is_int($val) ) ? round($val) : $val;
					$worksheet->write($index + 1, $i + 1, $val);
				}
			}

			$workbook->close();

			header("Content-type: application/ms-excel");
			header("Content-disposition: inline; filename=excel.xls");

			echo file_get_contents("/tmp/temp.xls");
			unlink("/tmp/temp.xls");
			die();
		}
	 
	?>
	<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
	</style>

	<?php
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';

	$HedearTitle = "";
	
	$qry = " select InfoDesc from Basic_Info where typeID = 72 and infoId = ? ";
        $res = PdoDataAccess::runquery($qry, array($_POST['PayType']));
        $HedearTitle = $res[0]['InfoDesc'];

	echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' width='110px'height = '110px'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پرداخت   " . '&nbsp; ' . $HedearTitle . '&nbsp;' . $month . " ماه " .
	$_POST['pay_year'] . " </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>	تاریخ تهیه گزارش : 
"
	. DateModules::shNow()  ;
	echo "</td></tr></table>";
			echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
				<tr class="header">					
					<td>ردیف </td>
					<td>شماره شناسایی</td>
					<td>نام </td>
					<td> نام خانوادگی </td>	<td> کدملی</td>			
					<td> اصل مبلغ </td>	
					<td> مالیات </td>
					<td> خالص پرداختی </td><td> شماره حساب</td>
				</tr>';
		$sum = $sumpayVal = $sumTempVal = $sumgetVal = 0;
		$sumMelli = $sumTejarat = $sumOthers = 0;

		for ($i = 0; $i < count($dataTable); $i++)
		{
			if ($i > 0 && $i % 44 == 0)
			{
				echo '</table><hr style="page-break-after:always; visibility: hidden"><br><br>';
				echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
						<tr class="header">					
							<td>ردیف </td>
							<td>شماره شناسایی</td>
							<td>نام </td>
							<td> نام خانوادگی </td>	
                                                        <td> کدملی</td>	
							<td> اصل مبلغ </td>							
							<td> مالیات </td>
							<td> خالص پرداختی </td><td> شماره حساب</td>
						</tr>';
			}
			echo " <tr>
			    <td>" . ( $i + 1 ) . "</td>
			    <td>" . $dataTable[$i]['staff_id'] . "</td> 
			    <td>" . $dataTable[$i]['pfname'] . "</td>
			    <td>" . $dataTable[$i]['plname'] . "</td>	
                                <td>" . $dataTable[$i]['national_code'] . "</td>
				<td>" . number_format($dataTable[$i]['payVal'], 0, '.', ',') . "</td>				
				<td>" . number_format($dataTable[$i]['getVal'], 0, '.', ',') . "</td>	
				<td>" . number_format(($dataTable[$i]['payVal'] - $dataTable[$i]['getVal']), 0, '.', ',') . "</td>
				<td>" . $dataTable[$i]['account_no'] . "</td>			   					
		    </tr>";
			$sumpayVal += $dataTable[$i]['payVal'];			 
			$sumgetVal += $dataTable[$i]['getVal'];
			
			$sumOthers += $dataTable[$i]['payVal'] - $dataTable[$i]['getVal']  ;
			$sum += $dataTable[$i]['payVal'] - $dataTable[$i]['getVal'] ;
		}
		echo "  <tr style='font-weight:bold' ><td colspan='5' align='left' >جمع: </td>
				<td colspan='1' >" . number_format($sumpayVal, 0, '.', ',') . "</td>				
				<td colspan='1' >" . number_format($sumgetVal, 0, '.', ',') . "</td>
				<td colspan='1' >" . number_format($sum, 0, '.', ',') . "</td></tr>";

		
		echo "</table></center>";
	
}
?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>