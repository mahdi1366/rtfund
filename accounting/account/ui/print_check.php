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


?>
<meta content='text/html; charset=utf-8' http-equiv='Content-Type'/>

<body style="margin:0">
	<center>
		<div align="right" style="font-weight: bold; vertical-align: top; bottom: 0px; height: 637px; width: 329px; direction: rtl; 
			 font-family: irannastaliq;font-size: 18px;">
			<div style="position: relative; -moz-transform: rotate(270deg); width: 100px; direction: ltr; right: 200px; top: 75px;">
				<?
					$date = DateModules::miladi_to_shamsi($record["checkDate"]);
					echo substr($date, 2, 2) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . substr($date, 5, 2) . 
							"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . substr($date, 8, 2);
				?></div>
			<div style="position: relative; -moz-transform: rotate(270deg); right: 120px; top: 180px;">
			<?= DateModules::DateToString($date)?></div>
			<div style="position: relative; -moz-transform: rotate(270deg); right: 37px; top: 185px;">
			<?= CurrencyModulesclass::CurrencyToString($record["amount"]) ?> &nbsp; ریال</div>
			<div style="position: relative; -moz-transform: rotate(270deg); right: -7px; top: 52px;">
				<?= $record["tafsiliTitle"]?> <?= $record["description"] ?></div>
			<div style="position: relative; -moz-transform: rotate(270deg); width: 150px; right: 22px; text-align: left; direction: ltr; top: 280px;">
				<?= number_format($record["amount"], 0, ".", "/") ?>/--
			</div>
			<div style="position: relative; -moz-transform: rotate(270deg); background-color: black; width: 47px; top: 218px; right: 141px; height: 3px;">&nbsp;</div>
			<div align="center" style="position: relative; -moz-transform: rotate(270deg); background-color: black; width: 223px; top: 141px; 
				 right: 133px; color: white; font-weight: bold; font-family: times new roman; padding-top: 7px; height: 28px;">
				#<?= number_format($record["amount"], 0, ".", ",") ?>Rls</div>
		</div>
	</center>
</body>