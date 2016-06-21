<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.01.22
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/evaluation_lists.class.php';
require_once '../class/evaluation_list_items.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ( $task) {
		case "SelectEvalList" :
              SelectEvalList();

        case "SaveValList" :
              SaveValList();

        case "deleteEval" :
              deleteEval();

        case "SelectMemberEvalList" :
              SelectMemberEvalList();

        case "AddAllPrn" :
              AddAllPrn();

        case "SaveMember" :
              SaveMember();

        case "deleteMember" :
              deleteMember();
	    
	case "DelAllPrn" :
	      DelAllPrn() ; 

                    }

function SelectEvalList()
{
        $where = dataReader::makeOrder();
        $temp = manage_evaluation_lists::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function SaveValList(){

     $obj = new manage_evaluation_lists();

     if(isset($_REQUEST['list_id'])){
        $obj->list_id = $_POST['list_id'] ;
        $obj->doc_state = $_POST['doc_state'];
     }
     else
        PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
     
        if($obj->list_id == ""){
            $return = $obj->AddList();
        }
        else
            $return = $obj->EditList();

        if($return)
            echo Response::createResponse(true,$obj->list_id);
        else
            echo Response::createResponse(false ,'');
        die();
}

function AddAllPrn(){
 
        $PersonList = manage_evaluation_list_items::SelectListOfPrn( $_REQUEST['ouid'] , $_REQUEST['person_type']);
        $return = "";
        $msg = "";
        $obj = new manage_evaluation_list_items();

        for($i=0 ; $i< count($PersonList); $i++)
        {  
                        
            $obj->list_id = $_REQUEST['list_id'] ;
            $obj->staff_id = $PersonList[$i]['staff_id'] ;

            $return = $obj->Add();

             if(!$return)
                break ; 
                 
        }

            if(count($PersonList) == 0 )
                $msg = 'این واحد شامل این گروه افراد نمی باشد.';

            if(!$return)
            {
                echo Response::createObjectiveResponse($return, $msg);
                die();
            }
            echo Response::createObjectiveResponse(true, "");
            die();

}

function SaveMember(){

     $obj = new manage_evaluation_list_items();
     PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

     $obj->list_id = $_POST['list_id'];

     
        if($obj->ListItemID == ""){
            $return = $obj->Add();
        }
        else
            $return = $obj->Edit();

        if($return)
            echo Response::createResponse(true,$obj->list_id);
        else
            echo Response::createResponse(false ,'');
        die();
}

function deleteEval()
{   
	$obj = new manage_evaluation_lists();
    $obj->list_id = $_POST["list_id"];

    $return =  $obj->Remove();

    Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}
function deleteMember()
{
	$obj = new manage_evaluation_list_items();
    
    $obj->list_id = $_POST["list_id"];
    $obj->ListItemID = $_POST["ListItemID"];

    $return =  $obj->Remove();

    Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}
function DelAllPrn()
{
    $obj = new manage_evaluation_list_items();
    $obj->list_id = $_POST["list_id"];

    $return =  $obj->Remove("true");

    Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}
function SelectMemberEvalList()
{
        $where = " where list_id = ".$_GET['list_id'] ;
        $where .= dataReader::makeOrder();
        $temp = manage_evaluation_lists::GetAllMembers($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();
}
?>