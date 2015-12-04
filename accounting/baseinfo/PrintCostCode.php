<?php
//--------------------------
// developer:	Jafarkhani
// Date:        94.06
//--------------------------
require_once '../header.inc.php';
require_once 'baseinfo.class.php';
require_once '../global/Manage_Report.class.php';
require_once inc_reportGenerator;

Manage_Report::BeginReport();

echo "<br><br>";
$dataTable = ACC_CostCodes::SelectCost(" 1=1 order by Costcode");

$rpg = new ReportGenerator();

$rpg->addColumn("کد حساب", "CostCode");
$rpg->addColumn("گروه حساب", "LevelTitle1");
$rpg->addColumn("حساب کل", "LevelTitle2");
$rpg->addColumn("معین", "LevelTitle3");

$rpg->mysql_resource = $dataTable;
$rpg->generateReport();
die();

?>
