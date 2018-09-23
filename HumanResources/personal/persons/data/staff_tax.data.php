<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.06.17
//---------------------------
require_once '../../../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;
require_once '../class/staff_tax.class.php';
require_once '../class/staff_costcode.class.php';
require_once '../../staff/class/staff.class.php';

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) 
{ 
    case "saveTax" :
		  saveTax();

	case "saveTaxHis" :
		  saveTaxHis();

    case "selectTaxHistory" :
		  selectTaxHistory();
		  
	case "selectCostCodeHistory" :
	    selectCostCodeHistory();

    case "saveCostCodeGrid" :
		  saveCostCodeGrid();
		  
    case "saveTaxHisGrid" :
		  saveTaxHisGrid();

    case "removeTaxHistory" :
		  removeTaxHistory();
		  
    case "removeCostCode" :
		  removeCostCode();
		  
}



function saveTax(){ 
		
	$return = manage_staff::SaveStaffTax($_POST['PersonID'],$_POST['staff_id'],$_POST['sum_paied_pension']);

	echo $return ? Response::createObjectiveResponse(true ,$_POST['PersonID']) :
		Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString("\n"));
	die();

}

function saveTaxHis(){

	$obj = new manage_staff_tax();

	PdoDataAccess::FillObjectByArray($obj, $_POST);

	$obj->start_date = DateModules::Shamsi_to_Miladi($_POST['start_date']);
	$obj->end_date = DateModules::Shamsi_to_Miladi($_POST['end_date']);
	
	if(!empty($_POST['tax_history_id']))
		$return =$obj->EditStaffTaxHistory($_POST['PersonID']);
		
	else $return =$obj->SaveStaffTaxHistory($_POST['PersonID']);
	
	echo $return ? Response::createObjectiveResponse(true ,$_POST['PersonID']) :
				   Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString("\n"));
	die();

}

function saveTaxHisGrid(){
	
	$obj = new manage_staff_tax();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->tax_history_id == "")
		$return = $obj->SaveStaffTaxHistory($_POST['PersonID']);
	else{
		$return = $obj->EditStaffTaxHistory($_POST['PersonID']);}

	echo $return ? Response::createObjectiveResponse(true ,$obj->staff_id) :
				   Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString("\n"));
	die();
}

function saveCostCodeGrid(){
	
	$obj = new manage_StaffPaidCostCode();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

    if(!empty($obj->StartDate))
     $obj->StartDate = DateModules::shamsi_to_miladi($obj->StartDate) ; 
     
    if(!empty($obj->EndDate))
      $obj->EndDate = DateModules::shamsi_to_miladi($obj->EndDate) ; 
     
      
	if($obj->SPID == "")
		$return = $obj->SaveStaffCostCode($_POST['PersonID']);
	else{
		$return = $obj->EditStaffCostCode($_POST['PersonID']);}

	echo $return ? Response::createObjectiveResponse(true ,$obj->StaffID) :
				   Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString("\n"));
	die();
}

function selectTaxHistory()
{
	$personID = $_GET["PID"];
	$temp = manage_staff_tax::GetAllStaffTaxHistory($personID);

	echo dataReader::getJsonData($temp, count($temp), $_GET ["callback"]);
	die();
}

function selectCostCodeHistory()
{
	$personID = $_GET["PID"];
	$temp = manage_StaffPaidCostCode::GetAllStaffCostCode($personID);

	echo dataReader::getJsonData($temp, count($temp), $_GET ["callback"]);
	die();
}

function removeTaxHistory()
{
	$obj = new manage_staff_tax();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	echo $obj->Remove() ? "true" : "false";
	die();
}

function removeCostCode()
{
	$obj = new manage_StaffPaidCostCode();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	echo $obj->Remove() ? "true" : "false";
	die();
}

?>