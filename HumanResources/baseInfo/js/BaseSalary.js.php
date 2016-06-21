<script>
//---------------------------
// programmer:	Mahdipour
// create Date:		94.03
//---------------------------

    BaseSalary.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>",

        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    };

function BaseSalary()
{
	
	 this.personCombo = new Ext.form.ComboBox({
                store: new Ext.data.Store({
                pageSize: 10,
                model: Ext.define(Ext.id(), {
                extend: 'Ext.data.Model',
                fields:['staff_id','pfname','plname','ledger_number',{
                        name : "fullname",
                        convert : function(v,record){return record.data.pfname+" "+record.data.plname;}
                }]
                }),
                remoteSort: true,
                proxy:{
                type: 'jsonp',
                url: '/HumanResources/personal/persons/data/person.data.php?task=searchRetPerson&newPersons=true'  ,
                reader: {
                root: 'rows',
                totalProperty: 'totalCount'
                }
                }
                }),
                emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
                typeAhead: false,
                listConfig : {
                    loadingText: 'در حال جستجو...'
                },
                pageSize:10,
                width: 550,
                hiddenName : "ledger_number",
                valueField : "ledger_number",
                displayField : "fullname" ,

                tpl: new Ext.XTemplate(
                        '<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
                            ,'<td>کد شخص</td>'
                            ,'<td>نام</td>'
                            ,'<td>نام خانوادگی</td>'
                            ,'<td>دفترکل</td></tr>',
                        '<tpl for=".">',
                        '<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{staff_id}</td>'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
                            ,'<td style="border-left:0;border-right:0" class="search-item">{ledger_number}</td>'
                            ,'</tr>'
                            ,'</tpl>'
                            ,'</table>')

            });
               
}

var BaseSalaryObject = new BaseSalary();


BaseSalary.prototype.AddEvalList = function()
{
      
    var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		ledger_number: null,
        sValue:null
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}



BaseSalary.prototype.editValList = function(store,record,op)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/BaseSalary.data.php?task=Save',
		params:{
			record: Ext.encode(record.data)
			
		},		
		method: 'POST',
		success: function(response,op){
                                mask.hide();
                                var st = Ext.decode(response.responseText);

                                if(st.success === "true" )
                                { 
									 alert("ذخیره سازی با موفقیت انجام گردید.");                                      
									 BaseSalaryObject.grid.getStore().load();
                                }
                                else
                                {  
									alert(st.data); 
									BaseSalaryObject.grid.getStore().load();
                                     // ShowExceptions("ErrorDiv",st.data);
                                }		
		
		},               
		failure: function(){
			
		}
	});
}

</script>












