<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.10
//---------------------------
require_once '../../header.inc.php';
require_once '../class/salary_item_report.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ( $task) {            
            
		case "searchSITR" :
                      searchSITR();
                    
                case "SaveSIR" :
                      SaveSIR() ; 
                    
                case "removeSIR" :
                    removeSIR() ; 
                    
                    
                    }

function searchSITR()
{      
        $where = dataReader::makeOrder();
        
        $temp = manage_salary_item_report::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function SaveSIR()
{
        $obj = new manage_salary_item_report();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
        
       	if ($obj->SalaryItemReportID == "" )
	{
                $return = $obj->Add();
	}
	else
	{
		$return = $obj->Edit();
	}
	
        if($return)
		echo Response::createResponse(true,$obj->SalaryItemReportID);
	else
		echo Response::createResponse(false , ExceptionHandler::ConvertExceptionsToJsObject());
	die();
            
}

function removeSIR()
{
	$return = manage_salary_item_report::Remove($_POST["sid"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

?>