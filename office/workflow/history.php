<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	91.12
//---------------------------
include("../header.inc.php");
require_once './wfm.class.php';
require_once inc_component;

$FlowRowObj = new WFM_FlowRows($_REQUEST["RowID"]);

$query = "select fr.* ,
				ifnull(fs.StepDesc,'شروع گردش') StepDesc,
				if(IsReal='YES',concat(fname, ' ', lname),CompanyName) fullname
			from WFM_FlowRows fr
			left join WFM_FlowSteps fs on(fr.FlowID=fs.FlowID and fs.StepID=fr.StepID)
			join BSC_persons p on(fr.PersonID=p.PersonID)
			where fr.FlowID=? AND fr.ObjectID=?
			order by RowID";
$Logs = PdoDataAccess::runquery($query, array($FlowRowObj->FlowID, $FlowRowObj->ObjectID));
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
				" . $Logs[$i]["StepDesc"] . " [ " . 
				($Logs[$i]["ActionType"] == "CONFIRM" ? "تایید" : "رد") . " ] </td>
			<td  width=150px>" . $Logs[$i]["fullname"] . "</td>
			<td width=110px>" . substr($Logs[$i]["ActionDate"], 11) . " " . 
								DateModules::miladi_to_shamsi($Logs[$i]["ActionDate"]) . "</td>
			<td><div style='cursor:pointer' class='qtip-target' data-qtip='" . 
				$Logs[$i]["ActionComment"] . "'>" .
				String::ellipsis($Logs[$i]["ActionComment"], 48). "</div></td>
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