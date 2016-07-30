<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.07
//---------------------------
require_once("../../../header.inc.php");

if(!isset($_REQUEST["show"]))
    require_once '../js/loan_subtract_payments.js.php';
require_once "ReportGenerator.class.php";

 $whr = "" ; 
 $whr2 = "" ;$whr3 = "" ;
 $khazaneh = "" ;
 $kh = "" ; $whrBI = $whrBI2 = "" ;
 
if(isset($_REQUEST["show"]))
{
	
	$whereParam = array();
       
	if(!empty($_POST["pay_month"]))
	{
		$whr .= " AND pit.pay_month = :pm ";
                $whr2 .= " AND sir.PayMonth = :pm ";
		$whereParam[":pm"] = $_POST["pay_month"];
	}
	if(!empty($_POST["pay_year"]))
	{
		$whr .= " AND pit.pay_year=:py";
                $whr2 .= " AND sir.PayYear=:py";
		$whereParam[":py"] = $_POST["pay_year"];
	}
        
        if(!empty($_POST["PersonType"]))
	{
            if($_POST["PersonType"]== 102 )
                {
                    $whr .= " AND s.person_type in (1,2,3) ";
                    $whr2 .= " AND sir.PersonType in (1,2,3,100) ";
		    $pt = "(1,2,3)" ; 
                }
            else {    
		
                    $whr .= " AND s.person_type=:pt";
                    $whr2 .= " AND sir.PersonType=:pt";
                    $whereParam[":pt"] = $_POST["PersonType"]; 
		    $pt = "(".$_POST["PersonType"].")";
                
                }                
                
                $whr3 = $whr ; 
                if($_POST["PersonType"]== 102 || $_POST["PersonType"]== 2 || $_POST["PersonType"]== 3 || $_POST["PersonType"]== 1 )
                    {
                        $whr.= " AND pit.salary_item_type_id not in (144,145,9920,38,143,149,150,146,147,148,399,242,9915,9911,243,242,399) " ; 
                    }
	}
        
        if(!empty($_POST['BeneficiaryID']))
        {
            if( $_POST['BeneficiaryID'] == 100 ){
                $whrBI.= "  " ; $whrBI2 = " " ; 
            }
            else { 
                $whrBI.= "  AND sii.BeneficiaryID=".$_POST['BeneficiaryID'] ; 
                $whrBI2.= "  AND sir.BeneficiaryID=".$_POST['BeneficiaryID'] ; 
                }
        }
        else      {       
            $whrBI.= "  " ;    $whrBI2.= "  " ;        
            
            }
        
       $month_start = DateModules::shamsi_to_miladi($_POST["pay_year"]."/".$_POST["pay_month"]."/01")  ;
       $month_end = DateModules::shamsi_to_miladi($_POST["pay_year"]."/".$_POST["pay_month"]."/29")  ;
       
        $query = " select tbl1.salary_item_type_id ,tbl1.salary_item_title ,  tbl1.person_type ,tbl1.getValue ,sii.arrangement , null cnItm , sii.description , null CostCenterID

                        from (
                                    select  sit.salary_item_type_id ,sit.full_title salary_item_title , 
                                            sit.person_type , 
                                            round(  if( pit.salary_item_type_id in(9920,144,744) , sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param2 + pit.diff_param2 * diff_param2_coef  ) ) ,
                                                                if( pit.salary_item_type_id in(145) , sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param2 + pit.diff_param2 * diff_param2_coef  ) + ( pit.param3 + pit.diff_param3 * diff_param3_coef  ) ) , 
                                                                if( pit.salary_item_type_id in(38,143) ,  sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param7 + pit.diff_param7 * diff_param7_coef ) + (( pit.param7 + pit.diff_param7 * diff_param7_coef ) * 1.7 / 1.65)) , 
                                                                if( (pit.salary_item_type_id in(149,150) AND s.last_retired_pay IS NOT NULL
                                                                                            AND s.last_retired_pay < '".$month_start."') , sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param3 + pit.diff_param3 * diff_param3_coef  ) )  ,
                                                                    if( (pit.salary_item_type_id in(149,150) AND s.last_retired_pay IS NULL
                                                                                            OR s.last_retired_pay >= '".$month_start."' ) , sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param3 + pit.diff_param3 * diff_param3_coef  ) ) ,                           
                                                                    sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef )))))))) getValue 



                                                    from  payment_items pit
                                                                    inner join salary_item_types sit
                                                                        on pit.salary_item_type_id = sit.salary_item_type_id

                                                                    inner join staff s on pit.staff_id = s.staff_id

                                                                    left join  CostCenterException cce
                                                                                                        on pit.cost_center_id = cce.CostCenterID AND
                                                                                                        pit.salary_item_type_id = cce.SalaryItemTypeID AND 
                                                                                                        cce.Fromdate <= '".$month_start."' AND 
                                                                                                        ( cce.ToDate >= '".$month_end."' or 
                                                                                                          cce.ToDate Is null or cce.ToDate = '0000-00-00') 
													  
								    inner join payments p on pit.pay_year = p.pay_year and 
											     pit.pay_month = p.pay_month and 
											     pit.staff_id = p.staff_id and 
											     pit.payment_type = p.payment_type 

                                        where  cce.CostCenterID is null AND pit.cost_center_id in ( ". manage_access::getValidCostCenters() .") AND 
					                           sit.effect_type = 2 AND pit.param2 not in (
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
270376965 , 
178519943 , 270366680 , 270377067 , 180021184 , 270374973 , 270374972 , 270379013 			   

											   ) AND p.state = 2 AND pit.payment_type = 1 ".$whr."

                                        group by sit.salary_item_type_id , sit.person_type ) tbl1  
                    
                    left join SubtractItemInfo sii
                            on tbl1.salary_item_type_id = sii.SalaryItemTypeID
                          where  sii.arrangement is null AND sii.Fromdate <= '".$month_start."' AND 
                                                                                                        ( sii.ToDate >= '".$month_end."' or 
                                                                                                          sii.ToDate Is null or sii.ToDate = '0000-00-00')   $whrBI
                          
                          Union ALL 
                          
		    select tbl1.salary_item_type_id ,tbl1.salary_item_title ,  tbl1.person_type ,sum(tbl1.getValue) getValue ,sii.arrangement , count(*) cnItm ,  sii.description , null CostCenterID

                        from (
                                    select  sit.salary_item_type_id ,sit.full_title salary_item_title , 
                                            sit.person_type , 
                                            round(  if( pit.salary_item_type_id in(9920,144,744) , sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param2 + pit.diff_param2 * diff_param2_coef ) ) ,
                                                                if( pit.salary_item_type_id in(145) , sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param2 + pit.diff_param2 * diff_param2_coef ) + ( pit.param3 + pit.diff_param3 * diff_param3_coef) ) , 
                                                                if( pit.salary_item_type_id in(38,143) ,  sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param7 + pit.diff_param7 * diff_param7_coef) + (( pit.param7 + pit.diff_param7 * diff_param7_coef) * 1.7 / 1.65)) , 
                                                                if( (pit.salary_item_type_id in(149,150) AND s.last_retired_pay IS NOT NULL
                                                                                            AND s.last_retired_pay < '".$month_start."') , sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param3 + pit.diff_param3 * diff_param3_coef ) )  ,
                                                                    if( (pit.salary_item_type_id in(149,150) AND s.last_retired_pay IS NULL
                                                                                            OR s.last_retired_pay >= '".$month_start."' ) , sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param3 + pit.diff_param3 * diff_param3_coef ) ) ,                           
                                                                    sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef )))))))) getValue 



                                                    from  payment_items pit
                                                                    inner join salary_item_types sit
                                                                        on pit.salary_item_type_id = sit.salary_item_type_id

                                                                    inner join staff s on pit.staff_id = s.staff_id

                                                                    left join  CostCenterException cce
                                                                                                        on pit.cost_center_id = cce.CostCenterID AND
                                                                                                        pit.salary_item_type_id = cce.SalaryItemTypeID AND 
                                                                                                        cce.Fromdate <= '".$month_start."' AND 
                                                                                                        ( cce.ToDate >= '".$month_end."' or 
                                                                                                          cce.ToDate Is null or cce.ToDate = '0000-00-00')
								    inner join payments p on pit.pay_year = p.pay_year and 
											     pit.pay_month = p.pay_month and 
											     pit.staff_id = p.staff_id and 
											     pit.payment_type = p.payment_type 

                                        where  cce.CostCenterID is null AND pit.cost_center_id in ( ". manage_access::getValidCostCenters() ." ) AND 
					       sit.effect_type = 2 AND p.state = 2 AND pit.param2 not in (

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
		270376965,
		178519943 , 270366680 , 270377067 , 180021184 , 270374973 , 270374972 , 270379013 
						   
						   )  AND  pit.payment_type = 1 ".$whr."

                                        group by sit.salary_item_type_id , sit.person_type ) tbl1  
                    
                    left join SubtractItemInfo sii
                            on tbl1.salary_item_type_id = sii.SalaryItemTypeID
                          where  sii.arrangement is not null and  sii.Fromdate <= '".$month_start."' AND 
                                                                                                        ( sii.ToDate >= '".$month_end."' or 
                                                                                                          sii.ToDate Is null or sii.ToDate = '0000-00-00')  $whrBI
                           group by sii.arrangement  
                           
                    union all
                     
                    SELECT sir.SalaryItemReportID salary_item_type_id ,sir.SalaryItemTitle salary_item_title , sir.PersonType person_type ,sir.ItemValue getValue , 
                           null arrangement ,null cnItm , sir.description , null CostCenterID

                                FROM SalaryItemReport sir

                    where (1=1) AND sir.ItemType = 1 ".$whr2." ".$whrBI2  ; 
        
        if($_POST['BeneficiaryID'] == 100 || $_POST['BeneficiaryID'] == 1 ){
        
                    $query.= "
                                union all 

                                SELECT  null salary_item_type_id , cc.title  salary_item_title , null person_type , sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef )) get_value ,
                                        null arrangement , tmp.cn cnItm , cc.description , tmp.CostCenterID

                                    FROM   payment_items pit inner join  CostCenterException cce
                                                                    on pit.cost_center_id = cce.CostCenterID AND pit.salary_item_type_id = cce.SalaryItemTypeID AND 
                                                                                                                    cce.Fromdate <= '".$month_start."' AND 
                                                                                                                    ( cce.ToDate >= '".$month_end."' or 
                                                                                                                    cce.ToDate Is null or cce.ToDate = '0000-00-00')

                                                            inner join cost_centers cc
                                                                        on cce.CostCenterID = cc.cost_center_id

                                                            inner join staff s
                                                                            on pit.staff_id = s.staff_id
                                                            inner join (
                                                                        SELECT CostCenterID , count(*) cn
                                                                            FROM CostCenterException
                                                                                    where  Fromdate <= '".$month_start."' AND 
                                                                                                                    ( ToDate >= '".$month_end."' or 
                                                                                                                      ToDate Is null or ToDate = '0000-00-00')
                                                                                group by  CostCenterID
                                                                        ) tmp on tmp.CostCenterID = cce.CostCenterID
									
							 inner join payments p on pit.pay_year = p.pay_year and 
											     pit.pay_month = p.pay_month and 
											     pit.staff_id = p.staff_id and 
											     pit.payment_type = p.payment_type 

                                    where pit.payment_type = 1 AND p.state = 2 AND  pit.param2 not in (
									
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
270376965	,
178519943 , 270366680 , 270377067 , 180021184 , 270374973 , 270374972 , 270379013 							


									) AND 
									pit.cost_center_id in (". manage_access::getValidCostCenters() .") ".$whr." 

                                        group by cce.CostCenterID
                             ";
                }
		
	//if($_SESSION["UserID"] == "jafarkhani" ) {	
		$query  = " 
select  pit.salary_item_type_id ,
	sit.full_title salary_item_title ,
	s.person_type ,
	sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef )) getValue ,
	null arrangement , null cnItm , null description , null CostCenterID
  
             from  payments p inner join  payment_items pit
                                    on p.pay_year = pit.pay_year and p.pay_month = pit.pay_month and
                                       p.payment_type = pit.payment_type and p.staff_id = pit.staff_id
                              inner join salary_item_types sit on pit.salary_item_type_id = sit.salary_item_type_id
                              inner join staff s on pit.staff_id = s.staff_id
                              left join CostCenterException cce
                                                   on pit.cost_center_id = cce.CostCenterID AND
                                                      pit.salary_item_type_id = cce.SalaryItemTypeID AND
                                                     cce.Fromdate <= '".$month_start."' AND 
						   ( cce.ToDate >= '".$month_end."' OR
						    cce.ToDate Is null or cce.ToDate = '0000-00-00')

                              left join SubtractItemInfo sii
                                          on pit.salary_item_type_id = sii.SalaryItemTypeID AND
						sii.Fromdate <= '".$month_start."' AND 
                                                                                                        ( sii.ToDate >= '".$month_end."' or 
                                                                                                          sii.ToDate Is null or sii.ToDate = '0000-00-00')


where cce.CostCenterID is null and sii.arrangement is null and sit.effect_type = 2 and
     
     
      p.state = 2 AND 
	  pit.param2 not in (
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
270376965 , 178519943 , 270366680 , 270377067 , 180021184 , 270374973 , 270374972 , 270379013 
)
	  AND  pit.payment_type = 1 ".$whr."

group by pit.salary_item_type_id union All 

".$query ; 
//	}     
             
	$dataTable = PdoDataAccess::runquery($query, $whereParam);
        
	if($_SESSION["UserID"] == "jafarkhani" ) {
	     //echo PdoDataAccess::GetLatestQueryString() ; die(); 
	}
	
        $qry = " select bi.Title month_title 
                        from  Basic_Info bi 
                                where  bi.typeid = 41 AND InfoID = ".$_POST["pay_month"] ; 
       $res = PdoDataAccess::runquery($qry) ; 
       $month = $res[0]['month_title'] ; 
           
       $qry = " select sum((pit.pay_value + (pit.diff_pay_value * pit.diff_value_coef ) ) - (pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) ) ) totalSum
                    from payment_items pit inner join staff s 
                                               on pit.staff_id = s.staff_id
					       inner join payments p on pit.pay_year = p.pay_year and 
											     pit.pay_month = p.pay_month and 
											     pit.staff_id = p.staff_id and 
											     pit.payment_type = p.payment_type 
                        where pit.payment_type = 1 AND p.state = 2 AND  pit.param2 not in (
						
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
270376965 ,
178519943 , 270366680 , 270377067 , 180021184 , 270374973 , 270374972 , 270379013 			
	

						) AND  pit.cost_center_id in (". manage_access::getValidCostCenters() .") ".$whr3 ; 
       
       $resSumSalary  = PdoDataAccess::runquery($qry,$whereParam) ; 
      
       $totalSum = $resSumSalary[0]["totalSum"] ;
       
        if($_POST["PersonType"]== 2 || $_POST["PersonType"]== 102 || $_POST["PersonType"]== 1 || $_POST["PersonType"]== 3  ) {
              
                $query = "   SELECT sum(sir.ItemValue) getValue 

                                            FROM SalaryItemReport sir

                                where (1=1) AND sir.ItemType = 2 ".$whr2." ".$whrBI2 ;

                $resKhazaneh = PdoDataAccess::runquery($query,$whereParam) ; 
                
                 
        if($_POST["PersonType"]== 2 || $_POST["PersonType"]== 102 || $_POST["PersonType"] == 1 || $_POST["PersonType"]== 3 ) {
                 $kh =  "پرداخت کسورات قانوني خزانه"."<br>" ; 
                 $khazaneh = $resKhazaneh[0]["getValue"]."<br>" ;                                       
                                            }                              
       
       }       
       
       global $sumSubValue  ;
       $sumSubValue =0 ; 
       $refArr = "" ; 
       $refCC = "" ; 
	
	if($_POST['PersonType']==2)
            $title = 'کارمندان'; 
        if($_POST['PersonType']==1)
            $title = 'اعضاي هيئت علمي'; 
        if($_POST['PersonType']==3)
            $title = 'کارکنان روزمزدبيمه اي'; 
        if($_POST['PersonType']==5)
            $title = 'کارکنان قراردادي'; 
        if($_POST['PersonType']==102)
            $title = 'هيئت علمي ، کارمندان ،روزمزدبيمه اي'; 
	
	
	if($_POST['PersonType']==102)
	     $bime_omr_sahme_sazman = 164376000 ;		  
		
	elseif($_POST['PersonType']==5)
	    $bime_omr_sahme_sazman = 52380000 ;
	else 
	    $bime_omr_sahme_sazman = 0 ;
	    
	    
        
    ?>
<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
			text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
		</style>
       
       	
    <?    
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'> گزارش درخواست پرداخت اقساط وام و کسورات متفرقه حقوق ".$month." ماه ".
                                  $_POST['pay_year']." ".$title." </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاريخ تهيه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			echo "<br>گزارش از تاريخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
	echo "</td></tr></table></center>";      
	
        echo '<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
		 <tr class="header">					
			<td>رديف </td>
			<td>شرح</td>
			<td>ترتيب</td>
			<td>تعداد</td>
			<td>در وجه</td>
			<td>مبلغ/ريال</td>					
		</tr>' ; 
	//
	for($i=0 ; $i < count($dataTable) ; $i++)
	    {	 
	    
	    $sumSubValue += $dataTable[$i]['getValue'] ; 		
	   
	    if($dataTable[$i]['cnItm'] > 0)
		{
		     
		    if($dataTable[$i]['arrangement']!=null && $dataTable[$i]['arrangement']>=0) 
			{   
			
			    $refArr[$i] = $dataTable[$i]['arrangement'] ; 
			    if($dataTable[$i]['arrangement']==0)
				$refArr[$i] = "0"; 
			    $refCC[$i] = Null ; 
			}
		    else { 
			    $refCC[$i] = $dataTable[$i]['CostCenterID'] ; 		    
			    $refArr[$i] = Null ; 
			}
		     //  $cnItm = " <a href='/HumanResources/salary/reports/ui/detailLoanReport.php?PY=".$_POST["pay_year"]."&PM=".$_POST["pay_month"]."&refArr=".$refArr[$i]."&refCC=".$refCC[$i]."' target='_blank'> ".$dataTable[$i]['cnItm']."</a>" ;
			
			 $cnItm = "<form id='myform$i' target='_blank' name='myform$i' action='/HumanResources/salary/reports/ui/detailLoanReport.php?refArr=".$refArr[$i]."&refCC=".$refCC[$i]."' method=\"post\" >
				   <input name='PY' type='hidden' value=".$_POST["pay_year"].">
				   <input name='PM' type='hidden' value=".$_POST["pay_month"].">				   
				   <input name='PT' type='hidden' value=".$pt."> 	
				   <input name='BI' type='hidden' value=".str_replace("AND"," ",$whrBI).">     
				   <input name='StartDate' type='hidden' value=".$month_start."> 
				   <input name='EndDate' type='hidden' value=".$month_end."> 
				   <input name='SumVal' type='hidden' value=".number_format($dataTable[$i]['getValue'], 0, '.', ',')."> 	 
				   <a href=\"javascript:void(0)\" onclick=\"document.getElementById('myform$i').submit();\" > ".$dataTable[$i]['cnItm']."
				       </a></form> 
				   " ;
		}
		else $cnItm = " " ; 
	    
	     echo " <tr>
			    <td>".( $i + 1 )."</td>
			    <td>".$dataTable[$i]['salary_item_title']."</td>
			    <td>".$dataTable[$i]['arrangement']."</td>
			    <td>".$cnItm."</td>
			    <td>".$dataTable[$i]['description']."</td>
			    <td>".number_format($dataTable[$i]['getValue'], 0, '.', ',')."</td>
		    </tr>" ; 
	   
	     if($i > 0 && $i%19 == 0  ){		
		echo '</table><hr style="page-break-after:always; visibility: hidden">';
		echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'> گزارش درخواست پرداخت اقساط وام و کسورات متفرقه حقوق ".$month." ماه ".
                                  $_POST['pay_year']." ".$title." </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاريخ تهيه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			echo "<br>گزارش از تاريخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
	echo "</td></tr></table></center>";   
		echo '
				<table  class="reportGenerator" style="text-align: right;width:100%!important" cellpadding="4" cellspacing="0">
				<tr class="header">					
					<td>رديف </td>
					<td>شرح</td>
					<td>ترتيب</td>
					<td>تعداد</td>
					<td>در وجه</td>
					<td>مبلغ/ريال</td>					
				</tr>' ; 
	    }
		
	    }
		
	//.................................
    $tempNO = 0 ;  
	if($_POST['PersonType'] == 102 && $_POST['pay_month'] == 10 ) 
	{
		$tempNO =  38116342 ; $t_1 = 20 ;  
	}
   if($_POST['PersonType'] == 5 && $_POST['pay_month'] == 10 ) 	  { 
		$tempNO =  26250439 ;  
		$t_1 = 10 ; 
		}
		
	//.........................................	
	 echo " <tr>
			    <td>".( $i +1 )."</td>
			    <td> اضافه پرداختي وامها</td>
			    <td> </td>
			    <td>$t_1</td> 
			    <td></td>
			    <td>$tempNO</td>
		    </tr>" ;  
    echo "<tr style='background-color:#E5E8E8;font-weight: bold'><td colspan ='5'></td><td>".number_format($sumSubValue + $tempNO , 0, '.', ',')."</td></tr></table>";
       // if($_POST['BeneficiaryID'] == 100 || empty($_POST['BeneficiaryID'])) {
        
        echo "<center>           <table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'>
                    <tr>
                    <td width='50%' colspan=2 style='border-left:1px solid' align='center'>
                    <table width='100%'><tr>
                            <td>خالص حقوق 
                            ".$month."
                                ماه
                                ".$_POST['pay_year']."<br>
                                    کسورات حقوق 
                                    ".$month."
                                        ماه 
                                        ".$_POST['pay_year']."
                                            به شرح فوق<br>
                 ".$kh."                           

                            هزينه حقوق                 
                            ".$month."
                                ماه 
                                ".$_POST['pay_year']."
                            </td>
                            <td align='left'>".number_format($totalSum, 0, '.', ',')."<br>
                                ".number_format($sumSubValue + $tempNO , 0, '.', ',')."<br> ".
								number_format($khazaneh, 0, '.', ',')." <br>				   
                                    ".number_format(($totalSum + $sumSubValue + $khazaneh ), 0, '.', ',')."
                            </td>                             

         </table>
</td>
                    <td width='50%' colspan=2 align='center' style='font-family:b titr;font-size:15px' > هادي لگزيان <br>
                    کارشناس مالي</td>
                </tr>
            </table>
            <table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'>
            <tr><td width= '35%' colspan=2 align='center' style='font-family:b titr;font-size:15px'  >اداره محترم دريافت و پرداخت <td><td colspan=2>&nbsp;</td></tr>
            <tr><td colspan=3 width='70%' align='center'> 
     با سلام ، موافقت مي شود مبلغ 
     ".number_format($sumSubValue, 0, '.', ',').
                
    " بابت کسورات حقوق "
                .$month.
                " ماه "
              .$_POST['pay_year'].  
                
          "  به شرح فوق پرداخت شود.
              </td><td colspan=1>&nbsp;</td>
              <tr><td colspan=2 width='50%'>&nbsp;</td><td colspan=2 width='50%'align='center' style='font-family:b titr;font-size:15px'>
              محمدعلي باقرپور ولاشاني
              <br>
سرپرست مديريت مالي دانشگاه فردوسي مشهد
              
</td></tr>
            </table>
              </center>" ; 
	die(); 
//}
}

?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>