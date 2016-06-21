<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.07
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ_item.class.php';

$salaryItemObj = new manage_salary_item_type($_REQUEST["salary_item_type_id"]);
$writSalaryItemObj = new manage_writ_item($_REQUEST["writ_id"], $_REQUEST["writ_ver"],
	$_REQUEST["staff_id"], $_REQUEST["salary_item_type_id"],$_REQUEST["mode"]);

$writRec = array("writ_id" => $_REQUEST["writ_id"] ,
                 "writ_ver" => $_REQUEST["writ_ver"] ,
                 "staff_id" => $_REQUEST["staff_id"] ,
                 "salary_item_type_id" => $_REQUEST["salary_item_type_id"],
				 "execute_date" => $_REQUEST["execute_date"] );

?>
<form id="form_newWritSalaryItem">
	<input type="hidden" id="writ_id" name="writ_id" value="<?= $_REQUEST["writ_id"] ?>">
	<input type="hidden" id="writ_ver" name="writ_ver" value="<?= $_REQUEST["writ_ver"]?>">
	<input type="hidden" id="staff_id" name="staff_id" value="<?= $_REQUEST["staff_id"] ?>">
	<input type="hidden" id="salary_item_type_id" name="salary_item_type_id" value="<?= $_REQUEST["salary_item_type_id"] ?>">
	<input type="hidden" id="mode" name="mode" value="<?= $_REQUEST["mode"] ?>">
	<input type="hidden" id="isset_by_user" name="isset_by_user" value="1">
<script>
	WritForm.prototype.afterLoadItem = function()
	{
		new Ext.form.SHDateField({applyTo: this.parent.get("remember_date"),format: "Y/m/d"});

		<? if(isset($_POST['Access']) && $_POST['Access'] == 'view'){?>
			Ext.get(this.parent.get("form_newWritSalaryItem")).readonly();
			ViewWritObject.addItemWin.down("[itemId=save]").hide();
                       
		<?}?>
			
		ViewWritObject.addItemWin.doLayout();
	}
	
	WritFormObject.afterLoadItem();
        
	
</script>
<style>.new td{padding: 4px;height: 21px}</style>
<table width="100%" dir="rtl" style="background-color: white;" class="new">
	<tr>
		<td width="30%">قلم حقوقي :</td>
		<td><?= $salaryItemObj->full_title ?></td>
	</tr>
	<?
	for($i=1; $i<=7; $i++)
	{
		eval("\$paramTitle = \$salaryItemObj->param" . $i . "_title;");
		eval("\$paramInput = \$salaryItemObj->param" . $i . "_input;");
		eval("\$paramValue = \$writSalaryItemObj->param" . $i . ";");
		
		if($paramTitle)
		{
			echo '<tr><td>' . $paramTitle . "</td>";
			echo '<td>';
			if ($paramInput == 1)
				echo "<input type='text' value='" . $paramValue . "' id='param" . $i . "' name='param" . $i . "'
						 class='x-form-text x-form-field' >";
			else
				echo $paramValue;
			echo '</td></tr>';
		}
	}
	?>
	<tr>
		<td>قابل پرداخت ؟</td>
		<td><input type="checkbox" name="must_pay" class="x-form-text x-form-field" id="must_pay"
				   value="1" <?= $writSalaryItemObj->must_pay == "1" ? "checked" : "" ?>></td>
	</tr>
	<tr>
		<td>تاريخ ياد آوري :</td>
		<td><input type="text" id="remember_date" name="remember_date"
				   value="<?= $writSalaryItemObj->remember_date != "" ? DateModules::Miladi_to_Shamsi($writSalaryItemObj->remember_date) : "" ?>"></td>
	</tr>
	<tr>
		<td>پيام يادآوري :</td>
		<td><input type="text" id="remember_message" class="x-form-text x-form-field" name="remember_message"
				   style="width: 98%" value="<?= $writSalaryItemObj->remember_message ?>">

		</td>
	</tr>
	<tr>
		<td>مبلغ :</td>
		<td>
			<?php
			$edit_after_calc = ($salaryItemObj->editable_value == "1");
			if ($salaryItemObj->salary_compute_type == SALARY_COMPUTE_TYPE_CONSTANT || $edit_after_calc)
			{
				if ($_REQUEST["salary_item_type_id"] == SIT_PROFESSOR_MANAGMENT_EXTRA)
				{
					if (empty($writSalaryItemObj->writ_id))		// new mode
					{
						$value = manage_writ_item::get_professor_management_extra($writRec);
						echo "<input type='hidden' name='value' id='vlaue' value='" . $value . "'>" . $value;
					} 
					else
					{
						echo "<input type='hidden' name='value' id='vlaue' value='" . $writSalaryItemObj->value . "'>" . $writSalaryItemObj->value;
					}
				}
				else
				{
					echo "<input type='text' name='value' id='vlaue' class='x-form-text x-form-field' value='" . $writSalaryItemObj->value . "'>";
					echo "<input type='hidden' name='isset_by_user' id='isset_by_user' value='1'>";
				}

			}
			else
				echo $writSalaryItemObj->value;
			?>
		</td>
	</tr>
	<?php
		if($salaryItemObj->editable_value == "1" && $writSalaryItemObj->value != $writSalaryItemObj->base_value &&
				$writSalaryItemObj->base_value)
		{
			echo "<tr><td>مبلغ محاسبه شده :</td>";
			echo "<td>" . $writSalaryItemObj->base_value . "</td></tr>";
		}
	?>
	<?php
	if($edit_after_calc)
	{
		echo "<tr><td>علت ويرايش مبلغ:</td>";
		echo "<td><input type='text' id='edit_reason' name='edit_reason' class='x-form-text x-form-field'
			style='width: 98%' value='" . $writSalaryItemObj->edit_reason . "'></td></tr>";
	}
	?>
	<tr>
		<td>يادآوري شد؟</td>
		<td>
			<input type="checkbox" id="remembered" name="remembered" <?= $writSalaryItemObj->remembered == "1" ? "checked" : "" ?>
				   value="1">
		</td>
	</tr>
</table>
</form>