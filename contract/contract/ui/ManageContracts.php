<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.10
//-----------------------------
ini_set("display_errors", "On");

require_once '../../header.inc.php';
require_once '../../global/CNTconfig.class.php';
require_once '../js/ManageContracts.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "../data/contract.data.php?task=SelectContracts", "div_dg");

$dg->addColumn("شماره قرارداد", "CntId");
$dg->addColumn("الگو", "TplTitle");
$dg->addColumn("", "RegPersonID", "", true);
$dg->addColumn("", "RegDate", "", true);
$dg->addColumn("", "TplId", "", true);
$dg->addColumn("توضیحات", "description");
$dg->addColumn("", "PenaltyAmount", "", true);
$col = $dg->addColumn("وضعیت", "StatusCode");
$col->renderer = "function(v,p,record){
        if(record.data.StatusCode == ".CNTconfig::ContractStatus_Raw.") return 'خام';
        if(record.data.StatusCode == ".CNTconfig::ContractStatus_Sent." ) return ' ارسال برای تایید';
        if(record.data.StatusCode == ". CNTconfig::ContractStatus_Confirmed .") return ' تایید واحد قراردادها';
        ;}";

$col = $dg->addColumn("", "TplId");
$col->renderer = "ManageContractsObj.OperationRender";
$col->width = 30;

$dg->title = "لیست قراردادها";
$dg->DefaultSortField = "RegDate";
$dg->DefaultSortDir = "desc";
$dg->autoExpandColumn = "description";
$dg->width = 780;
$dg->pageSize = 10;

$grid = $dg->makeGrid_returnObjects();
?>
<script>
    ManageContractsObj = new ManageContracts();
    ManageContractsObj.grid = <?= $grid ?>;
    ManageContractsObj.grid.render(ManageContractsObj.get("div_dg"));
</script>
<br>
<center>
    <div id="NewForm"></div>
    <div id="div_dg"></div>
</center>
