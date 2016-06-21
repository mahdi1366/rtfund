<?php
//---------------------------
// programmer:	Sh.Jafarkhani
// Date:		90.02
//---------------------------
require_once '../../../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset($_REQUEST["task"]) ? $_REQUEST["task"] : "";

switch ($task)
{
	case "selectUsers":
		selectUsers();

	case "selectCostCenters":
		selectCostCenters();

	case "saveCostAccess":
		saveCostAccess();

	//-----------------------

	case "selectPersonTypes":
		selectPersonTypes();
		
	case "savePersonTypeAccess":
		savePersonTypeAccess();
}


function selectUsers()
{
    $query = "
        select distinct s.UserID,p.pfname,plname
        from UsersSystems as s
            join AccountSpecs a on(s.UserID=a.WebUserID)
            join persons p on(p.PersonID=a.PersonID)
        where SysCode in (" . PersonalSystemCode . "," . SalarySystemCode . ") AND UserSystemStatus <> 'DELETE' ";

	$whereParam = array();
    if(!empty($_GET["fields"]) && !empty($_GET["query"]))
	{
		switch($_GET["fields"])
		{
			case "UserID":
				$query .= " AND s.UserID like :userid";
				$whereParam[":userid"] = "%" . $_GET["query"] . "%";
				break;
			case "pfname":
				$query .= " AND p.pfname like :pfname";
				$whereParam[":pfname"] = "%" . $_GET["query"] . "%";
				break;
			case "plname":
				$query .= " AND p.plname like :plname";
				$whereParam[":plname"] = "%" . $_GET["query"] . "%";
				break;
		}
	}
	
	$dt = PdoDataAccess::runquery($query . dataReader::makeOrder(), $whereParam);
	$no = count($dt);

	$dt = array_slice($dt, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($dt, $no, $_GET["callback"]);
	die();
}

function selectCostCenters()
{
	$UserID = $_POST["UserID"];
	
	$dt = PdoDataAccess::runquery("
		select cc.cost_center_id,cc.title,if(cca.cost_center_id is null,0,1) access
		from cost_centers as cc 
			left join cost_center_access as cca on(cc.cost_center_id=cca.cost_center_id AND cca.UserID=?)
		order by title", array($UserID));

    echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
	
}

function saveCostAccess()
{
	$UserID = $_POST["UserID"];
	PdoDataAccess::runquery("delete from cost_center_access where UserID=?", array($UserID));

	$query = "insert into cost_center_access values";
	$whereParam = array();
	$keys = array_keys($_POST);
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chk_") !== false)
		{
			$arr = preg_split('/_/', $keys[$i]);
			
			if($arr[1] !=46) {
				$query .= "(:uid,:c" . $i . "),";
				$whereParam[":c" . $i] = $arr[1];
			}
		}
	}

	if(count($whereParam) == 0)
	{
		echo "true";
		die();
	}

	$query = substr($query, 0, strlen($query)-1);
	$whereParam[":uid"] = $UserID;
	PdoDataAccess::runquery($query, $whereParam);
	echo "true";
	die();
}

//--------------------------------

function selectPersonTypes()
{
	$UserID = $_POST["UserID"];

	$dt = PdoDataAccess::runquery("
		select b.InfoID,b.Title,if(pa.person_type is null,0,1) access
		from Basic_Info as b
			left join person_type_access as pa on(b.InfoID=pa.person_type AND pa.UserID=?)
		where b.TypeID=16
		order by b.InfoID", array($UserID));

    echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();

}

function savePersonTypeAccess()
{
	$UserID = $_POST["UserID"];
	PdoDataAccess::runquery("delete from person_type_access where UserID=?", array($UserID));

	$query = "insert into person_type_access values";
	$whereParam = array();
	$keys = array_keys($_POST);
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chk_") !== false)
		{
			$arr = preg_split('/_/', $keys[$i]);
			$query .= "(:uid,:c" . $i . "),";
			$whereParam[":c" . $i] = $arr[1];
		}
	}

	if(count($whereParam) == 0)
	{
		echo "true";
		die();
	}

	$query = substr($query, 0, strlen($query)-1);
	$whereParam[":uid"] = $UserID;
	PdoDataAccess::runquery($query, $whereParam);
	echo "true";
	die();
}
?>
