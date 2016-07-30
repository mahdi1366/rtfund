<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.07
//---------------------------

require_once("../../../header.inc.php");
ini_set("display_errors","On") ; 
	
if (isset($_REQUEST["show"]))
{
	
	$keys = array_keys($_POST);
	$WhereCost = $WherePayItm = $WhereSubItm ="" ;
	$arr = "" ;
	
	//...................... مراکز هزینه ................
	
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chkcostID_") !== false)
		{			
			$arr = preg_split('/_/', $keys[$i]);	
			if(isset($arr[1]))
			$WhereCost .= ($WhereCost!="") ?  ",".$arr[1] : $arr[1] ; 
		}	
				
	}
	
	//......................اقلام مزایا...................
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chkPID_") !== false)
		{			
			$arr = preg_split('/_/', $keys[$i]);	
			if(isset($arr[1]))
			$WherePayItm .= ($WherePayItm!="") ?  ",".$arr[1] : $arr[1] ; 
		}	
				
	}
	
	//......................... اقلام کسورات ..............
	
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chkSID_") !== false)
		{			
			$arr = preg_split('/_/', $keys[$i]);	
			if(isset($arr[1]))
			$WherePayItm .= ($WherePayItm!="") ?  ",".$arr[1] : $arr[1] ; 
		}	
				
	}
	
	$staffID = (isset($_POST['staff_id']) && $_POST['staff_id'] > 0) ? $_POST['staff_id'] : " " ; 
	
	$this_month  = $_POST['pay_month'];
    $this_year   = $_POST['pay_year'];
	
	if ($this_month == 1) {
		$prior_month = 12;
		$prior_year  = $_POST['pay_year'] - 1;
	} else {
		$prior_month = $_POST['pay_month'] - 1;
		$prior_year  = $_POST['pay_year'];
	}
	
	$qry = "DROP TABLE IF EXISTS my_temp";
	PdoDataAccess::runquery($qry);
	
	$where = '((pai.pay_month = '.$this_month.' AND pai.pay_year = '.$this_year.' ) OR
			   (pai.pay_month = '.$prior_month.' AND pai.pay_year = '.$prior_year.'))';
			   
	if($_POST['ITMFormat'] == 1) {
		$where .= ' AND sit.compute_place = 1 ';
	}
	elseif($_POST['ITMFormat'] == 2) {
		$where .= ' AND sit.compute_place = 2 ';
	}
	
	$where .= ($staffID > 0 ) ? " AND pai.staff_id=".$staffID : "" ; 
	$where .= ($WhereCost !="" ) ? " AND pai.cost_center_id in (".$WhereCost.") " : "" ; 		
	$where .= ($WherePayItm !="" ) ? " AND pai.salary_item_type_id in (".$WherePayItm.") " : "" ; 	
	
	
	$qry = " CREATE TABLE my_temp as
				SELECT pai.staff_id,
					   pai.salary_item_type_id
				
				FROM  payment_items pai
					  INNER JOIN salary_item_types sit
						ON(pai.salary_item_type_id = sit.salary_item_type_id)
							
				where 
					  ".$where."
					  
				GROUP BY pai.staff_id,
						 pai.salary_item_type_id 
						 
				HAVING 	(SUM(CASE pai.pay_month
						   WHEN ".$prior_month." THEN (pai.pay_value - pai.get_value)
						   END) <>
						 SUM(CASE pai.pay_month
						   WHEN ".$this_month." THEN (pai.pay_value + pai.diff_pay_value - pai.get_value - pai.diff_get_value * pai.diff_value_coef)
						   END)) OR COUNT(*)=1 ";
						   
	PdoDataAccess::runquery($qry);
	
	$qry = "select  s.staff_id  staff_id,
					s.tafsili_id  tafsili_id,
					s.person_type,
					sit.salary_item_type_id salary_item_type_id,
					sit.full_title full_title,
					sit.compute_place,
					sit.person_type,
					CASE pai.pay_month
						WHEN ".$prior_month." THEN
												(CASE sit.effect_type
													WHEN 1 THEN  pai.pay_value
													WHEN 2 THEN  pai.get_value
												 END)
						WHEN ".$this_month." THEN
												(CASE sit.effect_type
													WHEN 1 THEN   pai.pay_value + pai.diff_pay_value
													WHEN 2 THEN   pai.get_value + pai.diff_get_value * pai.diff_value_coef
												END)
					END pay_value,
					pai.pay_year,
					pai.pay_month,
					cc.cost_center_id,
					cc.title cost_center_title,
					p.plname,
					p.pfname
			from my_temp mt
						      INNER JOIN payment_items pai
						            ON(mt.staff_id = pai.staff_id AND mt.salary_item_type_id = pai.salary_item_type_id)
                              INNER JOIN  salary_item_types sit
                                    ON (pai.salary_item_type_id = sit.salary_item_type_id)
						      INNER JOIN cost_centers cc
						            ON(pai.cost_center_id = cc.cost_center_id)
						      INNER JOIN staff s
						            ON(s.staff_id = pai.staff_id)
						      INNER JOIN persons p
						            ON(s.PersonID = p.PersonID) 
			where ".$where."  
			order by cost_center_id,plname,pfname,salary_item_type_id "; 
					
	$dataTable = PdoDataAccess::runquery($qry);	
/*echo PdoDataAccess::GetLatestQueryString();
die();*/	
		 
?>
<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
			text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#4D7094}
		.reportGenerator .header1 {color: white;font-weight: bold;background-color:#465E86}		
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
</style>
<?

	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl"><center>';
	
	//.........................................
	echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:50%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش مزایا و کسورات"." </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		     . DateModules::shNow() . "<br>";		
	echo "</td></tr></table>";      
	
			
	$cur_center_sum_value = 0;
	$pre_center_sum_value = 0;
	$cur_person_sum_value = 0;
	$pre_person_sum_value = 0;
	 
	$CurCostID = $dataTable[0]['cost_center_id']; 
	$SID = $dataTable[0]['staff_id']; 
	$Itm = $dataTable[0]['salary_item_type_id']; 
	$ItmTitle = $dataTable[0]['full_title']; 
	
	$this_year   = $_POST['pay_year'];	
	$cur_month = $_POST['pay_month'];
	
	if ($cur_month  == 1) {
		$prior_month = 12;
		$prior_year  = $_POST['pay_year'] - 1;
	} else {
		$prior_month = $_POST['pay_month'] - 1;
		$prior_year  = $_POST['pay_year'];
	}
	
	 $cur_month_value = 0;
	 $pre_month_value = 0;
	
	echo '<table  class="reportGenerator" style="text-align: right;width:58%!important" cellpadding="4" cellspacing="0">
			 <tr class="header1">					
				<td colspan="6"> مرکز هزینه :&nbsp;['.$dataTable[0]['cost_center_id'].']&nbsp;'.$dataTable[0]['cost_center_title'].'</td>
			 </tr>
		  </table>
		  <table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">
			 <tr class="header">					
				<td>ردیف: 1</td>
				<td>نام خانوادگی :'.$dataTable[0]['plname'].'</td>
				<td>نام : '.$dataTable[0]['pfname'].'</td>		
				<td>شماره شناسایی : '.$dataTable[0]['staff_id'].'</td>										
				<td>نوع فرد :'.$dataTable[0]['person_type'].'</td>		
			</tr>
			<tr class="header">					
				<td colspan="2" >قلم حقوقی</td>
				<td>مبلغ در ماه'.$prior_month.'(ريال)</td>
				<td>مبلغ در ماه'.$cur_month.'(ريال)</td>												
				<td>اختلاف(ريال)</td>		
			</tr> ' ; 
		

   for($i=0 ; $i < count($dataTable) ; $i++)
   {
			
	if($dataTable[$i]['cost_center_id'] != $CurCostID ) {
	
	
	
	echo " <tr>					
				<td colspan='2' >".$ItmTitle."</td> 
				<td>".number_format($pre_month_value, 0, '.', ',')."</td>	
				<td>".number_format($cur_month_value, 0, '.', ',')."</td>										
				<td>".number_format(($cur_month_value - $pre_month_value), 0, '.', ',')."</td>			  
		   </tr> " ; 
					
			$cur_person_sum_value += $cur_month_value;
			$pre_person_sum_value += $pre_month_value;

			$cur_center_sum_value += $cur_month_value;
			$pre_center_sum_value += $pre_month_value;
			
			$cur_month_value = 0;
			$pre_month_value = 0;
			
			$Itm = $dataTable[$i]['salary_item_type_id'] ; 
			$ItmTitle = $dataTable[$i]['full_title'] ; 
	
			echo '<tr style="background-color:#E1F0FF" ><td colspan="2" >جمع : </td>
					  <td>'.number_format($pre_person_sum_value, 0, '.', ',').'</td>
					  <td>'.number_format($cur_person_sum_value, 0, '.', ',').'</td>
					  <td>'.number_format(($cur_person_sum_value - $pre_person_sum_value), 0, '.', ',').'</td>
				  </tr> 				  
				  <tr style="background-color:#FFFFDA;font-weight:bold" ><td colspan="2" >جمع مبلغ مرکز هزینه :</td>
					  <td>'.number_format($pre_center_sum_value, 0, '.', ',').'</td>
					  <td>'.number_format($cur_center_sum_value, 0, '.', ',').'</td>
					  <td>'.number_format(($cur_center_sum_value - $pre_center_sum_value), 0, '.', ',').'</td>
				  </tr>
				  </table>'; 
						 
	    echo '<table><tr><td><br></td></tr></table>
			  <table  class="reportGenerator" style="text-align: right;width:58%!important" cellpadding="4" cellspacing="0">
				 <tr class="header1">					
					<td colspan="6"> مرکز هزینه :&nbsp;['.$dataTable[$i]['cost_center_id'].']&nbsp;'.$dataTable[$i]['cost_center_title'].'</td>
				 </tr>
			  </table>				
			  <table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">
				 <tr class="header">					
					<td>ردیف: '.($i+1).'</td>
					<td>نام خانوادگی :'.$dataTable[$i]['plname'].'</td>
					<td>نام : '.$dataTable[$i]['pfname'].'</td>		
					<td>شماره شناسایی : '.$dataTable[$i]['staff_id'].'</td>										
					<td>نوع فرد :'.$dataTable[$i]['person_type'].'</td>		
				 </tr>
				 <tr class="header">					
					<td colspan="2" >قلم حقوقی</td>
					<td>مبلغ در ماه'.$prior_month.'(ريال)</td>
					<td>مبلغ در ماه'.$cur_month.'(ريال)</td>												
					<td>اختلاف(ريال)</td>		
				 </tr> ';
			  
		
        $cur_center_sum_value = 0;
        $pre_center_sum_value = 0;
		
		$cur_person_sum_value = 0 ; 
		$pre_person_sum_value = 0 ;
			
		$CurCostID = $dataTable[$i]['cost_center_id'] ;
		$SID = $dataTable[$i]['staff_id'] ; 
	
	}
	  
		if($dataTable[$i]['staff_id'] != $SID ) 
		{
		
			echo " <tr>					
						<td colspan='2' >".$ItmTitle."</td> 
						<td>".number_format($pre_month_value, 0, '.', ',')."</td>	
						<td>".number_format($cur_month_value, 0, '.', ',')."</td>										
						<td>".number_format(($cur_month_value - $pre_month_value), 0, '.', ',')."</td>			  
				   </tr> " ; 
					
			$cur_person_sum_value += $cur_month_value;
			$pre_person_sum_value += $pre_month_value;

			$cur_center_sum_value += $cur_month_value;
			$pre_center_sum_value += $pre_month_value;
			
			$cur_month_value = 0;
			$pre_month_value = 0;
			
			$Itm = $dataTable[$i]['salary_item_type_id'] ; 
			$ItmTitle = $dataTable[$i]['full_title'] ; 
			
			echo '<tr style="background-color:#E1F0FF"><td colspan="2"  >جمع : </td>
					  <td>'.number_format($pre_person_sum_value, 0, '.', ',').'</td>
					  <td>'.number_format($cur_person_sum_value, 0, '.', ',').'</td>
					  <td>'.number_format(($cur_person_sum_value - $pre_person_sum_value), 0, '.', ',').'</td>
				  </tr>
				   <tr class="header">					
					<td>ردیف: '.($i+1).'</td>
					<td>نام خانوادگی :'.$dataTable[$i]['plname'].'</td>
					<td>نام : '.$dataTable[$i]['pfname'].'</td>		
					<td>شماره شناسایی : '.$dataTable[$i]['staff_id'].'</td>										
					<td>نوع فرد :'.$dataTable[$i]['person_type'].'</td>		
				  </tr>
				  <tr class="header">					
					<td colspan="2" >قلم حقوقی</td>
					<td>مبلغ در ماه'.$prior_month.'(ريال)</td>
					<td>مبلغ در ماه'.$cur_month.'(ريال)</td>												
					<td>اختلاف(ريال)</td>		
				  </tr> ';
		    
			$cur_person_sum_value = 0 ; 
			$pre_person_sum_value = 0 ;
			$SID = $dataTable[$i]['staff_id'] ;  
			
		}
					
			if($dataTable[$i]['salary_item_type_id'] != $Itm ) 
			{
							
				echo " <tr>					
						<td colspan='2' >".$ItmTitle."</td> 
						<td>".number_format($pre_month_value, 0, '.', ',')."</td>	
						<td>".number_format($cur_month_value, 0, '.', ',')."</td>										
						<td>".number_format(($cur_month_value - $pre_month_value), 0, '.', ',')."</td>			  
				       </tr> " ; 
					
				$cur_person_sum_value += $cur_month_value;
				$pre_person_sum_value += $pre_month_value;

				$cur_center_sum_value += $cur_month_value;
				$pre_center_sum_value += $pre_month_value;
				
				$cur_month_value = 0;
				$pre_month_value = 0;
				
				$Itm = $dataTable[$i]['salary_item_type_id'] ; 
				$ItmTitle = $dataTable[$i]['full_title'] ; 
			
			}
			
		//....................................................	
			if($dataTable[$i]['pay_month'] == $_POST['pay_month'])
				$cur_month_value = $dataTable[$i]['pay_value'];
			else 			 
				$pre_month_value = $dataTable[$i]['pay_value'];
		
			continue ; 
			
		
	}
	
	
	echo " <tr>					
			<td colspan='2' >".$dataTable[$i-1]['full_title']."</td> 
			<td>".number_format($pre_month_value, 0, '.', ',')."</td>	
			<td>".number_format($cur_month_value, 0, '.', ',')."</td>										
			<td>".number_format(($cur_month_value - $pre_month_value), 0, '.', ',')."</td>			  
		   </tr> " ; 
					
	$cur_person_sum_value += $cur_month_value;
	$pre_person_sum_value += $pre_month_value;

	$cur_center_sum_value += $cur_month_value;
	$pre_center_sum_value += $pre_month_value;
							
	echo '<tr style="background-color:#E1F0FF"><td colspan="2" >جمع : </td>
			  <td>'.number_format($pre_person_sum_value, 0, '.', ',').'</td>
			  <td>'.number_format($cur_person_sum_value, 0, '.', ',').'</td>
			  <td>'.number_format(($cur_person_sum_value - $pre_person_sum_value), 0, '.', ',').'</td>
		  </tr>
		  <tr style="background-color:#FFFFDA;font-weight:bold" ><td colspan="2" >جمع مبلغ مرکز هزینه :</td>
			  <td>'.number_format($pre_center_sum_value, 0, '.', ',').'</td>
			  <td>'.number_format($cur_center_sum_value, 0, '.', ',').'</td>
			  <td>'.number_format(($cur_center_sum_value - $pre_center_sum_value), 0, '.', ',').'</td>
		  </tr>'; 
		
	echo "</table></center>" ;
	die();
				 
 }

?>

<script>
	MonthlyComparative.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	MonthlyComparative.prototype.showReport = function(type)
	{
		
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "monthly_comparative_payment.php?show=true";
		this.form.action += type == "excel" ? "&excel=true" : "";	
		this.form.submit();	
		return;
	}
		
	function MonthlyComparative()
	{
		   
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 880,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش مقایسه پرداخت ماهانه",
			fieldDefaults: {
				labelWidth: 110
			},
			layout: {
				type: 'table',
				columns: 2
			},
			items :[{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "سال ",
					name : "pay_year",
					allowBlank : false,
					width : 200
					},{
						xtype : "numberfield",
						hideTrigger : true,
						fieldLabel : "ماه ",
						name : "pay_month",
						allowBlank : false,
						width : 200
					},
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
						xtype: 'fieldset',
						title : "مزایا",
						colspan : 3,		
						style:'background-color:#DFEAF7',					
						width : 840,						
						fieldLabel: 'Auto Layout',
						itemId : "chkPaygroup",
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
						}
					},	
					{
						xtype: 'fieldset',
						title : "کسورات",
						colspan : 3,		
						style:'background-color:#DFEAF7',					
						width : 840,						
						fieldLabel: 'Auto Layout',
						itemId : "chkSubgroup",
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
						}
					},	
					{
						xtype: 'fieldset',
						title : "مراکز هزینه",
						colspan : 3,		
						style:'background-color:#DFEAF7',					
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
									parentNode = MonthlyComparativeObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
									elems = parentNode.getElementsByTagName("input");
									for(i=0; i<elems.length; i++)
									{
										if(elems[i].id.indexOf("chkcostID_") != -1)
											elems[i].checked = this.getValue();
									}
								}
							}
						}]
					},
					{
						colspan:4,										
						xtype: 'container',                    											
						html:" نوع قلم : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='TRepFormat_0' name='ITMFormat' value='0' checked>&nbsp;  همه "+
							 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='TRepFormat_1' name='ITMFormat' value='1'>&nbsp; اقلام حکمي "  + 
							 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='TRepFormat_2' name='ITMFormat' value='1'>&nbsp; اقلام غيرحکمي "
					},
					{
						colspan:4,										
						xtype: 'container',                    											
						html:" نوع گزارش : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='RepFormat_0' name='RepFormat' value='0' checked>&nbsp; اشخاص "+
							 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='RepFormat_1' name='RepFormat' value='1'>&nbsp; مرکز هزینه  "  + 
							 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='RepFormat_2' name='RepFormat' value='1'>&nbsp; نوع شخص " + 
							 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='RepFormat_3' name='RepFormat' value='1'>&nbsp; کلیه مراکز هزینه "
					}									
					
			],
			buttons: [{
						text : "مشاهده گزارش",                                           
						iconCls : "report",
						handler: function(){MonthlyComparativeObject.showReport('show');}
					  }/*,{
						text : "خروجی Excel",
						iconCls : "excel",
						handler : function(){MonthlyComparativeObject.showReport('excel');}
					}*/]			
		});
		
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
						MonthlyComparativeObject.filterPanel.down("[itemId=chkgroup]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " checked > " + record.data.title
						});
						
					});
										
				}}
			
		});
		
		new Ext.data.Store({
			fields : ["salary_item_type_id","full_title","person_type"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "../../../global/domain.data.php?task=searchSalaryItemTypes&ET=1",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true,
			listeners:{
				load : function(){
				
					var ptype = 1 ;
					var t = 1 ; 
					
					MonthlyComparativeObject.filterPanel.down("[itemId=chkPaygroup]").add({
								xtype : "container",
								html : "<table><tr><td width='15%' style='font-size:12px;font-weight:bold;color:#336699'> هیئت علمی&nbsp;</td><td colspan=3><hr width=700 ></td></tr></table>",
								colspan : 4 									
								
							});
					MonthlyComparativeObject.filterPanel.down("[itemId=chkPaygroup]").add({
								xtype : "container",
								html : "<br>",
								colspan : 4 									
								
							});
					this.each(function (record) {
					
						if(record.data.person_type == 1 )
							ptitle = "هیئت علمی" ; 
						else if(record.data.person_type == 2 )
							ptitle = "کارمند" ; 	
						else if(record.data.person_type == 3 )
							ptitle = "روز مزد بیمه ای" ; 	
						else if(record.data.person_type == 5 )
							ptitle = " قراردادی" ; 	
						else if(record.data.person_type == 10 )
							ptitle = "بازنشسته" ; 	
						else if(record.data.person_type == 100 || record.data.person_type == 101 )
							ptitle = "کلیه کارکنان" ; 	
													
						if(record.data.person_type == ptype) {
							
							MonthlyComparativeObject.filterPanel.down("[itemId=chkPaygroup]").add({
								xtype : "container",
								html : "<input type=checkbox name=chkPID_" + record.data.salary_item_type_id + " id=chkPID_" + record.data.salary_item_type_id + " > " + record.data.full_title
							});
							t++ ; 
							if(t==5)
								t=1; 
						}
						else 
						{
							if(t>1)
							{
								MonthlyComparativeObject.filterPanel.down("[itemId=chkPaygroup]").add({
								xtype : "container",
								html : " <br> ",
								colspan : 4
							});
							}
							MonthlyComparativeObject.filterPanel.down("[itemId=chkPaygroup]").add({
								xtype : "container",
								html : "<br>",
								colspan : 4 									
								
							});
							MonthlyComparativeObject.filterPanel.down("[itemId=chkPaygroup]").add({
								xtype : "container",
								html : "<table><tr><td width='15%' style='font-size:12px;font-weight:bold;color:#336699' >" + ptitle + "&nbsp;</td><td colspan=3><hr width=700 ></td></tr></table>",
								colspan : 4
							});
							MonthlyComparativeObject.filterPanel.down("[itemId=chkPaygroup]").add({
								xtype : "container",
								html : "<br>",
								colspan : 4 									
								
							});
							MonthlyComparativeObject.filterPanel.down("[itemId=chkPaygroup]").add({
								xtype : "container",
								html : "<input type=checkbox name=chkPID_" + record.data.salary_item_type_id + " id=chkPID_" + record.data.salary_item_type_id + " > " + record.data.full_title
							});
							t++ ; 
							ptype = record.data.person_type ;
						} 
					});
										
				}}
			
		});
		
		//...................................................................
		
		new Ext.data.Store({
			fields : ["salary_item_type_id","full_title","person_type"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "../../../global/domain.data.php?task=searchSalaryItemTypes&ET=2",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true,
			listeners:{
				load : function(){
				
					var ptype = 1 ;
					var t = 1 ; 
					
					MonthlyComparativeObject.filterPanel.down("[itemId=chkSubgroup]").add({
								xtype : "container",
								html : "<table><tr><td width='15%' style='font-size:12px;font-weight:bold;color:#336699'> هیئت علمی&nbsp;</td><td colspan=3><hr width=700 ></td></tr></table>",
								colspan : 4 									
								
							});
					MonthlyComparativeObject.filterPanel.down("[itemId=chkSubgroup]").add({
								xtype : "container",
								html : "<br>",
								colspan : 4 									
								
							});
					this.each(function (record) {
					
						if(record.data.person_type == 1 )
							ptitle = "هیئت علمی" ; 
						else if(record.data.person_type == 2 )
							ptitle = "کارمند" ; 	
						else if(record.data.person_type == 3 )
							ptitle = "روز مزد بیمه ای" ; 	
						else if(record.data.person_type == 5 )
							ptitle = " قراردادی" ; 	
						else if(record.data.person_type == 10 )
							ptitle = "بازنشسته" ; 	
						else if(record.data.person_type == 100 || record.data.person_type == 101 )
							ptitle = "کلیه کارکنان" ; 	
													
						if(record.data.person_type == ptype) {
							
							MonthlyComparativeObject.filterPanel.down("[itemId=chkSubgroup]").add({
								xtype : "container",
								html : "<input type=checkbox name=chkSID_" + record.data.salary_item_type_id + " id=chkSID_" + record.data.salary_item_type_id + " > " + record.data.full_title
							});
							t++ ; 
							if(t==5)
								t=1; 
						}
						else 
						{
							if(t>1)
							{
								MonthlyComparativeObject.filterPanel.down("[itemId=chkSubgroup]").add({
								xtype : "container",
								html : " <br> ",
								colspan : 4
								});
							}
							MonthlyComparativeObject.filterPanel.down("[itemId=chkSubgroup]").add({
								xtype : "container",
								html : "<br>",
								colspan : 4 									
								
							});
							MonthlyComparativeObject.filterPanel.down("[itemId=chkSubgroup]").add({
								xtype : "container",
								html : "<table><tr><td width='15%' style='font-size:12px;font-weight:bold;color:#336699' >" + ptitle + "&nbsp;</td><td colspan=3><hr width=700 ></td></tr></table>",
								colspan : 4
							});
							MonthlyComparativeObject.filterPanel.down("[itemId=chkSubgroup]").add({
								xtype : "container",
								html : "<br>",
								colspan : 4 									
								
							});
							MonthlyComparativeObject.filterPanel.down("[itemId=chkSubgroup]").add({
								xtype : "container",
								html : "<input type=checkbox name=chkSID_" + record.data.salary_item_type_id + " id=chkSID_" + record.data.salary_item_type_id + " > " + record.data.full_title
							});
							t++ ;
							ptype = record.data.person_type ;
						} 
					});
										
				}}
			
		});
		
		
	}
	
	var MonthlyComparativeObject = new MonthlyComparative();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div>	
	</form>
</center>
