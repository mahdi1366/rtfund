<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.07
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ.class.php';
require_once inc_dataReader;

manage_writ::DRP_writType_writSubType(&$writTypes, &$subwritTypes, "writ_type_id", "writ_subtype_id");
?>
<html>
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
	<?php
		jsConfig::initialExt();
		jsConfig::grid(true, false, true);
		jsConfig::date();
	?>
	<script type="text/javascript">
	Ext.onReady(function(){
	
		var personStore = <?= dataReader::MakeStoreObject("../../persons/data/person.data.php?task=searchPerson"
			,"'PersonID','pfname','plname','unit_name','person_type','staff_id'") ?>;
		
		new Ext.form.ComboBox({
			id: 'PID',
			hiddenName:'staff_id',
			store: personStore,
			emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
			typeAhead: false,
			loadingText: 'در حال جستجو...',
			pageSize:10,	
			width: 500,	
			itemSelector: 'tr.search-item',
			applyTo: 'PID'
					
			,tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
			    	,'<td height="23px">کد پرسنلی</td>'
			    	,'<td>نام</td>'
			    	,'<td>نام خانوادگی</td>'
			    	,'<td>واحد محل خدمت</td></tr>',
			    '<tpl for=".">',
			    '<tr class="search-item" style="border-left:0;border-right:0">'
			    	,'<td style="border-left:0;border-right:0" class="search-item">{PersonID}</td>'
			    	,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
			    	,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
			    	,'<td style="border-left:0;border-right:0" class="search-item">{unit_name}&nbsp;</td></tr>',
			    '</tpl>'
			    ,'</table>')
			    						        
			,onSelect: function(record){
				Ext.getCmp("ext_writ_type_id").store.filter("person_type", record.data.person_type);
				Ext.getCmp("ext_writ_type_id").setValue();			
				document.getElementById('staff_id').value = record.data.staff_id;
				document.getElementById('PID').value = record.data.pfname + ' ' + record.data.plname;
				document.getElementById("person_type").value = record.data.person_type;
				this.collapse();
			}
		});
		
		new Ext.form.SHDateField({
			id: 'ext_issue_date',
			applyTo: 'issue_date',
			format: 'Y/m/d'		
		});
		
		new Ext.form.SHDateField({
			id: 'ext_execute_date',
			applyTo: 'execute_date',
			format: 'Y/m/d'		
		});
		
		new Ext.Panel({
			id: "j1",
			applyTo: "newWrit_DIV",
			contentEl : "newWrit_TBL",
			title: "صدور حکم",
			autoHeight: true,
			width: "600px",
			style: "padding-right:10px"
		})	
	});
	</script>
</head>
<body dir="rtl">
<?= ExceptionHandler::showExceptionPanel("newWrit_DIV"); ?>
	<form method="post" action="../data/writ.data.php?task=IssueWrit">
		<center>
		<br>
		<div id="newWrit_DIV" style="width: 600px">
			<table id="newWrit_TBL" style="width: 100%">
				<tr>
					<td>انتخاب فرد :</td>
					<td>
						<input type="text" id="PID">
						<input type="hidden" name="person_type" id="person_type">
					</td>
				</tr>
				<tr>
					<td>نوع حکم :</td>
					<td><?= $writTypes ?></td>
				</tr>
				<tr>
					<td>نوع فرعی حکم :</td>
					<td><?= $subwritTypes ?></td>
				</tr>
				<tr>
					<td>تاریخ صدور :</td>
					<td><input type="text" name="issue_date" id="issue_date"></td>
				</tr>
				<tr>
					<td>تاریخ اجرا :</td>
					<td><input type="text" name="execute_date" id="execute_date"></td>
				</tr>
				<tr>
					<td style="height: 21px">فقط ثبت سابقه :</td>
					<td><input type="checkbox" name="history_only" id="history_only"></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type="submit" class="big_button" value="صدور حکم" name="IssueWrit"></td>
				</tr>
			</table>
		</div>	
		</center>
	</form>
</body>
</html>