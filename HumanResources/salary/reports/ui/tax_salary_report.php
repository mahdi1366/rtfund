<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.03
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
		if($_POST["PTY"] == 102) 
		{
			$whr .= " AND s.person_type in ( 1,2,3 ) ";   					
		}
		else
		{
			$whr .= " AND s.person_type = :pt ";   		
			$whereParam[":pt"] = $_POST["PTY"] ; 
		}		
		
			
	}
	
	$whr .= "  AND pit.param1 > tbl3.from_value" ;
	
	if($_POST["PayType"] == 1 ) 
	{
		$SItmWhr = "34,36,12 , 1 , 6 , 22 , 3 , 283 , 605 , 885 ,10364 , 10365 , 10366 , 10367";
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

	$month_start = DateModules::shamsi_to_miladi($_POST["pay_year"] . "/" . $_POST["pay_month"] . "/01");
	if($_POST["pay_month"] < 7 )
	   $day = "31"; 
	
	else if($_POST["pay_month"] < 12  )  
	   $day = "30";
	
	else   $day = "29";
		
	$month_end = DateModules::shamsi_to_miladi($_POST["pay_year"] . "/" . $_POST["pay_month"] . "/".$day);

	$query = " select  p.national_code item_1 , p.pfname item_2 , p.plname item_3, p.father_name item_4, w.emp_state item_5 , 
					   p.postal_code1 item_6 , po.title item_7 , w.onduty_year item_8, bi.MasterID item_9 , jc.jcid item_10 ,					   
					   Azadeh.devotion_type item_11_1 , janbaz.devotion_type item_11_2 ,p.nationality item_12 , co.ptitle item_13 , 
					   sih.insure_include item_14_1 , sih.service_include item_14_2 , p.personid item_16_1 , p.insure_no item_16_2  ,
					   tbl1.sv  item_17 , tbl2.sv  item_18 , tbl4.gv  detail_item_31 ,
					   tbl3.from_value item_32 ,pit.get_value item_33 , pit.get_value item_34 
						 
						from persons p inner join staff s
												on p.personid = s.personid and p.person_type = s.person_type
									   inner join writs w
												on s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver  and s.staff_id = w.staff_id
									   inner join Basic_Info bi
												on  w.education_level = bi.InfoID and  bi.typeid = 6
									   left join position po
												on po.post_id = w.post_id
									   left join job_fields jf
												on (po.jfid = jf.jfid)
									   left join job_subcategory jsc
												on ((jf.jsid = jsc.jsid) AND (jf.jcid=jsc.jcid))												
									   left join job_category jc
												on (jsc.jcid = jc.jcid)
												
									   left join staff_include_history sih
												on sih.staff_id = s.staff_id and
												   start_date <= '".$month_start."' and
												 ( end_date > '".$month_start."' or end_date is null or end_date = '0000-00-00' )
													 
									   left join ( SELECT personid , devotion_type
														FROM person_devotions
																WHERE devotion_type in (2)
												   GROUP BY personid

												 ) Azadeh 												 
												on  Azadeh.personid = p.personid
										left join ( SELECT personid , devotion_type
														FROM person_devotions
																WHERE devotion_type in (3)
												   GROUP BY personid

												 ) janbaz 												 
												on  janbaz.personid = p.personid							
												
										inner join countries co 
												on p.nationality = co.country_id

										inner join ( select pit1.staff_id , sum(pay_value + diff_pay_value * diff_value_coef) sv
														from payment_items pit1 inner join staff s
																on pit1.staff_id = s.staff_id

															where pay_year = ".$_POST["pay_year"]." and pay_month = ".$_POST["pay_month"]." and 
															      payment_type = ".$_POST["PayType"]." and
																  salary_item_type_id in (".$SItmWhr.")

													 group by staff_id

													)   tbl1
												on s.staff_id = tbl1.staff_id

										left join ( select pit2.staff_id , sum(pay_value + diff_pay_value * diff_value_coef) sv
														from payment_items pit2 inner join staff s
																					on pit2.staff_id = s.staff_id
																			inner join salary_item_types sit
																					on sit.salary_item_type_id = pit2.salary_item_type_id and tax_include = 1
															where pay_year = ".$_POST["pay_year"]." and pay_month = ".$_POST["pay_month"]." and payment_type = ".$_POST["PayType"]." and
															      pit2.salary_item_type_id not in (".$SItmWhr.")

															group by staff_id
													) tbl2

												on s.staff_id = tbl2.staff_id

										left join (	SELECT staff_id , sth.tax_table_type_id , tti.from_value
														FROM staff_tax_history sth inner join tax_table_types ttt
																								on sth.tax_table_type_id = ttt.tax_table_type_id
																				   inner join  tax_tables tt
																								on ttt.tax_table_type_id = tt.tax_table_type_id and 																								   
																								   from_date <=  '".$month_start."' and
																								   ( to_date >= '".$month_end."' or to_date is null or to_date ='0000-00-00')
																				   inner join tax_table_items tti
																								on  tti.tax_table_id = tt.tax_table_id

														WHERE sth.start_date <= '".$month_start."' and
															( sth.end_date >= '".$month_start."' or sth.end_date is null or
															  sth.end_date = '0000-00-00')

													) tbl3 on s.staff_id = tbl3.staff_id
										left join (
											select staff_id , sum(get_value) gv

											from payment_items

											where pay_year = ".$_POST["pay_year"]." and pay_month = ".$_POST["pay_month"]." and
												  salary_item_type_id in (38 , 143 , 282 , 9971 , 9994 , 10149 , 9919 , 9964 , 9998 )

											group by staff_id
										)tbl4 on s.staff_id = tbl4.staff_id

										inner join payment_items pit

												on  pit.staff_id = s.staff_id and pit.pay_year = ".$_POST["pay_year"]." and
													pit.pay_month =".$_POST["pay_month"]."  and pit.payment_type = ".$_POST["PayType"]." and
													pit.salary_item_type_id in (146,147,148,747) 

						".$whr ;
	
		$dataTable = PdoDataAccess::runquery($query, $whereParam);
		if($_SESSION['UserID'] == 'jafarkhani')
		{
			//echo PdoDataAccess::GetLatestQueryString() ; die() ;
		}
		
		$record="";
		$Sitem_4 = 0 ; 
		$Sitem_5 = 0 ;
		$Sitem_6 = 0 ;
			
							
		for($i=0 ; $i < count($dataTable) ; $i++)
		{
			if($dataTable[$i]["item_5"] == 1 ||  $dataTable[$i]["item_5"] == 2 || $dataTable[$i]["item_5"] == 10 || $dataTable[$i]["item_5"] == 11 )
			{
				$dataTable[$i]["item_5"] = 1 ; 
			}	
			else if ( $dataTable[$i]["item_5"] == 4 ) 
			{
				$dataTable[$i]["item_5"] = 2 ; 
			}
			else if ( $dataTable[$i]["item_5"] == 5 ) 
			{
				$dataTable[$i]["item_5"] = 4 ; 
			}
			else if ( $dataTable[$i]["item_5"] == 3 ) 
			{
				$dataTable[$i]["item_5"] = 3 ; 
			}
			else if ( $dataTable[$i]["item_5"] == 8 ) 
			{
				$dataTable[$i]["item_5"] = 5 ; 
			}
			else if ( $dataTable[$i]["item_5"] == 6 ) 
			{
				$dataTable[$i]["item_5"] = 6 ; 
			}
			else 
				$dataTable[$i]["item_5"] = 0 ; 
						
			$dataTable[$i]["item_9"] = $dataTable[$i]["item_9"] + 1 ; 
			
			if($dataTable[$i]["item_10"] == 1 )
				$dataTable[$i]["item_10"] = 2 ; 
			
			else if($dataTable[$i]["item_10"] == 2 )
					$dataTable[$i]["item_10"] = 1 ; 
			
			else if($dataTable[$i]["item_10"] == 3 )
					$dataTable[$i]["item_10"] = 3 ; 
			
			else if($dataTable[$i]["item_10"] == 4 )
					$dataTable[$i]["item_10"] = 5 ; 
			
			else if($dataTable[$i]["item_10"] == 5 )
					$dataTable[$i]["item_10"] = 7 ; 
			
			else if($dataTable[$i]["item_10"] == 6 )
					$dataTable[$i]["item_10"] = 8 ; 
			
			else if($dataTable[$i]["item_10"] == 7 )
					$dataTable[$i]["item_10"] = 6 ; 
			
			else if($dataTable[$i]["item_10"] == 8 )
					$dataTable[$i]["item_10"] = 4 ; 
			
			$item_11 = 0 ;
			if($dataTable[$i]["item_11_1"] > 0 ||  $dataTable[$i]["item_11_1"] != NULL )
			   $item_11 = 2 ;
			
			if($dataTable[$i]["item_11_2"] > 0 ||  $dataTable[$i]["item_11_2"] != NULL )
			   $item_11 = 3 ;
			
			if($dataTable[$i]["item_11_1"] != NULL && $dataTable[$i]["item_11_2"] != NULL )
			   $item_11 = 7 ;	
			
			if($dataTable[$i]["item_12"] == 1111 )
			   $dataTable[$i]["item_12"] = 1 ; 
			
			else { 
				$dataTable[$i]["item_12"] = 2 ; 
				$item_13 = $dataTable[$i]["item_13"] ; 
			}		
			
			$item_14 = 0 ; 
			if($dataTable[$i]["item_14_1"] == 1 ){
				$item_14 = 1 ;  
				$item_16 = $dataTable[$i]["item_16_2"] ; 
			}
			else if ($dataTable[$i]["item_14_2"] == 1){				
				$item_14 = 2 ; 
				$item_16 = $dataTable[$i]["item_16_1"] ; 
			}	
			
			$item_22 = $item_20 = $dataTable[$i]["item_17"] + $dataTable[$i]["item_18"] ;  
			$item_28 = 0 ; // عیدی ......
			$item_30 =  $item_22 + $item_28 ; 
			$item_31 =	$dataTable[$i]["detail_item_31"]	 ; 
			$item_32 =	$item_30 - $item_31 - $dataTable[$i]["item_32"] ;  		
			         
			
			
			$FirstRec = "101902103472,1,".$_POST["pay_year"].",".str_pad($_POST["pay_month"], 2, "0", STR_PAD_LEFT).",2,2,," ; 
			$FirstRec .= "دانشگاه فردوسی مشهد"."," ; 
			$FirstRec .= "مرکزی"  ;
			$FirstRec .= ","."411339477389" .",". "9177948974" .",". "8802000" .",". "میدان آزادی - پردیس دانشگاه فردوسی مشهد" ;
			$FirstRec .= ","."0859450856" ;
			$FirstRec .= ","."محمد" ;
			$FirstRec .= ","."کافی" ; 
			$FirstRec .= ","."رئیس دانشگاه" ; 
			$FirstRec .= ","."0932891608" ; 
			$FirstRec .= ","."ابوالفضل" ; 
			$FirstRec .= ","."باباخانی" ; 
			$FirstRec .= ","."معاون اداری مالی" ."\r\n";
						
			$Sitem_4 += $item_20 + $item_28 ; 
			$Sitem_5 += $dataTable[$i]["item_32"] ;
			$Sitem_6 += $dataTable[$i]["item_33"] ;
			$Sitem_9 += $dataTable[$i]["item_34"] ;
				
			$record .= $dataTable[$i]["item_1"].",".$dataTable[$i]["item_2"].",".$dataTable[$i]["item_3"].",".$dataTable[$i]["item_4"].",".$dataTable[$i]["item_5"].",".
					   $dataTable[$i]["item_6"].",".$dataTable[$i]["item_7"].",".$dataTable[$i]["item_8"].",".$dataTable[$i]["item_9"].",".$dataTable[$i]["item_10"].",".
					   $item_11.",".$dataTable[$i]["item_12"].",".$item_13.",".$item_14.", ,".$item_16.",".$dataTable[$i]["item_17"].",".$dataTable[$i]["item_18"].",0,".
					   $item_20.",0,".$item_22.",0,0,0,0,0,".$item_28.",".				   
					   $item_29.",".					   
					   $item_30.",".$item_31.",".$item_32.",".$dataTable[$i]["item_33"].",0"."\r\n"; 			
					   
					  					
		}		
		
		PdoDataAccess::runquery("SET NAMES 'utf8'");
		
		if (isset($_REQUEST["summary"])) {
			
			list($eyear,$emonth,$eday) = preg_split('/[\/]/',DateModules::miladi_to_shamsi($month_end));		
			list($cyear,$cmonth,$cday) = preg_split('/[\/]/',$_POST["check_date"]);		
			
			$SRec = "101902103472".",".$_POST["pay_year"].",".str_pad($_POST["pay_month"], 2, "0", STR_PAD_LEFT).",".$Sitem_4.",".$Sitem_5.",".$Sitem_6 .",0,0,".$Sitem_9.
					",0,0,0,0,0,0,".$eyear."".$emonth."".$eday.",".count($dataTable).",0,2,".$_POST["check-serial"].",".$cyear."".$cmonth."".$cday.",".$_POST["BankCode"].",".
					$_POST["BankTitle"].",".$_POST["account_no"].",".$_POST["PayVal"] ;
					
							
			$file = "WK".$_POST["pay_year"].str_pad($_POST["pay_month"], 2, "0", STR_PAD_LEFT).".TXT";
			//$filename = "/mystorage/attachments/sadaf/HRProcess/".$file ;
			$filename = "../../../HRProcess/".$file ;
			$fp=fopen($filename,'w');
			fwrite($fp ,$SRec);
			fclose($fp);

			header('Content-disposition: filename="'.$file.'"');
			header('Content-type: application/file');
			header('Pragma: no-cache');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			echo file_get_contents("../../../HRProcess/".$file);

			die() ; 
			
		}
		else {
				
			$file = "WH".$_POST["pay_year"].str_pad($_POST["pay_month"], 2, "0", STR_PAD_LEFT).".TXT";
			//$filename = "/mystorage/attachments/sadaf/HRProcess/".$file ;
			$filename = "../../../HRProcess/".$file ;
			$fp=fopen($filename,'w');
			fwrite($fp ,$FirstRec.$record);
			fclose($fp);

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
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>