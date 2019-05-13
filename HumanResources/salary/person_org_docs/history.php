<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	93.03
//---------------------------
include("../../header.inc.php");
require_once 'subtracts.class.php';

$subtract_id = $_POST["subtract_id"];

$subtractObj = new manage_subtracts($subtract_id);

$query = "
select * from (
	select flow_date,flow_coaf*amount amount,'گردش دستی' title, comments 
	from person_subtract_flows 
	where subtract_id=:sid

	union 

	select calc_date,get_value,concat('فیش حقوقی ' , pay_year , '/', if(pay_month<10,'0',''), pay_month) title , ''
	from payments join payment_items using(payment_type,staff_id,pay_year,pay_month)
	where param1 in('LOAN','FIX_FRACTION','FIX_BENEFIT') AND param2=:sid
)t
order by flow_date";
$Logs = PdoDataAccess::runquery($query, array(':sid' => $subtract_id));
//print_r(ExceptionHandler::PopAllExceptions());
$tbl_content = "";
$sum = 0;
if(count($Logs) == 0)
{
	 $tbl_content = "<tr><td> ردیف انتخابی فاقد گردش می باشد</td></tr>";
}
else 
{
	for ($i=0; $i<count($Logs); $i++)
	{
		$tbl_content .= "<tr " . ($i%2 == 1 ? "style='background-color:#efefef'" : "") . ">
			<td width=250px>[" . ($i+1) . "]". ($i+1<10 ? "&nbsp;&nbsp;" : "") . "&nbsp;
				<img align='top' src='/generalUI/ext4/resources/themes/icons/arrow-left.gif'>&nbsp;	" . $Logs[$i]["title"] . "</td>
			<td  width=150px>" . number_format($Logs[$i]["amount"],0,'.',',') . "</td>
			<td width=110px>" . DateModules::miladi_to_shamsi($Logs[$i]["flow_date"]) . "</td>
			<td><div style='cursor:pointer' class='qtip-target' data-qtip='" . $Logs[$i]["comments"] . "'>" .
				str_ellipsis($Logs[$i]["comments"], 48). "</div></td>
		</tr>";
		
		$sum += $Logs[$i]["amount"];
	}
}
?>
<style>
.infotd td{border-bottom: solid 1px #e8e8e8;padding-right:4px; height: 21px;}
</style>
<div style="background-color:white;width: 100%; height: 100%">
	<table class="infotd" width="100%" bgcolor="white" cellpadding="0" cellspacing="0">
		<?= $tbl_content ?>
		<tr style="background-color: #0061de; color: white; font-weight: bold">
			<td>جمع : </td>
			<td><?= number_format($sum,0,'.',',') ?></td>
			<td></td>
			<td></td>
		</tr>
		<?if($subtractObj->subtract_type == SUBTRACT_TYPE_LOAN){?>
		<tr style="background-color: #0061de; color: white; font-weight: bold">
			<td>مبلغ وام : </td>
			<td><?= number_format($subtractObj->first_value,0,'.',',') ?></td>
			<td></td>
			<td></td>
		</tr>
		<tr style="background-color: #0061de; color: white; font-weight: bold">
			<td>مانده : </td>
			<td><?= number_format($subtractObj->first_value - $sum,0,'.',',') ?></td>
			<td></td>
			<td></td>
		</tr>
		<?}?>
	</table>
</div>