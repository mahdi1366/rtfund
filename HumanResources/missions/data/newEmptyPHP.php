<?php
/* -----------------------------
  //	Programmer	: s.taghizadeh
  //	Date		: 94.8
  ----------------------------- */
//require_once 'AutoLoad.php';
include_once '../../header.inc.php';
require_once inc_dataGrid;
$formID = null;

//ini_set('display_errors', 'On');
//error_reporting(E_ERROR);

$Uname = $_SESSION['User']->UserID;



$dg = new sadaf_datagrid("dg", $js_prefix_address ."../data/MissionRequest.data.php?task=GetControlKargozini", "grid");
$dg->addColumn("", "status", "", true);
$dg->addColumn("", "MissionLocationID", "", true);
$dg->addColumn("", "state_id", "", true);
$dg->addColumn("", "city_id", "", true);
$dg->addColumn("", "VehicleTitle", "", true);
$dg->addColumn("", "TypeTitle", "", true);
$dg->addColumn("", "TypeTitleMiss", "", true);
$dg->addColumn("", "StatusTitle", "", true);
$dg->addColumn("", "ControllerPerson", "", true);
$dg->addColumn("", "DispatcherId", "", true);
$dg->addColumn("", "FromDate_Time", "", true);
$dg->addColumn("", "ptitle", "", true);
$dg->addColumn("", "SupervisorId", "", true);
$dg->addColumn("", "RequesterPersonId", "", true);
$dg->addColumn("", "ManagerId", "", true);
$dg->addColumn("", "RequesterRole", "", true);
$dg->addColumn("", "stuff", "", true);
$dg->addColumn("", "PersonID", "", true);
$dg->addColumn("", "FromDate", "", true);
$dg->addColumn("", "ToDate", "", true);
$dg->addColumn("", "type", "", true);
$dg->addColumn("", "MissType", "", true);
$dg->addColumn("", "PlaceStay", "", true);
$dg->addColumn("", "transport", "", true);
$dg->addColumn("", "TitleDispatcher", "", true);

$col = $dg->addColumn("شناسه", "RequestID", "");
$col->sortable = true;
$col->width = 50;
$col = $dg->addColumn(" درخواست دهنده", "person");
$col->sortable = true;
$col->width = 100;
$col = $dg->addColumn("تاریخ درخواست", "RequestTime");
$col->sortable = true;
$col->width = 120;
$col = $dg->addColumn("نوع ماموریت", "MissType");
$col->sortable = true;
$col->width = 100;
$col->renderer = "function(v,p,r){return r.data.TypeTitleMiss;}";
/*$col = $dg->addColumn("نوع اعزام", "type");
$col->sortable = true;
$col->width = 50;
$col->renderer = "function(v,p,r){return r.data.TypeTitle;}";

$col = $dg->addColumn("نوع ماموریت", "MissType");
$col->sortable = true;
$col->width = 130;
$col->renderer = "function(v,p,r){return r.data.TypeTitleMiss;}";*/

 /*$col = $dg->addColumn("اعزام کننده", "TitleDispatcher");
    $col->sortable = true;
    $col->width = 250;*/
/* $col = $dg->addColumn("استان ", "sname");
  $col->sortable = true;
  $col->width = 100;

  $col = $dg->addColumn("شهر ", "cname");
  $col->sortable = true;
  $col->width = 100; */

$col = $dg->addColumn(" موضوع ", "subject");
$col->sortable = true;
$col->width = 100;

$col = $dg->addColumn("  از تاریخ", "FromDate2");
$col->sortable = true;
$col->width = 70;

$col = $dg->addColumn("تا تاریخ", "ToDate2");
$col->sortable = true;
$col->width = 70;

/*  $col = $dg->addColumn("  وسایل مورد نیاز", "stuff");
  $col->sortable = true;
  $col->width = 100; */

$col = $dg->addColumn("ایجاد کننده ", "RequesterRoleTitle");
$col->sortable = true;
$col->width = 110;
/* $col = $dg->addColumn("عملیات","", "");
  $col->renderer = "ControlMissionRequestObject.opRenderOriginalGrid";
  $col->width = 50; */
/*$col = $dg->addColumn("مشاهده سابقه", "","");
$col->renderer = "function(v,p,r){return ControlMissionRequest.opRenderHistory2(v,p,r);}";
$col->width = 100;
$col->align = "center";*/

/*$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return AcceptKargozini.opRenderOriginalGrid(v,p,r);}";
$col->width = 70;*/
$col = $dg->addColumn("بررسی و تایید", "","");
$col->renderer = "function(v,p,r){return AcceptKargozini.opRenderAccept(v,p,r);}";
$col->width = 100;
$col->align = "center";

$col = $dg->addColumn("مشاهده سابقه", "","");
$col->renderer = "function(v,p,r){return AcceptKargozini.opRenderHistory(v,p,r);}";
$col->width = 100;
$col->align = "center";
/* $col = $dg->addColumn(" نوع وسیله رفت و برگشت ", "vehicle");
  $col->sortable = true;
  $col->width = 100;
  $col->renderer = "function(v,p,r){return r.data.VehicleTitle;}"; */

/* $col = $dg->addColumn(" وضعیت ", "LastStatus");
  $col->renderer = "function(v,p,r){return MissionStatusRender(v,p,r);}";
  $col->sortable = true;
  $col->width = 100;

  $col = $dg->addColumn("", "");
  $col->sortable = true;
  $col->width = 30;
  $col->renderer = "OperationMenuRender"; */


//$dg->addButton = true;
//$dg->addHandler = "function(v,p,r){ return AcceptKargoziniObject.AddRequest(v,p,r);}";
$dg->title = "بررسی درخواست های ماموریت";

$dg->height = 500;
$dg->width = 900;
$dg->pageSize = 10;

$grid = $dg->makeGrid_returnObjects();
//-----------------------------------------------------------------------------------------


//********************************************************************

//------------------------------------------------------------------------------------
//لیست شهرهای ماموریت"-----------------------------------------
$dg_Citys = new sadaf_datagrid("dg_Citys",$js_prefix_address . "../data/MissionRequest.data.php?task=GetInfoDestination", "");
$dg_Citys->addColumn("", "InfoDestinationID", "", true);
$dg_Citys->addColumn("", "RequestId", "", true);
$dg_Citys->addColumn("", "CityId", "", true);
$dg_Citys->addColumn("", "StateId", "", true);
$dg_Citys->addColumn("", "Address", "", true);
$dg_Citys->addColumn("", "TimeStay", "", true);


$col = $dg_Citys->addColumn("مکان", "Destination");
$col->editor = "this.subItemCombo";
$col->width = 350;


$col = $dg_Citys->addColumn("آدرس", "Address");
$col->sortable = true;
$col->width = 250;
$col->editor = ColumnEditor::TextField();
$col = $dg_Citys->addColumn("مدت توقف", "TimeStay");
$col->sortable = true;
$col->width = 100;
$col->editor = ColumnEditor::NumberField();
//$dg_Citys->hideHeaders=true;
//$dg_Citys->addButton = true;
/*$col = $dg_Citys->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return AcceptKargoziniObject.opRender(v,p,r);}";
$col->width = 50;
$dg_Citys->addButton('', 'ایجاد ردیف', 'add', "function(v,p,r){ return AcceptKargoziniObject.AddDestinationMission(v,p,r);}");
$dg_Citys->enableRowEdit = true;
$dg_Citys->rowEditOkHandler = "function(v,p,r){return AcceptKargoziniObject.SaveDestination(v,p,r);}";
$dg_Citys->title = "";*/

$dg_Citys->height = 200;
$dg_Citys->width = 700;
//$dg_Citys->pageSize = 10;
$dg_Citys->EnablePaging = false ; 
 $dg_Citys->EnableSearch = false;
 $dg_Citys->disableFooter=true;

$grid2 = $dg_Citys->makeGrid_returnObjects();

//-------------------------------------------------------------------------------
//وسیله نقلیه-----------------------------------
//------------------------------------------------------------------------------------
$dg_Detail = new sadaf_datagrid("dg_Detail",$js_prefix_address . "../data/MissionRequest.data.php?task=GetVehicleDetail", "");
$dg_Detail->addColumn("", "VehicleDetailID", "", true);
$dg_Detail->addColumn("", "RequestId", "", true);
$dg_Detail->addColumn("", "VehicleId", "", true);
$dg_Detail->addColumn("", "FromCityId", "", true);
$dg_Detail->addColumn("", "FromStateId", "", true);
$dg_Detail->addColumn("", "ToCityId", "", true);
$dg_Detail->addColumn("", "ToStateId", "", true);
$dg_Detail->addColumn("", "CarSupplierId", "", true);
$dg_Detail->addColumn("", "VehicleTitle", "", true);
$dg_Detail->addColumn("", "CarSupplierTitle", "", true);

$col = $dg_Detail->addColumn("از شهر", "DestinationFrom");
$col->sortable = true;
$col->width = 200;
$col->editor = "this.subItemComboDetailFrom";


$col = $dg_Detail->addColumn("تا شهر", "DestinationTo");
$col->sortable = true;
$col->width = 200;
$col->editor = "this.subItemComboDetailTo";

$col = $dg_Detail->addColumn("نوع وسیله نقلیه", "VehicleId");
$col->sortable = true;
$col->width = 100;
$col->editor = "this.ComboMissionVehicles";
$col->renderer = "function(v,p,r){return r.data.VehicleTitle;}";

$col = $dg_Detail->addColumn("محل تامین خودرو", "CarSupplierId");
$col->sortable = true;
$col->width = 125;
$col->editor = "this.ComboCarSupplier";
$col->renderer = "function(v,p,r){return r.data.CarSupplierTitle;}";

$col = $dg_Detail->addColumn("تاریخ درخواست", "ReqDate", GridColumn::ColumnType_date);
$col->sortable = true;
$col->width = 85;
$col->editor = ColumnEditor::SHDateField();

/*$col = $dg_Detail->addColumn("حذف", "");
$col->renderer = "function(v,p,r){return AcceptKargoziniObject.opRender2(v,p,r);}";
$col->align = "center";
$col->width = 45;*/

/*$dg_Detail->title = "";
$dg_Detail->titleCollapse = false;
$dg_Detail->addButton('', 'ایجاد ردیف', 'add', "function(v,p,r){ return AcceptKargoziniObject.AddVehicleMission(v,p,r);}");
$dg_Detail->enableRowEdit = true;
$dg_Detail->rowEditOkHandler = "function(v,p,r){return AcceptKargoziniObject.SaveVehicle(v,p,r);}";*/
$dg_Detail->height = 190;
$dg_Detail->width = 750;
//$dg_Detail->pageSize = 10;
  $dg_Detail->EnableSearch = false;
$grid3 = $dg_Detail->makeGrid_returnObjects();
//------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------
   
    
   

//---------------------------------------------------------------------------------------
require_once '../js/AcceptKargozini.js.php';
//require_once '../js/MissionRequestsRenders.js.php';

//-------------------------------------------------------------------------------
?>
<body dir='rtl'>
<center>
    </br>
    <font size="2" color="red"> 
    تا اطلاع ثانوی درخواست صدور بلیط از طریق این سیستم انجام نمی شود. لذا خواهشمند است طبق روال سابق برای درخواست بلیط اقدام فرمایید.
    </font></br>
    <font size="2" color="red"> 
    لطفا از مرورگر فایرفاکس استفاده نمایید
    </font></br>
    <a href ="DownloadMissionsUserManual.php" >دریافت فایل راهنما</a>

    </br>
    </br>

    </br>

    <div id="main">
        <center>
            <div id='FormDIV'> </div>

            <br><br><div id='MissionRequests'></div>

        </center>




        <div style="color:black;font-size:12pt;font-family: B Nazanin" align=center>

            *** همکار گرامی چنانچه ماموریت شما تایید نهایی گردد به صورت اتومات در سیستم حضور و غیاب ثبت خواهد گردید ***
        </div>
</center>
</body>
<?php ?>
<script type="text/javascript">
                        
                       
                        
    var AcceptKargoziniObject = new AcceptKargozini();
             
                         
  
</script>



<?
//} ?>