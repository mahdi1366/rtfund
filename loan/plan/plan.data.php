<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------

include_once('../header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'plan.class.php';
include_once 'elements.class.php';

$task = $_REQUEST["task"];
switch ($task) {
		
	default : 
		eval($task. "();");
}

function SaveGroup(){
	
	$obj = new PLN_groups();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if($obj->GroupID*1 > 0)
		$reslt = $obj->Edit();
	else
		$reslt = $obj->Add();
	
	//print_r(ExceptionHandler::PopAllExceptions());	
	echo Response::createObjectiveResponse($reslt, "");
	die();
}

function DeleteGroup(){
	
	$obj = new PLN_groups($_POST["GroupID"]);
	$reslt = $obj->Remove();
	
	//print_r(ExceptionHandler::PopAllExceptions());	
	echo Response::createObjectiveResponse($reslt, "");
	die();
}

//............................................

function selectGroups(){
	
	$filled = !isset($_REQUEST["filled"]) ? "" : $_REQUEST["filled"];
	$PlanID = $_REQUEST["PlanID"];
	
	$nodes = PdoDataAccess::runquery("select g.ParentID, g.GroupID id, g.GroupDesc text , 'true' leaf ,
		'javascript:void(0)' href, 'false' expanded, '' iconCls , 
		concat(if(count(pi.RowID)>0, 'filled ', ''), 
				case t.ActType when 'REJECT' then 'reject'
							   when 'CONFIRM' then 'confirm'
							   else '' end
		) cls,
		ifnull(ActDesc,'') qtip
		
		FROM PLN_groups g
		left join PLN_Elements e on(e.ParentID=0 AND g.GroupID=e.GroupID)
		left join PLN_PlanItems pi on(pi.PlanID=:p AND e.ElementID=pi.ElementID)
		left join (
			select p.GroupID,ActType,ActDesc from PLN_PlanSurvey p,
				(select GroupID,max(RowID) RowID from PLN_PlanSurvey where PlanID=:p AND GroupID>0
				group by GroupID)t
			where PlanID=:p AND p.RowID =t.RowID AND p.GroupID=t.GroupID
			group by GroupID
		)t on(g.GroupID=t.GroupID)
		group by g.GroupID
		" . ($filled == "true" ? " having count(pi.RowID)>0 " : "") . "
	", array(":p" => $PlanID));
		
	$returnArr = array(); 
	$refArr = array();
	
	foreach($nodes as $node)
	{
		$parentNode = &$refArr[$node["ParentID"]];
		if(!isset($parentNode))
		{
			$node["text"] = "[ " . (count($returnArr)+1) . " ] " . $node["text"];
			$returnArr[] = $node;
			$refArr[ $node["id"] ] = &$returnArr[ count($returnArr)-1 ];
			continue;
		}

		if (!isset($parentNode["children"])) {
			$parentNode["children"] = array();
			$parentNode["leaf"] = "false";
			unset($parentNode["href"]);
		}
		$lastIndex = count($parentNode["children"]);
		$parentNode["children"][$lastIndex] = $node;
		
		$refArr[ $node["id"] ] = &$parentNode["children"][$lastIndex];
	}

	echo json_encode($returnArr);
	die();
}

function SelectElements(){

	$PlanID = $_REQUEST["PlanID"];
	$GroupID = $_REQUEST["GroupID"];
	$dt = PdoDataAccess::runquery("select e.* from PLN_Elements e
		where GroupID=? order by ElementID", array($GroupID));
	
	$planValues = array();
	for($i=0; $i < count($dt); $i++)
	{
		if($dt[$i]["ElementType"] == "grid")
			continue;
		if($dt[$i]["ElementType"] == "panel")
		{
			$temp = PLN_PlanItems::SelectAll("PlanID=? AND ElementID=?", array($PlanID, $dt[$i]["ElementID"]));
			if(count($temp) == 0)
				continue;
			$p = xml_parser_create();
			xml_parse_into_struct($p, $temp[0]["ElementValue"], $vals);
			xml_parser_free($p);
			$planValues[ $dt[$i]["ElementID"] ] = $vals;
		}
		else
		{
			if(!isset($planValues[ $dt[$i]["ParentID"] ]))
			{
				$dt[$i]["ElementValue"] = "";
				break;
			}
			
			$vals = $planValues[ $dt[$i]["ParentID"] ];
			foreach($vals as $element)
			{
				if($element["tag"] == "ELEMENT_" . $dt[$i]["ElementID"])
				{
					$dt[$i]["ElementValue"] = empty($element["value"]) ? "" : $element["value"];
					break;
				}
			}
		}
	}
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SelectPlanItems(){
	
	$PlanID = $_REQUEST["PlanID"];
	$ElementID = $_REQUEST["ElementID"];
	
	$dt = PLN_PlanItems::SelectAll("PlanID=? AND ElementID=?", array($PlanID, $ElementID));
	for($i=0; $i < count($dt); $i++)
	{
		$p = xml_parser_create();
		xml_parse_into_struct($p, $dt[$i]["ElementValue"], $vals);
		xml_parser_free($p);
		
		foreach($vals as $element)
		{
			if(strpos($element["tag"],"ELEMENT_") !== false)
				$dt[$i][strtolower($element["tag"]) ] = empty($element["value"]) ? "" : $element["value"];
		}		
		unset($dt[$i]["ElementValue"]);
	}	
		
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SavePlanItems(){
	
	$obj = new PLN_PlanItems();
	
	if(isset($_POST["record"]))
	{
		$st = stripslashes(stripslashes($_POST["record"]));
		$data = json_decode($st);
	
		$obj->RowID = $data->RowID;
		$obj->PlanID = $data->PlanID;
		$obj->ElementID = $data->ElementID;

		$xml = new SimpleXMLElement('<root/>');
		$elems = array_keys(get_object_vars($data));
		foreach($elems as $el)
		{
			if(strpos($el, "element_") === false)
				continue;
			$xml->addChild($el, $data->$el);
		}
		$obj->ElementValue = $xml->asXML();
	}
	else
	{
		$obj->PlanID = $_POST["PlanID"];
		$obj->ElementID = $_POST["ElementID"];
		
		$dt = PdoDataAccess::runquery("select RowID from PLN_PlanItems where PlanID=? AND ElementID=?",
			array($obj->PlanID, $obj->ElementID));
		if(count($dt)>0)
			$obj->RowID = $dt[0]["RowID"];		
		
		$xml = new SimpleXMLElement('<root/>');
		foreach($_POST as $key => $value)
		{
			if(strpos($key, "element_") === false)
				continue;
			$xml->addChild($key, $value);
		}
		$obj->ElementValue = $xml->asXML();
	}
	
	if($obj->RowID > 0)
		$result = $obj->EditItem();
	else
		$result = $obj->AddItem();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeletePlanItem(){
	
	$RowID = $_POST["RowID"];
	
	$result = PLN_PlanItems::DeleteItem($RowID);
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

//............................................

function SurveyGroup(){
	
	$PlanID = $_POST["PlanID"];
	$GroupID = $_POST["GroupID"];
	$mode = $_POST["mode"];
	$ActDesc = $_POST["ActDesc"];
	
	$obj = new PLN_PlanSurvey();
	$obj->PlanID = $PlanID;
	$obj->GroupID = $GroupID;
	$obj->ActType = $mode;
	$obj->ActDate = PDONOW;
	$obj->ActDesc = $ActDesc;
	$obj->ActPersonID = $_SESSION["USER"]["PersonID"];
	
	$result = $obj->AddRow();
	echo Response::createObjectiveResponse($result, "");
	die();
}

//............................................

function SelectAllPlans(){
	
	$param = array();
	$where = "1=1 ";
	if(!empty($_REQUEST["PlanID"]))
	{
		$where .= " AND PlanID=:pid";
		$param[":pid"] = $_REQUEST["PlanID"];
	}
	
	if(isset($_SESSION["USER"]["portal"]))
	{
		if($_SESSION["USER"]["IsExpert"] == "YES")
		{
			$where .= " AND e.PersonID=:ep AND e.StatusDesc<>'SEND' ";
			$param[":ep"] = $_SESSION["USER"]["PersonID"];
		}
		if($_SESSION["USER"]["IsSupporter"] == "YES")
		{
			$where .= " AND SupportPersonID=:sp AND p.StepID=" . STEPID_SEND_SUPPORTER;
			$param[":sp"] = $_SESSION["USER"]["PersonID"];
		}
	}
	else
	{
		if(!isset($_REQUEST["AllPlans"]) || $_REQUEST["AllPlans"] == "false")
			$where .= " AND p.StepID in(" . STEPID_CUSTOMER_SEND . "," . STEPID_ENDFLOW . ")";
	}
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) {
        $field = $_REQUEST['fields'];
		$field = $field == "ReqFullname" ? "concat_ws(' ',p1.fname,p1.lname,p1.CompanyName)" : $field;
        $where .= ' and ' . $field . ' like :fld';
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
	
	$dt = PLN_plans::SelectAll($where, $param, dataReader::makeOrder());
	//print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	$count = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);	
	echo dataReader::getJsonData($dt, $count, $_GET["callback"]);
	die();
}

function SelectMyPlans(){
	
	$param = array($_SESSION["USER"]["PersonID"]);
	$where = "p.PersonID=?";
	
	$dt = PLN_plans::SelectAll($where, $param, dataReader::makeOrder());
	print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	$count = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);	
	echo dataReader::getJsonData($dt, $count, $_GET["callback"]);
	die();
}

function SaveNewPlan(){
	
	$PlanID = $_POST["PlanID"];
	
	$obj = new PLN_plans();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if($PlanID*1 == 0)
	{
		if(isset($_SESSION["USER"]["framework"]))
			$obj->PersonID = $_POST["PersonID"];
		else
			$obj->PersonID = $_SESSION["USER"]["PersonID"];		
		$obj->RegDate = PDONOW;
		$obj->StepID = !isset($_SESSION["USER"]["framework"]) ? STEPID_RAW : STEPID_CUSTOMER_SEND;
		$result = $obj->AddPlan();
		
		PLN_plans::ChangeStatus($obj->PlanID, $obj->StepID , "", true);
	}
	else
	{
		$obj->PlanID = $PlanID;
		$result = $obj->EditPlan();
	}
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, $obj->PlanID);
	die();	
}

function ChangeStatus(){
	
	$PlanID = $_REQUEST["PlanID"];
	$obj = new PLN_plans($PlanID);

	//-------------------- control valid operation -----------------------
	if($_SESSION["USER"]["IsCustomer"] == "YES" && isset($_SESSION["USER"]["portal"]) && 
		$obj->PersonID != $_SESSION["USER"]["PersonID"])
	{
		Response::createObjectiveResponse(false, "");
		die();
	}	
	//---------------------------------------------------------------------
	$StepID = $_POST["StepID"];
	$ActDesc = $_POST["ActDesc"];
	
	if($_SESSION["USER"]["IsCustomer"] == "YES" && isset($_SESSION["USER"]["portal"]))
		$StepID = STEPID_CUSTOMER_SEND;
	
	if(isset($_SESSION["USER"]["framework"]) && $StepID == STEPID_CONFIRM)
	{
		$dt = PdoDataAccess::runquery("
			select p.GroupID,ActType from PLN_PlanSurvey p,
				(select GroupID,max(RowID) RowID from PLN_PlanSurvey where PlanID=2 AND GroupID>0
				group by GroupID)t
			where PlanID=2 AND p.RowID =t.RowID AND p.GroupID=t.GroupID AND ActType='REJECT'
			group by GroupID", array(":p" => $PlanID));
		if(count($dt)>0)
		{
			echo Response::createObjectiveResponse(false, "بعضی از جداول رد شده اند و قادر به تایید طرح نمی باشید");
			die();
		}
	}

	$result = PLN_plans::ChangeStatus($obj->PlanID, $StepID, $ActDesc);
	
	if($StepID == STEPID_CONFIRM)
	{
		$result = WFM_FlowRows::StartFlow(FLOWID, $obj->PlanID);
	}
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

//............................................

function GetPlanExperts(){
	
	$temp = PLN_experts::Get("AND PlanID=?", array($_REQUEST["PlanID"]));
	
	print_r(ExceptionHandler::PopAllExceptions());
	$res = $temp->fetchAll();
	echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
	die();
}

function SavePlanExpert(){
	
	$obj = new PLN_experts();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if(empty($obj->RowID))
	{
		$obj->RegDate = PDONOW;
		$result = $obj->Add();
	}
	else
	{
		$result = $obj->Edit();
	}
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

function DeletePlanExpert(){
	
	$obj = new PLN_experts();
	$obj->RowID = $_POST["RowID"];
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();	
}

function SendExpert(){
	
	$dt = PLN_experts::Get(" AND PlanID=? AND PersonID=?", array($_POST["PlanID"], $_SESSION["USER"]["PersonID"]));
	if($dt->rowCount() == 0)
	{
		echo Response::createObjectiveResponse(false, "دسترسی غیر مجاز");
		die();
	}
	$dt = $dt->fetch();
	
	$obj = new PLN_experts($dt["RowID"]);
	$obj->DoneDesc = $_POST["DoneDesc"];
	$obj->DoneDate = PDONOW;
	$obj->StatusDesc = "SEND";
	$result = $obj->Edit();
	
	Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}
?>