<?php
 // System : HRMSv(beta)
 // Author :B.Mahdipour
 // Date : 87.6
  
	require_once('header.inc.php');
	if( config::$critical_status == 1 )  {	
	die() ;   
  }
    if(/*$_SESSION['UserID'] != 'bmahdipour' &&*/ $_SESSION['UserID'] != 'orbsim' &&  $_SESSION['UserID'] != 'omid' &&  $_SESSION['UserID'] != 'jafarkhani' )
			include config::$root_path."framework/ntoken/CheckToken.php"; 
	
	if(!isset($_REQUEST["version"]) && !isset($_GET['page']))
	{
		?>
		<script>
		var now = new Date();
		window.location = "<?= $_SERVER["PHP_SELF"]?>?version=" + now.getTime();
		</script>
		<?
		die();
	}
	
			
    ?>
<html>
    <head>
		<META http-equiv=Content-Type content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Language" content="fa"/>
		<meta http-equiv="Pragma" content="no-cache" />
		<meta http-equiv="Expires" content="-1" />
		<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE"/>
		<link rel="stylesheet" href="/css/Default.css" type="text/css"/>
		 <link rel="stylesheet"  href="../../gateway/template/default/css/login.css" type="text/css"/>
	</head>
    <body  dir=rtl link="#0000FF" alink="#0000FF" vlink="#0000FF">
<?
	$UserID = $_SESSION['UserID'];
	
	$countsql = pdodb::getInstance();
	//$countsql->audit('مشاهده احكام كارگزيني');
	
	$WClause = "";
	
	$t = "";
		
	$HrmsPersonID = $_SESSION["PersonID"]; 
	
	/*if( $_SESSION['UserID'] == 'bmahdipour' ) 
		$HrmsPersonID = '200488'; // '401366284' ; // // 700063 ; // // 
	else */
		$HrmsPersonID = $_SESSION["PersonID"];
				
	echo "<form name='writsForm' method='post' enctype='multipart/form-data'>";
		 echo "<table  width=97% align=center cellpadding=12 >";
		 echo "<tr >";
		 echo "<td align=right width=15% >";
		    echo "کلمه کلیدی:";
		 echo "</td>";
		 
		 echo "<td align=right width=15% colspan=2>";
		 echo '<input name="wkind" type="text" size="40">';
		 echo "</td>";
		 echo "<td>".'<input type="submit" name="filter" value="فیلتر" />'."</td>";
		 echo "</tr>";
	echo "</table>";
	
	if(isset($_POST['filter'])){
			if( $_POST['wkind'] != null){
				$WClause = "and (wt.title LIKE'%".$_POST['wkind']."%' or 
				                 wst.title LIKE'%".$_POST['wkind']."%' or
				                 w.description LIKE'%".$_POST['wkind']."%')";
				
			}
			elseif ($_POST['wkind'] == null){
				    $WClause = " ";
			}
			
		}
	
	
	$countsql->prepare( "  select *, substr(g2j(execute_date),3,2),w.description
		              from hrmstotal.writs w
		                   INNER JOIN hrmstotal.staff s
		                         on (w.staff_id = s.staff_id)
		                   INNER JOIN hrmstotal.writ_types wt
		                         on (w.writ_type_id = wt.writ_type_id  AND w.person_type = wt.person_type)
		                   INNER JOIN hrmstotal.writ_subtypes wst
		                         on (w.writ_type_id = wst.writ_type_id and
		                             w.writ_subtype_id = wst.writ_subtype_id AND
		                             w.person_type = wst.person_type)
		              where w.person_type != 10 and s.PersonID= ?   and  /*if(s.person_type in (2,5) , w.execute_date < '2014-03-21' , (1=1) ) and */
					  if( ((s.person_type = 2 or s.person_type = 3 or s.person_type = 5 or s.person_type = 6 ) and 
					  (w.execute_date >= '2011-03-21' or w.issue_date >= '2011-03-21' ) ) ,(w.state = 3 OR w.view_flag = 1), (1=1)) and 
						    substr(g2j(execute_date),3,2)  >= 83 ".$WClause." 
		              order by issue_date DESC ,execute_date DESC, writ_ver DESC
			    ");
	
	$count_res = $countsql ->ExecuteStatement(array($HrmsPersonID));	
	$count_res = $count_res->fetchAll();
	
    $cnt = count($count_res);
    $totaln = $cnt;
	if(!isset($_GET['rfsh']))
	   $s = 0;
	if (isset($_GET['rfsh'])) 
	    $s = $_GET['start']; 
	   
	$mysql = pdodb::getInstance();
	$mysql->prepare ("select *, substr(g2j(execute_date),3,2),wt.title as wt_title , wst.title as  wst_title ,w.description
              from hrmstotal.writs w
                   INNER JOIN hrmstotal.staff s
                         on (w.staff_id = s.staff_id)
                   INNER JOIN hrmstotal.writ_types wt
                         on (w.writ_type_id = wt.writ_type_id  AND w.person_type = wt.person_type)
                   INNER JOIN hrmstotal.writ_subtypes wst
                         on (w.writ_type_id = wst.writ_type_id and
                             w.writ_subtype_id = wst.writ_subtype_id AND
                             w.person_type = wst.person_type)
              where s.PersonID= ?  and /*if(s.person_type in (2,5) , w.execute_date < '2014-03-21' , (1=1) ) and*/
 			  if( ((s.person_type = 2 or s.person_type = 3  or s.person_type = 5 or s.person_type = 6 ) and 
			          ( w.execute_date >= '2011-03-21' or  w.issue_date >= '2011-03-21') ) ,( w.state = 3 OR w.view_flag = 1 ), (1=1)) and
				    substr(g2j(execute_date),3,2)  >= 83 ".$WClause." 
              order by issue_date DESC ,execute_date DESC, writ_ver DESC limit ".$s.",10
              ");
	
	$res = $mysql->ExecuteStatement(array($HrmsPersonID));	
	$res = $res->fetchAll();
               
 if( $_SESSION['UserID'] == 'bmahdipour' ) 
 {
	
 }			   
	echo "<table class=ListTable>";
	echo "<tr class=HeaderOfTable>
			<td width=3%>&nbsp;&nbsp;</td><td width=10%>شماره حكم</td>
			<td>نسخه</td>
			<td width=62%>نوع حكم</td>
			<td width=10%>تاريخ اجرا</td>
			<td width=10%>تاریخ صدور</td>";
			//if( $_SESSION['UserID'] == 'bmahdipour' ) {
			echo "<td width=15%>نسخه چاپی</td>" ; 
			
			//}
	echo   "</tr>";
	if(isset($_POST['filter']))
			$writk = $_POST['wkind']; 
		else $writk = null;
	for($i=0; $i<count($res); $i++)
	{
		
			if($i%2==0)
				echo "<tr class=OddRow>";
			else
				echo "<tr class=EvenRow>";
			$p_type = $res[$i]["person_type"];
			echo "<td>".($s + $i + 1)."</td>";	
		//	echo $_SESSION['UserID']."-----";
			//if ( $res[$i]["execute_date"] >= '2013-02-19'){
			    /*if( $_SESSION['UserID']!= 'bmahdipour' &&  $_SESSION['UserID']!= 'n-hafezi' ) {
				echo "<td>";
				echo "<a target='_blank'  href='http://sadaf.um.ac.ir/HumanResources/personal/writs/ui/print_writ.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&staff_id=".$res[$i]["staff_id"]."&transcript_no=pooya'>";
				echo $res[$i]["writ_id"]."</a></td>";
			   
			    }
			    elseif( $_SESSION['UserID']== 'bmahdipour' ||  $_SESSION['UserID']== 'n-hafezi' ) {*/
				
				echo "<td>";
				echo "<a target='_blank'  href='http://pooya.um.ac.ir/HumanResources/personal/writs/ui/print_writ.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&staff_id=".$res[$i]["staff_id"]."&transcript_no=pooya'>";
				echo $res[$i]["writ_id"]."</a></td>";
			   // }
			//}
			/*else if( $res[$i]["person_type"] == 5 && $res[$i]["execute_date"] <'2009-03-21'){
				echo "<td>";
			    echo "<a href='sherkati_writ_one_yearv8.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&p_type=".$p_type."&wkind=".$writk."&reportType=full&reportRowCount=2&exeapp=employees&ownapp=staff'>";
			    echo $res[$i]["writ_id"]."</a></td>";
			}
			elseif($res[$i]["person_type"] == 5 && $res[$i]["execute_date"] >= '2009-03-21' && $res[$i]["execute_date"] < '2013-02-19'){
				echo "<td>";
			    echo "<a href='contract_writs.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&p_type=".$p_type."&wkind=".$writk."&reportType=full&reportRowCount=2&exeapp=employees&ownapp=staff'>";
			    echo $res[$i]["writ_id"]."</a></td>";
				
			}
						
			elseif( $res[$i]["person_type"] == 6 ){
				echo "<td>";
			    echo "<a href='sherkati_worker_writ_detailv8.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&p_type=".$p_type."&wkind=".$writk."&reportType=full&reportRowCount=2&exeapp=employees&ownapp=staff'>";
			    echo $res[$i]["writ_id"]."</a></td>";
			}
			elseif ( $res[$i]["person_type"] == 2 &&  $res[$i]["execute_date"] < '2013-02-19'){
				echo "<td>";
			    echo "<a href='employee_writ_detailv8.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&p_type=".$p_type."&wkind=".$writk."&reportType=full&reportRowCount=2&exeapp=employees&ownapp=staff'>";
			    echo $res[$i]["writ_id"]."</a></td>";
			}
			elseif($res[$i]["person_type"] == 2 &&  $res[$i]["execute_date"] >= '2013-02-19'){ 			    

			    echo "<td>";
			    echo "<a target='_blank'  href='http://sadaf.um.ac.ir/HumanResources/personal/writs/ui/print_writ.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&staff_id=".$res[$i]["staff_id"]."&transcript_no=pooya'>";
			    echo $res[$i]["writ_id"]."</a></td>";
				
			}
			elseif ( $res[$i]["person_type"] == 3 &&  $res[$i]["execute_date"] < '2013-02-19') {
				echo "<td>";
			    echo "<a href='worker_writ_detailv8.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&p_type=".$p_type."&wkind=".$writk."&reportType=full&reportRowCount=2&exeapp=employees&ownapp=staff'>";
			    echo $res[$i]["writ_id"]."</a></td>";
			}
			elseif($res[$i]["person_type"] == 3 &&  $res[$i]["execute_date"] >= '2013-02-19'){ 			     

			    echo "<td>";
			    echo "<a target='_blank'  href='http://sadaf.um.ac.ir/HumanResources/personal/writs/ui/print_writ.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&staff_id=".$res[$i]["staff_id"]."&transcript_no=pooya'>";
			    echo $res[$i]["writ_id"]."</a></td>";
				
			}	*/
			echo "<td>".$res[$i]["writ_ver"]."</td>";
			echo "<td>".($res[$i]["wt_title"]." - ".$res[$i]["wst_title"])."</td>";
			echo "<td>".shdate($res[$i]["execute_date"])."</td>";
			echo "<td>".shdate($res[$i]["issue_date"])."</td>";
			
			
				echo "<td align='center' >";
				/*echo "<a target='_blank'  href='http://pooya.um.ac.ir/hrms_portal/profs/professor_writ_detailv8_pdf.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&staff_id=".$res[$i]["staff_id"]."&transcript_no=pooya'>";
				echo $res[$i]["writ_id"]."</a></td>";*/
				
				if($res[$i]["person_type"]=='2'){
						
                        echo "<a  target='_blank' href='/HumanResources/personal/writs/ui/PrintPdf.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&staff_id=".$res[$i]["staff_id"]."&transcript_no=pooya'>
                            <img src='/HumanResources/img/print.gif'  align='center'></a>";
                         }
				 elseif($res[$i]["person_type"]=='5'){
				 echo "<a  target='_blank' href='/HumanResources/personal/writs/ui/PrintPdf1.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&staff_id=".$res[$i]["staff_id"]."&transcript_no=pooya'>
					<img src='/HumanResources/img/print.gif'  align='center'></a>";
					
				 }  
				elseif($res[$i]["person_type"]=='3'){
				 echo "<a  target='_blank' href='/HumanResources/personal/writs/ui/PrintPdf2.php?writ_id=".$res[$i]["writ_id"]."&writ_ver=".$res[$i]["writ_ver"]."&staff_id=".$res[$i]["staff_id"]."&transcript_no=pooya'>
					<img src='/HumanResources/img/print.gif'  align='center'></a>";
					
				 }
					 echo "</td>";			
			 	
			
			echo "</tr>";
		
	}
	
	
	$cnt = ceil($cnt/10);		
	echo "<tr ><td colspan=10 dir=ltr><table width=100% border=0 class=FooterOfTable ><tr><td>";
	 unset($count_res); 
	 
	 if(isset($_GET['rfsh'])){
		$f = $_GET['start']+1;
		$p = $_GET['page'];
    }
		else {
			$f = 1;
			$p = 1;
		
		}
		
	if($p < $cnt )	
	   $t = $p * 10;
	else {
	   	$t = $totaln ;
	   }   
	   
	  for($j=1; $j<=$cnt;$j++)
		{
	        $s = ($j-1)*10;	
	        if($j!=$p)
		    echo "<a href='ShowWritsv9.php?start=".$s."&rfsh=1&page=".$j."'>".$j."&nbsp"."</a>";
		    else 
		    echo "<font color=#000000>"."<b>".$j."</b>"."&nbsp"."</font>";
		
		}
		
    echo "</td>";
    
    echo "<td align=right>"."نمایش موارد ".$f." تا ".$t." از ".$totaln." مورد"."</td>"."</tr>";		
	echo "</table>"."</td>"."</tr>";
	echo "</table>";
	echo "</form>";
?>

<p align=right dir=rtl>
	
	<font face=tahoma size=2 color='red' >
	  
	<b> توجه 1:</b>
	همکار گرامی با توجه به اجرای آیین نامه استخدامی اعضای غیر هیئت علمی، احکام سال 93 به زودی قابل رویت می باشد.
	
    <br>
    </font>
	<font face=tahoma size=2 >
	  
	<b> توجه 2:</b>
	احکام قبل از سال 84 در حال تصحیح و ورود به سیستم می باشد که به محض پایان کار آنها را در لیست مشاهده خواهید کرد.
	
    <br>
    </font>
   <!-- <font face=tahoma size=2 color="Red">
	<b>توجه 3 :</b>
	كاربر گرامي چنانچه اطلاعات موجود در صفحه را مشاهده نمي نماييد و يا اطلاعات مربوط به شخص شما نمي باشد ، 
	از منوي tool گزينه connection  را انتخاب نموده و سپس از قسمت مربوط به LAN SETTING تيك مربوط به 
	 proxy  را حذف نماييد.
	</font>-->

</body>
</html>
