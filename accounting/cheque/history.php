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
				t.TafsiliDesc StatusDesc, docs
			from ACC_ChequeHistory h 
				left join ACC_tafsilis t on(t.TafsiliType=".TAFTYPE_ChequeStatus." AND StatusID=TafsiliID) 
				join BSC_persons using(PersonID) 
				left join (
					select SourceID, TafsiliID2, group_concat(distinct LocalNo) docs
					from ACC_DocItems join ACC_docs using(DocID)
					where SourceType in(" . DOCTYPE_INCOMERCHEQUE . ",".DOCTYPE_EDITINCOMECHEQUE.")
					group by SourceID, TafsiliID2
				)t on(h.IncomeChequeID=t.SourceID AND h.StatusID=t.TafsiliID2)
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
			<td >[" . ($i+1) . "]". ($i+1<10 ? "&nbsp;" : "") . "&nbsp;
				<img align='top' src='/generalUI/ext4/resources/themes/icons/user_comment.gif'>&nbsp;
				" . ($Logs[$i]["StatusID"] == "0" ? "تغییر چک" : $Logs[$i]["StatusDesc"]) . "</td>
			<td  >" . $Logs[$i]["fullname"] . "</td>
			<td >" . substr($Logs[$i]["ATS"], 11) . " " . 
								DateModules::miladi_to_shamsi($Logs[$i]["ATS"]) . "</td>
			<td>سند " . $Logs[$i]["docs"] . "</td>
			<td>".$Logs[$i]["details"]."</td>
			
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