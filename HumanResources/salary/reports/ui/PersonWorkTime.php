<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	93.11
//---------------------------

require_once("../../../header.inc.php");

require_once inc_reportGenerator;

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

global $tableQuery;

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
	
	//.........................محاسبه کارکرد سالانه...................
	
	 $PayYear = substr( $_POST['from_date'],0,4) ; 
	
	
		$year_fdate = DateModules::shamsi_to_miladi($PayYear."/01/01") ;  
		$year_edate = DateModules::shamsi_to_miladi(($PayYear+1)."/01/01") ;  
	
		$year_fdate = str_replace("/","-",$year_fdate);
		$year_edate = str_replace("/","-",$year_edate);
	  
		PdoDataAccess::runquery('DROP TABLE IF EXISTS temp_work_writs;') ; 
		PdoDataAccess::runquery('
								CREATE TABLE temp_work_writs  AS
								SELECT w.staff_id,										
									CASE WHEN w.emp_mode IN (3,8,9,15,7,16,11,12,14,20,22,25,27,28,29) 
										THEN 0 
										WHEN  w.emp_mode IN ( '.EMP_MODE_LEAVE_WITH_SALARY.' ) THEN 1
										ELSE (CASE w.annual_effect
														WHEN 1 THEN 1
														WHEN 2 THEN 0.5
														WHEN 3 THEN 0
														WHEN 4 THEN 2
											END) END annual_coef,
									CASE 
										WHEN w.execute_date < \''.$year_fdate.'\' THEN \''.$year_fdate.'\'
										ELSE w.execute_date
									END execute_date,
									CASE
										WHEN ( SELECT MIN(w2.execute_date) execute_date
												FROM writs w2
												WHERE w2.execute_date <= \''.$year_edate.'\' AND
														w2.staff_id = w.staff_id AND
														w2.history_only = 0 AND
														w2.state = '.WRIT_SALARY.' AND
														(w2.execute_date > w.execute_date OR
														(w2.execute_date = w.execute_date AND w2.writ_id > w.writ_id) OR
														(w2.execute_date = w.execute_date AND w2.writ_id = w.writ_id AND w2.writ_ver > w.writ_ver))
												GROUP BY staff_id) IS NULL THEN \''.$year_edate.'\'
										ELSE ( SELECT MIN(w2.execute_date) execute_date
												FROM writs w2
												WHERE   w2.execute_date <= \''.$year_edate.'\' AND
														w2.staff_id = w.staff_id AND
														w2.history_only = 0 AND
														w2.state = '.WRIT_SALARY.' AND
														(w2.execute_date > w.execute_date OR
														(w2.execute_date = w.execute_date AND w2.writ_id > w.writ_id) OR
														(w2.execute_date = w.execute_date AND w2.writ_id = w.writ_id AND w2.writ_ver > w.writ_ver))
												GROUP BY staff_id)
											END end_date,
									w.person_type
								FROM writs w
								WHERE w.history_only = 0 AND
									w.state = '.WRIT_SALARY.' AND
									( \''.$year_edate.'\' >= w.execute_date OR w.execute_date IS NULL OR w.execute_date = \'0000-00-00\') 								
							'); 
			 

		PdoDataAccess::runquery('ALTER TABLE temp_work_writs ADD INDEX(staff_id)');
		
		PdoDataAccess::runquery('DROP TABLE IF EXISTS temp_last_salary_writs;'); 
		PdoDataAccess::runquery('CREATE  TABLE temp_last_salary_writs  AS
									SELECT w.staff_id,
										SUBSTRING_INDEX(SUBSTRING( MAX( CONCAT(w.execute_date,w.writ_id,\'.\',w.writ_ver) ),11) ,\'.\',1) writ_id,
										SUBSTRING_INDEX(MAX( CONCAT(w.execute_date,w.writ_id,\'.\',w.writ_ver) ) ,\'.\',-1) writ_ver
									FROM writs w
									WHERE w.state = '.WRIT_SALARY.' AND
										w.history_only = 0  AND if(w.person_type = 3 , w.emp_mode not in ( 3,8,9,15,7,16,11,12,14,20,22) , (1=1)) 										 
									GROUP BY w.staff_id;');
										
	PdoDataAccess::runquery('ALTER TABLE temp_last_salary_writs ADD INDEX(staff_id,writ_id,writ_ver);');
		
	PdoDataAccess::runquery("SET NAMES 'utf8'");
			
	
	$MainRows = PdoDataAccess::runquery_fetchMode('
						SELECT  w.staff_id,
								p.plname,
								p.pfname,
								w.person_type,
								w.cost_center_id,
								tlw.writ_id  last_writ_id,
								tlw.writ_ver last_writ_ver,
								s.bank_id,
								s.account_no,
								s.UnitCode,
                                                                o.ptitle unit_title , 
                                                                s.ouid,
                                                                o1.ptitle sub_unit_title,
								si.tax_include,
								pay.staff_id as before_calced,
								( SELECT tax_table_type_id
								FROM staff_tax_history sth
								WHERE sth.staff_id = w.staff_id
								ORDER BY start_date DESC
								LIMIT 1
								) as tax_table_type_id,
								( SELECT SUM(wsi.value)
								FROM writ_salary_items wsi
								WHERE wsi.writ_id = w.writ_id AND
										wsi.writ_ver = w.writ_ver AND
										wsi.salary_item_type_id IN('.SIT_WORKER_BASE_SALARY.','.SIT_WORKER_ANNUAL_INC.') AND
										w.person_type = '.HR_WORKER.' AND
										w.state = '.WRIT_SALARY.'
								) as worker_base_salary,
								SUM(DATEDIFF(tw.end_date,tw.execute_date) * tw.annual_coef) work_time
						FROM    temp_work_writs tw
								INNER JOIN staff s
									ON(tw.staff_id = s.staff_id)
								INNER JOIN staff_include_history si
									ON(s.staff_id = si.staff_id AND si.start_date <= \''.$year_edate.'\' AND (si.end_date IS NULL OR si.end_date = \'0000-00-00\' OR si.end_date >= \''.$year_edate.'\') )
								INNER JOIN persons p
									ON(s.PersonID = p.PersonID)
								INNER JOIN temp_last_salary_writs tlw
									ON(s.staff_id = tlw.staff_id)
								INNER JOIN writs w
									ON(tlw.staff_id = w.staff_id AND tlw.writ_id = w.writ_id AND tlw.writ_ver = w.writ_ver AND
									  (w.person_type = '.HR_WORKER.' OR w.emp_mode <> '.EMP_MODE_RETIRE.') )
								LEFT OUTER JOIN payments pay
									ON(pay.pay_year = '.$PayYear.' AND pay.pay_month=12 AND pay.payment_type= '.HANDSEL_PAYMENT.' AND pay.staff_id = s.staff_id)
INNER JOIN org_new_units o 
                      ON s.UnitCode = o.ouid 
INNER JOIN org_new_units o1 
                      ON s.ouid = o1.ouid 


						WHERE tw.end_date > \''.$year_fdate.'\' '.$where.'
						GROUP BY w.staff_id,
								p.plname,
								p.pfname,
								w.person_type,
								w.cost_center_id,
								tlw.writ_id,
								tlw.writ_ver,
								s.bank_id,
								s.account_no,
								s.tafsili_id,
								pay.staff_id' ,$param  );
								
								//echo PdoDataAccess::GetLatestQueryString(); die();
	return $MainRows;
}

function ShowReport(){
		
	$rpt = new ReportGenerator();
	$rpt->mysql_resource = PrepareData();
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
        $rpt->addColumn("واحد اصلی محل خدمت", "unit_title");
        $rpt->addColumn("واحد فرعی محل خدمت", "sub_unit_title");
	$rpt->addColumn("کارکرد", "work_time");
	
	$rpt->header_alignment = "center";
	$rpt->headerContent = "
		<table width=100% border=0 style='font-family:b nazanin;'>
			<tr>
				<td width=120px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align=center style='font-weight:bold'>گزارش کارکرد کارکنان</td>
				<td width=120px>
					شماره : 
					<br>
					تاریخ : 
					" . DateModules::shNow() . "
				</td>
			</tr>
			
		</table>";
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
	
	$param = array();
		
	MakeWhere($where, $param);
			
	$rpt = new ReportGenerator();	
	$rpt->mysql_resource = PrepareData();
		
	$rpt->addColumn("شماره شناسایی", "staff_id");
	$rpt->addColumn("نام خانوادگي", "plname");
	$rpt->addColumn("نام", "pfname");
        $rpt->addColumn("واحد اصلی محل خدمت", "unit_title");
        $rpt->addColumn("واحد فرعی محل خدمت", "sub_unit_title");
	$rpt->addColumn("کارکرد", "work_time");
	
	$rpt->excel = true;
	$rpt->generateReport();
	die(); 
}


?>

<script>
	PersonWTM.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",
	
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};

	function PersonWTM()
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
				xtype : "shdatefield",
				colspan : 1,
				name : "from_date",
				fieldLabel : "تاریخ از",
				allowBlank : false ,
				width:180
			},{
				xtype : "shdatefield",
				colspan : 1,
				name : "to_date",
				fieldLabel : "تاریخ تا",
				allowBlank : false,
				width:180
			},{
				xtype : "fieldset",
				style: "margin-left:4px",
				colspan : 1,
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
							parentNode = PersonWTMObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
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
							handler : function(){PersonWTMObject.showReport()},
							listeners : {
								click : function(){
									PersonWTMObject.get('excel').value = "";
								}
							},
							iconCls : "report"
						},
						{
							text : "excel خروجی ",
							handler : function(){PersonWTMObject.showReport()},
							listeners : {
								click : function(){
									PersonWTMObject.get('excel').value = "1";
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
				url : this.address_prefix + "../../../global/domain.data.php?task=searchCostCenter",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true,
			listeners:{
				load : function(){
					this.each(function (record) {
						PersonWTMObject.filterPanel.down("[itemId=chkgroup]").add({
							xtype : "container",
							html : "<input checked type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " > " + record.data.title
						});
					});
				}}
		});
				
		//........................
		
		 this.filterPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
			PersonWTMObject.showReport();
			e.preventDefault();
			e.stopEvent();
			return false;
		});
		
	}
	
	var PersonWTMObject = new PersonWTM();
	
	PersonWTM.prototype.showReport = function(btn, e)
	{
		if(!this.filterPanel.getForm().isValid())
			return;
		
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		
		if(this.get("excel").value == "")
			this.form.action =  this.address_prefix + "PersonWorkTime.php?task=ShowReport";
		else if(this.get("excel").value == "1") 
			this.form.action =  this.address_prefix + "PersonWorkTime.php?task=GetWorkTimeExcel";
					

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
