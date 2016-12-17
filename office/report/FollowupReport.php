<?php
//-----------------------------
//	Programmer	: Kadkhoda
//	Date		: 1395.10
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;
require_once inc_dataReader;
require_once inc_response;

if(!empty($_REQUEST["task"]))
	$_REQUEST["task"]();

function SelectAllLetter(){
	
	$query = "select s.*,l.*, 
			concat_ws(' ',fname, lname,CompanyName) ToPersonName			

		from OFC_send s
			join OFC_letters l using(LetterID)
			join BSC_persons p on(s.ToPersonID=p.PersonID)		
		where s.FromPersonID=? AND FollowUpDate is not null ";
	$param = array( $_SESSION["USER"]["PersonID"]);
	
	if(!empty($_REQUEST["StartDate"]))
	{
		$query .= " AND FollowUpDate >= ?";
		$param[] = DateModules::shamsi_to_miladi($_REQUEST["StartDate"], "-");
	}
	if(!empty($_REQUEST["EndDate"]))
	{
		$query .= " AND FollowUpDate <= ?";
		$param[] = DateModules::shamsi_to_miladi($_REQUEST["EndDate"], "-");
	}

	$query .= " group by SendID " . dataReader::makeOrder();
	$dt = PdoDataAccess::runquery_fetchMode($query, $param);
	print_r(ExceptionHandler::PopAllExceptions());
	$cnt = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($dt, $cnt, $_GET["callback"]);
	die();
}

$dg = new sadaf_datagrid("dg", $js_prefix_address . "FollowupReport.php?task=SelectAllLetter", "grid_div");

$dg->addColumn("", "LetterID", "", true);
$dg->addColumn("", "SendComment", "", true);

$col = $dg->addColumn("<img src=/office/icons/LetterType.gif>", "LetterType", "");
$col->renderer = "FollowupReport.LetterTypeRender";
$col->width = 30;

$col = $dg->addColumn("<img src=/office/icons/attach.gif>", "hasAttach", "");
$col->renderer = "function(v,p,r){if(v == 'YES') return '<img src=/office/icons/attach.gif>';}";
$col->width = 30;

$col = $dg->addColumn("شماره", "LetterID", "");
$col->width = 60;
$col->align = "center";

$col = $dg->addColumn("موضوع نامه", "LetterTitle", "");

$col = $dg->addColumn("گیرنده", "ToPersonName", "");
$col->width = 150;

$col = $dg->addColumn("شرح ارجاع", "SendComment");
$col->ellipsis = 50;
$col->width = 200;

$col = $dg->addColumn("تاریخ ارجاع", "SendDate", GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("تاریخ پیگیری", "FollowUpDate", GridColumn::ColumnType_date);
$col->width = 80;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 380;
$dg->width = 800;
$dg->title = "نامه های دارای پیگیری";
$dg->DefaultSortField = "SendDate";
$dg->EnableSearch = false;
$dg->autoExpandColumn = "LetterTitle";
$grid = $dg->makeGrid_returnObjects();
?>
<script>
	
FollowupReport.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function FollowupReport(){
	
	this.SearchPanel = new Ext.form.Panel({
		renderTo : this.get("MainPanel"),
		width : 800,
		frame : true,
		layout : {
			type : "table",
			columns : 3
		},
		defaults : {
			width : 260
		},
		title : "فیلتر لیست",
		items : [{
			xtype : "shdatefield",
			name : "StartDate",
			fieldLabel : "تاریخ پیگری از"
		},{
			xtype : "shdatefield",
			name : "EndDate",
			fieldLabel : "تا"
		}],
		buttons : [{
			text:'جستجو',
			iconCls: 'search',
			handler: function(){
				me = FollowupReportObject;
				if(me.grid.rendered)
					me.grid.getStore().loadPage(1);
				else
					me.grid.render(me.get("DivGrid"));
			}
		},{
			text : "پاک کردن فرم جستجو",
			iconCls : "clear",
			handler : function(){
				this.up('form').getForm().reset();
			}
		}]
	});
	this.SearchPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		
		FollowupReportObject.searching();
		e.preventDefault();
		e.stopEvent();
		return false;
	});
	
	this.grid = <?= $grid ?>;
	
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsSeen == "NO")
			return "yellowRow";
		return "";
	}		
	this.grid.on("itemdblclick", function(view, record){
			
		framework.OpenPage("/office/letter/LetterInfo.php", "مشخصات نامه", 
		{
			LetterID : record.data.LetterID,
			SendID : record.data.SendID,
			ForView : true
		});

	});
	
	this.grid.getStore().proxy.form = this.get("MainForm");
	//this.grid.render(this.get("DivGrid"));
}

FollowupReport.LetterTypeRender = function(v,p,r){
	
	if(v == 'INNER') 
		return "<img data-qtip='نامه داخلی' src=/office/icons/inner.gif>";
	if(v == 'INCOME') 
		return "<img data-qtip='نامه وارده' src=/office/icons/income.gif>";
	if(v == 'OUTCOME') 
		return "<img data-qtip='نامه صادره' src=/office/icons/outcome.gif>";
}

FollowupReportObject = new FollowupReport();

</script>
<center>
	<br>
	<form id="MainForm">
		<div id="MainPanel"></div>
	</form>
	<br>
	<div id="DivGrid"></div>
</center>