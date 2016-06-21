<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
require_once '../header.inc.php';
require_once 'job.data.php';

$jfid = (!empty($_GET["jfid"])) ? $_GET["jfid"] : "";
$obj = new be_jobField($jfid);

$drp_jobTypes = GetAll_job_types_dropdown("job_type", $obj->job_type);
$drp_jobLevels = GetAll_job_levels_dropdown("job_level", $obj->job_level);
?>
<form id="jobFieldForm">
<input type="hidden" name="mode" value="<?= ($jfid != "") ? "edit" : "new" ?>"></input>
<table width="100%">
	<tr>
		<td height="21px">کد رسته اصلی:</td>
		<td><b><?= $_GET["jcid"] ?></b>
			<input type="hidden" value="<?= $_GET["jcid"] ?>" id="JF_jcid" name="JF_jcid" /></td>
		<td>عنوان رسته اصلی:</td>
		<td><b><label id="JF_ctitle"><?= $_GET["jc_title"] ?></label></b></td>
	</tr>
	<tr>
		<td height="21px">کد رسته فرعی:</td>
		<td><b><?= $_GET["jsid"] ?></b>
			<input type="hidden" id="JF_jsid" name="JF_jsid" value="<?= $_GET["jsid"] ?>"/></td>
		<td>عنوان رسته فرعی:</td>
		<td><b><label id="JF_stitle"><?= $_GET["js_title"] ?></label></b></td>
	</tr>
	<tr>
		<td>کد رشته شغلی:</td>
		<td><input type="text" id="jfid" name="jfid" value="<?= $obj->jfid ?>" class="x-form-text x-form-field"/></td>
		<td>عنوان رشته شغلی:</td>
		<td><input type="text" id="title" name="title" value="<?= $obj->title ?>" class="x-form-text x-form-field"/></td>
	</tr>
	<tr>
		<td>گروه ورودی:</td>
		<td><input type="text" id="start_group" name="start_group" value="<?= $obj->start_group ?>" class="x-form-text x-form-field"/></td>
		<td>تعداد طبقات:</td>
		<td><input type="text" id="grade" name="grade" value="<?= $obj->grade ?>" class="x-form-text x-form-field"/></td>
	</tr>
	<tr>
		<td>نوع شغل:</td>
		<td><?= $drp_jobTypes ?></td>
		<td>آموزشی و پژوهشی:</td>
		<td><input type="checkbox" name="educ_research" value="1" <?= ($obj->educ_research == "1") ? "checked" : "" ?> id="educ_research"/></td>
	</tr>
	<tr>
		<td>سطح شغل:</td>
		<td><?= $drp_jobLevels ?></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>شرایط احراز:</td>
		<td colspan="3">
			<textarea id="conditions" style="width:98%" rows="8" name="conditions" class="x-form-field"><?= $obj->conditions ?></textarea>
		</td>
	</tr>
	<tr>
		<td>شرح وظایف:</td>
		<td colspan="3">
			<textarea id="duties" name="duties" rows="8" style="width:98%" class="x-form-field"><?= $obj->duties ?></textarea>
		</td>
	</tr>
	<tr>
		<td colspan="4" align="center">
			<input type="button" value="ذخیره" onclick="saveJobField();" class="button" />
			<input type="button" value="انصراف" onclick="Ext.getCmp('NewJobField').hide();" class="button" />
		</td>
	</tr>
</table>
</form>