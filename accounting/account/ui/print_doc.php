<?php
//-----------------------------
//Programmer	: SH.Jafarkhani
//Date			: 91.01
//-----------------------------
require_once '../../header.inc.php';
require_once 'CurrencyModules.class.php';

$docID = $_GET["docID"];

$temp = PdoDataAccess::runquery("select * from acc_docs where docID=?", array($docID));
$DocRecord = $temp[0];
$docType = $temp[0]["docType"];

$temp = PdoDataAccess::runquery("
	select d.detail,d.docDate,d.docID,si.kolID,si.moinID,kolTitle,moinTitle,t.TotalDiscount,sum(bdAmount) sumBD,sum(bsAmount) sumBS
		from acc_doc_items si
			join acc_docs d using(docID)
			left join acc_kols k using(kolID)
			left join acc_moins m on(m.kolID=si.kolID AND m.moinID=si.moinID)
			left join (
				select AccDocID, sum(TotalDiscount) TotalDiscount 
				from store_docs
				group by AccDocID
			)t on(t.AccDocID=d.DocID)
	where " . ($docType == "SUMMARY" ? "d.ref_docID=?" : "d.docID=?") . " 
	group by if(bdAmount<>0,0,1),kolID,moinID
	order by if(bdAmount<>0,0,1),if(si.kolID=81,0,si.kolID),si.moinID", array($docID));

$record = $temp[0];

$DocDate = $docType == "SUMMARY" ? $temp[0]["docDate"] : $record["docDate"];

?>
<meta content='text/html; charset=utf-8' http-equiv='Content-Type'/>
<style media="print">
	.noPrint {display:none;}
</style>
<style>
	.border td{border-style: solid;}
	.header td{background-color: #cccccc; font-weight: bold;size: 14px;}
</style>
<center>
	<div class="noPrint" align="center" style="font-family: tahoma;font-size: 12px">
	<form method="POST" id="mainForm">
		<input type="checkbox" name="groupingTafsili" onchange="document.getElementById('mainForm').submit()" 
			 <?= isset($_POST["groupingTafsili"]) ? "checked" : ""?>>
	به تفکیک تفصیلی
	<hr>
	</form>
</div>
<div style="direction:rtl;width:95%;margin: 10px;font-family:b nazanin;font-size: 12px;">
	<table style="width:100%">
	<tr>
	    <td width="25%">
			شماره سند : 
			<?= $docID?><br>
			تاریخ سند :
			<?= DateModules::miladi_to_shamsi($DocDate)?>
			
			<?if($docType == "SUMMARY"){?>
			<br>
			اسناد عطف : 
			<?
				$dt = PdoDataAccess::runquery("select distinct DocID from acc_docs where ref_docID=?", array($docID));				
				$st = "";
				for($i=0; $i<count($dt); $i++)
					$st .= $dt[$i]["DocID"] . ",";
				echo substr($st, 0, strlen($st)-1);
			?>
			<?}?>
		</td>
	    <td style="height: 80px;font-family: b titr;font-size:14px" align="center">
			اعتماد شما سرلوحه خدمت ماست
			<br>سند حسابداری
	    </td>
	    <td width="25%" align="left" style="font-size:14px">
			<img src="../../img/logo3.png" style="width:80px" />
	    </td>
	</tr>
	<tr>
		<td colspan="3" align="center">
			<table class="border" style="font-size:12px;width:100%;border-collapse: collapse" border="1" cellspacing="0" cellpadding="2">
				<tr class="header">
					<td colspan="<?= $docType == "SUMMARY" ? 2 : 3 ?>" align="center" style="border-top-width: 2px;border-left-width: 2px;
						border-right-width: 2px;border-style: solid;">شرح</td>
					<td align="center" rowspan="2" style="width:30px;border-width: 2px;font-size: 11px">کد معین</td>
					<?if($docType != "SUMMARY"){?>
					<td align="center" rowspan="2" style="border-width: 2px;">مبلغ جزء</td>
					<?}?>
					<td align="center" colspan="2"  style="height:32px;border-top-width: 2px;border-left-width: 2px;
						border-right-width: 2px;border-style: solid;">مبلغ</td>
			</tr>
			<tr class="header" style="">
				<td align="center" style="border-right-width: 2px;border-bottom-width: 2px">کل</td>
				<td align="center" style="border-bottom-width: 2px" >معین</td>
				<?if($docType != "SUMMARY"){?>
				<td align="center" style="border-bottom-width: 2px;border-left-width: 2px;">تفصیلی</td>
				<?}?>
				<td align="center" style="width:200px;height:32px;border-right-width: 2px;border-bottom-width: 2px">بدهکار</td>
				<td align="center" style="width:200px;border-bottom-width: 2px;border-left-width: 2px;">بستانکار</td>
			</tr>
				<? 
				
				$TotalBD = 0;
				$TotalBS = 0;
				$banksGroup = false;
				$amountType = "";
				for($i=0; $i<count($temp); $i++)
				{
					if($amountType == "" && ($temp[$i]["kolID"] == 10 /*|| $temp[$i]["kolID"] == 50*/) )
						$amountType = $temp[$i]["sumBD"] > 0 ? "bed" : "bes";
					
					if($banksGroup && ($temp[$i]["kolID"] == 10 /*|| $temp[$i]["kolID"] == 50*/) 
							&& $amountType == ($temp[$i]["sumBD"] > 0 ? "bed" : "bes"))
						continue;
					
					$amountType = $temp[$i]["sumBD"] > 0 ? "bed" : "bes";
					
					$sumBD = $temp[$i]["sumBD"];
					$sumBS = $temp[$i]["sumBS"];
					
					if($docType != "SUMMARY")
					{
						$items = PdoDataAccess::runquery("
							select tafsiliTitle, sum(bdAmount) bdAmount, sum(bsAmount) bsAmount 
							from acc_doc_items si
								join acc_docs ac using(docID)
								left join acc_tafsilis t on(t.tafsiliID=si.tafsiliID)
							where " . ($docType == "SUMMARY" ? "ac.ref_docID=?" : "ac.docID=?") . " AND kolID=? AND moinID=?"
								. ($temp[$i]["sumBD"] > 0 ? " AND bdAmount>0" : " AND bsAmount>0") . 
							"
							group by si.tafsiliID" . (isset($_POST["groupingTafsili"]) ? ",RowID" : "") . "
							order by tafsiliTitle"
							,array($docID, $temp[$i]["kolID"], $temp[$i]["moinID"]));
						
						$no = count($items);
						
						if($temp[$i]["kolID"] == 10 /*|| $temp[$i]["kolID"] == 50*/)
						{
							$items = PdoDataAccess::runquery("
								select sum(bdAmount) bdAmount, sum(bsAmount) bsAmount,tafsiliTitle ,moinTitle
								from acc_doc_items si
										join acc_docs ac using(docID)
										left join acc_tafsilis t on(t.tafsiliID=si.tafsiliID)
										left join acc_moins m on(m.kolID=si.kolID AND m.moinID=si.moinID)
								where " . ($docType == "SUMMARY" ? "ac.ref_docID=?" : "ac.docID=?") . " AND si.kolID=? "
									. ($temp[$i]["sumBD"] > 0 ? " AND bdAmount>0" : " AND bsAmount>0") . "
								group by si.tafsiliID
								order by tafsiliTitle"
								,array($docID, $temp[$i]["kolID"]));

							$no = count($items);
							$banksGroup = true;
							
							$sumBD = 0;
							$sumBS = 0;
							for($k=0; $k<count($items); $k++)
							{
								$sumBD += $items[$k]["bdAmount"];
								$sumBS += $items[$k]["bsAmount"];
							}
						}
					}
					else
						$no = 1;
					
					$JOZamount = $temp[$i]["sumBD"] > 0 ? $items[0]["bdAmount"] : $items[0]["bsAmount"];
					
					echo "<tr><td style='vertical-align:top' rowspan='" . $no . "'>" . $temp[$i]["kolTitle"] . "</td>";
					
					if($temp[$i]["kolID"] != 10 /*&& $temp[$i]["kolID"] != 50*/)
						echo "<td style='vertical-align:top' rowspan='" . $no . "'>" . $temp[$i]["moinTitle"] . "</td>";
					else
						echo "<td style='vertical-align:top'>" . $items[0]["moinTitle"] . "</td>";
					
					if($docType != "SUMMARY")
						echo "<td style='border-left-width: 2px;'>" . ($no>0 ? $items[0]["tafsiliTitle"] : "") . "</td>";
						
					echo "<td style='border-left-width: 2px;'>&nbsp;</td>";
					
					if($docType != "SUMMARY")
						echo "<td style='vertical-align:top;border-left-width: 2px;'>" . ($no>1 ? number_format($JOZamount) : "") . "</td>";
					
					echo "<td style='vertical-align:top' rowspan='" . $no . "'>" . number_format($sumBD) . "</td>
							<td style='vertical-align:top' rowspan='" . $no . "' style='border-left-width: 2px;'>" . number_format($sumBS) . "</td>
						</tr>";		
					
					if($docType != "SUMMARY")
					{
						for($j=1; $j<count($items);$j++)
						{
							$JOZamount = $temp[$i]["sumBD"] > 0 ? $items[$j]["bdAmount"] : $items[$j]["bsAmount"];
							
							echo "<tr>";
							
							if($temp[$i]["kolID"] == 10 /*|| $temp[$i]["kolID"] ==50*/)
								echo "<td>" . $items[$j]["moinTitle"] . "</td>";
							echo "
								<td style='border-left-width: 2px;'>" . $items[$j]["tafsiliTitle"] . "</td>
								<td style='border-left-width: 2px;'>&nbsp;</td>
								<td style='border-left-width: 2px;vertical-align:top'>" . number_format($JOZamount) . "</td>
							</tr>";		
						}
					}
					$TotalBD += $sumBD;
					$TotalBS += $sumBS;
				}
				
				/*$sumBD = 0;
				$sumBS = 0;
				for($i=0; $i<count($temp); $i++)
				{
					$sumBD += $temp[$i]["bdAmount"];
					$sumBS += $temp[$i]["bsAmount"];
					echo "<tr>
							<td align='center' style='border-right-width: 2px;border-left-width: 2px'>" . ($i+1) . "</td>
							<td >" . $temp[$i]["kolTitle"] . "</td>
							<td >" . $temp[$i]["moinTitle"] . "</td>
							<td style='border-left-width: 2px;'>" . $temp[$i]["tafsiliTitle"] . "</td>
							<td style='border-left-width: 2px;'>&nbsp;</td>
							<td >" . number_format($temp[$i]["bdAmount"]) . "</td>
							<td style='border-left-width: 2px;'>" . number_format($temp[$i]["bsAmount"]) . "</td>
						</tr>";					
				}*/
				
				?>
			<tr class="header">
				<td colspan="<?= $docType != "SUMMARY" ? 5 : 3?>"  style="border-width: 2px">جمع : <?= CurrencyModulesclass::CurrencyToString($TotalBD) ?> ریال</td>
				
				<td  style="border-top-width: 2px;border-right-width: 2px;border-bottom-width: 2px"><?= number_format($TotalBD,0,'.',',') ?></td>
				<td  style="border-top-width: 2px;border-left-width: 2px;border-bottom-width: 2px"><?= number_format($TotalBS,0,'.',',') ?></td>
			</tr>
			<? if($TotalBD == $TotalBS){?>
			<tr>
				<td colspan="7" style="border-width: 2px"></td>
			</tr>
			<?}if($record["TotalDiscount"] != "" && $record["TotalDiscount"] != "0"){?>
			<tr>
				<td colspan="7">
					<?= "مبلغ رند سند برابر است با " . $record["TotalDiscount"] . " ريال" ?>
				</td>
			</tr>
			<?}?>
			<tr>
				<td colspan="7">
					شرح سند : <?= $DocRecord["description"] != "" ? $DocRecord["description"] : "----" ?>
				</td>
			</tr>
			</table>
			
			<div align="right" style="width:100%"><?= $record["detail"] != "" ? "ملاحظات : " . $record["detail"] : ""?></div>
			
			<br><br>
			
			<div align="center" style="float:right;width:50%;font-weight: bold;font-size: 12px">
			مسئول حسابداری<br>
			علی فتح آبادی
			</div>
			
			<? if(DateModules::miladi_to_shamsi($DocDate) < '1392/11/01'){?>
			<div align="center" style="float:left;width:50%;font-weight: bold;font-size: 12px">
			مدیر عامل<br>
			سعید بهزادی فر
			</div>
			<?}else{?>
			<div align="center" style="float:left;width:50%;font-weight: bold;font-size: 12px">
			مدیر عامل<br>
			جواد حیدری پور
			</div>
			<?}?>
			
			
			<br><br>
			<div style="width:100%" align="right">
			<?
				require_once '../class/acc_docs.class.php';
				$dt = manage_acc_checks::GetAll("docID=?", array($docID));
				for($i=0; $i < count($dt); $i++)
				{
					echo "چک شماره " . $dt[$i]["checkNo"] . " بانک " . $dt[$i]["bankTitle"] . " مورخ " . DateModules::miladi_to_shamsi($dt[$i]["checkDate"]) . " در وجه " .
							($dt[$i]["reciever"] != "" ? $dt[$i]["reciever"] : $dt[$i]["tafsiliTitle"]) . " " . $dt[$i]["description"] . " صادر گردید.<br>";
					echo '<div style="width:95%" align="left">امضاء تحویل گیرنده</div>';
				}
			?>
			</div>
	    </td>
	</tr>
    </table>
</div>
</center>