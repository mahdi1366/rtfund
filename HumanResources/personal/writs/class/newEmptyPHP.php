<?php
	require_once('header.inc');
	require_once('num2str.php');
    require_once('../profs/include/profUtil.class.php');
    require_once('../hrms_definitions.inc');
	
	$Psql = dbclass::getInstance();
	$Pquery = "select person_type from hrmstotal.persons 
	                                         where PersonID=".$_SESSION["PersonID"]; 
	$Presult = $Psql->Execute($Pquery);	
	$Presult = $Presult->GetRows();
	$prst = $Presult[0]['person_type'];
	
	if($prst==5 || $prst==6)
	{
		 $DB = 'hrms_sherkati.' ; 
	}
	else
	{
		 $DB = 'hrms.';
	}
	
     if( $prst != 10 && $_SESSION['UserID'] != 'bmahdipour' && $_SESSION['UserID'] != 'jafarkhani' && $_SESSION['UserID'] != 'bimakr' && $_SESSION['UserID'] != 'nilofar' && $_SESSION['UserID'] != 'omid' && $_SESSION['UserID'] != 'orbsim')
    include config::$root_path."framework/ntoken/CheckToken.php"; 
       
    	
    HTMLBegin('','rtl','','',true);
    
      ?>
  <style>
  .text{
    font-family: Tahoma,Verdana, Arial,Helvetica,Sans-serif;
	font-size: 11;
	font-weight: bold;
  } 
  .info{
    font-family: Tahoma,Verdana, Arial,Helvetica,Sans-serif;
	font-size: 11;
	font-weight: normal;
  }
  .title{
    font-family: Tahoma,Verdana, Arial,Helvetica,Sans-serif;
	font-size: 18;
	font-weight: bold;
  }
  
  .border-table{
    border-bottom: none;
	border-left: #999999 1px solid;
	border-right: #999999 1px solid;
	border-top: none;
  }
  .b-left{
    border-bottom: none;
	border-left:  #999999 1px solid;
	border-right: none;
	border-top: none;
  }
  .b-right{
    border-bottom: none;
	border-left:  none;
	border-right: #999999 1px solid;
	border-top: none;
  }
  </style>
  </head><body  dir=rtl link="#0000FF" alink="#0000FF" vlink="#0000FF">
 <?php
 
 if( $_SESSION['UserID'] == 'bmahdipour' ) {
    
    // $HrmsPersonID = '201101';
 }
 
        $pays['value']          = array();
    	$gets['value']          = array();
    	$pays['diff_value']     = array();
    	$gets['diff_value']     = array();
    	$pays['title']          = array();
    	$gets['title']          = array();
    	$pays['param3']         = array();
		$gets['loan_remainder'] = array();
    	$gets['frac_remainder'] = array();
    	$pay_sum = 0 ;
    	$get_sum = 0 ;
    	$pay_diff_sum = 0;
    	$get_diff_sum = 0;
    	    	
    	$stf_sql = dbclass::getInstance();
    	$stf_res = $stf_sql->Execute("select PersonID,person_type from hrmstotal.staff where staff_id=".$_GET['staff_id']);	
    	$stf_res = $stf_res->GetRows();
    	
    	if($stf_res[0]['PersonID']!= $_SESSION["PersonID"]) {
    		echo"<center>"."<b>"."شما به این فرم دسترسی ندارید"."</b>"."</center>";
    	}
    	else {
	$sly_sql = dbclass::getInstance();
	$sly_query = " SELECT   sit.print_order,ps.pfname,
                            ps.plname,
                            sit.effect_type,
                            sit.print_title salary_item_title,
                            c.cost_center_id,
                            c.title cost_center_title,
                            pai.pay_value,
                            pai.get_value,
                            (pai.diff_pay_value * diff_value_coef) diff_pay_value,
                            (pai.diff_get_value * diff_value_coef) diff_get_value,
                            pai.salary_item_type_id,
                            pai.param3,
                            pai.param2,
                            pa.account_no,
                            s.tafsili_id,
                            s.staff_id,
                            b.bank_id,
                            b.name,
                            pa.message,
                            pa.pay_year,
                            pa.pay_month,
                            pai.param1 ,
					        CASE pai.param1
					        	WHEN 'LOAN' THEN  pai.param4
					        END loan_remainder,
					        CASE pai.param1
					        	WHEN 'FRACTION' THEN  pai.param4
					        END frac_remainder
					        
				     FROM     hrmstotal.payment_items pai
                              INNER JOIN hrmstotal.payments pa
                                    ON (pa.pay_year = pai.pay_year AND
                                        pa.pay_month = pai.pay_month AND
                                        pa.staff_id = pai.staff_id AND
                                        pa.payment_type = pai.payment_type AND
                                        pai.payment_type = ".$_GET['payment_type'].")
                              INNER JOIN hrmstotal.salary_item_types sit
                                    ON (pai.salary_item_type_id = sit.salary_item_type_id)
                              INNER JOIN hrmstotal.writs w
                                    ON ((pa.writ_id = w.writ_id) AND (pa.writ_ver = w.writ_ver) AND (w.staff_id=pa.staff_id))
                              INNER JOIN hrmstotal.staff s
                                    ON (pa.staff_id = s.staff_id)
                              INNER JOIN hrmstotal.persons ps
                                    ON (s.PersonID = ps.PersonID)
                              INNER JOIN hrmstotal.cost_centers c
                                    ON (pai.cost_center_id = c.cost_center_id)
                              LEFT OUTER JOIN hrmstotal.banks b
                                    ON (s.bank_id = b.bank_id)
                                    
                    WHERE (pa.staff_id=".$_GET['staff_id']." AND pa.payment_type =".$_GET['payment_type']." AND
                           pa.pay_month=".$_GET['pay_month']." AND pa.pay_year=".$_GET['pay_year'].") AND  if(pa.pay_year=1393 ,pa.pay_month < 13 , (1=1))
                    ORDER BY c.cost_center_id,
							 pa.pay_year,
							 pa.pay_month,
							 ps.plname,
							 ps.pfname,
							 sit.print_order"; 
	
	/* if($_SESSION['UserID'] == 'bmahdipour')
                echo $sly_query ; */
         
	$sly_result = $sly_sql->Execute($sly_query);	
	$sly_result = $sly_result->GetRows();
			
	echo "<table  width=97% align=center cellspacing=0 >";
	echo "<tr >";
		echo "<td  class=info  width=15% >"."نام خانوادگي : "."</td>";
		echo "<td  class=info  width=20% >".$sly_result[0]['plname']."</td>";
		echo "<td  class=info  width=15% >"."شماره حساب : "."</td>";
		echo "<td  class=info  width=15% >".$sly_result[0]['account_no']."</td>";
		echo "<td  class=info  width=17% >"."كد شناسايي : "."</td>";
		echo "<td  class=info  width=15% >".$sly_result[0]['staff_id']."</td>";
	echo "</tr>";
	echo "<tr >";
		echo "<td  class=info  width=15% >"."نام : "."</td>";
		echo "<td  class=info  width=20% >".$sly_result[0]['pfname']."</td>";
		echo "<td  class=info  width=15% >"."بانك : "."</td>";
		echo "<td  class=info  width=15% >".$sly_result[0]['name']."</td>";
		echo "<td  class=info  width=17% >"."واحد محل خدمت :"."</td>";
		echo "<td  class=info  width=15% >".$sly_result[0]['cost_center_title']."</td>";
	echo "</tr>";
	echo "</table>";
	echo '<table border=1 width=97% align=center cellspacing=0 style="background-image:url(/'.BG_PIC.');background-repeat: no-repeat; background-position: center; ">';
	echo '<tr>';    
	    echo '<td  class=info align=center width=5% style="border-bottom: 1px solid #999999;">'."رديف"."</td>";
	    echo '<td  class=info align=center width=20% style="border-bottom: 1px solid #999999;">'."حقوق ومزايا"."</td>";
	    echo '<td  class=info align=center width=10% style="border-bottom: 1px solid #999999;">'."مبلغ"."</td>";
	    echo '<td  class=info align=center width=10% style="border-bottom: 1px solid #999999;">'."تفاوت"."</td>";
	    echo '<td  class=info align=center width=3% style="border-bottom: 1px solid #999999;">'."کارکرد/تعداد"."</td>";
	    echo '<td  class=info align=center width=20% style="border-bottom: 1px solid #999999;">'."شرح کسورات"."</td>";
	    echo '<td  class=info align=center width=10% style="border-bottom: 1px solid #999999;">'."مبلغ"."</td>";
	    echo '<td  class=info align=center width=10% style="border-bottom: 1px solid #999999;">'."تفاوت"."</td>";
	    echo '<td  class=info align=center width=10% style="border-bottom: 1px solid #999999;">'."مانده"."</td>";
	    echo '<td  class=info align=center width=7% style="border-bottom: 1px solid #999999;">'."موجودي"."</td>"; 
	echo "</tr>";
	
	if($stf_res[0]['person_type']==2 || $stf_res[0]['person_type']==3){
		$SIT_STAFF_EXTRA_WORK = 39;
		$SIT_STAFF_HORTATIVE_EXTRA_WORK = 9921;
		$SIT_WORKER_EXTRA_WORK = 152;
		$SIT_WORKER_HORTATIVE_EXTRA_WORK  = 9922;
		
	}
	else {
		$SIT_STAFF_EXTRA_WORK = SIT_STAFF_EXTRA_WORK;
		$SIT_STAFF_HORTATIVE_EXTRA_WORK = SIT_STAFF_HORTATIVE_EXTRA_WORK;
		$SIT_WORKER_EXTRA_WORK = SIT_WORKER_EXTRA_WORK;
		$SIT_WORKER_HORTATIVE_EXTRA_WORK  = SIT_WORKER_HORTATIVE_EXTRA_WORK;
		
	}
	
	for($i=0;$i<count($sly_result);$i++){
		if( $sly_result[$i]['effect_type'] == BENEFIT && 
		   ($sly_result[$i]['pay_value']!= 0 || $sly_result[$i]['effect_type']!= 0 )){
		   	array_push($pays['value'], $sly_result[$i]['pay_value']);
    		array_push($pays['diff_value'], $sly_result[$i]['diff_pay_value']);
    		array_push($pays['title'], $sly_result[$i]['salary_item_title']);
    		//echo $sly_result[$i]['salary_item_title'].$i."--";
    		if( $sly_result[$i]['salary_item_type_id'] == $SIT_STAFF_EXTRA_WORK ||
    			$sly_result[$i]['salary_item_type_id'] == $SIT_STAFF_HORTATIVE_EXTRA_WORK) {
		    	array_push($pays['param3'], $sly_result[$i]['param3']);
    		} elseif ( $sly_result[$i]['salary_item_type_id'] == $SIT_WORKER_EXTRA_WORK ||
    			       $sly_result[$i]['salary_item_type_id'] == $SIT_WORKER_HORTATIVE_EXTRA_WORK ) {
		    	array_push($pays['param3'], $sly_result[$i]['param2']);
    		} else {
		    	array_push($pays['param3'], NULL);
    		}
			
		}else if($sly_result[$i]['get_value'] != 0 || $sly_result[$i]['diff_get_value'] != 0){
	    		 array_push($gets['value'], $sly_result[$i]['get_value']);
	    		 array_push($gets['diff_value'],$sly_result[$i]['diff_get_value']);
	    		 array_push($gets['title'], $sly_result[$i]['salary_item_title']);
	    		 array_push($gets['loan_remainder'],$sly_result[$i]['loan_remainder']);
	    		 array_push($gets['frac_remainder'], $sly_result[$i]['frac_remainder']);
	    		 
	    		 
    	}
	}
	$loop_limit = max(MAX_PAYMENT_ROWS, count($pays['title']), count($gets['title']));
		//print_r($pays['value']);
	for($i=0; $i < $loop_limit; $i++) {
		echo "<tr >";    
		    if( $i < count($pays['title']) ){
			    echo "<td  class='info b-right'  width=5% >".($i + 1)."</td>";
			    echo "<td  class='info border-table'  width=20% >".$pays['title'][$i]."</td>";
			    echo "<td  class='info border-table'  width=10% >".(profUtil::format($pays['value'][$i]))."</td>";
			    $pay_sum += $pays['value'][$i];
			    echo "<td  class='info border-table'  width=10% >".(profUtil::format($pays['diff_value'][$i]))."</td>";
			    $pay_diff_sum += $pays['diff_value'][$i];
			    if($pays['param3'][$i] != null)
			    echo "<td  class='info border-table'  width=3% >".$pays['param3'][$i]."</td>";
			    else echo "<td  class='info border-table'  width=3% >"."&nbsp;"."</td>";
		    }
		    else {
			    echo "<td  class='info b-right'  width=5% >".($i + 1)."</td>";
			    echo "<td  class='info border-table'  width=20% >"."&nbsp;"."</td>";
			    echo "<td  class='info border-table'  width=10% >"."&nbsp;"."</td>";
			    echo "<td  class='info border-table'  width=10% >"."&nbsp;"."</td>";
			    echo "<td  class='info border-table'  width=3% >"."&nbsp;"."</td>";
		    }
		    if( $i < count($gets['title']) ){
			    echo "<td  class='info border-table'  width=20% >".$gets['title'][$i]."</td>";
			    echo "<td  class='info border-table'  width=10% >".(profUtil::format($gets['value'][$i]))."</td>";
			    $get_sum += $gets['value'][$i];
			    echo "<td  class='info border-table'  width=10% >".(profUtil::format($gets['diff_value'][$i]))."</td>";
			    $get_diff_sum += $gets['diff_value'][$i];
			    if($gets['loan_remainder'][$i] != null)
			    echo "<td  class='info border-table'  width=10% >".(profUtil::format($gets['loan_remainder'][$i]))."</td>";
			    else echo "<td  class='info border-table'  width=10% >"."&nbsp"."</td>";
			    echo "<td  class='info b-left'  width=7% >"."&nbsp;"."</td>"; 
		    }
		    else {
		    	echo "<td  class='info border-table'  width=20% >"."&nbsp;"."</td>";
			    echo "<td  class='info border-table'  width=10% >"."&nbsp;"."</td>";
			    echo "<td  class='info border-table'  width=10% >"."&nbsp;"."</td>";
			    echo "<td  class='info border-table'  width=10% >"."&nbsp;"."</td>";
			    echo "<td  class='info b-left'  width=7% >"."&nbsp;"."</td>"; 
		    }
	     echo "</tr>";
	}
	echo "<tr>";
	      echo "<td  class='info' colspan=2 >"."جمع کل حقوق و مزايا: ".(profUtil::format(($pay_sum + $pay_diff_sum)))."</td>";
	      echo "<td  class='info'>".(profUtil::format($pay_sum))."</td>";
	      echo "<td  class='info' colspan=2 >".(profUtil::format($pay_diff_sum))."</td>";
	      echo "<td  class='info'>"."جمع کسورات: ".(profUtil::format(($get_sum + $get_diff_sum)))."</td>";
	      echo "<td  class='info'>".(profUtil::format($get_sum))."</td>";
	      echo "<td  class='info' colspan=3 >".(profUtil::format($get_diff_sum))."</td>";
	echo "</tr>";
	echo "<tr>";
	      echo "<td  class='info' colspan=3 >"."خالص پرداختي  ".profUtil::display_month_title($sly_result[0]['pay_month']).' ماه '.$sly_result[0]['pay_year']."</td>";
	      echo "<td  class='info' colspan=4 >".Full_No2Str($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum)."</td>";
	      echo "<td  class='info' colspan=3 >".(profUtil::format(($pay_sum + $pay_diff_sum - $get_sum - $get_diff_sum)))."</td>";
	echo "</tr>";
	echo "<tr>";
	      if($sly_result[0]['message']!=null)
	      echo "<td  class='info' colspan=10 >".$sly_result[0]['message']."</td>";	
	      else "<td  class='info' colspan=10 >"."&nbsp;"."</td>";	
	echo "</table>";
    	}
 ?>
 
</body>
  </html>
