<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
require_once '../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ($task) {
	
	case "searchWritTypes":
		searchWritTypes();

	case "searchWritSubTypes":
		searchWritSubTypes();	

	case "searchUnits":
		searchUnits();

	case "searchSubUnits":
		searchSubUnits();

	case "searchCostCenter":		
		searchCostCenter();
            
        case "searchCostCenterPlan":		
		searchCostCenterPlan();            
           
	case "searchPost":
		searchPost();

	case "searchStates":
		searchStates();

	case "searchCities":
		searchCities();

	case "searchReligion":
		searchReligion();

	case "searchSubreligion":
		searchSubreligion();

	case "searchMilitary":
		searchMilitary();

    case "searchSubMilitary":
		searchSubMilitary();

	case "searchStudyField":
		searchStudyField();

	case "searchStudyBranches":
		searchStudyBranches();

	case "searchCountries":
		searchCountries();
		 
	case "searchuniversities":
		searchuniversities();

    case "searchComputetype":
		searchComputetype();

	case "searchMultiplicand":
		searchMultiplicand();

	case "selectAllPosts":
		selectAllPosts();
		
	case "searchPayType" ; 
		 searchPayType() ; 
		
	case "searchBank":
		searchBank() ; 
		
	case "searchPersonType" :
		 searchPersonType() ; 
		
	case "searchEmpState" :
		 searchEmpState() ; 
		 
	case "searchEmpMod" :
		  searchEmpMod() ;  
	 
	case "searchDetective" :
		 searchDetective() ; 
		 
	case "searchSalaryItemTypes":
		searchSalaryItemTypes() ; 
}

function searchWritTypes()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("writ_type_id" => $_REQUEST["extraRowID"], "title" => $_REQUEST["extraRowText"], "person_type" => ""));
	}
	$_REQUEST["person_type"] = (empty($_REQUEST["person_type"]) ? 3  : $_REQUEST["person_type"] ) ; 
	$dt = array_merge($dt, PdoDataAccess::runquery("
			select writ_type_id,title,person_type
			from HRM_writ_types
			where person_type = " . $_REQUEST["person_type"] . "
			order by title"));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchWritSubTypes()
{ 
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("writ_type_id" => "", 
							"writ_subtype_id" => $_REQUEST["extraRowID"],
							"title" => $_REQUEST["extraRowText"]),
							"person_type" => "");
	}
	$dt = array_merge($dt, PdoDataAccess::runquery("
			select writ_type_id,writ_subtype_id,title,person_type
			from HRM_writ_subtypes
			where writ_type_id=? and person_type = ?
			order by title", array($_REQUEST["writ_type_id"],$_REQUEST["person_type"] )));
   

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
	
}

function searchStudyField()
{       
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("sfid" => $_REQUEST["extraRowID"], "ptitle" => $_REQUEST["extraRowText"]));
	}
        $dt[] = array("sfid" => "-1", "ptitle" => "-");
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT sfid,ptitle FROM HRM_study_fields order by  ptitle "));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
	
	/*$dt = PdoDataAccess::runquery("select sfid,ptitle from study_fields");

	echo common_component::PHPArray_to_JSArray($dt, "sfid", "ptitle");
	die();*/
}

function searchStudyBranches()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("sbid" => $_REQUEST["extraRowID"],"sfid" => "", "ptitle" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery("SELECT sbid,sfid,ptitle FROM HRM_study_branchs where  sfid = ? order by  ptitle",
			array($_REQUEST["sfid"])));


	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
	
	/*$dt = PdoDataAccess::runquery("select sbid,ptitle,sfid from study_branchs");

	echo common_component::PHPArray_to_JSArray($dt, "ptitle", "sbid", "sfid");
	die();*/
}

function searchCountries()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("country_id" => $_REQUEST["extraRowID"], "ptitle" => $_REQUEST["extraRowText"]));
	}
        $dt[] = array("country_id" => "-1", "ptitle" => "-" );
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT country_id,ptitle FROM HRM_countries order by ptitle "));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchuniversities()
{   
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("university_id" => $_REQUEST["extraRowID"],"country_id" => "", "ptitle" => $_REQUEST["extraRowText"]));
	}
        
        $dt[] = array("university_id" => "-1","country_id" => "", "ptitle" => "-");
        
	$dt = array_merge($dt, PdoDataAccess::runquery("SELECT university_id,country_id,ptitle  FROM HRM_universities where  country_id = ? order by  ptitle",
			array($_REQUEST["country_id"])));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
	
	/*$dt = PdoDataAccess::runquery("select university_id,ptitle,country_id from universities");

	echo common_component::PHPArray_to_JSArray($dt, "ptitle", "university_id", "country_id");
	die();*/
}

function searchPost()
{ 
	   /*$dt = PdoDataAccess::runquery("select post_id,post_no,title,ouid from position where ouid=" . $_REQUEST["ouid"] . "
		AND (staff_id='' OR staff_id is null)");*/
    
	$dt = PdoDataAccess::runquery("
		select post_id,post_no,title, concat(o2.ptitle ,' - ', o.ptitle) unit_title ,
		from position p 
		left join org_new_units o on(p.ouid = o.ouid)
		left join org_new_units o2 on(o.parent_ouid = o2.ouid)
		where (staff_id='' OR staff_id is null)");
	
    echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchStates()
{
    
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("state_id" => $_REQUEST["extraRowID"], "ptitle" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery("select state_id,ptitle from HRM_states order by ptitle"));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchCities()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("state_id"=> "","city_id" => $_REQUEST["extraRowID"], "ptitle" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery("select state_id,city_id,ptitle from HRM_cities where state_id=? order by ptitle",
		array($_REQUEST["state_id"])));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchReligion()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("InfoID" => $_REQUEST["extraRowID"], "InfoDesc" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT InfoID,InfoDesc
                                                                    FROM BaseInfo where typeid = 50 order by  InfoDesc "));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchSubreligion()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("InfoID" => $_REQUEST["extraRowID"],"MasterID" => "", "Title" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT InfoID,param1,InfoDesc 
                                                            FROM BaseInfo where typeid = 51 and param1 = ? order by  InfoDesc ",
			array($_REQUEST["MasterID"])));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchComputetype()
{
    $dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("InfoID" => $_REQUEST["extraRowID"], "InfoDesc" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT InfoID,InfoDesc FROM BaseInfo where typeid = 66 order by InfoID "));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchCostCenter()
{
    $dt = array();
	if(!empty($_REQUEST["rep"]) && $_REQUEST["rep"]==1 )
	{
		$dt[] = array("cost_center_id" => "-1", "title" => "همه");
	}
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT cost_center_id,title FROM cost_centers where cost_center_id in (". manage_access::getValidCostCenters().")"));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchCostCenterPlan()
{
    $dt = array();
    if(!empty($_REQUEST["rep"]) && $_REQUEST["rep"]==1 )
    {
            $dt[] = array("cost_center_id" => "-1", "title" => "همه");
    }
    $dt = array_merge($dt, PdoDataAccess::runquery(" SELECT CostCenterID , Title
                                                        FROM cost_centers 
                                                   "));

    echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
    die();
}

function searchMultiplicand()
{  
    $dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("InfoID" => $_REQUEST["extraRowID"],"param1" => "", "InfoDesc" => $_REQUEST["extraRowText"]));
	}
   	$dt = array_merge($dt, PdoDataAccess::runquery("SELECT InfoID,param1,InfoDesc FROM BaseInfo 
		                                                   where typeid = 68 and param1 = ? order by  InfoDesc",
			array($_REQUEST["MasterID"])));
	
	//echo PdoDataAccess::GetLatestQueryString(); die();

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();   
}

function searchMilitary()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("InfoID" => $_REQUEST["extraRowID"], "InfoDesc" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT InfoID,InfoDesc FROM BaseInfo where typeid = 52 order by  InfoDesc "));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchSubMilitary()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("InfoID" => $_REQUEST["extraRowID"],"param1" => "", "InfoDesc" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery("SELECT InfoID,param1,InfoDesc FROM BaseInfo where typeid = 53 and param1 = ? order by  InfoDesc",
			array($_REQUEST["MasterID"])));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchUnits()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("ouid" => $_REQUEST["extraRowID"], "ptitle" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery(" select ouid,ptitle from org_new_units where parent_ouid is null or parent_ouid =0 order by ouid "));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchSubUnits()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("EduGrpCode" => $_REQUEST["extraRowID"], "PEduName" => $_REQUEST["extraRowText"],"FacCode" => ""));
	}
 
	$dt = array_merge($dt, PdoDataAccess::runquery("select EduGrpCode,PEduName,FacCode from baseinfo.EducationalGroups where FacCode = ? order by PEduName ",
			array($_REQUEST["FacCode"])));

   
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function selectAllPosts()
{
	require_once inc_manage_post;
	$where = "1=1";
	$whereParam = array();

	//-----------------------
	if(!empty($_POST["post_no"]))
	{
		$where .= " AND p.post_no=:pno";
		$whereParam[":pno"] = $_POST["post_no"];
	}

	if(!empty($_POST["post_type"]) && $_POST["post_type"] != -1)
	{
		$where .= " AND p.post_type=:ptype";
		$whereParam[":ptype"] = $_POST["post_type"];
	}

	if(!empty($_POST["title"]))
	{
		$where .= " AND p.title like :title";
		$whereParam[":title"] = "%" . $_POST["title"] . "%";
	}
				
	if(!empty($_POST["ouid"]) && $_POST["ouid"] != -1)
	{
				
		$result = QueryHelper::MK_org_units($_POST["ouid"], isset($_POST["sub_units"]) ? true : false);		
		$where .= " AND " . $result["where"];
		$whereParam = array_merge($whereParam, $result["param"]);
	}
	
	$where .= dataReader::makeOrder();	
	$temp = manage_posts::GetAllPosts($where , $whereParam);
	$no = count($temp);
	
	
	
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	for($i=0; $i<count($temp); $i++)
	{
		if($temp[$i]["ouid"] != "")
			$temp[$i]["full_unit_title"] = manage_units::get_full_title($temp[$i]["ouid"]);
		else
			$temp[$i]["full_unit_title"] = "";
	}

	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function searchPayType()
{
    $dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("InfoID" => $_REQUEST["extraRowID"], "Title" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT InfoID,Title FROM Basic_Info where typeid = 50  and InfoID in (".manage_access::getValidPayments().")"));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function searchBank()
{
	$dt = array();
	if(!empty($_REQUEST["rep"]) && $_REQUEST["rep"] == 1 )
		$dt[] = array("bank_id" => "-1", "name" => "همه");
	
	$query = "SELECT bank_id,name FROM banks where 1=1";
	$params = array();
	if(!empty($_REQUEST["query"]))
	{
		$query .= " AND name like ?";
		$params[] = "%" . $_REQUEST["query"] . "%";
	}
	
	if(!empty($_REQUEST["bank_id"]))
	{
		$query .= " AND bank_id=?";
		$params[] = $_REQUEST["bank_id"];
	}
	elseif($_REQUEST["sub"] == 1 && empty($_REQUEST["bank_id"]) )  {
		$query .= " AND 1=0 ";
	}
	
	$dt = array_merge($dt, PdoDataAccess::runquery($query, $params));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
	
}

function searchPersonType()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("InfoID" => $_REQUEST["extraRowID"], "Title" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT InfoID,Title FROM Basic_Info where typeid = 16 "));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();	
} 

function searchEmpState()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("InfoID" => $_REQUEST["extraRowID"], "Title" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT InfoID,Title FROM Basic_Info where typeid = 3 "));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();	
} 


function searchEmpMod()
{
	$dt = array();
	if(!empty($_REQUEST["extraRowText"]))
	{
		$dt[] = array(array("InfoID" => $_REQUEST["extraRowID"], "Title" => $_REQUEST["extraRowText"]));
	}
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT InfoID,Title FROM Basic_Info where typeid = 4 "));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();	
}

function searchDetective()
{
	$dt = array();
	
	$dt = array_merge($dt, PdoDataAccess::runquery(" SELECT distinct daily_work_place_no , detective_name
														 FROM org_new_units
															WHERE daily_work_place_no is not null and detective_name is not null "));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
} 

function searchSalaryItemTypes()
{
	$dt = array();
	if(!empty($_REQUEST["all"]) && $_REQUEST["all"] == 1 )
		$dt[] = array("salary_item_type_id" => "-1", "full_title" => "همه");
		
	$Whr = " " ; 
	if(!empty($_REQUEST["pt"]))
	{
		$Whr = " AND person_type in (100,101 ,".$_REQUEST["pt"].") " ; 
	}
	if(!empty($_REQUEST["subtype"]))
	{
		if($_REQUEST["subtype"] == 1 )
			$Whr .= " AND available_for in (1) " ; 
		else if($_REQUEST["subtype"] == 2  )
			$Whr .= " AND available_for in (2,4,5) " ; 
		
		else if($_REQUEST["subtype"] == 3  )
			$Whr .= " AND available_for in (3) " ; 
	}
	
	if(!empty($_REQUEST["salary_item_type_id"]))
	{
		$Whr .= " AND salary_item_type_id = ".$_REQUEST["salary_item_type_id"] ; 
	}
	
	if(!empty($_REQUEST["ET"]))
	{
		$Whr .= " AND effect_type =".$_REQUEST["ET"] ;
		
		if($_REQUEST["ET"] == 2)
			$Whr .= " and available_for not in (1,4) " ; 
			
		$Whr .= " AND ( validity_end_date IS NULL  OR validity_end_date = '0000-00-00'  OR validity_end_date > '2014-03-20' )  " ;
	}
	
	
	

	$query = "SELECT salary_item_type_id,full_title , person_type FROM salary_item_types where 1=1 ".$Whr ;
	$params = array();
	if(!empty($_REQUEST["query"]))
	{
		$query .= " AND ( full_title like :p1 OR salary_item_type_id like :p2  ) ";
		$params[":p1"] = "%" . $_REQUEST["query"] . "%";
		$params[":p2"] = "%" . $_REQUEST["query"] . "%";
	}
		
	$query .= " order by person_type " ; 
	$dt = array_merge($dt, PdoDataAccess::runquery($query, $params));
	if($_SESSION['UserID'] == 'bmahdipour')
	{
		//echo PdoDataAccess::GetLatestQueryString() . "***sds*"; die(); 
	}
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
	
}

?>