<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.02
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once inc_dataGrid;

require_once '../js/evaluation.js.php';

$dg = new sadaf_datagrid("eval",$js_prefix_address . "../data/person.data.php?task=selectEval&Q0=".$_POST['Q0'],"EvalGRID");

$col = $dg->addColumn("شماره", "list_id", "int");
$col->width = 50;

$col = $dg->addColumn("تاریخ", "list_date", "int");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 80;

$col = $dg->addColumn("واحد سازمانی", "ptitle", "string");

$col = $dg->addColumn("عوامل عملکردی", "functional_score", "int");
$col->width = 50;
$col->align = "center";

$col = $dg->addColumn(" رفتارشغلی", "job_behaviour_score", "int");
$col->width = 100;
$col->align = "center";

$col = $dg->addColumn(" رفتاراخلاقی", "social_behaviour_score", "int");
$col->width = 80;
$col->align = "center";

$col = $dg->addColumn("مجموع امتیازات", "scores_sum", "int");
$col->width = 80;
$col->align = "center";

$dg->width = 780;
$dg->autoExpandColumn = "ptitle";
$dg->EnableRowNumber = true ;
$dg->EnableSearch = false ; 
$grid = $dg->makeGrid_returnObjects();

?>
<script>    
    PersonEval.prototype.afterLoad = function()
    { 
        this.grid = <?= $grid?>;
        this.grid.render(this.parent.get("EvalGRID")); 
    }

var PersonEvalObject = new PersonEval();

</script>
<div id="EvalGRID"></div>
