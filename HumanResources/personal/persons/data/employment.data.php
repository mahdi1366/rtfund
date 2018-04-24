<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	96.06
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/employment.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) {
	
	case "selectEmp";
	      selectEmp();
	
	case "saveEmployee":
    	  saveEmpData();
    	  	
    case "DelEmployee";
          DelEmp();
				
}

function selectEmp ()
{
	$where = " eh.PersonID = :PID ";
	$whereParam = array();
	$whereParam[":PID"] = $_GET["Q0"];
	
	$field = isset ( $_GET ["fields"] ) ? $_GET ["fields"] : "";
	
	if (isset ( $_GET ["query"] ) && $_GET ["query"] != "") {
			switch ( $field) {
				case "organization" :
					$where .= " AND organization LIKE :qry " ;
					$whereParam[":qry"] = "%" . $_GET["query"] . "%";
			        
				break;
				case "unit" :
					$where .= " AND unit LIKE :qry " ;
					$whereParam[":qry"] = "%" . $_GET["query"] . "%";
			        
				break;
				case "from_date" :
					$where .= " AND from_date = :qry1 " ;
					$whereParam[":qry1"] = $_GET["query"];
					
				break;
				case "to_date" :
					$where .= " AND to_date = :qry1 " ;
					$whereParam[":qry1"] = $_GET["query"];
					
				break;
				case "org_title" :
					$where .= " AND org_title = :qry1 " ;
					$whereParam[":qry1"] = $_GET["query"];
				
				break;
				case "unempTitle" :
					$where .= " AND unempTitle = :qry1 " ;
					$whereParam[":qry1"] = $_GET["query"];
				
				break;
				case "title" :
					$where .= " AND title LIKE :qry ";
					$whereParam[":qry"] = "%" . $_GET["query"] . "%";
					
				break;
				
			}
		}
		
	$no = manage_person_employment::CountEmp($where, $whereParam);
	
	$where .=  dataReader::makeOrder(); 
	$where .= isset($_GET ["start"]) ? " limit " . $_GET ["start"] . "," . $_GET ["limit"] : "";
	
	$temp = manage_person_employment::GetAllEmp($where, $whereParam);
	
	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
		
}

function saveEmpData(){
	//........ Fill object ..............
	$obj = new manage_person_employment();
	
	$arr = get_object_vars($obj);
	$KeyArr = array_keys($arr);
	
	for($i=0; $i<count($arr); $i++)
	{
		eval("\$obj->" . $KeyArr[$i] . " = (isset(\$_POST) && isset(\$_POST['" . $KeyArr[$i] . "'])) 
			? \$_POST['" . $KeyArr[$i] . "'] : '';");
	}	
		
	$obj->PersonID = $_POST['PersonID'];
	$obj->from_date = DateModules::Shamsi_to_Miladi($obj->from_date);
	$obj->to_date = DateModules::Shamsi_to_Miladi($obj->to_date);
	//....................................
	if(empty($_POST["row_no"]))
	{
		$return = $obj->AddEmp();
		
	}
	else 
	{ 
		$return = $obj->EditEmp();
	}
	
	
	echo $return ? Response::createObjectiveResponse(true,$obj->row_no) :
		Response::createObjectiveResponse(false,ExceptionHandler::GetExceptionsToString("\n"));
	die();
	
}

function DelEmp()
{   
	$return = manage_person_employment::RemoveEmp($_POST['PersonID'],$_POST['row_no']);
	if($return !== true)
	{
		echo $return;
		die();
	} 
				
	echo Response::createObjectiveResponse("true", $_POST['PersonID']);
    die();
		
}

//-----------------------------------------------------------------------


?>