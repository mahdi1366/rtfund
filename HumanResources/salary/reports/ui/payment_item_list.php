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
	$WhereCost = $WherePT =	$WhereBT = $WhereEmpstate = "" ;
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
		
		if(strpos($keys[$i],"chkEmpState_") !== false)
		{		
			$arr = preg_split('/_/', $keys[$i]);		
			if(isset($arr[1]))
			$WhereEmpstate .= ($WhereEmpstate!="") ?  ",".$arr[1] : $arr[1] ;
		}
				
	}
	//........................... نوع فرد..............
	if(isset($_POST['PT_1']) && $_POST['PT_1']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,1 " :  "1 " ; 
	
	if(isset($_POST['PT_2']) && $_POST['PT_2']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,2 " :  "2 " ; 
	
	if(isset($_POST['PT_3']) && $_POST['PT_3']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,3 " :  "3 " ; 
	
	if(isset($_POST['PT_5']) && $_POST['PT_5']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,5 " :  "5" ; 
	
	//............................................
	$cost_from = $cost_group = '' ; 
	if(isset($_POST['RepType']) && $_POST['RepType']== 1)
	{
		$cost_from = 'cc.cost_center_id,cc.title cost_center_title,';
		$cost_group = 'cc.cost_center_id,cc.title,';
	}
	
	
	$query = " select  sit.salary_item_type_id,
					   sit.full_title salary_item_type_title,
					   pit.pay_year,
					   pit.pay_month,
					   sit.effect_type,
					   sit.person_type,
					   ".$cost_from."
					   SUM(pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) pay_value,
					   SUM(pit.get_value + pit.diff_get_value * pit.diff_value_coef) get_value 	   
					   
			   from payment_items pit
						      INNER JOIN salary_item_types sit
						            ON (pit.salary_item_type_id = sit.salary_item_type_id)
						      INNER JOIN payments p
						      		ON (pit.pay_year = p.pay_year AND pit.pay_month = p.pay_month AND pit.staff_id = p.staff_id AND pit.payment_type = p.payment_type)
						      INNER JOIN writs w
						      		ON (p.writ_id = w.writ_id AND p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id )
						      INNER JOIN cost_centers cc
						            ON (pit.cost_center_id = cc.cost_center_id)
							  INNER JOIN staff s 
									ON  s.staff_id = pit.staff_id  
			  
			  where pit.pay_year >= ".$_POST['from_pay_year']." AND
					pit.pay_year <= ".$_POST['to_pay_year']." AND
					pit.pay_month >= ".$_POST['from_pay_month']." AND					
					pit.pay_month <= ".$_POST['to_pay_month']." AND			
					pit.payment_type = ".$_POST['PayType'] ; 

	$query .= ($WhereCost !="" ) ? " AND pit.cost_center_id in (".$WhereCost.") " : "" ; 		
	$query .= ($WhereEmpstate !="" ) ? " AND w.emp_state in (".$WhereEmpstate.") " : "" ; 			
	$query .= ($WherePT !="" ) ? " AND s.person_type in (".$WherePT.") " : "" ; 	

	$query .=  " group by pay_year,
						  pay_month ,".$cost_group."
						 sit.effect_type,
						 sit.full_title,
						 sit.salary_item_type_id,
						 sit.person_type 
				 
				 order by 
					sit.effect_type,
					sit.salary_item_type_id "; 
			
	$dataTable = PdoDataAccess::runquery($query);		
				 
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
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش لیست حقوق به تفکیک قلم حقوقی"." </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		     . DateModules::shNow() . "<br>";		
	echo "</td></tr></table>";      
	

	if($dataTable[0]['effect_type'] == BENEFIT ) 
	{
		$HeaderTitle = "حقوق و مزایای کارکنان" ; 
	}
	else
	{
		$HeaderTitle = " کسورات " ; 
	}
	
	echo '<table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">
			 <tr class="header1">					
				<td colspan="3">'.$HeaderTitle.'</td>
				<td colspan="3">مربوط به &nbsp; '.DateModules::GetMonthName($dataTable[0]['pay_month']).' ماه &nbsp;&nbsp;'.$dataTable[0]['pay_year'].' </td>
			 </tr>
			 <tr class="header">					
				<td colspan="2" width="35%" >عنوان  </td>
				<td width="15%"  >مبلغ (ریال)</td>
				<td colspan="2" width="35%"  >عنوان  </td>
				<td width="15%"  >مبلغ (ریال)</td>	
			</tr>' ; 

	
	die();
	
	$PaySum = $GetSum = 0 ; 	
	$EF = $dataTable[0]['effect_type'];
	$PY = $dataTable[0]['pay_year'];
	$PM = $dataTable[0]['pay_month']; 
	 	
	for($i=0 ; $i < count($dataTable) ; $i=$i+2)
   {
		/*if(($count> 0 && $count%35 == 0 ) || $dataTable[$i]['cost_center_id'] != $CC || $SIID != $dataTable[0]['salary_item_type_id'] ||
			$PY != $dataTable[0]['pay_year'] || $PM != $dataTable[0]['pay_month'] )
		{		
		
				echo '<tr><td colspan=5 >جمع : </td><td>'.number_format($sum, 0, '.', ',').'</td></tr>
					  </table><hr style="page-break-after:always; visibility: hidden"><br><br>';
				
				if($dataTable[$i]['cost_center_id'] != $CC || $SIID != $dataTable[0]['salary_item_type_id'] ||
					$PY != $dataTable[0]['pay_year'] || $PM != $dataTable[0]['pay_month']) {
					$CC = $dataTable[$i]['cost_center_id'] ; 
					$SIID = $dataTable[$i]['salary_item_type_id'];
					$PY = $dataTable[$i]['pay_year'];
					$PM = $dataTable[$i]['pay_month'];				
					$sum = $count=0 ; 
				}
				echo '<table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">
					  <tr class="header1">					
						<td colspan="6"> سال : '.$dataTable[$i]['pay_year'].' &nbsp;&nbsp;&nbsp;&nbsp;  ماه :'.
												 DateModules::GetMonthName($dataTable[$i]['pay_month']).'&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;قلم حقوقی : '.
												 $dataTable[$i]['print_title'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; مرکز هزینه :'.$dataTable[$i]['title'].'</td>
					  </tr>
					  <tr class="header">					
						<td>ردیف </td>
						<td>شماره شناسایی</td>
						<td>کد تفضیلی</td>		
						<td> نام خانوادگی </td>						
						<td>نام </td>				
						<td>مبلغ </td>		
					  </tr>' ; 
		}*/
		
		if($dataTable[$i]['person_type'] )
		
		if($dataTable[$i]['effect_type'] == 1 )
		   $pgvalue = $dataTable[$i]['pay_value'] ;
	    else 
			$pgvalue = $dataTable[$i]['get_value'] ;
			
	     echo " <tr>					
					<td>".$dataTable[$i]['salary_item_type_title']."</td> 
					<td>".$dataTable[$i]['tafsili_id']."</td>	
					<td>".$dataTable[$i]['plname']."</td>					
					<td>".$dataTable[$i]['pfname']."</td>									
					<td>".number_format($pgvalue, 0, '.', ',')."</td>			  
				</tr>" ; 
				
		if($dataTable[$i]['effect_type'] == 1 )
		   $sum += $dataTable[$i]['pay_value'] ;
	    else 
			$sum += $dataTable[$i]['get_value'] ;
			
		$count++ ; 
	}
		echo '<tr><td colspan=5 >جمع : </td><td>'.number_format($sum, 0, '.', ',').'</td></tr>';
	   
	    echo "</table></center>" ;
		die();
				 
 }

?>

<script>
	PayItemList.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	PayItemList.prototype.showReport = function(type)
	{
		
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "payment_item_list.php?show=true";
		this.form.action += type == "excel" ? "&excel=true" : "";	
		this.form.submit();	
		return;
	}
		
	function PayItemList()
	{
		   
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش لیست حقوق به تفکیک قلم حقوقی",
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
					fieldLabel : "سال از",
					name : "from_pay_year",
					allowBlank : false,
					width : 200
				},{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "ماه از",
					name : "from_pay_month",
					allowBlank : false,
					width : 200
				},{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "سال تا",
					name : "to_pay_year",
					allowBlank : false,
					width : 200
				},{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "ماه تا",
					name : "to_pay_month",
					allowBlank : false,
					width : 200
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
								parentNode = PayItemListObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
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
					xtype: 'fieldset',
					title : "وضعیت استخدامی",
					colspan : 3,
					style:'background-color:#DFEAF7',					
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
								parentNode = PayItemListObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
								elems = parentNode.getElementsByTagName("input");
								for(i=0; i<elems.length; i++)
								{
									if(elems[i].id.indexOf("chkEmpState_") != -1)
										elems[i].checked = this.getValue();
								}
							}
						}
					}]
				},
				{
					colspan:3,										
					xtype: 'container',					
					style : "padding:5px",
					html:"نوع فرد : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
				
							"<input type=checkbox id='PT_1' name='PT_1' value=1 checked>&nbsp; هیئت علمی"+
							"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							"<input type=checkbox id='PT_2' name='PT_2' value=1 checked>&nbsp;  کارمند  " +
							"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							"<input type=checkbox id='PT_3' name='PT_3' value=1 checked>&nbsp;  روزمزدبیمه ای " +
							"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							"<input type=checkbox id='PT_5' name='PT_5' value=1 >&nbsp; قراردادی "
				},												
				{
					xtype : "combo",
					colspan:3,
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
										PayItemListObject.filterPanel.down("[itemId=PayType]").setValue("1");										
								}
							}
								
												}),
					valueField : "InfoID",
					displayField : "Title",
					hiddenName : "PayType",
					itemId : "PayType",
					fieldLabel : "نوع پرداخت&nbsp;",						
					listConfig: {
						loadingText: 'در حال جستجو...',
						emptyText: 'فاقد اطلاعات',
						itemCls : "search-item"
					},
					width:300
				},
				{
					colspan:3,										
					xtype: 'container',  					
					style : "padding:5px",
					html:"به تفکیک مرکز هزینه : &nbsp;&nbsp;&nbsp;"+				
							"<input type=checkbox id='RepType' name='RepType' value=1 >"
				}		
					
			],
			buttons: [{
						text : "مشاهده گزارش",                                           
						iconCls : "report",
						handler: function(){PayItemListObject.showReport('show');}
					  }
					  ]			
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
						PayItemListObject.filterPanel.down("[itemId=chkgroup]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " checked > " + record.data.title
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
						PayItemListObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.Title
						});
						
					});
										
				}}
			
		});		
		
		
	}
	
	var PayItemListObject = new PayItemList();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div>	
	</form>
</center>
