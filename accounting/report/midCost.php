<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	function MakeData(){
		
		$CostID = $_POST["CostID"];
		$TafsiliType = $_POST["TafsiliType"];
		$TafsiliID = $_POST["TafsiliID"];
		$BranchID = $_POST["BranchID"];
		$StartDate = $_POST["StartDate"];
		$EndDate = empty($_POST["EndDate"]) ? DateModules::Now() : $_POST["EndDate"];
		$CycleID = $_POST["CycleID"];
		//------------ get sum  ----------------
		$params = array(
				":cycle" => $CycleID,
				":tt" => $TafsiliType, 
				":tid" => $TafsiliID, 
				":cid" => $CostID, 
				":bid" => $BranchID);
		
		$query = "
			select DocDate,CostCode,TafsiliDesc,sum(CreditorAmount-DebtorAmount) amount
			from ACC_DocItems di
				join ACC_docs d using(DocID)
				join ACC_tafsilis t using(TafsiliType,TafsiliID)
				join ACC_CostCodes cc using(CostID)

			where ((di.TafsiliType=:tt AND TafsiliID =:tid) OR (di.TafsiliType2=:tt AND TafsiliID2 =:tid))
				AND di.CostID = :cid";
		if(!empty($CycleID))
		{
			$query .= " AND CycleID = :cycle";
			$params[":cycle"] = $CycleID;
		}
		if(!empty($BranchID))
		{
			$query .= " AND BranchID= :bid";
			$params[":bid"] = $BranchID;
		}
		if(!empty($StartDate))
		{
			$query .= " AND DocDate >= :sdate";
			$params[":sdate"] = DateModules::shamsi_to_miladi($StartDate, "-");
		}
		if(!empty($EndDate))
		{
			$query .= " AND DocDate <= :edate";
			$params[":edate"] = DateModules::shamsi_to_miladi($EndDate, "-");
		}
		
		$query .= " group by DocDate order by DocDate";
		
		$dt = PdoDataAccess::runquery($query, $params);
		//echo PdoDataAccess::GetLatestQueryString();
		
		if(count($dt) == 0)
		{
			$msg = "تفصیلی مورد نظر فاقد سندی با حساب فوق می باشد";
			echo $msg;
			die();
		}
		//------------ get the Deposite amount -------------
		$TraceArr = array();
		$remain = $dt[0]["amount"]*1;
		$totalDays = 0;
		$totalAmount = 0;
		$TraceArr[] = array(
			"TafsiliDesc" => $dt[0]["TafsiliDesc"],
			"CostCode" => $dt[0]["CostCode"],
			"Date" => $dt[0]["DocDate"],
			"amount" => $dt[0]["amount"],
			"remain" => $dt[0]["amount"],
			"days" => 0,
			"average" => 0
		);
		for($i=1; $i < count($dt); $i++)
		{
			$days = DateModules::GDateMinusGDate($dt[$i]["DocDate"],$dt[$i-1]["DocDate"]);
			$totalDays += $days;

			$totalAmount += $remain*$days;
			$remain += $dt[$i]["amount"];

			$TraceArr[count($TraceArr)-1]["days"] = $days;
			$TraceArr[count($TraceArr)-1]["average"] = $totalAmount / $totalDays;
			$TraceArr[] = array(
				"Date" => $dt[$i]["DocDate"],
				"amount" => $dt[$i]["amount"],
				"remain" => $remain,
				"days" => 0
			);
		}
		$days = DateModules::GDateMinusGDate($EndDate,$dt[$i-1]["DocDate"]);
		$totalAmount += $remain*$days;
		$totalDays += $days;
		$totalAmount = round($totalAmount / $totalDays);
		$TraceArr[count($TraceArr)-1]["days"] = $days;
		$TraceArr[count($TraceArr)-1]["average"] = $totalAmount;

		return $TraceArr;
	}
	
	$rpg = new ReportGenerator();
	$rpg->mysql_resource = MakeData();
	$rpg->excel = !empty($_POST["excel"]);

	function InRender($row){
		if($row["amount"]*1 < 0)
			return 0;
		return number_format($row["amount"]);
	}

	function OutRender($row){
		if($row["amount"]*1 > 0)
			return 0;
		return number_format($row["amount"]);
	}

	$rpg->addColumn("تاریخ", "Date", "ReportDateRender");
	$col = $rpg->addColumn("واریز", "amount", "InRender");
	$col = $rpg->addColumn("برداشت", "amount", "OutRender");
	$rpg->addColumn("مانده", "remain", "ReportMoneyRender");
	$col = $rpg->addColumn("تعداد روز", "days");
	$rpg->addColumn("میانگین", "average", "ReportMoneyRender");

	if(!$rpg->excel)
	{
		BeginReport();
		$rpg->headerContent = 
		"<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
					گزارش میانگین حساب
					 <br> کد حساب : " . $rpg->mysql_resource[0]["CostCode"] . 
					"<br>تفصیلی :  " . $rpg->mysql_resource[0]["TafsiliDesc"] .
				"</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			$rpg->headerContent .= "<br>گزارش از تاریخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
		$rpg->headerContent .= "</td></tr></table>";
	}
	
	$rpg->generateReport();
	die();
}
?>
<script>
AccReport_mid.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

AccReport_mid.prototype.showReport = function(btn, e)
{
	if(!this.formPanel.getForm().isValid())
		return;
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "midCost.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function AccReport_mid()
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
		title : "گزارش میانگین حساب",
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
			width : 540,
			fieldLabel : "کد حساب",
			colspan : 2,
			store: new Ext.data.Store({
				fields:["CostID","CostCode","CostDesc", "TafsiliType","TafsiliType2",{
					name : "fullDesc",
					convert : function(value,record){
						return "[ " + record.data.CostCode + " ] " + record.data.CostDesc
					}				
				}],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectCostCode',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			typeAhead: false,
			allowBlank : false,
			hiddenName : "CostID",
			valueField : "CostID",
			displayField : "fullDesc",
			listConfig: {
				loadingText: 'در حال جستجو...',
				emptyText: 'فاقد اطلاعات'
			}
		},{
			xtype : "combo",
			displayField : "InfoDesc",
			fieldLabel : "گروه تفصیلی",
			allowBlank : false,
			valueField : "InfoID",
			hiddenName : "TafsiliType",
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
					el = AccReport_midObj.formPanel.down("[itemId=cmp_tafsiliID]");
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
			allowBlank : false,
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
		},{
			xtype : "shdatefield",
			name : "StartDate",
			fieldLabel : "از تاریخ"
		},{
			xtype : "shdatefield",
			name : "EndDate",
			fieldLabel : "تا تاریخ"
		}],
		buttons : [{
			text : "گزارش ساز",
			iconCls : "db",
			handler : function(){ReportGenerator.ShowReportDB(
						AccReport_midObj, 
						<?= $_REQUEST["MenuID"] ?>,
						"mainForm",
						"formPanel"
						);}
		},'->',{
			text : "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls : "report"
		},{
			text : "خروجی excel",
			handler : Ext.bind(this.showReport,this),
			listeners : {
				click : function(){
					AccReport_midObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				AccReport_midObj.formPanel.getForm().reset();
				AccReport_midObj.get("mainForm").reset();
			}			
		}]
	});
}

AccReport_midObj = new AccReport_mid();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>
