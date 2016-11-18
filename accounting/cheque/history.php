<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	95.08
//---------------------------
include("../header.inc.php");
require_once inc_component;

$IncomeChequeID = $_POST["IncomeChequeID"];

$query = "select h.*,
				concat_ws(' ',fname, lname,CompanyName) fullname , 
				t.TafsiliDesc StatusDesc
			from ACC_ChequeHistory h 
				join ACC_tafsilis t on(t.TafsiliType=".TAFTYPE_ChequeStatus." AND StatusID=TafsiliID) 
				join BSC_persons using(PersonID) 
				where h.IncomeChequeID=?
			order by RowID ";
$Logs = PdoDataAccess::runquery($query, array($IncomeChequeID));

$tbl_content = "";

if(count($Logs) == 0)
{
	 $tbl_content = "<tr><td>فرم مورد نظر فاقد گردش می باشد</td></tr>";
}
else 
{
	for ($i=0; $i<count($Logs); $i++)
	{
		$tbl_content .= "<tr " . ($i%2 == 1 ? "style='background-color:#efefef'" : "") . ">
			<td width=250px>[" . ($i+1) . "]". ($i+1<10 ? "&nbsp;" : "") . "&nbsp;
				<img align='top' src='/generalUI/ext4/resources/themes/icons/user_comment.gif'>&nbsp;
				" . $Logs[$i]["StatusDesc"] . "</td>
			<td  width=150px>" . $Logs[$i]["fullname"] . "</td>
			<td width=110px>" . substr($Logs[$i]["ATS"], 11) . " " . 
								DateModules::miladi_to_shamsi($Logs[$i]["ATS"]) . "</td>
		</tr>";
	}
}
?>
<style>
.infotd td{border-bottom: solid 1px #e8e8e8;padding-right:4px; height: 21px;}
</style>
<div style="background-color:white;width: 100%; height: 100%">
	<table class="infotd" width="100%" bgcolor="white" cellpadding="0" cellspacing="0">
		<?= $tbl_content ?>
	</table>
</div>