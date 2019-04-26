<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../header.inc.php';
require_once 'form.class.php';
require_once 'form.class.php';
 
$ReqObj = new WFM_requests($_REQUEST['RequestID']);
$FormID = $ReqObj->FormID>0 ? $ReqObj->FormID : $_REQUEST["FormID"];

$temp = WFM_FormItems::Get(" AND fi.FormID in(0,:fid)", array(":fid" => $FormID));
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

function printGrid($record, $xmlDataArr){
	
	$returnVal = "";
	$columns = WFM_FormGridColumns::Get(" AND FormItemID=?", array($record["FormItemID"]));
	$columns = $columns->fetchAll();
	$returnVal .= "<center><table class=form border=1><caption>" . $record["ItemName"] . "</caption><tr>";
	foreach($columns as $col)
		$returnVal .= "<td class=titles>" . $col["ItemName"] . "</td>";
	$returnVal .= "</tr>";
	
	foreach($xmlDataArr as $xmlData)
	{
		$vals = array();
		$p = xml_parser_create();
		xml_parse_into_struct($p, $xmlData, $vals);
		xml_parser_free($p);

		$rowValues = array();
		foreach($vals as $element)
			if(strpos($element["tag"],"COLUMN_") !== false)
				$rowValues[ str_replace("COLUMN_","",$element["tag"]) ] = 
						empty($element["value"]) ? "" : $element["value"];

		foreach($columns as $col)
		{
			$returnVal .= "<td class=values>";
			if($col["ItemType"] == "currencyfield")
			{
				$value = $rowValues[ $col["ColumnID"] ]*1;
				$returnVal .= number_format($value);
			}
			else
				$returnVal .= $rowValues[ $col["ColumnID"] ];
			$returnVal .= "</td>";
		}
		$returnVal .= "</tr>";	
	}
	$returnVal .= "</table></center><br>";
	return $returnVal;
}

$ReqItems = WFM_RequestItems::Get(" AND RequestID=?", array($ReqObj->RequestID));
$ReqItems = $ReqItems->fetchAll();
$ValuesStore = array();
foreach ($ReqItems as $row) 
{
	if($row["ItemType"] == "grid")
	{
		if(!isset($ValuesStore[$row['FormItemID']]))
			$ValuesStore[$row['FormItemID']] = array();
		$ValuesStore[$row['FormItemID']][] = $row['ItemValue'];
		continue;
	}
	if($row["ItemType"] == "branch")
	{
		require_once '../../framework/baseInfo/baseInfo.class.php';
		$branchObj = new BSC_branches($row['ItemValue']);
		$ValuesStore[$row['FormItemID']] = $branchObj->BranchName;
		continue;
	}
	
	if($row["ItemType"] == "shdatefield")
		$ValuesStore[$row['FormItemID']] = DateModules::miladi_to_shamsi($row['ItemValue']);
	else if($row["ItemType"] == "currencyfield")
		$ValuesStore[$row['FormItemID']] = number_format($row['ItemValue']*1);
	else if($row["ItemType"] == "checkbox")
	{
		if($row["ComboValues"] != "")
		{
			$arr = explode("#", $row["ComboValues"]);
			if(!isset($ValuesStore[$row['FormItemID']]))
				$ValuesStore[$row['FormItemID']] = "● " . $arr[$row['ItemValue']*1];
			else
				$ValuesStore[$row['FormItemID']] .= "<br>" . "● " . $arr[$row['ItemValue']*1];
		}
		else
		{
			$ValuesStore[$row['FormItemID']] = "√";
		}
	}
	else
		$ValuesStore[$row['FormItemID']] = $row['ItemValue'];
}

$GlobalInfoRecord = WFM_requests::GlobalInfoRecord($ReqObj->PersonID, $ReqObj->RequestID);

if (substr($ReqObj->ReqContent, 0, 3) == WFM_forms::TplItemSeperator) {
    $res = array_merge(array(''), $res);
}
$st = '';
for ($i = 0; $i < count($res); $i++) {
    if ($i % 2 != 0) {
		if($ReqItemsStore[ $res[$i] ]["ItemType"] == "grid")
		{
			$st .= printGrid($ReqItemsStore[ $res[$i] ],$ValuesStore[$res[$i]]);
			continue;
		}
		if(isset($ValuesStore[$res[$i]]))
		{
			$st .= nl2br($ValuesStore[$res[$i]]);
		}
		else if(isset($ReqItemsStore[ $res[$i] ]["FieldName"]))
		{
			if($ReqItemsStore[ $res[$i] ]["ItemType"] == "shdatefield")
				$st .= DateModules::miladi_to_shamsi($GlobalInfoRecord [ $ReqItemsStore[ $res[$i] ]["FieldName"] ]);
			else if($ReqItemsStore[ $res[$i] ]["ItemType"] == "currencyfield")
				$st .= number_format($GlobalInfoRecord [ $ReqItemsStore[ $res[$i] ]["FieldName"] ]);
			else
				$st .= $GlobalInfoRecord [ $ReqItemsStore[ $res[$i] ]["FieldName"] ];
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
	body{font-family: Nazanin;font-size:12pt;}
	.form{margin-top:10px;width:98%;border-collapse: collapse;text-align: justify; direction: rtl}
	.form caption{border: 1px solid #777; border-bottom: 0px;}
	.form td{padding:0 4px 0 4px;}
	.titles{background-color: #eee;font-weight: bold;text-align: center;}
	.values{text-align: center;}
	</style>
</head>

<body dir="rtl">
    <table style='border-collapse:collapse;width:100%;height:100%' align='center'>
		<thead>
			<tr style="height:80px">
				<td width=200px><img style="width:110px" src='/framework/icons/logo.jpg'></td>
				<td align='center' style='font-family:Titr;font-size:14px;text-align:center !important;'>
					<?= $ReqObj->_FormTitle ?>					
				</td>
				<td width=200px style='text-align:center;'>
					شماره درخواست :  <?= $ReqObj->RequestNo ?><br> 
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
