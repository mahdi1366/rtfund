<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	91.12
//---------------------------
include("../header.inc.php");
require_once 'wfm.class.php';
require_once inc_component;

if(!empty($_REQUEST["RowID"]))
{
	$FlowRowObj = new WFM_FlowRows($_REQUEST["RowID"]);
	$FlowID = $FlowRowObj->FlowID;
	$ObjectID = $FlowRowObj->ObjectID;
}
else if(!empty($_REQUEST["FlowID"]) && !empty($_REQUEST["ObjectID"]))
{
	$FlowID = $_REQUEST["FlowID"];
	$ObjectID = $_REQUEST["ObjectID"];
}
else
	die();
	 
$query = "select fr.* ,fs.StepID,
				ifnull(fr.StepDesc,'شروع گردش') StepDesc,
				if(IsReal='YES',concat(fname, ' ', lname),CompanyName) fullname
			from WFM_FlowRows fr
			left join WFM_FlowSteps fs on(fr.StepRowID=fs.StepRowID)
			join BSC_persons p on(fr.PersonID=p.PersonID)
			where fr.FlowID=? AND fr.ObjectID=?
			order by RowID";
$Logs = PdoDataAccess::runquery($query, array($FlowID, $ObjectID));

$tbl_content = "";

if(count($Logs) == 0)
{
	 $tbl_content = "<tr><td>فرم مورد نظر فاقد گردش می باشد</td></tr>";
}
else 
{
	for ($i=0; $i<count($Logs); $i++)
	{
		$backgroundColor = ($i%2 == 1 ? "style='background-color:#efefef'" : "");
		$backgroundColor = $Logs[$i]["ActionType"] == "REJECT" ? "style='background-color:#ffccd1'" : $backgroundColor;
		
		$tbl_content .= "<tr " . $backgroundColor . ">
			<td width=250px>[" . ($i+1) . "]". ($i+1<10 ? "&nbsp;" : "") . "&nbsp;
				<img align='top' src='/generalUI/ext4/resources/themes/icons/user_comment.gif'>&nbsp;" .
				($Logs[$i]["ActionType"] == "CONFIRM" ? "تایید" : "رد") . " مرحله " . $Logs[$i]["StepDesc"] . " </td>
			<td  width=150px>" . $Logs[$i]["fullname"] . "</td>
			<td width=110px>" . substr($Logs[$i]["ActionDate"], 11) . " " . 
								DateModules::miladi_to_shamsi($Logs[$i]["ActionDate"]) . "</td>
			<td><div style='cursor:pointer' class='qtip-target' data-qtip='" . 
				$Logs[$i]["ActionComment"] . "'>" .
				String::ellipsis($Logs[$i]["ActionComment"], 48). "</div></td>
		</tr>";
	}
	//------------------------ get next one ------------------------------------
	$StepID = ($Logs[$i-1]["StepID"] == "" ? 0 : $Logs[$i-1]["StepID"]) + 1;
	$query = "select StepDesc,po.PostName,
				if(IsReal='YES',concat(fname, ' ', lname),CompanyName) fullname
			from WFM_FlowSteps fs
			left join BSC_posts po using(PostID)
			left join BSC_persons p on(if(fs.PersonID>0,fs.PersonID=p.PersonID,po.PostID=p.PostID))
			where fs.IsActive='YES' AND fs.FlowID=? AND fs.StepID=?";
	$nextOne = PdoDataAccess::runquery($query, array($FlowID, $StepID));
	
	if(count($nextOne)>0)
	{
		$str = "";
		foreach($nextOne as $row)
			$str .= $row["fullname"] . 
				($row["PostName"] != "" ? " [ پست : " . $row["PostName"] . " ]" : "") . " و ";
		$str = substr($str, 0, strlen($str)-3);
		
		$tbl_content .= "<tr style='background-color:#A9E8E8'>
				<td colspan=4 align=center>در حال حاضر فرم در مرحله <b>" . 
				$nextOne[0]["StepDesc"] . "</b>  در کارتابل <b>" . $str . "</b> می باشد.</td>
			</tr>";
	}
	else
	{
		$tbl_content .= "<tr style='background-color:#A9E8E8'>
			<td colspan=4 align=center><b>گردش فرم پایان یافته است.</b></td>
			<tr>";
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