<?php

require_once("../data/writ.data.php");

if(!empty($_POST['writ_id']))
{ 
	$drp_not_assigned_items = manage_writ_item::DRP_get_not_assigned_items("salary_item_type_id",
                                                                           $_POST['writ_id'], $_POST['writ_ver'], $_POST['staff_id']);
}
?>
<?= $drp_not_assigned_items?><input type="button" class="button" value="افزودن" onclick="WritFormObject.AddSalaryItem();">
