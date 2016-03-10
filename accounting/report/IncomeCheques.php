<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.12
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

if(!empty($_REQUEST["task"]))
{
	$param = array();
	$query = "select i.* , concat_ws(' ',fname,lname,CompanyName) fullname,PartAmount,PartDate
		from LON_installments i 
		join Lon_ReqParts using(PartID)
		join LON_requests using(RequestID)
		join BSC_persons on(LoanPersonID=PersonID)
		
		where ChequeNo>0
	";
	$temp = PdoDataAccess::runquery_fetchMode($query, $param);
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

$dg = new sadaf_datagrid("dg", $js_prefix_address . "IncomeCheques.php?task=selectChecks", "grid_div");

$col = $dg->addColumn("", "InstallmentID", "");
$col->width = 90;

$col = $dg->addColumn("وام گیرنده", "fullname", "");

$col = $dg->addColumn("تاریخ وام", "ReqDate", GridColumn::ColumnType_date);
$col->width = 110;

$col = $dg->addColumn("مبلغ", "PartAmount", GridColumn::ColumnType_money);
$col->width = 100;

$col = $dg->addColumn("شماره چک", "ChequeNo");
$col->width = 100;

$col = $dg->addColumn("تاریخ چک", "InstallmentDate", GridColumn::ColumnType_date);
$col->width = 100;

$col = $dg->addColumn("بانک", "ChequeBank");
$col->width = 100;

$col = $dg->addColumn("بانک", "ChequeBranch");
$col->width = 100;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 600;
$dg->width = 770;
$dg->title = "چک های وصول نشده";
$dg->DefaultSortField = "ReqDate";
$dg->autoExpandColumn = "fullname";
$grid = $dg->makeGrid_returnObjects();

?>
<script>

IncomeCheque.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function IncomeCheque(){
	
	this.MenuObj = Ext.button.Button({
		text: 'مشاهده چک ها بر اساس',
		menu: {
			items: [			
				'<b class="menu-title">انتخاب شرایط</b>',
				{
					text: 'چک های سه روز آینده',
					checked: true,
					group: 'theme',
					checkHandler: function(item,checked){
						if(checked)
							IncomeChequeObject.FilterGrid(item);
					}
				}, {
					text: 'چک های وصول نشده',
					group: 'theme',
					checkHandler: function(item,checked){
						if(checked)
							IncomeChequeObject.FilterGrid(item);
					}
				}, {
					text: 'چک های وصول شده',
					group: 'theme',
					checkHandler: function(item,checked){
						if(checked)
							IncomeChequeObject.FilterGrid(item);
					}
				}, {
					text: 'چک برگشت شده',
					group: 'theme',
					checkHandler: function(item,checked){
						if(checked)
							IncomeChequeObject.FilterGrid(item);
					}
				}, {
					text: 'چک عودت شده',
					group: 'theme',
					checkHandler: function(item,checked){
						if(checked)
							IncomeChequeObject.FilterGrid(item);
					}
				}
			]
		}
		});
	
	this.grid = <?= $grid ?>;
	this.grid.render(this.get("DivGrid1"));
}

IncomeChequeObject = new IncomeCheque();

IncomeCheque.prototype.FilterGrid = function(item){
	alert(item);
	
}

</script>
<center><br>
	<div id="DivGrid1"></div>
</center>