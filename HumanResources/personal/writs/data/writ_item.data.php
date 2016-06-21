<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.08
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ_item.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task)
{
	case "SaveItem":
		SaveItem();

	case "DeleteItem":
		DeleteItem();

	case "DontPayItem":
		DontPayItem();
		
	case "not_assigned_items":
		not_assigned_items();
}

function SaveItem()
{
	$obj = new manage_writ_item();
	PdoDataAccess::FillObjectByArray($obj, $_POST);

    $obj->remembered = (isset($_POST['remembered']))  ? $_POST['remembered'] : 0 ;
    $obj->must_pay = (isset($_POST['must_pay']))  ? $_POST['must_pay'] : 0 ;

	if(isset($_REQUEST["mode"])){
		if($_REQUEST["mode"] == "new")
			$return = $obj->AddWritItem();
		else
		$return = $obj->EditWritItem();
	}
	else 
		$return = $obj->EditWritItem();

	echo Response::createObjectiveResponse($return, ($return ? $obj->writ_id : ExceptionHandler::popExceptionDescription()));
	die();
  
}

function DontPayItem()
{
	$obj = new manage_writ_item();
	
	$return = $obj->DontPayItems($_POST["writ_id"],$_POST["writ_ver"],$_POST["staff_id"]);

	echo Response::createObjectiveResponse($return, ($return ? $obj->writ_id : ExceptionHandler::popExceptionDescription()));
	die();
	
}

function DeleteItem()
{
	$ret = manage_writ_item::RemoveWritItem("writ_id=:wid and writ_ver=:wver and staff_id=:stid and salary_item_type_id=:sid",
			array(":wid" => $_POST["writ_id"],
				  ":wver" => $_POST["writ_ver"],
				  ":stid" => $_POST["staff_id"],
				  ":sid" => $_POST["salary_item_type_id"]));
	echo $ret ? "true" : "false";
	die();
}

function not_assigned_items()
{
	$query = '
		SELECT s.salary_item_type_id,s.full_title
		FROM salary_item_types s
				LEFT OUTER JOIN writ_salary_items wsi ON(wsi.salary_item_type_id = s.salary_item_type_id AND wsi.writ_id = :wid AND wsi.writ_ver = :wver)
				,writs w 
		WHERE
			w.writ_id = :wid AND w.writ_ver = :wver AND w.staff_id = :stid AND
			(s.person_type = w.person_type OR s.person_type = '.PERSON_TYPE_ALL.' OR s.person_type = if(w.person_type != 1, 101,0)) AND
			s.compute_place = '.SALARY_ITEM_COMPUTE_PLACE_WRIT.' AND
			(s.user_data_entry = '.USER_DATA_ENTRY.' OR s.editable_value = 1) AND
			(s.validity_start_date IS NULL OR s.validity_start_date <= w.execute_date) AND
			(s.validity_end_date IS NULL OR s.validity_end_date = "0000-00-00" OR s.validity_end_date >= w.execute_date) AND
			wsi.salary_item_type_id IS NULL';

	$temp = PdoDataAccess::runquery($query, array(":wid" => $_GET["writ_id"], ":wver" => $_GET["writ_ver"], ":stid" => $_GET["staff_id"]));
	
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}
?>
