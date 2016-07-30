<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.09
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/tax_table_items.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) {
		
	case "selectAll" :
		  selectAll();
		  
	case "saveTaxItem":
		saveTaxItem();
		
	case "deleteTaxItem":
		  deleteTaxItem();
}

function selectAll()
{  

        $where = " where 1=1";
		if($_GET["tax_table_id"] != "" )
		{
			$where .= " AND tax_table_id=:ttid ";
			$whereParam[":ttid"] = $_GET["tax_table_id"];
		}

        $where .= dataReader::makeOrder();

    $temp = manage_Tax_Table_Item::GetAll($_GET["tax_table_id"], $where,$whereParam);
	
	
	$no = count($temp);
    $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
}

function saveTaxItem()
{
    
    $obj = new manage_Tax_Table_Item();
    
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	$obj->tax_table_id = $_POST["tax_table_id"] ;
	
    if($obj->row_no == ""){
 	   $return = $obj->AddTaxItem($obj->tax_table_id);
    }
	else
		$return = $obj->EditTaxItem($obj->tax_table_id,$obj->row_no);
	
	if($return)
		echo Response::createResponse(true,$obj->tax_table_id,$obj->row_no );
	else
		echo Response::createResponse(false ,ExceptionHandler::GetExceptionsToString());
	die();
	
}

function deleteTaxItem()
{
    $obj = new manage_Tax_Table_Item($_POST["tax_table_id"],$_POST["row_no"]);
   
	$return = $obj->RemoveTaxItem($obj->tax_table_id, $obj->row_no);
	
	 if($return)
		echo Response::createResponse(true,$obj->tax_table_id."-".$obj->row_no);
	else
		echo Response::createResponse(false , ExceptionHandler::ConvertExceptionsToJsObject());
	die();
	
}
























?>