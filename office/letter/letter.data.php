<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.10
//---------------------------

require_once '../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once 'letter.class.php';
require_once '../../dms/dms.class.php';

$task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';

switch ($task) {
	
	case "SelectLetter":
		SelectLetter();
		
    case 'SelectDraftLetters':
        SelectDraftLetters();

	case "SelectSendedLetters":
		SelectSendedLetters();
		
	case "SelectReceivedLetters":
		SelectReceivedLetters();
		
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
		
	
}

function SelectLetter() {

    $where = "1=1";
    $param = array();

    $list = OFC_letters::GetAll($where, $param);

    echo dataReader::getJsonData($list, count($list), $_GET['callback']);
    die();
}

function SelectDraftLetters() {

    $where = "LetterStatus='RAW' AND PersonID=:pid";
    $param = array();
    $param[':pid'] = $_SESSION["USER"]["PersonID"];

    $list = OFC_letters::GetAll($where, $param);

    echo dataReader::getJsonData($list, count($list), $_GET['callback']);
    die();
}

function SelectReceivedLetters(){
	
	$query = "select s.*,l.*, 
		if(IsReal='YES',concat(fname, ' ', lname),CompanyName) FromPersonName 
		from OFC_send s
			join OFC_letters l using(LetterID)
			join BSC_persons p on(s.FromPersonID=p.PersonID)
			left join OFC_Send s2 on(s2.SendID>s.SendID AND s2.FromPersonID=s.ToPersonID)
		where s2.SendID is null AND s.ToPersonID=:tpid";
	$param = array();
	$param[":tpid"] = $_SESSION["USER"]["PersonID"];
	
	$dt = PdoDataAccess::runquery_fetchMode($query, $param);
	$cnt = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($dt, $cnt, $_GET["callback"]);
	die();
}

function SelectSendedLetters(){
	
	$query = "select s.*,l.*, 
		if(IsReal='YES',concat(fname, ' ', lname),CompanyName) ToPersonName 
		from OFC_send s
			join OFC_letters l using(LetterID)
			join BSC_persons p on(s.ToPersonID=p.PersonID)
		where FromPersonID=:fpid";
	$param = array();
	$param[":fpid"] = $_SESSION["USER"]["PersonID"];
	
	$dt = PdoDataAccess::runquery_fetchMode($query, $param);
	$cnt = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($dt, $cnt, $_GET["callback"]);
	die();
}

function SaveLetter() {

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

	if(!empty($_POST["PageTitle"]))
	{
		if(!empty($_FILES["PageFile"]["tmp_name"]))
		{
			$st = preg_split("/\./", $_FILES ['PageFile']['name']);
			$extension = strtolower($st [count($st) - 1]);
			if (in_array($extension, array("jpg", "jpeg", "gif", "png", "pdf")) === false) 
			{
				Response::createObjectiveResponse(false, "فرمت فایل ارسالی نامعتبر است");
				die();
			}
		}	
		//..............................................

		$obj = new DMS_documents();
		$obj->DocType = 0;
		$obj->ObjectType = "letter";		
		$obj->ObjectID = $Letter->LetterID;
		$obj->DocDesc = $_POST["PageTitle"];
		$obj->AddDocument();
		
		//..............................................

		$fp = fopen(getenv("DOCUMENT_ROOT") . "/storage/documents/". $obj->DocumentID . "." . $extension, "w");
		fwrite($fp, substr(fread(fopen($_FILES['PageFile']['tmp_name'], 'r'), $_FILES ['PageFile']['size']),200) );
		fclose($fp);
		$obj->FileContent = substr(fread(fopen($_FILES['PageFile']['tmp_name'], 'r'), $_FILES ['PageFile']['size']), 0, 200);

		$db = PdoDataAccess::getPdoObject();
		$stmt = $db->prepare("update DMS_documents set FileType = :ft, FileContent = :data where DocumentID=:did");
		$stmt->bindParam(":did", $obj->DocumentID);
		$stmt->bindParam(":ft", $extension);
		$stmt->bindParam(":data", $obj->FileContent, PDO::PARAM_LOB);
		$stmt->execute();

	}	
	
    Response::createObjectiveResponse($res, $Letter->GetExceptionCount() != 0 ? 
			$Letter->popExceptionDescription() : $Letter->LetterID);
    die();
}

function deleteLetter() {

    $res = OFC_letters::RemoveLetter($_POST["LetterID"]);
    Response::createObjectiveResponse($res, '');
    die();
}

function selectLetterPages(){
	
	$letterID = !empty($_REQUEST["LetterID"]) ? $_REQUEST["LetterID"] : 0;
	$dt = PdoDataAccess::runquery("select DocumentID, DocDesc, ObjectID from DMS_documents 
		where ObjectType='letter' AND ObjectID=?", array($letterID));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function DeletePage(){
	
	$DocumentID = $_POST["DocumentID"];
	$ObjectID = $_POST["ObjectID"];
	
	$obj = new DMS_documents($DocumentID);
	if($obj->ObjectID != $ObjectID)
	{
		echo Response::createObjectiveResponse (false, "");
		die();
	}
	
	$result = DMS_documents::DeleteDocument($DocumentID);
	echo Response::createObjectiveResponse($result, "");
	die();
}

function selectSendTypes(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where TypeID=12");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//.............................................

function SendLetter(){
	
	$LetterID = $_POST["LetterID"];
	$toPersonArr = array();
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
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
		$obj->SendComment = $_POST[$index . "_SendComment"];
		if(!$obj->AddSend($pdo))
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	
	$letterObj = new OFC_letters($LetterID);
	$letterObj->LetterStatus = "CONFIRM";
	if(!$letterObj->EditLetter($pdo))
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "");
		die();
	}
		
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

?>
