<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../header.inc.php';
require_once 'FillForm.class.php';
require_once '../BuildForms/form.class.php';

if(empty($_REQUEST['FillFormID'])) 
	die();

$FillFormID = $_REQUEST['FillFormID'];
$fillFormObj = new FRG_FillForms($FillFormID);
$FormObj = new FRG_forms($fillFormObj->FormID);

//------------------ values datatable -----------------
$temp = FRG_FillFormElems::Get(" AND FillFormID=?", array($FillFormID));
$FillItems = $temp->fetchAll();
$ValuesStore = array();
foreach ($FillItems as $row) {
    $ValuesStore[ $row['ElementID'] ] = $row;
}
//------------------ values datatable -----------------
$temp = FRG_FormElems::Get(" AND FormID=? AND ParentID=0", array($FormObj->FormID));
$FormItems = $temp->fetchAll();
$FormItemsStore = array();
foreach ($FormItems as $row) {
    $FormItemsStore[ $row['ElementID'] ] = $row;
}
//------------------ fixed data -----------------------
$fixedData = PdoDataAccess::runquery("select * from ");
$fixedRecord = $fixedData[0];
//-----------------------------------------------------

$res = explode(FRG_forms::TplItemSeperator, $FormObj->FormContent);
if (substr($FormObj->FormContent, 0, 3) == FRG_forms::TplItemSeperator) {
    $res = array_merge(array(''), $res);
}
$st = '';
for ($i = 0; $i < count($res); $i++) {
    if ($i % 2 != 0) {
		$ElementID = $res[$i];
		
		if(isset($ValuesStore[$ElementID]))
		{
			$valueRecord = $ValuesStore[$ElementID];
			
			switch ($valueRecord["ElementType"]) {
				
				case 'shdatefield':
					$st .= DateModules::miladi_to_shamsi($valueRecord["ElementValue"]);
					break;
				case 'currencyfield':
					$st .= number_format($valueRecord["ElementValue"]);
					break;
				default : 
					$st .= nl2br($valueRecord["ElementValue"]);
			}
		}
		else if(isset($FormItemsStore[$ElementID]) && $FormItemsStore[$ElementID]["alias"] != "")
		{
			$valueRecord = $FormItemsStore[$ElementID];
			switch ($valueRecord["ElementType"]) {
				
				case 'shdatefield':
					$st .= DateModules::miladi_to_shamsi($fixedRecord[ $valueRecord["alias"] ]);
					break;
				case 'currencyfield':
					$st .= number_format($fixedRecord[ $valueRecord["alias"] ]);
					break;
				default : 
					$st .= nl2br($fixedRecord[ $valueRecord["alias"] ]);
			}			
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
    <table style='border:2px solid #AAAAAA;border-collapse:collapse;width:670px;height:100%' align='center'>
		<tr>
			<td colspan="3" style="vertical-align: top;padding-top: 10px">
				<?= $st ?>
			</td>
		</tr>
	</table>
</body>
