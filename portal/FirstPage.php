<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.10
//-----------------------------
require_once 'header.inc.php';

$IsStaff = $_SESSION["USER"]["IsStaff"] == "YES";
$IsCustomer = $_SESSION["USER"]["IsCustomer"] == "YES";
$IsShareholder = $_SESSION["USER"]["IsShareholder"] == "YES";
$IsAgent = $_SESSION["USER"]["IsAgent"] == "YES";
$IsSupporter = $_SESSION["USER"]["IsSupporter"] == "YES";

$loansText = "هنوز وامی به نام شما به صندوق معرفی نشده است";

?>
<script>

StartPage.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	IsStaff :		<?= $IsStaff ? "true" : "false" ?>,
	IsCustomer :	<?= $IsCustomer ? "true" : "false" ?>,
	IsShareholder : <?= $IsShareholder ? "true" : "false" ?>,
	IsAgent :		<?= $IsAgent ? "true" : "false" ?>,
	IsSupporte :	<?= $IsSupporter ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function StartPage(){
	
	if(this.IsCustomer)
	{
		new Ext.form.FieldSet({
			title : "وام هایی معرفی شده",
			width : 700,
			html : "<?= $loansText ?>",
			applyTo : this.get("div_loans")
		});
	}
}

StartPageObject = new StartPage();

</script>
<center><br>
	<div class="blueText">کاربر گرامی <br> به پرتال جامع <?= SoftwareName ?> خوش آمدید. </div>
	<br>
	<div id="div_loans"></div>
</center>