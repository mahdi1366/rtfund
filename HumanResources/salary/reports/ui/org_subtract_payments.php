<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.08.07
//---------------------------
require_once("../../../header.inc.php");
if(!isset($_REQUEST["show"]))
    require_once '../js/org_subtract_payments.js.php';
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
		$whereParam[":pm"] = $_POST["pay_month"];
	}
	if(!empty($_POST["pay_year"]))
	{
		$whr .= " AND pit.pay_year=:py";             
		$whereParam[":py"] = $_POST["pay_year"];
	}
        
        if(!empty($_POST["PersonType"]))
	{
            if($_POST["PersonType"]== 102 )
                {
                    $whr .= " AND s.person_type in (1,2,3) ";                   
		    $pt = "(1,2,3)" ; 
                }
            else {    
		
                    $whr .= " AND s.person_type=:pt";                   
                    $whereParam[":pt"] = $_POST["PersonType"];                 
                }                
                
                if($_POST["PersonType"]== 102 || $_POST["PersonType"]== 2 || $_POST["PersonType"]== 3 || $_POST["PersonType"]== 1 )
                    {
                        //$whr.= " AND pit.salary_item_type_id in (144,145,9920,38,143,149,150,146,147,148,399,242,9915,9911,243,242,399) " ; 
                    }
	}
        
        
       $month_start = DateModules::shamsi_to_miladi($_POST["pay_year"]."/".$_POST["pay_month"]."/01")  ;
       $month_end = DateModules::shamsi_to_miladi($_POST["pay_year"]."/".$_POST["pay_month"]."/29")  ;
       
        $query = " select   sit.salary_item_type_id ,sit.full_title salary_item_title , 
			    bi.Title  person_type , 
			    round(  if( pit.salary_item_type_id in(9920,144,744) , 
			    sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param2 + pit.diff_param2 * diff_param2_coef  ) ) ,
			    if( pit.salary_item_type_id in(145) , sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param2 + pit.diff_param2 * diff_param2_coef  ) + ( pit.param3 + pit.diff_param3 * diff_param3_coef  ) ) , 
			    if( pit.salary_item_type_id in(38,143) ,  sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param7 + pit.diff_param7 * diff_param7_coef ) + (( pit.param7 + pit.diff_param7 * diff_param7_coef ) * 1.7 / 1.65)) , 
			    if( (pit.salary_item_type_id in(149,150) AND s.last_retired_pay IS NOT NULL
								     AND s.last_retired_pay < '".$month_start."') , 
			    sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param3 + pit.diff_param3 * diff_param3_coef  ) )  ,
			    if( (pit.salary_item_type_id in(149,150) AND s.last_retired_pay IS NULL
								     OR s.last_retired_pay >= '".$month_start."' ) , 
			    sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) + ( pit.param3 + pit.diff_param3 * diff_param3_coef  ) ) ,                           
			    sum(pit.get_value + (pit.diff_get_value * pit.diff_value_coef )))))))) getValue 

                                                    from  payment_items pit
                                                                    inner join salary_item_types sit
                                                                        on pit.salary_item_type_id = sit.salary_item_type_id
                                                                    inner join staff s 
								              on pit.staff_id = s.staff_id
								    inner join Basic_Info bi 
								              on bi.typeid = 16  and bi.InfoID = sit.person_type

                                                                    left join  CostCenterException cce
                                                                                                        on pit.cost_center_id = cce.CostCenterID AND
                                                                                                        pit.salary_item_type_id = cce.SalaryItemTypeID AND 
                                                                                                        cce.Fromdate <= '".$month_start."' AND 
                                                                                                        ( cce.ToDate >= '".$month_end."' or 
                                                                                                          cce.ToDate Is null or cce.ToDate = '0000-00-00') 

                                        where  cce.CostCenterID is null AND pit.cost_center_id in ( ". manage_access::getValidCostCenters() .") AND 
					       sit.effect_type = 2 AND pit.payment_type = 1 AND
					       pit.salary_item_type_id in (144,145,9920,38,143,149,150,146,147,148,399,242,9915,9911,243,242,399,744,747) ".$whr."

                                        group by sit.salary_item_type_id , sit.person_type  
union all

select pit.salary_item_type_id ,sit.full_title salary_item_title ,  
       bi.Title  person_type , sum(pit.get_value  + (pit.diff_get_value * pit.diff_value_coef )) get_value 
  from payment_items pit left join SubtractItemInfo sii
                             on pit.salary_item_type_id = sii.SalaryItemTypeID and 
				      sii.FromDate <='".$month_start."' and
                                ( sii.ToDate iS NUll OR sii.ToDate >='".$month_end."' or  sii.ToDate='0000-00-00')
                         left join CostCenterException cc
                             on  pit.salary_item_type_id = cc.SalaryItemTypeID and
                                 pit.cost_center_id = cc.CostCenterID and cc.FromDate <='".$month_start."' and
                                  ( cc.ToDate iS NUll OR
                                    cc.ToDate >='".$month_end."'  or
                                    cc.ToDate='0000-00-00')
				    
			 inner join salary_item_types sit 
			     on pit.salary_item_type_id  = sit.salary_item_type_id
			 inner join Basic_Info bi 
			     on bi.typeid = 16  and bi.InfoID = sit.person_type
			inner join staff s on pit.staff_id = s.staff_id 

       where pay_year = 1391 and pay_month = 8 and sii.SalaryItemTypeID is null and get_value != 0 and
             pit.salary_item_type_id not in (144,145,9920,38,143,149,150,146,147,148,399,242,9915,9911,243,242,399,744,747) and

cc.SalaryItemTypeID is null and cc.SalaryItemTypeID is null ".$whr."

group by pit.salary_item_type_id

"  ; 
        
        
             
	$dataTable = PdoDataAccess::runquery($query, $whereParam);
        
	 if($_SESSION["UserID"] == "jafarkhani" ) {
		//  echo PdoDataAccess::GetLatestQueryString() ; die(); 
	}
	
        $qry = " select bi.Title month_title 
                        from  Basic_Info bi 
                                where  bi.typeid = 41 AND InfoID = ".$_POST["pay_month"] ; 
       $res = PdoDataAccess::runquery($qry) ; 
       $month = $res[0]['month_title'] ; 
                     
       global $sumSubValue  ;
       $sumSubValue =0 ; 
       $refArr = "" ; 
       $refCC = "" ; 
	
	if($_POST['PersonType']==2)
            $title = 'کارمندان'; 
        if($_POST['PersonType']==1)
            $title = 'اعضای هیئت علمی'; 
        if($_POST['PersonType']==3)
            $title = 'کارکنان روزمزدبیمه ای'; 
        if($_POST['PersonType']==5)
            $title = 'کارکنان قراردادی'; 
        if($_POST['PersonType']==102)
            $title = 'هیئت علمی ، کارمندان ،روزمزدبیمه ای'; 
        
    ?>
<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
			text-align: center;width: 50%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
</style>
       	
    <?    
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<center><table style='border:2px groove #9BB1CD;border-collapse:collapse;width:50%'><tr>
				<td width=60px><img src='/HumanResources/img/fum_symbol.jpg'></td>
				<td align='center' style='font-family:b titr;font-size:15px'> گزارش کسورات سازمانی ".$month." ماه ".
                                  $_POST['pay_year']." ".$title." </td>				
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			echo "<br>گزارش از تاریخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
	echo "</td></tr></table>";      
	
        echo '<table  class="reportGenerator" style="text-align: right;width:50%!important" cellpadding="4" cellspacing="0">
		 <tr class="header">					
			<td>ردیف </td>
			<td>کد قلم</td>
			<td>عنوان قلم </td>
			<td>نوع فرد </td>			
			<td>مبلغ/ریال</td>					
		</tr>' ; 
	
	for($i=0 ; $i < count($dataTable) ; $i++)
	    {	 
	    $sumSubValue +=  $dataTable[$i]['getValue'] ; 
	     echo " <tr>
			    <td>".( $i + 1 )."</td>
			    <td>".$dataTable[$i]['salary_item_type_id']."</td>
			    <td>".$dataTable[$i]['salary_item_title']."</td>
			    <td>".$dataTable[$i]['person_type']."</td>
			    <td>".number_format($dataTable[$i]['getValue'], 0, '.', ',')."</td>
		    </tr>" ; 
		
	    }
	    echo ' <tr style="border:1px;font-family:font-size:10px" >				
			<td colspan=4 align="left" style="font-family:b Titr" >جمع :</td>			
			<td>'.number_format($sumSubValue, 0, '.', ',').'</td>				
		   </tr>';
	    echo "</table></center>" ; 
   
}

?>
<form id="mainForm">
    <center>
        <div id="mainpanel"></div>
    </center>    
</form>