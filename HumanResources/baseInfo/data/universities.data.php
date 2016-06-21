<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.12
//---------------------------
require_once '../../header.inc.php';
require_once '../class/universities.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ( $task) {            
            
		case "SearchUni" :
                      SearchUni();
                    
                case "SaveUni" :
                      SaveUni() ;    
                    
                case "removeUni" : 
                      removeUni() ; 
                    
                    }

function SearchUni()
{      
        $where = dataReader::makeOrder();
        $temp = manage_University::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function SaveUni()
{
        $obj = new manage_University() ; 
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
        
        if($obj->university_id)        
            $return = $obj->Edit();	
        else
             $return = $obj->Add();	
	
        if($return)
		echo Response::createResponse(true,$obj->university_id );
	else
		echo Response::createResponse(false , ExceptionHandler::ConvertExceptionsToJsObject());
	die();
            
}

function removeUni()
{
	$return = manage_University::Remove($_POST["uid"] , $_POST["cid"] );
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

?>