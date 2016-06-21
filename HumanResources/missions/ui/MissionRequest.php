<?php
/* -----------------------------
  //	Programmer	: Fatemipour
  //	Date		: 93.8
  ----------------------------- */
require_once 'AutoLoad.php';

HTMLBegin();

ini_set('display_errors', 'On');
error_reporting(E_ERROR);

if(($_SESSION["UserID"])=='bmahdipour')
{
    echo 'hiiii';
$_SESSION["PersonID"]='401367006';
}

$Uname = $_SESSION['User']->UserID;
/*

ini_set('display_errors', 'On'); error_reporting(E_ALL); 
//update
$basemysql = PdoDataAccess::getPdoObject("172.20.20.46", 'fatemipour', 'Ft34#ps','baseinfo');
$res = PdoDataAccess::runquery("SELECT * FROM baseinfo.ChartNodes where ChartID = 24 ", array(), $basemysql);
    
$mysql = PdoDataAccess::getPdoObject("172.20.20.28", 'user1', 'user1','baseinfo');
foreach ($res as $rec) {
    $r = PdoDataAccess::runquery("INSERT INTO `baseinfo`.`ChartNodes`
                                    (`ChartNodeID`,`ChartID`,`ParentID`,`RelatedItemMasterID`,`RelatedItemID`,`NodeType`)
                                    VALUES
                                    (?,?,?,?,?,?) "
                    , array($rec['ChartNodeID'],$rec['ChartID'], $rec['ParentID'], $rec['RelatedItemMasterID'], $rec['RelatedItemID'], $rec['NodeType']), $mysql);
    var_dump($r);
    print_r(PdoDataAccess::PopAllExceptions());
    echo "**********</br>";
}
die();

*/
/*
  if ($Uname == 'fatemipour') {
  ini_set('display_errors', 'On');
  error_reporting(E_ALL);
  $_SESSION['User'] = new StaffUser("shalian");
  $_SESSION["PersonID"] = $_SESSION['User']->PersonID;
  $_SESSION["UserID"] = "shalian";
  } */

require_once '../js/MissionRequest.js.php';
require_once '../js/MissionRequestsRenders.js.php';

$dg = new sadaf_datagrid("dg", "../data/MissionRequest.data.php?task=GetMyRequests", "");
$dg->addColumn("", "status", "", true);
$dg->addColumn("", "MissionLocationID", "", true);
$dg->addColumn("", "state_id", "", true);
$dg->addColumn("", "city_id", "", true);
$dg->addColumn("", "VehicleTitle", "", true);
$dg->addColumn("", "TypeTitle", "", true);
$dg->addColumn("", "StatusTitle", "", true);
$dg->addColumn("", "ControllerPerson", "", true);

$col = $dg->addColumn("شماره پیگیری", "RequestID", "");
$col->sortable = true;
$col->width = 70;

$col = $dg->addColumn("تاریخ درخواست", "RequestTime");
$col->sortable = true;
$col->width = 70;

$col = $dg->addColumn(" نوع ", "type");
$col->sortable = true;
$col->width = 50;
$col->renderer = "function(v,p,r){return r.data.TypeTitle;}";

$col = $dg->addColumn("استان ", "sname");
$col->sortable = true;
$col->width = 100;

$col = $dg->addColumn("شهر ", "cname");
$col->sortable = true;
$col->width = 100;

$col = $dg->addColumn(" موضوع ", "subject");
$col->sortable = true;
$col->width = 200;

$col = $dg->addColumn("  از تاریخ", "FromDate");
$col->sortable = true;
$col->width = 70;

$col = $dg->addColumn("  لغایت", "ToDate");
$col->sortable = true;
$col->width = 70;

$col = $dg->addColumn("  وسایل مورد نیاز", "stuff");
$col->sortable = true;
$col->width = 150;

$col = $dg->addColumn(" نوع وسیله رفت و برگشت ", "vehicle");
$col->sortable = true;
$col->width = 100;
$col->renderer = "function(v,p,r){return r.data.VehicleTitle;}";

$col = $dg->addColumn(" وضعیت ", "LastStatus");
$col->renderer = "function(v,p,r){return MissionStatusRender(v,p,r);}";
$col->sortable = true;
$col->width = 100;

$col = $dg->addColumn("", "");
$col->sortable = true;
$col->width = 30;
$col->renderer = "OperationMenuRender";

$dg->addButton("", " ایجاد درخواست جدید", "add", "function(){AddRequest();}");

$dg->title = "لیست  درخواست های ماموریت";

$dg->height = 300;
$dg->width = 1130;
$dg->pageSize = 10;
$grid = $dg->makeGrid_returnObjects();
?>
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
    <div id='FormDIV'> </div>
    </br>
    <div id='gridDIV'> </div>
</center>

<script type="text/javascript">
    var grid = <?php echo $grid; ?>;  
    grid.render(document.getElementById('gridDIV'));   
    ReqPanel.render(document.getElementById('FormDIV'));
</script>

</body>
</html>