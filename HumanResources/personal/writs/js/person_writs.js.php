<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	88.07.20
//---------------------------

PersonWrits.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	grid : "",
	personCombo : "",
	mainPanel : "",
	advanceSearchPanel : "",
	advanceSearchPanelLoad : false,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PersonWrits()
{
	this.form = this.get("form_PSearchWrt");
	
	this.grid = <?= $grid?>;

   /* this.grid.getView().on('render', function(view) {
                                view.tip = Ext.create('Ext.tip.ToolTip', {
                                    target: view.el,
                                    delegate: view.itemSelector,
                                    trackMouse: true,
                                    width : 500,
                                    renderTo: 'PersonWritResultDIV',
                                    listeners: {
                                        beforeshow: function updateTipBody(tip) {
                                            tip.update('شماره شناسایی:' + view.getRecord(tip.triggerElement).get('staff_id') + '<br> واحد محل خدمت :' + view.getRecord(tip.triggerElement).get('parentTitle') + '<br> ثبت سابقه: ' +
                                                                           view.getRecord(tip.triggerElement).get('history_only_title') + '<br> اصلاحی :' +
                                                                           view.getRecord(tip.triggerElement).get('corrective_title') );
                                        }
                                    }
                                });
                            });*/

    this.grid.getView().getRowClass = function(record,index)
                                        { 
                                           if(record.data.correct_completed == 1 ){  return "YellowRow"; };
                                           return "";
                                        }
	
	this.personCombo = new Ext.form.ComboBox({
		store: personStore,
		emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
		typeAhead: false,
		listConfig : {
			loadingText: 'در حال جستجو...'
		},
		pageSize:10,
		width: 550,
		hiddenName : "PersonID",
		valueField : "PersonID",
		fieldLabel : "جستجوی فرد",

		tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
					,'<td height="23px">کد پرسنلی</td>'
					,'<td>کد شخص</td>'
					,'<td>نام</td>'
					,'<td>نام خانوادگی</td>'
					,'<td>واحد محل خدمت</td></tr>',
				'<tpl for=".">',
				'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
					,'<td style="border-left:0;border-right:0" class="search-item">{PersonID}</td>'
					,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
					,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
					,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
					,'<td style="border-left:0;border-right:0" class="search-item">{unit_name}&nbsp;</td></tr>',
				'</tpl>'
				,'</table>'),

		listeners :{
			select : function(combo, records){
				var record = records[0];
				this.setValue("[" + record.data.PersonID + "] " + record.data.pfname + ' ' + record.data.plname);
				PersonWritsObject.form.PersonID.value = record.data.PersonID;
				
				PersonWritsObject.person_type = record.data.person_type;
             				
				this.collapse();
			}
		}
	});

	this.mainPanel = new Ext.Panel({
		applyTo: this.get("selectPersonDIV"),
		title: "انتخاب حکم",
		width : 700,
		frame :true,
		items : [this.personCombo],
		buttons : [
			{
				text: "جستجو",
				iconCls: 'search',
				handler: function(){
					PersonWritsObject.searching();
				}
			}
		]
	});
}

var PersonWritsObject = new PersonWrits();
//------------------------------------------------------

PersonWrits.prototype.searching = function()
{
	this.grid.getStore().proxy.extraParams["Q0"] = this.form.PersonID.value;
	if(!this.grid.rendered)
	{ 
		this.grid.render(this.get("PersonWritResultDIV"));
	}
	else 
		this.grid.getStore().loadPage(1); 
            

	if(this.person_type != 1)
	{
		this.grid.columns[10].setVisible(false);
		this.grid.columns[11].setVisible(false);
	}
    else { 
        this.grid.columns[10].setVisible(true);
		this.grid.columns[11].setVisible(true);

    }
	
}

PersonWrits.opRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='PersonWritsObject.editWrit();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	<?if($accessObj->DeleteAccess()){?>
		st += "<div  title='حذف اطلاعات' class='remove' onclick='PersonWritsObject.deleteWrit();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	<?}?>
	return st;
}
	    
PersonWrits.prototype.editWrit = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../writs/ui/view_writ.php", "مشاهده حکم",
		 {
			 WID : record.data.writ_id,
             STID : record.data.staff_id,
             WVER : record.data.writ_ver ,
			 PID : record.data.PersonID,
             ExeDate : record.data.execute_date,
			 FacilID : <?= $_REQUEST["FacilID"]?>
		 });
}	    

PersonWrits.prototype.deleteWrit = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();

	if(!confirm("آيا از حذف اطمينان داريد؟"))
		return;

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url : this.address_prefix + "../data/writ.data.php",
		method : "POST",
		params : {
			task : "DeleteWrit",
			writ_id : record.data.writ_id,
			writ_ver: record.data.writ_ver,
			staff_id : record.data.staff_id
		},
		success : function(response)
		{
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حکم مورد نظر با موفقیت حذف شد");
					PersonWritsObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
			mask.hide();
		}
	});
}


</script>