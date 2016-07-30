<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.01.22
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/pay_get_lists.class.php';
require_once '../class/pay_get_list_items.class.php';
require_once '../class/mission_list_items.class.php';

require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ( $task) {
		case "EXTRA_WORK_LIST" :
		      EXTRA_WORK_LIST();
		    
		case "MISSION_LIST" :    
		      MISSION_LIST() ; 
		    
		case "PAY_GET_LIST" :
		      PAY_GET_LIST();
		    
		case "DEC_PAY_GET_LIST" :
		      DEC_PAY_GET_LIST(); 
		    
		case "MemberPGList" :
		      MemberPGList();

		case "SavePGList" :
		      SavePGList();

		case "deletePG" :
		      deletePG();
		    
		case "deletePGItems" :
		      deletePGItems();

		case "AddAllPrn" :
		    AddAllPrn();

		case "SaveMember" :
		    SaveMember();

		case "deleteMember" :
		    deleteMember();
		    
		case "SaveMission" :
		     SaveMission() ; 

                    }

function EXTRA_WORK_LIST()
{
        $where  =  " where list_type =".EXTRA_WORK_LIST ; 
	$where .= dataReader::makeOrder();
	
        $temp = manage_pay_get_lists::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function MISSION_LIST()
{
        $where  =  " where list_type =".MISSION_LIST ; 
	$where .= dataReader::makeOrder();
	
        $temp = manage_pay_get_lists::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function PAY_GET_LIST()
{
        $where  =  " where list_type =".PAY_GET_LIST ; 
	$where .= dataReader::makeOrder();
	
        $temp = manage_pay_get_lists::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function DEC_PAY_GET_LIST()
{
        $where  =  " where list_type =".DEC_PAY_GET_LIST ; 
	$where .= dataReader::makeOrder();
	
        $temp = manage_pay_get_lists::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function MemberPGList()
{  
        
	if($_GET['list_type'] == MISSION_LIST ){
	    
	    $where = " where list_id = ".$_GET['list_id'] ;
	    $where .= dataReader::makeOrder();

	    $temp = manage_mission_list_items::GetAllMembers($where);	
	}
	else {
	    
	    $where = " where list_id = ".$_GET['list_id'] ;
	    $where .= dataReader::makeOrder();

	    $temp = manage_pay_get_list_items::GetAllMembers($where);	
	}
	
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();
}

function SavePGList(){

     $obj = new manage_pay_get_lists() ; 

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

function SaveMission(){
      
     $obj = new manage_mission_list_items() ;  
              
     PdoDataAccess::FillObjectByArray($obj, $_POST); 
	 $obj->staff_id = $_POST['staffID'] ; 
          
     $qry = " select person_type from staff where staff_id =".$obj->staff_id ; 
     $res = PdoDataAccess::runquery($qry) ; 
     
     if( $res[0]['person_type'] == HR_PROFESSOR ) 
		    $obj->salary_item_type_id = 42 ; 
		else if( $res[0]['person_type'] == HR_EMPLOYEE ) 
		    $obj->salary_item_type_id = 43 ;
		else if( $res[0]['person_type'] == HR_CONTRACT ) 
		    $obj->salary_item_type_id = 643 ;
		     
     if(!empty($_REQUEST['list_row_no'])){  
	 	 		
		$qry = "select list_id from mission_list_items
            		where doc_no = ".$obj->doc_no." AND
						  list_id <> ".$obj->list_id." AND staff_id <> ".$obj->staff_id." AND doc_date >='2014-03-21' AND doc_date < '2015-03-21' " ; 
		$Result = PdoDataAccess::runquery($qry) ; 
			
		if(count($Result) > 0 ) 
		{			
			  echo Response::createObjectiveResponse(false,'این شماره برگه قبلا ثبت شده است.');
			  die(); 
		}	 
	 
		$obj->using_facilities = (!empty($_POST['using_facilities'])) ? $_POST['using_facilities'] : 0 ; 
		$obj->staff_id = $_POST['staffID']; 
		$return = $obj->Edit();	
     }
     else { 	
	  
		$qry = "select list_id from mission_list_items where doc_no = ".$obj->doc_no." AND
												doc_date >='2014-03-21' AND doc_date < '2015-03-21' " ; 
		$Result = PdoDataAccess::runquery($qry) ; 
		
		if(count($Result) > 0 )  
		{   
			  echo Response::createObjectiveResponse(false,'این شماره برگه قبلا ثبت شده است.');
			  die(); 
		}
		
	  $obj->staff_id = $_POST['STID']; 
	  $return = $obj->Add();	  
	  }
    
        if($return) {
            echo Response::createObjectiveResponse("true", $obj->list_id );
	    die();
	    }	
        else {
             echo Response::createObjectiveResponse(false,'');
	     die(); 
	    
	    }
}

function AddAllPrn(){
     
        $PersonList = manage_pay_get_list_items::SelectListOfPrn( $_REQUEST['cost_center_id'] );
        $return = "";
        $msg = "";
	
	if($_POST['list_type'] == EXTRA_WORK_LIST)  {
	    	
	    $obj = new manage_pay_get_list_items();

	    for($i=0 ; $i< count($PersonList); $i++)
	    {  
		$obj->list_id = $_REQUEST['list_id'] ;
		$obj->staff_id = $PersonList[$i]['staff_id'] ;
		if( $PersonList[$i]['person_type'] == HR_EMPLOYEE ) 
		    $obj->salary_item_type_id = 39 ; 
		else if( $PersonList[$i]['person_type'] == HR_WORKER ) 
		    $obj->salary_item_type_id = 152 ;
		else if( $PersonList[$i]['person_type'] == HR_CONTRACT ) 
		    $obj->salary_item_type_id = 639 ;

		$return = $obj->Add();

		if(!$return)
		    break ; 

	    }
	}
	else if( $_POST['list_type'] == MISSION_LIST ){
	    
	    $obj = new manage_mission_list_items();

	    for($i=0 ; $i< count($PersonList); $i++)
	    {  
		$obj->list_id = $_REQUEST['list_id'] ;
		$obj->staff_id = $PersonList[$i]['staff_id'] ;
		if( $PersonList[$i]['person_type'] == HR_PROFESSOR ) 
		    $obj->salary_item_type_id = 42 ; 
		else if( $PersonList[$i]['person_type'] == HR_EMPLOYEE ) 
		    $obj->salary_item_type_id = 43 ;
		else if( $PersonList[$i]['person_type'] == HR_CONTRACT ) 
		    $obj->salary_item_type_id = 643 ;

		$return = $obj->Add();

		if(!$return)
		    break ; 

	    }
	    
	}
	else if( $_POST['list_type'] == PAY_GET_LIST ){
	    
	    $obj = new manage_pay_get_list_items();

	    for($i=0 ; $i< count($PersonList); $i++)
	    {  
		$obj->list_id = $_REQUEST['list_id'] ;
		$obj->staff_id = $PersonList[$i]['staff_id'] ;
		$obj->salary_item_type_id = $_REQUEST['itemID'] ;
		 
		$return = $obj->Add();

		if(!$return)
		    break ; 

	    }
	    
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

     $obj = new manage_pay_get_list_items();
     PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

     $obj->list_id = $_POST['list_id'];
     
     //---
     $qry = " select person_type from staff where staff_id =".$obj->staff_id ; 
     $res = PdoDataAccess::runquery($qry) ; 
     
     if($_POST['list_type'] == EXTRA_WORK_LIST ){
	 
	 if( $res[0]['person_type'] == HR_EMPLOYEE ) 
		    $obj->salary_item_type_id = 39 ; 
		else if( $res[0]['person_type'] == HR_WORKER ) 
		    $obj->salary_item_type_id = 152 ;
		else if( $res[0]['person_type'] == HR_CONTRACT ) 
		    $obj->salary_item_type_id = 639 ; 
     }
     
     //-------
     
        if($obj->list_row_no == ""){
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

function deletePG()
{   
    $obj = new manage_pay_get_lists();
    $obj->list_id = $_POST["list_id"];
	$obj->list_type = $_POST["list_type"];

    $return =  $obj->Remove();

    Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
    die();
}

function deletePGItems()
{   
    $obj = new manage_pay_get_list_items();
    $obj->list_id = $_POST["list_id"];

    $return =  $obj->Remove('true');

    Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
    die();
}

function deleteMember()
{
    
   if($_POST['list_type'] == MISSION_LIST )
        $obj = new manage_mission_list_items() ; 
   else 
	$obj = new manage_pay_get_list_items();
    
    $obj->list_id = $_POST["list_id"];
    $obj->list_row_no = $_POST["rowNo"];
   
    $return = $obj->Remove() ; 

    Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

?>