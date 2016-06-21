<script>
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
var tree;
var SourceMenu;
var CatMenu;
var subCatMenu;
var jobFieldMenu;

Ext.onReady(function(){

	//-------------------------------------------------------------------
	tree = new Ext.tree.TreePanel({
		id: 'myTree',
		el:'tree-div',
		width: 350,
		height: 600,
		title: "طبقه بندی مشاغل",
		autoScroll:true,
		rootVisible:false,
		animCollapse:false,
	    animate: false,
	    collapseMode:'mini',
		containerScroll: true, 
		loader: new Ext.tree.TreeLoader({
			preloadChildren: true,
			clearOnLoad: false		
		}),

		plugins: [new Ext.tree.Search()],
		
		root: new Ext.tree.AsyncTreeNode({
			text: 'رسته ها',
			draggable: false,
			id: 'source',
			expanded: true,
	        children: [TreeData]
		})
	});
	// طبقه بندی مشاغل
	SourceMenu = new Ext.menu.Menu({
		id: 'tmp_menu',
		items: [
			{
				text: 'ایجاد رسته',
				iconCls: 'add',
				handler: ShowJobCategory.createDelegate(this,["new"])
			}]
	});
	// رسته ها
	CatMenu = new Ext.menu.Menu({
		id: 'tmp_menu',
		items: [
			{
				text: 'ویرایش رسته',
				handler: ShowJobCategory.createDelegate(this,["edit"]),
				iconCls: 'edit'
			},{
				text: 'حذف',
				handler: DeleteJobCategory.createDelegate(this),
				iconCls: 'remove'
			},{
				text: 'ایجاد رسته فرعی',
				iconCls: 'add',
				handler: ShowJobSubCategory.createDelegate(this,["new"])
			}]
	});
	// رسته های فرعی
	SubCatMenu = new Ext.menu.Menu({
		id: 'tmp_menu',
		items: [
			{
				text: 'ویرایش رسته فرعی',
				handler: ShowJobSubCategory.createDelegate(this,["edit"]),
				iconCls: 'edit'
			},{
				text: 'حذف',
				handler: DeleteJobSubCategory.createDelegate(this),
				iconCls: 'remove'
			},{
				text: 'ایجاد رشته شغلی',
				handler: ShowJobField.createDelegate(this,["new"]),
				iconCls: 'add'
			}]
	});
	// رشته های شغلی
	jobFieldMenu = new Ext.menu.Menu({
		id: 'tmp_menu',
		items: [
			{
				text: 'ویرایش رشته شغلی',
				handler: ShowJobField.createDelegate(this,["edit"]),
				iconCls: 'edit'
			},{
				text: 'حذف',
				handler: DeleteJobField.createDelegate(this),
				iconCls: 'remove'
			},{
				text: 'انتقال',
				handler: MoveJobField.createDelegate(this),
				iconCls: 'forward'
			}]
	});
	//-------------------------------------------------------------------
	tree.on("contextmenu", function(node, e)
	{
		e.stopEvent();
		e.preventDefault();		
		node.select();
		
		if(node.id == "source")
		{
			tmp_menu = SourceMenu;
		}		
		else if(node.parentNode.id == "source")
		{
			tmp_menu = CatMenu;
		}
		else if(node.parentNode.parentNode.id == "source")
		{
			tmp_menu = SubCatMenu;
		}
		else
		{
			tmp_menu = jobFieldMenu;
		}
		
		var coords = e.getXY();
		tmp_menu.showAt([coords[0]-120, coords[1]]);
	});
	//-------------------------------------------------------------------
	tree.render();
	tree.getRootNode().expand();
	tree.getRootNode().firstChild.expand();
	//-------------------------------------------------------------------
	new Ext.Panel({
		id : "NewJobCategory",
		applyTo: "DIV_NewJobCategory",
		contentEl : "PNL_NewJobCategory",
		title: "رسته اصلی",
		width: "350px",
		autoHeight: true,
		autoShow : false
	});
	new Ext.Panel({
		id : "NewJobSubCategory",
		applyTo: "DIV_NewJobSubCategory",
		contentEl : "PNL_NewJobSubCategory",
		title: "رسته فرعی",
		width: "550px",
		autoHeight: true,
		autoShow : false
	});
	new Ext.Panel({
		id : "NewJobField",
		applyTo: "DIV_NewJobField",
		title: "رشته شغلی",
		width: "550px",
		autoHeight: true,
		autoShow : false
	});
});
//-------------------------------------------------------------------
function ShowJobCategory(mode)
{
	var node = tree.selModel.selNode;
	Ext.getCmp("NewJobCategory").show();
	Ext.getCmp("NewJobSubCategory").hide();
	
	if(mode == 'edit')
	{
		document.getElementById("JC_jcid").value = node.id;
		document.getElementById("JC_title").value = node.text;
	}
	else
	{
		document.getElementById("JC_jcid").value = "";
		document.getElementById("JC_title").value = "";
	}
	document.getElementById("JC_title").focus();
}

function saveJobCategory()
{
	if(document.getElementById("JC_title").value == "")
	{
		alert("ورود عنوان رسته اصلی الزامی است");
		return;
	}
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>job.data.php?task=JobCategorySave',
		method : 'POST',
		params : {
			id : document.getElementById("JC_jcid").value,
			title : document.getElementById("JC_title").value
		},
		success: function(response,option){			
			mask.hide();
			if(response.responseText.indexOf("true") != -1)
			{
				var st = Ext.decode(response.responseText);
				if(document.getElementById("JC_jcid").value == "")
				{
					tree.root.childNodes[0].appendChild(new Ext.tree.TreeNode({
						id : st.data,
						text : document.getElementById("JC_title").value,
						leaf : true
					}));
				}
				else
				{
					tree.root.childNodes[0].findChild("id", 
						document.getElementById("JC_jcid").value).setText(document.getElementById("JC_title").value);
				}
						
				Ext.getCmp("NewJobCategory").hide();
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
			}
		}
	});
}

function DeleteJobCategory()
{
	var node = tree.selModel.selNode;
	
	if(node.childNodes.length != 0)
	{
		alert("این رسته شامل رسته فرعی می باشد و تنها زمانی قابل حذف است که هیچ رسته فرعی نداشته باشد");
		return;
	}
	
	if(!confirm("آیا مایل به حذف می باشید؟"))
	{
		return;
	}
	
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>job.data.php?task=JobCategoryDelete',
		method : 'POST',
		params : {
			id : node.id
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

//-------------------------------------------------------------------
function ShowJobSubCategory(mode)
{
	var node = tree.selModel.selNode;
	
	Ext.getCmp("NewJobCategory").hide();
	Ext.getCmp("NewJobSubCategory").show();
	
	if(mode == 'edit')
	{
		document.getElementById("JSC_jcid").value = node.parentNode.id;
		document.getElementById("JSC_m_id").innerText = node.parentNode.id;
		document.getElementById("JSC_m_id").textContent = node.parentNode.id;
		document.getElementById("JSC_m_title").innerText = node.parentNode.text;
		document.getElementById("JSC_m_title").textContent = node.parentNode.text;
		document.getElementById("JSC_jsid").value = node.id;
		document.getElementById("JSC_old_jsid").value = node.id;
		document.getElementById("JSC_title").value = node.text;
	}
	else
	{
		document.getElementById("JSC_jcid").value = node.id;
		document.getElementById("JSC_m_id").innerText = node.id;
		document.getElementById("JSC_m_id").textContent = node.id;
		document.getElementById("JSC_m_title").innerText = node.text;
		document.getElementById("JSC_m_title").textContent = node.text;
		document.getElementById("JSC_jsid").value = "";
		document.getElementById("JSC_old_jsid").value = "";
		document.getElementById("JSC_title").value = "";
	}
	document.getElementById("JSC_jsid").focus();
}

function saveJobSubCategory()
{
	if(document.getElementById("JSC_jsid").value == "" && document.getElementById("JSC_title").value == "")
	{
		alert("ورود کد و عنوان رسته فرعی الزامی است");
		return;
	}
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>job.data.php?task=JobSubCategorySave',
		method : 'POST',
		params : {
			jcid : document.getElementById("JSC_jcid").value,
			jsid : document.getElementById("JSC_jsid").value,
			oldjsid : document.getElementById("JSC_old_jsid").value,
			title : document.getElementById("JSC_title").value
		},
		success: function(response,option){			
			mask.hide();
			if(response.responseText == "duplicateError")
			{
				alert("کد وارد شده تکراری می باشد.");
				return;
			}
			if(response.responseText == "true")
			{
				cur_node = tree.root.childNodes[0].findChild("id", document.getElementById("JSC_jcid").value).findChild(
						"id", document.getElementById("JSC_old_jsid").value);
						
				if(cur_node != null)
				{
					cur_node.attributes["id"] = document.getElementById("JSC_jsid").value;
					cur_node.id = document.getElementById("JSC_jsid").value;
					cur_node.setText(document.getElementById("JSC_title").value);
				}
				else
				{
					cur_node = tree.root.childNodes[0].findChild("id",document.getElementById("JSC_jcid").value);
					cur_node.appendChild(new Ext.tree.TreeNode({
						id : document.getElementById("JSC_jsid").value, 
						text : document.getElementById("JSC_title").value,
						leaf : true
					}));
				}
						
				Ext.getCmp("NewJobSubCategory").hide();
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
			}
		}
	});
}

function DeleteJobSubCategory()
{
	var node = tree.selModel.selNode;
	
	if(node.childNodes.length != 0)
	{
		alert("این رسته فرعی شامل رشته شغلی می باشد و تنها زمانی قابل حذف است که هیچ رشته شغلی نداشته باشد");
		return;
	}
	
	if(!confirm("آیا مایل به حذف می باشید؟"))
	{
		return;
	}
	
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>job.data.php?task=JobSubCategoryDelete',
		method : 'POST',
		params : {
			jcid : node.parentNode.id,
			jsid : node.id
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
//-------------------------------------------------------------------
function ShowJobField(mode)
{
	var node = tree.selModel.selNode;
	
	Ext.getCmp("NewJobField").show();
	
	if(mode == 'edit')
	{
		var jfid = node.id;
		
		var jcid = node.parentNode.parentNode.id;
		var jsid = node.parentNode.id;
		
		var jc_title = node.parentNode.parentNode.text;
		var js_title = node.parentNode.text;
		
		Ext.getCmp("NewJobField").load({url: "newJobField.php?jfid=" + jfid + "&jcid=" + jcid + "&jsid=" + jsid + 
			"&jc_title=" + jc_title + "&js_title=" + js_title,scripts:true});
	
	}
	else
	{
		var jcid = node.parentNode.id;
		var jsid = node.id;
		
		var jc_title = node.parentNode.text;
		var js_title = node.text;
	
		Ext.getCmp("NewJobField").load({url: "newJobField.php?jcid=" + jcid + "&jsid=" + jsid + 
			"&jc_title=" + jc_title + "&js_title=" + js_title,scripts:true});
	}
}

function saveJobField()
{
	if(document.getElementById("jfid").value == "" && document.getElementById("title").value == "")
	{
		alert("ورود کد و عنوان رشته شغلی الزامی است");
		return;
	}
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>job.data.php?task=JobFieldSave',
		method : 'POST',
		form : document.getElementById("jobFieldForm"),
		params : {
			jcid : document.getElementById("JF_jcid").value,
			jsid : document.getElementById("JF_jsid").value
		},
		
		success: function(response,option){			
			mask.hide();
			if(response.responseText == "duplicateError")
			{
				alert("کد وارد شده تکراری می باشد.");
				return;
			}
			if(response.responseText == "true")
			{
				var jcNode = tree.root.childNodes[0].findChild("id", document.getElementById("JF_jcid").value);
				var jsNode = jcNode.findChild("id", document.getElementById("JF_jsid").value);
				var curNode = jsNode.findChild("id", document.getElementById("jfid").value);
						
				if(curNode != null)
				{
					curNode.attributes["id"] = document.getElementById("jfid").value;
					curNode.id = document.getElementById("jfid").value;
					curNode.setText(document.getElementById("title").value);
				}
				else
				{
					jsNode.appendChild(new Ext.tree.TreeNode({
						id : document.getElementById("jfid").value, 
						text : document.getElementById("title").value,
						leaf : true
					}));
				}
						
				Ext.getCmp("NewJobField").hide();
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
			}
		}
	});
}

function DeleteJobField()
{
	var node = tree.selModel.selNode;
	
	if(!confirm("آیا مایل به حذف می باشید؟"))
	{
		return;
	}
	
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>job.data.php?task=DeleteJobField',
		method : 'POST',
		params : {
			jfid : node.id
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
				alert("این رشته شغلی در پست های سازمانی استفاده شده و قابل حذف نمی باشد");
			}
		}
	});
}

var moveWin;
function MoveJobField()
{
	var node = tree.selModel.selNode;
	document.getElementById("MV_jfid").value = node.id;
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
			    handler: saveChange
			}
			,{
				text: 'انصراف',
				handler: function(){moveWin.hide();}
			}]		
		});	
	}
	moveWin.show();
}

function saveChange()
{
	if(document.getElementById("MV_jcid").value == "" && document.getElementById("MV_jsid").value == "")
	{
		alert("انتخاب رسته اصلی و رسته فرعی الزامی است");
		return;
	}
	mask = new Ext.LoadMask(document.body, {msg:'در حال انتقال...'});
	mask.show();

	Ext.Ajax.request({
		url : 'job.data.php?task=MoveJobField',
		method : 'POST',
		params : {
			jfid : document.getElementById("MV_jfid").value,
			jcid : document.getElementById("MV_jcid").value,
			jsid : document.getElementById("MV_jsid").value
		},

		success: function(response){
			mask.hide();

			if(response.responseText == "true")
			{
				var jcNode = tree.root.childNodes[0].findChild("id", document.getElementById("MV_jcid").value);
				var jsNode = jcNode.findChild("id", document.getElementById("MV_jsid").value);
				var node = tree.selModel.selNode;

				jsNode.appendChild(new Ext.tree.TreeNode({
					id : node.id,
					text : node.text,
					leaf : true
				}));

				node.parentNode.removeChild(node);

				moveWin.hide();
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
			}
		}
	});
}




</script>