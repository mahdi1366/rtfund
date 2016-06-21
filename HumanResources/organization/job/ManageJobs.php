<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
require_once '../header.inc.php';
require_once 'job.data.php';

JobCategory_subCategory_dropdowns($drp_category, $drp_subcategory, "MV_jcid", "MV_jsid");

	jsConfig::initialExt();
	jsConfig::tree();
	jsConfig::window();
	require_once 'ManageJobs.js.php';
?>
<script language="JavaScript">
var TreeData = <?php echo GetTreeNodes(); ?>;
</script>
<body dir="rtl">
	<table width="750px">
		<tr>
			<td width="40%"><div id="tree-div" style="overflow:auto; width:250px;border:1px solid #c3daf9;"></div></td>
			<td valign="top" style="">
				<!-- -------------------------------------------- -->
				<div id="DIV_NewJobCategory" class="x-hide-display">
					<div id="PNL_NewJobCategory">
						<table width="100%">
							<tr>
								<td>عنوان رسته اصلی:</td>
								<td>
									<input type="text" id="JC_title" style="width: 98%" class="x-form-text x-form-field"/>
									<input type="hidden" id="JC_jcid"/>
								</td>
							</tr>
							<tr>
								<td></td>
								<td><input type="button" value="ذخیره" onclick="saveJobCategory();" class="button" />
									<input type="button" value="انصراف" onclick="Ext.getCmp('NewJobCategory').hide();" class="button" />
								</td>
							</tr>
						</table>
					</div>
				</div>
				<!-- -------------------------------------------- -->
				<div id="DIV_NewJobSubCategory" class="x-hide-display">
					<div id="PNL_NewJobSubCategory">
						<table width="100%">
							<tr>
								<td height="21px" width="15%">کد رسته اصلی:</td>
								<td width="10%"><b><label id="JSC_m_id"></label></b><input type="hidden" id="JSC_jcid" /></td>
								<td width="25%">عنوان رسته اصلی:</td>
								<td><b><label id="JSC_m_title"></label></b></td>
							</tr>
							<tr>
								<td>کد رسته فرعی:</td>
								<td><input type="text" id="JSC_jsid" style="width:98%" class="x-form-text x-form-field"/>
									<input type="hidden" id="JSC_old_jsid"/></td>
								<td>عنوان رسته فرعی:</td>
								<td>
									<input type="text" id="JSC_title" style="width: 98%" class="x-form-text x-form-field"/>
									<input type="hidden" id="JSC_jcid"/>
									
								</td>
							</tr>
							<tr>
								<td colspan="4" align="center">
									<input type="button" value="ذخیره" onclick="saveJobSubCategory();" class="button" />
									<input type="button" value="انصراف" onclick="Ext.getCmp('NewJobSubCategory').hide();" class="button" />
								</td>
							</tr>
						</table>
					</div>
				</div>
				<!-- -------------------------------------------- -->
				<div id="DIV_NewJobField" class="x-hide-display">
					<div id="PNL_NewJobField">
					</div>
				</div>
				<!-- -------------------------------------------- -->
			</td>
		</tr>
	</table>
	<!-- -------------------------------------------- -->
	<div id="moveDIV" class="x-hidden">
		<div id="movePNL">
			<input type="hidden" id="MV_jfid">
			<table width="100%">
				<tr>
					<td width="20%">انتخاب رسته اصلی :</td>
					<td><?= $drp_category?></td>
				</tr>
				<tr>
					<td>انتخاب رسته فرعی :</td>
					<td><?= $drp_subcategory?></td>
				</tr>
			</table>
		</div>
	</div>
	<!-- -------------------------------------------- -->
</body>