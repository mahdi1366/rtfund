<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.10
//---------------------------
require_once '../../header.inc.php';
require_once '../class/priority.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ($task) {            
            
				case "SearchPriority" :
                      SearchPriority();
                    
                case "SavePriority" :
                      SavePriority() ;    
                    
                case "remove" : 
                      remove() ; 
                    
                    }

function SearchPriority()
{      
        $where = dataReader::makeOrder();
        $temp = manage_priority::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function SavePriority()
{
        $obj = new manage_priority() ; 
		PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
		
        $where = " where PriorityID=".$obj->PriorityID ; 
		$res = manage_priority::GetAll($where) ; 
		
        if( count($res) > 0 )        
            $return = $obj->Edit();	
        else
             $return = $obj->Add();	
	
        if($return)
		echo Response::createResponse(true,$obj->PriorityID );
	else
		echo Response::createResponse(false , ExceptionHandler::ConvertExceptionsToJsObject());
	die();
            
}

function remove()
{
	 
	$return = manage_priority::Remove($_POST["pid"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

?>