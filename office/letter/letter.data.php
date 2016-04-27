<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.10
//---------------------------

require_once '../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once 'letter.class.php';
require_once '../dms/dms.class.php';

$task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';

switch ($task) {
	
	case "SelectLetter":
		SelectLetter();
		
	case "SelectAllLetter":
		SelectAllLetter();
		
    case 'SelectDraftLetters':
        SelectDraftLetters();

	case "SelectSendedLetters":
		SelectSendedLetters();
		
	case "SelectReceivedLetters":
		SelectReceivedLetters();
		
	case "SelectArchiveLetters":
		SelectArchiveLetters();
		
	//.....................................
    case 'SaveLetter':
        SaveLetter();

    case 'DeleteLetter':
        deleteLetter();
		
	case "selectLetterPages":
		selectLetterPages();
		
	case "DeletePage":
		DeletePage();
	
	//.............................................
		
	case "selectSendTypes":
		selectSendTypes();
		
	//.............................................
		
	case "SendLetter":
		SendLetter();
		
	case "ReturnSend":
		ReturnSend();	
		
	case "SignLetter":
		SignLetter();
		
	case "DeleteSend":
		DeleteSend();
		
	//...............................................
		
	case "SelectArchiveNodes":
		SelectArchiveNodes();
		
	case "SaveFolder":
		SaveFolder();
		
	case "DeleteFolder":
		DeleteFolder();
		
	case "AddLetterToFolder":
		AddLetterToFolder();
		
	case "RemoveLetterFromFolder":
		RemoveLetterFromFolder();
	
}

function SelectLetter() {

    $where = "1=1";
    $param = array();
	
	if(isset($_REQUEST["LetterID"]))
	{
		$where .= " AND LetterID=:lid";
		$param[":lid"] = $_REQUEST["LetterID"];
	}

    $list = OFC_letters::GetAll($where, $param);
    echo dataReader::getJsonData($list, count($list), $_GET['callback']);
    die();
}

function SelectAllLetter(){
	
	$where = "1=1";
    $param = array();
	
	foreach($_POST as $field => $value)
	{
		if(empty($value) || strpos($field, "inputEl") !== false)
			continue;

		switch($field)
		{
			case "LetterTitle":		
			case "organization":
			case "SendComment":
			case "context":	
				$where .= " AND " . $field . " like :" . $field;
				$param[":" . $field] = "%" . $value . "%";
				break;
			
			case "FromSendDate":
				$where .= " AND SendDate >= :" . $field;
				$param[":" . $field] = DateModules::shamsi_to_miladi($value, "-");
				break;
			case "FromLetterDate":
				$where .= " AND LetterDate >= :" . $field;
				$param[":" . $field] = DateModules::shamsi_to_miladi($value, "-");
				break;				
			case "ToSendDate":
				$where .= " AND SendDate <= :" . $field;
				$param[":" . $field] = DateModules::shamsi_to_miladi($value, "-");
				break;
			case "ToLetterDate":
				$where .= " AND LetterDate <= :" . $field;
				$param[":" . $field] = DateModules::shamsi_to_miladi($value, "-");
				break;	
			
			case "PersonID":
				$where .= " AND l.PersonID = :" . $field;
				$param[":" . $field] = $value;
				break;
			
			default:
				$where .= " AND " . $field . " = :" . $field;
				$param[":" . $field] = $value;
		}
		
	}
	//echo $where;
    $list = OFC_letters::FullSelect($where, $param);
	
	//print_r(ExceptionHandler::PopAllExceptions());
	
	$no = $list->rowCount();
	$list = PdoDataAccess::fetchAll($list, $_GET["start"], $_GET["limit"]);
    echo dataReader::getJsonData($list, count($list), $_GET['callback']);
    die();
}

function SelectDraftLetters() {

    $list = OFC_letters::SelectDraftLetters();
    echo dataReader::getJsonData($list, count($list), $_GET['callback']);
    die();
}

function SelectReceivedLetters(){
	
	$where = " AND IsDeleted='NO'";
	
	if(isset($_REQUEST["deleted"]) && $_REQUEST["deleted"] == "true")
		$where = " AND IsDeleted='YES'";
	
	$dt = OFC_letters::SelectReceivedLetters($where);
	$cnt = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($dt, $cnt, $_GET["callback"]);
	die();
}

function SelectSendedLetters(){
	
	$dt = OFC_letters::SelectSendedLetters();
	$cnt = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($dt, $cnt, $_GET["callback"]);
	die();
}

function SelectArchiveLetters(){
	
	$FolderID = isset($_REQUEST["FolderID"]) ? $_REQUEST["FolderID"] : "";
	if(empty($FolderID))
	{
		echo dataReader::getJsonData(array(), 0, $_GET["callback"]);
		die();
	}
	$query = "select l.*,a.FolderID,if(count(DocumentID) > 0,'YES','NO') hasAttach

			from OFC_ArchiveItems a
				join OFC_letters l using(LetterID)
				left join DMS_documents on(ObjectType='letterAttach' AND ObjectID=l.LetterID)				
			where FolderID=:fid";
	
	$param = array(":fid" => $FolderID);
	
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) {
        $field = $_REQUEST['fields'];
        $query .= ' and ' . $field . ' like :f';
        $param[':f'] = '%' . $_REQUEST['query'] . '%';
    }
	
	$query .= " group by LetterID";
	$dt = PdoDataAccess::runquery($query, $param);
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//.............................................

function SaveLetter($dieing = true) {

    $Letter = new OFC_letters();
    pdoDataAccess::FillObjectByArray($Letter, $_POST);

    if ($Letter->LetterID == '') {
		$Letter->PersonID = $_SESSION["USER"]["PersonID"];
		$Letter->LetterDate = PDONOW;
		$Letter->RegDate = PDONOW;
        $res = $Letter->AddLetter();
    }
    else
        $res = $Letter->EditLetter();

	if(!empty($_FILES["PageFile"]["tmp_name"]))
	{
		$st = preg_split("/\./", $_FILES ['PageFile']['name']);
		$extension = strtolower($st [count($st) - 1]);
		if (in_array($extension, array("jpg", "jpeg", "gif", "png", "pdf")) === false) 
		{
			Response::createObjectiveResponse(false, "فرمت فایل ارسالی نامعتبر است");
			die();
		}
		
		$dt = DMS_documents::SelectAll("ObjectType='letter' AND ObjectID=?", array($Letter->LetterID));
		if(count($dt) == 0)
		{
			$obj = new DMS_documents();
			$obj->DocType = 0;
			$obj->ObjectType = "letter";		
			$obj->ObjectID = $Letter->LetterID;
			$obj->AddDocument();
			$DocumentID = $obj->DocumentID;
		}
		else
			$DocumentID = $dt[0]["DocumentID"];
		
		//..............................................

		$obj2 = new DMS_DocFiles();
		$obj2->DocumentID = $DocumentID;
		$obj2->PageNo = PdoDataAccess::GetLastID("DMS_DocFiles", "PageNo", 
			"DocumentID=?", array($DocumentID)) + 1;
		$obj2->FileType = $extension;
		$obj2->FileContent = substr(fread(fopen($_FILES['PageFile']['tmp_name'], 'r'), 
				$_FILES ['PageFile']['size']), 0, 200);
		$obj2->AddPage();

		$fp = fopen(getenv("DOCUMENT_ROOT") . "/storage/documents/". $obj2->RowID . "." . $extension, "w");
		fwrite($fp, substr(fread(fopen($_FILES['PageFile']['tmp_name'], 'r'), 
				$_FILES ['PageFile']['size']),200) );
		fclose($fp);
	}	
	
	if($dieing)
	{
		Response::createObjectiveResponse($res, $Letter->GetExceptionCount() != 0 ? 
			$Letter->popExceptionDescription() : $Letter->LetterID);
		die();
	}
	return true;    
}

function deleteLetter() {

    $res = OFC_letters::RemoveLetter($_POST["LetterID"]);
    Response::createObjectiveResponse($res, '');
    die();
}

function selectLetterPages(){
	
	$letterID = !empty($_REQUEST["LetterID"]) ? $_REQUEST["LetterID"] : 0;
	$dt = PdoDataAccess::runquery("select RowID, DocumentID, DocDesc, ObjectID 
		from DMS_DocFiles join DMS_documents using(DocumentID)
		where ObjectType='letter' AND ObjectID=?", array($letterID));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function DeletePage(){
	
	$DocumentID = $_POST["DocumentID"];
	$ObjectID = $_POST["ObjectID"];
	$RowID = $_POST["RowID"];
	
	$obj = new DMS_documents($DocumentID);
	if($obj->ObjectID != $ObjectID)
	{
		echo Response::createObjectiveResponse (false, "");
		die();
	}
	
	$result = DMS_DocFiles::DeletePage($RowID);
	
	$dt = DMS_DocFiles::SelectAll("DocumentID=?", array($DocumentID));
	if(count($dt) == 0)
	{
		$result = DMS_documents::DeleteDocument($DocumentID);
	}
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function selectSendTypes(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where TypeID=12");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SignLetter(){
	
	$LetterID = $_POST["LetterID"];
	
	$obj = new OFC_letters($LetterID);
	if($obj->SignerPersonID == $_SESSION["USER"]["PersonID"])
	{
		$obj->IsSigned = "YES";
		$obj->EditLetter();
	}
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

//.............................................

function SendLetter(){
	
	SaveLetter(false);
	
	$LetterID = $_POST["LetterID"];
	$toPersonArr = array();
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if(isset($_POST["SendID"]) && $_POST["SendID"]*1 > 0)
	{
		$obj = new OFC_send();
		$obj->SendID = $_POST["SendID"];
		$obj->IsSeen = "YES";
		$obj->EditSend($pdo);
	}
	$arr = array_keys($_POST);
	foreach($arr as $key)
	{
		if(strpos($key, "ToPersonID") === false)
			continue;
		
		$toPersonID = $_POST[$key];
		if(isset($toPersonArr[$toPersonID]) || $toPersonID*1 == 0)
			continue;
		$toPersonArr[$toPersonID] = true;		
		
		$index = preg_split("/_/", $key);
		$index = $index[0];

		$obj = new OFC_send();
		$obj->LetterID = $LetterID;
		$obj->FromPersonID = $_SESSION["USER"]["PersonID"];
		$obj->ToPersonID = $toPersonID;
		$obj->SendDate = PDONOW;
		$obj->SendType = $_POST[$index . "_SendType"];
		$obj->IsUrgent = $_POST[$index . "_IsUrgent"];
		$obj->IsCopy = isset($_POST[$index . "_IsCopy"]) ? "YES" : "NO";
		$obj->SendComment = $_POST[$index . "_SendComment"];
		$obj->SendComment = $obj->SendComment == "شرح ارجاع" ? "" : $obj->SendComment;
		if(!$obj->AddSend($pdo))
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function ReturnSend(){
	
	$LetterID = $_POST["LetterID"];
	$SendID = $_POST["SendID"];
	
	$obj = new OFC_send($SendID);
	if($obj->LetterID <> $LetterID)
	{
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	if($obj->IsSeen == "YES")
	{
		echo Response::createObjectiveResponse(false, "IsSeen");
		die();
	}
	
	$result = $obj->DeleteSend();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteSend(){
	
	$mode = $_POST["mode"];
	$LetterID = $_POST["LetterID"];
	$SendID = $_POST["SendID"];
	$obj = new OFC_send($SendID);	
	if($obj->ToPersonID == $_SESSION["USER"]["PersonID"])
	{
		$obj->IsDeleted = $mode == "1" ? "NO" : "YES";
		$obj->EditSend();
	}	
	echo Response::createObjectiveResponse(true, "");
	die();
}

//.............................................

function SelectArchiveNodes(){

	$dt = PdoDataAccess::runquery("
		SELECT 
			ParentID,FolderID id,FolderName as text,'true' as leaf, f.*
		FROM OFC_archive f
		where PersonID=?
		order by ParentID,FolderName", array($_SESSION["USER"]["PersonID"]));

    $returnArray = array();
    $refArray = array();

    foreach ($dt as $row) {
        if ($row["ParentID"] == 0) {
            $returnArray[] = $row;
            $refArray[$row["id"]] = &$returnArray[count($returnArray) - 1];
            continue;
        }

        $parentNode = &$refArray[$row["ParentID"]];

        if (!isset($parentNode["children"])) {
            $parentNode["children"] = array();
            $parentNode["leaf"] = "false";
        }
        $lastIndex = count($parentNode["children"]);
        $parentNode["children"][$lastIndex] = $row;
        $refArray[$row["id"]] = &$parentNode["children"][$lastIndex];
    }

    $str = json_encode($returnArray);

    $str = str_replace('"children"', 'children', $str);
    $str = str_replace('"leaf"', 'leaf', $str);
    $str = str_replace('"text"', 'text', $str);
    $str = str_replace('"id"', 'id', $str);
    $str = str_replace('"true"', 'true', $str);
    $str = str_replace('"false"', 'false', $str);

    echo $str;
    die();
}

function SaveFolder(){
	
	$obj = new OFC_archive();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->PersonID = $_SESSION["USER"]["PersonID"];
	$obj->ParentID = $obj->ParentID == "src" ? "0" : $obj->ParentID;		
	
	if(empty($obj->FolderID))
		$result = $obj->AddFolder();
	else
		$result = $obj->EditFolder();
	
	echo Response::createObjectiveResponse($result, $result ? $obj->FolderID : "");
	die();
}

function DeleteFolder(){
	
	$FolderID = $_POST["FolderID"];
	$result = OFC_archive::DeleteFolder($FolderID);
	echo Response::createObjectiveResponse($result, "");
	die();
}

function AddLetterToFolder(){
	
	$LetterID = $_POST["LetterID"];
	$FolderID = $_POST["FolderID"];
	
	PdoDataAccess::runquery("insert into OFC_ArchiveItems values(?,?)", array($FolderID, $LetterID));
	echo Response::createObjectiveResponse(true, "");
	die();
}

function RemoveLetterFromFolder(){
	
	$LetterID = $_POST["LetterID"];
	$FolderID = $_POST["FolderID"];
	
	PdoDataAccess::runquery("delete from OFC_ArchiveItems where FolderID=? AND LetterID=?",
		array($FolderID, $LetterID));

	echo Response::createObjectiveResponse(ExceptionHandler::GetExceptionCount() == 0, "");
	die();
}

?>