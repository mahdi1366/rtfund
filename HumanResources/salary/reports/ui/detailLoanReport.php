<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.7.02
//---------------------------
require_once("../../../header.inc.php");
require_once inc_manage_unit;

echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';

$title = "لیست مربوط به قلمهای  " ;
	
if(!empty($_GET['refArr']) && $_GET['refArr'] >= 0 ) 
    { 
   
    if(!empty($_POST['BI']))
	$WBI = " AND ". $_POST['BI'] ; 
    else $WBI = " " ; 
	
    $query = " select pit.staff_id, p.pfname , p.plname , pit.salary_item_type_id , sit.full_title , (pit.get_value + (pit.diff_get_value * pit.diff_value_coef ) )getValue

			from payment_items pit inner join salary_item_types sit
				    on pit.salary_item_type_id = sit.salary_item_type_id
					    inner join staff s on pit.staff_id = s.staff_id
					    left join CostCenterException cce
						    on pit.cost_center_id = cce.CostCenterID AND
							pit.salary_item_type_id = cce.SalaryItemTypeID AND cce.Fromdate <= '".$_POST['StartDate']."' AND
						    ( cce.ToDate >= '".$_POST['EndDate']."' or
							cce.ToDate Is null or cce.ToDate = '0000-00-00')
							
				left join SubtractItemInfo sii 
					on pit.salary_item_type_id = sii.SalaryItemTypeID  AND  sii.FromDate <= '".$_POST['StartDate']."' AND 
					 ( sii.ToDate is null OR sii.ToDate = '0000-00-00' OR sii.ToDate >= '".$_POST['EndDate']."' )
				
				inner join payments pa 
							on pa.staff_id = pit.staff_id and  pa.pay_year = pit.pay_year and
                               pa.pay_month = pit.pay_month and pa.payment_type = pit.payment_type
		 
				inner join persons p 
					on p.personid = s.personid 
				
			where	cce.CostCenterID is null AND  pa.state = 2 AND 
				pit.cost_center_id in ( ".manage_access::getValidCostCenters()." ) AND
				sit.effect_type = 2 AND pit.payment_type = 1 AND pit.pay_month = ".$_POST['PM']." AND pit.pay_year=".$_POST['PY']." AND 
				pit.param2 not in (
270376904	,
270376905	,
270376906	,
270376907	,
270376908	,
270376909	,
270376910	,
270376911	,
270376912	,
270376913	,
270376914	,
270376915	,
270376916	,
270376917	,
270376918	,
270376919	,
270376920	,
270376921	,
270376922	,
270376924	,
270376925	,
270376926	,
270376927	,
270376928	,
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
270376943	,
270376944	,
270376945	,
270376946	,
270376948	,
270376950	,
270376951	,
270376952	,
270376953	,
270376954	,
270376955	,
270376956	,
270376957	,
270376958	,
270376960	,
270376961	,
270376961	,
270376962	,
270376963	,
270376963	,
270376964	,
270376965	,
270376966	
)
				AND 
				s.person_type in ".$_POST['PT']." AND
				sii.arrangement  = ".$_GET['refArr']." ".$WBI."
								
			order by pit.salary_item_type_id , p.plname , p.pfname "  ; 
    
    $data = PdoDataAccess::runquery($query) ; 
    
	if($_SESSION['UserID'] == 'jafarkhani') {	
	//	echo PdoDataAccess::GetLatestQueryString() ; die() ;
	} 
	
    //.............................
	$query = "  select si.SalaryItemTypeID , sit.full_title   
		
			    from SubtractItemInfo si inner join salary_item_types sit 
							on si.SalaryItemTypeID = sit.salary_item_type_id AND  si.FromDate <= '".$_POST['StartDate']."' AND 
							 ( si.ToDate is null OR si.ToDate = '0000-00-00' OR si.ToDate >= '".$_POST['EndDate']."' )
								 
				where si.arrangement=".$_GET['refArr'] ; 
	
	$res = PdoDataAccess::runquery($query) ; 
	
	
	
	for($i=0 ; $i < count($res) ; $i++)
	{
	    $title .=  $res[$i]['SalaryItemTypeID']." : ".$res[$i]['full_title']." ، "; 
	    $sit = $res[$i]['SalaryItemTypeID']."," ; 
	}
	
		
    }
else if ($_GET['refCC'] > 0 )
    {      
	$query = "  SELECT   pit.staff_id , p.pfname , p.plname  , sit.full_title , 
			    (pit.get_value + (pit.diff_get_value * pit.diff_value_coef )) getValue

			    FROM payment_items pit inner join CostCenterException cce 
							on pit.cost_center_id = cce.CostCenterID AND pit.salary_item_type_id = cce.SalaryItemTypeID AND
							   cce.Fromdate <= '".$_POST['StartDate']."' AND ( cce.ToDate >= '".$_POST['EndDate']."' or cce.ToDate Is null or 
							   cce.ToDate = '0000-00-00')
							   
							inner join payments pa 
								on  pa.staff_id = pit.staff_id and  pa.pay_year = pit.pay_year and
									pa.pay_month = pit.pay_month and pa.payment_type = pit.payment_type

						   inner join cost_centers cc 
							on cce.CostCenterID = cc.cost_center_id
						   inner join staff s 
							on pit.staff_id = s.staff_id
						   inner join persons p 
							on s.personid= p.personid
						   inner join salary_item_types sit 
							on  pit.salary_item_type_id = sit.salary_item_type_id

			where pit.payment_type = 1 AND pa.state = 2 AND pit.param2 not in (
			
270376904	,
270376905	,
270376906	,
270376907	,
270376908	,
270376909	,
270376910	,
270376911	,
270376912	,
270376913	,
270376914	,
270376915	,
270376916	,
270376917	,
270376918	,
270376919	,
270376920	,
270376921	,
270376922	,
270376924	,
270376925	,
270376926	,
270376927	,
270376928	,
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
270376943	,
270376944	,
270376945	,
270376946	,
270376948	,
270376950	,
270376951	,
270376952	,
270376953	,
270376954	,
270376955	,
270376956	,
270376957	,
270376958	,
270376960	,
270376961	,
270376961	,
270376962	,
270376963	,
270376963	,
270376964	,
270376965	,
270376966	

			) AND
					pit.cost_center_id in (".manage_access::getValidCostCenters().") AND
					pit.pay_month = ".$_POST['PM']." AND pit.pay_year=".$_POST['PY']." AND 
					s.person_type in ".$_POST['PT']." and cce.CostCenterID = ".$_GET['refCC']." order by  pit.salary_item_type_id "  ; 
	$data = PdoDataAccess::runquery($query) ; 
    

    //...................................
	$query = " select cce.SalaryItemTypeID , sit.full_title
			    from CostCenterException cce inner join salary_item_types sit 
							    on cce.SalaryItemTypeID = sit.salary_item_type_id
						where cce.CostCenterID =".$_GET['refCC'] ;
	$res = PdoDataAccess::runquery($query) ; 
	
	for($i=0 ; $i < count($res) ; $i++)
	{
	    $title .=   $res[$i]['SalaryItemTypeID']." : ".$res[$i]['full_title']." ، "; 
	    $sit = $res[$i]['SalaryItemTypeID']."," ; 
	}	
	
    }
	
	
       
       $qry = " select bi.Title month_title 
                        from  Basic_Info bi 
                                where  bi.typeid = 41 and InfoID = ".$_POST['PM'] ; 
       $res = PdoDataAccess::runquery($qry) ; 
       $month = $res[0]['month_title'] ; 
       
   $sit = substr($sit,0, ( strlen($sit) - 1 )); 
   $title = substr($title,0, ( strlen($title) - 3 )) ;
   $title.= " در ".$month." ماه " ;
   
   
?>
<html dir='rtl'>
	<head>
		<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
			text-align: center;width: 70%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;background-color:#3F5F96}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
		</style>
		<title> </title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
		<link rel=stylesheet href="/HumanResources/css/writ.css">
	</head>
	<body>
		<center>
			
				
			<?
				 $qry = " select bi.Title month_title 
                        from  Basic_Info bi 
                                where  bi.typeid = 41 AND InfoID = ".$_POST["PM"] ; 
				$res = PdoDataAccess::runquery($qry) ; 
				$month = $res[0]['month_title'] ; 
				
				$Supervisor = "" ; 
				
				if(!empty($_GET['refArr'])) {
					$qry = " select PriorityTitle  from priority where PriorityID = ".$_GET['refArr']  ;
					$Pres = PdoDataAccess::runquery($qry) ; 
					$Supervisor = $Pres[0]['PriorityTitle'] ; 
				}
				if(!empty($_GET['refCC'])) {
					
					$qry = " select title  from cost_centers  where cost_center_id = ".$_GET['refCC']  ;
					$Cres = PdoDataAccess::runquery($qry) ; 
					$Supervisor = $Cres[0]['title'] ; 
				}
				
				
				?>
			<br><br><br><br>
			<table width="70%" style='font-family:b titr;font-size:13px' >
				<tr>
					<td colspan="4">
						مسئول محترم  
						&nbsp;&nbsp;
						<?=$Supervisor?>
						<br>
						با سلام، احتراما به پیوست کسورات &nbsp;
						<?= $month ?>
						ماه &nbsp;
						<?=$_POST['PY']?>
						آن واحد محترم به مبلغ  &nbsp;
						<?=$_POST['SumVal']?>
						 ریال جهت هر گونه اقدام مقتضی ارسال می گردد. ضمنا مبلغ فوق به حساب شما واریز گردیده است.
					<td>
				</tr>
				<tr>
					<td colspan="3" width="65%">
						&nbsp;
					</td>
					<td><br><br>
						محمدعلی باقرپور ولاشانی
						<br>
						سرپرست مدیریت مالی دانشگاه فردوسی مشهد
					</td>
				</tr>
			</table>
				<hr style="page-break-after:always; visibility: hidden"><br><br>
			<table width="70%" cellpadding="0" cellspacing="0">
				<tr class="header" >
					<td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" ></td>
					<td align="center" style="font-family:b titr;font-size: 11pt;font-weight: bold;"><?= $title ;?></td>
					<td width="20%" align="left" style="font-family:tahoma;font-size:8pt">تاریخ :  <?= DateModules::shNow()?></td>
				</tr>
			</table>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0">
			    <tr class="header"><td>ردیف</td>
				<td>شماره شناسایی</td>
				<td>نام</td>
				<td>نام خانوادگی</td>
				<td>قلم حقوقی</td>
				<td>مبلغ</td>
			    </tr>
			      <?
				$TotalSum = 0  ; 
				$j = 12 ; 
				for($i=0; $i< count($data) ; $i++)
				    {
						if($j > 12 && $j%40 == 0  ){
							
							?>
				
				</table> <hr style="page-break-after:always; visibility: hidden"><br><br>
			<table style="text-align: right;" class="reportGenerator" cellpadding="4" cellspacing="0">
			    <tr class="header"><td>ردیف</td>
				<td>شماره شناسایی</td>
				<td>نام</td>
				<td>نام خانوادگی</td>
				<td>قلم حقوقی</td>
				<td>مبلغ</td>
			    </tr>
							<?
						}											
						echo " <tr><td>".($i+1)."</td>
									<td>".$data[$i]['staff_id']."</td><td>".$data[$i]['pfname']."</td><td>".$data[$i]['plname']."</td>
									<td>".$data[$i]['full_title']."</td><td>".number_format($data[$i]['getValue'], 0, '.', ',') ."</td>
							   </tr>";                                     
						$TotalSum += $data[$i]['getValue'] ; 
						$j++ ; 
				    }
				                                
                                ?>	
			<tr style="background-color:#CAD6E6" >
				<td colspan="5" align="left" ><b>
					&nbsp; جمع : 
				</b></td>
				<td><?=number_format($TotalSum, 0, '.', ',')  ?></td>
			</tr>
			</table>
					
		</center>
	</body>
</html>
