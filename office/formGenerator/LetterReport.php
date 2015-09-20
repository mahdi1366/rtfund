<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	87.04
//---------------------------
require_once 'header.php';
require_once inc_dataGrid;
?>
<script type="text/javascript" src="../formGenerator/Forms.js?1"></script>
<script type="text/javascript">
function operationRender(v,p,r)
{
	return "<div align='center' title='مشاهده فرم' onclick='operationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;"+
		"background-image:url(images/setting.gif);" +
		"cursor:pointer;width:100%;height:16'></div>";
}

function operationMenu(e)
{
	var record = dg_grid.selModel.getSelected();
	var op_menu = new Ext.menu.Menu();
	
	op_menu.add({text: 'مشاهده فرم',iconCls: 'info',handler : function(){reportshowInfo();} });
	op_menu.add({text: 'پیوست',iconCls: 'attach',handler : function(){Attaching("true");} });
	op_menu.add({text: 'سابقه',iconCls: 'history',handler : function(){showHistoryForm();} });
	
	op_menu.showAt([e.clientX-120, e.clientY+5]);
}
function reportshowInfo()
{
	var record = dg_grid.selModel.getSelected();
	
	OpenPage("../formGenerator/NewForm.php?LetterID=" + record.data.LetterID + "&FormID=" + 
			record.data.FormID + "&referenceID=" + record.data.referenceID + "&from=none&returnTo=../formGenerator/LetterReport");
}
function unloadFn()
{
	if(HistoryWin)
	{
		HistoryWin.destroy();
		HistoryWin = null;
	}
	if(AttachWin)
	{
		AttachWin.destroy();
		AttachWin = null;
	}
}
</script>
<?php
$dg = new sadaf_datagrid("dg","../formGenerator/wfm.data.php", "div_grid");
$dg->method = "POST";
$dg->baseParams = "task: 'AllLetters'";

$col = $dg->addColumn('کد فرم', "LetterID", "string");
$col->width = 30;

$col = $dg->addColumn('كد پیگیری', "pursuitCode", "string");
$col->width = 40;

$col = $dg->addColumn("نوع فرم","FormName","string");
$col->width = 50;

$col = $dg->addColumn("ایجاد کننده","fullname","string");
$col->width = 50;

$col = $dg->addColumn('تاریخ ایجاد', "regDate", "string");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 40;


$col = $dg->addColumn("عملیات","","");
$col->renderer = "operationRender";
$col->sortable = false;
$col->width = 10;

$dg->addColumn('', "FormID", "",true);
$dg->addColumn("","referenceID","",true);

$dg->height = 400;
$dg->width = 750;
$dg->autoExpandColumn = "LetterID";

$dg->DefaultSortField = "LetterID";
$dg->DefaultSortDir = "ASC";
$dg->makeGrid();
?>
<center>
<div id="div_grid" align="right"></div>
</center>
<!-- ----------------------------------------- -->
<div id="div_history" class="x-hidden"></div>
<!-- ----------------------------------------- -->
<div id="div_attach" class="x-hidden"></div>
<!-- ----------------------------------------- -->