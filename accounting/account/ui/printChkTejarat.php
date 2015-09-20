<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.05
//-----------------------------

require_once '../../header.inc.php';
require_once '../class/acc_docs.class.php';
require_once 'CurrencyModules.class.php';

$checkID = $_REQUEST["checkID"];
$dt = manage_acc_checks::GetAll("checkID=?", array($checkID));
$record = $dt[0];

$date = DateModules::miladi_to_shamsi($record["checkDate"]);
?>
<meta content='text/html; charset=utf-8' http-equiv='Content-Type'/>

<body style="margin:0">
	<center>
		<div align="right" style="font-weight: bold; vertical-align: top; bottom: 0px; height: 637px; width: 329px; direction: rtl; 
			 font-family: irannastaliq; font-size: 26px;">
			
			<div style="position: relative; transform: rotate(270deg); top: 175px; right: 93px;">
			<?= DateModules::DateToString($date)?></div>
			<div style="position: relative; transform: rotate(270deg); right: 6px; top: 248px;">
			 <?= CurrencyModulesclass::CurrencyToString($record["amount"]) ?>&nbsp; ریال</div>
			<div style="position: relative; transform: rotate(270deg); right: -35px; top: 108px;">
			<?= $record["reciever"] != "" ? $record["reciever"] : $record["tafsiliTitle"]?> <?= $record["description"] ?></div>
			<div style="position: relative; transform: rotate(270deg); width: 150px; text-align: left; direction: ltr; right: 5px; top: 335px;">
				<?= number_format($record["amount"], 0, ".", "/") ?>/--
			</div>
			<div style="position: relative; transform: rotate(270deg); background-color: white; width: 47px; height: 23px; top: 370px; right: 108px;">&nbsp;</div>
			<div align="center" style="position: relative; transform: rotate(270deg); background-color: black; color: white; 
				 font-weight: bold; font-family: times new roman; padding-top: 7px; height: 28px; width: 196px; top: 246px; right: 145px;">
				#<?= number_format($record["amount"], 0, ".", ",") ?>Rls</div>
		</div>
	</center>
</body>