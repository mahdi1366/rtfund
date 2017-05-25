/*-----------------------
 * programmer: Jafarkhani
 * CreateDate: 86.11.29
 *-----------------------*/

Ext.form.TwinTriggerField = Ext.extend(Ext.form.TriggerField, {
    initComponent : function(){
        Ext.form.TwinTriggerField.superclass.initComponent.call(this);

        this.triggerConfig = {
            tag:'span', cls:'x-form-twin-triggers', cn:[
            {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger1Class},
            {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger2Class}
        ]};
    },

    getTrigger : function(index){
        return this.triggers[index];
    },

    initTrigger : function(){
        var ts = this.trigger.select('.x-form-trigger', true);
        var triggerField = this;
        ts.each(function(t, all, index){
            var triggerIndex = 'Trigger'+(index+1);
            t.hide = function(){
                var w = triggerField.wrap.getWidth();
                this.dom.style.display = 'none';
                triggerField.el.setWidth(w-triggerField.trigger.getWidth());
                this['hidden' + triggerIndex] = true;
            };
            t.show = function(){
                var w = triggerField.wrap.getWidth();
                this.dom.style.display = '';
                triggerField.el.setWidth(w-triggerField.trigger.getWidth());
                this['hidden' + triggerIndex] = false;
            };

            if(this['hide'+triggerIndex]){
                t.dom.style.display = 'none';
                this['hidden' + triggerIndex] = true;
            }
            this.mon(t, 'click', this['on'+triggerIndex+'Click'], this, {preventDefault:true});
            t.addClassOnOver('x-form-trigger-over');
            t.addClassOnClick('x-form-trigger-click');
        }, this);
        this.triggers = ts.elements;
    },

    getTriggerWidth: function(){
        var tw = 0;
        Ext.each(this.triggers, function(t, index){
            var triggerIndex = 'Trigger' + (index + 1),
                w = t.getWidth();
            if(w === 0 && !this['hidden' + triggerIndex]){
                tw += this.defaultTriggerWidth;
            }else{
                tw += w;
            }
        }, this);
        return tw;
    },

    
    onDestroy : function() {
        Ext.destroy(this.triggers);
        Ext.form.TwinTriggerField.superclass.onDestroy.call(this);
    },

    
    onTrigger1Click : Ext.emptyFn,
    
    onTrigger2Click : Ext.emptyFn
});

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

        grid.onRender = grid.onRender.createSequence(this.onRender, this);
        grid.reconfigure = grid.reconfigure.createSequence(this.reconfigure, this);
    }
    ,onRender:function() {
        var grid = this.grid;
        var tb = 'bottom' == this.position ? grid.getBottomToolbar() : grid.getTopToolbar();

        // add menu
        this.menu = new Ext.menu.Menu();
        if('right' === this.align) {
            tb.addFill();
        }
        else {
            tb.addSeparator();
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
            ,trigger1Class:'x-form-clear-trigger'
            ,trigger2Class:'x-form-search-trigger'
            ,onTrigger1Click:this.onTriggerClear.createDelegate(this)
            ,onTrigger2Click:this.onTriggerSearch.createDelegate(this)
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
        var store = this.grid.store;

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
        	// get fields to search array
            /*var fields = [];
            this.menu.items.each(function(item) {
                if(item.checked) {
                    fields.push(item.dataIndex);
                }
            });
            
            var types = [];
            this.menu.items.each(function(item) {
                if(item.checked) {
                    types.push(item.type);
                }
            });
        	*/
			var fields = ""; 
        	this.menu.items.each(function(item) {
                if(item.checked) {
                    fields = item.dataIndex;                   
                }
            });
            if(fields == "")
			{
				//Ext.MessageBox.alert('خطا','ابتدا ستون مورد نظر براي جستجو را انتخاب كنيد');
				alert('ابتدا ستون مورد نظر براي جستجو را انتخاب كنيد');
				return;
			}
            // clear start (necessary if we have paging)
            if(store.lastOptions && store.lastOptions.params) {
                store.lastOptions.params[store.paramNames.start] = 0;
            }

            // add fields and query to baseParams of store
            delete(store.baseParams[this.paramNames.fields]);
            //delete(store.baseParams[this.paramNames.types]);
            delete(store.baseParams[this.paramNames.query]);
            if (store.lastOptions && store.lastOptions.params) {
                delete(store.lastOptions.params[this.paramNames.fields]);
                //delete(store.lastOptions.params[this.paramNames.types]);
                delete(store.lastOptions.params[this.paramNames.query]);
            }
           
            store.baseParams[this.paramNames.fields] = fields;//Ext.encode(fields);
            //store.baseParams[this.paramNames.types] = Ext.encode(types);
            store.baseParams[this.paramNames.query] = val;
        

            // reload store
            store.reload();
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

        // add new items
        var cm = this.grid.colModel;
        Ext.each(cm.config, function(config) {
            var disable = false;
            if(config.header && config.dataIndex) {
                Ext.each(this.disableIndexes, function(item) {
                    disable = disable ? disable : item === config.dataIndex;
                });
                if(!disable) {                	     
                    menu.add(
                        new Ext.menu.CheckItem({
	                         text:config.header
	                        ,hideOnClick:false
	                        ,checked:'all' === this.checkIndexes
	                        ,dataIndex:config.dataIndex
	                        ,type:config.type
	                        ,handler:function(item) {
			                    if(item.checked)
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

 
/*Ext.app.SearchField = Ext.extend(Ext.form.TwinTriggerField, {
    initComponent : function(){
        Ext.app.SearchField.superclass.initComponent.call(this);
        this.on('specialkey', function(f, e){
            if(e.getKey() == e.ENTER){
                this.onTrigger2Click();
            }
        }, this);
    },

    validationEvent:false,
    validateOnBlur:false,
    trigger1Class:'x-form-clear-trigger',
    trigger2Class:'x-form-search-trigger',
    hideTrigger1:true,
    width:180,
    hasSearch : false,
    paramName1 : 'field',
    paramName2 : 'value',
    paramName3 : 'isText',

    onTrigger1Click : function(){
        if(this.hasSearch){
            this.el.dom.value = '';
            var o = {start: 0};
            this.store.baseParams = this.store.baseParams || {};
            this.store.baseParams[this.paramName1] = '';
            this.store.baseParams[this.paramName2] = '';
            this.store.baseParams[this.paramName3] = '';
            this.store.reload({params:o});
            this.triggers[0].hide();
            this.hasSearch = false;
        }
    },

    onTrigger2Click : function(){
        var v = this.getRawValue();
        if(v.length < 1){
            this.onTrigger1Click();
            return;
        }
        var o = {start: 0};
        this.store.baseParams = this.store.baseParams || {};
        this.store.baseParams[this.paramName] = v;
        this.store.baseParams[this.paramName] = v;
        this.store.baseParams[this.paramName] = v;
        this.store.reload({params:o});
        this.hasSearch = true;
        this.triggers[0].show();
    }
});*/
