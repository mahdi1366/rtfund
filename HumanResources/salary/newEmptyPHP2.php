<?php 
/*
 صفحه  نمایش لیست و مدیریت داده ها مربوط به : فرم آموزش به همکار
	برنامه نویس:   مکیال اخلاقی
	تاریخ ایجاد: 92-9-10
*/
include("header.inc.php");
include("../sharedClasses/SharedClass.class.php");
include("classes/SEVL_Suggestion.class.php");
include("classes/SEVL_EvlForms.class.php");
include("../staff/PAS/PAS_shared_utils.php");
include "classes/ChartServices.class.php";
include('../sharedClasses/sendLetterModule.php');
include("classes/SEVL_ExperimentalDocuments.class.php");
include("classes/SEVL_TeacherStudent.class.php");
include("classes/SEVL_TechnicalReport.class.php");
include("classes/SEVL_Indicators.class.php");
include ("classes/SEVL_IndictGrps.class.php");

HTMLBegin();
?>
<META http-equiv=Content-Type content="text/html; charset=UTF-8" >
<link rel="stylesheet" type="text/css" href="/sharedClasses/resources/css/ext-all.css" />
<body dir='rtl'>
<?
/*if(isset($_REQUEST["saveBtn"])) 
{		
	$keys = array_keys($_POST);
	 
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"rd_") !== false)
		{
			$arr = preg_split('/_/', $keys[$i]);
			
			manage_SEVL_TeacherStudent::UpdateStuStatus($arr[1] , $_REQUEST[$keys[$i]] ,$_REQUEST['com_'.$arr[1]]) ; 
		}				
	}
	
	echo SharedClass::CreateMessageBox("اطلاعات ذخیره شد");
}  */
if($_SESSION['PersonID'] == '200037' ) 
    {
    
    echo "******" ; die();
    }
if(!empty($_GET['EvlFormID']) )
{

	$mysql = pdodb::getInstance(); 
	$qry = " SELECT p.pfname , p.plname
				 FROM ease.SEVL_EvlForms se inner join ease.persons p
															on se.PersonID = p.PersonID

					where se.EvlFormID =".$_GET['EvlFormID'] ;
					
	$mysql->Prepare($qry);
	$Pres = $mysql->ExecuteStatement(array());
	$Prec=$Pres->fetch(); 
	
	  
	
}

if($_GET['colleague'] == true)
{
 //...... آخرین دوره ارزیابی را نشان بدهد .................
 $periodID = $_GET['PeriodID'] ;  

 $res = manage_SEVL_TeacherStudent::GetList(NULL,$_SESSION['PersonID'],NULL,NULL,$periodID);
 
?>

<center><br><br>
<fieldset  style="border-color: #99BBE8;width:900px">
<legend class='greenText'>&nbsp;آموزش <?=$Prec['plname']." ".$Prec['pfname'];?></legend>
<br><br>
<form name="AmoozeshForm" method="post"> 
<table align=center border=1 width='80%' cellspacing=0 cellpadding=5 dir = 'rtl' style="font-family: Tahoma">
<tr bgcolor="#CCE0CC">
	<td colspan="8">
	آموزش به همکار
	</td>
</tr>
<tr class="HeaderOfTable">	
	<td width="2%">ردیف</td>	
	<td width="2%">مشاهده</td>	
	<td width="15%">آموزش دهنده</td>	
	<td width="35%" >موارد آموزشی</td>	
	<td width="8%" > &nbsp; مدت</td>		
	<td width="12%" > وضعیت</td>		
</tr>
<? 
$ActiveSave = 0 ; 
for($k=0; $k<count($res); $k++)
{
	if($k%2==0)
		echo "<tr class=\"OddRow\">";
	else
	echo "<tr class=\"EvenRow\">";	
	echo "<td align='center'>".($k+1)."</td>";    
	echo "<td><a target=\"_blank\" href=\"NewSEVL_TeacherStudent.php?UpdateID=".$res[$k]->TeacherStudentID."\"><img src='images/view.png' title='مشاهده'></a></td>";  
    echo "	<td>".$res[$k]->TeacherID_FullName."</td>";	
	echo "	<td>".$res[$k]->TeachingContent."</td>";	
	echo "	<td>&nbsp;&nbsp;".$res[$k]->TeachingTime."</td>";		
	echo "<td colspan='2' >".$res[$k]->TeacherStudentStatus_Desc."</td>";
	/*if($res[$k]->TeacherStudentStatus != 3  && $res[$k]->TeacherStudentStatus != 4 ) { 
		echo "	<td><input type=radio name='rd_".$res[$k]->TeacherStudentID."' value='1' " ; 
		if($res[$k]->TeacherStudentStatus == 1 )   echo " checked ";  echo "> &nbsp;تائید&nbsp;		
					<input type=radio name='rd_".$res[$k]->TeacherStudentID."' value='2' "; 
		if($res[$k]->TeacherStudentStatus == 2 )   echo " checked ";  echo "> &nbsp;عدم تائید 	</td>";
		echo "<td><textarea name='com_".$res[$k]->TeacherStudentID."' id='com_".$res[$k]->TeacherStudentID."' cols='45' rows='1'>".$res[$k]->comment."</textarea></td>" ;  
	}
	else {
		echo "<td colspan='2' >".$res[$k]->TeacherStudentStatus_Desc."</td>";
		echo "<td  >&nbsp;".$res[$k]->comment."</td>" ;  
	}
	echo "</tr>";
	if( $res[$k]->TeacherStudentStatus !=3 && $res[$k]->TeacherStudentStatus !=4 ) $ActiveSave = 1 ; */
}

?>

<tr class="FooterOfTable">
<td colspan="8" align="center">
<? if($ActiveSave == 1 ) { ?>
	<input type="submit" name='saveBtn' value="اعمال">   
<? } ?>
</td>
</tr></form>
</table><br>
<table align=center width='78%'  >
<tr ><td colspan='8'><font style='color:red;font-size:12px'>
*توجه : لطفا کلیه فرمهای مربوط به آموزش را مشاهده و بررسی فرمایید.
</font></td></tr><tr><td><br></td></tr></table><br><br>
</fieldset>
<br>
<? 
 
 
}
else{
//................... کد فرد و دوره ....................
$mysql = pdodb::getInstance();
$qry = " select PersonID ,EvlPeriodID  
			from ease.SEVL_EvlForms 
				where EvlFormID = ".$_GET['EvlFormID'] ; 
		
$mysql->Prepare($qry);
$Pres = $mysql->ExecuteStatement(array());
$rec=$Pres->fetch();

//......................................................	
$res = manage_SEVL_TeacherStudent::GetList($rec['PersonID'],NULL,NULL,$rec['EvlPeriodID']); 
$res2 = manage_SEVL_TechnicalReport::GetList($rec['PersonID'],$rec['EvlPeriodID']); 

//----------------امتیاز حاصل از شرکت در دوره های آموزشی-----------------------------------

$query = " select TotalHour 
					from  ease.SEVL_StaffTotalClass  
						where PersonID= ? AND EvlPeriodID = ?  " ; 
$mysql = pdodb::getInstance();
$mysql->Prepare ($query); 

$res1 = $mysql->ExecuteStatement (array ($rec['PersonID'],$rec['EvlPeriodID']));
$rec1=$res1->fetch() ; 
$CoursePass = ( $rec1['TotalHour'] / 21 ) * 2.5  ; 

if( $CoursePass >= 2.5 ) 
	$TrainScore = 20 ; 
	
elseif($CoursePass >= 2 && $CoursePass < 2.5 )
	   $TrainScore = 16 ; 
	   
elseif($CoursePass >= 1.5 && $CoursePass < 2 )
	   $TrainScore = 12 ; 
	   
elseif($CoursePass >= 1 && $CoursePass < 1.5 )
	   $TrainScore = 8 ; 
	   
elseif($CoursePass >= 0.5 && $CoursePass < 1)
	   $TrainScore = 4 ; 
	   
//..................تدریس در زمینه شغل مورد تصدی .......................................

$query = " select TeachHour , SatisfyPercent  
					from  ease.SEVL_StaffTeaching
						where PersonID= ? AND EvlPeriodID = ? " ; 
$mysql->Prepare ($query);
$res3 = $mysql->ExecuteStatement (array ($rec['PersonID'],$rec['EvlPeriodID']));
$k=0;
$TeachingScore= 0 ; 
$SatisfyScore= 0 ; 
while($rec3=$res3->fetch())
{ 
	//......مدت زمان دوره ...
	if($rec3['TeachHour'] < 8 )
	   $TeachingScore += 3 ;     
	else 
	   $TeachingScore += 6 ;
	   
   //..... نتیجه نظر سنجی...
   if($rec3['SatisfyPercent'] < 30 )
	   $SatisfyScore += 0 ; 
    
   elseif( $rec3['SatisfyPercent'] >= 30  && $rec3['SatisfyPercent'] < 50) 
	   $SatisfyScore += 1 ;
	   
   elseif( $rec3['SatisfyPercent'] >= 50  && $rec3['SatisfyPercent'] < 80) 
	   $SatisfyScore += 2 ;
	   
   elseif( $rec3['SatisfyPercent'] >= 80 ) 
	   $SatisfyScore += 4 ;
	
$k++;
}

//......	 دستاوردهای علمی و پژوهشی.......................
$resResearch = manage_SEVL_TechnicalReport::GetResearchList($rec['PersonID'],$rec['EvlPeriodID']); 
?>

<center><br><br>
<fieldset  style="border-color: #99BBE8;width:800px">
<legend class='greenText'>&nbsp;آموزش <font color='#1D5539' style='font-size:12px;font-weight:bold' ><?=$Prec['pfname']." ".$Prec['plname'];?></font></legend>
<br>
<table align=center border=1 width='90%' cellspacing=0 cellpadding=5 dir = 'rtl' style="font-family: Tahoma">
<tr class="HeaderOfTable">
	<td>ردیف</td>
	<td> عنوان</td>
	<td width='10%'>حداکثر امتیاز</td>
	<td width="12%" align='center'>امتیاز</td>
</tr>
<tr> 
	<td> 1 </td>
	<td> امتیاز شرکت در دوره های آموزشی *</td>	
	<td align='center' >15</td>
	<td align='center' >&nbsp;<?=$TrainScore?></td>
</tr>
<tr> 
	<td> 2 </td>
	<td>امتیاز تدریس در زمینه شغل مورد تصدی *</td>
	<td align='center' >10</td>
	<td align='center' ><?=($TeachingScore + $SatisfyScore)?></td>
</tr>
<tr bgcolor="#CCE0CC">
	<td>3</td>
	<td>
	آموزش به همکار
	</td>
	<td align='center' >5</td>
	<td align='center' >&nbsp;</td>
</tr>
</table>
<table align=center border=1 width='90%' cellspacing=0 cellpadding=5 dir = 'rtl' style="font-family: Tahoma">
<tr class="HeaderOfTable">	
	<td width="1%">ردیف</td>
	<td width="2%">مشاهده</td>
	<td>شخص اموزش گیرنده</td>
	<td>طول ساعات</td>
	<td width="15%" >وضعیت**</td>	
	<td>حداکثر امتیاز</td>	
	<td  width="12%" >امتیاز مکتسبه</td>

</tr>
<?
$SumA  = 0 ;
for($k=0; $k<count($res); $k++)
{
	if($k%2==0)
		echo "<tr class=\"OddRow\">";
	else
	echo "<tr class=\"EvenRow\">";	
	echo "<td>".($k+1)."</td>"; //&MMode=true
	
	if($_GET['SVR'] == true )
		echo "<td><a target=\"_blank\" href=\"NewSEVL_TeacherStudent.php?UpdateID=".$res[$k]->TeacherStudentID."&SVR=true\"><img src='images/view.png' title='مشاهده'></a></td>";  
	if($_GET['MMode'] == true )
		echo "<td><a target=\"_blank\" href=\"NewSEVL_TeacherStudent.php?UpdateID=".$res[$k]->TeacherStudentID."&MMode=true\"><img src='images/view.png' title='مشاهده'></a></td>";  
		
    echo "	<td>".$res[$k]->StudentID_FullName."</td>";
	echo "	<td>".$res[$k]->TeachingTime."</td>";
	echo "	<td>".htmlentities($res[$k]->TeacherStudentStatus_Desc, ENT_QUOTES, 'UTF-8')."</td><td align='center' >10</td>";	
//		echo "	<td>".str_replace("\n", "<br>", htmlentities($res[$k]->TeachingContent))."</td>";
	echo "<td align='center'  >&nbsp;".$res[$k]->TeachStuGrade ."</td>";	
	echo "</tr>";
	$SumA += $res[$k]->TeachStuGrade ; 
}
?>
<tr><td colspan="5" height='30px'>
<b>جمع کل امتیاز : &nbsp;&nbsp; </b></td>
<td align='center' width='10%'>30</td>
	<td align='center' ><?=($SumA + $TeachingScore + $SatisfyScore +  $TrainScore )?></td></tr>
</table><br>
<table align=center border=0 width='90%' ><tr ><td colspan='8'><font style='color:red'>
* توجه : امتیاز  دو مولفه اول از سیستم آموزش محاسبه شده است.  <br>
** لطفا فرمهای مربوط به آموزش را مشاهده و بررسی نمایید.
</font><br></td></tr></table><br>
</fieldset>
<br>
<fieldset  style="border-color: #99BBE8;width:800px">
<legend class='greenText'>&nbsp;دستاوردهای علمی و پژوهشی<font color='#1D5539' style='font-size:12px;font-weight:bold' ><?=$Prec['pfname']." ".$Prec['plname'];?></font></legend>
<form id="techForm" name="techForm" method="post" enctype="multipart/form-data" > 
<br><br>
<table align=center border=1 width='90%' cellspacing=0 cellpadding=5 dir = 'rtl' style="font-family: Tahoma">
<tr class="HeaderOfTable">
	<td>ردیف</td>
	<td colspan='2'>نوع دستاورد علمی </td>
	<td width='10%'>حداکثر امتیاز</td>
	<td width="12%">امتیاز سرپرست </td>
</tr>
<tr >
	<td align='center'>1</td>
	<td colspan='2'>ثبت اختراع</td>
	<td align='center' >20</td>
	<td align='center' >&nbsp;<?=$resResearch['invention']['TotalScore']?></td>
</tr> 
<tr >
	<td align='center'>2</td>
	<td colspan='2'>تالیف یا ترجمه کتاب</td>
	<td align='center' >20</td>
	<td align='center' >&nbsp;<?=$resResearch['book']['TotalScore']?></td>
</tr>
<tr >
	<td align='center'>3</td>
	<td colspan='2'>چاپ مقاله تألیفی</td>
	<td align='center' >15</td> 
	<td align='center' >&nbsp;<?=$resResearch['paper']['TotalScore']?></td>
</tr>
<tr >
	<td align='center'>4</td>
	<td colspan='2'>چاپ مقاله ترجمه شده</td>
	<td align='center' >10</td> 
	<td align='center'>&nbsp;<?=$resResearch['TranslatedPaper']['TotalScore']?></td>
</tr>
<tr >
	<td align='center' >5</td>
	<td colspan='2'>دریافت جوایز از مراجع، مراکز یا جشنواره هاي معتبر ملی یا بین المللی (جشنواره خوارزمی، جشنواره جوان خوارزمی، جشنواره
فارابی، جشنواره فردوسی، جشنواره علم و عمل، جشنواره پژوهشگران برگزیده و جوایز دریافت شده از سایر مراکز معتبر ملی
یا بین المللی با تأیید معاونت پژوهش و فناوري دانشگاه)</td>
	<td align='center' >20</td>
	<td align='center' >&nbsp;<?=$resResearch['ScientificReward']['TotalScore']?></td>
</tr> 
<tr >
	<td align='center' >6</td>
	<td colspan='2'>ارائه مقاله درمجامع علمی</td>
	<td align='center' >15</td>
	<td align='center' >&nbsp;<?=$resResearch['ConferencePaper']['TotalScore']?></td>
</tr> 
<tr >
	<td align='center' >7</td>
	<td colspan='2'>ارائه سخنرانی درداخل یاخارج از دانشگاه</td>
	<td align='center' >10</td>
	<td align='center' >&nbsp;<?=$resResearch['lecture']['TotalScore']?></td>
</tr>
<tr >
	<td align='center' >8</td>
	<td colspan='2'>همکاري درطرح هاي پژوهشی برون دانشگاهی متناسب با شغل فرد یا کارکرد دانشگاهی</td>
	<td align='center' >10</td>
	<td align='center' >&nbsp;<?=$resResearch['AppPlanCoHelper']['TotalScore']?></td>
</tr>
<tr >
	<td align='center' >9</td>
	<td colspan='2'>همکاري درطرح هاي مطالعاتی دانشگاه متناسب با شغل فرد یا کارکرد دانشگاهی</td>
	<td align='center' >10</td>
	<td align='center' >&nbsp;<?=$resResearch['TechnicalReport']['TotalScore']?></td>
</tr>
<tr bgcolor="#CCE0CC">
	<td>10</td>
	<td colspan="2">
	 گزارشات تخصصی داوطلبانه
	</td>
	<td align='center' >10</td><td>&nbsp;</td>
</tr>
</table>
<table align=center border=1 width='90%' cellspacing=0 cellpadding=5 dir = 'rtl' style="font-family: Tahoma">
<tr class="HeaderOfTable">	
	<td width="1%">ردیف</td>
	<?if($_GET['SVR'] == true ) {?>	
	<td width="5%" >مشاهده</td><?}?>
	<td width="55%" >عنوان گزارش</td>
	<?if($_GET['SVR'] != true ) {?>
	<td>فایل</td><?}?>
	<td width="15%"  >وضعیت</td>	
	<td width="10%" >حداکثر امتیاز</td>	
	<td width="12%" >امتیاز سرپرست</td>

</tr>
<?
$TecrepScore = 0 ; 
for($k=0; $k<count($res2); $k++)
{
	if($k%2==0)
		echo "<tr class=\"OddRow\">";
	else
		echo "<tr class=\"EvenRow\">";	
		echo "<td>".($k+1)."</td>";
	if($_GET['SVR'] == true){
		echo "	<td>";
		echo "<a target=\"_blank\" href=\"NewSEVL_TechnicalReport.php?UpdateID=".$res2[$k]->TechnicalReportID."&view=true\">";
		echo "<img src='images/view.png' title='ویرایش'>";
		echo "</a></td>";
	}
	echo "	<td>".$res2[$k]->ReportTitle."</td>";
	if($_GET['SVR'] != true ) { echo "	<td><a target=\"_blank\" href=\"GetDocuments.php?DocID=".$res2[$k]->TechnicalReportID."&type=2\">".$res2[$k]->FileName."</td>"; } 
	echo "	<td>".htmlentities($res2[$k]->RepStatus_Desc, ENT_QUOTES, 'UTF-8')."</td>";
	echo "	<td align='center' > 10 </td>";	
	echo "<td align='center' >".($res2[$k]->SupGrade1 + $res2[$k]->SupGrade2 + $res2[$k]->SupGrade3 + $res2[$k]->SupGrade4 + $res2[$k]->SupGrade5)."</td>";	
	echo "</tr>";
	$TecrepScore += ($res2[$k]->SupGrade1 + $res2[$k]->SupGrade2 + $res2[$k]->SupGrade3 + $res2[$k]->SupGrade4 + $res2[$k]->SupGrade5) ;
}
?>
<tr><td colspan="4" height='30px'><b>جمع کل امتیاز : &nbsp;&nbsp; </b></td>
<td align='center' width='10%'>140</td>
	<td align='center' ><?=($TecrepScore + $resResearch['Sum']['TotalScore'] )?></td></tr>
</table><br><br>
<table align=center border=0 width='90%' ><tr ><td colspan='8'><font style='color:red'>
* لطفا فرمهای مربوط به گزارشات تخصصی را مشاهده و بررسی نمایید.
</font><br></td></tr></table><br>
</form></fieldset><br>
<fieldset  style="border-color: #99BBE8;width:800px">
<legend class='greenText'>&nbsp;مستندسازی تجربیات<font color='#1D5539' style='font-size:12px;font-weight:bold' ><?=$Prec['pfname']." ".$Prec['plname'];?></font></legend>
<?php 
$res = manage_SEVL_ExperimentalDocuments::Search("", "","CreatorID=".$rec["PersonID"]." AND se.EvlPeriodID=".$rec['EvlPeriodID'], $FromRec, $NumberOfRec , 2 ); 
 
?>

<form id="SearchForm" name="SearchForm" method=post> 
<input type="hidden" name="PageNumber" id="PageNumber" value="0">
</form>

<form id="ListForm" name="ListForm" method="post"> 
<? if(isset($_REQUEST["PageNumber"]))
	echo "<input type=\"hidden\" name=\"PageNumber\" value=".$_REQUEST["PageNumber"].">"; ?>
<br><table align=center border=1 width='90%' cellspacing=0 cellpadding=5 dir = 'rtl' style="font-family: Tahoma">
<tr class="HeaderOfTable">	
	<td width="1%">ردیف</td>
	<td width="2%">مشاهده</td>	
	<td>عنوان</td>
	<td>وضعیت</td>
	<td width="12%" >حداکثر امتیاز</td>
	<td width="12%" >امتیاز مکتسبه</td>	
</tr>
<?
for($k=0; $k<count($res); $k++)
{
	if($k%2==0)
		echo "<tr class=\"OddRow\">";
	else
		echo "<tr class=\"EvenRow\">";
	
	echo "<td>".($k+$FromRec+1)."</td>";
	echo "	<td>";
   	
	if($_GET['SVR'] == true )
		echo "<a target=\"_blank\" href=\"NewSEVL_ExperimentalDocuments.php?UpdateID=".$res[$k]->ExperimentalDocumentID."&SVR=true\">";  
	if($_GET['MMode'] == true )
		echo "<a target=\"_blank\" href=\"NewSEVL_ExperimentalDocuments.php?UpdateID=".$res[$k]->ExperimentalDocumentID."&MMode=true\">";  
		
    echo "<img src='images/view.png' title='ویرایش'>";
	echo "</a></td>";		
	echo "	<td>".str_replace("\n", "<br>",htmlentities($res[$k]->ExperimentTitle, ENT_QUOTES, 'UTF-8'))."</td>";
	echo "	<td>".htmlentities($res[$k]->DocStatus_Desc, ENT_QUOTES, 'UTF-8')."</td><td align='center'>10</td>";
	echo "<td align='center' >".($res[$k]->SupGrade1 + $res[$k]->SupGrade2 + $res[$k]->SupGrade3 + $res[$k]->SupGrade4 + $res[$k]->SupGrade5)."</td>";	
	echo "</tr>";
	$DocScore += ($res[$k]->SupGrade1 + $res[$k]->SupGrade2 + $res[$k]->SupGrade3 + $res[$k]->SupGrade4 + $res[$k]->SupGrade5) ; 
}
?>
<tr>
	<td colspan="4" height='30px'><b>جمع کل امتیاز : &nbsp;&nbsp; </b></td>
	<td align='center' width='10%'>10</td>
	<td align='center' >&nbsp;<?=($DocScore)?></td>
</tr>
<tr bgcolor="#cccccc"><td colspan="8" align="right">

</tr>

</table>
<br><br>
<table align=center border=0 width='90%' ><tr ><td colspan='8'><font style='color:red'>
* لطفا فرم های مربوط به مستندسازی تجربیات را مشاهده و بررسی نمایید.
</font><br></td></tr></table><br>
</form>
</table></fieldset><br>
<?
$MeritRes = manage_SEVL_ExperimentalDocuments::Search("", "", "CreatorID=".$rec["PersonID"]." AND se.EvlPeriodID=".$rec['EvlPeriodID'], $FromRec, $NumberOfRec , '1'); 
$Merit1 = $Merit2 = $Merit3 = $Merit4 = $Merit5 = $Merit6 = $Merit7 = $Merit8 = $Merit9 = $Merit10 = $Merit11 = $Merit12 = $Merit13 = 0 ; 
$Merit1no =  $Merit2no = $Merit3no =  $Merit4no =  $Merit5no = $Merit6no = $Merit7no = $Merit8no = $Merit9no = $Merit10no = $Merit11no = $Merit12no = $Merit13no = 0 ; 
for($i=0; $i <count($MeritRes) ; $i++)
{
	if($MeritRes[$i]->MeritType == 1 ){
	   $Merit1 += $MeritRes[$i]->DocGrade ;
	   $Merit1no++ ; 
	}   	   
   elseif($MeritRes[$i]->MeritType == 2 ){
	   $Merit2 += $MeritRes[$i]->DocGrade ;
	   $Merit2no++ ;
    }
   elseif($MeritRes[$i]->MeritType == 3 ){
	   $Merit3 += $MeritRes[$i]->DocGrade ;
	   $Merit3no++ ;
   }	   
   elseif($MeritRes[$i]->MeritType == 4 ){
	   $Merit4 = $MeritRes[$i]->DocGrade ;
	   $Merit4no++ ;
	}   
   elseif($MeritRes[$i]->MeritType == 5 ){
	   $Merit5 += $MeritRes[$i]->DocGrade ;
	   $Merit5no++ ;
	}   
    elseif($MeritRes[$i]->MeritType == 6 ){
	   $Merit6 += $MeritRes[$i]->DocGrade ;
	   $Merit6no++ ;
	}   
    elseif($MeritRes[$i]->MeritType == 7 ){
	   $Merit7 += $MeritRes[$i]->DocGrade ;
	   $Merit7no++ ;
	}   
   elseif($MeritRes[$i]->MeritType == 8 ){
	   $Merit8 += $MeritRes[$i]->DocGrade ;
	   $Merit8no++ ;
	}   
    elseif($MeritRes[$i]->MeritType == 9 ){
	   $Merit9 += $MeritRes[$i]->DocGrade ;
	   $Merit9no++ ;
	}   
    elseif($MeritRes[$i]->MeritType == 10 ){
	   $Merit10 += $MeritRes[$i]->DocGrade ;
	   $Merit10no++ ;
   }
   elseif($MeritRes[$i]->MeritType == 11 ){
	   $Merit11 += $MeritRes[$i]->DocGrade ;
	   $Merit11no++ ;
   }
   elseif($MeritRes[$i]->MeritType == 12 ){
	   $Merit12 += $MeritRes[$i]->DocGrade ;
	   $Merit12no++ ;
   }
   elseif($MeritRes[$i]->MeritType == 13 ){
	   $Merit13 += $MeritRes[$i]->DocGrade ;
	   $Merit13no++ ;
   }
}
$MeritScore = ($Merit1 + $Merit2 + $Merit3 + $Merit4 + $Merit5 + $Merit6 + $Merit7 + $Merit8 + $Merit9 + $Merit10 + $Merit11 + $Merit12 + $Merit13) / 22 ; 

?>
<fieldset  style="border-color: #99BBE8;width:800px">
<legend class='greenText'>&nbsp;دریافت نشان لیاقت<font color='#1D5539' style='font-size:12px;font-weight:bold' ><?=$Prec['pfname']." ".$Prec['plname'];?></font>
</legend>
<div id='D'>
<br><table align=center border=1 width='90%' cellspacing=0 cellpadding=5 dir = 'rtl' style="font-family: Tahoma">
<tr class="HeaderOfTable">
	<td>ردیف</td>
	<td colspan='2'>نوع نشان لیاقت </td>	
	<td>تعداد</td>
	<td width='10%'>حداکثر امتیاز</td>
	<td>امتیاز مکتسبه</td>
</tr> 
<tr >
	<td>1</td>
	<td colspan='2'>دریافت تقدیرنامه از رئیس جمهور</td>
	<td>&nbsp;<?=$Merit1no?></td>
	<td align='center' >10</td>	
	<td align='center' >&nbsp;<?=$Merit1?></td>
</tr>
<tr >
	<td>2</td>
	<td colspan='2'>دریافت تقدیرنامه از وزیر</td>
	<td>&nbsp;<?=$Merit2no?></td>
	<td align='center' >9</td>	
	<td align='center' >&nbsp;<?=$Merit2?></td>
</tr>
<tr >
	<td>3</td>
	<td colspan='2'>دریافت تقدیرنامه از معاون رئیس جمهور</td>
	<td>&nbsp;<?=$Merit3no?></td>
	<td align='center' >9</td>
	<td align='center' >&nbsp;<?=$Merit3?></td>
</tr>
<tr >
	<td>4</td>
	<td colspan='2'>دریافت تقدیرنامه از معاون وزیر</td>
	<td>&nbsp;<?=$Merit4no?></td>
	<td align='center' >8</td>
	<td align='center' >&nbsp;<?=$Merit4?></td>
</tr>
<tr >
	<td>5</td>
	<td colspan='2'>دریافت تقدیرنامه از استاندار</td>
	<td>&nbsp;<?=$Merit5no?></td>
	<td align='center' >7</td>
	<td align='center' >&nbsp;<?=$Merit5?></td>
</tr>
<tr >
	<td>6</td>
	<td colspan='2'>دریافت تقدیرنامه از فرماندار</td>
	<td>&nbsp;<?=$Merit6no?></td>
	<td align='center' >6</td>
	<td align='center' >&nbsp;<?=$Merit6?></td>
</tr>
<tr >
	<td>7</td>
	<td colspan='2'>دریافت تقدیرنامه از رئیس دانشگاه</td>
	<td>&nbsp;<?=$Merit7no?></td>
	<td align='center' >7</td>
	<td align='center' >&nbsp;<?=$Merit7?></td>
</tr>
<tr >
	<td>8</td>
	<td colspan='2'>دریافت تقدیرنامه از معاونان دانشگاه</td>
	<td>&nbsp;<?=$Merit8no?></td>
	<td align='center' >6</td>
	<td align='center' >&nbsp;<?=$Merit8?></td>
</tr>
<tr >
	<td>9</td>
	<td colspan='2'>دریافت تقدیرنامه از رئیس دانشکده</td>
	<td>&nbsp;<?=$Merit9no?></td>
	<td align='center' >6</td>
	<td align='center' >&nbsp;<?=$Merit9?></td>
</tr>
<tr >
	<td>10</td>
	<td colspan='2'>دریافت تقدیرنامه ازمدیر واحد</td>
	<td>&nbsp;<?=$Merit10no?></td>
	<td align='center' >5</td>
	<td align='center' >&nbsp;<?=$Merit10?></td>
</tr> 
<tr >
	<td>11</td>
	<td colspan='2'>کسب عنوان کارمند نمونه درسطح کشور</td>
	<td>&nbsp;<?=$Merit11no?></td>
	<td align='center' >9</td>
	<td align='center' >&nbsp;<?=$Merit11?></td>
</tr> 
<tr >
	<td>12</td>
	<td colspan='2'>کسب عنوان کارمند نمونه درسطح دانشگاه</td>
	<td>&nbsp;<?=$Merit12no?></td>
	<td align='center' >4</td>
	<td align='center' >&nbsp;<?=$Merit12?></td>
</tr> 
<tr >
	<td>13</td>
	<td colspan='2'>کسب عنوان کارمند نمونه درسطح واحد</td>
	<td>&nbsp;<?=$Merit13no?></td>
	<td align='center' >2</td>
	<td align='center' >&nbsp;<?=$Merit13?></td>
</tr> 
<tr>
<td colspan="4" height='30px'><b>جمع کل امتیاز : &nbsp;&nbsp; </b></td>
<td align='center' width='10%'>88</td>
<td align='center' >&nbsp;<?=($Merit1 + $Merit2 + $Merit3 + $Merit4 + $Merit5 + $Merit6 + $Merit7 + $Merit8 + $Merit9 + $Merit10 + $Merit11 + $Merit12 + $Merit13 )?></td>
</tr>
</table>
</div><br>
</fieldset>
<br><br>
<?

//........................................... محاسبه امتیاز پیشنهادات.................................

$query = " SELECT ImplementedSuggests , AcceptedSuggests , UnitSug , UnvSug  
				FROM ease.SEVL_StaffSuggests 
					where PersonID = ? and EvlPeriodID = ? " ; 

$mysql->Prepare ($query);
$res5 = $mysql->ExecuteStatement (array ($rec["PersonID"],$rec['EvlPeriodID']));
$SSUG=$res5->fetch(); 

//.... هدف گذاری 0.5 برای سال 91 برای پیشنهاد پذیرفته شده
//..... هدفگذاری 0.3 برای سال 91 برای پیشنهاد اجرا شده
//....................پیشنهاد در سطح واحد ...............................
//$UnitSug = ($SSUG['UnitSug'] / 1.5) * 1.25  ; 
$UnitSug = ($SSUG['UnitSug'] / 0.75) * 1.25  ; 

if( $UnitSug > 20 )
	$UnitSugScore = 1.25;
elseif( $UnitSug >15 && $UnitSug <= 20  )
	$UnitSugScore = 1 ;
elseif( $UnitSug >10 && $UnitSug <= 15  )
	$UnitSugScore = 0.75 ;
elseif( $UnitSug >5 && $UnitSug <= 10  )
	$UnitSugScore = 0.5;
elseif( $UnitSug > 0 && $UnitSug <= 5)
	$UnitSugScore = 0.25;

//....................پیشنهاد در سطح دانشگاه.............................
//$UnvSug = ($SSUG['UnvSug'] / 1.5) * 0.75  ; 
$UnvSug = ($SSUG['UnvSug'] / 0.75) * 0.75  ; 

if( $UnvSug > 10 )
	$UnvSugScore = 0.75;
elseif( $UnvSug > 5 && $UnvSug <= 10  )
	$UnvSugScore = 0.5 ;
elseif( $UnvSug > 0 && $UnvSug <= 5  )
	$UnvSugScore = 0.25 ;

//.................... پیشنهاد پذیرفته شده......................
//$AccSug = ($SSUG['AcceptedSuggests'] / 0.5) * 0.75  ; 
$AccSug = ($SSUG['AcceptedSuggests'] / 0.25) * 0.75  ; 

if($AccSug > 10 )
	$AccSugScore = 0.75 ; 	
elseif($AccSug > 5 && $AccSug <= 10 )
	$AccSugScore = 0.5 ; 	
elseif($AccSug > 0 && $AccSug <= 5 )
	$AccSugScore = 0.25 ; 
	
//...................  پیشنهاد اجرا شده .............................

//$ImpSug = ($SSUG['ImplementedSuggests'] / 0.3) * 1.25  ; 
$ImpSug = ($SSUG['ImplementedSuggests'] / 0.15) * 1.25  ; 

if($ImpSug > 40 )
	$ImpSugScore = 1.25 ;  	
elseif($ImpSug  > 30 && $ImpSug  <= 40 )
	$ImpSugScore = 1 ;  	
elseif($ImpSug > 20 && $ImpSug <= 30 )
	$ImpSugScore = 0.75 ;  
elseif($ImpSug > 10 && $ImpSug <= 20 )
	$ImpSugScore = 0.5 ; 
elseif($ImpSug > 0 && $ImpSug <= 10 )
	$ImpSugScore = 0.25 ; 	
	
$SugScore = $UnitSugScore + $UnvSugScore + $AccSugScore + $ImpSugScore ; 
?>
<fieldset  style="border-color: #99BBE8;width:800px">
<legend class='greenText'>&nbsp;پیشنهادات<font color='#1D5539' style='font-size:12px;font-weight:bold' ><?=$Prec['pfname']." ".$Prec['plname'];?></font></legend>
<div id='E'>
<br>
<table  align=center width='70%' border=1  cellspacing=0 cellpadding=5 dir = 'rtl'  style="color:black">
<tr class="HeaderOfTable">
<td colspan='2'>
نوع پیشنهاد
</td>
<td align=center width='20%' >
حداکثر امتیاز
</td>
<td align=center >
امتیاز مکتسبه
</td>
</tr>
<tr height='30px'>
	<td colspan=2>پیشنهاد ارائه شده در سطح واحد	</td>   
	<td align=center >1.25</td> <td align=center >&nbsp;<?=$UnitSugScore?></td> 
</tr>
<tr height='30px' >
	<td colspan=2> پیشنهاد ارائه شده در سطح دانشگاه
	</td>
	<td align=center >0.75</td> <td align=center >&nbsp;<?=$UnvSugScore?></td> 
</tr>	
<tr height='30px' >
	<td colspan=2>پیشنهاد پذیرفته شده
	</td>
	<td align=center >0.75</td> <td align=center >&nbsp;<?=$AccSugScore?></td> 
</tr>	
<tr height='30px' >
	<td colspan=2>پیشنهاد اجرا شده	
	</td>
	<td align=center >1.25</td>
	<td align=center >&nbsp;<?=$ImpSugScore?></td> 
</tr>
<tr>
<td colspan="2" height='30px'><b>جمع کل امتیاز : &nbsp;&nbsp; </b></td>
<td align='center' width='10%'>4</td>
<td align='center' >&nbsp;<?=($AccSugScore + $ImpSugScore + $UnitSugScore + $UnvSugScore)?></td>
</tr>
</table><br>
</div>
</fieldset><br><br>

<? } ?>
</center>
<script>
	<? echo $LoadDataJavascriptCode; ?>
	function ValidateForm()
	{
		if(document.f1.Item_EvlPeriodID.value == "0")
                {
                    alert('لطفا دوره ارزیابی را تعیین کنید');
                    return;
		}
                if(document.f1.Item_StudentID.value == "")
                {
                    alert('لطفا همکار آموزش گیرنده را تعیین کنید');
                    return;
                }
                else
                    document.f1.submit();
	}
</script>
</html>