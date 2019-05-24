<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.06
//---------------------------

require_once '../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once './budget.class.php';
//ini_set("display_errors", "On");

$task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';
switch ($task) {

	case "SelectBudgets":
	case "SaveBudget":
	case "DeleteBudget":
	case "ActivateBudget":
		
		$task();
};

function SelectBudgets() {

    $where = " AND IsActive='YES'";
    $param = array();

    if (!empty($_REQUEST['query'])) {
		$field = isset($_REQUEST['fields']) ? $_REQUEST['fields'] : "BudgetDesc";
        $where .= ' and ' . $field . ' like :' . $field;
        $param[':' . $field] = '%' . $_REQUEST['query'] . '%';
    }

	$list = ACC_budgets::Get($where . dataReader::makeOrder(), $param);
	print_r(ExceptionHandler::PopAllExceptions());
    $count = $list->rowCount();

    if (isset($_GET["start"]) && !isset($_GET["All"]))
        $list = PdoDataAccess::fetchAll($list, $_GET["start"], $_GET["limit"]);
    else
        $list = $list->fetchAll();

    echo dataReader::getJsonData($list, $count, $_GET['callback']);
    die();
}

function SaveBudget() {

    $obj = new ACC_budgets();
    pdoDataAccess::FillObjectByJsonData($obj, $_POST['record']);

    if ($obj->BudgetID == '')
        $result = $obj->Add();
    else
        $result = $obj->Edit();
	
    Response::createObjectiveResponse($result, "");
    die();
}

function DeleteBudget() {

	$obj = new ACC_budgets($_POST["BudgetID"]*1);
	$obj->IsActive = "NO";
	$result = $obj->Edit();
    Response::createObjectiveResponse($result, '');
    die();
}

function ActivateBudget(){
	
	$obj = new ACC_budgets($_POST["BudgetID"]*1);
	$obj->IsActive = "YES";
	$result = $obj->Edit();
    Response::createObjectiveResponse($result, '');
    die();
}

?>
