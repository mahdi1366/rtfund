<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.06
//---------------------------
require_once '../../header.inc.php';
require_once '../class/banks.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ( $task) {            
            
		case "Searchbank" :
                      Searchbank();
                    
                case "SaveBank" :
                      SaveBank() ;    
                    
                case "removebank" : 
                      removebank() ; 
                    
                    }

function Searchbank()
{      
        $where = dataReader::makeOrder();
        $temp = manage_bank::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function SaveBank()
{
        $obj = new manage_bank() ; 
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
        
        if($obj->bank_id)        
            $return = $obj->Edit();	
        else
             $return = $obj->Add();	
	
        if($return)
		echo Response::createResponse(true,$obj->bank_id );
	else
		echo Response::createResponse(false , ExceptionHandler::ConvertExceptionsToJsObject());
	die();
            
}

function removebank()
{
	$return = manage_bank::Remove($_POST["bid"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

?>