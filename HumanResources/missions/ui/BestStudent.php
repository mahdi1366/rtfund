<?php
//-----------------------------
//	Programmer	: B.Mahdipour
//	Date		: 94.05
//-----------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;
ini_set("display_errors", "On"); 

require_once '../js/BestStudent.js.php';

$dg = new sadaf_datagrid("dg", "StaffUtility/data/BestChildren.data.php?task=GetStu", "chgridDIV");
//$dg->addColumn("", "BSID", "", true);
$dg->addColumn("", "grade", "", true);
$dg->addColumn("", "sex", "", true);
$dg->addColumn("", "EducLevel", "", true);
$dg->addColumn("", "EducBase", "", true);
$dg->addColumn("", "PicFileType", "", true);
$dg->addColumn("", "PaperFileType", "", true);

$col = $dg->addColumn("", "FullChildName", "", true);
$col->renderer = "function(v){ return ' '; }" ;
$col = $dg->addColumn("", "sexTitle", "", true);
$col->renderer = "function(v){ return ' '; }" ;
$col = $dg->addColumn("", "EducLevelTitle", "", true);
$col->renderer = "function(v){ return ' '; }" ;
$col = $dg->addColumn("", "EducBaseTitle", "", true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("", "ptype", "", true);
$col->renderer = "function(v){ return ' '; }" ;
$col = $dg->addColumn("", "FullUnitTitle", "", true);
$col->renderer = "function(v){ return ' '; }" ;
$col = $dg->addColumn("", "mobile_phone", "", true);
$col->renderer = "function(v){ return ' '; }" ;
$col = $dg->addColumn("", "comments", "", true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("", "FullName", "string");
$col->renderer = "function(v,p,r){return r.data.FullName }" ;

$col = $dg->addColumn("", "CFName", "string",true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("", "CLName", "string",true);
$col->renderer = "function(v){ return ' '; }" ;

$col = $dg->addColumn("نام و نام خانوادگی", "BSID", "int");
$col->renderer = "function(v,p,r){return r.data.CFName + '  ' + r.data.CLName }";

$col = $dg->addColumn("تاریخ درخواست", "RegDate");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 120;

$col = $dg->addColumn(" وضعیت ", "status");
$col->renderer = "function(v,p,r){return ItemStatus(v,p,r);}";
$col->width = 80;

$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return BestStu.opRender(v,p,r);}";
$col->width = 60;

$dg->EnableGrouping = true;
$dg->DefaultGroupField = "FullName";

$dg->title = "درخواست های ارسال شده";
$dg->EnableSearch = false ;
$dg->width = 680;
$dg->autoExpandColumn = "BSID" ; 
$dg->pageSize = 10;
$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">
 	BestStu.prototype.afterLoad = function()
{
   this.grid = <?=$grid?>;       
   this.grid.render("chgridDIV"); 
}
 var BestStuObject = new BestStu();
</script>

<form id="MainChildForm">
    <center>        
        <div id="ChildInfoPanel">			
		</div>			
    </center>    
</form>
<center><br><br>
<div id="chgridDIV"></div>
</center>  

</body>
</html>