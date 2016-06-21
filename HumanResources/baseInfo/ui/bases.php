<?php 
//---------------------------
// programmer:	Jafarkhani
// create Date:	91.04
//---------------------------

require_once '../../header.inc.php';
require_once inc_dataGrid;

$BaseTypesDT = PdoDataAccess::runquery("select * from Basic_Info where TypeID=43");

require_once '../js/bases.js.php';

$dgh = new sadaf_datagrid("deph",$js_prefix_address."../data/bases.data.php?task=selectBases","div_dg");

$dgh->addColumn("کد","RowID","",true);

$col = $dgh->addColumn("نام و نام خانوادگی", "fullName");
$col->editor = "BaseObject.personCombo";

$col = $dgh->addColumn("نوع پایه", "typeName");
$col->editor = "BaseObject.baseTypeCombo";
$col->width = 250;

$col = $dgh->addColumn("پایه", "BaseValue");
$col->editor = ColumnEditor::NumberField(false,"cmp_baseValue");
$col->width = 40;

$col = $dgh->addColumn("تاریخ ثبت", "RegDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dgh->addColumn("تاریخ اجرا", "ExecuteDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 80;

$col = $dgh->addColumn("ثبت", "BaseMode");
$col->renderer = "function(v){return v == 'USER' ? 'کاربر' : 'خودکار';}";
$col->width = 50;

$col = $dgh->addColumn("حذف", "", "");
$col->renderer = "Base.DeleteRender";
$col->width = 40;

$dgh->addButton = true;
$dgh->addHandler = "function(v,p,r){ return BaseObject.AddBase(v,p,r);}";

$dgh->addColumn("","BaseMode","",true);
$dgh->addColumn("","PersonID","",true);
$dgh->addColumn("","BaseStatus","",true);
$dgh->addColumn("","BaseType","",true);

$dgh->EnableGrouping = true;
$dgh->DefaultGroupField = "fullName";

$dgh->title = "خلاصه پایه ها";
$dgh->width = 780;
$dgh->height = 500;
$dgh->DefaultSortField = "RowID";
$dgh->DefaultSortDir = "ASC";
$dgh->autoExpandColumn = "fullName";
$dgh->EnableSearch = true;
$dgh->enableRowEdit = true ;
$dgh->rowEditOkHandler = "function(v,p,r){ return BaseObject.SaveBase(v,p,r);}";

$grid = $dgh->makeGrid_returnObjects();

?>
<style type="text/css">
.pinkRow, .pinkRow td,.pinkRow div{ background-color:#FFB8C9 !important;}
</style>
<script>
BaseObject.grid = <?= $grid?>;
BaseObject.grid.render(BaseObject.get("div_dg"));
BaseObject.grid.plugins[0].on("beforeedit",function(rowEditor,e){
	
	var record = BaseObject.grid.getStore().getAt(e.rowIdx);
	if(record.data.RowID != "")
		return false;
});
BaseObject.grid.getView().getRowClass = function(record)
{
	if(record.data.BaseStatus == "DELETED")
		return "pinkRow";
	return "";
}
</script>
<center>
	<div id="div_dg"></div>
</center>
