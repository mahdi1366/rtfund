<script>
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
var tree;
var op_menu;
Ext.onReady(function(){
	//-------------------------------------------------------------------
	tree = new Ext.tree.TreePanel({
		id: 'myTree',
		el:'tree-div',
		width: 500,
		height: 600,
		title: "واحد های سازمانی",
		autoScroll:true,
		animCollapse:false,
	    animate:true,
        //enableDD:true,
	    collapseMode:'mini',
		containerScroll: true, 
		loader: new Ext.tree.TreeLoader({
			preloadChildren: true,
			clearOnLoad: false		
		}),
		
		plugins: [new Ext.tree.Search()],
		
		root: new Ext.tree.AsyncTreeNode({
			text: 'واحد های اصلی',
			draggable: false,
			id: 'source',
			expanded: true,
			loader: new Ext.tree.TreeLoader({
				preloadChildren: true,
				dataUrl : "unit.data.php?task=GetTreeNodes",
				clearOnLoad: false
			})
		})
	});	
	//-------------------------------------------------------------------
	op_menu = new Ext.menu.Menu({
		id: 'op_menu',
		items: [
			{
				text: 'ایجاد زیر واحد',
				iconCls: 'add',
				//handler: AddUnit.createDelegate(this,[node])
				handler: AddUnit
			},{
				text: 'ویرایش اطلاعات واحد سازمانی',
				//handler: EditUnit.createDelegate(this,[node]),
				handler: EditUnit,
				iconCls: 'edit'
			},{
				text: 'حذف واحد سازمانی',
				//handler: DeleteUnit.createDelegate(this,[node]),
				handler: DeleteUnit,
				iconCls: 'remove'
			},{
				text: 'انتقال',
				//handler: BeforeMoveUnit.createDelegate(this,[node]),
				handler: BeforeMoveUnit,
				iconCls: 'forward'
			}]
	});

	tree.on("contextmenu", function(node, e)
	{
		e.stopEvent();
		e.preventDefault();		
		node.select();
		
		var coords = e.getXY();
		op_menu.showAt([coords[0]-120, coords[1]]);
	});
	//-------------------------------------------------------------------
	tree.render();
	tree.getRootNode().expand();
	//-------------------------------------------------------------------
	new Ext.Panel({
		id : "Ext_NewUnit",
		applyTo: "DIV_NewUnit",
		title: "واحد سازمانی",
		width: "400px",
		autoHeight: true,
		autoShow : false
	});

});
//-------------------------------------------------------------------
var selectedNode;
function AddUnit()
{
	var node = tree.selModel.selNode;
	
	Ext.getCmp("Ext_NewUnit").show();
	
	var parentId = node.id;
	var parentText = node.text;
	var parent_path = node.attributes["parent_path"];
	
	Ext.getCmp("Ext_NewUnit").load({url: "<?= $js_prefix_address?>newOrgUnit.php" +
		"?parent_ouid=" + parentId + "&parentText=" + parentText + "&parent_path=" + parent_path, scripts:true});
		
	selectedNode = node;
}

function EditUnit()
{

	var node = tree.selModel.selNode;
	
	Ext.getCmp("Ext_NewUnit").show();
	
	var parentId = node.parentNode.id;
	var parentText = node.parentNode.text;
	var parent_path = node.attributes["parent_path"];
	alert(node.id) ;
	alert(parentId) ;
	alert(parentText); 
	alert(parent_path) ; 
	
	Ext.getCmp("Ext_NewUnit").load({url: "<?= $js_prefix_address?>newOrgUnit.php?ouid=" + node.id +
		"&parent_ouid=" + parentId + "&parentText=" + parentText + "&parent_path=" + parent_path, scripts:true});
		
	selectedNode = node;
}

function DeleteUnit()
{
	var node = tree.selModel.selNode;
	
	if(node.childNodes.length != 0)
	{
		alert("این واحد شامل واحد فرعی می باشد و تنها زمانی قابل حذف است که هیچ واحد فرعی نداشته باشد");
		return;
	}
	
	if(!confirm("آیا مایل به حذف می باشید؟"))
	{
		return;
	}
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>unit.data.php?task=DeleteUnit',
		method : 'POST',
		params :{
			ouid : node.id
		},
		
		success: function(response,option){			
			mask.hide();
			if(response.responseText == "true")
			{
				Ext.fly(node.ui.elNode).ghost('l', {
	                callback: function(){
	                	node.remove();
	                }, scope: node, duration: 1
	            });										
				
				return;
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
			}
		}
	});
}

function saveUnit()
{
	if(document.getElementById("ptitle").value == "")
	{
		alert("ورود عنوان واحد الزامی است");
		return;
	}
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>unit.data.php?task=SaveUnit',
		method : 'POST',
		form : document.getElementById("form_newUnit"),
		
		success: function(response,option){			
			mask.hide();
			if(response.responseText.indexOf("true") != -1)
			{
				var st = Ext.decode(response.responseText);
				if(document.getElementById("ouid").value == "")
				{
					selectedNode.appendChild(new Ext.tree.TreeNode({
						id : st.data,
						text : document.getElementById("ptitle").value,
						parent_path : document.getElementById("parent_path").value,
						leaf : true
					}));
				}
				else
				{
					selectedNode.setText(document.getElementById("ptitle").value);
				}
						
				Ext.getCmp("Ext_NewUnit").hide();
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
			}
		}
	});
}

var moveWin;
function BeforeMoveUnit()
{
	selectedNode = tree.selModel.selNode;
	if(!moveWin)
	{
		moveWin = new Ext.Window({
			id: 'movWin',
			el:'moveDIV',
			layout:'fit',
			modal: true,
			width:500,
			height:150,
			closeAction:'hide',
			items: [
				new Ext.Panel({
					contentEl : "movePNL",
					title: "انتقال رشته شغلی"
				})
			],
			buttons: [{
			    text:'انتقال',
			    iconCls: 'forward',
			    handler: MoveUnit
			}
			,{
				text: 'انصراف',
				handler: function(){moveWin.hide();}
			}]
		});
	}
	moveWin.show();
}

function MoveUnit()
{
	var node = selectedNode;
	if(Ext.getCmp("ext_org_units").getValue() == "")
	{
		alert("واحد پدر را ابتدا انتخاب کنید.");
		return false;
	}
	var root = tree.getRootNode();
	var descNode = root.fullFindChild("id", Ext.getCmp("ext_org_units").getValue());

	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>unit.data.php?task=MoveUnit',
		method : 'POST',
		params : {
			source_ouid : node.id,
			parent_path : descNode.attributes["parent_path"],
			desc_ouid : descNode.id
		},

		success: function(response){
			mask.hide();
			moveWin.hide();
			if(response.responseText == "true")
			{
				var secondParent = descNode;
				secondParent.appendChild(node);
				return true;
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
				return false;
			}

		}
	});
}
//-------------------------------------------------------------------

</script>