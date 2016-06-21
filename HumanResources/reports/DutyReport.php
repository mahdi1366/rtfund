<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	93.11
//---------------------------

require_once("../header.inc.php");
require_once inc_reportGenerator;

global $dutyArr ; 

if (isset($_REQUEST["task"]))
{
	switch($_REQUEST["task"])
	{
		case "ShowReport":
			ShowReport();	
			
		case "GetWorkTimeExcel":
			  GetWorkTimeExcel();
		
	}
	
}

function MakeWhere(&$where, &$param){
	
	$PersonTypes = array();
	$CostCenters = array();
	$EmpStates = array();
	
	if(!empty($_POST["staff_id"]))
	{
		$where .= " AND s.staff_id=:staffid";
		$param[":staffid"] = $_POST["staff_id"];
	}
	
	$keys = array_keys($_POST);
	for($i=0; $i < count($keys); $i++)
	{
		if(strpos($keys[$i],"PersonType_") !== false)
			$PersonTypes[] = str_replace ("PersonType_", "", $keys[$i]);
		
		if(strpos($keys[$i],"chkcostID_") !== false)
			$CostCenters[] = str_replace ("chkcostID_", "", $keys[$i]);
		
		if(strpos($keys[$i],"chkEmpState_") !== false)
			$EmpStates[] = str_replace ("chkEmpState_", "", $keys[$i]);
	}
	
	if(count($PersonTypes) > 0)
		$where .= " AND s.person_type in(" . implode(",", $PersonTypes) . ")";
	if(count($CostCenters) > 0)
		$where .= " AND s.last_cost_center_id in(" . implode(",", $CostCenters) . ")";
	
}

function PrepareData(){
	
	$where = "";
	$param = array();
	MakeWhere($where, $param);
		
	$MainRows = PdoDataAccess::runquery_fetchMode("select  s.staff_id ,p.pfname , p.plname ,
														   case  p.sex when 1 then 'مرد' when 2 then 'زن' end psex ,
														   g2j(p.birth_date) bdate , bi1.title emp_state , bi2.title emp_mode , o1.ptitle unitTitle ,
														   o2.ptitle subunitTitle,  bi3.title education_level ,
														   w.onduty_year , w.onduty_month , w.onduty_day

													  from staff s
																   inner join writs w
																				 on s.staff_id = w.staff_id and
																					s.last_writ_id = w.writ_id and
																					s.last_writ_ver = w.writ_ver

																   inner join  Basic_Info bi1
																				 on  bi1.TypeID = 3 and  bi1.InfoID = w.emp_state

																	inner join  Basic_Info bi2
																				 on  bi2.TypeID = 4 and  bi2.InfoID = w.emp_mode

																   inner join persons p on s.personID = p.personID

																   left join org_new_units o1
																						  on o1.ouid = s.unitcode

																   left join org_new_units o2
																						  on o2.ouid = s.ouid

																   inner join  Basic_Info bi3
																				 on  bi3.TypeID = 6 and  bi3.InfoID = w.education_level





												where (1=1) ".$where ,$param  );
								
	//echo PdoDataAccess::GetLatestQueryString(); die();
	$nowDate = DateModules::Now() ; 
	return $MainRows;
}

function DutyYearRender($row){

		unset($dutyArr) ; 
	   	$query = "select w.execute_date,
						 w.contract_start_date ,
						 w.contract_end_date ,
						 w.person_type ,
						 w.onduty_year ,
						 w.onduty_month ,
						 w.onduty_day ,
						 w.annual_effect
				from writs as w";
	  
		$query .= " where w.staff_id =". $row['staff_id'];

		$query .= " AND (w.history_only != " . HISTORY_ONLY . " OR w.history_only is null) AND w.execute_date <= '".DateModules::Now()."'
						 order by w.execute_date DESC,w.writ_id DESC,w.writ_ver DESC
						 limit 1";

		$temp = PdoDataAccess::runquery($query);

		if(count($temp) == 0)
			return array("year" => 0,"month" => 0,"day" => 0);

		$writ_rec = $temp[0];

		$temp_duration = 0;

		if(DateModules::CompareDate(DateModules::Now(), $writ_rec['execute_date'])>=0)
			$temp_duration = DateModules::GDateMinusGDate(DateModules::Now(), $writ_rec['execute_date']);

		if ($writ_rec['annual_effect'] == HALF_COMPUTED)
			$temp_duration *= 0.5;
		else if ($writ_rec['annual_effect'] == DOUBLE_COMPUTED)
			$temp_duration *= 2;
		else if ($writ_rec['annual_effect'] == NOT_COMPUTED)
			$temp_duration = 0;

		$prev_writ_duration = DateModules::ymd_to_days($writ_rec['onduty_year'],$writ_rec['onduty_month'], $writ_rec['onduty_day']);



		$duration =  $prev_writ_duration + $temp_duration ;

		$return = array();
		DateModules::day_to_ymd($duration , $return['year'], $return['month'], $return['day']);
		
		global $dutyArr ; 		
		$dutyArr[$row["staff_id"]]['year'] = $return['year'] ; 
		$dutyArr[$row["staff_id"]]['month'] = $return['month'] ; 
		$dutyArr[$row["staff_id"]]['day'] = $return['day'] ; 
		
		return $dutyArr[$row["staff_id"]]['year'];
	}
	
	function DutyMonthRender($row){
	        global $dutyArr ; 
		return $dutyArr[$row["staff_id"]]['month'];
	}
	
	function DutyDayRender($row){	 
                global $dutyArr ;	
		return $dutyArr[$row["staff_id"]]['day'];
	}

function ShowReport(){
		global $dutyArr ; 
	$rpt = new ReportGenerator();
	$rpt->mysql_resource = PrepareData();
	
	if(isset($_POST["excel"])){
		header("Content-type: application/ms-excel");
		header("Content-disposition: inline; filename=excel.xls");
	}

	?>
	<html>
	<head>
		<META http-equiv=Content-Type content="text/html; charset=UTF-8" >
	</head>
		<body dir=rtl>
			
	<?	
	
	
	
				
	$rpt->addColumn("شماره<br> شناسایی", "staff_id");
	$rpt->addColumn("نام خانوادگي", "plname");
	$rpt->addColumn("نام", "pfname");
	$rpt->addColumn("جنسیت", "psex");
	$rpt->addColumn("تاریخ تولد", "bdate");
	$rpt->addColumn("وضعیت استخدامی", "emp_state");
	$rpt->addColumn("حالت استخدامی", "emp_mode");
	$rpt->addColumn("محل خدمت اصلی", "unitTitle");
	$rpt->addColumn("محل خدمت فرعی", "subunitTitle");
	$rpt->addColumn("مقطع تحصیلی", "education_level");
	$rpt->addColumn("سال", "onduty_year", "DutyYearRender");
	$rpt->addColumn("ماه", "onduty_month", "DutyMonthRender");
	$rpt->addColumn("روز", "onduty_day", "DutyDayRender");
	
	$rpt->header_alignment = "center";
if(!isset($_POST["excel"])){
	$rpt->headerContent = "
		<table width=100% border=0 style='font-family:b nazanin;'>
			<tr>
				<td width=120px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align=center style='font-weight:bold'>گزارش سنوات کارکنان</td>
				<td width=120px>
					شماره : 
					<br>
					تاریخ : 
					" . DateModules::shNow() . "
				</td>
			</tr>
			
		</table>";
}
	$rpt->page_size = 30;
	$rpt->paging = true;
	$rpt->generateReport();
	die();
?>
	</body>		
</html>		
<?
}

function GetWorkTimeExcel(){
	global $dutyArr ; 
	$param = array();
		
	MakeWhere($where, $param);
			
	$rpt = new ReportGenerator();	
	$rpt->mysql_resource = PrepareData();
		
	$rpt->addColumn("شماره شناسایی", "staff_id");
	$rpt->addColumn("نام خانوادگي", "plname");
	$rpt->addColumn("نام", "pfname");
	$rpt->addColumn("جنسیت", "psex");
	$rpt->addColumn("تاریخ تولد", "bdate");
	$rpt->addColumn("وضعیت استخدامی", "emp_state");
	$rpt->addColumn("حالت استخدامی", "emp_mode");
	$rpt->addColumn("محل خدمت اصلی", "unitTitle");
	$rpt->addColumn("محل خدمت فرعی", "subunitTitle");
	$rpt->addColumn("مقطع تحصیلی", "education_level");
	$rpt->addColumn("سال", "onduty_year", "DutyYearRender");
	$rpt->addColumn("ماه", "onduty_month", "DutyMonthRender");
	$rpt->addColumn("روز", "onduty_day", "DutyDayRender");
	
	$rpt->excel = true;
	$rpt->generateReport();
	die(); 
}


?>

<script>
	PersonDuty.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",
	
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};

	function PersonDuty()
	{
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
		 
			title :"تنظیمات گزارش",
			fieldDefaults: {
				labelWidth: 50
			},
			layout: {
				type: 'table',
				columns: 3
			},
			items :[
				new Ext.form.ComboBox({
				store: personStore,
				emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
				typeAhead: false,
				listConfig : {
					loadingText: 'در حال جستجو...'
				},
				pageSize:10,
				width: 610,
				colspan: 3,
				hiddenName : "staff_id",
				fieldLabel : "فرد",
				valueField : "staff_id",
				displayField : "fullname",
				tpl: new Ext.XTemplate(
						'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
							,'<td height="23px">کد پرسنلی</td>'
							,'<td>کد شخص</td>'
							,'<td>نام</td>'
							,'<td>نام خانوادگی</td>'
							,'<td>واحد محل خدمت</td></tr>',
						'<tpl for=".">',
						'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
							,'<td style="border-left:0;border-right:0" class="search-item">{PersonID}</td>'
							,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
							,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
							,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
							,'<td style="border-left:0;border-right:0" class="search-item">{unit_name}&nbsp;</td></tr>',
						'</tpl>'
						,'</table>'),

				listeners :{
					select : function(combo, records){
						var record = records[0];
						record.data.fullname = record.data.pfname + " " + record.data.plname; 
						this.setValue(record.data.staff_id);
						this.collapse();
					}
				}
			}),
			{
				xtype : "fieldset",
				style: "margin-left:4px",
				colspan : 3,
				title : "نوع فرد",
				html :  "<input type=checkbox name='PersonType_1' checked>  هیئت علمی &nbsp;&nbsp;&nbsp;&nbsp;" +
						"<input type=checkbox name='PersonType_2' checked>  کارمند &nbsp;&nbsp;&nbsp;&nbsp;" +
						"<input type=checkbox name='PersonType_3' checked>  روزمزد &nbsp;&nbsp;&nbsp;&nbsp;" +
						"<input type=checkbox name='PersonType_5' checked>  قراردادی &nbsp;&nbsp;&nbsp;&nbsp;"
			},
			{
				xtype: 'fieldset',
				title : "مراکز هزینه",
				colspan : 3,
				cls: 'x-check-group-alt',
				width : 700,
				fieldLabel: 'Auto Layout',
				itemId : "chkgroup",
				collapsible: true,
				collapsed: true,
				layout : {
					type : "table",
					columns : 4,
					tableAttrs : {
						width : "100%",
						align : "center"
					},
					tdAttrs : {							
						align:'right',
						width : "۱6%"
					}
				},
				items : [{
					xtype : "checkbox",
					boxLabel : "همه",
					checked : true,
					listeners : {
						change : function(){
							parentNode = PersonDutyObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
							elems = parentNode.getElementsByTagName("input");
							for(i=0; i<elems.length; i++)
							{
								if(elems[i].id.indexOf("chkcostID_") != -1)
									elems[i].checked = this.getValue();
							}
						}
					}
				}]
			}],
			buttons :  [{
							text : "مشاهده گزارش",
							handler : function(){PersonDutyObject.showReport()},
							listeners : {
								click : function(){
									PersonDutyObject.get('excel').value = "";
								}
							},
							iconCls : "report"
						},
						{
							text : "excel خروجی ",
							handler : function(){PersonDutyObject.showReport()},
							listeners : {
								click : function(){
									PersonDutyObject.get('excel').value = "1";
								}
							},
							iconCls : "excel"
						}]
		});
		
		//..........................
		
		new Ext.data.Store({
			fields : ["cost_center_id","title"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "../global/domain.data.php?task=searchCostCenter",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true,
			listeners:{
				load : function(){
					this.each(function (record) {
						PersonDutyObject.filterPanel.down("[itemId=chkgroup]").add({
							xtype : "container",
							html : "<input checked type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " > " + record.data.title
						});
					});
				}}
		});
				
		//........................
		
		 this.filterPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
			PersonDutyObject.showReport();
			e.preventDefault();
			e.stopEvent();
			return false;
		});
		
	}
	
	var PersonDutyObject = new PersonDuty();
	
	PersonDuty.prototype.showReport = function(btn, e)
	{
		if(!this.filterPanel.getForm().isValid())
			return;
		
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		
		//if(this.get("excel").value == "")
			this.form.action =  this.address_prefix + "DutyReport.php?task=ShowReport" + (this.get("excel").value == "1" ? "&excel=true" : "");
		//else if(this.get("excel").value == "1") 
		//	this.form.action =  this.address_prefix + "DutyReport.php?task=GetWorkTimeExcel";
					

		this.form.submit();
		return;
	}

</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div><br>
		<input type="hidden" name="excel" id="excel">
	</form>
</center>
