<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/education.class.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) 
{
	case "selectEduc";
	      selectEduc(); 
	      
	case "saveEducation";
	      saveEducData();  
	      
	 case "DelEduc";
           DelEduc();        
	      
         
}

function selectEduc()
{ 
		
	$where = " pe.PersonID = :PID ";
	$whereParam = array();
	$whereParam[":PID"] = $_GET["Q0"];
	
	$field = isset ( $_GET ["fields"] ) ? $_GET ["fields"] : "";
	if (isset ( $_GET ["query"] ) && $_GET ["query"] != "") {
		switch ( $field) {
			case "fname" :
				$where .= " AND fname LIKE :qry " ;
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";
		        
			break;
			case "lname" :	
				$where .= " AND lname LIKE :qry " ;
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";		
		
			break;
			case "birth_date" :
				$where .= " AND birth_date = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
				
			break;
			case "idcard_no" :
				$where .= " AND idcard_no = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
			
			break;
			case "father_name" :
				$where .= " AND Fname LIKE :qry ";
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";		
				
			break;
			case "Title" :
				$where .= " AND Title LIKE :qry ";
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";		
				
			break;
			
			case "insure_type" : // شماره بیمه
				$where .= " AND insure_type LIKE :qry ";
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";		
				
			break;
			
		}
	}
	
	$no = manage_person_education::CountEducation($where, $whereParam);
	
	$where .=  dataReader::makeOrder(); 
	$where .= isset($_GET ["start"]) ? " limit " . $_GET ["start"] . "," . $_GET ["limit"] : "";
	
	$temp = manage_person_education::GetAllEducations($where,$whereParam);
	
	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
}

function saveEducData(){
	//........ Fill object ..............
	$obj = new manage_person_education();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
		
	$obj->PersonID = $_POST['PersonID'];
	$obj->doc_date = DateModules::Shamsi_to_Miladi($obj->doc_date);
	$obj->certificated = (empty($obj->certificated)) ? "0" : $obj->certificated;
	//....................................
        
        if($_POST['sfid'] == "-1" )            
           $obj->sfid = PDONULL ; 

        if(!isset($_POST['sbid']))
           $obj->sbid = PDONULL ; 
        
       if($_POST['university_id'] == "-1")
           $obj->university_id = PDONULL ; 
       
       if($_POST['country_id'] == "-1")
           $obj->country_id = PDONULL ;  

	if(empty($_POST["row_no"]))
	{		
		$return = $obj->AddEducation();		
	}
	else 
	{ 
		$return = $obj->EditEducation();
	}
	
	
	echo $return ? Response::createObjectiveResponse(true,$obj->row_no) :
		Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString("\n"));
	die();
	
}

function DelEduc()
{
	$return = manage_person_education::RemoveEducation($_POST['PersonID'],$_POST['row_no']);
	if($return !== true)
	{
		echo $return;
		die();
	} 
				
	echo Response::createObjectiveResponse("true", $_POST['PersonID']);
    die();
		
}

?>