<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------


require_once (getenv("DOCUMENT_ROOT") . '/framework/MainFrame.php');


$_SESSION["accounting"]["BranchID"] = 1;
$_SESSION["accounting"]["CycleID"] = 1;
$_SESSION["accounting"]["CycleYear"] = 1394;
$_SESSION["accounting"]["BranchName"] = "پردیس دانشگاه فردوسی";
?>

<script>
	FrameWorkClass.SystemLoad = function(){
		framework.SystemInfo.update("دوره مالی : " + "<?= $_SESSION["accounting"]["CycleYear"]?>" + 
		"<br>" + "شعبه : " + "<?= $_SESSION["accounting"]["BranchName"]?>");
	}
	
</script>