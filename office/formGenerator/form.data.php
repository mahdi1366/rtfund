<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------
require_once '../header.inc.php';
require_once 'form.class.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "formsSelect":
		formsSelect();
	
	case "formDelete":
		formDelete();
		
	case "formSave":
		formSave();

	case "RemoveFile":
		RemoveFile();
	//........................
	
	case "SelectSteps":
		SelectSteps();
		
	case "SaveSteps":
		SaveSteps();
		
	case "DeleteSteps":
		DeleteSteps();
	
	case "ChangeLevel":
		ChangeLevel();
	//........................
		
	case "SelectElements":
		SelectElements();
		
	case "elementDelete":
		elementDelete();
		
	case "elementSave":
		elementSave();
		
	//........................
	case "GetPursuitCode":
		echo FormGenerator::GetPursuitCode($_POST["LetterID"]);
		die();
}

function formsSelect()
{
	$where = "1=1" . dataReader::makeOrder();
	
	$dt = FGR_forms::select($where);
	$no = $dt->rowCount();
	
	$temp = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);	
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function formDelete()
{
	$res = FGR_forms::RemoveForm($_POST["FormID"]);
	echo Response::createObjectiveResponse($res, "");
	die();
}

function formSave()
{
	$obj = new FGR_forms();
	
	$obj->FormID = $_POST["FormID"];
	$obj->FormName = $_POST["FormName"];
	$obj->reference = $_POST["reference"];
	
	if($obj->FormID == "")
		$res = $obj->AddForm();
	else 
		$res = $obj->EditForm();
	
	//-------------------- file upload ------------------------------
	if ($res &&  isset($_FILES ['attach']) && trim($_FILES['attach']['tmp_name']) != '') 
	{
		$st = split ( '\.', $_FILES ['attach'] ['name'] );
		$extension = $st [count ( $st ) - 1];
		
		$fp = fopen(getenv("DOCUMENT_ROOT") . "/attachment/office/forms/" . $obj->FormID . "." . $extension, "w");
		fwrite ($fp, fread ( fopen ( $_FILES ['attach'] ['tmp_name'], 'r' ), $_FILES ['attach']['size']));
		fclose ($fp);
		
		$obj->FileInclude = "YES";
		$obj->EditForm();
		
	}
	//print_r(ExceptionHandler::PopAllExceptions());
	Response::createObjectiveResponse($res, "");
	die();
}

function RemoveFile()
{
	dataAccess::RUNQUERY("update fm_forms set FileType='' where FormID=" . $_REQUEST["FormID"]);
	unlink("../../" . FormImagePath . "form" . $_REQUEST["FormID"] . "." . $_REQUEST["FileType"]);
	dataAccess::AUDIT("حذف فایل فرم کد[" . $_REQUEST["FormID"] . "]");
	echo "true";
	die();
}

//...................................................................

function SelectSteps()
{
	$temp = dataAccess::RUNQUERY("select w.*, 
		concat(u.name,' ',u.family)as fullName,
		group_concat(a.ElementID) as elements
		
		from fm_workflow as w 
			join um_user as u using(PersonID) 
			left join fm_element_access as a 
				on(w.FormID=a.FormID and w.PersonID=a.PersonID and w.StepID=a.StepID)
					
		where w.FormID=" . $_REQUEST["FormID"] . "
		group by w.FormID,StepID");
	
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveSteps()
{
	
	$StepID = (!empty($_POST["StepID"])) ? $_POST["StepID"] : 
		dataAccess::GetLastID("fm_workflow", "StepID", "FormID=" . $_POST["FormID"]) + 1;
	
	dataAccess::RUNQUERY("insert into fm_workflow values(" . $_POST["FormID"] . ",$StepID,'" .
		$_POST["StepTitle"] . "'," . $_POST["PersonID"] . "," . $_POST["BreakDuration"] . ")
		on duplicate key update StepTitle='" . $_POST["StepTitle"] . "', 
								PersonID=" . $_POST["PersonID"] . ",
								BreakDuration=" . $_POST["BreakDuration"]);

	dataAccess::AUDIT("ایجاد/ویرایش مرحله [$StepID] گردش برای فرم کد[" . $_POST["FormID"] . "]");
	
	//.............................
	dataAccess::RUNQUERY("delete from fm_element_access where FormID=" . $_POST["FormID"] . 
		" and StepID=" . $StepID);
	
	$tmp = array_keys($_POST);
	for ($j = 0; $j < count($_POST); $j++)
	{
		if(substr($tmp[$j], 0, strlen("elem_")) == "elem_")
		{
			$ElementID = substr($tmp[$j], strlen("elem_"));
			dataAccess::RUNQUERY("insert into fm_element_access values(" . $_POST["PersonID"] . "," .
				$_POST["FormID"] . "," . $ElementID . "," . $StepID . ")");
		}
	}
	
	if(isset($_POST["referenceApply"]))
		dataAccess::RUNQUERY("insert into fm_element_access values(" . $_POST["PersonID"] . "," .
				$_POST["FormID"] . ",2000," . $StepID . ")");
	if(isset($_POST["CopyAccess"]))
		dataAccess::RUNQUERY("insert into fm_element_access values(" . $_POST["PersonID"] . "," .
				$_POST["FormID"] . ",2001," . $StepID . ")");
	//.............................
	echo "true";
	die();
}

function DeleteSteps()
{
	$temp = dataAccess::RUNQUERY("delete from fm_workflow where FormID=" . $_REQUEST["FormID"] . 
		" and StepID=" . $_POST["StepID"]);
	
	dataAccess::RUNQUERY("update fm_workflow set StepID=StepID-1 where FormID=" . $_REQUEST["FormID"] . 
		" and StepID>" . $_POST["StepID"]);
	
	dataAccess::AUDIT("حذف مرحله [" . $_POST["StepID"] . "] گردش فرم [" . $_REQUEST["FormID"] . "]");
	die();
}

function ChangeLevel()
{
	$curStepID = $_POST["StepID"];
	$secStepID = ($_POST["direction"] == "up") ? $_POST["StepID"] - 1 : $_POST["StepID"] + 1;
	
	dataAccess::RUNQUERY("update fm_workflow set StepID=10000 where StepID=" . $secStepID . " and FormID=" . $_POST["FormID"]);
	dataAccess::RUNQUERY("update fm_workflow set StepID=$secStepID where StepID=" . $curStepID . " and FormID=" . $_POST["FormID"]);
	dataAccess::RUNQUERY("update fm_workflow set StepID=$curStepID where StepID=10000 and FormID=" . $_POST["FormID"]);
	
	echo "true";
	die();
}

//...................................................................

function SelectElements()
{
	$no = count(FormElements::select("FormID=" . $_GET["FormID"]));
	
	$temp = FormElements::select("FormID=" . $_GET["FormID"] . " order by " . $_GET["sort"] . " " . $_GET["dir"]);
	
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function elementDelete()
{
	FormElements::delete("ElementID=" . $_POST["ElementID"]);
	dataAccess::AUDIT("حذف جزء قالب فرم با عنوان [" . $_POST["ElementTitle"] . "]");
	echo "true";
	die();
}

function elementSave()
{
	$obj = new FormElements();
	
	if(isset($_POST["FormID"]))			$obj->FormID = $_POST["FormID"];
	if(isset($_POST["ElementTitle"]))	$obj->ElementTitle = $_POST["ElementTitle"];
	if(isset($_POST["ElementType"]))	$obj->ElementType = $_POST["ElementType"];
	if(isset($_POST["ElementValue"]))	$obj->ElementValue = $_POST["ElementValue"];
	if(isset($_POST["width"]))			$obj->width = $_POST["width"];
	if(isset($_POST["referenceDesc"]) && $_POST["referenceDesc"] != "---")	
		$obj->referenceDesc = $_POST["referenceDesc"];
		
	if(isset($_POST["ordering"]))		$obj->ordering = $_POST["ordering"];
	
	
	if(isset($_POST["referenceField"]))
	{
		if(strpos($_POST["referenceField"], "info_") === false)
		{
			$obj->referenceField = $_POST["referenceField"];
			$obj->referenceInfoID = 0;
		}
		else 
		{
			$st = split('_', $_POST["referenceField"]);
			$obj->referenceField = $st[1];
			$obj->referenceInfoID = $st[2];
		}
	}
	else 
	{
		$obj->referenceField = "";
		$obj->referenceInfoID = 0;
	}
	//----------------------------------------------------------------------
	
	if($_POST["ElementID"] == "")
	{
		$obj->ElementID = null;
		$obj->insert();
		$obj->ElementID = FormElements::LastID();
		dataAccess::AUDIT("ایجاد اجراء قالب فرم با کد[" . $obj->ElementID . "]");
	}
	else 
	{
		$obj->ElementID = $_POST["ElementID"];
		$obj->update("ElementID=" . $obj->ElementID);
		dataAccess::AUDIT("ویرایش اجراء قالب فرم با کد[" . $obj->ElementID . "]");
	}
	echo "true";
	die();
}

?>