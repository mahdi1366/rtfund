<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$dt = PdoDataAccess::runquery("select TafsiliID from ACC_tafsilis where ObjectID=? AND TafsiliType=1",
	array($_SESSION["USER"]["PersonID"]));
$TafsiliID = $dt[0][0];
$_SESSION["accounting"]["CycleID"] = 1395;
header("location: /accounting/share/PrintShare.php?portal=true&print=true&TafsiliID=" . $TafsiliID);
die();
?>
<script>
ShareInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ShareInfo()
{

}

ShareInfoObject = new ShareInfo();


</script>

<div id="panelDIV"></div>

