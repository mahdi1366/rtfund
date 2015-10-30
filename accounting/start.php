<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------


require_once (getenv("DOCUMENT_ROOT") . '/framework/MainFrame.php');

$dt = PdoDataAccess::runquery("
	select * from ACC_UserState 
		join BSC_branches using(BranchID)
		join ACC_cycles using(CycleID)
	where PersonID=?", 
		array($_SESSION["USER"]["PersonID"]));

if(count($dt) > 0)
{
	$_SESSION["accounting"]["BranchID"] = $dt[0]["BranchID"];
	$_SESSION["accounting"]["CycleID"] = $dt[0]["CycleID"];
	$_SESSION["accounting"]["CycleYear"] = $dt[0]["CycleYear"];
	$_SESSION["accounting"]["BranchName"] = $dt[0]["BranchName"];
}
?>

<script>
	FrameWorkClass.SystemLoad = function(){
		framework.SystemInfo.update("دوره مالی : " + "<?= $_SESSION["accounting"]["CycleYear"]?>" + 
		"<br>" + "شعبه : " + "<?= $_SESSION["accounting"]["BranchName"]?>");
	
		<?if(count($dt) == 0){?>
			framework.OpenPage("/accounting/global/UserState.php","تعیین شعبه و دوره");
		<?}?>
	}
	
</script>