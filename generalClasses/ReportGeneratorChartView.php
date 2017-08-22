<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1396.05
//-----------------------------

global $ReportID;
global $NewPage;
global $SourceObject;
/* @var  $SourceObject ReportGenerator */

$chartTitle = isset($_POST["rpcmp_chartName"]) ? $_POST["rpcmp_chartName"] : "";
$axe_x1 = $_POST["rpcmp_x1"];
$axe_x2 = isset($_POST["rpcmp_x2"]) ? $_POST["rpcmp_x2"] : "";
$axe_x1_renderer = "";
$axe_x2_renderer = "";
$axe_y = $_POST["rpcmp_y"];
$axe_y_renderer = "";
$axe_x1_title = $axe_y_title = "";
$SeriesType = $_POST["rpcmp_series"];
$y_func = $_POST["rpcmp_y_func"];
$x1_type = "";
$x2_type = "";
$x1_func = isset($_POST["rpcmp_x1_date"]) ? $_POST["rpcmp_x1_date"] : "";
$x2_func = isset($_POST["rpcmp_x2_date"]) ? $_POST["rpcmp_x2_date"] : "";


foreach ($SourceObject->columns as $col) {
	if ($col->field == $axe_x1)
	{
		$axe_x1_title = $col->header;
		$axe_x1_renderer = $col->renderFunction;
		$x1_type = $col->type;
	}
	if ($col->field == $axe_x2)
	{
		$axe_x2_renderer = $col->renderFunction;
		$x2_type = $col->type;
	}
	if ($col->field == $axe_y)
	{
		$axe_y_title = $col->header;
		$axe_y_renderer = $col->renderFunction;
		switch($y_func)
		{
			case "sum" :	$axe_y_title = "مجموع " . $axe_y_title;	 break;
			case "count" :	$axe_y_title = "تعداد " . $axe_y_title;	 break;
			case "min" :	$axe_y_title = "مینیمم " . $axe_y_title;	 break;
			case "max" :	$axe_y_title = "ماکزیمم " . $axe_y_title;	 break;
			case "average" :$axe_y_title = "میانگین " . $axe_y_title;	 break;
		}
	}
}

$data = array();
$x2_keys = array();
$y_fields = array();
foreach ($SourceObject->mysql_resource as $row) {
	
	//................... render values .................
	if($axe_x1_renderer != "")
		$row[$axe_x1] = $axe_x1_renderer($row,$row[$axe_x1]);
	if($axe_x2_renderer != "")
		$row[$axe_x2] = $axe_x1_renderer($row,$row[$axe_x2]);
	if($axe_y_renderer != "")
		$row[$axe_y] = $axe_y_renderer($row,$row[$axe_y]);
	
	if($x1_type == "date")
		$row[$axe_x1] = DateModules::miladi_to_shamsi ($row[$axe_x1] );
	if($x2_type == "date")
		$row[$axe_x2] = DateModules::miladi_to_shamsi ($row[$axe_x2] );
	
	switch($x1_func)
	{
		case "year" :		$row[$axe_x1] = "سال" . substr($row[$axe_x1] , 0 , 4);	break;
		case "month" :		$row[$axe_x1] = DateModules::GetMonthName(substr($row[$axe_x1] , 5 , 2)*1);	break;
		case "weekday" :	$row[$axe_x1] = DateModules::GetWeekDay($row[$axe_x1]);	break;
		case "monthday" :	$row[$axe_x1] = "روز " . substr($row[$axe_x1] , 8 , 2);	break;
	}
	switch($x2_func)
	{
		case "year" :		$row[$axe_x2] = "سال" . substr($row[$axe_x2] , 0 , 4);	break;
		case "month" :		$row[$axe_x2] = DateModules::GetMonthName(substr($row[$axe_x2] , 5 , 2)*1);	break;
		case "weekday" :	$row[$axe_x2] = DateModules::GetWeekDay($row[$axe_x2]);	break;
		case "monthday" :	$row[$axe_x2] = "روز " . substr($row[$axe_x2] , 8 , 2);	break;
	}
	//..................................................
	if(!isset($data[ $row[$axe_x1] ]))
		$data[ $row[$axe_x1] ] = array();
	
	$axe_x2_value = $axe_x2 == "" ? "1" : $row[$axe_x2];
	
	if(!isset($data[ $row[$axe_x1] ][ $axe_x2_value ]))
	{
		if($y_func == "average")
			$data[ $row[$axe_x1] ][ $axe_x2_value ] = array("sum" => 0, "count" => 0);
		else
			$data[ $row[$axe_x1] ][ $axe_x2_value ] = 0;
	}
	$row[$axe_y] = $row[$axe_y]*1;
	if(!isset($x2_keys[ $axe_x2_value ]))
		$y_fields[] = "y" . count($y_fields);
	$x2_keys[$axe_x2_value] = ".";
	//.................................................
	
	switch($y_func)
	{
		case "value" :	$data[ $row[$axe_x1] ][ $axe_x2_value ] = $row[$axe_y];	break;
		case "sum" :	$data[ $row[$axe_x1] ][ $axe_x2_value ] += $row[$axe_y];	break;
		case "count" :	$data[ $row[$axe_x1] ][ $axe_x2_value ] ++;				break;
		case "min" : 
			$data[ $row[$axe_x1] ][ $axe_x2_value ] = min($data[ $row[$axe_x1] ][ $axe_x2_value ] , $row[$axe_y]);				
			break;
		case "max" : 
			$data[ $row[$axe_x1] ][ $axe_x2_value ] = max($data[ $row[$axe_x1] ][ $axe_x2_value ] , $row[$axe_y]);				
			break;
		case "average" : 
			$data[ $row[$axe_x1] ][ $axe_x2_value ]["sum"] += $row[$axe_y];
			$data[ $row[$axe_x1] ][ $axe_x2_value ]["count"] ++;
			break;
	}	
}
$returnArray = array();
foreach($data as $x1 => $x2Arr)
{
	$y_index = 0;
	$returnRow = array("x" => $x1);
	foreach($x2_keys as $x2_key => $x2)
	{
		if(isset($x2Arr[ $x2_key ]))
		{
			if(is_array($x2Arr[ $x2_key ]))
				$returnRow[ "y" . $y_index ] = round($x2Arr[ $x2_key ]["sum"]/$x2Arr[ $x2_key ]["count"], 2);
			else
				$returnRow[ "y" . $y_index ] = $x2Arr[ $x2_key ];
		}
		else
			$returnRow[ "y" . $y_index ] = 0;
		
		$y_index++;
	}
	$returnArray[] = $returnRow;
}

$x_fields = array("x");
$y_titles = array_keys($x2_keys);

/*print_r($returnArray);
$returnArray = array(
	array("x" => "منابع داخلی", "y" => 0, "y2" => 0, "y3" => 458),
	array("x" => "پارک علم و فناوری", "y" => 850, "y2" => 0, "y3" => 135),
	array("x" => "دانشگاه فردوسی", "y" => 664, "y2" => 552, "y3" => 0)
		);*/
//print_r($returnArray);die();

if($NewPage)
{
?>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/Loading.css" />
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />

		<style type="text/css">
			html, body {
				font:normal 11px tahoma;
				margin:10px;
				
			}
		</style>
	</head>
	<body >
		<div id="loading-mask"></div>
		<div id="loading">
			<div class="loading-indicator">در حال بارگذاری سیستم . . .
				<img src="/generalUI/ext4/resources/themes/icons/loading-balls.gif" style="margin-right:8px;" align="absmiddle"/></div>
		</div>

		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
		<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" />
		<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>

		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
		<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/component.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/message.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/grid/SearchField.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/TreeSearch.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/CurrencyField.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/grid/ExtraBar.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/grid/gridprinter/Printer.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/Printer/Printer-all.js"></script>
		<script type="text/javascript" src="/generalUI/ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="/generalUI/pdfobject.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/ImageViewer.js"></script>
		<link rel="stylesheet" type="text/css" href="/office/icons/icons.css" />		
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/ux/calendar/resources/css/calendar.css" />
<?php } ?>
<script>

	ReportGeneratorChart.prototype = {
		TabID: document.body,
		
		chartTitle : "<?= $chartTitle ?>",

		axe_x1_title: "<?= $axe_x1_title ?>",
		axe_y_title: "<?= $axe_y_title ?>",
		SeriesType: "<?= $SeriesType ?>",

		x_fields: <?= json_encode($x_fields) ?>,
		y_fields: <?= json_encode($y_fields) ?>,
		y_titles: <?= json_encode($y_titles) ?>,
		
		NewPage : <?= $NewPage ? "true" : "false" ?>,

		get: function (elementID) {
			return findChild(this.TabID, elementID);
		}
	};

	function ReportGeneratorChart() {

		fields = ["x"];
		fields = fields.concat(this.y_fields);
		
		this.MainStore = new Ext.data.SimpleStore({
			data: <?= reportGenerator::PHPArray_to_JSSimpleArray($returnArray) ?>,
			fields: fields
		});

		this.legendView = this.y_fields.length > 1 ? true : false;
		if(this.SeriesType == "pie")
			this.legendView = true;

		eval("this." + this.SeriesType + "();");
		if(this.NewPage)
		{
			this.ChartPanel = new Ext.form.Panel({
				width: 1050,
				title : this.chartTitle,
				height: 650 ,
				autoScroll : true,
				border: true,
				frame: true,
				maximizable: true,
				renderTo: this.get("charts"),
				items: [{
						xtype: "chart",
						store: this.MainStore,
						style: 'background:#fff',
						height: 600 ,
						width: 1000 ,
						animate: true,
						shadow: true,
						axes: this.axes,
						series: this.series,
						legend: {
							position: 'left',
							visible : this.legendView
						}
					}]
			});
		}
		else
		{
			new Ext.chart.Chart({
				renderTo: this.get("charts"),
				store: this.MainStore,
				style: 'background:#fff',
				height: 280 ,
				width: 350 ,
				animate: true,
				shadow: true,
				axes: this.axes,
				series: this.series,
				legend: {
					position: 'left',
					visible : this.legendView
				}
			});
		}
	}

	ReportGeneratorChart.prototype.bar = function () {

		this.axes = [{
				type: 'Numeric',
				position: 'bottom',
				fields: this.y_fields,
				label: {
					renderer: Ext.util.Format.numberRenderer('0,0')
				},
				title: this.axe_y_title,
				grid: true,
				minimum: 0
			}, {
				type: 'Category',
				position: 'left',
				fields: this.x_fields,
				title: this.axe_x1_title
			}];

		this.series = [{
				type: 'bar',
				axis: 'bottom',
				highlight: true,
				xField: this.x_fields,
				yField: this.y_fields,
				title: this.y_titles,
				label: {
					display: 'insideEnd',
					field: this.y_fields,
					renderer: Ext.util.Format.numberRenderer('0,0'),
					orientation: 'horizontal',
					color: '#333',
					'font-weight': "bold",
					'text-anchor': 'middle',
					contrast: true
				}
			}];
	};

	ReportGeneratorChart.prototype.column = function () {

		this.axes = [{
				type: 'Numeric',
				position: 'left',
				fields: this.y_fields,
				label: {
					renderer: Ext.util.Format.numberRenderer('0,0')
				},
				title: this.axe_y_title,
				grid: true,
				minimum: 0
			}, {
				type: 'Category',
				position: 'bottom',
				fields: this.x_fields,
				title: this.axe_x1_title
			}];

		this.series = [{
			type: 'column',
			axis: 'left',
			highlight: true,
			xField: this.x_fields,
			yField: this.y_fields,
			title: this.y_titles,
			label: {
				display: 'insideEnd',
				field: this.y_fields,
				renderer: Ext.util.Format.numberRenderer('0,0'),
				orientation: 'horizontal',
				color: '#333',
				'font-weight': "bold",
				'text-anchor': 'middle',
				contrast: true
			}
		}];
	};

	ReportGeneratorChart.prototype.pie = function () {

		this.axes = [];
		this.series = [{
			type: 'pie',
			field: this.y_fields[0],
			showInLegend: true,
			donut: false,
			tips: {
				trackMouse: true,
				width: 250,
				height: 28,
				autoWidth : true,
				'direction' : "rtl",
				renderer: function (storeItem) {
					//calculate percentage.
					<?php if($NewPage) { ?>
					me = ReportGeneratorChartObject;
					<?php } else { ?>
					me = Rpt_chart_Object<?= $ReportID ?> 
					<?php } ?>
					var total = 0;
					me.MainStore.each(function (rec) {
						total += rec.get(me.y_fields[0])*1;
					});
					
					this.setTitle(storeItem.get(me.x_fields[0]) + ': ' +
							Math.round(storeItem.get(me.y_fields[0])*1 / total * 100) + '%');
				}
			},
			highlight: {
				segment: {
					margin: 20
				}
			},
			label: {
				field: this.x_fields[0],
				display: 'rotate',
				contrast: true,
				font: 'bold 12px Tahoma'
			}
		}];
	};

	ReportGeneratorChart.prototype.gauge = function () {

		this.axes = [{
			type: 'gauge',
			position: 'gauge',
			minimum: 0,
			maximum: 100,
			steps: 10,
			margin: -10
		}];
        this.series = [{
			type: 'gauge',
			field: this.y_fields[0],
			donut: false,
			colorSet: ['#F49D10', '#ddd']
		}];
	};

	if(<?= $NewPage ? "true" : "false" ?>){

		setTimeout(function () {
			Ext.get('loading').remove();
			Ext.get('loading-mask').fadeOut({
				remove: true
			});
		}, 1);
		
		var ReportGeneratorChartObject;
		Ext.onReady(function () {
			ReportGeneratorChartObject = new ReportGeneratorChart();
		});
	}
	else
	{
		Rpt_chart_Object<?= $ReportID ?> = new ReportGeneratorChart();
	}

	


</script>
<center>
	<div id="charts" ></div>
</center>