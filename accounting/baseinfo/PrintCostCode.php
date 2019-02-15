<?php
//--------------------------
// developer:	Jafarkhani
// Date:        94.06
//--------------------------
require_once '../header.inc.php';
require_once 'baseinfo.class.php';
require_once '../global/ManageReport.class.php';
require_once inc_reportGenerator;

Manage_Report::BeginReport();

echo "<br><br>";
$dataTable = ACC_CostCodes::SelectCost(" 1=1 order by Costcode");

$rpg = new ReportGenerator();

$rpg->addColumn("کد حساب", "CostCode");
$rpg->addColumn("گروه حساب", "LevelTitle0");
$rpg->addColumn("حساب کل", "LevelTitle1");
$rpg->addColumn("معین1", "LevelTitle2");
$rpg->addColumn("معین2", "LevelTitle3");

$rpg->mysql_resource = $dataTable;
$rpg->generateReport();
die();

?>
