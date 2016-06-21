<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.07.07
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once inc_dataGrid;

require_once '../js/course.js.php';

$dg = new sadaf_datagrid("cours",$js_prefix_address . "../data/person.data.php?task=selectCourse&Q0=".$_POST['Q0'],"coursGRID");

$col = $dg->addColumn("عنوان دوره", "title", "string");

$col = $dg->addColumn("از تاريخ", "from_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 80;

$col = $dg->addColumn("تا تاريخ", "to_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width =80;

$col = $dg->addColumn("نمره", "score", "int");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("تاریخ گواهینامه", "certficate_date", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width =80;

$col = $dg->addColumn("شماره گواهینامه", "register_no", "int");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("مرتبط؟", "related_Title", "string");
$col->width = 50;
$col->align = "center";

$col = $dg->addColumn("داخلي؟", "internal_Title", "string");
//$col->summaryRenderer = "function(){return 'جمع';}";
$col->width = 50;
$col->align = "center";

$col = $dg->addColumn("تعداد ساعت", "total_hours", GridColumn::ColumnType_int);
//$col->summaryType = GridColumn::SummeryType_sum;
$col->width = 80;
$col->align = "center";

$dg->width = 780;
$dg->pageSize = 10 ; 
$dg->DefaultSortField = "from_date";
$dg->DefaultSortDir = "ASC";
$dg->autoExpandColumn = "title";
$dg->EnableRowNumber = true;
$dg->EnableSummaryRow = true;

$grid = $dg->makeGrid_returnObjects();

?>
<script>
    
    PersonCourse.prototype.afterLoad = function()
    { 
        this.grid = <?= $grid?>;
        this.grid.render(this.get("coursGRID")); 
		//.............................
		
		this.grid.getStore().on("load", function(store){
		
				var r = store.getProxy().getReader().jsonData;
				PersonCourseObject.get("cmp_th").innerHTML = r.message; 				
						
	});
	
		//............................
    }

var PersonCourseObject = new PersonCourse();

</script>
<form id="courseForm">
<div id="coursGRID"></div>
<div id="totalHours">
	<div id="Sum_TBL"> 
		<table>
			<tr>
				<td  width="650px" colspan="2" >&nbsp;</td>
				<td  style="font-weight:bold;font-family: 'B nazanin';font-size: 14px;">
					جمع ساعات : 					 
					&nbsp;&nbsp;&nbsp;
				</td>				
				<td  ><div id="cmp_th" style="direction: 'rtl';"> </div></td>				
			</tr>
		</table>
	</div>
</div>

</form>