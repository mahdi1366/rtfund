<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	93.06
//---------------------------

require_once("../../../header.inc.php");

	function copyDbfFiles() {
		$from_path = '/var/www/sadaf/HumanResources/dbf/';
		$to_path ="../../../HRProcess/";

		$this_path = getcwd();

		if(!is_dir($to_path)) {
			mkdir($to_path, 0775);
		}

		if (is_dir($from_path)) {
			chdir($from_path);
			$handle=opendir('.');
			while (($file = readdir($handle))!==false) {
				if (($file != ".") && ($file != "..")) {
					if (is_file($file)) {
					chdir($this_path);
					copy($from_path.$file, $to_path.$file);
					chdir($from_path);
					}
				}
			}
		closedir($handle);
		}
		chdir($this_path);
	}

	function get_stage_code($pay_year , $pay_month , $pay_type){
		$qry = " SELECT COUNT(distinct pay_year , pay_month , payment_type) rcount
				 FROM  payments
				 WHERE pay_year >= 1386 AND pay_year<=".$pay_year." AND pay_month<=".$pay_month." AND payment_type<=".$pay_type ; 

		$res = PdoDataAccess::runquery($qry);
		return str_pad(100+$res[0]['rcount'] ,4,"0",STR_PAD_LEFT);
	}
	
	
if (isset($_REQUEST["show"]))
{
	
	$keys = array_keys($_POST);
	$WhereCost = $WherePT =	$WhereBT = $WhereEmpstate = "" ;
	$arr = "" ;
	
	//...................... مراکز هزینه و وضعیت استخدامی ................
	
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
	
	if(isset($_POST['PT_10']) && $_POST['PT_10']== 1) 
	   	$WherePT .= ($WherePT !="" ) ?  " ,10 " :  "10" ; 
	
	//........... بانکها..........................
	if(isset($_POST['BI_1000']) && $_POST['BI_1000']== 1) 
	   	$WhereBT .= ($WhereBT !="" ) ?  " ,1000 " :  "1000" ; 
	
	if(isset($_POST['BI_2007']) && $_POST['BI_2007']== 1) 
	   	$WhereBT .= ($WhereBT !="" ) ?  " ,2007 " :  "2007 " ; 
		
	//............................................
	
	$staffID = (isset($_POST['staff_id']) && $_POST['staff_id'] > 0) ? $_POST['staff_id'] : " " ; 
	
	copyDbfFiles() ;
	
	if($_POST['RepFormat'] == 0 ) // .... حقوق 
	{
					
		$query = "  select pa.account_no ,
       						   pa.pay_year ,
       						   pa.pay_month ,
       						   pa.payment_type ,
       						   s.staff_id ,
       						   s.account_no ,
       						   b.branch_code ,
         					   SUM(pai.pay_value + pai.diff_pay_value * pai.diff_value_coef - pai.get_value - pai.diff_get_value * pai.diff_value_coef) amount

					from payment_items pai
						 INNER JOIN  payments  pa
								ON (pai.staff_id     = pa.staff_id  AND
									pai.pay_year     = pa.pay_year  AND
									pai.pay_month    = pa.pay_month  AND
									pai.payment_type = pa.payment_type)
						 INNER JOIN staff s 
							  ON pai.staff_id = s.staff_id 
						 INNER JOIN banks b
								ON b.bank_id = pa.bank_id 
						 LEFT JOIN writs w
							  ON s.last_writ_id = w.writ_id AND s.last_writ_ver = w.writ_ver AND s.staff_id = w.staff_id

					where pai.pay_year = ".$_POST['pay_year']." AND
						  pai.pay_month = ".$_POST['pay_month']." AND
						  pai.payment_type = ".$_POST['PayType'] ; 

						  
	    $query .= ($staffID > 0 ) ? " AND pai.staff_id=".$staffID : "" ; 
		$query .= ($WhereCost !="" ) ? " AND pai.cost_center_id in (".$WhereCost.") " : "" ; 		
		$query .= ($WhereEmpstate !="" ) ? " AND w.emp_state in (".$WhereEmpstate.") " : "" ; 
		$query .= ($WherePT !="" ) ? " AND s.person_type in (".$WherePT.") " : "" ; 
		$query .= ($WhereBT  !="" ) ? " AND  pa.bank_id in (".$WhereBT.") " : "" ; 
		
		$query .= " group by    pa.account_no,
       						    pa.pay_year,
       						    pa.pay_month,
       						    pa.payment_type ,
         					    s.staff_id , 
         					    s.personel_no ,
         					    b.branch_code 	" ; 
		$dt = PdoDataAccess::runquery($query);		
		
		$output = "" ;
		$sum =0 ;
		for($i=0;$i< count($dt);$i++)
		{
					
			$rowno = str_pad(($i+1), 5,"0",STR_PAD_LEFT);
			$acc = str_pad($dt[$i]['account_no'],13,"0",STR_PAD_LEFT);
			$amn= str_pad($dt[$i]['amount'],15,"0",STR_PAD_LEFT);
			$record = $rowno.$acc.$amn.'000000000000000'."\r\n";

			$output .= $record ;
			$sum+= $dt[$i]['amount']; 				
		}
		
		list($year,$month,$day) =  preg_split('/\//',DateModules::shNow()); 					
		$list_date = substr($year,2).str_pad($month,2,"0",STR_PAD_LEFT).str_pad($day,2,"0",STR_PAD_LEFT);
		$sum= str_pad($sum,15,"0",STR_PAD_LEFT);
		$count = $i;
		$count = str_pad($count,5,"0",STR_PAD_LEFT);
		$stage_code = get_stage_code($_POST['pay_year'],$_POST['pay_month'],$_POST['PayType']) ;
		$list_header = $dt[0]['branch_code'].'2685'.$stage_code.$list_date.$sum.$count.'0000000000'; 
		$file = '2685'.$dt[0]['branch_code'].'_'.substr($stage_code,1,3);
		
		$filename = "../../../HRProcess/".$file.".DAT";	
		$fp=fopen($filename,'w');
		fwrite($fp , $list_header."\r\n".$output);
		fclose($fp);
	
		header('Content-disposition: filename="'.$file.'.DAT"');
		header('Content-type: application/file');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		echo file_get_contents("../../../HRProcess/".$file.".DAT");
		die() ; 
		
	}
	elseif($_POST['RepFormat'] == 1 ) // ..... وام
	{
	
		$query = "  select ps.loan_no,
						   pa.pay_year,
						   pa.pay_month,
						   ba.branch_code,
						   p.pfname,
						   p.plname,
						   pai.get_value + pai.diff_get_value * pai.diff_value_coef as amount

					from payment_items pai
						 INNER JOIN  payments  pa
							   ON ( pai.staff_id     = pa.staff_id  AND
									pai.pay_year     = pa.pay_year  AND
									pai.pay_month    = pa.pay_month  AND
									pai.payment_type = pa.payment_type)
						 INNER JOIN person_subtracts ps
							   ON (pai.param1 = 'LOAN' AND pai.param2 = ps.subtract_id AND ps.subtract_type = ".LOAN.") 
						 INNER JOIN banks ba
							   ON (ba.bank_id = ps.bank_id)
						 INNER JOIN staff s 
							  ON pai.staff_id = s.staff_id 
						 INNER JOIN persons p
							  ON(p.personID=s.personID)
						 LEFT JOIN writs w
							  ON s.last_writ_id = w.writ_id AND s.last_writ_ver = w.writ_ver AND s.staff_id = w.staff_id
							  
					where pai.pay_year = ".$_POST['pay_year']." AND
						  pai.pay_month = ".$_POST['pay_month']." AND
						  pai.payment_type = ".$_POST['PayType']." AND ps.bank_id IS NOT NULL 
AND pai.param2 not in ( 
270376904	,
270376908	,
270376909	,
270376911	,
270376912	,
270376914	,
270376915	,
270376916	,
270376917	,
270376929	,
270376930	,
270376931	,
270376933	,
270376934	,
270376935	,
270376936	,
270376937	,
270376938	,
270376939	,
270376940	,
270376941	,
270376942	,
270376944	,
270376945	,
270376946	,
270376948	,
270376950	,
270376958	,
270376960	,
270376961	,
270376962	,
270376963	,
270376965	, 178519943 , 270366680 , 270377067 , 180021184 , 270374973 , 270374972 , 270379013  ) " ; 
						  
		$query .= ($staffID > 0 ) ? " AND pai.staff_id=".$staffID : "" ; 
		$query .= ($WhereCost !="" ) ? " AND pai.cost_center_id in (".$WhereCost.") " : "" ; 		
		$query .= ($WhereEmpstate !="" ) ? " AND w.emp_state in (".$WhereEmpstate.") " : "" ; 
		$query .= ($WherePT !="" ) ? " AND s.person_type in (".$WherePT.") " : "" ; 
		$query .= ($WhereBT  !="" ) ? " AND  ps.bank_id in (".$WhereBT.") " : "" ; 
							  
		$dt = PdoDataAccess::runquery($query);
//echo $query ; die(); 
		list($year,$month,$day) =  preg_split('/\//',DateModules::shNow()); 	
				
		$date = substr($year,2,2).$month.$day ;
	
		for($i=0;$i<count($dt);$i++)
		{
			$record = array($dt[$i]['branch_code'],
							$dt[$i]['loan_no'],
							$dt[$i]['amount'],
							$date);
			
			$db_path = "../../../HRProcess/BMIPYR.DBF";
			$dbi = dbase_open($db_path,2);
			dbase_add_record($dbi, $record);
			dbase_close($dbi);		
		}
		$file = "BMIPYR.DBF" ; 
		header('Content-disposition: filename="'.$file.'"');
		header('Content-type: application/file');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		echo file_get_contents("../../../HRProcess/".$file);
		die() ; 
					
	}
				 
}

?>

<script>
	MelliDiskette.prototype = {
		TabID : '<?= $_REQUEST["ExtTabID"] ?>',
		address_prefix : "<?= $js_prefix_address ?>",		
		get : function(elementID){
			return findChild(this.TabID, elementID);
		}
	};
	
	MelliDiskette.prototype.showReport = function(btn, e)
	{
		if(!this.filterPanel.getForm().isValid())
			return;
			
		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action =  this.address_prefix + "melli_diskette.php?show=true";
	
		this.form.submit();
		this.get("excel").value = "";
		return;
	}
	
	function MelliDiskette()
	{
		   
		this.filterPanel = new Ext.form.Panel({
			renderTo : this.get('DivInfo'),
			width : 780,
			titleCollapse : true,
			frame : true,
			collapsible : true,
			bodyStyle : "padding:5px",
			title :"تنظیم گزارش تهیه دیسکت حقوق/وام بانک ملی",
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
									parentNode = MelliDisketteObject.filterPanel.down("[itemId=chkgroup]").getEl().dom;
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
									parentNode = MelliDisketteObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
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
						colspan:3,										
						xtype: 'container',  
						style : "padding:5px",
						html:"بانک &nbsp; &nbsp; : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
					
								"<input type=checkbox id='BI_1000' name='BI_1000' value=1 >&nbsp; ملی پردیس"+
								"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
								"<input type=checkbox id='BI_2007' name='BI_2007' value=1 checked>&nbsp;  ملی سیبا  " 
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
											MelliDisketteObject.filterPanel.down("[itemId=PayType]").setValue("1");										
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
						colspan:4,										
						xtype: 'container',                    											
						html:" نوع گزارش : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='TRepFormat_0' name='RepFormat' value='0' checked>&nbsp;  حقوق "+
							 "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
							 "<input type=radio id='TRepFormat_1' name='RepFormat' value='1'>&nbsp; وام " 
					}		
					
			],
			buttons :  [ {
							text : "تهیه دیسکت ",
							handler :  Ext.bind(this.showReport,this),
							iconCls : "save"                                
						},{
						iconCls : "clear",
						text : "پاک کردن فرم",
						handler : function(){
						this.up("form").getForm().reset();
						MelliDisketteObject.get("mainForm").reset();
					}
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
						MelliDisketteObject.filterPanel.down("[itemId=chkgroup]").add({
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
						MelliDisketteObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype : "container",
							html : "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.Title
						});
						
					});
										
				}}
			
		});		
		
		
	}
	
	var MelliDisketteObject = new MelliDiskette();
	
	
		
</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div><br>
		<input type="hidden" name="excel" id="excel">
	</form>
</center>
