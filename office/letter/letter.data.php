<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.10
//---------------------------

require_once '../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once 'letter.class.php';

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

    Response::createObjectiveResponse($res, $Letter->GetExceptionCount() != 0 ? 
			$Letter->popExceptionDescription() : $Letter->LetterID);
    die();
}

function deleteLetter() {

    $res = OFC_letters::RemoveLetter($_POST["LetterID"]);
    Response::createObjectiveResponse($res, '');
    die();
}


?>
