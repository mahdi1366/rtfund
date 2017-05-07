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
	
	function PrintDocRender($row, $val){
		
		return "<a target=_blank href='../docs/print_doc.php?DocID=" . $row["DocID"] . "'>" . $val . "</a>";
	}
	
	$rpg->addColumn("شماره سند", "LocalNo", "PrintDocRender");
	//$rpg->addColumn("کد حساب", "CostCode");
	$rpg->addColumn("شرح حساب", "CostDesc");
	$rpg->addColumn("تفصیلی", "TafsiliDesc");
	$rpg->addColumn("تاریخ سند", "DocDate","dateRender");
	$rpg->addColumn("شرح", "detail");	
	
	function MakeWhere(&$where, &$whereParam , $ForRemain = false){
		
		if(isset($_REQUEST["taraz"])){
			
			/*if(!isset($_REQUEST["IncludeStart"]))
				$where .= " AND d.DocType != " . DOCTYPE_STARTCYCLE;*/

			if(!isset($_REQUEST["IncludeEnd"]))
				$where .= " AND d.DocType != " . DOCTYPE_ENDCYCLE;
			
			$where .= " AND d.CycleID=:c" ;
				$whereParam[":c"] = $_SESSION["accounting"]["CycleID"];
		}
		if(!empty($_REQUEST["CycleID"]))
		{
			$where .= " d.CycleID=:c" ;
			$whereParam[":c"] = $_REQUEST["CycleID"];
		}
		 
		if(!isset($_REQUEST["IncludeRaw"]))
			$where .= " AND d.DocStatus != 'RAW' ";
		
		if(!empty($_REQUEST["BranchID"]))
		{
			$where .= " AND BranchID=:b";
			$whereParam[":b"] = $_REQUEST["BranchID"];
		}	
		if(!empty($_REQUEST["GroupID"]))
		{
			$where .= " AND b1.GroupID = :gid";
			$whereParam[":gid"] = $_REQUEST["GroupID"];
		}
		
		if(!empty($_REQUEST["level1"]))
		{
			$where .= " AND b1.BlockID = :bf1";
			$whereParam[":bf1"] = $_REQUEST["level1"];
		}
		if(!empty($_REQUEST["level2"]))
		{
			$where .= " AND b2.BlockID = :bf2";
			$whereParam[":bf2"] = $_REQUEST["level2"];
		}
		if(!empty($_REQUEST["level3"]))
		{
			$where .= " AND b3.BlockID = :bf3";
			$whereParam[":bf3"] = $_REQUEST["level3"];
		}
		if(!empty($_REQUEST["level4"]))
		{
			$where .= " AND b4.BlockID = :bf4";
			$whereParam[":bf4"] = $_REQUEST["level4"];
		}
		if(isset($_REQUEST["taraz"]) && isset($_REQUEST["TafsiliID"]))
		{
			if($_REQUEST["TafsiliID"] == "")
				$where .= " AND (di.TafsiliID=0 OR di.TafsiliID is null)";
			else
			{
				$where .= " AND (di.TafsiliID = :tid or di.TafsiliID2 = :tid) ";
				$whereParam[":tid"] = $_REQUEST["TafsiliID"];
			}
		}
		if(!empty($_REQUEST["TafsiliID"]))
		{
			$where .= " AND (di.TafsiliID = :tid or di.TafsiliID2 = :tid)";
			$whereParam[":tid"] = $_REQUEST["TafsiliID"];
		}
		if(!empty($_REQUEST["TafsiliType"]))
		{
			$where .= " AND (di.TafsiliType = :tt or di.TafsiliType2 = :tt)";
			$whereParam[":tt"] = $_REQUEST["TafsiliType"];
		}
		if(isset($_REQUEST["TafsiliID2"]))
		{
			if($_REQUEST["TafsiliID2"] == "")
			{
				if(isset($_REQUEST["taraz"]))
					$where .= " AND (di.TafsiliID2=0 OR di.TafsiliID2 is null)";
			}
			else
			{
				$where .= " AND di.TafsiliID2 = :tid ";
				$whereParam[":tid"] = $_REQUEST["TafsiliID2"];
			}
		}
		if(!empty($_REQUEST["TafsiliType2"]))
		{
			$where .= " AND di.TafsiliType2 = :tt ";
			$whereParam[":tt"] = $_REQUEST["TafsiliType2"];
		}
		if(!empty($_REQUEST["fromLocalNo"]))
		{
			$where .= " AND d.LocalNo >= :lo1 ";
			$whereParam[":lo1"] = $_REQUEST["fromLocalNo"];
		}
		if(!empty($_REQUEST["toLocalNo"]))
		{
			$where .= " AND d.LocalNo <= :lo2 ";
			$whereParam[":lo2"] = $_REQUEST["toLocalNo"];
		}
		if(!$ForRemain && !empty($_REQUEST["fromDate"]))
		{
			$where .= " AND d.docDate >= :q1 ";
			$whereParam[":q1"] = DateModules::shamsi_to_miladi($_REQUEST["fromDate"], "-");
		}
		if(!$ForRemain && !empty($_REQUEST["toDate"]))
		{
			$where .= " AND d.docDate <= :q2 ";
			$whereParam[":q2"] = DateModules::shamsi_to_miladi($_REQUEST["toDate"], "-");
		}
		if(!empty($_REQUEST["description"]))
		{
			$where .= " AND d.description like :des ";
			$whereParam[":des"] = "%" . $_REQUEST["description"] . "%";
		}
		if(!empty($_REQUEST["details"]))
		{
			$where .= " AND di.details like :det ";
			$whereParam[":det"] = "%" . $_REQUEST["details"] . "%";
		}
	}	
	
	//.....................................
	$query = "select d.*,di.DebtorAmount,CreditorAmount,
		concat_ws(' - ',di.details,d.description) detail,
		concat_ws(' - ' , b1.BlockCode,b2.BlockCode,b3.BlockCode,b4.BlockCode) CostCode,
		concat_ws(' - ' , b1.BlockDesc,b2.BlockDesc,b3.BlockDesc,b4.BlockDesc) CostDesc,
		b.InfoDesc TafsiliTypeDesc,
		concat_ws(' - ',t.TafsiliDesc,t2.TafsiliDesc ) TafsiliDesc,
		bi2.InfoDesc TafsiliTypeDesc2
		
		from ACC_DocItems di join ACC_docs d using(DocID)
			join ACC_CostCodes cc using(CostID)
			join ACC_blocks b1 on(level1=b1.BlockID)
			left join ACC_blocks b2 on(level2=b2.BlockID)
			left join ACC_blocks b3 on(level3=b3.BlockID)
			left join ACC_blocks b4 on(level4=b4.BlockID)
			left join BaseInfo b on(TypeID=2 AND di.TafsiliType=InfoID)
			left join ACC_tafsilis t using(TafsiliID)
			left join BaseInfo bi2 on(bi2.TypeID=2 AND di.TafsiliType2=bi2.InfoID)
			left join ACC_tafsilis t2 on(di.TafsiliID2=t2.TafsiliID)
		where 1=1 ";
	
	$where = "";
	$whereParam = array();
		
	MakeWhere($where, $whereParam);
	$query .= $where;
	
	$query .= " order by d.DocDate";	
	$dataTable = PdoDataAccess::runquery($query, $whereParam);
	print_r(ExceptionHandler::PopAllExceptions());
	//-------------------------- previous remaindar ----------------------------
	$BeforeRemaindar = "";
	$BeforeAmount = 0;
	if(!empty($_REQUEST["fromDate"]))
	{
		$query = "select sum(CreditorAmount-di.DebtorAmount)

			from ACC_DocItems di join ACC_docs d using(DocID)
				join ACC_CostCodes cc using(CostID)
				join ACC_blocks b1 on(level1=b1.BlockID)
				left join ACC_blocks b2 on(level2=b2.BlockID)
				left join ACC_blocks b3 on(level3=b3.BlockID)
				left join ACC_blocks b4 on(level4=b4.BlockID)
				left join BaseInfo b on(TypeID=2 AND di.TafsiliType=InfoID)
				left join ACC_tafsilis t using(TafsiliID)
				left join BaseInfo bi2 on(bi2.TypeID=2 AND di.TafsiliType2=bi2.InfoID)
				left join ACC_tafsilis t2 on(di.TafsiliID2=t2.TafsiliID)
			where d.CycleID=" . $_SESSION["accounting"]["CycleID"] . " AND 
				d.DocDate < :fd";
		
		$where = "";
		$whereParam = array(":fd" => DateModules::shamsi_to_miladi($_REQUEST["fromDate"], "-"));

		MakeWhere($where, $whereParam, true);
		$query .= $where;

		$DT = PdoDataAccess::runquery($query, $whereParam);
		$BeforeAmount = $DT[0][0];
		global $BeforeRemaindar;
		$BeforeRemaindar = "<div align=left style='font-family:nazanin;font-size:18px;font-weight:bold;".
				"padding:4px;border:1px solid black'>مانده از قبل : " . 
				number_format($DT[0][0]) . "</div>";
	}
	//--------------------------------------------------------------------------
	
	function moneyRender($row, $val) {
		return  number_format($val);
	}
	
	$col = $rpg->addColumn("مبلغ بدهکار", "DebtorAmount", "moneyRender");
	$col->EnableSummary();
	$col = $rpg->addColumn("مبلغ بستانکار", "CreditorAmount", "moneyRender");
	$col->EnableSummary();
	
	function bdremainRender($row){
		$v = $row["DebtorAmount"] - $row["CreditorAmount"];
		return $v < 0 ? 0 : number_format($v);
	}
	
	function bsremainRender($row){
		$v = $row["CreditorAmount"] - $row["DebtorAmount"];
		return $v < 0 ? 0 : number_format($v);
	}
	
	function TotalRemainRender(&$row, $value, $BeforeAmount, $prevRow){
		
		if(!$prevRow)
			$row["Sum"] = $BeforeAmount + $row["CreditorAmount"] - $row["DebtorAmount"];
		else
			$row["Sum"] = $prevRow["Sum"] + $row["CreditorAmount"] - $row["DebtorAmount"];
		
		return "<div style=direction:ltr>" . number_format($row["Sum"]) . "</div>";;
	}
	
	$col = $rpg->addColumn("مانده حساب", "CreditorAmount", "TotalRemainRender", $BeforeAmount);
	//$col->EnableSummary(true);
	
	$rpg->mysql_resource = $dataTable;
	$rpg->page_size = 10;
	$rpg->paging = true;
	
	if(!$rpg->excel)
	{
		BeginReport();
		$rpg->headerContent = 
		"<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
					گزارش گردش حساب ها 
					 <br> ".
				 $_SESSION["accounting"]["BranchName"]. "<br>" . "دوره سال " .
				$_SESSION["accounting"]["CycleID"] .
				"</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			$rpg->headerContent .= "<br>گزارش از تاریخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
		$rpg->headerContent .= "</td></tr></table>";
	}

	$rpg->SubHeaderFunction = "RemainRender";
function RemainRender($PageNo)
{
global $BeforeRemaindar;
if($PageNo == 1)
echo $BeforeRemaindar;
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
			labelWidth :100,
			width : 270
		},
		width : 600,
		items :[{
			xtype : "combo",
			colspan : 2,
			width : 400,
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: "/accounting/global/domain.data.php?task=GetAccessBranches",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['BranchID','BranchName'],
				autoLoad : true					
			}),
			fieldLabel : "شعبه",
			queryMode : 'local',
			value : "<?= !isset($_SESSION["accounting"]["BranchID"]) ? "" : $_SESSION["accounting"]["BranchID"] ?>",
			displayField : "BranchName",
			valueField : "BranchID",
			hiddenName : "BranchID"
		},{
			xtype : "combo",
			colspan : 2,
			width : 400,
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: "/accounting/global/domain.data.php?task=SelectCycles",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['CycleID','CycleDesc'],
				autoLoad : true					
			}),
			fieldLabel : "دوره",
			queryMode : 'local',
			value : "<?= !isset($_SESSION["accounting"]["CycleID"]) ? "" : $_SESSION["accounting"]["CycleID"] ?>",
			displayField : "CycleDesc",
			valueField : "CycleID",
			hiddenName : "CycleID"
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "گروه حساب",
			valueField : "BlockID",
			itemId : "cmp_level0",
			hiddenName : "GroupID",
			queryMode : 'local',
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=0',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "کل",
			valueField : "BlockID",
			itemId : "cmp_level1",
			hiddenName : "level1",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=1',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true,
				PageSize : 20
			}),
			tpl: this.blockTpl,
			PageSize : 20,
			listeners : {
				select : function(combo,records){
					AccReport_flowObj.formPanel.down("[hiddenName=level2]").getStore().load({
						params : {
							PreLevel : records[0].data.BlockID
						}
					});
				}
			}
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "معین",
			valueField : "BlockID",
			itemId : "cmp_level2",
			queryMode : "local",
			hiddenName : "level2",
			store : new Ext.data.Store({
				fields:["BlockID","BlockCode","BlockDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectBlocks&level=2',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				autoLoad : true
			}),
			tpl: this.blockTpl,
			listeners : {
				select : function(combo,records){
					AccReport_flowObj.formPanel.down("[hiddenName=level3]").getStore().load({
						params : {
							PreLevel : records[0].data.BlockID
						}
					});
				}
			}
		},{
			xtype : "combo",
			displayField : "BlockDesc",
			fieldLabel : "جزء معین",
			valueField : "BlockID",
			itemId : "cmp_level3",
			queryMode : "local",
			hiddenName : "level3",
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
			queryMode : 'local',
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
					el.enable();
					el.getStore().proxy.extraParams["TafsiliType"] = this.getValue();
					el.getStore().load();
				}
			}
		},{
			xtype : "combo",
			displayField : "TafsiliDesc",
			fieldLabel : "تفصیلی",
			disabled : true,
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
		},/*{
			xtype : "combo",
			displayField : "InfoDesc",
			fieldLabel : "گروه تفصیلی2",
			valueField : "InfoID",
			hiddenName : "TafsiliGroup2",
			queryMode : 'local',
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
					el = AccReport_flowObj.formPanel.down("[itemId=cmp_tafsiliID2]");
					el.setValue();
					el.enable();
					el.getStore().proxy.extraParams["TafsiliType"] = this.getValue();
					el.getStore().load();
				}
			}
		},{
			xtype : "combo",
			displayField : "TafsiliDesc",
			fieldLabel : "تفصیلی",
			disabled : true,
			valueField : "TafsiliID",
			itemId : "cmp_tafsiliID2",
			hiddenName : "TafsiliID2",
			store : new Ext.data.Store({
				fields:["TafsiliID","TafsiliDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			})
		},*/{
			xtype : "numberfield",
			hideTrigger : true,
			name : "fromLocalNo",
			fieldLabel : "از سند شماره"
		},{
			xtype : "numberfield",
			hideTrigger : true,
			name : "toLocalNo",
			fieldLabel : "تا سند شماره"
		},{
			xtype : "shdatefield",
			name : "fromDate",
			fieldLabel : "تاریخ سند از"
		},{
			xtype : "shdatefield",
			name : "toDate",
			fieldLabel : "تا تاریخ"
		},{
			xtype : "textfield",
			name : "description",
			fieldLabel : "شرح سند"
		},{
			xtype : "textfield",
			name : "details",
			fieldLabel : "جزئیات ردیف"
		},{
			xtype : "container",
			colspan : 2,
			html : "<input type=checkbox name=IncludeRaw> گزارش شامل اسناد پیش نویس نیز باشد"
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
