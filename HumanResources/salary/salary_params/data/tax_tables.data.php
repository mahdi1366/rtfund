<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.09
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/tax_tables.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) {
		
	case "selectAll" :
		  selectAll();
		  
	case "saveTax":
		saveTax();
		
	case "deleteTax":
		deleteTax();
}

function selectAll()
{  
	$where = " where (1=1)";
    if($_GET["tax_table_type_id"] != "" )
    {
        $where .= " AND tax_table_type_id=:ttid ";
        $whereParam[":ttid"] = $_GET["tax_table_type_id"] ;
    }
        
    $where .= dataReader::makeOrder();

	$temp = manage_Tax_Table::GetAll($_GET["tax_table_type_id"], $where,$whereParam);
	$no = count($temp);

    $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
}

function saveTax()
{
    
    $obj = new manage_Tax_Table();
    
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	$obj->tax_table_type_id = $_POST["tax_table_type_id"] ;
	
    if($obj->tax_table_id == ""){
 	   $return = $obj->AddTax();
    }
	else
		$return = $obj->EditTax($obj->tax_table_id);
	
	if($return)
		echo Response::createResponse(true,$obj->tax_table_id);
	else
		echo Response::createResponse(false ,ExceptionHandler::ConvertExceptionsToJsObject());
	die();
	
}

function deleteTax()
{
    $obj = new manage_Tax_Table($_POST["tax_table_id"]);
   	
	$return = $obj->RemoveTax();
	
	 if($return)
		echo Response::createResponse(true,$obj->tax_table_id);
	else
		echo Response::createResponse(false , ExceptionHandler::ConvertExceptionsToJsObject());
	die();
	
}
























?>