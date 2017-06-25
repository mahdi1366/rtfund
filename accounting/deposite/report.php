<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------
 
require_once '../header.inc.php';
require_once '../docs/import.data.php';

$IsFlow = false;
if(isset($_REQUEST["IsFlow"]) && $_REQUEST["IsFlow"] == "true")
{
	$ToDate = DateModules::Now();
	$IsFlow = true;
}
else
	$ToDate = DateModules::shamsi_to_miladi($_REQUEST["ToDate"]);

$dataTable = ComputeDepositeProfit($ToDate, array($_REQUEST["TafsiliID"]), true, $IsFlow);
$dataTable = $dataTable[$_REQUEST["TafsiliID"]];

echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
echo '<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" />';
echo "<style>
		table { border-collapse:collapse; width:100%}
		#header {background-color : blue; color : white; font-weight:bold}
		#footer {background-color : #bbb;}
		td {font-family : nazanin; font-size:16px; padding:4px}
	</style>";
echo "<table></table>";
echo "<table border=1>
	<tr id=header>
		<td>تاریخ</td>
		<td>شرح</td>
		<td>مبلغ گردش</td>
		<td>مانده حساب</td>
		<td>تعداد روز</td>
		<td>درصد</td>
		<td>سود</td>
	</tr>";
$amount = 0;
$sumProfit = 0;
for($i=0; $i<count($dataTable); $i++)
{
	$row = $dataTable[$i];
	
	$amount += $row["row"]["amount"]*1;
	$sumProfit += $row["profit"]*1;
	echo "<tr>
			<td>" . DateModules::miladi_to_shamsi($row["row"]["DocDate"]) . "</td>
			<td>" . $row["row"]["DocDesc"] . "</td>
			<td>" . number_format($row["row"]["amount"]) . "</td>
			<td>" . number_format($amount) . "</td>
			<td>" . $row["days"] . "</td>
			<td>" . $row["percent"] . "</td>
			<td>" . number_format($row["profit"]) . "</td>
		</tr>";
}
echo "<tr id=footer>
		<td colspan=6>جمع</td>
		<td>" . number_format($sumProfit) . "</td>
	</tr>";
echo "</table>";

?>
