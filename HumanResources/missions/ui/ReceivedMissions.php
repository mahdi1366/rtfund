<?php
ini_set("display_errors", "Off");

require_once '../../header.inc.php';
require_once inc_dataGrid;
$Acc_Address = $js_prefix_address . "../data/ReceivedMissions.data.php";

require_once '../js/ReceivedMissions.js.php';
?>
<script>
    
</script>
<?
$dg = new sadaf_datagrid("MissionsGrid", $js_prefix_address . "../data/ReceivedMissions.data.php?task=GetAllFinalized", "MissDIV");

$col = $dg->addColumn(" کد", "RequestID");
$col->width = 50;
$col->align = 'center';

$col = $dg->addColumn("زمان درخواست", "RequestTime");
$col->width = 120;

$col = $dg->addColumn("درخواست دهنده", "person");


$col = $dg->addColumn("موضوع", "subject");
$col->width = 150;

$col = $dg->addColumn("از تاریخ ", "FromDate");
$col->width = 120;

$col = $dg->addColumn("تا تاریخ", "ToDate");
$col->width = 120;

$col = $dg->addColumn(" محل ماموریت", "MissionLocation");
$col->width = 100;

/*$col = $dg->addColumn("گزارش", "", "");
$col->width = 45;
$col->renderer = "ShowReportRender";*/

/*
$col = $dg->addColumn(" ضریب منطقه", "AreaCoef");
$col->width = 80;
$col->renderer = "function(value,p,r){return ReceivedMissionsObject.ZaribTextBoxRender(value,p,r);}"; 
 */ 

$dg->addButton("", "ماموریت های تائید شده ", "list", "function(){ReceivedMissionsObject.ACCItem();}");

/*$col = $dg->addColumn("تایید", "");
$col->width = 30;
$col->renderer = "ReceivedMissionsObject.AcceptRender";*/

$col = $dg->addColumn("عملیات", "");
$col->sortable = true;
$col->width = 50;
$col->renderer = "OperationMenuRender";

$dg->pageSize = "40";
$dg->EnableSearch = false;
$dg->width =900;
$dg->title = 'ماموریت های رسیده';
$dg->autoExpandColumn = "person";
$grid = $dg->makeGrid_returnObjects();
//*******************************************************************************
$dg = new sadaf_datagrid("MissionsGrid", $js_prefix_address . "../data/ReceivedMissions.data.php?task=GetAllAdmitted", "MissDIV");

$col = $dg->addColumn(" کد", "RequestID");
$col->width = 50;
$col->align = 'center';

$col = $dg->addColumn("زمان درخواست", "RequestTime");
$col->width = 120;

$col = $dg->addColumn("درخواست دهنده", "person");


$col = $dg->addColumn("موضوع", "subject");
$col->width = 150;

$col = $dg->addColumn("از تاریخ ", "FromDate");
$col->width = 120;

$col = $dg->addColumn("تا تاریخ", "ToDate");
$col->width = 120;

$col = $dg->addColumn(" محل ماموریت", "MissionLocation");
$col->width = 100;

$dg->addButton("", "ماموریت های رسیده ", "list", "function(){ReceivedMissionsObject.RecieveItem();}");

$col = $dg->addColumn("عملیات", "");
$col->width = 60;
$col->renderer = "ReceivedMissionsObject.ReturnRender";

$dg->pageSize = "40";
$dg->EnableSearch = false;
$dg->width = 900;
$dg->title = 'ماموریت های تایید شده';
$dg->autoExpandColumn = "person";
$grid_admitteds = $dg->makeGrid_returnObjects();

//*******************************************************************************
?>

<center>    
<br><br>
    <div id="ShowTypePanelDIV"></div>
    <div id="MissDIV"></div>
    <div id="AddMissDIV"></div>
</center>

<script>
    ReceivedMissionsObject.grid = <?php echo $grid; ?>; 
    ReceivedMissionsObject.grid_admitted = <?php echo $grid_admitteds; ?>;
    
    ReceivedMissionsObject.grid.render(ReceivedMissionsObject.get('MissDIV'));     
   
    ReceivedMissionsObject.ShowTypePanel.render(ReceivedMissionsObject.get('ShowTypePanelDIV'));    
   // ReceivedMissionsObject.ShowTypePanel.down("[itemId=ShowType]").setValue(ReceivedMissionsObject.ShowTypes.getAt('0').getData());
</script>
