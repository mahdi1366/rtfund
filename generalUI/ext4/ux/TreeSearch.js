/*-----------------------
 * programmer: Jafarkhani
 * CreateDate: 86.11.29
 *-----------------------*/

// @constructor ------------------------
Ext.tree.Search = function (config) {
    Ext.apply(this, config);

    Ext.tree.Search.superclass.constructor.call(this);
};
//--------------------------------------

Ext.tree.View.override({
    ensureVisible: function (record) {
        if (record.parentNode) {
            record.parentNode.expand();
            this.ensureVisible(record.parentNode);
        }

        new Ext.util.DelayedTask(Ext.bind(function () {
            this.focusRow(record);
        }, this)).delay(500);

    }
});

Ext.extend(Ext.tree.Search, Ext.util.Observable, {
    // defaults
    searchText: 'جستجو بر اساس'
    , searchTipText: 'متن مورد نظر خود را تايپ كرده و كليد Enter را فشار دهيد'
    , selectAllText: 'Select All'
    , position: 'top'
    , iconCls: 'icon-magnifier'
    , showSelectAll: false
    , mode: 'local'
    , width: 200
    , xtype: 'treesearch'
            /**
             * @param {String} align 'left' or 'right' (defaults to 'left')
             */
    , init: function (tree) {
        this.tree = tree;
        tree.on("afterrender", Ext.bind(this.onRender, this));
        tree.on("reconfigure", Ext.bind(this.reconfigure, this));
    }
    , onRender: function () {
        var tree = this.tree;

        //var tb = 'bottom' == this.position ? this.tree.getBottomToolbar() : this.tree.getTopToolbar();
        var tb = new Ext.toolbar.Toolbar({dock: this.position});
        //this.tree.tbar[this.tree.tbar.length] = this.tree.searchField;

        // add menu
        this.menu = new Ext.menu.Menu();
        tb.add({
            text: this.searchText
            , menu: this.menu
            , id: this.tree.id + "searchButton"
            , iconCls: this.iconCls
        });

        // add filter field
        this.field = new Ext.form.TwinTriggerField({
            width: this.width
            , selectOnFocus: undefined === this.selectOnFocus ? true : this.selectOnFocus
            , trigger1Cls: 'x-form-clear-trigger'
            , trigger2Cls: 'x-form-search-trigger'
            , onTrigger1Click: Ext.bind(this.onTriggerClear, this)
            , onTrigger2Click: Ext.bind(this.onTriggerSearch, this)
        });
        this.field.on('render', function () {
            this.field.el.dom.qtip = this.searchTipText;
            var map = new Ext.KeyMap(this.field.el, [{
                    key: Ext.EventObject.ENTER
                    , scope: this
                    , fn: this.onTriggerSearch
                }, {
                    key: Ext.EventObject.ESC
                    , scope: this
                    , fn: this.onTriggerClear
                }]);
            map.stopEvent = true;
        }, this, {single: true});

        tb.add(this.field);

        tree.addDocked(tb);
        // reconfigure
        this.reconfigure();
    } // eo function onRender
    , onTriggerClear: function () {
        if (this.field.getValue() == "")
            return;
        this.field.setValue('');
        this.field.focus();
        this.onTriggerSearch();
    } // eo function onTriggerClear
    , onTriggerSearch: function (a, event, c) {
        // stop event propagation
//        debugger;
//        if (event.browserEvent.stopPropagation)
 //           event.browserEvent.stopPropagation();
  //      if (event.browserEvent.cancelBubble != null)
  //          event.browserEvent.cancelBubble = true;
//        event.browserEvent.bubbles = false;
//        event.browserEvent.cancelBubble = true;
//        event.browserEvent.stopPropagation();
        // ======================
        if (this.levelFilter) {
            console.log('searching in levels: ', this.levelFilter);
        }
        var val = this.field.getValue();
        if (this.tree.columns.length > 1)
        {
            for (i = 0; i < this.menu.items.items.length; i++)
            {
                if (this.menu.items.items[i].checked)
                {
                    this.tree.searchField = this.menu.items.items[i].dataIndex;
                    break;
                }
            }
            if (i == this.menu.items.items.length)
            {
                alert('ابتدا ستون مورد نظر براي جستجو را انتخاب كنيد');
                return false;
            }
        } else
        {
            this.tree.searchField = "text";
        }
        Ext.tree.searchingTree(this.tree, val, this.levelFilter);
        return false;

    } // eo function onTriggerSearch
    , reconfigure: function () {

        // remove old items
        var menu = this.menu;
        menu.removeAll();

        // add Select All item plus separator
        if (this.showSelectAll) {
            menu.add(new Ext.menu.CheckItem({
                text: this.selectAllText
                , checked: false
                , hideOnClick: false
                , handler: function (item) {
                    var checked = !item.checked;
                    item.parentMenu.items.each(function (i) {
                        if (item !== i && i.setChecked) {
                            i.setChecked(checked);
                        }
                    });
                }
            }), '-');
        }

        // add new items

        var cols = this.tree.columns;
        if (cols.length > 1)
        {
            for (i = 0; i < cols.length; i++)
            {
                menu.add(
                        new Ext.menu.CheckItem({
                            text: cols[i].header
                            , hideOnClick: false
                            , checked: i == 0
                            , dataIndex: cols[i].dataIndex
                            , handler: function (item) {
                                if (item.checked)
                                    return;
                                item.parentMenu.items.each(function (i) {
                                    if (item !== i)
                                        i.setChecked(false);
                                })
                            }
                        })

                        );
            }
        } else
        {
            Ext.getCmp(this.tree.id + "searchButton").hide();
        }
    } // eo function reconfigure

}); // eo extend

Ext.tree.RecurciveSearch = function (value, tree, findedNodes, node, levelFilter, currentLevel)
{
    var cs;
    if (node.childNodes && node.childNodes.length != 0)
    {
        cs = node.childNodes;
    } else
    {
        if (node.attributes)
            cs = node.attributes.children;
        else
            cs = node.children;
    }
    if (cs == undefined || cs.length == 0)
        return null;

    for (var i = 0, len = cs.length; i < len; i++) {
        //if(cs[i].attributes[tree.searchAttribute].indexOf(tree.searchField.getValue()) != -1)
        if ((!levelFilter || levelFilter.length < 1 || levelFilter.indexOf(currentLevel) > -1) && cs[i].raw[tree.searchField].indexOf(value) != -1) // if text of node contains value
        {
            findedNodes[findedNodes.length] = {
                node: cs[i],
                parent: node,
                index: i
            };
        }
        Ext.tree.RecurciveSearch(value, tree, findedNodes, cs[i], levelFilter, currentLevel + 1);
//        var node = Ext.tree.RecurciveSearch(value, tree, findedNodes, cs[i], levelFilter, currentLevel + 1);
//        if (node != null)
//        {
//            findedNodes[findedNodes.length] = {
//                node: cs[i],
//                index: i
//            };
//        }

    }
    return null;
}
Ext.tree.searchingTree = function (tree, value, levelFilter) {
    if (value == "")
        return false;

    var mask = new Ext.LoadMask(tree, {msg: 'در حال جستجو ...'});
    mask.show();

    var findedNodes = new Array();
    Ext.tree.RecurciveSearch(value, tree, findedNodes, tree.getRootNode(), levelFilter, 1);

    if (findedNodes.length != 0)
    {
        mask.hide();

        var search_menu = new Ext.menu.Menu();
        for (var i = 0; i < findedNodes.length; i++)
        {
            search_menu.add(new Ext.menu.Item({
                text: findedNodes[i].node.raw[tree.searchField],
                handler: Ext.bind(function (tree, node) {

                    tree.getSelectionModel().select(node);
                    tree.getView().ensureVisible(node);
                    setTimeout(function () {
                        tree.getView().ensureVisible(node);
                    }, 100);
                    this.destroy();

                }, search_menu, [tree, findedNodes[i].node]), //tree.selectFindedNode.createDelegate(tree,[findedNodes,i]),
                iconCls: 'arrow_left'
            }));
        }
        var coords = tree.getDockedItems("toolbar[dock='top']")[0].getEl().getXY();
        search_menu.showAt([coords[0], coords[1] + 25]);

    } else
    {
        mask.hide();
        alert('عبارت مورد جستجو يافت نشد');
    }
}
