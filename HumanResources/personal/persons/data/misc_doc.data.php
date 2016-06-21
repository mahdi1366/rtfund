<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.06.17
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/misc_doc.class.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) 
{
	case "saveMiscDoc";
	      saveMiscDoc();
	
	case "selectMiscDoc":
		  selectMiscDoc();
		  
	case "DeleteMiscDoc":
		  DeleteMiscDoc();	
         
}

function selectMiscDoc()
{  
	$where = " PersonID = :PID ";
	$whereParam = array();
	$whereParam[":PID"] = $_GET["Q0"];
	
	$field = isset ( $_GET ["fields"] ) ? $_GET ["fields"] : "";
	if (isset ( $_GET ["query"] ) && $_GET ["query"] != "") 
	{
		switch ( $field)
		{
			case "title" :
				$where .= " AND title LIKE :qry " ;
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";
		        
			break;
			case "doc_no" :	
				$where .= " AND doc_no=:qry " ;
				$whereParam[":qry"] = $_GET["query"];
		
			break;
			case "row_no" :
				$where .= " AND row_no=:qry " ;
				$whereParam[":qry"] = $_GET["query"];
				
			break;				
		}
	}
	$no = manage_person_misc_doc::CountMiscDoc($where, $whereParam);
	
	$where .=  dataReader::makeOrder(); 
	$where .= isset($_GET ["start"]) ? " limit " . $_GET ["start"] . "," . $_GET ["limit"] : "";
	
	$temp = manage_person_misc_doc::GetAllMiscDoc($where,$whereParam);
	
	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
}

function saveMiscDoc()
{ 
	//........ Fill object ..............
	$obj = new manage_person_misc_doc();
	
	$arr = get_object_vars($obj);
	$KeyArr = array_keys($arr);
	
	for($i=0; $i<count($arr); $i++)
	{
		eval("\$obj->" . $KeyArr[$i] . " = (isset(\$_POST) && isset(\$_POST['" . $KeyArr[$i] . "'])) 
			? \$_POST['" . $KeyArr[$i] . "'] : '';");
	}	
	$obj->PersonID = $_POST['PersonID'];
	$obj->doc_date = DateModules::Shamsi_to_Miladi($obj->doc_date);
	//....................................
	if(empty($_POST["row_no"]))
	{
		$return = $obj->AddMiscDoc();
		
	}
	else 
	{ 
		$return = $obj->EditMiscDoc();
	}
	
	
	echo $return ? Response::createObjectiveResponse(true,$obj->row_no) :
		Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString("\n"));
	die();
	
}

function DeleteMiscDoc()
{
	$return = manage_person_misc_doc::RemoveMiscDoc($_POST['PersonID'],$_POST['row_no']);
	if($return !== true)
	{
		echo $return;
		die();
	} 
				
	echo Response::createObjectiveResponse("true", $_POST['PersonID']);
    die();
    
}

?>