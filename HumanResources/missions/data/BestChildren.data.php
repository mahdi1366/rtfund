<?php

//---------------------------
// programmer:	B.Mahdipour
// create Date: 94.05
//---------------------------

require_once '../../header.inc.php'; 
require_once '../class/BestChildren.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");


switch ($task) {
    
	case "SaveItem" :
	      SaveItem();
      
	case "GetChildItm" :
		  GetChildItm();
		
	case "GetStu" :
		  GetStu();
        case "ChangeStatus" :
		  ChangeStatus();
         case "GetPersonType"    :
            GetPersonType();
        case "GetPersons"    :
            GetPersons();
case "GetUnits" :
GetUnits();

	case "GetTreeNodes":
		GetTreeNodes();
}


function GetStu() {
	
	$where  =  " where (1=1) " ; 
$whereParam = array();

$field = isset($_GET ["fields"]) ? $_GET ["fields"] : "";

if (isset($_GET ["query"]) && $_GET ["query"] != "") {
    
        switch ($field) {

                                case "BSID":
                                        $where .= " AND concat(pfname,' ',plname) like :qry ";
                                        $whereParam[":qry"] = "%" . $_GET["query"] . "%";
                                        break;

                                case "status":
                                        $where .= " AND status= :qry ";
                                        if($_GET["query"] == 'تائید') $_GET["query"] = 1 ; 
                                        elseif($_GET["query"] == 'عدم تائید') $_GET["query"] = 2 ; 
                                        elseif($_GET["query"] == 'ارسال شده') $_GET["query"] = 0 ; 

                                        $whereParam[":qry"] = $_GET["query"];
                                        break;

                                case "RegDate":
                                        $where .= " AND bs.RegDate= :qry ";
                                        $whereParam[":qry"] = DateModules::shamsi_to_miladi($_GET["query"]);
                                        break;
                        }

}

 
	$where .= dataReader::makeOrder();

	$temp = manageBestChild::GetChildInfo($where,$whereParam);
	$no = count($temp);
/*if($_SESSION['UserID'] == 'bmahdipour' ) 
{

echo PdoDataAccess::GetLatestQueryString() ; die();

}*/
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();		
	
}


function GetChildItm() {
	
	$where  =  " where PersonID =".$_SESSION['PersonID'] ; 
	$where .= dataReader::makeOrder();

	$temp = manageBestChild::GetChildItm($where);
	$no = count($temp);

	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();		
	
}

function SaveItem() {
		
	$obj = new manageBestChild();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$obj->PersonID = $_SESSION['PersonID'];
	     
	$st1 = preg_split ( "/\./", $_FILES ['PicFileType']['name'] );
    $extension1 = $st1 [count ( $st1 ) - 1];	
	$obj->PicFileType =  $extension1;
	
	$st2 = preg_split ( "/\./", $_FILES ['PaperFileType']['name'] );
    $extension2 = $st2 [count ( $st2 ) - 1];	
	$obj->PaperFileType =  $extension2;
	$obj->RegDate = DateModules::Now(); 
	$obj->status = 0 ; // وضعیت ارسال شده			
			
    $return = $obj->ADD();

	$filename = $obj->BSID;

	$fp = fopen("/mystorage/BestStuDocument/PicDoc/" .$filename . "." . $extension1, "w");
	fwrite($fp, fread(fopen($_FILES['PicFileType']['tmp_name'], 'r' ),$_FILES['PicFileType']['size']));
	fclose($fp);
	
	$fp = fopen("/mystorage/BestStuDocument/PaperDoc/" .$filename . "." . $extension2, "w");
	fwrite($fp, fread(fopen($_FILES['PaperFileType']['tmp_name'], 'r' ),$_FILES['PaperFileType']['size']));
	fclose($fp);


	echo Response::createObjectiveResponse(true,$obj->BSID);
	die();


}

function ChangeStatus() { 
	
		
	if(!empty($_POST['AccField']) && $_POST['AccField'] ==1 )	
		$status = 1 ; 
	else 
		$status = 2 ; 
	
	$qry = " update hrmstotal.BestStudent set status = ".$status." , comments ='".$_POST['comments']."'  where BSID = ".$_POST['BSID'] ; 
        PdoDataAccess::runquery($qry);

	if(ExceptionHandler::GetExceptionCount() != 0 ) 
	{		
           echo Response::createObjectiveResponse(false,'عملیات مورد نظر با شکست مواجه گردید.');
	   die();				
	}		
	else 
	{
	  echo Response::createObjectiveResponse(true,$_POST['BSID']);
	  die();
	}
		
	
	
}
function GetPersonType() {
	
	  
    if (isset($_REQUEST["query"]) && $_REQUEST["query"] != "") {
		$where .= " AND Title like :qry";           
				
		$whereParam = array(":qry" => "%" . $_REQUEST["query"] . "%" );
	}
	
	 $query = "select *
			from hrmstotal.Basic_Info 
			where TypeID = 16 $where order by InfoID  ";
         
         $temp = PdoDataAccess::runquery($query,$whereParam);
      /* if($_SESSION['UserID'] == 'staghizadeh' ) 
{
echo PdoDataAccess::GetLatestQueryString()	; 
print_r(ExceptionHandler::PopAllExceptions()); die();
}*/
	$no = count($temp);
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();		
	
}
function GetPersons() {
   
	$where = " 1=1";
	$whereParam = array();
	if (isset($_REQUEST["query"]) && $_REQUEST["query"] != "") {
		$where .= " AND (PFName LIKE :qry or PLName LIKE :qry or concat(PFName,' ',PLName) like :qry
				                          or concat(PLName,' ',PFName) like :qry 
				                          OR p.PersonID = :qry2
				                          OR o.ptitle like :qry
										  OR s.staff_id = :qry2
				)";
		$whereParam = array(":qry" => "%" . $_REQUEST["query"] . "%",
			            ":qry2" => $_REQUEST["query"]);
	}

	$include_new_persons = isset($_REQUEST["newPersons"]) ? true : false;
	$show_All_history = isset($_REQUEST["show_All_history"]) ? true : false;

	if (isset($_REQUEST['ouid'])) {

		$whereParam[":ouid"] = $_REQUEST['ouid'];
		$whereParam[":ouid2"] = "%," . $_REQUEST['ouid'] . ",%";
		$whereParam[":ouid3"] = "%" . $_REQUEST['ouid'] . ",%";
		$whereParam[":ouid4"] = "%," . $_REQUEST['ouid'] . "%";

		$where .= " AND (  o.parent_path like :ouid2 OR
                             o.parent_path like :ouid3 OR
                             o.parent_path like :ouid4 OR
                             o.ouid=:ouid OR
                             o.parent_ouid = :ouid
                               ) ";
	}

	$temp = manageBestChild::SelectPerson($where, $whereParam, $include_new_persons, $show_All_history);
/*if($_SESSION['UserID'] == 'staghizadeh' ) 
{
echo PdoDataAccess::GetLatestQueryString()	; 
print_r(ExceptionHandler::PopAllExceptions()); die();
}*/
	$no = count($temp);
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}
function GetUnits()
{
 if (isset($_REQUEST["query"]) && $_REQUEST["query"] != "") {
		$where .= " AND ptitle like :qry";           
				
		$whereParam = array(":qry" => "%" . $_REQUEST["query"] . "%" );
	}
	
	
     $query = "select ouid,ptitle from hrmstotal.org_new_units where (parent_ouid='' or parent_ouid is null) $where ";
    
         $temp = PdoDataAccess::runquery($query,$whereParam);
     /*  if($_SESSION['UserID'] == 'staghizadeh' ) 
{
echo PdoDataAccess::GetLatestQueryString()	; 
print_r(ExceptionHandler::PopAllExceptions()); die();
}*/
	$no = count($temp);
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();	
}
function GetTreeNodes()
{
	$nodes = PdoDataAccess::runquery("select ouid as id,ptitle as text,'true' as leaf,parent_path
		from org_new_units where parent_ouid=0 or parent_ouid=0 is null order by ptitle");
		
	$cur_level_uids = "";
	$returnArray = $nodes;

	$ref_cur_level_nodes = array(); 
	for($i=0; $i<count($nodes); $i++)
	{
		$ref_cur_level_nodes[] = & $returnArray[$i];
		$cur_level_uids .= $nodes[$i]["id"] . ",";
	}
	$cur_level_uids = substr($cur_level_uids, 0, strlen($cur_level_uids) - 1);
	
	while (true)
	{
		$nodes = PdoDataAccess::runquery("select ouid as id,ptitle as text,'true' as leaf,parent_ouid as parentId,parent_path
			from org_new_units where parent_ouid in (" . $cur_level_uids . ") order by ptitle");
		
		if(count($nodes) == 0)
			break;
		//............ add current level nodes to returnArray ................
		$temp_ref = array();
		$cur_level_uids = "";
		
		for($i=0; $i<count($nodes); $i++)
		{
			//............ extract current level uids ..................
			$cur_level_uids .= $nodes[$i]["id"] . ",";
			
			for($j=0; $j < count($ref_cur_level_nodes); $j++)
			{
				if($nodes[$i]["parentId"] == $ref_cur_level_nodes[$j]["id"])
				{
					if(!isset($ref_cur_level_nodes[$j]["children"]))
					{
						$ref_cur_level_nodes[$j]["children"] = array();
						$ref_cur_level_nodes[$j]["leaf"] = "false";
					}
					$ref_cur_level_nodes[$j]["children"][] = $nodes[$i];
					$temp_ref[] = & $ref_cur_level_nodes[$j]["children"][count($ref_cur_level_nodes[$j]["children"])-1];
					break;
				}
			}
		}
		
		$ref_cur_level_nodes = $temp_ref;
		$cur_level_uids = substr($cur_level_uids, 0, strlen($cur_level_uids) - 1);	
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

	//print_r($returnArray);
	$return_str = '{"text":"واحد های سازمانی","id":"source","parent_path":"",';
	$return_str .= (count($returnArray) == 0) ? '"leaf":true' : '"children":';
	if(count($returnArray) != 0)
		$return_str .= json_encode($returnArray);
	$return_str .= '}';
	return $return_str;
}
?>