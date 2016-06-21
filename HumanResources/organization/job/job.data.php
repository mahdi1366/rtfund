<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
require_once '../header.inc.php';
require_once 'job.class.php';
require_once inc_response;
require_once inc_component;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "JobCategorySave":
		JobCategorySave();
		
	case "JobCategoryDelete":
		JobCategoryDelete();
		
	case "JobSubCategorySave":
		JobSubCategorySave();
		
	case "JobSubCategoryDelete":
		JobSubCategoryDelete();
		
	case "JobFieldSave":
		JobFieldSave();
	
	case "DeleteJobField":
		DeleteJobField();

	case "MoveJobField":
		MoveJobField();
}

function JobCategorySave()
{
	if(empty($_POST["id"]))
	{
		$query = "insert into job_category(title) values('" . $_POST["title"] . "')";
		PdoDataAccess::runquery($query);
		$id = PdoDataAccess::InsertID();

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $id;
		$daObj->TableName = "job_category";
		$daObj->execute();

		echo Response::createObjectiveResponse("true", $id);
	}
	else
	{
		$query = "update job_category set title = '" . $_POST["title"] . "' where jcid=" . $_POST["id"];
		PdoDataAccess::runquery($query);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $_POST["id"];
		$daObj->TableName = "job_category";
		$daObj->execute();
		
		echo Response::createObjectiveResponse("true", $_POST["id"]);
	}	
	die();
}

function JobCategoryDelete()
{
	PdoDataAccess::runquery("delete from job_category where jcid=" . $_POST["id"]);

	$daObj = new DataAudit();
	$daObj->ActionType = DataAudit::Action_delete;
	$daObj->MainObjectID = $_POST["id"];
	$daObj->TableName = "job_category";
	$daObj->execute();

	echo "true";
	die();
}

//-------------------------------------------------------------------

function JobSubCategorySave()
{
	if(empty($_REQUEST["oldjsid"]))
	{
		$query = "select * from job_subcategory where jcid=" . $_POST["jcid"] . " and jsid=" . $_POST["jsid"];
		$temp = PdoDataAccess::runquery($query);
		if(count($temp) > 0)
		{
			echo "duplicateError";
			die();
		}
		$query = "insert into job_subcategory(jcid,jsid,title) 
				values(" . $_POST["jcid"] . "," . $_POST["jsid"] . ",'" . $_POST["title"] . "')";
		PdoDataAccess::runquery($query);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $_POST["jcid"];
		$daObj->SubObjectID = $_POST["jsid"];
		$daObj->TableName = "job_subcategory";
		$daObj->execute();
	}
	else
	{
		if($_REQUEST["oldjsid"] != $_POST["jsid"])
		{
			$query = "select * from job_subcategory where jcid=" . $_POST["jcid"] . " and jsid=" . $_POST["jsid"];
			$temp = PdoDataAccess::runquery($query);
			if(count($temp) > 0)
			{
				echo "duplicateError";
				die();
			}
		}
		$query = "update job_subcategory set jsid=" . $_POST["jsid"] . ",title='" . $_POST["title"] . 
			"' where jsid=" . $_REQUEST["oldjsid"] . " and jcid=" . $_POST["jcid"];
		
		PdoDataAccess::runquery($query);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $_POST["jcid"];
		$daObj->SubObjectID = $_POST["jsid"];
		$daObj->TableName = "job_subcategory";
		$daObj->execute();
	}
	echo "true";
	die();
}

function JobSubCategoryDelete()
{
	PdoDataAccess::runquery("delete from job_subcategory where jcid=" . $_POST["jcid"] . " and jsid=" . $_POST["jsid"]);

	$daObj = new DataAudit();
	$daObj->ActionType = DataAudit::Action_delete;
	$daObj->MainObjectID = $_POST["jcid"];
	$daObj->SubObjectID = $_POST["jsid"];
	$daObj->TableName = "job_subcategory";
	$daObj->execute();

	echo "true";
	die();
}

//-------------------------------------------------------------------

function JobFieldSave()
{
	$obj = new be_jobField();
	PdoDataAccess::FillObjectByArray($obj, $_POST);

	if($_POST["mode"] == "new")
	{
		$obj->Add();
	}
	else
	{
		$obj->Edit();
	}	
	echo "true";
	die();
}

function DeleteJobField()
{
	be_jobField::remove($_POST["jfid"]);
	echo "true";
	die();
}

function MoveJobField()
{
	$jfid = $_POST["jfid"];
	$jcid = $_POST["jcid"];
	$jsid = $_POST["jsid"];

	$obj = new be_jobField();
	$obj->jfid = $jfid;
	$obj->jcid = $jcid;
	$obj->jsid = $jsid;

	$return = $obj->Edit();
	echo $return ? "true" : "false";
	die();
}
//-------------------------------------------------------------------

function GetTreeNodes()
{
	$returnArray = PdoDataAccess::runquery("select jcid as id, title as text,'true' as leaf from job_category order by jcid");
	$dt = PdoDataAccess::runquery("select jcid,jsid as id,title as text,'true' as leaf from job_subcategory order by jcid,title");
	$dt2 = PdoDataAccess::runquery("select jcid,jsid,jfid as id,title as text,'true' as leaf from job_fields order by jcid,jsid,title");
	
	$rowIndex = 0;
	$current_devotion = $returnArray[$rowIndex]["id"];
	
	for($i=0; $i<count($dt); $i++)
	{
		if($current_devotion == $dt[$i]["jcid"])
		{
			if(!isset($returnArray[$rowIndex]["children"]))
			{
				$returnArray[$rowIndex]["children"] = array();
				$returnArray[$rowIndex]["leaf"] = "false";
			}
			$lastIndex = count($returnArray[$rowIndex]["children"]);
			$returnArray[$rowIndex]["children"][$lastIndex] = $dt[$i];
			
			$dt2Copy = $dt2;
			$keys = array_keys($dt2);
			
			for($j=0; $j < count($keys); $j++)
			{
				if($dt2[$keys[$j]]["jcid"] == $dt[$i]["jcid"] && $dt2[$keys[$j]]["jsid"] == $dt[$i]["id"])
				{
					$returnArray[$rowIndex]["children"][$lastIndex]["children"][] = $dt2[$keys[$j]];
					unset($dt2Copy[$keys[$j]]);
				}
			}
			$dt2 = $dt2Copy;
			
		}
		else //reset to next devotion 
		{
			$rowIndex++;
			$current_devotion = $returnArray[$rowIndex]["id"];
			$i--;
		}
	}
	

	//print_r($returnArray);
	$str = '{"text":"طبقه بندی مشاغل","id":"source",';
	$str .= (count($dt) == 0) ? '"leaf":true' : '"children":';
	if(count($returnArray) != 0)
		$str .= json_encode($returnArray);
	$str .= (count($dt) == 0) ? '}' : '}';
	
	return $str;
}

function GetAll_job_types_dropdown($drp_id, $selectedID = "", $extraRow = "")
{
	$obj = new DROPDOWN();

	$obj->datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID=22");
	$obj->valuefield = "%InfoID%";
	$obj->textfield = "%Title%";
	$obj->Style = 'class="x-form-text x-form-field" style="width:90%"';
	$obj->id = $drp_id;

	if(!empty($extraRow))
		$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow))
		,$obj->datasource);
	return $obj->bind_dropdown($selectedID);
}

function GetAll_job_levels_dropdown($drp_id, $selectedID = "", $extraRow = "")
{
	$obj = new DROPDOWN();

	$obj->datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID=23");
	$obj->valuefield = "%InfoID%";
	$obj->textfield = "%Title%";
	$obj->Style = 'class="x-form-text x-form-field" style="width:90%"';
	$obj->id = $drp_id;

	if(!empty($extraRow))
		$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow))
		,$obj->datasource);
	return $obj->bind_dropdown($selectedID);
}

function JobCategory_subCategory_dropdowns(&$OutputDrp, &$Output2Drp, $masterName, $detailName)
{
	$obj = new MaserDetail_DROPDOWN();
	
	$obj->Master_datasource = PdoDataAccess::runquery("select jcid,title from job_category order by title");
	
	$obj->Master_id = $masterName;
	$obj->Master_valuefield = "jcid";
	$obj->Master_textfield = "title";
	$obj->Master_Width = "200";
	
	$obj->Detail_datasource = PdoDataAccess::runquery("select jcid,jsid,title from job_subcategory order by title");
	
	$obj->Detail_id = $detailName;
	$obj->Detail_valuefield = "jsid";
	$obj->Detail_textfield = "title";
	$obj->Detail_masterfield = "jcid";
	
	$obj->bind_dropdown(&$OutputDrp, &$Output2Drp,"", "");
}
//-------------------------------------------------------------------


?>