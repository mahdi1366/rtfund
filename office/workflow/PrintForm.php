<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../header.inc.php';
require_once 'form.class.php';
require_once 'form.class.php';

$ReqObj = new WFM_requests($_REQUEST['RequestID']);

$temp = WFM_FormItems::Get(" AND FormID=0");
$ReqItems = $temp->fetchAll();
$ReqItemsStore = array();
foreach ($ReqItems as $it) {
    $ReqItemsStore[$it['FormItemID']] = $it;
}


$content = $ReqObj->ReqContent;
if($ReqObj->ReqContent == "" && !empty($_REQUEST["FormID"]))
{
	$FormObj = new WFM_forms($_REQUEST["FormID"]);
	$content = $FormObj->FormContent;
}
$res = explode(WFM_forms::TplItemSeperator, $content);


$ReqItems = WFM_RequestItems::Get(" AND RequestID=?", array($ReqObj->RequestID));
$ReqItems = $ReqItems->fetchAll();

$ValuesStore = array();
foreach ($ReqItems as $row) 
{
	$ValuesStore[$row['FormItemID']] = $row['ItemValue'];
			
	if($row["ItemType"] == "shdatefield")
		$ValuesStore[$row['FormItemID']] = DateModules::miladi_to_shamsi($row['ItemValue']);
	if($row["ItemType"] == "currencyfield")
		$ValuesStore[$row['FormItemID']] = number_format($row['ItemValue']*1);
	if($row["ItemType"] == "checkbox")
	{
		if($row["ComboValues"] != "")
		{
			$arr = explode("#", $row["ComboValues"]);
			if(!isset($ValuesStore[$row['FormItemID']]))
				$ValuesStore[$row['FormItemID']] = "";
			$ValuesStore[$row['FormItemID']] .= "<br>● " . $arr[$row['ItemValue']*1];
		}
		else
		{
			$ValuesStore[$row['FormItemID']] = "√";
		}
	}
}

$dt = WFM_requests::FullSelect(" AND RequestID=?", array($ReqObj->RequestID));
$RequestRecord = $dt->fetch();

if (substr($ReqObj->ReqContent, 0, 3) == WFM_forms::TplItemSeperator) {
    $res = array_merge(array(''), $res);
}
$st = '';
for ($i = 0; $i < count($res); $i++) {
    if ($i % 2 != 0) {
		if(isset($ValuesStore[$res[$i]]))
		{
			$st .= nl2br($ValuesStore[$res[$i]]);
		}
		else if(isset($ReqItemsStore[ $res[$i] ]["FieldName"]))
		{
			if($ReqItemsStore[ $res[$i] ]["ItemType"] == "shdatefield")
				$st .= DateModules::miladi_to_shamsi($RequestRecord [ $ReqItemsStore[ $res[$i] ]["FieldName"] ]);
			else if($ReqItemsStore[ $res[$i] ]["ItemType"] == "currencyfield")
				$st .= number_format($RequestRecord [ $ReqItemsStore[ $res[$i] ]["FieldName"] ]);
			else
				$st .= $RequestRecord [ $ReqItemsStore[ $res[$i] ]["FieldName"] ];
		}
    } else {
        $st .= $res[$i];
    }
}
?>
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
	<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css">
    <style media="print">
        .noPrint {display:none;}
		@page  
		{ 
			size: auto;   /* auto is the initial value */ 

			/* this affects the margin in the printer settings */ 
			margin: 2mm;  
		} 
    </style>
    <style type="text/css">
        body {margin: 2mm}
        td	 {
			padding : 0 10px 0 10px;
			font-family: nazanin;
			font-size: 16px; 
			text-align: justify; 
			}
		table { page-break-inside:auto; }
		tr    { page-break-after:auto }
		
	</style>
</head>

<body dir="rtl">
    <table style='border:2px dashed #AAAAAA;border-collapse:collapse;width:100%;height:100%' align='center'>
		<thead>
			<tr style="height:80px">
				<td width=110px><img style="width:110px" src='/framework/icons/logo.jpg'></td>
				<td align='center' style='font-family:Titr;font-size:14px;text-align:center !important;'>
					<?= $ReqObj->_FormTitle ?>
					<br>ثبت کننده : <?= $ReqObj->_PersonName ?>
				</td>
				<td width=140px style='text-align:center;'>
					شماره درخواست :  <?= $ReqObj->RequestID ?><br>
					تاریخ ثبت :  <?= DateModules::miladi_to_shamsi($ReqObj->RegDate) ?>					
				</td>
			</tr>
		</thead>
		<tr>
			<td colspan="3" style="vertical-align: top;padding-top: 10px">
				<?= $st ?>
			</td>
		</tr>
	</table>
</body>
