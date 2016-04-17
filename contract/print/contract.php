<?php
require_once '../header.inc.php';
require_once '../global/CNTconfig.class.php';
require_once '../contract/class/contract.class.php';
require_once '../contract/class/ContractItems.class.php';
require_once '../contract/class/templates.class.php';
require_once '../contract/class/TemplateItems.class.php';

//
$CntObj = new CNT_contracts($_REQUEST['CntId']);
//
$temp = CNT_TemplateItems::Get();
$TplItems = PdoDataAccess::fetchAll($temp, 0, $temp->rowCount());
$TplItemsStore = array();
foreach ($TplItems as $it) {
    $TplItemsStore[$it['TplItemId']] = $it['TplItemType'];
}
//
$obj = new CNT_templates($CntObj->TplId);
$TplContent = $obj->TplContent;
$res = explode(CNTconfig::TplItemSeperator, $TplContent);
//
$CntItems = CNT_ContractItems::GetContractItems($CntObj->CntId);
$ValuesStore = array();
foreach ($CntItems as $it) {
    $ValuesStore[$it['TplItemId']] = $it['ItemValue'];
}
//
if (substr($TplContent, 0, 3) == CNTconfig::TplItemSeperator) {
    $res = array_merge(array(''), $res);
}
$counter = 0;
$st = '';

for ($i = 0; $i < count($res); $i++) {
    if ($i % 2 != 0) {
        switch ($res[$i]) {
            case 1:
                // TODO : array in CNTConfig bashad
                $st .= $CntObj->SupplierId;
                break;
            case 2:
                $st .= $CntObj->Supervisor;
                break;
            case 3:
                $st .= DateModules::miladi_to_shamsi($CntObj->StartDate);
                break;
            case 4:
                $st .= DateModules::miladi_to_shamsi($CntObj->EndDate);
                break;
            case 5:
                $st .= $CntObj->price;
                break;

            default :
                switch ($TplItemsStore[$res[$i]]) {
                    case 'numberfield':
                        $st .= (string) $ValuesStore[$res[$i]];
                        break;
                    case 'textfield':
                        $st .= $ValuesStore[$res[$i]];
                        break;
                    case 'shdatefield':
                        $st .= DateModules::miladi_to_shamsi($ValuesStore[$res[$i]]);
                        break;
                }
        }
    } else {
        $st .= $res[$i];
    }
}
?>
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
    <style media="print">
        .noPrint {display:none;}
    </style>
    <style type="text/css">
        body {font-family: tahoma;font-size: 10pt;}
        td	 {padding: 4px 30px 10px 30px;font-size: 11pt; text-indent : 20px; text-align: justify; line-height : 2;}
    </style>
</head>

<body dir="rtl">
    <br><br>
    <table style='border:2px dashed #AAAAAA;border-collapse:collapse;width:21cm' align='center'><tr>
            <td width=60px style='padding:10px 0px 0px 0px !important;'><img src='../img/fum.jpg'></td>
            <td align='center' style='font-family:b titr;font-size:15px;text-align:center !important;'>
                <b><?php
                    echo $obj->TplTitle;
                    echo '<br>';
                    echo $CntObj->description;
                    ?></b>
            </td>
            <td width=60px style='padding:10px 0px 0px 0px !important;'></td>
        </tr>

        <?php
        echo "<tr><td colspan=3>";
        echo $st;
        echo "</td></tr></table>";
        die();
        ?>
