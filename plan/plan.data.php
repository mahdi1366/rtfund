<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------

include_once('header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'plan.class.php';

$task = $_REQUEST["task"];
switch ($task) {
		
	case "selectSubGroups":
		selectSubGroups();
	
}

function selectSubGroups(){
	
	$ParentID = $_GET["ParentID"];
	
	$nodes = PdoDataAccess::runquery("
			select p4.ParentID, p4.GroupID id, p4.GroupDesc text , 'true' leaf , 'false' expanded, '' iconCls
			from PLN_groups  p4
				left join PLN_groups p3 on(p4.ParentID=p3.GroupID)  
				left join PLN_groups p2 on(p3.ParentID=p2.GroupID)
				left join PLN_groups p1 on(p2.ParentID=p1.GroupID)
				
			where (p4.ParentID=:p or p3.ParentID=:p or p2.ParentID=:p or p1.ParentID=:p)", array(":p" => $ParentID));
		
	$returnArr = array(); 
	$refArr = array();
	
	foreach($nodes as $node)
	{
		$parentNode = &$refArr[$node["ParentID"]];
		if(!isset($parentNode))
		{
			$node["text"] = "[ " . (count($returnArr)+1) . " ] " . $node["text"];
			$returnArr[] = $node;
			$refArr[ $node["id"] ] = &$returnArr[ count($returnArr)-1 ];
			continue;
		}

		if (!isset($parentNode["children"])) {
			$parentNode["children"] = array();
			$parentNode["leaf"] = "false";
		}
		$lastIndex = count($parentNode["children"]);
		$parentNode["children"][$lastIndex] = $node;
	}

	echo json_encode($returnArr);
	die();
}

?>
