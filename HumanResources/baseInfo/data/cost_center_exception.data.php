<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.01.22
//---------------------------
require_once '../../header.inc.php';
require_once '../class/cost_center_exception.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ( $task) {            
            
		case "searchcostCenterItm" :
                      searchcostCenterItm();
                    
                case "SaveCostException":
                      SaveCostException(); 
                    
                case "removeCostException":
                      removeCostException() ; 
                    
                    }

function searchcostCenterItm()
{      
        $where = dataReader::makeOrder();
        $temp = manage_cost_center_exception::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function SaveCostException()
{
        $obj = new manage_cost_center_exception();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
        
       
	if ($_GET['newMode'] == 1 )
	{
                $return = $obj->Add();
	}
	else
	{
		$return = $obj->Edit();
	}
	
        if($return)
		echo Response::createResponse(true,$obj->SalaryItemTypeID);
	else
		echo Response::createResponse(false , ExceptionHandler::ConvertExceptionsToJsObject());
	die();
            
}

function removeCostException()
{
	$return = manage_cost_center_exception::Remove($_POST["sid"] , $_POST["pty"] , $_POST["cid"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

?>