<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.06.17
//---------------------------
ini_set("display_errors","On") ; 
require_once '../../../header.inc.php'; 

require_once '../class/writ.class.php'; 
require_once '../../staff/class/staff.class.php';
require_once inc_response; 
require_once inc_dataReader; 
 
require_once '../class/group_writ_log.class.php'; 

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) {

	case "selectPersonWrt":
		selectPersonWrt();
		
	case "selectWrt" :
		  selectWrtData();
		  
	case "saveWritInfo" :
		  saveData();
		  
	case "selectItemWrit" :
		  selectItems();	
		     
	case "IssueWrit":
		  IssueWrit();

	case "DeleteWrit":
		DeleteWrit();

	case "IssueCorrectiveWrit":
		  IssueCorrectiveWrit();		
		  
	case "GroupIssueWrit":
		GroupIssueWrit();
		
	case "GroupCancelWrit":
		GroupCancelWrit();
		
	case "selectPossibleTransferWrits":
		selectPossibleTransferWrits();
		
	case "selectArrearTransferWrits" :
		selectArrearTransferWrits();
		
	case "transferAction":
		transferAction();
		
	case "ArrearTransferAction" :
		 ArrearTransferAction(); 

	case "confirmAction":
		confirmAction();

	case "recalculate":
		 recalculate();

    case "calculate":
          calculate();

    case "Prior_Corrective_Writ":
          Prior_Corrective_Writ();

   case "Next_Corrective_Writ":
         Next_Corrective_Writ();
	   
   
        
}

function selectPersonWrt()
{
		$query = "DROP TABLE IF EXISTS temp_sum_item_writs2";
         PdoDataAccess::runquery($query);

         $qry = " CREATE TEMPORARY TABLE temp_sum_item_writs2  AS
                select sum(w.value) sumValue ,w.writ_id , w.writ_ver, w.staff_id
				from writ_salary_items w
					join staff s on(w.staff_id=s.staff_id)
					join persons p on(p.PersonID=? AND s.PersonID=p.PersonID)
                group by writ_id , writ_ver, staff_id";
	 
        PdoDataAccess::runquery($qry, array($_GET["Q0"]));

//if($_SESSION['UserID'] == 'jafarkhani') {	echo PdoDataAccess::GetLatestQueryString();  die() ;  }
	

        $query = " ALTER TABLE temp_sum_item_writs2 ADD INDEX(writ_id,writ_ver,staff_id) ";
         PdoDataAccess::runquery($query);
$whr = "" ; 
/*if($_SESSION['UserID'] != 'jafarkhani' && $_SESSION['UserID'] != 'delkalaleh' && $_SESSION['UserID'] != 'nadaf' &&
   $_SESSION['UserID'] != 'm-hakimi' && $_SESSION['UserID'] != 'shokri' ) {
	
	 $whr = " AND w.execute_date < '2014-02-20' " ; 
}*/
	$query = " SELECT w.*, w.ouid sub_ouid ,
				  bi1.Title corrective_title ,
				  bi2.Title history_only_title ,
				  bi3.Title science_level_title ,
				  wt.title MainWtitle,
				  wst.title wst_title,
				  wst.time_limited,
				  concat(wt.title ,' ',wst.title) wt_title ,
				  bi4.Title emp_state_title ,
				  bi5.Title educTitle ,
				  bi6.Title SPTitle ,
				  o.ptitle o_ptitle,
				  parentu.ouid ,
				  parentu.ptitle parentTitle ,
				temp.sumValue

		   FROM staff s 
				INNER JOIN writs w ON (s.staff_id = w.staff_id )
				LEFT JOIN temp_sum_item_writs2 temp
					ON(temp.writ_id = w.writ_id and
					temp.writ_ver = w.writ_ver and
					temp.staff_id = w.staff_id)
				LEFT JOIN Basic_Info bi1 ON ( bi1.InfoID = w.corrective  AND bi1.TypeID = 5)
				LEFT JOIN Basic_Info bi2 ON ( bi2.InfoID = w.history_only  AND bi2.TypeID = 5)
				LEFT JOIN Basic_Info bi3 ON (bi3.InfoID = w.science_level AND bi3.TypeID = 8)
				LEFT JOIN Basic_Info bi4 ON (bi4.InfoID = w.emp_state AND bi4.TypeID = 3)
				LEFT JOIN Basic_Info bi5 ON (bi5.InfoID = w.education_level AND bi5.TypeID = 6 )
				LEFT JOIN Basic_Info bi6 ON (bi6.InfoID = w.salary_pay_proc AND bi6.TypeID = 12 )
				LEFT OUTER JOIN writ_types wt ON ((w.writ_type_id = wt.writ_type_id) AND (w.person_type = wt.person_type))
				LEFT OUTER JOIN writ_subtypes wst ON ((w.writ_subtype_id = wst.writ_subtype_id) AND (w.writ_type_id = wst.writ_type_id) AND (w.person_type = wst.person_type))
				LEFT OUTER JOIN org_new_units o ON (o.ouid = w.ouid)
				LEFT OUTER JOIN org_new_units parentu ON (parentu.ouid = o.parent_ouid)

		where s.personid = ? AND (s.last_cost_center_id is null OR s.last_cost_center_id in(" . manage_access::getValidCostCenters() . "))
			AND s.person_type in(" . manage_access::getValidPersonTypes() . ") $whr 
		" . dataReader::makeOrder() . " , staff_id DESC ,corrective DESC  , writ_id DESC , writ_ver DESC";

	$temp = PdoDataAccess::runquery_fetchMode($query, array($_GET["Q0"]));

	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET ["start"], $_GET ["limit"]);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"]);
	die();
}

function selectItems()
{
	
	$where = " writ_id = :WID AND writ_ver = :WVER AND staff_id = :STID" ;
	
	$whereParam = array(":WID" => $_GET['WID'] ,
	                    ":WVER" => $_GET['WVER'] ,
						":STID" => $_GET['STID']);
		
	$temp = manage_writ_item::GetAllWritItems($where,$whereParam);
	
	echo dataReader::getJsonData ( $temp, count($temp), $_GET ["callback"] );
	die ();
		
}

function MakeAdvanceSearchWhere(&$where, &$whereParam)
{ 
	$where = "(1=1)";
	
	$whereParam = array();
		
	if(isset($_REQUEST["Q0"]))
	{
		$where .= " AND p.PersonID = :PID ";
		$whereParam[":PID"] = $_REQUEST["Q0"];
		return;
	}
	
	if(!empty($_REQUEST["from_WID"]))
	{
		$where .= " AND w.writ_id >=:fwid";
		$whereParam[":fwid"] = $_REQUEST["from_WID"];
	}
	
	if(!empty($_REQUEST["person_type"]) && $_REQUEST["person_type"] != 100 )
	{					
			$where .= " AND w.person_type = :persontype";
			$whereParam[":persontype"] = $_REQUEST["person_type"]; 		
	}
	
	if(!empty($_REQUEST["to_WID"]))
	{
		$where .= " AND w.writ_id <=:twid";
		$whereParam[":twid"] = $_REQUEST["to_WID"];
	}
	
	if(!empty($_REQUEST["from_PersonID"]))
	{
		$where .= " AND  p.PersonID >= :fpid";
		$whereParam[":fpid"] = $_REQUEST["from_PersonID"];
	}
	if(!empty($_REQUEST["to_PersonID"]))
	{
		$where .= " AND p.PersonID <= :tpid";
		$whereParam[":tpid"] = $_REQUEST["to_PersonID"];
	}
	if(!empty($_REQUEST["from_staff_id"]))
	{
		$where .= " AND  s.staff_id >= :fstaffid";
		$whereParam[":fstaffid"] = $_REQUEST["from_staff_id"];
	}
	if(!empty($_REQUEST["to_staff_id"]))
	{
		$where .= " AND s.staff_id <= :tstaffid";
		$whereParam[":tstaffid"] = $_REQUEST["to_staff_id"];
	}
	if(!empty($_REQUEST["pfname"]) )
	{
		$where .= " AND p.pfname LIKE :pfn";
		$whereParam[":pfn"] = "%" . $_REQUEST["pfname"] . "%";
	}
	if(!empty($_REQUEST["plname"]) )
	{
		$where .= " AND p.plname LIKE :pln";
		$whereParam[":pln"] = "%" . $_REQUEST["plname"] . "%";
	}
    
	if(!empty($_REQUEST["ouid"]) )
	{
		
			$where .= " AND  ( o.parent_path like :ouid2 OR
							   o.parent_path like :ouid3 OR
                               o.parent_path like :ouid4 OR
							   o.ouid=:ouid OR
							   o.parent_ouid = :ouid 
                               )";

			$whereParam[":ouid"] = $_REQUEST["ouid"];
			$whereParam[":ouid2"] = "%," . $_REQUEST["ouid"] . ",%";
			$whereParam[":ouid3"] = "%" . $_REQUEST["ouid"] . ",%";
                        $whereParam[":ouid4"] = "%," . $_REQUEST["ouid"] . "%";

		
		/*else {
			$where .= " AND  (( o.parent_path like :ouid2 OR
							    o.parent_path like :ouid3 OR
							    o.parent_ouid = :ouid4 ) AND
							    o.ouid=:ouid
							   )";

			$whereParam[":ouid"] = $_REQUEST["org_sub_unit"];
			$whereParam[":ouid2"] = "%," . $_REQUEST["org_unit"] . ",%";
			$whereParam[":ouid3"] = "%" . $_REQUEST["org_unit"] . ",%";
			$whereParam[":ouid4"] = $_REQUEST["org_unit"];

		}*/
		
	}
	
	if(!empty($_REQUEST["staff_group"]) && $_REQUEST["staff_group"] != -1)
	{
		$where .= " AND sgm.staff_group_id = :staff_group";
		$whereParam[":staff_group"] = $_REQUEST["staff_group"];
	}
	if(!empty($_REQUEST["writ_type"]) && $_REQUEST["writ_type"] != -1)
	{
		$where .= " AND w.writ_type_id = :wtyp";
		$whereParam[":wtyp"] = $_REQUEST["writ_type"];
		
		if(!empty($_REQUEST["writsubtype"]) && $_REQUEST["writsubtype"] != -1)
		{
			$where .= " AND w.writ_subtype_id = :wstyp";
			$whereParam[":wstyp"] = $_REQUEST["writsubtype"];
		}
	}

	if(isset($_REQUEST["person_type"]))
	{
		$emp_state = QueryHelper::makeWhereOfCheckboxList("w.emp_state", "emp_state");
		$where  .= ($emp_state != "") ? " AND " . $emp_state : "";
				
		$emp_mode = QueryHelper::makeWhereOfCheckboxList("w.emp_mode", "emp_mod");
		$where  .= ($emp_mode != "") ? " AND " . $emp_mode : "";
	}
	if(!empty($_REQUEST["worktime_type"]) && $_REQUEST["worktime_type"] != -1)
	{
		$where .= " AND w.worktime_type = :worktime_type";
		$whereParam[":worktime_type"] = $_REQUEST["worktime_type"];
	}
	if(!empty($_REQUEST["from_post_id"]))
	{
		$where .= " AND w.post_id>:fpostid";
		$whereParam[":postid"] = $_REQUEST["from_post_id"];
	}
	if(!empty($_REQUEST["to_post_id"]))
	{
		$where .= " AND w.post_id<=:tpostid";
		$whereParam[":tpostid"] = $_REQUEST["to_post_id"];
	}
	if(!empty($_REQUEST["from_ref_letter_no"]))
	{
		$where .= " AND w.ref_letter_no>=:fletno ";
		$whereParam[":fletno"] = $_REQUEST["from_ref_letter_no"];
	}
	if(!empty($_REQUEST["to_ref_letter_no"]))
	{
		$where .= " AND w.ref_letter_no<=:tletno";
		$whereParam[":tletno"] = $_REQUEST["to_ref_letter_no"];
	}
	if(!empty($_REQUEST["from_ref_letter_date"]))
	{
		$where .= " AND w.ref_letter_date >=:frefDate";
		$whereParam[":frefDate"] = DateModules::Shamsi_to_Miladi($_REQUEST["from_ref_letter_date"]);
	}
	if(!empty($_REQUEST["to_ref_letter_date"]))
	{
		$where .= " AND w.ref_letter_date <=:trefDate";
		$whereParam[":trefDate"] =DateModules::Shamsi_to_Miladi($_REQUEST["to_ref_letter_date"]);
	}
	if(!empty($_REQUEST["from_send_letter_no"]))
	{
		$where .= " AND w.send_letter_no>=:fsendlet";
		$whereParam[":fsendlet"] = $_REQUEST["from_send_letter_no"];
	}
	if(!empty($_REQUEST["to_send_letter_no"]))
	{
		$where .= " AND w.send_letter_no<=:tsendlet";
		$whereParam[":tsendlet"] = $_REQUEST["to_send_letter_no"];
	}
	if(!empty($_REQUEST["from_send_letter_date"]))
	{
		$where .= " AND w.send_letter_date>=:fsendletDate";
		$whereParam[":fsendletDate"] = DateModules::Shamsi_to_Miladi($_REQUEST["from_send_letter_date"]);
	}
	if(!empty($_REQUEST["to_send_letter_date"]))
	{
		$where .= " AND w.send_letter_date<=:tsendletDate";
		$whereParam[":tsendletDate"] = DateModules::Shamsi_to_Miladi($_REQUEST["to_send_letter_date"]);
	}
	if(!empty($_REQUEST["from_issue_date"]))
	{
		$where .= " AND w.issue_date>=:fissueDate";
		$whereParam[":fissueDate"] = DateModules::Shamsi_to_Miladi($_REQUEST["from_issue_date"]);
	}
	if(!empty($_REQUEST["to_issue_date"]))
	{
		$where .= " AND w.issue_date<=:tissueDate";
		$whereParam[":tissueDate"] = DateModules::Shamsi_to_Miladi($_REQUEST["to_issue_date"]);
	}
	if(!empty($_REQUEST["from_execute_date"]))
	{
		$where .= " AND w.execute_date>=:fexeDate";
		$whereParam[":fexeDate"] = DateModules::Shamsi_to_Miladi($_REQUEST["from_execute_date"]);
	}
	if(!empty($_REQUEST["to_execute_date"]))
	{
		$where .= " AND w.execute_date<=:texeDate";
		$whereParam[":texeDate"] = DateModules::Shamsi_to_Miladi($_REQUEST["to_execute_date"]);
	}
	if(!empty($_REQUEST["from_pay_date"]))
	{
		$where .= " AND w.pay_date>=:fpayDate";
		$whereParam[":fpayDate"] = DateModules::Shamsi_to_Miladi($_REQUEST["from_pay_date"]);
	}
	if(!empty($_REQUEST["to_pay_date"]))
	{
		$where .= " AND w.pay_date<=:tpayDate";
		$whereParam[":tpayDate"] = DateModules::Shamsi_to_Miladi($_REQUEST["to_pay_date"]);
	}
	if(!empty($_REQUEST["from_cur_group"]))
	{
		$where .= " AND w.cur_group>=:fWG";
		$whereParam[":fWG"] = $_REQUEST["from_cur_group"];
	}
	if(!empty($_REQUEST["to_cur_group"]))
	{
		$where .= " AND w.cur_group<=:tWG";
		$whereParam[":tWG"] = $_REQUEST["to_cur_group"];
	}
	
	if(!empty($_REQUEST["dont_transfer"]))
	{
		$where .= " AND w.dont_transfer = :Trans";
		$whereParam[":Trans"] = $_REQUEST["dont_transfer"];
	}

    if(!empty($_REQUEST["writ_type_id"]))
	{
		$where .= " AND w.writ_type_id = :WTY";
		$whereParam[":WTY"] = $_REQUEST["writ_type_id"];
	}
    
    if(!empty($_REQUEST["writ_subtype_id"]))
	{
		$where .= " AND w.writ_subtype_id = :WSTY";
		$whereParam[":WSTY"] = $_REQUEST["writ_subtype_id"];
	}

    
}

function selectWrtData ()
{ 

	$where = "";
	$whereParam = array();
	
	MakeAdvanceSearchWhere($where, $whereParam);
	
	$field = isset ( $_GET ["fields"] ) ? $_GET ["fields"] : "";
	
	if (isset ( $_GET ["query"] ) && $_GET ["query"] != "")
	{
		switch ( $field) 
		{
			case "PersonID" :
				$where .= " AND p.PersonID = :qry1 " ;
				$whereParam[":qry1"] = "%" . $_GET["query"] . "%";
		        
			break;
			
			case "writ_id" :
				$where .= " AND w.writ_id = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
				
			break;
			
			case "writ_ver" :
				$where .= " AND w.writ_ver = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
				
			break;
							
			case "fullname" :
				$where .= " AND fullname LIKE :qry " ;
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";
		        
			break;
			
			case "staff_id" :
				$where .= " AND w.staff_id = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
								
			break;
			case "org_title" :
				$where .= " AND org_title = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
			
			break;
			case "wt_title" :
				$where .= " AND wt_title LIKE :qry " ;
				$whereParam[":qry"] = $_GET["query"];
			
			break;
			case "emp_state_title" :
				$where .= " AND emp_state_title LIKE :qry ";
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";
				
			break;
			case "execute_date" :
				$where .= " AND w.execute_date = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
			
			break;
			case "history_only_title" :
				$where .= " AND history_only_title = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
			
			break;
			case "corrective_title" :
				$where .= " AND corrective_title = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
			
			break;
			case "corrective_writ_id" :
				$where .= " AND corrective_writ_id = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
			
			break;
			case "mablagh" :
				$where .= " AND mablagh = :qry1 " ;
				$whereParam[":qry1"] = $_GET["query"];
			
			break;
		}
	}
	
	//$where .= isset ( $_GET ["sort"] ) ? " order by " . $_GET ["sort"] . " " . $_GET ["dir"] ." , writ_id DESC , writ_ver DESC" : "";
	//$where .= isset($_GET ["start"]) ? " limit " . $_GET ["start"] . "," . $_GET ["limit"] : "";

	$temp = manage_writ::GetAllWrits($where .dataReader::makeOrder(), $whereParam);
 
	$no = $temp->rowCount();
        
	/*if($no){
	    
		require_once 'excel.php';
		header("Content-type: application/zip");
		header("Content-disposition: inline; filename=excel.xls");
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header("Pragma: public");
		
		require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
		require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";

		$workbook = &new writeexcel_workbook("/tmp/hrmstemp.xls");
		$worksheet =& $workbook->addworksheet("Sheet1");
		$heading =& $workbook->addformat(array('align' => 'center', 'bold' => 1, 'bg_color' => 'black', 'color' => 'white'));
		
		$temp = $temp->fetchAll();
		
		// group headers
		for($i=0; $i < count($temp); $i++)
		    for($j=0; $j < count($temp[$i]); $j++)
			$worksheet->write($i, $j, $temp[$i][$j]);

		 $workbook->close();

		echo file_get_contents("/tmp/hrmstemp.xls");
		unlink("/tmp/hrmstemp.xls");
		die();   
	}*/
       

	$temp = PdoDataAccess::fetchAll($temp, $_GET ["start"], $_GET ["limit"]);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
		  //  echo PdoDataAccess::GetLatestQueryString();
	die();
		
}

function saveData()
{      
  	//........ Fill object ..............
	$obj = new manage_writ($_POST['writ_id'],$_POST['writ_ver'], $_POST['staff_id']);
	PdoDataAccess::FillObjectByArray($obj, $_POST);

    $obj->family_responsible = (isset($_POST['family_responsible'])) ?  $_POST['family_responsible'] : 0 ;
    $obj->history_only = (isset($_POST['history_only'])) ?  $_POST['history_only'] : 0 ;
    $obj->remembered = (isset($_POST['remembered'])) ?  $_POST['remembered'] : 0 ;
       
    $obj->dont_transfer = (isset($_POST['dont_transfer'])) ?  $_POST['dont_transfer'] : 0 ;
	
	//.............. محاسبه مجدد پایه سنواتی فرد ..................
	$Pqry = " select sex , p.person_type ,military_duration_day , military_duration
				from persons p inner join staff s 
									on p.PersonID = s.PersonID 
									
				 where s.staff_id = ".$obj->staff_id  ; 
	$Pres = PdoDataAccess::runquery($Pqry) ; 
	
	if( $obj->person_type == 2 ||  $obj->person_type == 5  ) {
		if($Pres[0]["sex"] == 1 &&  $Pres[0]["person_type"] == 2 && ($Pres[0]["military_duration_day"] > 0 || $Pres[0]["military_duration"] > 0 ) )
		{

			$totalDayWrt = DateModules::ymd_to_days($obj->onduty_year, $obj->onduty_month, $obj->onduty_day ) ; 			
			$totalDaySar = DateModules::ymd_to_days(0, $Pres[0]["military_duration"] , $Pres[0]["military_duration_day"] ) ; 					
			$resDay = $totalDayWrt -  $totalDaySar  ; 

			$Vyear = 0 ; 
			$Vmonth = $Vday = 0 ; 
			DateModules::day_to_ymd($resDay, $Vyear, $Vmonth, $Vday) ; 
			$Vyear =  $Vyear ; 
		
		}						
		else  		
			$Vyear =  $obj->onduty_year ;  
			 
		$obj->base = $Vyear + 1; 
	}
//............................................................
	/*$arr = get_object_vars($obj);
	$KeyArr = array_keys($arr);
	
	for($i=0; $i<count($arr); $i++)
	{
		eval("if(isset(\$_POST['" . $KeyArr[$i] . "']))
			\$obj->" . $KeyArr[$i] . "= \$_POST ['" . $KeyArr[$i] . "'];");
		
	}
		
	$obj->staff_id = $_POST['staff_id'];
	$obj->writ_id  = $_POST['writ_id'];
	$obj->writ_ver = $_POST['writ_ver'];
	$obj->sbid = (empty($obj->sbid)) ? PDONULL : $obj->sbid;
	$obj->issue_date = DateModules::Shamsi_to_Miladi($obj->issue_date);
	$obj->pay_date = DateModules::Shamsi_to_Miladi($obj->pay_date);	
	$obj->ref_letter_date = DateModules::Shamsi_to_Miladi($obj->ref_letter_date);
	$obj->send_letter_date = DateModules::Shamsi_to_Miladi($obj->send_letter_date);	
	$obj->warning_date = DateModules::Shamsi_to_Miladi($obj->warning_date);	
	$obj->remembered = (isset($_POST['remembered'])) ? $_POST['remembered'] : "0" ;*/

   	//....................................
        
	if( $obj->check_send_letter_no() === false )   
	{
		Response::createObjectiveResponse(false, ExceptionHandler::ConvertExceptionsToJsObject());
		die();
	}       
        
	if(!$obj->EditWrit())
		{
                    Response::createObjectiveResponse(false, ExceptionHandler::ConvertExceptionsToJsObject());
			die();
		} 	
	Response::createObjectiveResponse(true, "{WID:" . $obj->writ_id . "}");
	//echo Response::createObjectiveResponse("true", $obj->writ_id);
	die();
}

function IssueWrit()
{
	//-----------------------------------------
	
	$_POST["issue_date"] = '1395/01/25';
	$_POST["staff_id"] =  1; 
	$_POST["writ_type_id"] =1;
	$_POST["writ_subtype_id"] = 1;
	$_POST["execute_date"] = '1395/01/01';
	$_POST['person_type'] = 3 ; 
	$_POST["history_only"] = 0;	
	$_POST["contract_start_date"] =  '1395/01/01';
	$_POST["contract_end_date"] = '1395/12/29';
	
			
	//------------------------------------------
	
	
	$history_only = isset($_POST["history_only"]) ? true : false ;
	 
	$_POST["issue_date"] = (empty($_POST["issue_date"])) ? DateModules::shNow() :  $_POST["issue_date"] ; 
	$ret = manage_writ::IssueWrit($_POST["staff_id"], $_POST["writ_type_id"], $_POST["writ_subtype_id"],
								  $_POST["execute_date"], $_POST['person_type'] ,$_POST["issue_date"], $history_only, false,
								  null, null, null,$_POST["contract_start_date"],$_POST["contract_end_date"],"indiv");
   
	if($ret === false)
	{  
		//ExceptionHandler::SaveExceptionsToSession("writ_exceptions");
		//header("location: ../ui/issue_writ.php");
		Response::createObjectiveResponse(false, ExceptionHandler::ConvertExceptionsToJsObject());
		die();
	}
		
	//......................................

	$ret2 = manage_writ_item::compute_writ_items($ret->writ_id, $ret->writ_ver , $ret->staff_id);
    //	$ret2 = manage_writ_item::compute_writ_items(1, 1 , 1);
	if($ret2 === false)
	{   
		//ExceptionHandler::SaveExceptionsToSession("writ_exceptions");
		//header("location: ../ui/issue_writ.php");
      
		Response::createObjectiveResponse(false, ExceptionHandler::ConvertExceptionsToJsObject());
		die();
	}
 
	//header("location: ../ui/view_writ.php?WID=" . $ret->writ_id . "&WVER=" . $ret->writ_ver . "&STID=" . $ret->staff_id);
	Response::createObjectiveResponse(true, "{WID:" . $ret->writ_id . ",WVER:" . $ret->writ_ver . ",STID:" . $ret->staff_id . "}");
	die();
}

function DeleteWrit()
{
	$return = manage_writ::RemoveWrit($_REQUEST["writ_id"], $_REQUEST["writ_ver"], $_REQUEST["staff_id"]);
	echo Response::createObjectiveResponse($return, ($return ? "" : ExceptionHandler::popExceptionDescription()));
	die();
}

function IssueCorrectiveWrit()
{             
	$start_date = isset($_POST["base_writ_issue"]) ? $_POST["base_execute_date"] : $_POST["corrective_date"];
	$end_date = $_POST["execute_date"];
		
	if (!isset($_POST["base_writ_issue"]))
	{    
		if(manage_writ::Is_Writ_For_Correct($_POST["staff_id"],$_POST['corrective_date'])== null)
		{
			ExceptionHandler::PushException("در تاريخ شروع اصلاح حکم وجود ندارد .");
			//	header("location: ../ui/CorrectiveIssueWrit.php");
			Response::createObjectiveResponse(false, ExceptionHandler::ConvertExceptionsToJsObject());
			die();
		}		
	}
	
	if(manage_staff::check_exist_staff_id($_POST["staff_id"])== true)
	{ 
		/*if(isset($_POST["issue_date"]))
			 $issueDate = $_POST["issue_date"] ; 
		else*/ $issueDate = DateModules::shNow ();
			
		$writ_rec = manage_writ::IssueWrit($_POST["staff_id"],
										   $_POST["writ_type_id"], 
										   $_POST["writ_subtype_id"],
										   $_POST["execute_date"],
                                           $_POST["person_type"],
										   $issueDate,
										   true,
										   true,
										   $_POST["send_letter_no"],
										   NULL,
										   NULL,
										   $_POST["base"]
										   );
                                           
                                           
							  
	}
	
	if($writ_rec === false )
	{
		//header("location: ../ui/CorrectiveIssueWrit.php");
		Response::createObjectiveResponse(false, ExceptionHandler::ConvertExceptionsToJsObject());
		die();
	}
	else
	{ 
		if (isset($_POST["base_writ_issue"]))
		{
			$writ_type_id = $_POST["base_writ_type_id"];
			$writ_subtype_id = $_POST["base_writ_subtype_id"];
			$execute_date = $_POST["base_execute_date"];
			$base = $_POST["base_base"];
			$send_letter_no = $_POST["base_send_letter_no"]; 
			$issue_date = /*(!empty($_POST["base_issue_date"])) ?  $_POST["base_issue_date"] :*/ DateModules::shNow();   
		}
		else 
		{
			$execute_date = $_POST["corrective_date"];
            $writ_type_id = NULL ;
            $writ_subtype_id = NULL ;
            $base = NULL ;
            $send_letter_no = NULL ;
            $issue_date = NULL ;
            $_POST["base_writ_issue"] = NULL ; 
		} 
		 //شروع اصلاح 
		$ret = manage_writ::start_corrective_writ_issue($_POST["staff_id"],
		 										 $execute_date,
		 										 $writ_rec,
		 										 $writ_type_id,
		 										 $writ_subtype_id,
		 										 $base,
		 										 $send_letter_no,
		 										 $issue_date,
		 										 $_POST["base_writ_issue"]);

		if($ret === false)
		{
			Response::createObjectiveResponse(false, ExceptionHandler::ConvertExceptionsToJsObject());
			die();
		}

        Response::createObjectiveResponse(true, "{WID:" . $ret->writ_id . ",WVER:" . $ret->writ_ver . ",STID:" . $ret->staff_id . "}");      
		die();
	}
	
}

function GroupIssueWrit()
{
	ini_set('max_execution_time', 1000); //300 seconds = 5 minutes
	ini_set("memory_limit",'500M');
	
	$where = "s.person_type=" . $_POST["person_type"];
	$whereParams = array();
	//------------------------ Make Where Clause ----------------------
	
    if(!empty($_REQUEST["ouid"]))
	{
		$where .= " AND ( o.ouid = :ouid OR o.parent_path LIKE :ouid2 OR  o.parent_path LIKE :ouid3 OR o.parent_path LIKE :ouid4 ) ";
		$whereParams[":ouid"] = $_REQUEST["ouid"];
        $whereParams[":ouid2"] = "%,". $_REQUEST["ouid"].",%";
        $whereParams[":ouid3"] = "%".$_REQUEST["ouid"].",%";
        $whereParams[":ouid4"] = "%,".$_REQUEST["ouid"]."%";
	}
	
	if(!empty($_POST["from_PersonID"]))
	{
		$where .= " AND s.staff_id >= :f_personid";
		$whereParams[":f_personid"] = $_POST["from_PersonID"];
	}
	if(!empty($_POST["to_PersonID"]))
	{
		$where .= " AND s.staff_id <= :t_personid";
		$whereParams[":t_personid"] = $_POST["to_PersonID"];
	}
	
	$where .= " AND " . QueryHelper::makeWhereOfCheckboxList("emp_state", "emp_state");
	//-----------------------------------------------------------------
	//$execute_date = DateModules::Shamsi_to_Miladi($_POST["execute_date"]);
	//-----------------------------------------------------------------
	/*$sql = 'SELECT s.staff_id,
                   s.last_writ_id,
                   s.last_writ_ver,
                   p.pfname ,
                   p.plname 

            FROM staff s
                 JOIN writs w ON (s.staff_id=w.staff_id AND s.last_writ_id = w.writ_id AND s.last_writ_ver = w.writ_ver)
		 
                 JOIN persons p ON(s.PersonID = p.PersonID and s.person_type=p.person_type)';
	$sql .= (!empty($_REQUEST["ouid"])) ? 	
					"LEFT OUTER JOIN org_new_units o ON (s.ouid=o.ouid)" : "";
                 
//JOIN Basic_Info bi ON bi.typeid = 6 and w.education_level = bi.InfoID and bi.MasterID = 1 
	
     $sql .= ' WHERE s.last_writ_id > 0 AND s.last_writ_ver > 0 AND   w.cost_center_id != 46 AND 

                  w.emp_mode in('.EMP_MODE_PRACTITIONER.',
			                    '.EMP_MODE_EDUCATIONAL_MISSION.',
			                    '.EMP_MODE_INTERNAL_STUDY_OPPORTUNITY.',
			                    '.EMP_MODE_RUSTICATION_EXTERNAL_STUDY_OPPORTUNITY.',
			                    '.EMP_MODE_INTERNAL_STUDY_MISSION.',
			                    '.EMP_MODE_EXTERNAL_STUDY_MISSION.',
			                    '.EMP_MODE_ENGAGEMENT.') AND ' . $where; */
        
 $whereParams = array();
 $sql = " select  s.staff_id,
                   s.last_writ_id,
                   s.last_writ_ver,
                   p.pfname ,
                   p.plname , writ_type_id, writ_subtype_id  ,
 g2j(wr.execute_date) execute_date 

 from writs w inner join staff s
                       on s.staff_id=w.staff_id AND s.last_writ_id = w.writ_id AND
                          s.last_writ_ver = w.writ_ver
           inner join writs wr on s.staff_id = wr.staff_id
inner join persons p on s.personid = p.personid

     where w.emp_state =4  and
           wr.writ_type_id = 5 and
           wr.writ_subtype_id = 145 and
           wr.execute_date > '2014-08-22' and
           wr.execute_date < '2014-11-22' and
           wr.history_only = 0 " ; 
 
 
    $staff_dt = PdoDataAccess::runquery_fetchMode($sql, $whereParams);

echo PdoDataAccess::GetLatestQueryString() ;
echo "****" ; die();
	unset($sql);
	unset($where);
	unset($whereParams);
	
    if($staff_dt->rowCount() == 0)
    {
            echo "موارد انتخابی شما هیج فردی را برای صدور حکم شامل نمی شود.";
            die();
    }
	
	$log_obj = new manage_writ_group_issue_log();
	$send_letter_no = $_POST["send_letter_no"];
	$unsuccess_count = 0;
		
	if(empty($_POST["step"])) 
			 $_POST["step"] = 1 ; 
    
	$staff_count = $staff_dt->rowCount();
	$writ_obj = "";
	
	for($index=0; $index < $staff_count; $index++)
	{
        $staff_row = $staff_dt->fetch();
		/*if(isset($_POST["prevent_two_writ_at_one_day"]))
		{
			if(count(PdoDataAccess::runquery("
				select * from writs
				where staff_id=" . $staff_dt[$index]["staff_id"] . " 
					and execute_date = '" . $execute_date . "'")) != 0)
			continue;
		}*/
		//.............................
		if(!empty($_POST["to_send_letter_no"]))
		{
			if ($send_letter_no > $_POST["to_send_letter_no"]) 
			{
				ExceptionHandler::PushException(EXCEED_OF_END_SEND_LETTER_NO_ERR);
				break;
			}
		}

		//.............................
		/*$writ_obj = manage_writ::IssueWrit($staff_row["staff_id"], 
			$_POST["writ_type_id"], 
			$_POST["writ_subtype_id"],
			$_POST["execute_date"],
			$_POST["person_type"],
			DateModules::Shamsi_to_Miladi($_POST["issue_date"]),
			false,
			false,
			$send_letter_no); */ 
                
                $_POST["issue_date"] = DateModules::shNow(); 
                $execute_date =  $staff_row["execute_date"] ;                 
                $arr = preg_split('/\//', $execute_date);	
                $execute_date = '1394/'.$arr[1]."/".$arr[2] ; 
                
                $writ_obj = manage_writ::IssueWrit($staff_row["staff_id"], 
			$staff_row["writ_type_id"], 
			$staff_row["writ_subtype_id"],
			$execute_date,
			$_POST["person_type"],
			DateModules::Shamsi_to_Miladi($_POST["issue_date"]),
			false,
			false,
			$send_letter_no);
		
		if(!$writ_obj)
		{
			$log_obj->make_unsuccess_rows($staff_row);
			continue;
		}

		manage_writ_item::compute_writ_items($writ_obj->writ_id, $writ_obj->writ_ver, $writ_obj->staff_id);
		/*@var $writ_obj manage_writ*/

		if ($writ_obj !== false && $writ_obj->writ_id > 0)
		{
			$staff_row['writ_id'] = $writ_obj->writ_id;
			$log_obj->make_success_row($staff_row);

			$send_letter_no = (!empty($_POST["step"]) && !empty($_POST["send_letter_no"])) ? $send_letter_no + $_POST["step"] : "";
		}
		else
		{
			$log_obj->make_unsuccess_rows($staff_row);

			if(!empty($_POST["stop_error_count"]) && $unsuccess_count >= $_POST["stop_error_count"])
			{
				ExceptionHandler::PushException(TOO_MANY_ERR);
				break;
			}
		}
		unset($writ_obj);
	}
	
	$log_obj->finalize();
	$log_obj->make_result();
	die();
	
	
}

function GroupCancelWrit()
{
	ini_set('max_execution_time', 1000); //300 seconds = 5 minutes
	ini_set("memory_limit",'500M');
	
	$where = "s.person_type=" . $_POST["person_type"];
	$whereParams = array();
	//------------------------ Make Where Clause ----------------------	
	if(!empty($_REQUEST["ouid"]))
	{
		$where .= " AND ( o.ouid = :ouid OR o.parent_path LIKE '%,:ouid,%' OR  o.parent_path LIKE '%:ouid,%' OR o.parent_path LIKE '%,:ouid%' ) ";
		$whereParams[":ouid"] = $_REQUEST["ouid"];
	}
	if(!empty($_POST["from_PersonID"]))
	{
		$where .= " AND p.PersonID >= :f_personid";
		$whereParams[":f_personid"] = $_POST["from_PersonID"];
	}
	if(!empty($_POST["to_PersonID"]))
	{
		$where .= " AND p.PersonID <= :t_personid";
		$whereParams[":t_personid"] = $_POST["to_PersonID"];
	}
	if(!empty($_POST["from_issue_date"]))
	{
		$where .= " AND issue_date <= :f_issue_date";
		$whereParams[":f_issue_date"] = $_POST["from_issue_date"];
	}
	if(!empty($_POST["to_issue_date"]))
	{
		$where .= " AND issue_date >= :t_issue_date";
		$whereParams[":t_issue_date"] = $_POST["to_issue_date"];
	}
	if(!empty($_POST["execute_date"]))
	{
		$execute_date = DateModules::Shamsi_to_Miladi($_POST["execute_date"]);
		$where .= " AND execute_date = :exe_date";
		$whereParams[":exe_date"] = $execute_date;
	}
	if(!empty($_POST["writ_type_id"]))
	{
		$where .= " AND w.writ_type_id = :writ_type_id";
		$whereParams[":writ_type_id"] = $_POST["writ_type_id"];
	}
	if(!empty($_POST["writ_subtype_id"]))
	{
		$where .= " AND w.writ_subtype_id = :writ_subtype_id";
		$whereParams[":writ_subtype_id"] = $_POST["writ_subtype_id"];
	}
	//-----------------------------------------------------------------
	
	/*$where .= " AND bf.MasterID in (1,2) AND 
	                   if( tbl1.personid is not null , bf.MasterID in (1) , ( bf.MasterID in (1,2) and  tbl1.personid is null  ) )" ; */
	
	$query = "SELECT s.staff_id,
		    	   w.writ_id,
		           w.writ_ver,
		           w.writ_type_id,
		           w.writ_subtype_id,
		           w.issue_date,
		           w.execute_date,
		           p.pfname,
			   p.plname
			  
	
		    FROM writs w
		    	JOIN org_new_units o ON(w.ouid=o.ouid)
				JOIN staff s ON (s.staff_id=w.staff_id)
				
                JOIN persons p ON(s.PersonID = p.PersonID and s.person_type=p.person_type)				
                
		    WHERE " . $where;
	
	/*inner join Basic_Info bf on(bf.TypeID=6 AND bf.InfoID=w.education_level)
		left join (select personid
			    from bases
			    where BaseType in(4) AND

				BaseStatus = 'NORMAL')  tbl1 
				
			on tbl1.personid = s.personid*/
	
	$staff_dt = PdoDataAccess::runquery($query, $whereParams);
	//echo PdoDataAccess::GetLatestQueryString() ; die() ; 
	if(count($staff_dt) == 0)
	{
		echo "موارد انتخابی شما هیچ حکمی را برای ابطال شامل نمی شود.";
		die();
	}
	
	$log_obj = new manage_writ_group_cancel_log();
	$success_count = 0;
        $unsuccess_count = 0;
       
	for($index=0; $index < count($staff_dt); $index++)
	{
		/*if(manage_writ::IsUsed($staff_dt[$index]["writ_id"], $staff_dt[$index]["writ_ver"], $staff_dt[$index]["staff_id"]))
		{
			ExceptionHandler::PushException("از حکم در جای دیگری استفاده شده است.");
			$log_obj->make_unsuccess_rows($staff_dt[$index], ExceptionHandler::GetExceptionsToString("<br>"));
			$unsuccess_count++;
		}*/
		if(manage_writ::get_writ_state($staff_dt[$index]["writ_id"], $staff_dt[$index]["writ_ver"], $staff_dt[$index]["staff_id"]) != WRIT_PERSONAL)
		{
			ExceptionHandler::PushException("این حکم منتقل شده است.");
			$log_obj->make_unsuccess_rows($staff_dt[$index]);
			$unsuccess_count++;
		}
		else
		{
			if(!manage_writ::RemoveWrit($staff_dt[$index]["writ_id"], $staff_dt[$index]["writ_ver"], $staff_dt[$index]["staff_id"]))
			{
				$log_obj->make_unsuccess_rows($staff_dt[$index]);
				$unsuccess_count++;
			}
			else 
			{
				$log_obj->make_success_row($staff_dt[$index]);
				$success_count++;
			}
		}
		/*if($index == 0)
		{
			$log_obj->finalize();
			echo $log_obj->make_result();
			die();
		}*/
	}
	
	$log_obj->finalize();
	echo $log_obj->make_result();
	die();
}

function selectPossibleTransferWrits()
{  
	MakeAdvanceSearchWhere($where, $whereParam);

	$state;
	$Allowed = " "; 
	if(HRSystem == PersonalSystem)
		$state = isset($_REQUEST["return"]) ? "2" : "1";
	else {
	
		$state = isset($_REQUEST["return"]) ? "3" : "2"; 
		//$Allowed = isset($_REQUEST["return"]) ? " AND w.writ_recieve_date >='".DateModules::shamsi_to_miladi(substr(DateModules::shNow(), 0,8)."01")."'" : "";
		
		}

    if($state == "3" )
    {
      // temp Table payment_writs 
    }
	
	$stateWhere = "" ; 
	$viewWhere = "" ; 

	if(isset($_GET['view']))
	{
		if($_GET['view'] == 1)
			
			$viewWhere = " w.view_flag = 0 AND  " ; 


	}
	else {
		
		$stateWhere = " w.state=" . $state . " AND " ; 
	}
    
	$query = "select w.writ_id,
                     w.writ_ver,
                     w.staff_id,
                     w.ouid,
                     w.issue_date,
                     w.history_only,
                     w.corrective,
                     w.execute_date,
                     concat(wt.title,' - ', wst.title) as wt_title,
                     bi_emp_state.title as emp_state_title,
                     w.ref_letter_no,
                     w.ref_letter_date,
                     w.person_type,
                     concat(p.pfname, ' ', p.plname) fullname,
                     w.corrective_writ_id,
                     w.correct_completed,
					 w.view_flag
				
				from staff s
                              
        		  LEFT OUTER JOIN writs w ON (w.staff_id = s.staff_id)
                  LEFT OUTER JOIN writ_types wt ON ((w.writ_type_id = wt.writ_type_id) AND (w.person_type = wt.person_type))
                  LEFT OUTER JOIN writ_subtypes wst ON (w.writ_subtype_id = wst.writ_subtype_id AND w.writ_type_id = wst.writ_type_id 
                  											AND w.person_type = wst.person_type)
                  LEFT OUTER JOIN persons p ON (s.PersonID = p.PersonID)
                  LEFT OUTER JOIN org_new_units o ON (w.ouid = o.ouid)
                  LEFT JOIN Basic_Info bi_emp_state on(bi_emp_state.TypeID=3 and w.emp_state=bi_emp_state.InfoID)
                  LEFT JOIN payment_writs pw ON pw.writ_id = w.writ_id and pw.writ_ver = w.writ_ver and pw.staff_id = w.staff_id 
				  
			where w.execute_date >= '" . TRANSFER_WRIT_EXE_DATE . "' $Allowed AND 
				 $stateWhere $viewWhere
                s.person_type in (".manage_access::getValidPersonTypes().") /*AND  w.cost_center_id in (".manage_access::getValidCostCenters().")*/ AND 
				 (w.cost_center_id !=46 OR  w.cost_center_id is NULL ) AND w.emp_state <> 0 AND
				(w.history_only=0 OR w.history_only IS NULL) AND 
				(w.dont_transfer = 0 OR w.dont_transfer IS NULL) AND
                ( pw.writ_id is null ) AND
				(w.correct_completed!=" . WRIT_CORRECTING . ") AND " . $where . "
			
			order by p.plname,p.pfname,s.staff_id,w.execute_date,w.writ_id,w.writ_ver";
   	
	$temp = PdoDataAccess::runquery($query, $whereParam);

        for($i=0 ; $i < count($temp) ; $i++ ){

				$temp[$i]['full_unit_title'] = manage_units::get_full_title($temp[$i]['ouid']);
			} 
	//if($_SESSION["UserID"] == "jafarkhani") { echo $query."wewe"; print_r($whereParam) ; die(); }
	echo dataReader::getJsonData ($temp, count($temp), $_GET ["callback"]);
	die ();
}


function selectArrearTransferWrits()
{

	MakeAdvanceSearchWhere($where, $whereParam);

	$state;

	$state = isset($_REQUEST["return"]) ? "1" : "0";
	
	$stateWhere = "" ; 
			
	$stateWhere = " w.arrear = " . $state . " AND " ; 
		 
	$curYear = DateModules::GetYear(DateModules::miladi_to_shamsi(DateModules::Now()));
	
	$query = "select w.writ_id,
                     w.writ_ver,
                     w.staff_id,
                     w.ouid,
                     w.issue_date,
                     w.history_only,
                     w.corrective,
                     w.execute_date,
                     concat(wt.title,' - ', wst.title) as wt_title,
                     bi_emp_state.title as emp_state_title,
                     w.ref_letter_no,
                     w.ref_letter_date,
                     w.person_type,
                     concat(p.pfname, ' ', p.plname) fullname,
                     w.corrective_writ_id,
                     w.correct_completed,
					 w.view_flag
				
				from staff s
                              
        		  LEFT OUTER JOIN writs w ON (w.staff_id = s.staff_id)
                  LEFT OUTER JOIN writ_types wt ON ((w.writ_type_id = wt.writ_type_id) AND (w.person_type = wt.person_type))
                  LEFT OUTER JOIN writ_subtypes wst ON (w.writ_subtype_id = wst.writ_subtype_id AND w.writ_type_id = wst.writ_type_id 
                  											AND w.person_type = wst.person_type)
                  LEFT OUTER JOIN persons p ON (s.PersonID = p.PersonID)
                  LEFT OUTER JOIN org_new_units o ON (w.ouid = o.ouid)
                  LEFT JOIN Basic_Info bi_emp_state on(bi_emp_state.TypeID=3 and w.emp_state=bi_emp_state.InfoID)
                  LEFT JOIN payment_writs pw ON pw.writ_id = w.writ_id and pw.writ_ver = w.writ_ver and pw.staff_id = w.staff_id 
				  
			where w.execute_date >= '" . TRANSFER_WRIT_EXE_DATE . "' AND w.state = 3 AND  
				  w.execute_date < '".str_replace("/","-",DateModules::shamsi_to_miladi($curYear."/01/01"))."'  AND				 
				  substr(g2j(w.execute_date),1,4) < substr(g2j(writ_recieve_date),1,4) AND 
				 $stateWhere																								
                s.person_type in (".manage_access::getValidPersonTypes().") AND w.cost_center_id in (".manage_access::getValidCostCenters().") AND
				w.emp_state <> 0 AND
				(w.history_only=0 OR w.history_only IS NULL) AND 
				(w.dont_transfer = 0 OR w.dont_transfer IS NULL) AND
                ( pw.writ_id is null ) AND
				(w.correct_completed!=" . WRIT_CORRECTING . ") AND " . $where . "
			
			order by p.plname,p.pfname,s.staff_id,w.execute_date,w.writ_id,w.writ_ver";
		
	$temp = PdoDataAccess::runquery($query, $whereParam);
/*if($_SESSION['UserID'] == 'jafarkhani') {
	echo PdoDataAccess::GetLatestQueryString(); 
	die(); 
}*/
	for($i=0 ; $i < count($temp) ; $i++ ){

			$temp[$i]['full_unit_title'] = manage_units::get_full_title($temp[$i]['ouid']);

	}
	
	echo dataReader::getJsonData ($temp, count($temp), $_GET ["callback"]);
	die ();
}

function transferAction()
{
	$selected_writs = array();
	$keys = array_keys($_POST);
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chk_") !== false)
		{
			$arr = preg_split('/_/', $keys[$i]);
			$selected_writs[] = array("writ_id" => $arr[1] ,
									  "writ_ver" => $arr[2] ,
									  "staff_id"  => $arr[3],
									  "execute_date"  => $arr[4]);
		}
	}
	
	$new_state = $_POST["new_state"];
	$old_state = ($_POST["mode"] == "return") ? $new_state+1 : $new_state-1;

	$return = true;
	for($i=0; $i < count($selected_writs); $i++)
	{	
		
			if(manage_writ::change_writ_state($old_state, $new_state, $selected_writs[$i]["writ_id"], $selected_writs[$i]["writ_ver"],
				$selected_writs[$i]["staff_id"],$selected_writs[$i]["execute_date"]) == false)
				$return = false;
					
	}

	if($return)
		echo "true";
	else
		print_r(ExceptionHandler::PopAllExceptions());
	die();

	/*
	$selected_writs = "";
	$keys = array_keys($_POST);
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chk_") !== false)
		{
			$arr = preg_split('/_/', $keys[$i]);
			$selected_writs .= "'" . $arr[1] . "_" . $arr[2] . "_" . $arr[3] . "',";
		}
	}
	$selected_writs = ($selected_writs != "") ? substr($selected_writs, 0, strlen($selected_writs) - 1) : "''";
	 
	$return = PdoDataAccess::runquery("DROP TABLE if EXISTS tmp_transfer_writs");
	if($return === false)
	{
		$DB->rollBack();
		echo "false";
		die();
	}
	
	$return = PdoDataAccess::runquery("CREATE TABLE tmp_transfer_writs TYPE=MyIsam AS
				(SELECT w.staff_id , w.writ_id , w.writ_ver , w.execute_date
				FROM writs w
				WHERE (w.history_only=0 OR w.history_only IS NULL)
 			    	AND (w.dont_transfer = 0 OR w.dont_transfer IS NULL)
  			    	AND (w.correct_completed !=" . WRIT_CORRECTING . ")
  			     	AND CONCAT(writ_id,'_',writ_ver,'_',staff_id) in (" . $selected_writs . "))");
	if($return === false)
	{
		$DB->rollBack();
		echo "false";
		die();
	}
	
	$return = PdoDataAccess::runquery("UPDATE writs w
	  		  SET state = " . $_POST["new_state"] . ($_POST["new_state"] == "2" ? ",view_flag=1" : "") . "
			  WHERE (w.history_only=0 OR w.history_only IS NULL) 
   			    	AND (w.dont_transfer = 0 OR  w.dont_transfer IS NULL)
  			    	AND (w.correct_completed!=" . WRIT_CORRECTING . ")
  	     			AND CONCAT(writ_id,'_',writ_ver,'_',staff_id) in (" . $selected_writs . ")");
	if($return === false)
	{
		$DB->rollBack();
		echo "false";
		die();
	}
	
	if(PdoDataAccess::AffectedRows() > 0)
	{
		$return = PdoDataAccess::runquery("UPDATE writs w
				INNER JOIN tmp_transfer_writs tw ON(tw.staff_id = w.staff_id AND 
						tw.execute_date = w.execute_date AND
						tw.staff_id = w.staff_id AND 
						((tw.writ_id=w.writ_id AND tw.writ_ver>w.writ_ver) OR tw.writ_id>w.writ_id)	AND 
						(w.history_only=0 OR w.history_only IS NULL))
				SET history_only=1
				WHERE w.state = " . $_POST["new_state"]);
		if($return === false)
		{
			$DB->rollBack();
			echo "false";
			die();
		}
	}
	
	$DB->commit();
	echo "true";
	die();*/
}

function ArrearTransferAction()
{
	$selected_writs = array();
	$keys = array_keys($_POST);
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chk_") !== false)
		{
			$arr = preg_split('/_/', $keys[$i]);
			$selected_writs[] = array("writ_id" => $arr[1] ,
									  "writ_ver" => $arr[2] ,
									  "staff_id"  => $arr[3],
									  "execute_date"  => $arr[4]);
		}
	}
	
	$new_state = $_POST["new_state"];
	$old_state = ($_POST["mode"] == "return") ? $new_state+1 : $new_state-1;

	$return = true;
	
	for($i=0; $i < count($selected_writs); $i++)
	{	
		
		if(PdoDataAccess::runquery(" update writs set arrear = ".$new_state."
										where writ_id = ".$selected_writs[$i]["writ_id"]." and  writ_ver = ".$selected_writs[$i]["writ_ver"]." and 
												staff_id = ".$selected_writs[$i]["staff_id"]." and execute_date = '". $selected_writs[$i]["execute_date"]."'") === false) {
					
			$return = false;
		}
					
	}
	
	
	if($return)
		echo "true";
	else
		print_r(ExceptionHandler::PopAllExceptions());
	die();
	
}

function confirmAction()
{
	$checked_writs = "";
	$unchecked_writs = "";
	$keys = array_keys($_POST);
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"hdn_") !== false && $_POST[$keys[$i]] != "")
		{
			$arr = preg_split('/_/', $keys[$i]);
			if($_POST[$keys[$i]] == "1")
				$checked_writs .= "'" . $arr[1] . "_" . $arr[2] . "_" . $arr[3] . "',";
			else
				$unchecked_writs .= "'" . $arr[1] . "_" . $arr[2] . "_" . $arr[3] . "',";
		}
	}
	$checked_writs = ($checked_writs != "") ? substr($checked_writs, 0, strlen($checked_writs) - 1) : "''";
	$unchecked_writs = ($unchecked_writs != "") ? substr($unchecked_writs, 0, strlen($unchecked_writs) - 1) : "''";

	$return = PdoDataAccess::runquery("update writs set view_flag=1
				WHERE CONCAT(writ_id,'_',writ_ver,'_',staff_id) in (" . $checked_writs . ")");
	if($return === false)
	{
		echo "false";
		die();
	}
	$return = PdoDataAccess::runquery("update writs set view_flag=0
				WHERE CONCAT(writ_id,'_',writ_ver,'_',staff_id) in (" . $unchecked_writs . ")");
	if($return === false)
	{
		echo "false";
		die();
	}

	echo "true";
	die();
}

function recalculate()
{
   
	$return = manage_writ_item::compute_writ_items( $_POST["writ_id"],  $_POST["writ_ver"],  $_POST["staff_id"], true);
    if($return)
		$return = 'true';
	else
		$return =  'false';

    echo Response::createObjectiveResponse($return, ($return ? $_POST["writ_id"] : ExceptionHandler::popExceptionDescription()));
	die();
}

function calculate()
{   
     
    $return = manage_writ_item::compute_writ_items( $_POST["writ_id"],  $_POST["writ_ver"],  $_POST["staff_id"]);
    if($return)
		$return = 'true';
	else
		$return =  'false';

    echo Response::createObjectiveResponse($return, ($return ? $_POST["writ_id"] : ExceptionHandler::popExceptionDescription()));
	die();
}

function Prior_Corrective_Writ()
{
    $obj = new manage_writ($_POST['writ_id'],$_POST['writ_ver'], $_POST['staff_id']);
    PdoDataAccess::FillObjectByArray($obj,$_POST);
  
    $ret = $obj->prior_corrective_writ();
  
    if($ret != 'Stop') {
        //echo $ret['writ_id']."---".$ret['writ_ver']."---".$ret['staff_id'] ; die();
		echo Response::createObjectiveResponse(true,"{WID: ".$ret['writ_id']." , WVER: ".$ret['writ_ver']." , STF:".$ret['staff_id']." }" ); 
		
		}
    else if ($ret == 'Stop')
        echo Response::createResponse(false,"Stop");

    die();
    
}

function Next_Corrective_Writ()
{

     $obj = new manage_writ($_POST['writ_id'],$_POST['writ_ver'], $_POST['staff_id']);
     PdoDataAccess::FillObjectByArray($obj,$_POST);
	 
     $ret = $obj->Next_Corrective_Writ();
 
     if(!empty($ret->writ_id))
        echo Response::createObjectiveResponse(true,"{WID: ".$ret->writ_id." , WVER: ".$ret->writ_ver." , STF:".$ret->staff_id." }" );
     else
      echo Response::createResponse(false,"Stop");
}

