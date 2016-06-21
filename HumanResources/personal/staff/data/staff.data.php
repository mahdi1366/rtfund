<?php

//---------------------------
// programmer:	Mahdipour
// create Date:	89.06.27
//---------------------------


 require_once '../../../header.inc.php';
 require_once '../class/staff.class.php';
 
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ($task) {
	
	case "save" :
		saveStaffData ();

    case  "WarningMsg" :
        WarningMsg ();

    case "IcludedChildrenMsg" :
        IcludedChildrenMsg ();

    case "eglMsg" :
         eglMsg ();
        
    case "Estelaji" :    
         EstelajiMsg() ; 
		 
	 case "RetMsg" : 
               RetMsg() ; 
             
case "TarfiMsg" :
      TarfiMsg();

	 case "ChangeSandoogh" :
		 ChangeSandoogh() ;  
	    
}

function saveStaffData(){
			
    $obj = new manage_staff();	
    
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	if(isset($_POST["work_start_date"]))
        $obj->work_start_date = DateModules::Shamsi_to_Miladi($_POST["work_start_date"]);
	if(isset($_POST["ProfWorkStart"]))
        $obj->ProfWorkStart = DateModules::Shamsi_to_Miladi($_POST["ProfWorkStart"]);
    if(isset($_POST["retired_date"]))
        $obj->retired_date = DateModules::Shamsi_to_Miladi($_POST["retired_date"]);

	if(isset($_POST["last_retired_pay"]))
        $obj->last_retired_pay = DateModules::Shamsi_to_Miladi($_POST["last_retired_pay"]);
	
	
	if(!isset($_POST['Over25']))
		$obj->Over25 = 0 ; 
		        
	$obj->sum_paied_pension =  ($obj->sum_paied_pension > 0) ?  $obj->sum_paied_pension  : "0";

	$return = $obj->EditStaff();	 
	$key = $obj->staff_id.",".$obj->PersonID.",".$obj->person_type ; 		
	echo $return ? Response::createObjectiveResponse("true",$key) :
		Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString("\n"));
	die();
	
}

function WarningMsg(){
 
    $pt = 0 ;
    
    if(!empty($_GET['prof']) == 1 )
          $pt = 1 ;
    elseif (!empty($_GET['emp']) == 1 )
          $pt = 2 ;
    elseif ( !empty($_GET['worker']) == 1 )
          $pt = 3 ;
    elseif ( !empty($_GET['gharardadi']) == 1 )
          $pt = 5 ;
        
    $where = " (1=1) and  p.person_type = ".$pt  ;
	$whereParam["PT"] = $pt;

    $no = manage_staff::CountWarningMsg($where, $whereParam);
    $temp = manage_staff::SelectWarningMsg($where,$whereParam);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
    
}

function RetMsg(){
	
    $pt = 0 ;
    
    if(!empty($_GET['prof']) == 1 )
          $pt = 1 ;
    elseif (!empty($_GET['emp']) == 1 )
          $pt = 2 ;
    elseif ( !empty($_GET['worker']) == 1 )
          $pt = 3 ;
    elseif ( !empty($_GET['gharardadi']) == 1 )
          $pt = 5 ;
        
    $where = " where (1=1) and  p.person_type = ".$pt  ;
	$whereParam["PT"] = $pt;

    $no = manage_staff::CountRetMsg($where, $whereParam);
    $temp = manage_staff::SelectRetMsg($where,$whereParam);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
    
}

function TarfiMsg(){
	
    $pt = 0 ;
       
    if (!empty($_GET['emp']) == 1 )
          $pt = 2 ;   
    elseif ( !empty($_GET['gharardadi']) == 1 )
          $pt = 5 ;
        
    $where = " where (1=1) and  p.person_type = ".$pt  ;
    $whereParam["PT"] = $pt;

    $no = manage_staff::CountTarfiMsg($where, $whereParam, $pt);
    $temp = manage_staff::SelectTarfiMsg($where,$whereParam, $pt);

    echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
    die ();
    
}

function IcludedChildrenMsg(){
    $pt = 0 ;

    if(!empty($_GET['prof']) == 1 )
          $pt = 1 ;
    elseif (!empty($_GET['emp']) == 1 )
          $pt = 2 ;
    elseif ( !empty($_GET['worker']) == 1 )
          $pt = 3 ;
    elseif ( !empty($_GET['gharardadi']) == 1 )
          $pt = 5 ;

    $where = " (1=1) and  p.person_type = ".$pt  ;
	$whereParam = array();

    $no = manage_staff::CountICMsg($where, $whereParam);
    $temp = manage_staff::SelectICMsg($where,$whereParam);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();

}

function eglMsg(){
   
    $pt = 0 ;

   if(!empty($_GET['prof']) == 1 )
          $pt = 1 ;
    elseif (!empty($_GET['emp']) == 1 )
          $pt = 2 ;
    elseif ( !empty($_GET['worker']) == 1 )
          $pt = 3 ;
    elseif ( !empty($_GET['gharardadi']) == 1 )
          $pt = 5 ;

    $where = " (1=1) and  p.person_type = ".$pt  ;
	$whereParam = array();

    $no = manage_staff::CounteglMsg($where, $whereParam);
    $temp = manage_staff::SelecteglMsg($where,$whereParam);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();

}

function EstelajiMsg(){
            
    $temp = manage_staff::SelectEsMsg();
    $no = count($temp) ; 
    echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
    die ();    
}

function ChangeSandoogh(){
            
    $temp = manage_staff::change_Retired_Pay();
    $no = count($temp) ; 
    echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
    die ();    
}

?>