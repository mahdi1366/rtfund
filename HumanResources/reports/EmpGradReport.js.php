<script type="text/javascript">
//---------------------------
// programmer:	Mahdipour
// create Date:	94.02
//---------------------------

EmpGradation.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
	advanceSearchPanel : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function EmpGradation()
{
	this.form = this.get("form_SearchGrad");
		
	
	this.advanceSearchPanel = new Ext.Panel({
		applyTo: this.get("AdvanceSearchDIV"),		
		title: "گزارش ارتقاء رتبه",
		autoWidth:true,
		autoHeight: true,
		collapsible : true,
		animCollapse: false,
		frame: true,
		width : 600,
		bodyCfg: {style : "padding-right:10px;background-color:white;"},
                layout: {
				type:"table",
				columns:1
			},
                bodyPadding: '5 5 0',
                width:580,
                fieldDefaults: {
                        msgTarget: 'side',
                        labelWidth: 80	 
                },
		items :[
				new Ext.form.ComboBox({
				store: personStore,
				emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
				typeAhead: false,
				listConfig : {
					loadingText: 'در حال جستجو...'
				},
				pageSize:10,
				width: 480,
				colspan: 3,
				hiddenName : "staff_id",
				fieldLabel : "جستجوی فرد",
				valueField : "staff_id",
				displayField : "fullname",
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
						record.data.fullname = record.data.pfname + " " + record.data.plname; 
						this.setValue(record.data.staff_id);
						this.collapse();
					}
				}
			}),
                        {
                        xtype: "shdatefield",
                        name : "ToDate",
                        itemId : "ToDate",
                        fieldLabel : "تا تاریخ",
                        allowBlank:false                
                        }],	
		buttons : [{
					text:'جستجو',
					iconCls: 'search',
					handler: function(){ EmpGradationObject.advance_searching();}
				   }]
	});	
}

var EmpGradationObject = new EmpGradation();

EmpGradation.prototype.advance_searching = function()
{ 
	this.form = this.get("form_SearchGrad") ;
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "EmpGradReport.php?showRes=1";
	this.form.submit();	
	return;

}

</script>