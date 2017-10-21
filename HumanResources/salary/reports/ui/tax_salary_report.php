<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	96.05
//---------------------------
require_once("../../../header.inc.php");

if (!isset($_REQUEST["show"]))
	
	require_once '../js/tax_salary_report.js.php';
	require_once "ReportGenerator.class.php";

$whr = " where (1=1) ";
$whr2 = "";
$whr3 = "";
$khazaneh = "";
$kh = "";
$whrBI = $whrBI2 = "";

if (isset($_REQUEST["show"])) {

	$whereParam = array();
	
	if(!empty($_POST["PTY"]))
	{
		
			$whr .= " AND s.person_type = :pt ";   		
			$whereParam[":pt"] = $_POST["PTY"] ; 
	
	}

$taxJoin = "" ; 
$from_value = " 20000000 " ; 

$month_start = DateModules::shamsi_to_miladi($_POST["pay_year"] . "/" . $_POST["pay_month"] . "/01");
	if($_POST["pay_month"] < 7 )
	   $day = "31"; 
	
	else if($_POST["pay_month"] < 12  )  
	   $day = "30";
	
	else   $day = "29";
		
	$month_end = DateModules::shamsi_to_miladi($_POST["pay_year"] . "/" . $_POST["pay_month"] . "/".$day);
	
        if($_POST["PayType"] != 14 ) {
	           $whr .= " AND ( ( pit.param1 >= tbl3.from_value AND pit.param1 <= tbl3.to_value ) OR  tbl3.from_value IS NULL ) " ; 

 $taxJoin  = " left join (	SELECT staff_id , sth.tax_table_type_id , tti.from_value , tti.to_value
														FROM HRM_staff_tax_history sth inner join HRM_tax_table_types ttt
																								on sth.tax_table_type_id = ttt.tax_table_type_id
																				   inner join  HRM_tax_tables tt
																								on ttt.tax_table_type_id = tt.tax_table_type_id and 																								   
																								   from_date <=  '".$month_start."' and
																								   ( to_date >= '".$month_end."' or to_date is null or to_date ='0000-00-00')
																				   inner join HRM_tax_table_items tti
																								on  tti.tax_table_id = tt.tax_table_id

														WHERE sth.start_date <= '".$month_start."' and
															( sth.end_date >= '".$month_start."' or sth.end_date is null or
															  sth.end_date = '0000-00-00')

													) tbl3 on s.staff_id = tbl3.staff_id " ; 
	
	$from_value = " tbl3.from_value " ; 

	}
	if($_POST["PayType"] == 1 ) 
	{
		$SItmWhr = " 1,2,3,4,9 " ;
	}
	elseif($_POST["PayType"] == 3) 
	{	
		$SItmWhr = "39,152,639,752"; //............ اضافه کار
	}
	
	elseif($_POST["PayType"] == 14)
	{
		$SItmWhr = " 40 "; //............. حق التدریس
	}
	
	elseif($_POST["PayType"] == 12 )
	{
		$SItmWhr = " 10389 "; //..............تالیف و ویراستاری 
	}

        elseif($_POST["PayType"] == 2 )
	{
		$SItmWhr = " 163,164,764,165 "; //..............عیدی و پاداش 
	}
	
	
	$query = " select  p.national_code item_1 , 
					   if(tbl5.MonthPeresence is null , 1 , (tbl5.MonthPeresence + 1 )) item_3 ,
					   tbl7.staff_id item_4 , 
					   s.work_start_date   item_7 , 
					   tbl7.execute_date item_8 ,
					   janbaz.devotion_type item_9_1 , 
					   ShohadaChild.devotion_type item_9_2 ,
					   Azadeh.devotion_type item_9_3 , 
					   tbl1.sv  item_11 , 
					   tbl1.diff_sv item_12 , 
					   tbl2.sv  item_22 , 
					   tbl6.sv item_23 , 
					   0 item_24 ,
	 			       tbl6.diff_sv  item_25 ,
					   0 item_26 ,
					   0 item_27 , 0 item_28 , 0 item_29 ,0 item_30 ,0 item_31 ,0 item_32 , 
					   pit.get_value item_34  ,
					   p.nationality item_35 , 
					   p.pfname item_36 , 
					   p.plname item_37 , 
					   co.country_id item_38 ,
					   s.staff_id item_39 ,
					   bi.InfoDesc item_40 , 
					   po.title item_41 , 
					   sih.insure_include item_42_1 , 
					   sih.service_include item_42_2 , 
					   p.insure_no item_43_1  ,
					   p.personid item_43_2 , 
					   p.postal_code1 item_44 ,
					   p.address1 item_45,
					   w.emp_state item_46 ,
					   w.worktime_type  item_48 , 
					   p.mobile_phone item_49 ,
					   p.email item_50
	 
						 
						from HRM_persons p inner join HRM_staff s
												on p.personid = s.personid 
									   inner join HRM_writs w
												on s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver  and s.staff_id = w.staff_id
									   inner join BaseInfo bi
												on  w.education_level = bi.InfoID and  bi.typeid = 56
									   left join HRM_position po
												on po.post_id = w.post_id
									   /*left join job_fields jf
												on (po.jfid = jf.jfid)
									   left join job_subcategory jsc
												on ((jf.jsid = jsc.jsid) AND (jf.jcid=jsc.jcid))												
									   left join job_category jc
												on (jsc.jcid = jc.jcid)*/
												
									   left join HRM_staff_include_history sih
												on sih.staff_id = s.staff_id and
												   start_date <= '".$month_start."' and
												 ( end_date > '".$month_start."' or end_date is null or end_date = '0000-00-00' )
													 
									   left join ( SELECT personid , devotion_type
														FROM HRM_person_devotions
																WHERE devotion_type in (2)
												   GROUP BY personid

												 ) Azadeh 												 
												on  Azadeh.personid = p.personid
										left join ( SELECT personid , devotion_type
														FROM HRM_person_devotions
																WHERE devotion_type in (3)
												   GROUP BY personid

												 ) janbaz 												 
												on  janbaz.personid = p.personid							
										
										left join ( SELECT personid , devotion_type
														FROM HRM_person_devotions
																WHERE devotion_type in (5) and personel_relation in (5,6)
												   GROUP BY personid

												 ) ShohadaChild 												 
												on  ShohadaChild.personid = p.personid	
												
										left join HRM_countries co 
												on p.country_id = co.country_id
												
										left join (										
													select staff_id ,count(*)  MonthPeresence
														from HRM_payments
																where pay_year = ".$_POST["pay_year"]." and 
																	  pay_month < ".$_POST["pay_month"]."  and 
																	  payment_type = ".$_POST["PayType"]."

													group by staff_id
													) tbl5 on  s.staff_id = tbl5.staff_id

										inner join ( select pit1.staff_id , sum(pay_value) sv ,sum(diff_pay_value * diff_value_coef) diff_sv
														from HRM_payment_items pit1 inner join HRM_staff s
																on pit1.staff_id = s.staff_id

															where pay_year = ".$_POST["pay_year"]." and pay_month = ".$_POST["pay_month"]." and 
															      payment_type = ".$_POST["PayType"]." and
																  salary_item_type_id in (".$SItmWhr.")

													 group by staff_id

													)   tbl1
												on s.staff_id = tbl1.staff_id

										left join ( select pit2.staff_id , sum(pay_value + diff_pay_value * diff_value_coef) sv
														from HRM_payment_items pit2 inner join HRM_staff s
																					on pit2.staff_id = s.staff_id
																			inner join HRM_salary_item_types sit
																					on sit.salary_item_type_id = pit2.salary_item_type_id and tax_include = 1
															where pay_year = ".$_POST["pay_year"]." and pay_month = ".$_POST["pay_month"]." and payment_type = ".$_POST["PayType"]." and
															      pit2.salary_item_type_id  in ( 12 )

															group by staff_id
													) tbl2

												on s.staff_id = tbl2.staff_id
										
						left join ( select pit2.staff_id , sum(pay_value) sv , sum(diff_pay_value * diff_value_coef) diff_sv
										from HRM_payment_items pit2 inner join HRM_staff s
																	on pit2.staff_id = s.staff_id
															inner join HRM_salary_item_types sit
																	on sit.salary_item_type_id = pit2.salary_item_type_id and tax_include = 1
											where pay_year = ".$_POST["pay_year"]." and pay_month = ".$_POST["pay_month"]." and payment_type = ".$_POST["PayType"]." and
													pit2.salary_item_type_id not in ( 12 , ".$SItmWhr." )

											group by staff_id
									) tbl6

								on s.staff_id = tbl6.staff_id
								left join (
										select staff_id , emp_mode , execute_date
										from HRM_writs
										where state = 1 and execute_date >='".$month_start."' and execute_date <= '".$month_end."' and 
											  emp_mode in (3,4) and  person_type in (1,2,3,5)
											) tbl7 
												on s.staff_id = tbl7.staff_id

										$taxJoin
										left join (
											select staff_id , sum(get_value) gv

											from HRM_payment_items

											where pay_year = ".$_POST["pay_year"]." and 
												  pay_month = ".$_POST["pay_month"]." and  
												  payment_type = ".$_POST["PayType"]." and 
												  salary_item_type_id in (11)

											group by staff_id
										)tbl4 on s.staff_id = tbl4.staff_id

										inner join HRM_payment_items pit

												on  pit.staff_id = s.staff_id and pit.pay_year = ".$_POST["pay_year"]." and
													pit.pay_month =".$_POST["pay_month"]."  and pit.payment_type = ".$_POST["PayType"]." and
													pit.salary_item_type_id in (8) 

						".$whr ;
	
		$dataTable = PdoDataAccess::runquery($query, $whereParam);
		
			//echo PdoDataAccess::GetLatestQueryString() ; die() ;
	
		
		$record=$WPrecord ="";
		$Sitem_4 = 0 ; 
		$Sitem_5 = 0 ;
		$Sitem_6 = 0 ;
		$Sitem_9 = 0 ;	
	
		for($i=0 ; $i < count($dataTable) ; $i++)
		{	
		 $dataTable[$i]["item_7"] = '2017-03-21'  ; 
			//..........................New Version.................................
			list($swyear,$swmonth,$swday) = preg_split('/[\/]/',DateModules::miladi_to_shamsi($dataTable[$i]["item_7"]));				
			$dataTable[$i]["item_7"] =  $swyear.$swmonth.$swday ;
			
			
			if($dataTable[$i]["item_9_1"] > 0 ||  $dataTable[$i]["item_9_1"] != NULL )
			{
				$dataTable[$i]["item_9"] = 2 ;
			}
			elseif($dataTable[$i]["item_9_2"] > 0 ||  $dataTable[$i]["item_9_2"] != NULL )
			{
				$dataTable[$i]["item_9"] = 3 ;
			}
			elseif($dataTable[$i]["item_9_3"] > 0 ||  $dataTable[$i]["item_9_3"] != NULL )
			{
				$dataTable[$i]["item_9"] = 4 ;
			}
			else 
				$dataTable[$i]["item_9"] = 1 ;
			
			if($dataTable[$i]["item_4"] > 0 )
			   $dataTable[$i]["item_4"] = 1 ;
			else
			   $dataTable[$i]["item_4"] = 0 ;
			
			
		//	if($dataTable[$i]["item_35"] == 1111 )
			   $dataTable[$i]["item_35"] = 1 ;
		/*	else
			   $dataTable[$i]["item_35"] = 2 ;*/
				
		//	if($dataTable[$i]["item_38"] == 1111 )
			   $dataTable[$i]["item_38"] = 103 ;
		/*	else
			   $dataTable[$i]["item_38"] = 1 ;*/
			
			
			$dataTable[$i]["item_41"] =  'قراردادی' ;  
			
			$item_42 = 0 ;
			$item_43 = 0 ;
			if($dataTable[$i]["item_42_1"] == 1 ){
				$item_42 = 2 ;  
				$item_43 = $dataTable[$i]["item_43_1"] ; 
			}
			else if ($dataTable[$i]["item_42_2"] == 1){				
				$item_42 = 1 ; 
				$item_43 = $dataTable[$i]["item_43_2"] ; 
			}
			
			if($dataTable[$i]["item_46"] == 1 || $dataTable[$i]["item_46"] == 2 || $dataTable[$i]["item_46"] == 11 )
			   $dataTable[$i]["item_46"] = 4  ;
			else if($dataTable[$i]["item_46"] == 3 || $dataTable[$i]["item_46"] == 4  )
			   $dataTable[$i]["item_46"] = 3 ;
		   else 
			    $dataTable[$i]["item_46"] = 5 ;
			   
		   $item_47 = 'صندوق پژوهش و فن آوری غیردولتی استان خراسان رضوی';  
			
		   if($dataTable[$i]["item_48"] == 1 ){
				$item_48 =  1 ; 
			}
			else  $item_48 =  2 ; 
			
			if(!empty($dataTable[$i]["item_8"]) && $dataTable[$i]["item_8"] != '0000-00-00' && $dataTable[$i]["item_8"] != NULL)
			{
				list($ldyear,$ldmonth,$ldday) = preg_split('/[\/]/',DateModules::miladi_to_shamsi($dataTable[$i]["item_8"]));				
				$dataTable[$i]["item_8"] =  $ldyear.$ldmonth.$ldday ;				
			}
			
			
			
			
			
			$Sitem_9 += $dataTable[$i]["item_34"] ;
				
		
$record .= $dataTable[$i]["item_1"].",1,".$dataTable[$i]["item_3"].",0,85,1,".$dataTable[$i]["item_7"].",,".
		   $dataTable[$i]["item_9"].",1,".$dataTable[$i]["item_11"].",,,,,,,,,,,,,,,,,,,,,,".$dataTable[$i]["item_34"].",".$dataTable[$i]["item_34"]."\r\n";
			

                         $WPrecord .= $dataTable[$i]["item_35"].",1,".$dataTable[$i]["item_1"].",".$dataTable[$i]["item_36"].",".
						 $dataTable[$i]["item_37"].",,,".
					     $dataTable[$i]["item_40"].",".$dataTable[$i]["item_41"].",".$item_42.",,,,,".$dataTable[$i]["item_7"].",".
					     $dataTable[$i]["item_46"].",".$item_47.",1,".$item_48.",,".
						 $dataTable[$i]["item_9"].",,\r\n";	
		}		
		
		PdoDataAccess::runquery("SET NAMES 'utf8'");
		
		if (isset($_REQUEST["summary"])) {

//echo "Summary"; die();
			
			list($eyear,$emonth,$eday) = preg_split('/[\/]/',DateModules::miladi_to_shamsi($month_end));		
			list($cyear,$cmonth,$cday) = preg_split('/[\/]/',$_POST["check_date"]);	
			
			list($tyear,$tmonth,$tday) = preg_split('/[\/]/',$_POST["TreasureDate"]);	
			
			
			$SRec = $_POST["pay_year"].",".str_pad($_POST["pay_month"], 2, "0", STR_PAD_LEFT).",".$Sitem_9.",0,".$eyear."".$emonth."".$eday.",7,". 
					$_POST["check-serial"].",".$cyear."".$cmonth."".$cday.",".$_POST["BankCode"].",".$_POST["BankTitle"].",".$_POST["account_no"].",".
					$_POST["PayVal"].",".$tyear."".$tmonth."".$tday.",".$_POST["TreasurPayVal"]	;
					
							
			$file = "WK".$_POST["pay_year"].str_pad($_POST["pay_month"], 2, "0", STR_PAD_LEFT).".TXT";
			//$filename = "/mystorage/attachments/sadaf/HRProcess/".$file ;
			$filename = "../../../tempDir/".$file ;
			$fp=fopen($filename,'w');
			fwrite($fp ,$SRec);
			fclose($fp);

			header('Content-disposition: filename="'.$file.'"');
			header('Content-type: application/file');
			header('Pragma: no-cache');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			echo file_get_contents("../../../tempDir/".$file);

			die() ; 
			
		}
		elseif (isset($_REQUEST["WP"])) {
		//echo "WP"; die();
			$file = "WP".$_POST["pay_year"].str_pad($_POST["pay_month"], 2, "0", STR_PAD_LEFT).".TXT";
			//$filename = "/mystorage/attachments/sadaf/HRProcess/".$file ;
			$filename = "../../../tempDir/".$file ;
			$fp=fopen($filename,'w');
			fwrite($fp ,$WPrecord);
			fclose($fp);

			header('Content-disposition: filename="'.$file.'"');
			header('Content-type: application/file');
			header('Pragma: no-cache');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			echo file_get_contents("../../../tempDir/".$file);

			die() ; 	
			
		}
		else {
				//echo "WH"; die();
			$file = "WH".$_POST["pay_year"].str_pad($_POST["pay_month"], 2, "0", STR_PAD_LEFT).".TXT";
			//$filename = "/mystorage/attachments/sadaf/HRProcess/".$file ;
			$filename = "../../../tempDir/".$file ;
			$fp=fopen($filename,'w');
			fwrite($fp ,$record);
			fclose($fp);

			header('Content-disposition: filename="'.$file.'"');
			header('Content-type: application/file');
			header('Pragma: no-cache');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			echo file_get_contents("../../../tempDir/".$file);

			die() ; 	
		
		}	
}


?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>