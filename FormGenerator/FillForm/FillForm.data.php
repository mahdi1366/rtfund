<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../header.inc.php';
require_once 'FillForm.class.php';
require_once '../BuildForms/form.class.php';
require_once inc_dataReader;
require_once inc_response;

if(!empty($_REQUEST["task"]))
      $_REQUEST["task"]();

function GetFillFormElements() {
	
    $res = FRG_FillFormElems::Get(" AND FillFormID=?", array($_REQUEST['FillFormID']));
    echo dataReader::getJsonData($res->fetchAll(),$res->rowCount(), $_GET["callback"]);
    die();
}

function SaveFillForm() {
   
	$FillFormID = $_POST["FillFormID"];
	
	$pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	
	FRG_FillFormElems::RemoveAll($FillFormID, $pdo);
	
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack();
        print_r(ExceptionHandler::PopAllExceptions());
        //echo PdoDataAccess::GetLatestQueryString();
        echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
        die();
	}	
	if(isset($_POST["record"]))
	{
		$st = stripslashes(stripslashes($_POST["record"]));
		$data = json_decode($st);
	
		$Obj = new FRG_FillFormElems();
		$Obj->RowID = $data->RowID;
		$Obj->FillFormID = $data->FillFormID;
		$Obj->ElementID = $data->ElementID;

		$xml = new SimpleXMLElement('<root/>');
		$elems = array_keys(get_object_vars($data));
		foreach($elems as $el)
		{
			if(strpos($el, "ElementID_") === false)
				continue;
			$str = $data->$el;
			
			if(strlen($str) > 10 && substr($str,10) == "T00:00:00")
				$str = substr($str,0,10);	
			
			if(strlen($str) == 10 && $str[4] == "-" && $str[7] == "-")
				$str = preg_replace('/\-/', "/", $str);
			
			$xml->addChild($el, $str);
		}
		$Obj->ElementValue = $xml->asXML();
	}
	else
	{
		/* Adding the values of Form Elements */
		foreach ($_POST as $PostData => $val) 
		{
			if(empty($val))
				continue;

			if (!(substr($PostData, 0, 10) == "ElementID_"))
				continue;

			$items = explode('_', $PostData);
			$ElementID = $items[1];

			$Obj = new FRG_FillFormElems();
			$Obj->FillFormID = $FillFormID;
			$Obj->ElementID = $ElementID;

			$elementObj = new FRG_FormElems($ElementID);
			switch ($elementObj->ElementType) {
				case 'shdatefield':
					$Obj->ElementValue = DateModules::shamsi_to_miladi($val);
					break;
				default :
					$Obj->ElementValue = $val;
			}
			if($Obj->RowID > 0)
				$Obj->Edit($pdo);
			else
				$Obj->Add($pdo);
		}
	}
	
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack();
        print_r(ExceptionHandler::PopAllExceptions());
        //echo PdoDataAccess::GetLatestQueryString();
        echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
        die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}


?>
