/*-----------------------
 * programmer: Jafarkhani
 * CreateDate: 86.11.29
 *-----------------------*/

Ext.namespace('Ext.ux', 'Ext.ux.grid');

// @constructor ------------------------
Ext.tree.Search = function(config) {
    Ext.apply(this, config);

    Ext.tree.Search.superclass.constructor.call(this);
};
//--------------------------------------

Ext.extend(Ext.tree.Search, Ext.util.Observable, {
    // defaults
     searchText:'جستجو بر اساس'
    ,searchTipText:'متن مورد نظر خود را تايپ كرده و كليد Enter را فشار دهيد'
    ,selectAllText:'Select All'
    ,position:'top'
    ,iconCls:'icon-magnifier'
    ,disableIndexes:[]
    ,dateFormat:undefined
    ,showSelectAll:false
    ,mode:'local'
    ,width:200
    ,xtype:'treesearch'
    
    
    /**
     * @param {String} align 'left' or 'right' (defaults to 'left')
     */
    
    ,init:function(tree) {
        this.tree = tree;
		tree.onRender = tree.onRender.createSequence(this.onRender, this);
		//tree.reconfigure = tree.reconfigure.createSequence(this.reconfigure, this);
    }
    ,onRender:function() {
        var tree = this.tree;
        var tb = 'bottom' == this.position ? this.tree.getBottomToolbar() : this.tree.getTopToolbar();
		//this.tree.tbar[this.tree.tbar.length] = this.tree.searchField;
		aa = tree.tbar;
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
			,id: tree.id + "searchButton"
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
        this.reconfigure(this);
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
		
        if(this.tree.columns != undefined)
		{
			for(i=0; i< this.menu.items.items.length; i++)
			{
				if(this.menu.items.items[i].checked) 
				{
					this.tree.searchField = this.menu.items.items[i].dataIndex; 
					break;
				}
			}
			if(i == this.menu.items.items.length)
			{
				alert('ابتدا ستون مورد نظر براي جستجو را انتخاب كنيد');
				return;
			}
		}
		else
		{
			this.tree.searchField = "text";
		}
		Ext.tree.searchingTree(this.tree, val);

    } // eo function onTriggerSearch
    
    ,reconfigure:function() {

        // remove old items
        var menu = this.menu;
        menu.removeAll();

        // add Select All item plus separator
        if(this.showSelectAll) {
            menu.add(new Ext.menu.CheckItem({
                 text:this.selectAllText
                ,checked:false
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
		
        var cols = this.tree.columns;
		if(cols != undefined)
		{
			for(i=0; i<cols.length; i++)
			{
				menu.add(
					new Ext.menu.CheckItem({
						 text: cols[i].header
						,hideOnClick:false
						,checked: i == 0
						,dataIndex: cols[i].dataIndex
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
		else
		{
			Ext.getCmp(this.tree.id + "searchButton").hide();
		}
    } // eo function reconfigure

}); // eo extend

Ext.tree.RecurciveSearch = function (value, tree, findedNodes, node)
{
	//var cs = node.childNodes;
	if(node.attributes)
		var cs = node.attributes.children;
	else
		var cs = node.children;
		
	if(cs == undefined || cs.length == 0)
		return null;
	
	for(var i = 0, len = cs.length; i < len; i++) {
		//if(cs[i].attributes[tree.searchAttribute].indexOf(tree.searchField.getValue()) != -1)
		if(cs[i][tree.searchField].indexOf(value) != -1)
		{
			findedNodes[findedNodes.length] = { 
				node : cs[i],
				parent : node,
				index : i
			};
		}
		else
		{
			var node = Ext.tree.RecurciveSearch(value, tree, findedNodes, cs[i]);
			if(node != null)
			{
				findedNodes[findedNodes.length] ={ 
					node : cs[i],
					index : i
				};
			}
				
		}
	}
	return null;
}

Ext.tree.searchingTree = function (tree, value){
	if(value == "")
		return false;
		
	var mask = new Ext.LoadMask(document.body,{msg: 'در حال جستجو ...'});
	mask.show();
	
	var findedNodes = new Array();
	Ext.tree.RecurciveSearch(value, tree, findedNodes, tree.getRootNode());
	
	if(findedNodes.length != 0)
	{
		mask.hide();   
		if(findedNodes.length == 1)
		{
			 tree.selectFindedNode(findedNodes, 0);
		}
		else
		{
			var search_menu = new Ext.menu.Menu({id: 'search_menu'});
			for(var i=0; i<findedNodes.length; i++)
			{
				search_menu.addItem(new Ext.menu.Item({
					text : findedNodes[i].node[tree.searchField] ,
					handler : tree.selectFindedNode.createDelegate(tree,[findedNodes,i]),
					iconCls : 'arrow_left'
				}));
			}
			var coords = tree.tbar.getXY();
			search_menu.showAt([coords[0], coords[1] + 25]);
		}
	}	
	else
	{
		mask.hide();
		alert('عبارت مورد جستجو يافت نشد');
	}
}