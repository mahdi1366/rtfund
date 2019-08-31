<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.06
//---------------------------

require_once '../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once './store.class.php';
require_once 'TreeModules.class.php';

ini_set("display_errors", "On");

$task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';
switch ($task) {

	case "SelectGoods":
	case "SaveGood":
	case "DeleteGood":
	case "GetGoodsTree":
	case "SelectGoodScales":
		
		$task();
};

function SelectGoods() {

    $where = " AND IsActive='YES'";
    $param = array();

    if (!empty($_REQUEST['query'])) {
		$field = isset($_REQUEST['fields']) ? $_REQUEST['fields'] : "BudgetDesc";
        $where .= ' and ' . $field . ' like :' . $field;
        $param[':' . $field] = '%' . $_REQUEST['query'] . '%';
    }

	$list = STO_goods::Get($where . dataReader::makeOrder(), $param);
	print_r(ExceptionHandler::PopAllExceptions());
    $count = $list->rowCount();

    if (isset($_GET["start"]) && !isset($_GET["All"]))
        $list = PdoDataAccess::fetchAll($list, $_GET["start"], $_GET["limit"]);
    else
        $list = $list->fetchAll();

    echo dataReader::getJsonData($list, $count, $_GET['callback']);
    die();
}

function SaveGood() {

    $obj = new STO_goods();
    pdoDataAccess::FillObjectByArray($obj, $_POST);

    if ($obj->GoodID == '')
        $result = $obj->Add();
    else
        $result = $obj->Edit();
	
    Response::createObjectiveResponse($result, "");
    die();
}

function DeleteGood() {

	$obj = new STO_goods($_POST["GoodID"]*1);
	$obj->IsActive = "NO";
	$result = $obj->Edit();
    Response::createObjectiveResponse($result, '');
    die();
}

function GetGoodsTree() {

	$nodes = PdoDataAccess::runquery("
			select * from STO_goods  where IsActive='YES'
			order by ParentID,GoodName");
	$returnArr = TreeModulesclass::MakeHierarchyArray($nodes, "ParentID", "GoodID", "GoodName");
	//print_r(ExceptionHandler::PopAllExceptions());
	echo json_encode($returnArr);
	die();
}

function SelectGoodScales(){
	
	$list = PdoDataAccess::runquery("select * from BaseInfo where TypeID=94");
	echo dataReader::getJsonData($list, count($list), $_GET['callback']);
    die();
} 
//...................................

?>
