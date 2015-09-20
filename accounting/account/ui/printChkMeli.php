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
			 font-family: irannastaliq;font-size: 26px;">
			<div style="position: relative; -moz-transform: rotate(270deg); width: 100px; direction: ltr; right: 175px; top: 75px;">
				<?
					$date = DateModules::miladi_to_shamsi($record["checkDate"]);
					echo substr($date, 2, 2) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . substr($date, 5, 2) . 
							"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . substr($date, 8, 2);
				?></div>
			<div style="position: relative; -moz-transform: rotate(270deg); right: 63px; top: 291px;">
			<?= DateModules::DateToString($date)?></div>
			<div style=" position: relative;right: 4px;top: 230px;transform: rotate(270deg);width: 372px;">
			<?= CurrencyModulesclass::CurrencyToString($record["amount"]) ?> &nbsp; ریال</div>
			<div style="position: relative; -moz-transform: rotate(270deg); right: -11px; top: 88px;">
				<?= $record["reciever"] != "" ? $record["reciever"] : $record["tafsiliTitle"]?> <?= $record["description"] ?></div>
			<div style="position: relative; -moz-transform: rotate(270deg); width: 150px; right: 35px; text-align: left; direction: ltr; top: -90px;">
				<?= number_format($record["amount"], 0, ".", "/") ?>/--
			</div>
			<div style="position: relative; -moz-transform: rotate(270deg); background-color: black; width: 47px; 
				 top: 260px; right: 130px; height: 23px;">&nbsp;</div>
			<div align="center" style="position: relative; -moz-transform: rotate(270deg); background-color: black; width: 223px; top: 234px; 
				 right: 115px; color: white; font-weight: bold; font-family: times new roman; padding-top: 7px; height: 28px;">
				#<?= number_format($record["amount"], 0, ".", ",") ?>Rls</div>
		</div>
	</center>
</body>