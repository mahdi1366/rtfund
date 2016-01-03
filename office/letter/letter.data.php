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
    case 'SelectDraftLetters':
        SelectDraftLetters();

	case "SelectLetter":
		SelectLetter();
		
    case 'SaveLetter':
        SaveLetter();

    case 'DeleteLetter':
        deleteLetter();
		
	case "selectLetterPages":
		selectLetterPages();
}

function SelectDraftLetters() {

    $where = "LetterStatus='RAW' AND PersonID=:pid";
    $param = array();
    $param[':pid'] = $_SESSION["USER"]["PersonID"];

    $list = OFC_letters::GetAll($where, $param);

    echo dataReader::getJsonData($list, count($list), $_GET['callback']);
    die();
}

function SelectLetter(){
	
	$dt = OFC_letters::GetAll("LetterID=?", array($_GET["LetterID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
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
	$dt = PdoDataAccess::runquery("select DocumentID, DocDesc from DMS_documents 
		where ObjectType='letter' AND ObjectID=?", array($letterID));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}
?>
