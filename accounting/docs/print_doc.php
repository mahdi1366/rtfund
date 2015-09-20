<?php
//-----------------------------
//Programmer	: SH.Jafarkhani
//Date			: 94.06
//-----------------------------
require_once '../header.inc.php';
require_once 'doc.class.php';
require_once inc_CurrencyModule;

$docID = $_GET["DocID"];
$DocObject = new ACC_docs($docID);

$temp = PdoDataAccess::runquery("
	select	b1.BlockDesc level1Desc, 
			b2.BlockDesc level2Desc, 
			b3.BlockDesc level3Desc,
			b.InfoDesc TafsiliType,
			t.TafsiliDesc,
			sum(DebtorAmount) DSUM, 
			sum(CreditorAmount) CSUM
		from ACC_DocItems di
			join ACC_docs d using(docID)
			join ACC_CostCodes cc using(CostID)
			left join ACC_blocks b1 on(cc.level1=b1.BlockID)
			left join ACC_blocks b2 on(cc.level2=b2.BlockID)
			left join ACC_blocks b3 on(cc.level3=b3.BlockID)
			left join BaseInfo b on(TafsiliType=InfoID AND TypeID=2)
			left join ACC_Tafsilis t on(t.TafsiliID=di.TafsiliID)
			
		where di.DocID=?
		group by if(DebtorAmount<>0,0,1),di.CostID,di.TafsiliID
		order by if(DebtorAmount<>0,0,1),cc.CostCode", 
		
		array($docID));
?>
<meta content='text/html; charset=utf-8' http-equiv='Content-Type'/>
<style media="print">
	.noPrint {display:none;}
</style>
<style>
	.header td{background-color: #cccccc; font-weight: bold;size: 12px;}
	td { font-family: tahoma; font-size: 12px; line-height: 18px; padding: 6px;}
</style>
<center>
<div style="direction:rtl;width:95%;margin: 10px;">
	<table style="width:100%">
	<tr>
	    <td width="25%">
			<img src="../../img/logo3.png" style="width:80px" />
		</td>
	    <td style="height: 80px;font-family: b titr;font-size:16px" align="center">
			<br>سند حسابداری
	    </td>
	    <td width="20%" align="center" >
			شماره سند : 
			<?= $docID?><br><br>
			تاریخ سند :
			<?= DateModules::miladi_to_shamsi($DocObject->DocDate)?>
	    </td>
	</tr>
	<tr>
		<td colspan="3" align="center">
			<table style="width:100%;border-collapse: collapse" border="1" cellspacing="0" cellpadding="2">
				<tr class="header">
					<td colspan="3" align="center" >کد حساب</td>
					<td align="center" colspan="2" >تفصیلی</td>
					<td align="center" colspan="2" >مبلغ</td>
			</tr>
			<tr class="header" style="">
				<td align="center" >گروه</td>
				<td align="center" >کل</td>
				<td align="center" >معین</td>
				<td align="center" >گروه تفصیلی</td>
				<td align="center" >تفصیلی</td>
				<td align="center" >بدهکار</td>
				<td align="center" >بستانکار</td>
			</tr>
				<? 
				$DSUM = 0;
				$CSUM = 0;
				for($i=0; $i<count($temp); $i++)
				{
					$DSUM += $temp[$i]["DSUM"];
					$CSUM += $temp[$i]["CSUM"];
					echo "<tr>
							<td >" . $temp[$i]["level1Desc"] . "</td>
							<td >" . $temp[$i]["level2Desc"] . "</td>
							<td >" . $temp[$i]["level3Desc"] . "</td>
							<td >" . $temp[$i]["TafsiliType"] . "</td>
							<td >" . $temp[$i]["TafsiliDesc"] . "</td>
							<td >" . number_format($temp[$i]["DSUM"]) . "</td>
							<td >" . number_format($temp[$i]["CSUM"]) . "</td>
						</tr>";					
				}
				
				?>
			<tr class="header">
				<td colspan="5">جمع : 
				<?= $CSUM != $DSUM ? "<span style=color:red>سند تراز نمی باشد</span>" : 
					CurrencyModulesclass::CurrencyToString($CSUM) . "ریال" ?> </td>
				
				<td><?= number_format($DSUM,0,'.',',') ?></td>
				<td><?= number_format($CSUM,0,'.',',') ?></td>
			</tr>
			<tr>
				<td colspan="7">
					شرح سند : <?= $DocObject->description?>
				</td>
			</tr>
			</table>
			<table style="width:100%;border-collapse: collapse" border="1" cellspacing="0" cellpadding="2">
			<?
				$dt = ACC_DocChecks::GetAll("DocID=?", array($docID));
				for($i=0; $i < count($dt); $i++)
				{
					echo "<tr style='height:60px;vertical-align:middle'>
						<td>" . "چک شماره " . "<b>" . $dt[$i]["CheckNo"] . "</b>" . " بانک " . 
							"<b>" . $dt[$i]["BankDesc"] . "</b>" . " شماره حساب " . 
							"<b>" . $dt[$i]["AccountNo"] . "</b>" . " مورخ " . 
							"<b>" . DateModules::miladi_to_shamsi($dt[$i]["CheckDate"]) . "</b>" . " به مبلغ " . 
							"<b>" . number_format($dt[$i]["amount"]) . " ریال</b>" . " در وجه " .
							"<b>" . $dt[$i]["reciever"] . " " . $dt[$i]["description"] . "</b>" . " صادر گردید." . 
							"</td>";
					echo '<td>امضاء تحویل گیرنده</td></tr>';
				}
			?>
			</table>
			<br>
			<div align="center" style="float:right;width:50%;font-weight: bold;font-size: 12px">
			امضاء مسئول حسابداری
			</div>
			
			<div align="center" style="float:left;width:50%;font-weight: bold;font-size: 12px">
			امضاء مدیر عامل
			</div>
			<br><br><br><br>
	    </td>
	</tr>
    </table>
</div>
</center>