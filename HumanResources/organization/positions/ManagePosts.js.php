<script>
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------

Ext.onReady(function(){
	new Ext.Panel({
		id: "ext_NewPost",
		applyTo: "DIV_NewPost",
		title: "پست سازمانی",
		width: "600px",
		autoHeight: true,
		autoShow : false
	}); 
});
//-------------------------------------------------------------------
var postsList = new Array();

function subPostRender(v,p,r)
{
	return "<a href='javascript:void(1);' onclick='subPostBind(\"" + r.data.post_id + "\")'>...</a>";
}

function subPostBind(post_id)
{
	Ext.getCmp('ext_NewPost').hide();
	
	var arr = [];
	arr["record"] = dg_grid.selModel.getSelected();
	arr["start"] = dg_store.lastOptions.params.start;
	
	postsList[postsList.length] = arr;

	document.getElementById("CurrentPost").value = post_id;
	dg_store.lastOptions.params.start = 0;
	dg_store.reload();
}

function back()
{
	if(postsList.length == 0)
		return;
	dg_store.lastOptions.params.start = postsList[postsList.length-1]["start"];
	postsList.splice(postsList.length-1,1);
	document.getElementById("CurrentPost").value = (postsList.length != 0) ? postsList[postsList.length-1]["record"].data.post_id : "";
	
	dg_store.reload();
}
//-------------------------------------------------------------------

function AddPost()
{
	Ext.getCmp("ext_NewPost").show();

	var currentPost = (postsList.length != 0) ? postsList[postsList.length-1]["record"] : null;
	var parentId = (currentPost) ? currentPost.data.post_id : "";
	var parentText = (currentPost) ? currentPost.data.title : "";
	var parent_path = (currentPost) ? currentPost.data.parent_path : "";
	Ext.getCmp("ext_NewPost").load({url: "newPost.php" +
		"?parent_post_id=" + parentId + "&parentText=" + parentText + "&parent_path=" + parent_path, scripts:true});
	

	/*var parentId = node.id;
	var parentText = node.text;
	var parent_path = node.parent_path;
	Ext.getCmp("DIV_NewPost").load({url: "newPost.php" +
		"?parent_post_id=" + parentId + "&parentText=" + parentText + "&parent_path=" + parent_path, scripts:true});
		
	selectedNode = node;*/
}

function GridEdit(v,p,r)
{
	return "<div align='center' title='ویرایش اطلاعات' class='edit' onclick='EditPost();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:hand;width:100%;height:16'></div>";
}

function EditPost()
{
	Ext.getCmp("ext_NewPost").show();

	var record = dg_grid.selModel.getSelected();
	var parentRecord = (postsList.length != 0) ? postsList[postsList.length-1]["record"] : null;

	var parentID = parentRecord ? parentRecord.data.post_id : "";
	var parentTitle = parentRecord ? parentRecord.data.title : "";


	Ext.getCmp("ext_NewPost").load({url: "<?= $js_prefix_address?>newPost.php?post_id=" + record.data.post_id +
		"&parent_post_id=" + parentID + "&parentText=" + parentTitle +
		"&parent_path=" + record.data.parent_path, scripts:true});

	/*var parentId = node.parentNode.id;
	var parentText = node.parentNode.text;
	var parent_path = node.parentNode.parent_path;

	Ext.getCmp("DIV_NewPost").load({url: "newPost.php?post_id=" + node.id + 
		"&parent_post_id=" + parentId + "&parentText=" + parentText + "&parent_path=" + parent_path, scripts:true});
		
	selectedNode = node;*/
}

function GridRemove()
{
	return "<div align='center' title='حذف پست' class='remove' onclick='DeletePost();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:hand;width:100%;height:16'></div>";
}

function DeletePost()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
	{
		return;
	}
	mask = new Ext.LoadMask(document.body, {msg:'در حال حذف ...'});
	mask.show();
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>post.data.php',
		method : 'POST',
		params :{
			task: "DeletePost",
			post_id : dg_grid.selModel.getSelected().data.post_id
		},
		
		success: function(response,option){			
			mask.hide();
			if(response.responseText == "true")
			{
				dg_store.reload();
			}
			else if(response.responseText == "ChildError")
			{
				alert("این پست شامل پست فرعی می باشد و تنها زمانی قابل حذف است که هیچ پست فرعی نداشته باشد");
			}
			else if(response.responseText.indexOf("FOREIGN KEY") != -1)
			{
				alert("این پست در جای دیگری استفاده شده است و قابل حذف نمی باشد.");
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
			}
		}
	});
}

function savePost()
{
	if(document.getElementById("title").value == "")
	{
		alert("ورود عنوان پست الزامی است");
		return;
	}
	if(document.getElementById("post_rowno").value == "")
	{
		alert("ورود ردیف پست الزامی است");
		return;
	}
	mask = new Ext.LoadMask(document.body, {msg:'در حال ذخيره سازي...'});
	mask.show();
	
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>post.data.php?task=SavePost',
		method : 'POST',
		form : document.getElementById("newPostForm"),
		
		success: function(response,option){			
			mask.hide();
			if(response.responseText.indexOf("true") != -1)
			{
				dg_store.reload();
				var st = Ext.decode(response.responseText);
				if(document.getElementById("post_id").value == "")
				{
					/*selectedNode.appendChild(new Ext.tree.TreeNode({
						id : st.data,
						text : document.getElementById("title").value,
						leaf : true
					}));*/
				}
				else
				{
					//selectedNode.setText(document.getElementById("title").value);
				}
						
				Ext.getCmp("ext_NewPost").hide();
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
			}
		}
	});
}

function MovePost(e)
{
	var srcNode = e.dropNode;
	var descNode = e.target; 
	if(!confirm("آیا مایل به انتقال پست \" " + srcNode.text + " \" به عنوان پست فرعی پست \" " + descNode.text + " \" می باشید؟"))
		return false;
		
	var mask = new Ext.LoadMask(document.body,{msg: 'در حال جستجو ...'});
	mask.show();
	
	Ext.Ajax.request({
		url : '<?= $js_prefix_address?>Post.data.php?task=MovePost',
		method : 'POST',
		params : {
			source_post_id : srcNode.id,
			parent_path : descNode.parent_path,
			desc_post_id : descNode.id
		},
		
		success: function(response,option){			
			mask.hide();
			if(response.responseText != "true")
				alert("عملیات مورد نظر با شکست مواجه شد.");
		}
	});
	return true;
}

function clearPID()
{
	Ext.getCmp("PID").clearValue();
	document.getElementById('staff_id').value = "";
}
</script>

