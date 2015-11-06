<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.07
//---------------------------
require_once '../header.inc.php';
require_once 'wfm.class.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "SelectMyForms":
		SelectMyForms();
		
	case "ChangeStatus":
		ChangeStatus();
}

function SelectMyForms()
{
	$where = "1=1";
	
	$dt = PdoDataAccess::runquery("select FlowID,StepID 
		 from WFM_FlowSteps s join BSC_persons p using(PostID)
		 where p.PersonID=" . $_SESSION["USER"]["PersonID"]);
	if(count($dt) == 0)
	{
		echo dataReader::getJsonData(array(), 0, $_GET["callback"]);
		die();
	}
	$where .= " AND (";
	foreach($dt as $row)
	{
		$where .= "(fr.FlowID=" . $row["FlowID"] .
			" AND fr.StepID=" . ($row["StepID"]*1-1) . " AND ActionType='CONFIRM') OR 
			(fr.FlowID=" . $row["FlowID"] . 
			" AND fr.StepID=" . ($row["StepID"]*1+1) . " AND ActionType='REJECT') OR";
	}
	$where = substr($where, 0, strlen($where)-2) . ")";
	
	$query = "select fr.*,f.FlowDesc, 
					b.InfoDesc ObjectTypeDesc,
					ifnull(fs.StepDesc,'شروع گردش') StepDesc,
					if(IsReal='YES',concat(fname, ' ', lname),CompanyName) fullname
				from WFM_FlowRows fr
				join ( select max(RowID) RowID,FlowID,ObjectID from WFM_FlowRows group by FlowID,ObjectID )t
					using(RowID,FlowID,ObjectID)
				join WFM_flows f using(FlowID)
				join BaseInfo b on(b.TypeID=11 AND b.InfoID=f.ObjectType)
				left join WFM_FlowSteps fs on(fr.FlowID=fs.FlowID and fs.StepID=fr.StepID)
				join BSC_persons p on(fr.PersonID=p.PersonID)
				where " . $where . dataReader::makeOrder();
	$temp = PdoDataAccess::runquery_fetchMode($query);
	//echo PdoDataAccess::GetLatestQueryString();
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function ChangeStatus(){
	
	$mode = $_REQUEST["mode"];
	$SourceObj = new WFM_FlowRows($_POST["RowID"]);
	
	$newObj = new WFM_FlowRows();
	$newObj->FlowID = $SourceObj->FlowID;
	$newObj->ObjectID = $SourceObj->ObjectID;
	$newObj->PersonID = $_SESSION["USER"]["PersonID"];
	$newObj->StepID = $SourceObj->ActionType == "CONFIRM" ? $SourceObj->StepID+1 : $SourceObj->StepID-1;
	$newObj->ActionType = $mode;
	$newObj->ActionDate = PDONOW;
	$newObj->ActionComment = $_POST["ActionComment"];
	$result = $newObj->AddFlowRow();
	echo Response::createObjectiveResponse($result, "");
	die();
}
?>