<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// Date:		90.04
//---------------------------

CreateNewStaff.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
    msgFieldSet : "",
    personCombo : "",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function CreateNewStaff()
{
	this.form = this.get("form_newStaff");
	
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
		fieldLabel : "جستجوی فرد"
		,tpl: new Ext.XTemplate(
			'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
				,'<td height="23px">کد پرسنلی</td>'
				,'<td>کد شخص</td>'
				,'<td>نام</td>'
				,'<td>نام خانوادگی</td>'
				,'<td>نوع شخص</td>'
				,'<td>واحد محل خدمت</td></tr>',
			'<tpl for=".">',
			'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{PersonID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{personTypeName}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{unit_name}&nbsp;</td></tr>',
			'</tpl>'
			,'</table>'),
            listeners :{
					select : function(combo, records){
						var record = records[0];

						this.setValue("[" + record.data.PersonID + "] " + record.data.pfname + ' ' + record.data.plname);
						newStaffObject.form.person_type.value = record.data.person_type;
                        newStaffObject.form.staff_id.value = record.data.staff_id;
                        newStaffObject.form.personid.value = record.data.PersonID;
						this.collapse();

					}
				}
	});
	this.mainPanel = new Ext.Panel({
		applyTo: this.get("newStaff_DIV"),
		contentEl : this.get("newStaff_TBL"),
		title: "تبدیل وضعیت",
		autoHeight: true,
		width: 700,
        items : [this.personCombo,
            this.msgFieldSet = new Ext.form.FieldSet({
                collapsible: false,
                title : "شماره شناسایی جدید ",
                html : 'تبدیل وضعیت با موفقیت انجام شد و شماره شناسایی جدید	<span id="newstaff" style="font-weight:bold;color:#15428B; "></span>	می باشد.',
                hidden :true
            })
        ],
        frame:true,
		bodyCfg: {style : "padding-right:10px;background-color:white;"},
        buttons : [{
			text:'تغییر وضعیت',
			iconCls: 'user_edit',
			handler: function(){newStaffObject.ChangePerson();}
		}]
	});    
	
            }

var newStaffObject = new CreateNewStaff();

CreateNewStaff.prototype.ChangePerson = function()
{

    var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال تغییر وضعیت ...'});
	mask.show();

	Ext.Ajax.request({
		url : this.address_prefix + "../data/create_new_staff.data.php?task=CreateStaff",
		method : "POST",
		form : this.form,

		success : function(response)
		{
			mask.hide();

			var ret = Ext.decode(response.responseText);

            if( ret.success == true )
			{
                
                newStaffObject.msgFieldSet.show();
                newStaffObject.get('newstaff').innerHTML = ret.data.STID ;
                //newStaffObject.get('newstaff').onclick = framework.OpenPage(newStaffObject.address_prefix + "new_person.php", "شماره شناسایی جدید", {Q0 : ret.data.PID });
                
			}
            else
            {
               ShowExceptions(newStaffObject.get("errorDiv_correctiveWrit"), ret.data);
            }

		}
	});

}


</script>