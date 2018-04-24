<?php
//---------------------------
// programmer:	B.Mahdipour
// create Date:	96.06
//---------------------------

require_once("../../../header.inc.php");

if (isset($_REQUEST["show"])) {

	$keys = array_keys($_POST);
	$WhereCost = $WherePT = $WhereBT = $WhereEmpstate = "";
	$arr = "";

	//......................  وضعیت استخدامی ................

	for ($i = 0; $i < count($_POST); $i++) {


		if (strpos($keys[$i], "chkEmpState_") !== false) {
			$arr = preg_split('/_/', $keys[$i]);
			if (isset($arr[1]))
				$WhereEmpstate .= ($WhereEmpstate != "") ? "," . $arr[1] : $arr[1];
		}
	}
	
	$WhereUnit = "" ; 
	if(!empty($_POST['DomainID'])){
		$WhereUnit = " AND bj.UnitID = ".$_POST['DomainID'] ; 
	}
	
	$query = "		
					select  hp.pfname , hp.plname , pit.pay_year, pit.pay_month,
							if(p.pay_month > 6 , 30 , 31 ) item1 ,
							SUM(if( sit.salary_item_type_id = 1 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item2 ,
							SUM(if( sit.salary_item_type_id = 2 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item3 ,
							SUM(if( sit.salary_item_type_id = 9 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item4 ,
							SUM(if( sit.salary_item_type_id = 4 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item5 ,
							SUM(if( sit.salary_item_type_id = 5 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item6 ,
							SUM(if( sit.salary_item_type_id = 6 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item7 ,
							SUM(if( sit.salary_item_type_id = 12 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  ))   item9 ,
							SUM(if( sit.salary_item_type_id = 12 , (pit.param2 + pit.diff_param2 * pit.diff_value_coef) , 0  ))   item8,
							SUM(if( sit.salary_item_type_id = 7 , (pit.param1 + pit.diff_param1) , 0  )) item10 ,
							0 item11 /*حق سنوات*/,
							SUM(if( sit.salary_item_type_id = 8 , pit.param1  , 0  )) item12 ,
							SUM(if( sit.salary_item_type_id = 24 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item13 ,
							SUM(if( sit.salary_item_type_id = 7 , (pit.param2 + pit.diff_param2) , 0  )) item14 ,
							SUM(if( sit.salary_item_type_id = 7 , (pit.param3 + pit.diff_param3) , 0  )) item14_1 ,
							SUM(if( sit.salary_item_type_id = 7 , (pit.get_value + pit.diff_get_value * pit.diff_value_coef) , 0  )) item15 ,
							SUM(if( sit.salary_item_type_id = 8 , (pit.get_value + pit.diff_get_value * pit.diff_value_coef) , 0  )) item16 ,
							SUM(if( sit.salary_item_type_id = 11 , (pit.get_value + pit.diff_get_value * pit.diff_value_coef) , 0  ))  item17  ,
							SUM(if( sit.effect_type = 2 and sit.available_for in (1,2,4,5) and  sit.salary_item_type_id != 11 , (pit.get_value + pit.diff_get_value * pit.diff_value_coef) , 0 )) item18 ,
							SUM(if( sit.effect_type = 2 , (pit.get_value + pit.diff_get_value * pit.diff_value_coef) , 0 )) item21 , 							
							SUM(if( sit.salary_item_type_id in (17) , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item23 ,
							SUM(if( sit.salary_item_type_id in (13,14,15,16,21,22) , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0  )) item24 ,
							SUM(if( sit.effect_type = 1 , (pit.pay_value + pit.diff_pay_value * pit.diff_value_coef) , 0 )) item25 
							 

					from HRM_payment_items pit
							INNER JOIN HRM_salary_item_types sit ON (pit.salary_item_type_id = sit.salary_item_type_id)
							INNER JOIN HRM_payments p ON (  pit.pay_year = p.pay_year AND pit.pay_month = p.pay_month AND pit.staff_id = p.staff_id AND
															pit.payment_type = p.payment_type)
							INNER JOIN HRM_writs w ON ( p.writ_id = w.writ_id AND
														p.writ_ver = w.writ_ver AND p.staff_id = w.staff_id )
							INNER JOIN HRM_staff s ON s.staff_id = pit.staff_id
							INNER JOIN HRM_persons hp ON hp.PersonID = s.PersonID
							LEFT JOIN BSC_jobs bj ON bj.JobID = w.job_id
					
					where   pit.pay_year >= " . $_POST['from_pay_year'] . " AND
							pit.pay_year <= " . $_POST['to_pay_year'] . " AND
							pit.pay_month >= " . $_POST['from_pay_month'] . " AND					
							pit.pay_month <= " . $_POST['to_pay_month'] . " AND			
							pit.payment_type = " . $_POST['PayType'];

	$query .= ($WhereEmpstate != "" ) ? " AND w.emp_state in (" . $WhereEmpstate . ") " : "";
	$query .= $WhereUnit ; 

	$query .= "  group by hp.personID , pay_year, pay_month 
				 order by hp.personID  
				 ";

	$dataTable = PdoDataAccess::runquery($query);

	//echo PdoDataAccess::GetLatestQueryString()  ; 
	//die() ; 
	?>
	<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
						  text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#4D7094}
		.reportGenerator .header1 {color: white;font-weight: bold;background-color:#465E86}		
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
	</style>
	<?php
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl"><center>';

	//.........................................
	echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
	<td width=10px><img src='/framework/icons/logo.jpg' width='100px'></td>
	<td align='center' style='font-family:b titr;font-size:15px'>گزارش لیست حقوق  و دستمزد ماهانه" .
	"<br><br> " . DateModules::GetMonthName($dataTable[0]['pay_month']) . ' ماه &nbsp;&nbsp;' . $dataTable[0]['pay_year'] . " </td>				
	<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : "
	. DateModules::shNow() . "<br>";
	echo "</td></tr></table>";



	echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">

	<tr class="header">					
	<td>نام و نام خانوادگی</td>	
	<td>کارکرد روز</td>
	<td>حقوق پایه</td>
	<td>فوق العاده جذب</td>
	<td>پایه سنواتی</td>
	<td>تفاوت تطبیق</td>
	<td>اقلام مصرفی</td>
	<td>حق مسکن</td>
	<td>حق اولاد</td>
	<td> سایر اضافات</td>
	<td>اضافه کار</td>
	<td>مبلغ اضافه کار</td>
	<td>حق ماموریت</td>
	<td>جمع مشمول کسر بیمه</td>
	<td>حق سنوات</td>
	<td>جمع مشمول مالیات</td>	
	<td>بیمه سهم کارفرما</td>
	<td> بیمه بیکاری</td>
	<td>بیمه سهم مجری</td>
	<td>مالیات</td>
	<td>بیمه تکمیلی</td>
	<td>اقساط</td>
	<td>جمع کسورات</td>
	<td>قابل پرداخت</td>			 
	</tr>';


	$Item_1 = $Item_2 = $Item_3 = $Item_4 = $Item_5 = $Item_6 = $Item_7 = $Item_8 = $Item_9 = $Item_10 = 0;
	$Item_11 = $Item_12 = $Item_13 = $Item_14 = $Item_14_1 = $Item_15 = $Item_16 = $Item_17 = 0; 
	$Item_18 = $Item_19 = $Item_20 = $Item_23 = $Item_24 = $Item_25 = 0;

	$PY = $dataTable[0]['pay_year'];
	$PM = $dataTable[0]['pay_month'];
	$SumGetVal = $SumPayVal = 0 ; 
	for ($i = 0; $i < count($dataTable); $i++ ) {		
		$SumGetVal = $dataTable[$i]['item18'] + $dataTable[$i]['item17'] +$dataTable[$i]['item16'] +$dataTable[$i]['item15'] ; 
		$SumPayVal = $dataTable[$i]['item2'] + $dataTable[$i]['item3'] +$dataTable[$i]['item4'] + $dataTable[$i]['item5'] 
				   + $dataTable[$i]['item6'] + $dataTable[$i]['item7']+ $dataTable[$i]['item23'] + $dataTable[$i]['item24'] 
				   + $dataTable[$i]['item9'] ;
	echo " <tr>					
	<td>" . $dataTable[$i]['pfname'].' '.$dataTable[$i]['plname']. "</td> 
	<td>" . $dataTable[$i]['item1'] . "</td>	
	<td>" . number_format($dataTable[$i]['item2'] ,0, '.', ',') . "</td>					
	<td>" . number_format($dataTable[$i]['item3'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item4'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item5'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item6'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item7'] ,0, '.', ',') . "</td>
	<td>" . number_format($dataTable[$i]['item23'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item24'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item8'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item9'] ,0, '.', ',') . "</td>
	<td>" . number_format($dataTable[$i]['item13'] ,0, '.', ',') . "</td>	
	<td>" . number_format($dataTable[$i]['item10'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item11'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item12'] ,0, '.', ',') . "</td>					
	<td>" . number_format($dataTable[$i]['item14'] ,0, '.', ',') . "</td>
	<td>" . number_format($dataTable[$i]['item14_1'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item15'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item16'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item17'] ,0, '.', ',') . "</td>		
	<td>" . number_format($dataTable[$i]['item18'] ,0, '.', ',') . "</td>	
	<td>" . number_format($SumGetVal ,0, '.', ',') . "</td>	
	<td>" . number_format(($SumPayVal - $SumGetVal),0, '.', ',') . "</td>
	</tr>";
 
	$Item_1 += $dataTable[$i]['item1'];
	$Item_2 += $dataTable[$i]['item2'];
	$Item_3 += $dataTable[$i]['item3'];
	$Item_4 += $dataTable[$i]['item4'];
	$Item_5 += $dataTable[$i]['item5'];
	$Item_6 += $dataTable[$i]['item6'];
	$Item_7 += $dataTable[$i]['item7'];
	$Item_8 += $dataTable[$i]['item8'];
	$Item_9 += $dataTable[$i]['item9'];
	$Item_10 += $dataTable[$i]['item10'];
	$Item_11 += $dataTable[$i]['item11'];
	$Item_12 += $dataTable[$i]['item12'];
	$Item_13 += $dataTable[$i]['item13'];
	$Item_14 += $dataTable[$i]['item14'];
	$Item_14_1 += $dataTable[$i]['item14_1'];
	$Item_15 += $dataTable[$i]['item15'];
	$Item_16 += $dataTable[$i]['item16'];
	$Item_17 += $dataTable[$i]['item17'];
	$Item_18 += $dataTable[$i]['item18'];
	$Item_23 += $dataTable[$i]['item23'];
	$Item_24 += $dataTable[$i]['item24'];
	$Item_19 += $dataTable[$i]['item21'] ;
	$Item_20 += $dataTable[$i]['item25'] - $dataTable[$i]['item21']  ;

	}
	echo '<tr>
	<td>جمع : </td>
	<td>'.$Item_1.'</td>
	<td>' . number_format($Item_2, 0, '.', ',') . '</td>
	<td>' . number_format($Item_3, 0, '.', ',') . '</td>
	<td>' . number_format($Item_4, 0, '.', ',') . '</td>
	<td>' . number_format($Item_5, 0, '.', ',') . '</td>
	<td>' . number_format($Item_6, 0, '.', ',') . '</td>
	<td>' . number_format($Item_7, 0, '.', ',') . '</td>
	<td>' . number_format($Item_23, 0, '.', ',') . '</td>
	<td>' . number_format($Item_24, 0, '.', ',') . '</td>
	<td>' . number_format($Item_8, 0, '.', ',') . '</td>
	<td>' . number_format($Item_9, 0, '.', ',') . '</td>
	<td>' . number_format($Item_13, 0, '.', ',') . '</td>	
	<td>' . number_format($Item_10, 0, '.', ',') . '</td>
	<td>' . number_format($Item_11, 0, '.', ',') . '</td>
	<td>' . number_format($Item_12, 0, '.', ',') . '</td>	
	<td>' . number_format($Item_14, 0, '.', ',') . '</td>
	<td>' . number_format($Item_14_1, 0, '.', ',') . '</td>
	<td>' . number_format($Item_15, 0, '.', ',') . '</td>
	<td>' . number_format($Item_16, 0, '.', ',') . '</td>
	<td>' . number_format($Item_17, 0, '.', ',') . '</td>
	<td>' . number_format($Item_18, 0, '.', ',') . '</td>
	<td>' . number_format($Item_19, 0, '.', ',') . '</td>
	<td>' . number_format($Item_20, 0, '.', ',') . '</td>							
	</tr>';

	echo "</table>";
	echo '<br><table style="border:2px groove #9BB1CD;border-collapse:collapse;width:100%">
	<tr style="height:200px">
	<td> &nbsp;امور مالی</td>
	<td> &nbsp; مدیر عامل</td>
	<td> &nbsp; رییس هیات مدیره</td> </tr></table></center>' ; 
	die();
	}
	?>

	<script>
		PayItemList.prototype = {
			TabID: '<?= $_REQUEST["ExtTabID"] ?>',
			address_prefix: "<?= $js_prefix_address ?>",
		get: function (elementID) {
			return findChild(this.TabID, elementID);
		}
	};

	PayItemList.prototype.showReport = function (type)
	{

		if (!this.filterPanel.getForm().isValid())
			return;

		this.form = this.get("mainForm");
		this.form.target = "_blank";
		this.form.method = "POST";
		this.form.action = this.address_prefix + "payment_item_list.php?show=true";
		this.form.action += type == "excel" ? "&excel=true" : "";
		this.form.submit();
		return;
	}

	function PayItemList()
	{

		this.filterPanel = new Ext.form.Panel({
			renderTo: this.get('DivInfo'),
			width: 780,
			titleCollapse: true,
			frame: true,
			collapsible: true,
			bodyStyle: "padding:5px",
			title: "تنظیم گزارش لیست حقوق به تفکیک قلم حقوقی",
			fieldDefaults: {
				labelWidth: 110
			},
			layout: {
				type: 'table',
				columns: 2
			},
			items: [{
					xtype: "numberfield",
					hideTrigger: true,
					fieldLabel: "سال از",
					name: "from_pay_year",
					allowBlank: false,
					width: 200
				}, {
					xtype: "numberfield",
					hideTrigger: true,
					fieldLabel: "ماه از",
					name: "from_pay_month",
					allowBlank: false,
					width: 200
				}, {
					xtype: "numberfield",
					hideTrigger: true,
					fieldLabel: "سال تا",
					name: "to_pay_year",
					allowBlank: false,
					width: 200
				}, {
					xtype: "numberfield",
					hideTrigger: true,
					fieldLabel: "ماه تا",
					name: "to_pay_month",
					allowBlank: false,
					width: 200
				},
				{
					xtype: 'fieldset',
					title: "وضعیت استخدامی",
					colspan: 3,
					style: 'background-color:#DFEAF7',
					width: 700,
					fieldLabel: 'Auto Layout',
					itemId: "chkgroup2",
					collapsible: true,
					collapsed: true,
					layout: {
						type: "table",
						columns: 4,
						tableAttrs: {
							width: "100%",
							align: "center"
						},
						tdAttrs: {
							align: 'right',
							width: "۱6%"
						}
					},
					items: [{
							xtype: "checkbox",
							boxLabel: "همه",
							checked: true,
							listeners: {
								change: function () {
									parentNode = PayItemListObject.filterPanel.down("[itemId=chkgroup2]").getEl().dom;
									elems = parentNode.getElementsByTagName("input");
									for (i = 0; i < elems.length; i++)
									{
										if (elems[i].id.indexOf("chkEmpState_") != -1)
											elems[i].checked = this.getValue();
									}
								}
							}
						}]
				},
				{
					xtype: "combo",
					colspan: 3,
					store: new Ext.data.Store({
						fields: ["InfoID", "InfoDesc"],
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + "../../../global/domain.data.php?task=searchPayType",
							reader: {
								root: 'rows',
								totalProperty: 'totalCount'
							}
						}
						,
						autoLoad: true,
						listeners: {
							load: function () {
								PayItemListObject.filterPanel.down("[itemId=PayType]").setValue("1");
							}
						}

					}),
					valueField: "InfoID",
					displayField: "InfoDesc",
					hiddenName: "PayType",
					itemId: "PayType",
					fieldLabel: "نوع پرداخت&nbsp;",
					listConfig: {
						loadingText: 'در حال جستجو...',
						emptyText: 'فاقد اطلاعات',
						itemCls: "search-item"
					},
					width: 300
				},
				{
					xtype: "trigger",
					fieldLabel: 'حوزه فعالیت',
					name: 'DomainDesc',
					triggerCls: 'x-form-search-trigger',
					onTriggerClick: function () {
						PayItemListObject.ActDomainLOV();
					}
				},
				{
					xtype : "hidden",
					name : "DomainID",
					colspan : 2
				}

			],
			buttons: [{
					text: "مشاهده گزارش",
					iconCls: "report",
					handler: function () {
						PayItemListObject.showReport('show');
					}
				}
			]
		});


		new Ext.data.Store({
			fields: ["InfoID", "InfoDesc"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + "../../../global/domain.data.php?task=searchEmpState",
				reader: {
					root: 'rows',
					totalProperty: 'totalCount'
				}
			},
			autoLoad: true,
			listeners: {
				load: function () {
					this.each(function (record) {
						PayItemListObject.filterPanel.down("[itemId=chkgroup2]").add({
							xtype: "container",
							html: "<input type=checkbox name=chkEmpState_" + record.data.InfoID + " id=chkEmpState_" + record.data.InfoID + " checked > " + record.data.InfoDesc
						});

					});

				}}

		});


	}

	var PayItemListObject = new PayItemList();

	PayItemList.prototype.ActDomainLOV = function (record) {

		if (!this.DomainWin)
		{
			this.DomainWin = new Ext.window.Window({
				autoScroll: true,
				width: 480,
				height: 550,
				title: "حوزه فعالیت",
				closeAction: "hide",
				loader: {
					url: this.address_prefix + "../../../../framework/baseInfo/units.php?mode=adding",
					scripts: true
				}
			});

			Ext.getCmp(this.TabID).add(this.DomainWin);
		}

		this.DomainWin.show();

		this.DomainWin.loader.load({
			params: {
				ExtTabID: this.DomainWin.getEl().dom.id,
				parent: "PayItemListObject.DomainWin",
				MenuID: this.MenuID,
				selectHandler: function (id, name) {
					PayItemListObject.filterPanel.down("[name=DomainDesc]").setValue(name);
					PayItemListObject.filterPanel.down("[name=DomainID]").setValue(id);
					
				}
			}
		});


	}



</script>
<center>
	<form id="mainForm">
		<br>
		<div id="DivInfo"></div>	
	</form>
</center>
