<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

function compareObject(o1, o2){
	for(var p in o1){
		if(o1[p] !== o2[p]){
			return false;
		}
	}
	for(var p in o2){
		if(o1[p] !== o2[p]){
			return false;
		}
	}
	return true;
}

PortalClass.prototype = {
	
	loaded_itemURL : "",
	loaded_params : {}
	
};

function PortalClass()
{
	this.mainPanel = new Ext.Panel({
		border : 0,
		autoHeight : true,
		minHeight : 600,
		renderTo : document.getElementById("mainPortalFrame"),
		loader : {}
	})

}

PortalClass.prototype.OpenPage = function(itemURL, params)
{
	if(itemURL == "")
		return;

	if(arguments.length < 2)
		params = {};

	itemURL = this.formatUrl(itemURL);
	
	var id = this.mainPanel.getEl().dom.id;
	params.ExtTabID = id;
	params.portal = 1;
	
	this.loaded_itemURL = itemURL;
	this.loaded_params = params;
	
	this.mainPanel.loader.load({
		url: this.loaded_itemURL,
		method: "POST",
		params : this.loaded_params,
		text: "در حال بار گذاری...",
		scripts: true
	});	
}

PortalClass.prototype.ReloadTab = function()
{
	this.mainPanel.loader.load({
		url: this.loaded_itemURL,
		method: "POST",
		params : this.loaded_params,
		text: "در حال بار گذاری...",
		scripts: true
	});	
}

PortalClass.prototype.logout = function()
{
	Ext.Ajax.request({
		url : "/framework/logout.php",
		method : "POST",

		success : function()
		{
			window.location = "/framework/login.php";
		}
	});
}

PortalClass.prototype.home = function()
{
	window.location = document.location;
}

PortalClass.prototype.formatUrl = function(url)
{
	var list = url.split("/");
	var list2 = new Array();
	for(var i=0; i<list.length; i++)
	{
		if(list[i] == "")
			continue;
		if(list[i] == "..")
			list2.pop();
		else
			list2.push(list[i]);
	}

	return "/" + list2.join("/");
}

PortalClass.SystemLoad = function(){};
</script>