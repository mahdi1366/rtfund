<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	
	function dateRender($row, $val){
		return DateModules::miladi_to_shamsi($val);
	}	
	
	$rpg->addColumn("شماره سند", "LocalNo");
	$rpg->addColumn("گروه حساب", "level1Desc");
	$rpg->addColumn("حساب کل", "level2Desc");
	$rpg->addColumn("حساب معین", "level3Desc");
	$rpg->addColumn("گروه تفصیلی", "TafsiliTypeDesc");
	$rpg->addColumn("تفصیلی", "TafsiliDesc");
	$rpg->addColumn("تاریخ سند", "DocDate","dateRender");
	$rpg->addColumn("توضیحات", "description");	
	
	function MakeWhere(&$where, &$whereParam){
		
		if(!empty($_POST["from_level1"]) || !empty($_POST["to_Level1"]))
		{
			if(!empty($_POST["from_level1"]))
			{
				$where .= " AND b1.BlockCode >= :bf1";
				$whereParam[":bf1"] = $_POST["from_level1"];
			}
			if(!empty($_POST["to_level1"]))
			{
				$where .= " AND b1.BlockCode <= :bt1";
				$whereParam[":bt1"] = $_POST["to_level1"];
			}
		}

		if(!empty($_POST["from_level2"]) || !empty($_POST["to_Level2"]))
		{
			if(!empty($_POST["from_level2"]))
			{
				$where .= " AND b2.BlockCode >= :bf2";
				$whereParam[":bf2"] = $_POST["from_level2"];
			}
			if(!empty($_POST["to_level2"]))
			{
				$where .= " AND b2.BlockCode <= :bt2";
				$whereParam[":bt2"] = $_POST["to_level2"];
			}
		}
		if(!empty($_POST["from_level3"]) || !empty($_POST["to_Level3"]))
		{
			if(!empty($_POST["from_level3"]))
			{
				$where .= " AND b3.BlockCode >= :bf3";
				$whereParam[":bf3"] = $_POST["from_level3"];
			}
			if(!empty($_POST["to_level3"]))
			{
				$where .= " AND b3.BlockCode <= :bt3";
				$whereParam[":bt3"] = $_POST["to_level3"];
			}
		}
		if(!empty($_POST["from_tafsiliType"]) || !empty($_POST["to_tafsiliType"]))
		{
			if(!empty($_POST["from_TafsiliType"]))
			{
				$where .= " AND di.TafsiliType >= :ttf";
				$whereParam[":ttf"] = $_POST["from_TafsiliType"];
			}
			if(!empty($_POST["to_TafsiliType"]))
			{
				$where .= " AND di.TafsiliType <= :ttt";
				$whereParam[":ttt"] = $_POST["to_TafsiliType"];
			}
		}
		if(!empty($_POST["from_TafsiliID"]) || !empty($_POST["to_TafsiliID"]))
		{
			if(!empty($_POST["from_TafsiliID"]))
			{
				$where .= " AND di.TafsiliID >= :tf";
				$whereParam[":tf"] = $_POST["from_TafsiliID"];
			}
			if(!empty($_POST["to_TafsiliID"]))
			{
				$where .= " AND di.TafsiliID <= :tt";
				$whereParam[":tt"] = $_POST["to_TafsiliID"];
			}
		}

		if(!empty($_REQUEST["fromDate"]))
		{
			$where .= " AND d.docDate >= :q1 ";
			$whereParam[":q1"] = DateModules::shamsi_to_miladi($_REQUEST["fromDate"], "-");
		}
		if(!empty($_REQUEST["toDate"]))
		{
			$where .= " AND d.docDate <= :q2 ";
			$whereParam[":q2"] = DateModules::shamsi_to_miladi($_REQUEST["toDate"], "-");
		}

		//.....................................
		if(isset($_REQUEST["level1"]))
		{
			$where .= " AND cc.level1 = :l1";
			$whereParam[":l1"] = $_REQUEST["level1"];
		}
		if(isset($_REQUEST["level2"]))
		{
			$where .= " AND cc.level2 = :l2";
			$whereParam[":l2"] = $_REQUEST["level2"];
		}
		if(isset($_REQUEST["level3"]))
		{
			$where .= " AND cc.level3 = :l3";
			$whereParam[":l3"] = $_REQUEST["level3"];
		}
		if(!empty($_REQUEST["TafsiliGroup"]))
		{
			$where .= " AND di.TafsiliType = :ttype";
			$whereParam[":ttype"] = $_REQUEST["TafsiliGroup"];
		}
		if(!empty($_REQUEST["TafsiliID"]))
		{
			$where .= " AND di.TafsiliID = :taf";
			$whereParam[":taf"] = $_REQUEST["TafsiliID"];
		}
	}	
	
	//.....................................
	$query = "select d.*,di.DebtorAmount,CreditorAmount,
		concat('[ ' , b1.BlockCode , ' ] ', b1.BlockDesc) level1Desc,
		concat('[ ' , b2.BlockCode , ' ] ', b2.BlockDesc) level2Desc,
		concat('[ ' , b3.BlockCode , ' ] ', b3.BlockDesc) level3Desc,
		InfoDesc TafsiliTypeDesc,
		concat('[ ', t.TafsiliID, ' ] ',t.TafsiliDesc) TafsiliDesc
		
		from ACC_DocItems di join ACC_docs d using(DocID)
			join ACC_CostCodes cc using(CostID)
			join ACC_blocks b1 on(level1=b1.BlockID)
			left join ACC_blocks b2 on(level2=b2.BlockID)
			left join ACC_blocks b3 on(level3=b3.BlockID)
			left join BaseInfo b on(TypeID=2 AND di.TafsiliType=InfoID)
			left join ACC_tafsilis t using(TafsiliID)
	";
	$where = "";
	$whereParam = array();
	MakeWhere($where, $whereParam);
	$query .= " where d.CycleID=" . $_SESSION["accounting"]["CycleID"]
			. " AND BranchID=" . $_SESSION["accounting"]["BranchID"] . $where;

	$dataTable = PdoDataAccess::runquery($query, $whereParam);

	function moneyRender($row, $val) {
		return number_format($val);
	}
	
	$col = $rpg->addColumn("مبلغ بدهکار", "DebtorAmount", "moneyRender");
	$col->EnableSummary();
	$col = $rpg->addColumn("مبلغ بستانکار", "CreditorAmount", "moneyRender");
	$col->EnableSummary();
	
	$rpg->mysql_resource = $dataTable;
	if(!$rpg->excel)
	{
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<div style=display:none>" . PdoDataAccess::GetLatestQueryString() . "</div>";
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.png'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:b titr;font-size:15px'>
					گزارش گردش حساب ها
				</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			echo "<br>گزارش از تاریخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
		echo "</td></tr></table>";
		}
	$rpg->generateReport();
	die();
}
?>
<script>
AccReport_flow.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

AccReport_flow.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "flow.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function AccReport_flow()
{
	this.blockTpl = new Ext.XTemplate(
		'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
		,'<td>کد</td><td>عنوان</td>'
		,'<tpl for=".">'
		,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
		,'<td style="border-left:0;border-right:0" class="search-item">{BlockCode}</td>'
		,'<td style="border-left:0;border-right:0" class="search-item">{BlockDesc}</td>'
		,'</tpl>'
		,'</table>');
		
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش گردش حساب ها",
		defaults : {
			labelWidth :100
		},
		width : 600,
		items :[{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "گروه حساب از",
			valueField : "BlockCode",
			itemId : "cmp_from_level1",
			hiddenName : "from_level1",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=1',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "تا",
			valueField : "BlockCode",
			itemId : "cmp_to_level1",
			hiddenName : "to_level1",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=1',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "حساب کل از",
			valueField : "BlockCode",
			itemId : "cmp_from_level2",
			hiddenName : "from_level2",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=2',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "تا",
			valueField : "BlockCode",
			itemId : "cmp_to_level2",
			hiddenName : "to_level2",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=2',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "حساب معین از",
			valueField : "BlockCode",
			itemId : "cmp_from_level3",
			hiddenName : "from_level3",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=3',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "تا",
			valueField : "BlockCode",
			itemId : "cmp_to_level3",
			hiddenName : "to_level3",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=3',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "InfoDesc",
			fieldLabel : "گروه تفصیلی",
			valueField : "InfoID",
			hiddenName : "TafsiliGroup",
			store : new Ext.data.Store({
				fields:['InfoID','InfoDesc'],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectTafsiliGroups',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			listeners : {
				select : function(combo,records){
					el = AccReport_flowObj.formPanel.down("[itemId=cmp_tafsiliID]");
					el.setValue();
					el.getStore().proxy.extraParams["TafsiliType"] = this.getValue();
					el.getStore().load();
				}
			}
		},{
			xtype : "combo",
			displayField : "TafsiliDesc",
			fieldLabel : "تفصیلی",
			valueField : "TafsiliID",
			itemId : "cmp_tafsiliID",
			hiddenName : "TafsiliID",
			store : new Ext.data.Store({
				fields:["TafsiliID","TafsiliDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			})
		},{
			xtype : "shdatefield",
			name : "fromDate",
			fieldLabel : "از تاریخ"
		},{
			xtype : "shdatefield",
			name : "toDate",
			fieldLabel : "تا تاریخ"
		}],
		buttons : [{
			text : "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls : "report"
		},{
			text : "خروجی excel",
			handler : Ext.bind(this.showReport,this),
			listeners : {
				click : function(){
					AccReport_flowObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				AccReport_flowObj.formPanel.getForm().reset();
				AccReport_flowObj.get("mainForm").reset();
			}			
		}]
	});
}

AccReport_flowObj = new AccReport_flow();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>