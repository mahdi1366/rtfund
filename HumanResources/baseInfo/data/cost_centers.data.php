<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.12
//---------------------------
require_once '../../header.inc.php';
require_once '../class/cost_centers.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ( $task) {            
            
		case "searchcostCenter" :
                      searchcostCenter();
                    
                case "SaveCostCenter":
                      SaveCostCenter(); 
                    
                case "removeCC":
                      removeCC() ; 
                    
                    }

function searchcostCenter()
{      
        $where = dataReader::makeOrder();
        $temp = manage_cost_centers::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function SaveCostCenter()
{
        $obj = new manage_cost_centers();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
       	
	if(!empty($obj->cost_center_id)) {
		if(!$obj->Edit())
		    {
			 echo "UpdateError";
			 die();   
		    }		    
	}
	else  {
		 if(!$obj->Add())
		    {
			 echo "InsertError";
			 die();   
		    }		 
	      }
		
       echo Response::createObjectiveResponse("true", $obj->cost_center_id);
       die();		
	    
}

function removeCC()
{
	$result = manage_cost_centers::Remove($_POST["cid"]);
	
	if(!$result){	
	    Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString()) ; 
	    die();
	    
	    }	
	else { 
	     Response::createObjectiveResponse(true,"") ; 
	     die();	 
	     
	     }
}

?>