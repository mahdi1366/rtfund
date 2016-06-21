<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.07
//---------------------------
require_once '../../header.inc.php';
require_once '../class/jobs.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ( $task) {            
            
		case "SearchJob" :
			  SearchJob();
                    
		case "SaveJob" :
			  SaveJob() ;    

		case "removeJob" : 
			  removeJob() ; 
                    
                    }

function SearchJob()
{      
    	
	$where = " (1=1) AND PersonType <> 3 ";
	$whereParam = array();

	$field = isset ( $_GET ["fields"] ) ? $_GET ["fields"] : "";

	if (isset ( $_GET ["query"] ) && $_GET ["query"] != "" && $_REQUEST['JobH'] != 1 ) {
			switch ( $field) {
				case "title" :
					$where .= " AND title LIKE :qry " ;
					$whereParam[":qry"] = "%" . $_GET["query"] . "%";

				break;
				case "job_id" :
					$where .= " AND job_id = :qry " ;
					$whereParam[":qry"] = $_GET["query"] ;

				break;
				
			}
		}
		
		if (isset($_REQUEST["query"]) && $_REQUEST["query"] != "" && $_REQUEST['JobH'] == 1 ) { 
			
			$where .= " AND title LIKE :qry " ; 
			$whereParam = array(":qry" => "%" . $_REQUEST["query"] . "%");
			
		}
	

	$where .=  dataReader::makeOrder(); 

	$temp = manage_Job::GetAll($where,$whereParam);
	$no = count($temp);
	
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData ($temp, $no, $_GET ["callback"] );
	die ();
}

function SaveJob()
{
        $obj = new manage_Job() ; 
		PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
        
        if($obj->job_id)        
            $return = $obj->Edit();	
        else
             $return = $obj->Add();	
	
        if($return)
		echo Response::createResponse(true,$obj->job_id );
	else
		echo Response::createResponse(false , ExceptionHandler::ConvertExceptionsToJsObject());
	die();
            
}

function removeJob()
{
	$return = manage_Job::Remove($_POST["jid"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

?>