<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.09.25
//---------------------------
require_once '../../../header.inc.php'; 
require_once inc_dataGrid;
require_once inc_manage_unit;

 
//-----------------------------------------------

$FacilID = isset($_POST["FacilID"]) ? $_POST["FacilID"] : "";
//________________  GET ACCESS  _________________
$accessObj = new ModuleAccess($FacilID,4, Deputy, Module_writ);

if(!$accessObj->UpdateAccess()){ 
    die() ; 
}

$listAcc = explode(',',manage_access::getValidPersonTypes()) ;

$emp = 0;
$worker = 0;
$prof = 0;
$gharardadi = 0;

for($t=0 ; $t < count($listAcc); $t++)
{
    if($listAcc[$t] == 1 )
        $prof = 1  ;

    if($listAcc[$t] == 2)
        $emp = 1 ;

    if($listAcc[$t] == 3)
       $worker = 1 ;

    if($listAcc[$t] == 5)
       $gharardadi = 1 ;

}

require_once '../js/user_messages.js.php';

if($_SESSION['SystemCode'] == PersonalSystemCode ) {

if( $prof ==1 ) {
 
    $profdg = new sadaf_datagrid("ProfSTFGrid", $js_prefix_address . "../data/staff.data.php?task=WarningMsg&prof=1", "ProfWarningMsgDIV");

    $col=$profdg->addColumn("نسخه حکم","writ_ver","int","true");
  
    $col = $profdg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $profdg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 100;

    $col = $profdg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 80;

    $col = $profdg->addColumn("شماره حکم", "writ_id", "int");
    $col->width = 80;

	$col = $profdg->addColumn("واحد محل خدمت", "full_unit_title", "int");
    $col->width =160;

    $col = $profdg->addColumn("قلم حقوقی", "full_title", "string");
    $col->width = 80;

    $col = $profdg->addColumn("تاریخ", "warning_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 80;

    $col = $profdg->addColumn("پیام", "warning_message", "string");
   
    $col = $profdg->addColumn("عملیات", "", "string");
    $col->renderer = "UserMsg.profopRender";
    $col->width = 50;

    $profdg->width = 950;
    $profdg->title = "پیامهای هشدار";
    $profdg->EnableSearch = false;
    $profdg->notRender = true;
    $profdg->DefaultSortField = "warning_date";
    $profdg->autoExpandColumn = "warning_message";
    $profgrid = $profdg->makeGrid_returnObjects();

//..............................................................................................................................................................

    $profidg = new sadaf_datagrid("ProfICGrid", $js_prefix_address . "../data/staff.data.php?task=IcludedChildrenMsg&prof=1", "ProfIcludedChildrenDIV");

    $col = $profidg->addColumn("شماره پرسنلی", "PersonID", "int","true");

    $col = $profidg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width =80;

    $col = $profidg->addColumn("نام فرد", "pname", "string");
    $col->width = 140;

    $col = $profidg->addColumn("نام بستگان", "dname", "string");
    
    $col = $profidg->addColumn("وابستگی", "dependency_title", "string");
    $col->width = 80;

    $col = $profidg->addColumn("تاریخ تولد", "birth_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $profidg->addColumn("از تاریخ", "from_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $profidg->addColumn("تا تاریخ", "to_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $profidg->addColumn("نوع بیمه", "insure_type", "string");
    $col->width = 100;

     $col = $profidg->addColumn("عملیات", "", "string");
     $col->renderer = "UserMsg.profopRenderIC";
     $col->width = 50;

    $profidg->width = 950;
    $profidg->title = "خاتمه یا تغییر سابقه کفالت";
    $profidg->EnableSearch = false;
    $profidg->notRender = true;
    $profidg->autoExpandColumn = "dname";
    $profidg->DefaultSortField = "to_date";
    $profigrid = $profidg->makeGrid_returnObjects();
   

    }

    //..............................................  کارمندان...................................
    if( $emp ==1 ) {
	
    $trdg = new sadaf_datagrid("TarfiGrid", $js_prefix_address . "../data/staff.data.php?task=TarfiMsg&emp=1", "EmpTarfiMsgDIV");
    
    $col = $trdg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 120;

    $col = $trdg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $trdg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 120;
   
    $col = $trdg->addColumn(" تاریخ شروع به کار", "work_start_date", "int");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 120;
   
    $col = $trdg->addColumn("مدت سربازی", "military_duration", "int");
    $col->width = 100;
     
    $col = $trdg->addColumn("تاریخ آخرین ترفیع", "UpgradeDate", "int");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    
    $trdg->width = 750;
    $trdg->pageSize = 10;
    $trdg->autoExpandColumn = "UpgradeDate";
    $trdg->DefaultSortField = "UpgradeDate";
    $trdg->title = "لیست افراد منتظر ترفیع استحقاقی";
    $trdg->EnableSearch = false;
    $trdg->notRender = true;
    
	
    $tarfigrid = $trdg->makeGrid_returnObjects();  
//..................................................................
	$rdg = new sadaf_datagrid("RetGrid", $js_prefix_address . "../data/staff.data.php?task=RetMsg&emp=1", "EmpRetMsgDIV");
    
    $col = $rdg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 120;

	$col = $rdg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $rdg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 120;
   
    $col = $rdg->addColumn("تاریخ بازنشستگی", "retired_date", "int");
	$col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 120;
	
	$col = $rdg->addColumn("علت بازنشستگی", "retiredTitle", "int");
    $col->width = 120;
	
	$rdg->width = 750;
    $rdg->autoExpandColumn = "warning_message";
    $rdg->DefaultSortField = "warning_date";
    $rdg->title = "لیست افراد منتظر بازنشستگی";
    $rdg->EnableSearch = false;
    $rdg->notRender = true;
	
    $retgrid = $rdg->makeGrid_returnObjects();
	
	//.........................................................................................

    $empdg = new sadaf_datagrid("STFGrid", $js_prefix_address . "../data/staff.data.php?task=WarningMsg&emp=1", "EmpWarningMsgDIV");

    $col=$empdg->addColumn("نسخه حکم","writ_ver","int","true");
    
    $col = $empdg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $empdg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 100;

    $col = $empdg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 80;

    $col = $empdg->addColumn("شماره حکم", "writ_id", "int");
    $col->width = 80;

	$col = $empdg->addColumn("واحد محل خدمت", "full_unit_title", "int");
    $col->width =160;

    $col = $empdg->addColumn("قلم حقوقی", "full_title", "string");
    $col->width = 80;

    $col = $empdg->addColumn("تاریخ", "warning_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 80;

    $col = $empdg->addColumn("پیام", "warning_message", "string");
   
    $col = $empdg->addColumn("عملیات", "", "string");
    $col->renderer = "UserMsg.empopRender";
    $col->width = 50;

    $empdg->width = 950;
    $empdg->autoExpandColumn = "warning_message";
    $empdg->DefaultSortField = "warning_date";
    $empdg->title = "پیامهای هشدار";
    $empdg->EnableSearch = false;
    $empdg->notRender = true;
    $empgrid = $empdg->makeGrid_returnObjects();

//..............................................................................................................................................................

    $empidg = new sadaf_datagrid("ICGrid", $js_prefix_address . "../data/staff.data.php?task=IcludedChildrenMsg&emp=1", "EmpIcludedChildrenDIV");

    $col = $empidg->addColumn("شماره پرسنلی", "PersonID", "int","true");

    $col = $empidg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 80;

    $col = $empidg->addColumn("نام فرد", "pname", "string");
    $col->width = 140;

    $col = $empidg->addColumn("نام بستگان", "dname", "string");
   
    $col = $empidg->addColumn("وابستگی", "dependency_title", "string");
    $col->width = 80;

    $col = $empidg->addColumn("تاریخ تولد", "birth_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $empidg->addColumn("از تاریخ", "from_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $empidg->addColumn("تا تاریخ", "to_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $empidg->addColumn("نوع بیمه", "insure_type", "string");
    $col->width = 100;

     $col = $empidg->addColumn("عملیات", "", "string");
     $col->renderer = "UserMsg.empopRenderIC";
     $col->width = 50;

    $empidg->width = 950;
    $empidg->autoExpandColumn = "dname";
    $empidg->DefaultSortField = "to_date";
    $empidg->title = "خاتمه یا تغییر سابقه کفالت";
    $empidg->EnableSearch = false;
    $empidg->notRender = true;
    $empigrid = $empidg->makeGrid_returnObjects();

//..............................................................................................................................

  /*  $empegldg = new sadaf_datagrid("eglGrid", $js_prefix_address . "../data/staff.data.php?task=eglMsg&emp=1", "EmpEglDIV");

    $col = $empegldg->addColumn("شماره پرسنلی", "PersonID", "int","true");

    $col = $empegldg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 100;

    $col = $empegldg->addColumn("نام", "pfname", "string");
    $col->width = 100;

    $col = $empegldg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 100;

    $col = $empegldg->addColumn("نام پدر ", "father_name", "string");
    $col->width = 100;

    $col = $empegldg->addColumn("کد ملی", "national_code", "string");
    $col->width = 100;

    $col = $empegldg->addColumn("واحد سازمانی", "total_org_unit_title", "string");
   
    $col = $empegldg->addColumn("عملیات", "", "string");
    $col->renderer = "UserMsg.empopRenderegl";
    $col->width = 50;

    $empegldg->width = 950;
    $empegldg->autoExpandColumn = "total_org_unit_title";
    $empegldg->DefaultSortField = "staff_id";
    $empegldg->title = "ارتقاء طبقه";
    $empegldg->EnableSearch = false;
    $empegldg->notRender = true;
    $empeglgrid = $empegldg->makeGrid_returnObjects();*/

    }
    //.................................................... روزمزدبیمه ای .............................
    if( $worker ==1 ) {
	
	$wrdg = new sadaf_datagrid("wRetGrid", $js_prefix_address . "../data/staff.data.php?task=RetMsg&worker=1", "WRetMsgDIV");
    
    $col = $wrdg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 120;

	$col = $wrdg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $wrdg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 120;
   
    $col = $wrdg->addColumn("تاریخ بازنشستگی", "retired_date", "int");
	$col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 120;
	
	$col = $wrdg->addColumn("علت بازنشستگی", "retiredTitle", "int");
    $col->width = 140;
	
	$wrdg->width = 750;
    $wrdg->autoExpandColumn = "warning_message";
    $wrdg->DefaultSortField = "warning_date";
    $wrdg->title = "لیست افراد منتظر بازنشستگی";
    $wrdg->EnableSearch = false;
    $wrdg->notRender = true;
	
    $wretgrid = $wrdg->makeGrid_returnObjects();
	
	//.........................................................................................

    $workerdg = new sadaf_datagrid("STFGrid", $js_prefix_address . "../data/staff.data.php?task=WarningMsg&worker=1", "WorkerWarningMsgDIV");

    $col=$workerdg->addColumn("نسخه حکم","writ_ver","int","true");

    $col = $workerdg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $workerdg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 100;

    $col = $workerdg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 80;

    $col = $workerdg->addColumn("شماره حکم", "writ_id", "int");
    $col->width = 80;

	$col = $workerdg->addColumn("واحد محل خدمت", "full_unit_title", "int");
    $col->width = 160;

    $col = $workerdg->addColumn("قلم حقوقی", "full_title", "string");
    $col->width = 80;

    $col = $workerdg->addColumn("تاریخ", "warning_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 80;

    $col = $workerdg->addColumn("پیام", "warning_message", "string");
    
    $col = $workerdg->addColumn("عملیات", "", "string");
    $col->renderer = "UserMsg.workeropRender";
    $col->width = 50;

    $workerdg->width = 950;
    $workerdg->autoExpandColumn = "warning_message";
    $workerdg->DefaultSortField = "warning_date";
    $workerdg->title = "پیامهای هشدار";
    $workerdg->EnableSearch = false;
    $workerdg->notRender = true;
    $workergrid = $workerdg->makeGrid_returnObjects();

//..............................................................................................................................................................

    $workeridg = new sadaf_datagrid("ICGrid", $js_prefix_address . "../data/staff.data.php?task=IcludedChildrenMsg&worker=1", "WorkerIcludedChildrenDIV");

    $col = $workeridg->addColumn("شماره پرسنلی", "PersonID", "int","true");

    $col = $workeridg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 80;

    $col = $workeridg->addColumn("نام فرد", "pname", "string");
    $col->width = 140;

    $col = $workeridg->addColumn("نام بستگان", "dname", "string");
  
    $col = $workeridg->addColumn("وابستگی", "dependency_title", "string");
    $col->width = 80;

    $col = $workeridg->addColumn("تاریخ تولد", "birth_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $workeridg->addColumn("از تاریخ", "from_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $workeridg->addColumn("تا تاریخ", "to_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $workeridg->addColumn("نوع بیمه", "insure_type", "string");
    $col->width = 100;

     $col = $workeridg->addColumn("عملیات", "", "string");
     $col->renderer = "UserMsg.workeropRenderIC";
     $col->width = 50;

    $workeridg->width = 950;
    $workeridg->autoExpandColumn = "dname";
    $workeridg->DefaultSortField = "to_date";
    $workeridg->title = "خاتمه یا تغییر سابقه کفالت";
    $workeridg->EnableSearch = false;
    $workeridg->notRender = true;
    $workerigrid = $workeridg->makeGrid_returnObjects();


    }
    //.............................................. قراردادی ها .....................................

    if( $gharardadi ==1 ) {

    $trghdg = new sadaf_datagrid("GHTarfiGrid", $js_prefix_address . "../data/staff.data.php?task=TarfiMsg&gharardadi=1", "GTarfiMsgDIV");
    
    $col = $trghdg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 120;

    $col = $trghdg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $trghdg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 120;
   
    $col = $trghdg->addColumn(" تاریخ شروع به کار", "work_start_date", "int");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 120;
   
    $col = $trghdg->addColumn("مدت سربازی", "military_duration", "int");
    $col->width = 100;
     
    $col = $trghdg->addColumn("تاریخ آخرین ترفیع", "UpgradeDate", "int");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    
    $trghdg->width = 750;
    $trghdg->pageSize = 10;
    $trghdg->autoExpandColumn = "UpgradeDate";
    $trghdg->DefaultSortField = "UpgradeDate";
    $trghdg->title = "لیست افراد منتظر ترفیع استحقاقی";
    $trghdg->EnableSearch = false;
    $trghdg->notRender = true;
    
	
    $ghtarfigrid = $trghdg->makeGrid_returnObjects();  

//.............................................................................................
	
	$grdg = new sadaf_datagrid("gRetGrid", $js_prefix_address . "../data/staff.data.php?task=RetMsg&gharardadi=1", "GRetMsgDIV");
    
    $col = $grdg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 120;

	$col = $grdg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $grdg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 120;
   
    $col = $grdg->addColumn("تاریخ بازنشستگی", "retired_date", "int");
	$col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 120;
	
	$col = $grdg->addColumn("علت بازنشستگی", "retiredTitle", "int");
    $col->width = 120;
	
	$grdg->width = 750;
    $grdg->autoExpandColumn = "warning_message";
    $grdg->DefaultSortField = "warning_date";
    $grdg->title = "لیست افراد منتظر بازنشستگی";
    $grdg->EnableSearch = false;
    $grdg->notRender = true;
	
    $gretgrid = $grdg->makeGrid_returnObjects();


    $ghdg = new sadaf_datagrid("STFGrid", $js_prefix_address . "../data/staff.data.php?task=WarningMsg&gharardadi=1", "GhWarningMsgDIV");

    $col=$ghdg->addColumn("نسخه حکم","writ_ver","int","true");
    
    $col = $ghdg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $ghdg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 100;

    $col = $ghdg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 80;

    $col = $ghdg->addColumn("شماره حکم", "writ_id", "int");
    $col->width = 80;

	$col = $ghdg->addColumn("واحد محل خدمت", "full_unit_title", "int");
    $col->width =160;

    $col = $ghdg->addColumn("قلم حقوقی", "full_title", "string");
    $col->width = 80;

    $col = $ghdg->addColumn("تاریخ", "warning_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 80;

    $col = $ghdg->addColumn("پیام", "warning_message", "string");
    
    $col = $ghdg->addColumn("عملیات", "", "string");
    $col->renderer = "UserMsg.ghopRender";
    $col->width = 50;

    $ghdg->width = 950;
    $ghdg->autoExpandColumn = "warning_message";
    $ghdg->DefaultSortField = "warning_date";
    $ghdg->title = "پیامهای هشدار";
    $ghdg->EnableSearch = false;
    $ghdg->notRender = true;
    $ghgrid = $ghdg->makeGrid_returnObjects();

//..............................................................................................................................................................

    $ghidg = new sadaf_datagrid("ICGrid", $js_prefix_address . "../data/staff.data.php?task=IcludedChildrenMsg&gharardadi=1", "GhIcludedChildrenDIV");

    $col = $ghidg->addColumn("شماره پرسنلی", "PersonID", "int","true");

    $col = $ghidg->addColumn("شماره شناسایی", "staff_id", "int");
    $col->width = 80;

    $col = $ghidg->addColumn("نام فرد", "pname", "string");
    $col->width = 140;

    $col = $ghidg->addColumn("نام بستگان", "dname", "string");
   
    $col = $ghidg->addColumn("وابستگی", "dependency_title", "string");
    $col->width = 80;

    $col = $ghidg->addColumn("تاریخ تولد", "birth_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $ghidg->addColumn("از تاریخ", "from_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $ghidg->addColumn("تا تاریخ", "to_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 100;

    $col = $ghidg->addColumn("نوع بیمه", "insure_type", "string");
    $col->width = 100;

     $col = $ghidg->addColumn("عملیات", "", "string");
     $col->renderer = "UserMsg.ghopRenderIC";
     $col->width = 50;

    $ghidg->width = 950;
    $ghidg->DefaultSortField = "to_date";
    $ghidg->autoExpandColumn = "dname";
    $ghidg->title = "خاتمه یا تغییر سابقه کفالت";
    $ghidg->EnableSearch = false;
    $ghidg->notRender = true;
    $ghigrid = $ghidg->makeGrid_returnObjects();

    }
    
}

if($_SESSION['SystemCode'] == SalarySystemCode )
    {
        
    $dg = new sadaf_datagrid("ESGrid", $js_prefix_address . "../data/staff.data.php?task=Estelaji", "ESDIV");

    
    $col = $dg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $dg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 100;

    $col = $dg->addColumn(" ش.شناسایی", "staff_id", "int");
    $col->width = 80;

    $col = $dg->addColumn("ش. حکم", "writ_id", "int");
    $col->width = 80;
    
    $col=$dg->addColumn("نسخه حکم","writ_ver","int");
    $col->width = 80;
    
    $col=$dg->addColumn("وضعیت","emp_state_title","string");
    $col->width = 60;
    
    $col = $dg->addColumn("واحد محل خدمت", "full_unit_title", "string");
        
    $col = $dg->addColumn("تاریخ اجرا", "execute_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 80;
    
    $col = $dg->addColumn(" تاریخ صدور", "issue_date", "string");
    $col->renderer = "function(v){return MiladiToShamsi(v);}";
    $col->width = 80;
    
    $col = $dg->addColumn("وضعیت حکم", "state", "int");   
    $col->renderer ="function(v){if(v == '2') return 'وضعیت میانی'; else return 'تایید حقوق';}" ;     
    $col->width = 80;
    
    $dg->width = 820;
    $dg->DefaultSortField = "writ_id";
    $dg->autoExpandColumn = "full_unit_title";
    $dg->title = "لیست افرادی که حکم مرخصی استعلاجی آنها در دو ماه اخیر صادر گردیده است";
    $dg->EnableSearch = false;    
    $esgrid = $dg->makeGrid_returnObjects();
	
	//................................................ اسامی افرادی که تغییر صندوق بازنشستگی داشتند........................
	
	$retdg = new sadaf_datagrid("RetGrid", $js_prefix_address . "../data/staff.data.php?task=ChangeSandoogh", "CHSDIV");

    
    $col = $retdg->addColumn("نام", "pfname", "string");
    $col->width = 80;

    $col = $retdg->addColumn("نام خانوادگی", "plname", "string");
    $col->width = 100;

    $col = $retdg->addColumn(" ش.شناسایی", "staff_id", "int");
    $col->width = 80;
   
    $col = $retdg->addColumn("واحد محل خدمت", "full_unit_title", "string");
         
    $col = $retdg->addColumn("وضعیت حکم", "state", "int");   
    $col->renderer ="function(v){if(v == '2') return 'وضعیت میانی'; else return 'تایید حقوق';}" ;     
    $col->width = 80;
    
    $retdg->width = 820;
    $retdg->DefaultSortField = "writ_id";
    $retdg->autoExpandColumn = "full_unit_title";
    $retdg->title = " لیست افرادی که تغییر صندوق بازنسشتگی دارند" ; 
    $retdg->EnableSearch = false;    
    $chrgrid = $retdg->makeGrid_returnObjects();
    
    }

?>

<script>
	UserMsg.prototype.afterLoad = function()
	{
		
        <?  if($_SESSION['SystemCode'] == PersonalSystemCode ) { 
            if ($prof == 1 ) { ?>

            this.profgrid = <?= $profgrid?>;
            this.profigrid = <?= $profigrid?>;
                   
        <? } ?>

        <? if ($emp == 1 ) { ?>

            this.empgrid = <?= $empgrid?>;
            this.empigrid = <?= $empigrid?>;
			 this.empretgrid = <?= $retgrid?>;
           this.emptarfigrid = <?= $tarfigrid?>;
         
        <? } ?>
        <? if ($worker == 1 ) { ?>

            this.workergrid = <?= $workergrid?>;         
            this.workerigrid = <?= $workerigrid?>;          
			this.workerretgrid = <?= $wretgrid?>;

        <? } ?>
        <? if ($gharardadi == 1 ) { ?>

            this.ghgrid = <?= $ghgrid?>; 
            this.ghigrid = <?= $ghigrid?>;  
            this.ghretgrid = <?= $gretgrid?>;
            this.ghtarfigrid = <?= $ghtarfigrid?>;

        <? } 
        
        }?>
            
        <?  if($_SESSION['SystemCode'] == SalarySystemCode ) {  ?>

            this.esgrid = <?= $esgrid?>;
            this.esgrid = <?= $esgrid?>;
            
            this.esgrid.render("ESDIV");  
			
			//...................
			
			this.chrgrid = <?= $chrgrid?>;
            this.chrgrid = <?= $chrgrid?>;
            
            this.chrgrid.render("CHSDIV");  
            
        <? } ?>        

	}
    
var WarningMsgObject = new UserMsg();
</script>
<form id="form_UserMsg" method="POST">
<br>
<br>
<? if($_SESSION['SystemCode'] == PersonalSystemCode ) { 
    if( $prof == 1 ){ ?>
    <div id="FS_PROF">
    <div id="FS_PROF1">
        <div id="ProfWarningMsgDIV" style="width:100%"></div>
        <br><br>
        <div id="ProfIcludedChildrenDIV" style="width:100%"></div>
        <br>
        <font color='#15428B' style="width:70%" >
          در اين قسمت ليست افراد از 20 روز قبل از تاريخ ارتقاء تا 10 روز بعد از تاريخ ارتقاء آنان نمايش داده مي شود .
        </font>
        <br><br>
        <div id="ProfEglDIV" style="width:100%"></div>
    </div></div>

 
<? } ?>
<br>
<? if( $emp == 1 ){ ?>
    <div id="FS_EMP">
    <div id="FS_EMP1">
	 <div id="EmpTarfiMsgDIV" style="width:100%"></div>
    <br><br>
	<div id="EmpRetMsgDIV" style="width:100%"></div>
    <br><br>
	
	<div id="EmpWarningMsgDIV" style="width:100%"></div>
    <br><br>
    <div id="EmpIcludedChildrenDIV" style="width:100%"></div>
    <br>
    <font color='#15428B' style="width:70%" >
      در اين قسمت ليست افراد از 20 روز قبل از تاريخ ارتقاء تا 10 روز بعد از تاريخ ارتقاء آنان نمايش داده مي شود .
    </font>
    <br><br>
    <div id="EmpEglDIV" style="width:100%"></div>

</div></div>
<? } ?>
<br>
<? if( $worker == 1 ){ ?>
    <div id="FS_WORK">
    <div id="FS_WORK1">
	
	<div id="WRetMsgDIV" style="width:100%"></div>
    <br><br>
	
	<div id="WorkerWarningMsgDIV" style="width:100%"></div>
    <br><br>
    <div id="WorkerIcludedChildrenDIV" style="width:100%"></div>
    <br>
    <font color='#15428B' style="width:70%" >
      در اين قسمت ليست افراد از 20 روز قبل از تاريخ ارتقاء تا 10 روز بعد از تاريخ ارتقاء آنان نمايش داده مي شود .
    </font>
    <br><br>
    <div id="WorkerEglDIV" style="width:100%"></div>

</div></div>
<? } ?>
<br>
<? if( $gharardadi == 1 ){ ?>
    <div id="FS_GH">
    <div id="FS_GH1">
	 <div id="GTarfiMsgDIV" style="width:100%"></div>
    <br><br>	
	<div id="GRetMsgDIV" style="width:100%"></div>
    <br><br>
	<div id="GhWarningMsgDIV" style="width:100%"></div>
    <br><br>
    <div id="GhIcludedChildrenDIV" style="width:100%"></div>
    <br>
    <font color='#15428B' style="width:70%" >
      در اين قسمت ليست افراد از 20 روز قبل از تاريخ ارتقاء تا 10 روز بعد از تاريخ ارتقاء آنان نمايش داده مي شود .
    </font>
    <br><br>
    <div id="GhEglDIV" style="width:100%"></div>

</div></div>
<? } 

    }
    if($_SESSION['SystemCode'] == SalarySystemCode)
    {   ?>
        <div id="FS_ES">
        <div id="FS_ES1">

        <div id="ESDIV" style="width:100%"></div>
		<br><br>
		<div id="CHSDIV" style="width:100%"></div>
        </div></div>
        <?    
    }
    ?>

</form>
