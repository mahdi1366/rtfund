<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
require_once '../header.inc.php';
require_once 'post.class.php';
require_once inc_response;
require_once inc_dataReader;
require_once inc_component;
require_once inc_manage_unit;
require_once inc_QueryHelper;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "fullSelect":
		fullSelect();

	case "selectPost":
		selectPost();

	case "SavePost":
		SavePost();

	case "DeletePost":
		DeletePost();

	case "MovePost":
		MovePost();
}

function selectPost()
{
	$where = "1=1";
	$whereParam = array();

	//-----------------------
	if(!empty($_POST["post_id"]))
	{
		$where .= "post_id=:pid";
		$whereParam[":pid"] = $_POST["post_id"];
	}
	
	if(!empty($_POST["title"]))
	{
		$where .= "title like :title";
		$whereParam[":pid"] = "%" . $_POST["title"] . "%";
	}
	if(!empty($_POST["org_units"]) && $_POST["org_units"] != -1)
	{
		$result = QueryHelper::MK_org_units($_POST["ouid"], isset($_POST["sub_units"]) ? true : false);
		$where .= " AND " . $result["where"];
		$whereParam = array_merge($whereParam, $result["param"]);
	}
	//-----------------------
	$field = isset ( $_GET ["fields"] ) ? $_GET ["fields"] : "";

	if (isset ( $_GET ["query"] ) && $_GET ["query"] != "")
	{
		switch ( $field)
		{
			case "post_id" :
				$where .= " AND p.post_id = :postid " ;
				$whereParam[":postid"] = $_GET["query"];
				break;
			case "post_no" :
				$where .= " AND p.post_no = :postno " ;
				$whereParam[":postno"] = $_GET["query"];
				break;
			
			case "unitTitle":
				$where .= " AND o.ptitle LIKE :ot ";
				$whereParam[":ot"] = "%" . $_GET["query"] . "%";
				break;
			case "":
			case "title" :
				$where .= " AND p.title LIKE :title ";
				$whereParam[":title"] = "%" . $_GET["query"] . "%";
				break;
			case "full_unit_title" :
				$qry = " select ouid from org_new_units where ptitle like '%".$_GET["query"]."%'" ; 
				$temp = PdoDataAccess::runquery($qry);
				$wh = "(";
				for($j=0; $j<count($temp) ; $j++)
				{
					$wh .= $temp[$j]['ouid'] ;
                                        $wh .= "," ;
				}		
				$wh = substr($wh,0,-1) ; 
				$wh .= ")";
				$where .= " AND o.ouid in ".$wh ;				
				break;			
			case "post_type_title" :
				$where .= " AND bi.Title LIKE :type ";
				$whereParam[":type"] = "%" . $_GET["query"] . "%";
				break;
			case "jobCategory" :
				$where .= " AND jc.title LIKE :jt ";
				$whereParam[":jt"] = "%" . $_GET["query"] . "%";
				break;
		}
	}
	$where .= (!empty($_REQUEST["CurrentPost"])) ? " AND parent_post_id=" . $_REQUEST["CurrentPost"] : "";

	$temp = manage_posts::GetAllPosts($where, $whereParam);
	$no = count($temp);
	
	$temp = manage_posts::GetAllPosts($where . " limit " . $_GET["start"] . "," . $_GET["limit"], $whereParam);
	for($i=0; $i<count($temp); $i++)
	{
		if($temp[$i]["ouid"] != "")
			$temp[$i]["full_unit_title"] = manage_units::get_full_title($temp[$i]["ouid"]);
		else
			$temp[$i]["full_unit_title"] = "";
	}
	
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function fullSelect()
{
	$where = "1=1";
	$whereParam = array();

	//-----------------------
	if(!empty($_POST["post_no"]))
	{
		$where .= " AND p.post_no=:pno";
		$whereParam[":pno"] = $_POST["post_no"];
	}

	if(!empty($_POST["post_type"]) && $_POST["post_type"] != -1)
	{
		$where .= " AND p.post_type=:ptype";
		$whereParam[":ptype"] = $_POST["post_type"];
	}

	if(!empty($_POST["title"]))
	{
		$where .= " AND p.title like :title";
		$whereParam[":title"] = "%" . $_POST["title"] . "%";
	}

	if(!empty($_POST["ouid"]) && $_POST["ouid"] != -1)
	{
		$result = QueryHelper::MK_org_units($_POST["ouid"], isset($_POST["sub_units"]) ? true : false);
		$where .= " AND " . $result["where"];
		$whereParam = array_merge($whereParam, $result["param"]);
	}
	$no = count(manage_posts::GetAllPosts($where , $whereParam));

	$where .= isset ( $_GET ["sort"] ) ? " order by " . $_GET ["sort"] . " " . $_GET ["dir"] : "";
	$where .= isset($_GET ["start"]) ? " limit " . $_GET ["start"] . "," . $_GET ["limit"] : "";

	$temp = manage_posts::GetAllPosts($where , $whereParam);

	for($i=0; $i<count($temp); $i++)
	{
		if($temp[$i]["ouid"] != "")
			$temp[$i]["full_unit_title"] = manage_units::get_full_title($temp[$i]["ouid"]);
		else
			$temp[$i]["full_unit_title"] = "";
	}

	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function SavePost()
{
       
	$obj = new manage_posts();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	if(isset($_POST['included'])) 
	    $obj->included = 1 ; 
	else 
	    	    $obj->included = 0 ; 
	
	if(isset($_POST['ManagementCoef'])) 
	    $obj->ManagementCoef = 1 ; 
	else 
	    	    $obj->ManagementCoef = 0 ; 

	if($obj->jfid == "-1")
		$obj->jfid = PDONULL;

	if(empty($_POST["post_id"]))
	{
		$obj->parent_path = ($_POST["parent_path"] == "") ? $obj->parent_path : $_POST["parent_path"] . "," . $obj->parent_path;
		$obj->RegDate = date('Y-m-d');
		$obj->AddPost();
	}
	else
	{
		$obj->post_id = $_POST["post_id"];
		$obj->EditPost();
	}

	echo Response::createObjectiveResponse("true", $obj->post_id);
	die();
}

function DeletePost()
{
	$query = "select * from `position` where parent_post_id=" . $_REQUEST["post_id"];
	$temp = PdoDataAccess::runquery($query);
	if(count($temp) != 0)
	{
		echo "ChildError";
		die();
	}

	manage_posts::RemovePost($_POST["post_id"]);
	print_r(ExceptionHandler::PopAllExceptions());
	echo "true";
	die();
}

function MovePost()
{
	$obj = new manage_posts();
	$obj->parent_post_id = $_POST["desc_post_id"];
	$obj->parent_path = ($_POST["parent_path"] == "") ? $obj->parent_ouid : $_POST["parent_path"] . "," . $obj->parent_ouid;
	$obj->post_id = $_POST["source_post_id"];

	$obj->EditPost();

	echo "true";
	die();
}
//-------------------------------------------------------------------
function GetTreeNodes()
{
	$nodes = PdoDataAccess::runquery("select post_id as id,title as text,'true' as leaf
		from position where parent_post_id=0 or parent_post_id=0 is null order by title");

	$cur_level_uids = "";
	$returnArray = $nodes;

	$ref_cur_level_nodes = array();
	for($i=0; $i<count($nodes); $i++)
	{
		$ref_cur_level_nodes[] = & $returnArray[$i];
		$cur_level_uids .= $nodes[$i]["id"] . ",";
	}
	$cur_level_uids = substr($cur_level_uids, 0, strlen($cur_level_uids) - 1);

	while (true)
	{
		$nodes = PdoDataAccess::runquery("select post_id as id,title as text,'true' as leaf,parent_post_id as parentId
			from position where parent_post_id in (" . $cur_level_uids . ") order by title");

		if(count($nodes) == 0)
			break;
		//............ add current level nodes to returnArray ................
		$temp_ref = array();
		$cur_level_uids = "";

		for($i=0; $i<count($nodes); $i++)
		{
			//............ extract current level uids ..................
			$cur_level_uids .= $nodes[$i]["id"] . ",";

			for($j=0; $j < count($ref_cur_level_nodes); $j++)
			{
				if($nodes[$i]["parentId"] == $ref_cur_level_nodes[$j]["id"])
				{
					if(!isset($ref_cur_level_nodes[$j]["children"]))
					{
						$ref_cur_level_nodes[$j]["children"] = array();
						$ref_cur_level_nodes[$j]["leaf"] = "false";
					}
					$ref_cur_level_nodes[$j]["children"][] = $nodes[$i];
					$temp_ref[] = & $ref_cur_level_nodes[$j]["children"][count($ref_cur_level_nodes[$j]["children"])-1];
					break;
				}
			}
		}

		$ref_cur_level_nodes = $temp_ref;
		$cur_level_uids = substr($cur_level_uids, 0, strlen($cur_level_uids) - 1);
	}

	//print_r($returnArray);

	$return_str = '{"text":"پست های سازمانی","id":"source",';
	$return_str .= (count($returnArray) == 0) ? '"leaf":true' : '"children":';
	if(count($returnArray) != 0)
		$return_str .= json_encode($returnArray);
	$return_str .= '}';
	return $return_str;
}

?>