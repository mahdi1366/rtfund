<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../header.inc.php';
require_once 'form.class.php';
require_once 'form.class.php';

$ReqObj = new WFM_requests($_REQUEST['RequestID']);

$temp = WFM_RequestItems::Get(" AND r.FormID=?", array($ReqObj->FormID));
$ReqItems = $temp->fetchAll();

$ReqItemsStore = array();
foreach ($ReqItems as $it) {
    $ReqItemsStore[$it['FormItemID']] = $it;
}

$res = explode(WFM_forms::TplItemSeperator, $ReqObj->ReqContent);

$ReqItems = WFM_RequestItems::Get(" AND RequestID=?", array($ReqObj->RequestID));
$ValuesStore = array();
foreach ($ReqItems as $it) {
    $ValuesStore[$it['FormItemID']] = $it['ItemValue'];
}

if (substr($ReqObj->ReqContent, 0, 3) == WFM_forms::TplItemSeperator) {
    $res = array_merge(array(''), $res);
}
$st = '';
for ($i = 0; $i < count($res); $i++) {
    if ($i % 2 != 0) {
		if(isset($ValuesStore[$res[$i]]))
		{
			switch ($ReqItemsStore[$res[$i]]["ItemType"]) {
				case 'shdatefield':
					$st .= DateModules::miladi_to_shamsi($ValuesStore[$res[$i]]);
					break;
				case 'currencyfield':
					$st .= number_format($ValuesStore[$res[$i]]);
					break;
				default : 
					$st .= nl2br($ValuesStore[$res[$i]]);
			}
		}
		else if(isset($ReqItemsStore[ $res[$i] ]["FieldName"]))
		{
			 $st .= $ContractRecord [ $ReqItemsStore[ $res[$i] ]["FieldName"] ];
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
