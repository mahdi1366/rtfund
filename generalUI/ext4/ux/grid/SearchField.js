/*-----------------------
 * programmer: Jafarkhani
 * CreateDate: 86.11.29
 *-----------------------*/

Ext.namespace('Ext.ux', 'Ext.ux.grid');

// @constructor ------------------------
Ext.ux.grid.Search = function(config) {
    Ext.apply(this, config);

    Ext.ux.grid.Search.superclass.constructor.call(this);
};
//--------------------------------------

Ext.extend(Ext.ux.grid.Search, Ext.util.Observable, {
    // defaults
     searchText:'جستجو بر اساس'
    ,searchTipText:'متن مورد نظر خود را تايپ كرده و كليد Enter را فشار دهيد'
    ,selectAllText:'Select All'
    ,position:'top'
    ,iconCls:'icon-magnifier'
    ,checkIndexes:''
    ,disableIndexes:[]
    ,dateFormat:undefined
    ,showSelectAll:false
    ,mode:'remote'
    ,width:200
    ,xtype:'gridsearch'
    ,paramNames: {
         fields:'fields'
         ,types:'types'
        ,query:'query'
    }
    
    /**
     * @param {String} align 'left' or 'right' (defaults to 'left')
     */
    
    ,init:function(grid) {
        this.grid = grid;

        grid.on("afterrender", Ext.bind(this.onRender, this));
		grid.on("reconfigure", Ext.bind(this.reconfigure, this));
    }
    
	,onRender:function() {
        var grid = this.grid;
        var tb = 'bottom' == this.position ? grid.getDockedItems('toolbar[dock="bottom"]')[0] : grid.getDockedItems('toolbar[dock="top"]')[0];

        // add menu
        this.menu = new Ext.menu.Menu();
        if('right' === this.align) {
            tb.add('->');
        }
        else {
            tb.add('-');
        }
		tb.add({
             text:this.searchText
			,id: grid.id + "searchButton"
            ,menu:this.menu
            ,iconCls:this.iconCls
        });

        // add filter field
        this.field = new Ext.form.TwinTriggerField({
             width:this.width
            ,selectOnFocus:undefined === this.selectOnFocus ? true : this.selectOnFocus
            ,trigger1Cls:'x-form-clear-trigger'
            ,trigger2Cls:'x-form-search-trigger'
            ,onTrigger1Click: Ext.bind(this.onTriggerClear, this)
            ,onTrigger2Click: Ext.bind(this.onTriggerSearch, this)
        });
        this.field.on('render', function() {
            this.field.el.dom.qtip = this.searchTipText;
            var map = new Ext.KeyMap(this.field.el, [{
                 key:Ext.EventObject.ENTER
                ,scope:this
                ,fn:this.onTriggerSearch
            },{
                 key:Ext.EventObject.ESC
                ,scope:this
                ,fn:this.onTriggerClear
            }]);
            map.stopEvent = true;
        }, this, {single:true});

        tb.add(this.field);

        // reconfigure
        this.reconfigure();
    } // eo function onRender
   
    ,onTriggerClear:function() {
    	if(this.field.getValue() == "")
    		return;
        this.field.setValue('');
        this.field.focus();
        this.onTriggerSearch();
    } // eo function onTriggerClear
    
    ,onTriggerSearch:function() {
        var val = this.field.getValue();
        var store = this.grid.getStore();

        if('local' === this.mode) {
            store.clearFilter();
            if(val) {
                store.filterBy(function(r) {
                    var retval = false;
                    this.menu.items.each(function(item) {
                        if(!item.checked || retval) {
                            return;
                        }
                        var rv = r.get(item.dataIndex);
                        rv = rv instanceof Date ? rv.format(this.dateFormat || r.fields.get(item.dataIndex).dateFormat) : rv;
                        var re = new RegExp(val, 'gi');
                        retval = re.test(rv);
                    }, this);
                    if(retval) {
                        return true;
                    }
                    return retval;
                }, this);
            }
            else {
            }
        }
        else {
        	
			var fields = ""; 
        	this.menu.items.each(function(item) {
                if(item.checked) {
                    fields = item.dataIndex;                   
                }
            });
            if(fields == "")
			{
				this.menu.items.getAt(0).setChecked(true);
				fields = this.menu.items.getAt(0).dataIndex;
				//Ext.MessageBox.alert('خطا','ابتدا ستون مورد نظر براي جستجو را انتخاب كنيد');
				//alert('ابتدا ستون مورد نظر براي جستجو را انتخاب كنيد');
				//return;
			}
            // clear start (necessary if we have paging)
			

            store.proxy.extraParams[this.paramNames.fields] = fields;//Ext.encode(fields);
            store.proxy.extraParams[this.paramNames.query] = val;
        

            // reload store
            store.loadPage(1);
			//store.load();
        }

    } // eo function onTriggerSearch
    
    ,setDisabled:function() {
        this.field.setDisabled.apply(this.field, arguments);
    } // eo function setDisabled
    
    ,enable:function() {
        this.setDisabled(false);
    } // eo function enable
    
    ,disable:function() {
        this.setDisabled(true);
    } // eo function disable
    
    ,reconfigure:function() {

        // remove old items
        var menu = this.menu;
        menu.removeAll();

        // add Select All item plus separator
        if(this.showSelectAll) {
            menu.add(new Ext.menu.CheckItem({
                 text:this.selectAllText
                ,checked:!(this.checkIndexes instanceof Array)
                ,hideOnClick:false
                ,handler:function(item) {
                    var checked = ! item.checked;
                    item.parentMenu.items.each(function(i) {
                        if(item !== i && i.setChecked) {
                            i.setChecked(checked);
                        }
                    });
                }
            }),'-');
        }

        Ext.each(this.grid.columns, function(config) {
            var disable = false;
            if(config.searchable == false)
                return;
            if(config.text != "" && config.dataIndex) {
                Ext.each(this.disableIndexes, function(item) {
                    disable = disable ? disable : item === config.dataIndex;
                });
                if(!disable) {                	     
                    menu.add(
                        new Ext.menu.CheckItem({
	                         text:config.text
	                        ,hideOnClick:false
	                        ,checked:'all' === this.checkIndexes
	                        ,dataIndex:config.dataIndex
	                        ,type:config.type
	                        ,handler:function(item) {
			                    if(!item.checked)
			                    	return;
			                    item.parentMenu.items.each(function(i) {
			                        if(item !== i)
			                            i.setChecked(false);			                        
			                    })}                        
	                    })
                    
                    );
                }
            }
        }, this);
        
        // check items
        if(this.checkIndexes instanceof Array) {
            Ext.each(this.checkIndexes, function(di) {
                var item = menu.items.find(function(itm) {
                    return itm.dataIndex === di;
                });
                if(item) {
                    item.setChecked(true, true);
                }
            }, this);
        }
        
    } // eo function reconfigure

}); // eo extend
