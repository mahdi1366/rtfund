<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.07
//---------------------------

?>
<html>
	<head>
	<META http-equiv=Content-Type content="text/html; charset=UTF-8" >
	<meta http-equiv="Content-Language" content="fa">

	<script type="text/javascript" src="/generalUI/resources/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="/generalUI/resources/ext-all.js"></script>

	<script src="scripts/prototype.js" type="text/javascript"></script>
	<script src="scripts/scriptaculous.js?load=effects,builder,dragdrop" type="text/javascript"></script>
	<script src="scripts/cropper.js" type="text/javascript"></script>
	<script>
	//netscape.security.PrivilegeManager.enablePrivilege('UniversalFileRead');
	netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');

	// create rendering file ...............................................
	var renderFile = Components.classes["@mozilla.org/file/directory_service;1"].
		getService(Components.interfaces.nsIProperties).get("TmpD", Components.interfaces.nsIFile);

	renderFile.append("SHJ_renderFile.tmp");

	if(!renderFile.exists("SHJ_renderFile.tmp"))
		renderFile.create(Components.interfaces.nsIFile.NORMAL_FILE_TYPE, 0666);

	/*var foStream = Components.classes["@mozilla.org/network/file-output-stream;1"].
					 createInstance(Components.interfaces.nsIFileOutputStream);
	foStream.init(renderFile, 0x02 | 0x08 | 0x20, 0666, 0);
	var converter = Components.classes["@mozilla.org/intl/converter-output-stream;1"].
							  createInstance(Components.interfaces.nsIConverterOutputStream);
	converter.init(foStream, "UTF-8", 0, 0);
	converter.writeString("executing");
	converter.close();*/

	// run the scan program ................................................
	var file = Components.classes["@mozilla.org/file/local;1"]
		.createInstance(Components.interfaces.nsILocalFile);
	file.initWithPath("C:\\SHJ_Twain\\SHJ_Twain.exe");
	//file.launch();

	// wait for scan program to completed ..................................
	var IntervalID = setInterval('checkCompletion()', 500);

	function checkCompletion()
	{
		netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
		
		var fstream = Components.classes["@mozilla.org/network/file-input-stream;1"].
					createInstance(Components.interfaces.nsIFileInputStream);
		var cstream = Components.classes["@mozilla.org/intl/converter-input-stream;1"].
								createInstance(Components.interfaces.nsIConverterInputStream);

		var data = {};

		fstream.init(renderFile, -1, 0, 0);
		cstream.init(fstream, "UTF-8", 0, 0);

		cstream.readString(-1, data); // read the whole file and put it in data.value
		cstream.close();

		if(data.value.substr(0,8) == "complete")
		{
			data.value = data.value.substr(8, data.value.length - 8);
			scanComplete(data.value);
		}
		else
			return false;
	}

	function scanComplete(images)
	{
		clearInterval(IntervalID);

		// make file inputs for images
		var imgs = images.split(',');
		for(var i=0; i < (imgs.length-1); i++)
		{
			var elem = document.createElement("input");
			elem.type = 'file';
			elem.value = imgs[i];
			elem.name = "img" + i;
			document.getElementById("div_files").appendChild(elem);
		}
		//.....................................................
		preSaveImages();
		//var src = window.location.href.split("src=")[1].split('&')[0];
		/*var form = document.getElementById("mainForm");
		data.value = src.replace('#','&');
		form.action = data.value;
		submitting();*/
	}

	function submitting()
	{
		form.submit();
		window.close();
	}

	function preSaveImages()
	{
		Ext.Ajax.request({
			url : "PreSave.php",
			method : "POST",
			form : document.getElementById("mainForm"),
			isUpload : true,

			success : function(res)
			{
				var imgs = res.responseText.split(",");
				for(var i=0; i < (imgs.length-1); i++)
				{
					var elem2 = document.createElement("img");
					elem2.src = "tempScan/" + imgs[i];
					elem2.id = "img" + i;
					
					document.getElementById("div_images").appendChild(elem2);
				
					new Cropper.Img (
						elem2.id,
						{
							minWidth: 200,
							minHeight: 200,
							ratioDim: { x: 200, y: 200 },
							displayOnInit: true,
							onEndCrop: saveCoords,
							onloadCoords: { x1: 0, y1: 0, x2: 200, y2: 200 },
						}
					);
				}
			}
		});

	}
	function saveCoords (coords, dimensions)
	{
		$( 'x1' ).value = coords.x1;
		$( 'y1' ).value = coords.y1;
		$( 'width' ).value = dimensions.width;
		$( 'height' ).value = dimensions.height;
	}
	</script>
	</head>
	<body style="font-family:tahoma">
		<center>
			لطفا تا تکمیل کامل اسکن از بستن این پنجره خودداری کنید.
		</center>
		<form id="mainForm" method="POST" enctype='multipart/form-data'>
			<div id="div_files" style="display: none;"></div>
		</form>		

		<form action="saveCrop.php" method="post">
			<div style="width:750px">
				<div style="float:left" id="div_images">
					
				</div>
				<div id="preview" style="float:right;"></div>
				<div style="clear:both"></div>
				<input type="hidden" name="x1" id="x1" value="">
				<input type="hidden" name="y1" id="y1" value="">
				<input type="hidden" name="width" id="width" value="">
				<input type="hidden" name="height" id="height" value="">
			</div>
			<input type="submit" name="Done" value=" Done ">
		</form>
	</body>
</html>