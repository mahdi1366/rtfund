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
if(!empty($task))
	$task();

function SelectAllFlows(){
	
	$where = "1=1";
	$param = array();
	
	if(!empty($_REQUEST["ObjectType"]))
	{
		$where .= " AND ObjectType=?";
		$param[] = $_REQUEST["ObjectType"];
	}
	
	$dt = WFM_flows::GetAll($where, $param);
	$no = $dt->rowCount();
	
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($dt, $no, $_GET["callback"]);
	die();
}

function SaveFlow(){
	
	$obj = new WFM_flows();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->FlowID > 0)
		$result = $obj->EditFlow();
	else
	{
		if($obj->ObjectType != SOURCETYPE_FORM)
		{
			$dt = PdoDataAccess::runquery("select * from WFM_flows where ObjectType=?", array($obj->ObjectType));
			if(count($dt) > 0)
			{
				echo Response::createObjectiveResponse(false, "برای این آیتم قبلا گردش تعریف شده است");
				die();
			}
		}
		$result = $obj->AddFlow ();
	}
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteFlow(){
	
	$result = WFM_flows::RemoveFlow($_POST["FlowID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

function selectFlowSteps(){
	$where = "";
	if(!isset($_REQUEST["all"]))
		$where = " AND IsOuter='NO'";
	$dt = PdoDataAccess::runquery("select * from WFM_FlowSteps 
		where IsActive='YES' AND FlowID=? 
                 ". $where , array($_GET["FlowID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//............................

function SelectSteps(){
	
	$dt = WFM_FlowSteps::GetAll("fs.IsActive='YES' AND fs.IsOuter='NO' AND FlowID=? " . dataReader::makeOrder(), array($_GET["FlowID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SaveStep(){
	
	$obj = new WFM_FlowSteps();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->StepRowID > 0)
		$result = $obj->EditFlowStep();
	else
	{
		$dt = PdoDataAccess::runquery("select ifnull(max(StepID),0) from WFM_FlowSteps where FlowID=? AND IsActive='YES' AND IsOuter='NO'", 
				array($obj->FlowID));
		$obj->StepID = $dt[0][0]*1 + 1;
		
		$result = $obj->AddFlowStep();
	}
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteStep(){
	
	$result = WFM_FlowSteps::RemoveFlowStep($_POST["StepRowID"]);	
	echo Response::createObjectiveResponse($result, ExceptionHandler::popExceptionDescription());
	die();
}

function MoveStep(){
	
	$FlowID = $_POST["FlowID"];
	$StepID = $_POST["StepID"];
	$direction = $_POST["direction"] == "-1" ? -1 : 1;
	$revdirection = $direction == "-1" ? "+1" : "-1";
	
	PdoDataAccess::runquery("update WFM_FlowSteps 
		set StepID=1000 
		where FlowID=? AND StepID=? AND IsOuter='NO' AND IsActive='YES'",
			array($FlowID, $StepID));
	
	PdoDataAccess::runquery("update WFM_FlowSteps 
			set StepID=StepID $revdirection 
			where FlowID=? AND StepID=? AND IsOuter='NO' AND IsActive='YES'",
			array($FlowID, $StepID + $direction));
	
	PdoDataAccess::runquery("update WFM_FlowSteps 
		set StepID=? 
		where FlowID=? AND StepID=1000 AND IsOuter='NO' AND IsActive='YES'",
			array($StepID + $direction, $FlowID));
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

//............................

function SelectAllForms(){
	
	$where = "1=1";
	$param = array();

	$ObjectDesc = 
		"case b.param4
			when 'loan' then concat_ws(' ','وام شماره',lp.RequestID,'به مبلغ',
				PartAmount,'مربوط به',if(pp.IsReal='YES',concat(pp.fname, ' ', pp.lname),pp.CompanyName))
				
			when 'contract' then concat_ws(' ','قرارداد شماره',c.ContractID,cp.CompanyName,cp.fname,cp.lname)
			
			when 'warrenty' then concat_ws(' ','ضمانت نامه [',wr.RefRequestID,'] ', wp.CompanyName,wp.fname,wp.lname, 'به مبلغ ',wr.amount)
			
			when 'form' then concat_ws(' ',wfmf.FormTitle,'به شماره',wfmr.RequestID)
			
		end";
	$ObjectID = 
		"case b.param4
			when 'loan'		then lp.RequestID				
			when 'contract' then c.ContractID			
			when 'warrenty' then wr.RefRequestID
			when 'form'		then wfmr.RequestID
		end";
	
	if(!empty($_GET["fields"]) && !empty($_GET["query"]))
	{
		$field = $_GET["fields"] == "ObjectDesc" ? $ObjectDesc : $_GET["fields"];
		$field = $_GET["fields"] == "StepDesc" ? "ifnull(fs.StepDesc,'شروع گردش')" : $field;
		$where .= " AND $field like :fld";
		$param[":fld"] = "%" . $_GET["query"] . "%";
	}
	//----------------- received forms ----------------------
	if(!empty($_GET["MyForms"]) && $_GET["MyForms"] == "true")
	{
		$dt = PdoDataAccess::runquery("
			select FlowID,StepID 
			from WFM_FlowSteps s 
			where s.IsActive='YES' AND s.PersonID=:pid
			
			union all
			
			select FlowID,StepID 
			from WFM_FlowSteps s 
			join BSC_jobs j using(JobID)
			where s.IsActive='YES' AND j.PersonID=:pid
			
			union all 
			
			select FlowID,StepID 
			from WFM_FlowSteps s 
			join BSC_jobs j using(PostID)
			where s.IsActive='YES' AND j.PersonID=:pid

			", array(":pid" => $_SESSION["USER"]["PersonID"]));
		if(count($dt) == 0)
		{
			echo dataReader::getJsonData(array(), 0, $_GET["callback"]);
			die();
		}
		$where .= " AND fr.IsEnded='NO' AND (";
		foreach($dt as $row)
		{
			$preStep = $row["StepID"]*1-1;
			$nextStep = $row["StepID"]*1+1;
			
			$where .= "(fr.FlowID=" . $row["FlowID"] .
				" AND fs.StepID" . ($preStep == 0 ? " is null" : "=" . $preStep) . 
				" AND ActionType='CONFIRM') OR (fr.FlowID=" . $row["FlowID"] . 
				" AND fs.StepID=" . $nextStep . " AND ActionType='REJECT') OR";
		}
		$where = substr($where, 0, strlen($where)-2) . ")";
	}
	//--------------------------------------------------------
	$query = "select fr.*,f.FlowDesc, 
					b.InfoDesc ObjectTypeDesc,
					ifnull(fr.StepDesc,'شروع گردش') StepDesc,
					if(p.IsReal='YES',concat(p.fname, ' ',p.lname),p.CompanyName) fullname,
					$ObjectDesc ObjectDesc,
					b.param1 url,
					b.param2 parameter,
					b.param3 target,
					$ObjectID ObjectID
	
				from WFM_FlowRows fr
				join ( select max(RowID) RowID,FlowID,ObjectID from WFM_FlowRows group by FlowID,ObjectID )t
					using(RowID,FlowID,ObjectID)
				join WFM_flows f using(FlowID)
				join BaseInfo b on(b.TypeID=11 AND b.InfoID=f.ObjectType)
				left join WFM_FlowSteps fs on(fr.StepRowID=fs.StepRowID)
				join BSC_persons p on(fr.PersonID=p.PersonID)
				
				left join LON_ReqParts lp on(b.param4='loan' AND fr.ObjectID=PartID)
				left join LON_requests lr on(lp.RequestID=lr.RequestID)
				left join BSC_persons pp on(lr.LoanPersonID=pp.PersonID)

				left join CNT_contracts c on(b.param4='contract' AND fr.ObjectID=c.ContractID)
				left join BaseInfo cbf on(cbf.TypeID=18 AND cbf.InfoID=ContractType)
				left join BSC_persons cp on(cp.PersonID=c.PersonID)

				left join WAR_requests wr on(b.param4='warrenty' AND wr.RequestID=fr.ObjectID)
				left join BaseInfo bf on(bf.TypeID=74 AND bf.InfoID=wr.TypeID)
				left join BSC_persons wp on(wp.PersonID=wr.PersonID)

				left join WFM_requests wfmr on(b.param4='form' AND wfmr.RequestID=fr.ObjectID)
				left join WFM_forms wfmf on(wfmr.FormID=wfmf.FormID)	
	
				where " . $where . dataReader::makeOrder();
	$temp = PdoDataAccess::runquery_fetchMode($query, $param);
	//echo PdoDataAccess::GetLatestQueryString();
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function ChangeStatus(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$mode = $_REQUEST["mode"];
	$SourceObj = new WFM_FlowRows($_POST["RowID"]);
	$newObj = new WFM_FlowRows();
	$newObj->FlowID = $SourceObj->FlowID;
	$newObj->ObjectID = $SourceObj->ObjectID;
	$newObj->PersonID = $_SESSION["USER"]["PersonID"];
	$newObj->ActionType = $mode;
	$newObj->ActionDate = PDONOW;
	$newObj->ActionComment = $_POST["ActionComment"];
	//.............................................
	if(isset($_POST["StepID"]))
		$StepID = $_POST["StepID"];
	else 
		$StepID = $SourceObj->ActionType == "CONFIRM" ? $SourceObj->_StepID+1 : $SourceObj->_StepID-1;
	
	$dt = PdoDataAccess::runquery("select StepRowID, StepDesc from WFM_FlowSteps 
		where IsActive='YES' AND FlowID=? AND StepID=?" , array($newObj->FlowID, $StepID));
	if(count($dt) == 0)
	{
		echo Response::createObjectiveResponse(false, "1");
		die();
	}
	$newObj->StepRowID = $dt[0]["StepRowID"];	
	$newObj->StepDesc = $dt[0]["StepDesc"];	
	//.............................................
	if($SourceObj->ActionType == "CONFIRM")
	{
		$dt = PdoDataAccess::runquery("select Max(StepID) maxStepID from WFM_FlowSteps 
			where IsActive='YES' AND FlowID=? AND IsOuter='NO'" , array($newObj->FlowID));
		if($dt[0][0] == $StepID)
			$newObj->IsEnded = "YES";
	}
	//.............................................
	$result = $newObj->AddFlowRow($StepID, $pdo);	
	if(!$result)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse($result, "2");
		die();
	}
	
	if($newObj->IsEnded == "YES")
		$result = WFM_FlowRows::EndObjectFlow($newObj->FlowID, $newObj->ObjectID, $pdo);

	if(!$result)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse($result, "3");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function StartFlow(){
	
	$FlowID = $_REQUEST["FlowID"];
	$ObjectID = $_REQUEST["ObjectID"];
	$result = WFM_FlowRows::StartFlow($FlowID, $ObjectID);
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ReturnStartFlow(){
	
	$FlowID = $_REQUEST["FlowID"];
	$ObjectID = $_REQUEST["ObjectID"];
	$result = WFM_FlowRows::ReturnStartFlow($FlowID, $ObjectID);
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteAllFlow(){
	
	$FlowID = $_REQUEST["FlowID"];
	$ObjectID = $_REQUEST["ObjectID"];
	$result = WFM_FlowRows::DeleteAllFlow($FlowID, $ObjectID);
	
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}
//-------------------------------------

function GetStepPersons(){
	
	$dt = WFM_FlowStepPersons::Get(" AND StepRowID=?", array($_REQUEST["StepRowID"]));
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function SaveStepPerson(){
	
	$obj = new WFM_FlowStepPersons();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	$result = $obj->Add();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function RemoveStepPersons(){
	
	$obj = new WFM_FlowStepPersons($_REQUEST["RowID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

?>