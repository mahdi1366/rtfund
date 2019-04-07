<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	97.06
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once '../../staff/class/staff.class.php';
require_once '../class/staff_costcode.class.php';
require_once inc_dataGrid;
ini_set("display_errors" , "On"); 

$staffInfo = new manage_staff($_POST['Q0']);


$dg = new sadaf_datagrid("CostCodeHistory", $js_prefix_address . "../data/staff_tax.data.php?task=selectCostCodeHistory&PID=" . $_POST['Q0'], "CostCodeGRID");
$dg->addColumn("", "StaffID", "", true);
$dg->addColumn("", "SPID", "", true);
//$dg->addColumn("", "personid", "", true);
$dg->addColumn("", "CostCode", "", true);
$dg->addColumn("", "CostDesc", "", true);
$dg->addColumn("", "BranchName", "", true);

$col = $dg->addColumn("شماره شناسایی", "StaffID", "int");
$col->width = 90;

$col = $dg->addColumn("تاريخ شروع", "StartDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 100;

$col = $dg->addColumn("تاريخ پايان", "EndDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField(true);
$col->width = 100;

$col = $dg->addColumn("شعبه", "BranchID", GridColumn::ColumnType_string);
$col->renderer = "function(v,p,r){return r.data.BranchName}";
$col->editor = "this.branchCombo";
$col->width = 150; 

$col = $dg->addColumn("کد حساب", "CostID", GridColumn::ColumnType_string);
$col->renderer = "function(v,p,r){return '[' + r.data.CostCode + '] ' + r.data.CostDesc}";
$col->editor = "this.accountCombo";

$dg->width = 750;
$dg->height = 200;
$dg->autoExpandColumn = "CostID";
$dg->emptyTextOfHiddenColumns = true;
$dg->DefaultSortField = "StartDate";
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->title = "کدهای حساب پرداختی";

$col = $dg->addColumn("حذف", "", "string");
$col->renderer = " function(v,p,r){ return StaffCostCodeObject.opDelRender(v,p,r); }";
$col->width = 60;

$dg->addButton = true;
$dg->addHandler = "function(v,p,r){ return StaffCostCodeObject.AddIncludeHistory(v,p,r);}";

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(v,p,r){ return StaffCostCodeObject.SaveHistory(v,p,r);}";


$CostCodeGrid = $dg->makeGrid_returnObjects();

require_once '../js/staff_costcode.js.php';
?>
<script>
    StaffCostCode.prototype.afterLoad = function ()
    {
		this.branchCombo = new Ext.form.ComboBox({
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: '/framework/baseInfo/baseInfo.data.php?' +
						"task=SelectBranches",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['BranchID','BranchName'],
				autoLoad : true					
			}),
			queryMode : 'local',
			displayField : "BranchName",
			valueField : "BranchID"
		});
		this.accountCombo = new Ext.form.ComboBox({
			store: new Ext.data.Store({
				fields:["CostID","CostCode","CostDesc", "TafsiliType1","TafsiliType2",{
					name : "fullDesc",
					convert : function(value,record){
						return "[ " + record.data.CostCode + " ] " + record.data.CostDesc
					}				
				}],
				proxy: {
					type: 'jsonp',
					url: '/accounting/baseinfo/baseinfo.data.php?task=SelectCostCode',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			valueField : "CostID",
			displayField : "fullDesc"
		});
        this.PersonID = <?= $_POST["Q0"] ?>;
        this.IncludeCostGrid = <?= $CostCodeGrid ?>;
        this.IncludeCostGrid.render(this.parent.get("CostCodeGRID"));
        this.sid = <?= $staffInfo->staff_id ?>;

    }

    var StaffCostCodeObject = new StaffCostCode();

</script>

<div id="CostCodeGRID"></div>
