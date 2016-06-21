<?php
//-----------------------------
//	Programmer	: B.Mahdipour
//	Date		: 94.05
//-----------------------------
require_once '../../header.inc.php';
require_once inc_dataGrid;
ini_set("display_errors", "on"); 


?>
<html>
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/component.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/message.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/grid/SearchField.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/TreeSearch.js"></script>
</head>
<body dir="rtl">
<?
require_once '../js/BestChildren.js.php';

$dg = new sadaf_datagrid("dg", "../data/BestChildren.data.php?task=GetChildItm", "chgridDIV");
$dg->addColumn("", "BSID", "", true);
$dg->addColumn("", "grade", "", true);
$dg->addColumn("", "sex", "", true);
$dg->addColumn("", "EducLevel", "", true);
$dg->addColumn("", "EducBase", "", true);
$dg->addColumn("", "PicFileType", "", true);
$dg->addColumn("", "PaperFileType", "", true);
$dg->addColumn("", "NationalCode", "", true);

$col = $dg->addColumn("نام", "CFName", "string");
$col->width = 70;

$col = $dg->addColumn("نام خانوادگی", "CLName", "string");
$col->width = 100;

$col = $dg->addColumn("تاریخ درخواست", "RegDate");
$col->renderer = "function(v){return MiladiToShamsi(v);}";
$col->width = 100;

$col = $dg->addColumn(" وضعیت ", "status");
$col->renderer = "function(v,p,r){return ItemStatus(v,p,r);}";
$col->width = 100;

$col = $dg->addColumn("توضیحات", "comments");


$col = $dg->addColumn("عملیات", "", "string");
$col->renderer = "function(v,p,r){return BestChild.opRender(v,p,r);}";
$col->width = 60;

//$dg->addButton("", "ثبت فرد جدید", "add", "function(){BestChildObject.AddRequest();}");

$dg->title = "درخواست های ارسال شده";
$dg->EnableSearch = false ;
$dg->width = 780;
$dg->autoExpandColumn = "comments" ; 
$dg->pageSize = 10;
$grid = $dg->makeGrid_returnObjects();

?>
<script type="text/javascript">
	
BestChild.prototype.afterLoad = function()
{
   this.grid = <?=$grid?>;       
   this.grid.render("chgridDIV"); 
}

</script>

<form id="MainChildForm">
    <center>
        <div id="maindiv"></div><br><br>		
        <div id="MainChildPanel"></div>	
		
		<div id="AgreementPanel">
			<table id="AgrPNL"  style="width:100%" >
				<tr  >
					<td colspan="4" class="blueText">  شرایط احراز <hr></td>
				</tr>
				<tr>
					<td colspan="4">1- دانش‌آموزان ممتاز با شرط معدل به شرح ذيل:	<br><br>
						<table border="1"  style="width:100%"  >
							<tr  style="background-color: #DCDCFF;height:20px "><td>دوره ابتدایی</td><td>دوره متوسطه اول</td><td>دوره متوسطه دوم</td></tr>
							<tr><td>پایه های اول،دوم و سوم:<br> همه دروس نوبت اول و دوم &nbsp;&nbsp; &nbsp; "خیلی خوب"</td>
								<td>پایه اول متوسطه اول:<br> معدل کل 19 به بالا</td>
								<td rowspan="2">پایه اول :&nbsp; 
									معدل کل 18 به بالا <br>
									<hr>
									پایه دوم : &nbsp;
									معدل کل 18 به بالا <br>
									<hr>
									پایه سوم
									:&nbsp; 
									معدل کل 17 به بالا <br>
									<hr>
									 پیش دانشگاهی : &nbsp;
									معدل کل 17 به بالا <br>
								</td>
							</tr>
							<tr>
								<td>پایه های چهارم،پنجم،ششم :<br>
								همه دروس نوبت اول و دوم &nbsp;&nbsp; &nbsp; "خیلی خوب"</td>
								</td>
								<td>پایه دوم متوسطه اول:<br> معدل کل 19 به بالا</td>
								
							</tr>
							
						</table><br>
						
						2- دانش‌آموزان داراي رتبه در مسابقات و جشنواره‌هاي كشوري يا استاني در زمينه‌هاي علمي، ورزشي، هنري و...</td>
				</tr>
				<tr>
					<td colspan="4" class="blueText" ><br>مدارک ثبت نام <hr></td>
				</tr>
				<tr>
					<td colspan="4" > 1- فرم تكميل شده شكوفه‌هاي دانشگاه
						<br>
						2- فايل كارنامه تحصيلي اسكن شده دانش‌آموز كه معدل پايان سال در آن قيد شده و مورد تاييد آموزشگاه قرار گرفته باشد.(حداکثر سایز فایل کارنامه 2 مگا بایت می باشد.)
						<br>
						3- فايل عكس اسكن شده با زمينه سفيد (با رعايت پوشش اسلامي) و وضوح DPI 300-600 (فرمت قابل قبول عکس 
jpeg ، jpg است.
 حداقل سایز عکس 50 کیلو بایت و حداکثر سایز آن 180 کیلو بایت می باشد.) 
						<br>
						4- اسكن حكم يا لوح تقدير براي دانش‌آموزان داراي رتبه در مسابقات و جشنواره‌هاي كشوري يا استاني
						<br><br>
					</td>
				</tr>
			</table>
		</div>	
		
    </center>    
</form>
<center><br><br>
<div id="chgridDIV"></div>
</center>  

</body>
</html>