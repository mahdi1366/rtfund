<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.06
//---------------------------

require_once("../../../header.inc.php");
ini_set("display_errors","On") ; 
	
if (isset($_REQUEST["show"]))
{
	
	$keys = array_keys($_POST);
	$WhereCost = $WherePT =	$WhereBT = $WhereEmpstate = "" ;
	$arr = "" ;
	
	//...................... مراکز هزینه و بانکها................
	
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chkcostID_") !== false)
		{			
			$arr = preg_split('/_/', $keys[$i]);	
			if(isset($arr[1]))
			$WhereCost .= ($WhereCost!="") ?  ",".$arr[1] : $arr[1] ; 
		}	
		
		
		if(strpos($keys[$i],"chkBnk_") !== false)
		{		
			$arr = preg_split('/_/', $keys[$i]);		
			if(isset($arr[1]))
			$WhereBT .= ($WhereBT!="") ?  ",".$arr[1] : $arr[1] ;
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
		
	if(isset($_POST['PT_10']) && $_POST['PT_10']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,10 " :  "10" ; 
	//............................................
	
	$staffID = (isset($_POST['staff_id']) && $_POST['staff_id'] > 0) ? $_POST['staff_id'] : " " ; 

	$query = " select  s.staff_id,
					   p.pfname,
					   p.plname,
					   pa.account_no,
					   p.national_code,
					   pi.pay_year,
					   pi.pay_month,
					   s.tafsili_id,
					   sum(pi.pay_value + pi.diff_pay_value * pi.diff_value_coef - pi.get_value - pi.diff_get_value * pi.diff_value_coef) pure_pay,
					   b.name,
					   b.bank_id
			   from    payment_items pi
					 INNER JOIN payments pa
						  ON(pa.pay_year = pi.pay_year AND pa.pay_month = pi.pay_month AND pa.staff_id = pi.staff_id AND pa.payment_type = pi.payment_type)
					 INNER JOIN staff s
						  ON (pi.staff_id = s.staff_id)
					 INNER JOIN persons p
						  ON (s.PersonID = p.PersonID)
					 INNER JOIN banks b
						  ON (b.bank_id = s.bank_id)
			  where pi.pay_year = ".$_POST['pay_year']." AND
					pi.pay_month = ".$_POST['pay_month']." AND
					pi.payment_type = ".$_POST['PayType']." AND 
					pi.payment_type = ".$_POST['PayType'] ; 
			  
    $query .= ($staffID > 0 ) ? " AND pi.staff_id=".$staffID : "" ; 
	$query .= ($WhereCost !="" ) ? " AND pi.cost_center_id in (".$WhereCost.") " : "" ; 			
	$query .= ($WherePT !="" ) ? " AND s.person_type in (".$WherePT.") " : "" ; 
	$query .= ($WhereBT  !="" ) ? " AND  pa.bank_id in (".$WhereBT.") " : "" ; 

	$query .= " group by b.bank_id,
						 p.pfname,
						 p.plname,
						 s.staff_id,
						 pa.account_no,
						 p.national_code,
						 pi.pay_year,
						 pi.pay_month,
						 s.tafsili_id,
						 b.name 
				order by bank_id "; 
				
	$dataTable = PdoDataAccess::runquery($query);		

    $qry = " select bi.Title month_title 
                        from  Basic_Info bi 
                                where  bi.typeid = 41 AND InfoID = ".$_POST["pay_month"] ; 
    $res = PdoDataAccess::runquery($qry) ; 
    $monthTitle = $res[0]['month_title'] ; 

	if(isset($_GET['excel']))
	{
		
		ini_set("display_errors","On") ; 
	
		require_once 'excel.php';
		require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
		require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";
		
		$workbook = &new writeexcel_workbook("/tmp/temp.xls");
		$worksheet =& $workbook->addworksheet("Sheet1");
		$heading =& $workbook->addformat(array('align' => 'center', 'bold' => 1, 'bg_color' => 'blue', 'color' => 'white'));

		$worksheet->write(0, 0, "ردیف", $heading);
		$header = array("شماره شناسایی ",
						"کدملی",
						"نام",
						"نام خانوادگی" ,
						"شماره حساب" ,
						"مانده قابل پرداخت" 
						) ;
		
			for ($i = 0; $i< count($header); $i++)
			{
				$worksheet->write(0, $i+1 , $header[$i], $heading);			
			}
			              
			$content = array( "staff_id" ,"national_code" , "pfname" , "plname" ,  "account_no" , "pure_pay" ); 
			
			for($index=0; $index < count($dataTable); $index++)
			{
				$row = $dataTable[$index];
			
				$worksheet->write($index+1, 0, ($index+1));
				
				for ($i = 0; $i < count($content); $i++)
				{
					$val = "";
					/*if(!empty($this->columns[$i]->renderFunction))
						eval("\$val = " . $this->columns[$i]->renderFunction . "(\$row,\$row[\$this->columns[\$i]->field]);");
					else*/
					$val = $row[$content[$i]];					
                    $val = ( is_int($val) ) ? round($val) : $val ; 
					$worksheet->write($index+1, $i+1, $val);
				}
			}
		
			$workbook->close();
			
			header("Content-type: application/ms-excel");
			header("Content-disposition: inline; filename=excel.xls");
			
			echo file_get_contents("/tmp/temp.xls");
			unlink("/tmp/temp.xls");
			die();			
	}
				 
?>
<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
			text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#4D7094}
		.reportGenerator .header1 {color: white;font-weight: bold;background-color:#465E86}		
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
</style>
<?

	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:90%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>گزارش پرداخت  بانک ها"." </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		     . DateModules::shNow() . "<br>";		
	echo "</td></tr></table>";      
	
	echo '<table  class="reportGenerator" style="text-align: right;width:90%!important" cellpadding="4" cellspacing="0">
			 <tr class="header1">					
				<td colspan="8">'.$dataTable[0]['name'].'&nbsp;&nbsp;&nbsp;'. $monthTitle.'&nbsp;'.$_POST['pay_year'].'</td>
			 </tr>
			 <tr class="header">					
				<td>ردیف </td>
				<td>شماره شناسایی</td>
				<td>کد تفضیلی</td>
				<td>کد ملی</td>
				<td>نام </td>
				<td> نام خانوادگی </td>		
				<td>شماره حساب</td>
				<td>مانده قابل پرداخت</td>
			</tr>' ; 
		
	$sum = 0 ; 
	$BID = $dataTable[0]['bank_id'];
	for($i=0 ; $i < count($dataTable) ; $i++)
	    {
		if(($i> 0 && $i5 == 0 ) || $dataTable[$i]['bank_id'] != $BID )
		{		$BID = $dataTable[$i]['bank_id'] ;
				echo '</table><hr style="page-break-after:always; visibility: hidden"><br><br>';
				echo '<table  class="reportGenerator" style="text-align: right;width:90%!important" cellpadding="4" cellspacing="0">
						<tr class="header1">					
							<td colspan="8">'.$dataTable[$i]['name'].'&nbsp;&nbsp;&nbsp;'. $monthTitle.'&nbsp;'.$_POST['pay_year'].'</td>
						</tr>
						<tr class="header">					
							<td>ردیف </td>
							<td>شماره شناسایی</td>
							<td>کد تفضیلی</td>
							<td>کد ملی</td>
							<td>نام </td>
							<td> نام خانوادگی </td>		
							<td>شماره حساب</td>
							<td>مانده قابل پرداخت</td>
						</tr>' ; 
		}
	     echo " <tr>
					<td>".( $i + 1 )."</td>
					<td>".$dataTable[$i]['staff_id']."</td> 
					<td>".$dataTable[$i]['tafsili_id']."</td>
					<td>".$dataTable[$i]['national_code']."</td>
					<td>".$dataTable[$i]['pfname']."</td>
					<td>".$dataTable[$i]['plname']."</td>
					<td>".$dataTable[$i]['account_no']."</td>
					<td>".number_format($dataTable[$i]['pure_pay'], 0, '.', ',')."</td>			  
				</tr>" ; 
		 
		 $sum += $dataTable[$i]['pure_pay']; 
		
	    }
		echo "<tr style='font-weight:bold' ><td colspan='7' align='left' >جمع: </td><td colspan='2' >".number_format($sum, 0, '.', ',')."</td></tr>" ;
	   
	    echo "</table></center>" ;
		die();
				 
 }

?>

<script>
	BankPay.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	BankPay.prototype.showReport = function(type)
	{
		
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "bank_pay.php?show=true";
		this.form.action += type == "excel" ? "&excel=true" : "";	
		this.form.submit();	
		return;
	}
		
	function BankPay()
	{
		   
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش   لیست پرداخت بانکها",
			fieldDefaults: {
				labelWidth: 80
			},
			layout: {
				type: 'table',
				columns: 3
			},
			items :[{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "سال",
					name : "pay_year",
					allowBlank : false,
					width : 150
				},{
					xtype : "numberfield",
					hideTrigger : true,
					fieldLabel : "ماه",
					name : "pay_month",
					allowBlank : false,
					width : 150
				},
				{
					xtype : "numberfield",
					hideTrigger : true,
					width : 180,
					labelWidth: 110,
					fieldLabel : "شماره شناسایی",
					name : "staff_id"
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
									parentNode = BankPayObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
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
						title : "بانک",
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
									parentNode = BankPayObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
									elems = parentNode.getElementsByTagName("input");
									for(i=0; i<elems.length; i++)
									{
										if(elems[i].id.indexOf("chkBnk_") != -1)
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
						html:"نوع فرد : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
					
								"<input type=checkbox id='PT_1' name='PT_1' value=1 checked>&nbsp; هیئت علمی"+
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_2' name='PT_2' value=1 checked>&nbsp;  کارمند  " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_3' name='PT_3' value=1 checked>&nbsp;  روزمزدبیمه ای " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_5' name='PT_5' value=1 >&nbsp; قراردادی " +
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='PT_10' name='PT_10' value=1 >&nbsp;  بازنشسته"
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
											BankPayObject.filterPanel.down("[itemId=PayType]").setValue("1");										
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
					}	
					
			],
			buttons: [{
						text : "مشاهده گزارش",                                           
						iconCls : "report",
						handler: function(){BankPayObject.showReport('show');}
					  },{
						text : "خروجی Excel",
						iconCls : "excel",
						handler : function(){BankPayObject.showReport('excel');}
					}]			
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
						BankPayObject.filterPanel.down("[itemId=chkgroup]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkcostID_" + record.data.cost_center_id + " id=chkcostID_" + record.data.cost_center_id + " checked > " + record.data.title
						});
						
					});
										
				}}
			
		});
		
		new Ext.data.Store({
			fields : ["bank_id","name"],
			proxy : {
				type: 'jsonp',
				url : this.address_prefix + "../../../global/domain.data.php?task=searchBank",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad : true,
			listeners:{
				load : function(){
					this.each(function (record) {
						BankPayObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkBnk_" + record.data.bank_id + " id=chkBnk_" + record.data.bank_id + " checked > " + record.data.name
						});
						
					});
										
				}}
			
		});		
		
		
	}
	
	var BankPayObject = new BankPay();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div>	
	</form>
</center>
