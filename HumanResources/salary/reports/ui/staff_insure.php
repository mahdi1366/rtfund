<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	93.03
//---------------------------

require_once("../../../header.inc.php");

require_once inc_reportGenerator;

if (isset($_REQUEST["task"]))
{
	switch($_REQUEST["task"])
	{
		case "ShowReport":
			ShowReport();	
			
		case "GetInsureExcel":
			GetInsureExcel();
		
		case "GetChangesExcel":
			GetChangesExcel();
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
		$where .= " AND pit.cost_center_id in(" . implode(",", $CostCenters) . ")";
	if(count($EmpStates) > 0)
		$where .= " AND w.emp_state in(" . implode(",", $EmpStates) . ")";
	
	
	if(!empty($_POST["PayType"]) && $_REQUEST["task"] != "GetChangesExcel")
	{
		$where .= " AND pit.payment_type = :pt";
		$param[":pt"] = $_POST['PayType'];
	}
}

function PrepareData(){
	
	$where = "";
	$param = array();
	MakeWhere($where, $param);
	
	$pay_year = $_POST["pay_year"];
	$pay_month = $_POST["pay_month"];
	//.............................................
	
	PdoDataAccess::runquery("DROP TABLE IF EXISTS temp_pure_pay");
	PdoDataAccess::runquery('CREATE TABLE temp_pure_pay as
		SELECT pit.staff_id,
			SUM(pit.pay_value - pit.get_value) pure_payment,
			SUM(pit.diff_pay_value - pit.diff_get_value * pit.diff_value_coef)  diff_pure_payment,
			SUM(CASE s.person_type
				WHEN '.HR_PROFESSOR.' THEN (CASE 
							WHEN pit.salary_item_type_id IN('.SIT_PROFESSOR_BASE_SALARY.','.SIT_PROFESSOR_SPECIAL_EXTRA.') THEN pit.pay_value
							END)
				WHEN '.HR_EMPLOYEE.' THEN (CASE
							WHEN pit.salary_item_type_id IN('.SIT_STAFF_BASE_SALARY.','.SIT_STAFF_ANNUAL_INC.','.SIT_STAFF_MIN_PAY.','.
																SIT_STAFF_ADAPTION_DIFFERENCE.','.SIT_STAFF_ABSOPPTION_EXTRA.','.
																SIT_STAFF_DOMINANT_JOB_EXTRA.','.SIT_STAFF_JOB_EXTRA.') THEN pit.pay_value
							END)
				END) continus_payment,
			SUM(CASE s.person_type
				WHEN '.HR_PROFESSOR.' THEN (CASE
							WHEN pit.salary_item_type_id IN('.SIT_PROFESSOR_BASE_SALARY.','.SIT_PROFESSOR_SPECIAL_EXTRA.') THEN pit.diff_pay_value
							END)
				WHEN '.HR_EMPLOYEE.' THEN (CASE
							WHEN pit.salary_item_type_id IN('.SIT_STAFF_BASE_SALARY.','.SIT_STAFF_ANNUAL_INC.','.SIT_STAFF_MIN_PAY.','.
																SIT_STAFF_ADAPTION_DIFFERENCE.','.SIT_STAFF_ABSOPPTION_EXTRA.','.
																SIT_STAFF_DOMINANT_JOB_EXTRA.','.SIT_STAFF_JOB_EXTRA.') THEN pit.diff_pay_value
							END)
				END) diff_continus_payment	
		FROM payment_items pit
				INNER JOIN staff s ON(pit.staff_id = s.staff_id)
				INNER JOIN payments p ON(p.pay_year = pit.pay_year AND p.pay_month = pit.pay_month AND p.staff_id = pit.staff_id AND p.payment_type = pit.payment_type)
				INNER JOIN writs w ON(p.writ_id = w.writ_id AND p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id )
				
		WHERE pit.pay_year = ' . $pay_year . ' AND pit.pay_month = ' . $pay_month . $where . '

		GROUP BY pit.staff_id', $param);
	
	global $tableQuery;
	$tableQuery = PdoDataAccess::GetLatestQueryString();
	
	$MainRows = PdoDataAccess::runquery_fetchMode("
		select tpp.staff_id,
			per.personID,
			per.pfname,
			per.plname,
			c.title,
			c.cost_center_id,
			s.person_type,
			pit.pay_year,
			pit.pay_month,
			pit.get_value insure_value,
			(pit.diff_get_value * pit.diff_value_coef) diff_insure_value,
			tpp.pure_payment,
			tpp.diff_pure_payment,
			tpp.continus_payment,
			tpp.diff_continus_payment,
			pit.salary_item_type_id,
			pit.param1 normal_insure,
			pit.param8 normal2_insure ,
			pit.param2 first_surplus_insure,
			pit.param3 second_surplus_insure,
			pit.param4 normal_insure_value,
			pit.param9 normal2_insure_value,
			pit.param5 first_surplus_insure_value,
			pit.param6 second_surplus_insure_value,
			pit.param7 org_insure_value,
			(pit.diff_param7 * pit.diff_value_coef) diff_org_insure_value 
		
		from temp_pure_pay tpp
			INNER JOIN payment_items pit ON (tpp.staff_id = pit.staff_id)
			INNER JOIN payments p ON (p.pay_year = pit.pay_year AND p.pay_month = pit.pay_month AND 
					p.payment_type = pit.payment_type AND p.staff_id = pit.staff_id)
			INNER JOIN writs w ON(p.writ_id = w.writ_id AND p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id )
			INNER JOIN staff s ON (tpp.staff_id = s.staff_id)
			INNER JOIN persons per ON (s.PersonID = per.PersonID)
			INNER JOIN cost_centers c ON (pit.cost_center_id = c.cost_center_id)
		
		where  pit.pay_year = $pay_year AND pit.pay_month = $pay_month AND 
			(pit.get_value > 0 OR (pit.diff_get_value * pit.diff_value_coef) <> 0) AND
			pit.salary_item_type_id in(" . SIT_PROFESSOR_REMEDY_SERVICES_INSURE.",".SIT_STAFF_REMEDY_SERVICES_INSURE.")
			$where
	
		order by pit.pay_year,pit.pay_month,per.plname,per.pfname",$param);
	return $MainRows;
}

function ShowReport(){
	
	global $tableQuery;
	$rpt = new ReportGenerator();
	$rpt->mysql_resource = PrepareData();
	?>
	<html>
	<head>
		<META http-equiv=Content-Type content="text/html; charset=UTF-8" >
	</head>
		<body dir=rtl>
			<div style="display:none" >
				<? echo $tableQuery . "\n----------------------------------------------------\n";?>
				<? echo PdoDataAccess::GetLatestQueryString();?>
				
			</div>
	<?	
	
	function moneyRender($row,$value){
		return number_format($value, 0, '.', ',');
	}
	
	function pure_paymentRender($row){
		return moneyRender(null,$row["pure_payment"]) . "<br>" . moneyRender(null,$row["diff_pure_payment"]);
	}
	
	function continus_paymentRender($row){
		return moneyRender(null,$row["continus_payment"]) . "<br>" . moneyRender(null,$row["diff_continus_payment"]);
	}
	
	function totalRender($row){
		return moneyRender(null, $row["normal_insure"] + $row["normal2_insure"] + $row["first_surplus_insure"] + $row["second_surplus_insure"]);
	}
	
	function insure_valueRender($row){
		return moneyRender(null,$row["insure_value"]) . "<br>" . moneyRender(null,$row["diff_insure_value"]);
	}
	
	function org_insure_valueRender($row){
		return moneyRender(null,$row["org_insure_value"]) . "<br>" . moneyRender(null,$row["diff_org_insure_value"]);
	}
		
	$rpt->addColumn("شماره<br> شناسایی", "staff_id");
	$rpt->addColumn("نام خانوادگي", "plname");
	$rpt->addColumn("نام", "pfname");
	$rpt->addColumn("خالص دريافتي <br>تفاوت", "pure_payment", "pure_paymentRender");
	$rpt->addColumn("مستمر<br>تفاوت", "continus_payment", "continus_paymentRender");
	$rpt->addColumn("کل", "normal_insure", "totalRender");
	$rpt->addColumn("مازاد2", "second_surplus_insure");
	$rpt->addColumn("مازاد1", "first_surplus_insure");
	$rpt->addColumn("معمولي2", "normal2_insure");
	$rpt->addColumn("معمولي", "normal_insure");
	$rpt->addColumn("مبلغ بيمه<br>تفاوت", "insure_value", "insure_valueRender");
	$rpt->addColumn("سهم سازمان<br>تفاوت", "org_insure_value", "org_insure_valueRender");

	$rpt->header_alignment = "center";
	$rpt->headerContent = "
		<table width=100% border=0 style='font-family:b nazanin;'>
			<tr>
				<td width=120px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align=center style='font-weight:bold'>گزارش کارمندان استفاده کننده از بيمه</td>
				<td width=120px>
					شماره : 
					<br>
					تاریخ : 
					" . DateModules::shNow() . "
				</td>
			</tr>
			<tr bgcolor='#BDD3EF' >
				<td colspan=3 style='border: 1px solid black;font-weight:bold;color:#15428B;padding-right:5px'>
					" . DateModules::GetMonthName($_POST["pay_month"]) . " ماه " . $_POST["pay_year"] . "						
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

function GetInsureExcel(){
		
	$param = array();
	$where = " AND p.pay_year=:year AND p.pay_month=:month";
	$param[":year"] = $_POST["pay_year"];
	$param[":month"] = $_POST["pay_month"];
	
	MakeWhere($where, $param);
	
	if($_POST["pay_year"] > 1392 ){
		$coef = 2 ; 
	}
	else $coef = 1.65 ; 
	
	$dataTable = PdoDataAccess::runquery_fetchMode("
		select     
			pfname ,
			plname ,
			if(pit.param1=0,0,1) main_insure ,
			pit.param1 - if(param1=0,0,1) normal_insure,
			pit.param8 normal2_insure ,
			pit.param2 first_surplus_insure ,
			pit.param3 second_surplus_insure,
			(pit.param7 * 100 / ".$coef." ) sumBimeh  , 
			pit.param7 normal_value ,       
			( pit.param8 * 80000 ) normal2_insure_value ,
			( pit.param2 * pit.param5 ) first_surplus_value ,      
			( pit.param3 * pit.param6 ) second_surplus_value ,
			pit.param7 org_value ,
			if( (pit.pay_year >= 1393 and pit.pay_month >=10) ,pit.param7 ,
			if((pit.pay_year >= 1390 and pit.pay_month >=5) ,(( pit.param7 * 1.7 ) / ".$coef." ) ,
				if(pit.pay_year >= 1391 ,(( pit.param7 * 1.7 ) / ".$coef." ) ,(( pit.param7 * 3 ) / 2 ))))   gov_value ,
			( pit.param7 + pit.get_value ) total_value ,
			(pit.diff_get_value * pit.diff_value_coef)+ (pit.diff_param7) insure_diff_value,
			s.staff_id ,
			NULL insure_code ,
			per.national_code

		from payments p
			INNER JOIN payment_items pit
				ON ((p.staff_id = pit.staff_id) AND (p.pay_year = pit.pay_year) AND
					(p.pay_month = pit.pay_month) AND (p.payment_type = pit.payment_type))
					AND (pit.salary_item_type_id IN (143,38) )
			LEFT OUTER JOIN writs w
				ON (w.writ_id = p.writ_id AND w.writ_ver = p.writ_ver AND w.staff_id = p.staff_id )
			LEFT OUTER JOIN staff s
				ON (s.staff_id = w.staff_id)
			LEFT OUTER JOIN persons per
				ON (per.personID = s.personID)
			
		where 1=1 $where

	union all

	select per.pfname  pfname,
			per.plname plname, 0 main_insure ,
			0 normal_insure,
			0 normal2_insure ,
			0 first_surplus_insure ,
			0 second_surplus_insure,
			0 sumBimeh  ,
			0 normal_value ,
			0 normal2_insure_value ,
			0 first_surplus_value ,
			0 second_surplus_value ,
			0 org_value ,
			0  gov_value ,
			0 total_value ,
			0 insure_diff_value,
			s.staff_id ,
			NULL insure_code ,
			per.national_code

		FROM persons per inner join staff s
				on per.personid = s.personid and per.person_type = s.person_type and per.sex = 2
			inner join staff_include_history sih
				on s.staff_id = sih.staff_id and sih.service_include = 1
			inner join payments p
				on p.staff_id = s.staff_id and p.payment_type = 1
			left join payment_items pit
				on p.pay_year = pit.pay_year and p.pay_month = pit.pay_month and
					p.payment_type  = pit.payment_type and p.staff_id = pit.staff_id and pit.salary_item_type_id in(38 , 143 )
			LEFT OUTER JOIN writs w ON (w.writ_id = p.writ_id AND w.writ_ver = p.writ_ver AND w.staff_id = p.staff_id )
			
		where pit.salary_item_type_id  is null $where ", $param);
	//echo PdoDataAccess::GetLatestQueryString() ; die() ; 
	$rpt = new ReportGenerator();
	
	$rpt->mysql_resource = $dataTable;
		
	$rpt->addColumn("نام", "pfname");
	$rpt->addColumn('نام خانوادگي', "plname");
	$rpt->addColumn("اصلي", "main_insure");
	$rpt->addColumn("تبعي1", "normal_insure");
	$rpt->addColumn("تبعی1 جدید", "normal2_insure");
	$rpt->addColumn("تبعي2", "first_surplus_insure");
	$rpt->addColumn("تبعي3", "second_surplus_insure");	
	$rpt->addColumn("میزان حقوق مشمول حق سرانه", "sumBimeh");
	$rpt->addColumn("مبلغ اصلی و تبعی1", "normal_value");
	$rpt->addColumn("مبلغ تبعی1 جدید", "normal2_insure_value");
	$rpt->addColumn("مبلغ تبعی2", "first_surplus_value");
	$rpt->addColumn("مبلغ تبعی3", "second_surplus_value");
	$rpt->addColumn("سهم بیمه گذار", "org_value");
	$rpt->addColumn("سهم دولت", "gov_value");
	$rpt->addColumn("جمع ماه جاری", "total_value");
	$rpt->addColumn("مبلغ تفاوت", "insure_diff_value");
	$rpt->addColumn("كد پرسنلي", "staff_id");
	$rpt->addColumn("كد بيمه", "insure_code");
	$rpt->addColumn("شماره ملي", "national_code");
	
	$rpt->excel = true;
	$rpt->generateReport();
	die();
}

function GetChangesExcel(){
	
	$param = array();
	$where = "";
	$param[":year"] = $_POST["pay_year"];
	$param[":month"] = $_POST["pay_month"];
	$param[":pt"] = $_POST["PayType"];
	
	$pre_year = $_POST["pay_year"] ;
	$pre_month = $_POST["pay_month"] - 1 ;
	if($pre_month == 0){
		$pre_year--;
		$pre_month = 12 ;
	}
	$param[":preyear"] = $pre_year;
	$param[":premonth"] = $pre_month;
	
	
	MakeWhere($where, $param);
	
	$dataTable = PdoDataAccess::runquery_fetchMode("
		select     
			'' date,
			concat(plname,' ',pfname) name,
			s.staff_id ,
			bi.Title emp_mode_desc,
			pit1.get_value val1,
			if( pit2.param1 - pit1.param1>0 , pit2.param1 - pit1.param1 , 0 ) normal_insure_inc, 
			if( pit2.param8 - pit1.param8>0 , pit2.param8 - pit1.param8 , 0 ) normal2_insure_inc, 
			if( pit2.param2 - pit1.param2>0 , pit2.param2 - pit1.param2 , 0 ) first_surplus_insure_inc,
			if( pit2.param3 - pit1.param3>0 , pit2.param3 - pit1.param3 , 0 ) second_surplus_insure_inc,
			if( pit2.param1 - pit1.param1>0 , pit2.param1 - pit1.param1 , 0 ) +
				if(pit2.param2 - pit1.param2>0 , pit2.param2 - pit1.param2 , 0 ) +
				if(pit2.param3 - pit1.param3>0 , pit2.param3 - pit1.param3 , 0 ) insure_inc_count,
			bi2.Title emp_mode_desc2,
			pit2.get_value val2,
			if( pit2.param1 - pit1.param1>0 , 0 , pit1.param1 - pit2.param1 ) normal_insure_dec,
			if( pit2.param8 - pit1.param8>0 , 0 , pit1.param8 - pit2.param8 ) normal2_insure_dec,
			if( pit2.param2 - pit1.param2>0 , 0 , pit1.param2 - pit2.param2 ) first_surplus_insure_dec,
			if( pit2.param3 - pit1.param3>0 , 0 , pit1.param3 - pit2.param3 ) second_surplus_insure_dec,
			if( pit2.param1 - pit1.param1>0 , 0 , pit1.param1 - pit2.param1 ) +
				if(	pit2.param2 - pit1.param2>0 , 0 , pit1.param2 - pit2.param2 ) +
				if( pit2.param3 - pit1.param3>0 , 0 , pit1.param3 - pit2.param3 ) insure_dec_count

		from staff s
			INNER JOIN persons per ON (per.personID = s.personID)
			LEFT OUTER JOIN payments p1
				ON p1.pay_year = :preyear AND p1.pay_month = :premonth AND p1.payment_type = :pt AND s.staff_id = p1.staff_id 
			LEFT OUTER JOIN payments p2
				ON p2.pay_year = :year AND p2.pay_month = :month AND p2.payment_type = :pt AND s.staff_id = p2.staff_id
			LEFT OUTER JOIN writs w1
				ON (w1.writ_id = p1.writ_id AND w1.writ_ver = p1.writ_ver AND w1.staff_id = p1.staff_id )
			LEFT OUTER JOIN writs w2
				ON (w2.writ_id = p2.writ_id AND w2.writ_ver = p2.writ_ver AND w2.staff_id = p2.staff_id )
			LEFT OUTER JOIN payment_items pit1
				ON ((p1.staff_id = pit1.staff_id) AND (p1.pay_year = pit1.pay_year) AND
					(p1.pay_month = pit1.pay_month) AND (p1.payment_type = pit1.payment_type))
					AND (pit1.salary_item_type_id IN (143,38) )
			LEFT OUTER JOIN payment_items pit2
				ON ((p2.staff_id = pit2.staff_id) AND (p2.pay_year = pit2.pay_year) AND
					(p2.pay_month = pit2.pay_month) AND (p2.payment_type = pit2.payment_type))
					AND (pit2.salary_item_type_id IN (143,38) )
			left join Basic_Info bi on(bi.TypeID=4 AND bi.InfoID=w1.emp_mode)
			left join Basic_Info bi2 on(bi2.TypeID=4 AND bi2.InfoID=w2.emp_mode)
		
		where (pit1.get_value > 0 OR pit2.get_value >0 ) AND
			(pit1.param1 != pit2.param1 OR
			 pit1.param8 != pit2.param8 OR
			 pit1.param2 != pit2.param2 OR
			 pit1.param3 != pit2.param3 OR
			 w1.emp_mode != w2.emp_mode ) $where ", $param);
	
	//echo PdoDataAccess::GetLatestQueryString();die();
	
	$rpt = new ReportGenerator();
	$rpt->mysql_resource = $dataTable;
	
	$rpt->addColumn("تاريخ", "date");
	$rpt->addColumn('نام و نام خانوادگي', "name");
	$rpt->addColumn("كد اصلي بيمه شده", "staff_id");
	$rpt->addColumn("وضعيت استخدامي قبل", "emp_mode_desc");
	$rpt->addColumn("مبلغ قبلي", "val1");
	$rpt->addColumn("افزايش تبعي1", "normal_insure_inc");
	$rpt->addColumn("افزايش تبعي 1 جديد", "normal2_insure_inc");	
	$rpt->addColumn("افزايش تبعي2", "first_surplus_insure_inc");
	$rpt->addColumn("افزايش تبعي3", "second_surplus_insure_inc");
	$rpt->addColumn("جمع افزايش", "insure_inc_count");
	$rpt->addColumn("وضعيت استخدامي جديد", "emp_mode_desc2");
	$rpt->addColumn("'مبلغ جديد", "val2");
	$rpt->addColumn("كاهش تبعي1", "normal_insure_dec");
	$rpt->addColumn("كاهش تبعي 1 جدید", "normal2_insure_dec");
	$rpt->addColumn("كاهش تبعي2", "first_surplus_insure_dec");
	$rpt->addColumn("كاهش تبعي3", "second_surplus_insure_dec");
	$rpt->addColumn("جمع كاهش", "insure_dec_count");
	
	$rpt->excel = true;
	$rpt->generateReport();
	
	die();
}
?>

<script>
	StaffInsure.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",
	
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};

	function StaffInsure()
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
			}),{
				xtype : "numberfield",
				hideTrigger : true,
				fieldLabel : "سال",
				width : 120,
				allowBlank : false,
				name : "pay_year"
			},{
				xtype : "numberfield",
				hideTrigger : true,
				width : 120,
				fieldLabel : "ماه",
				allowBlank : false,
				name : "pay_month"
			},{
				xtype : "combo",
				colspan:2,
				store :  new Ext.data.Store({
					fields : ["InfoID","Title"],
					proxy : {
						type: 'jsonp',
						url : this.address_prefix + "../../../global/domain.data.php?task=searchPayType",
						reader: {
							root: 'rows',
							totalProperty: 'totalCount'
						}
					}
					,
					autoLoad : true,
					listeners:{
						load : function(){
							StaffInsureObject.filterPanel.down("[itemId=PayType]").setValue("1");										
						}
					}
				}),
				valueField : "InfoID",
				displayField : "Title",
				hiddenName : "PayType",
				itemId : "PayType",
				fieldLabel : "نوع پرداخت",						
				width:300,
				labelWidth: 100
			},{
				xtype : "fieldset",
				style: "margin-left:4px",
				colspan : 3,
				title : "نوع فرد",
				html :  "<input type=checkbox name='PersonType_1' checked>  هیئت علمی &nbsp;&nbsp;&nbsp;&nbsp;" +
						"<input type=checkbox name='PersonType_2' checked>  کارمند &nbsp;&nbsp;&nbsp;&nbsp;" +
						"<input type=checkbox name='PersonType_3' checked>  روزمزد &nbsp;&nbsp;&nbsp;&nbsp;" +
						"<input type=checkbox name='PersonType_5' checked>  قراردادی &nbsp;&nbsp;&nbsp;&nbsp;"
			},{
				xtype: 'fieldset',
				title : "وضعیت استخدامی",
				colspan : 3,
				cls: 'x-check-group-alt',
				width : 700,					
				fieldLabel: 'Auto Layout',
				itemId : "chkgroup2",	
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
							parentNode = StaffInsureObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
							elems = parentNode.getElementsByTagName("input");
							for(i=0; i<elems.length; i++)
							{
								if(elems[i].id.indexOf("chkEmpState_") != -1)
									elems[i].checked = this.getValue();
							}
						}
					}
				}]
			},{
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
							parentNode = StaffInsureObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
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
				handler : function(){StaffInsureObject.showReport()},
				listeners : {
					click : function(){
						StaffInsureObject.get('excel').value = "";
					}
				},
				iconCls : "report"
			},{
				text : "excel بیمه",
				handler : function(){StaffInsureObject.showReport()},
				listeners : {
					click : function(){
						StaffInsureObject.get('excel').value = "1";
					}
				},
				iconCls : "excel"
			},{
				text : "excel تغییرات",
				handler : function(){StaffInsureObject.showReport()},
				listeners : {
					click : function(){
						StaffInsureObject.get('excel').value = "2";
					}
				},
				iconCls : "excel"
			},{
				iconCls : "clear",
				text : "پاک کردن فرم",
				handler : function(){
					this.up("form").getForm().reset();
					StaffInsureObject.get("mainForm").reset();
				}
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
						StaffInsureObject.filterPanel.down("[itemId=chkgroup]").add({
							xtype : "container",
							html : "<input checked type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " > " + record.data.title
						});
					});
				}}
		});
		
		new Ext.data.Store({
			fields : ["InfoID","Title"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "../../../global/domain.data.php?task=searchEmpState",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true,
			listeners:{
				load : function(){
					this.each(function (record) {
						StaffInsureObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input checked type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " > " + 
								record.data.Title
						});
					});
				}}
		});
		//........................
		
		 this.filterPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
			StaffInsureObject.showReport();
			e.preventDefault();
			e.stopEvent();
			return false;
		});
		
	}
	
	var StaffInsureObject = new StaffInsure();
	
	StaffInsure.prototype.showReport = function(btn, e)
	{
		if(!this.filterPanel.getForm().isValid())
			return;
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		if(this.get("excel").value == "")
			this.form.action =  this.address_prefix + "staff_insure.php?task=ShowReport";
		else if(this.get("excel").value == "1")
			this.form.action =  this.address_prefix + "staff_insure.php?task=GetInsureExcel";
		else if(this.get("excel").value == "2")
			this.form.action =  this.address_prefix + "staff_insure.php?task=GetChangesExcel";
			
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
