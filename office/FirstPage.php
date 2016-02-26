<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.10
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataReader;
require_once 'letter/letter.class.php';
require_once 'workflow/wfm.class.php';

$dt = OFC_letters::SelectReceivedLetters(" AND s.IsSeen='NO'");
$NewReceived = $dt->rowCount();

$dt = OFC_letters::SelectDraftLetters();
$DraftCount = count($dt);

$dt = WFM_FlowRows::SelectReceivedForms(); 
$ReceiveForms = is_array($dt) ? count($dt) : $dt->rowCount();
?>
<script>

StartPage.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function StartPage(){
	
	new Ext.panel.Panel({
		frame : true,
		title : "خلاصه کارتابل",
		width : 600,
		applyTo : this.get("div_summary"),
		contentEl : this.get("div_content")
	});
}

StartPageObject = new StartPage();

StartPage.prototype.OpenPage = function(mode){
	
	if(mode == "receive")
		framework.OpenPage("/office/letter/MyLetter.php","نامه های رسیده", {mode : mode});
	if(mode == "draft")
		framework.OpenPage("/office/letter/DraftLetters.php","نامه های پیش نویس");
	if(mode == "form")
		framework.OpenPage("/office/workflow/MyForms.php","فرم های رسیده");
}

</script>
<style>
	#div_content td{
		height: 21px
	}
</style>
<center><br>
	<div id="div_summary" align="right">
		<table id="div_content" align="right" style="width:85%;margin : 20 40 20 0">
			<tr>
				<td><img src="/office/icons/summary.png" style="width:30px;vertical-align: middle;">
					نامه های رسیده جدید : 
					<a href="javascript:StartPageObject.OpenPage('receive')">( <?= $NewReceived ?> )</a>
				</td>
			</tr>
			<tr>
				<td><img src="/office/icons/summary.png" style="width:30px;vertical-align: middle;">
					نامه های پیش نویس : 
					<a href="javascript:StartPageObject.OpenPage('draft')">( <?= $DraftCount ?> )</a>
				</td>
			</tr>
			<tr>
				<td><hr></td>
			</tr>
			<tr>
				<td><img src="/office/icons/form.png" style="width:30px;vertical-align: middle;">
					فرم های منتظر تایید :
					<a href="javascript:StartPageObject.OpenPage('form')">( <?= $ReceiveForms ?> )</a>					
				</td>
			</tr>
		</table>
	</div>
</center>