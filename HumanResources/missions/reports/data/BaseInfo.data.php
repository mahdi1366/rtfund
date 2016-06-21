<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.02
//-----------------------------
//ini_set('display_errors', 'On'); error_reporting(E_ERROR);
ini_set('display_errors', 'Off');
require_once "../../header.inc.php";
require_once 'definitions.php';
require_once '../../../generalUI/ext4/response.class.php';
require_once '../../../generalUI/ext4/dataReader.class.php';
require_once '../../../generalClasses/PDODataAccess.class.php';

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
    case "SelectMissionTypes":
        SelectMissionTypes();

    case "SelectMissionVehicles":
        SelectMissionVehicles();

    case "SelectMissionLocations":
        SelectMissionLocations();

    case "SelectRequestTypes":
        SelectRequestTypes();
}

function SelectMissionTypes() {
    $res = PdoDataAccess::runquery("SELECT InfoID as type , title as name FROM hrmstotal.Miss_BaseInfo where TypeID = 3");
    echo dataReader::getJsonData($res, count($res), $_GET["callback"]);
    die();
}

function SelectMissionVehicles() {
    $res = PdoDataAccess::runquery("SELECT InfoID as type , title as name FROM hrmstotal.Miss_BaseInfo where TypeID = 2");
    echo dataReader::getJsonData($res, count($res), $_GET["callback"]);
    die();
}

function SelectMissionLocations() {
    $where = " 1=1 ";
    $whereParams = array();
    if (!empty($_REQUEST['query'])) {
        $where .= " AND cities.ptitle like ? OR  states.ptitle like ? ";
        $whereParams[] = "%" . $_REQUEST['query'] . "%";
        $whereParams[] = "%" . $_REQUEST['query'] . "%";
    }
    if (!empty($_REQUEST['city_id'])) {
        $where .= " AND city_id= ? ";
        $whereParams[] = $_REQUEST['city_id'];
    }
    if (!empty($_REQUEST['state_id'])) {
        $where .= " AND state_id= ? ";
        $whereParams[] = $_REQUEST['state_id'];
    }

    $query = "SELECT cities.city_id ,states.state_id , cities.ptitle as cname , states.ptitle as sname        
            FROM hrmstotal.cities join hrmstotal.states using (state_id)
            where $where";
    $res = PdoDataAccess::runquery_fetchMode($query, $whereParams);
    $no = $res->rowCount();
    $res_limited = PdoDataAccess::fetchAll($res, $_GET["start"], $_GET["limit"]);
    echo dataReader::getJsonData($res_limited, $no, $_GET['callback']);
    die();
}

function SelectRequestTypes() {
    $query = "select InfoID , title from hrmstotal.Basic_Info where TypeID = " . TYPE_STATUS;
    if (isset($_REQUEST['NotRaw']) && $_REQUEST["NotRaw"] == true){
        $query .= " and InfoID != " . DRAFT;
    }
    $res = PdoDataAccess::runquery($query);    
    echo dataReader::getJsonData($res, count($res), $_GET['callback']);    
    die();
}

?>
