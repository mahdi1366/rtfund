<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.03.24
//---------------------------

class jsConfig
{
	const js_version = "/generalUI";

	static function initialExt($themeColor = "", $version = 3)
	{
		if($version == 3)
		{
			echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>			
				<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
				<script type="text/javascript" src="' . self::js_version . '/resources/adapter/ext/ext-base.js"></script>';

			switch($themeColor)
			{
				case "":
					echo '<link rel="stylesheet" type="text/css" href="' . self::js_version . '/resources/css/ext-all.css" />';
					break;
				case "gray":
					break;
			}


			echo '<script type="text/javascript" src="' . self::js_version . '/resources/ext-all.js"></script>';
			//echo '<script type="text/javascript" src="' . self::js_version . '/resources/ext-rtl.js"></script>';

			echo '<link rel="stylesheet" type="text/css" href="' . self::js_version . '/resources/css/icons.css" />
				<script type="text/javascript" src="' . self::js_version . '/resources/ux/component.js"></script>
				<script type="text/javascript" src="' . self::js_version . '/resources/ux/message.js"></script>';
		}
		if($version == 4)
		{
			echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>			
				<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />
				<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
				<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
				<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>
				<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
				
				<script type="text/javascript" src="/generalUI/ext4/ux/component.js"></script>
				<script type="text/javascript" src="/generalUI/ext4/ux/message.js"></script>
				<script type="text/javascript" src="/generalUI/ext4/ux/grid/SearchField.js"></script>
				<script type="text/javascript" src="/generalUI/ext4/ux/TreeSearch.js"></script>
				<script type="text/javascript" src="/generalUI/ext4/ux/CurrencyField.js"></script>
				<script type="text/javascript" src="/generalUI/ext4/ux/grid/ExtraBar.js"></script>
				<script type="text/javascript" src="/generalUI/ext4/ux/grid/gridprinter/Printer.js"></script>';
		}
	}

	static function grid($searchFlag = true, $summeryRowFlag = false, $RowEditFlag = false, $ColumnHeaderGroup = false)
	{
		echo '<script src="' . self::js_version . '/resources/grid.js" type="text/javascript"></script>';
		if($searchFlag)
		{
			echo '<script src="' . self::js_version . '/resources/ux/grid/SearchField.js" type="text/javascript"></script>';
			echo '<script src="' . self::js_version . '/resources/ux/grid/GridFilters.js" type="text/javascript"></script>';
		}
			echo '<script src="' . self::js_version . '/resources/ux/grid/pPageSize.js" type="text/javascript"></script>';
			echo '<script src="' . self::js_version . '/resources/ux/grid/ExtraBar.js" type="text/javascript"></script>';

		if($RowEditFlag)
		{
			echo '<script src="' . self::js_version . '/resources/ux/grid/RowEditor.js?v1" type="text/javascript"></script>
				<link rel="stylesheet" type="text/css" href="' . self::js_version . '/resources/ux/grid/css/RowEditor.css" />';
		}

		if($summeryRowFlag)
			echo '<script src="' . self::js_version . '/resources/ux/grid/summeryRow.js" type="text/javascript"></script>';

		if($ColumnHeaderGroup)
			echo '<script src="' . self::js_version . '/resources/ux/grid/ColumnHeaderGroup.js" type="text/javascript"></script>';
	}

	static function tree($searchFlag = true)
	{
		echo '<script src="' . self::js_version . '/resources/DragDrop.js" type="text/javascript"></script>';
		echo '<script src="' . self::js_version . '/resources/treePanel.js" type="text/javascript"></script>';
		if($searchFlag)
			echo '<script src="' . self::js_version . '/resources/ux/TreeSearch.js" type="text/javascript"></script>';
	}

	static function tab()
	{
		echo '<script src="' . self::js_version . '/resources/TabPanel.js" type="text/javascript"></script>';
	}

	static function HtmlEditor()
	{
		echo '<script src="' . self::js_version . '/resources/HtmlEditor.js" type="text/javascript"></script>';
		echo '<script src="' . self::js_version . '/resources/ux/wordFix.js" type="text/javascript"></script>';
	}

	static function window()
	{
		echo '<script src="' . self::js_version . '/resources/DragDrop.js" type="text/javascript"></script>';
		echo '<script src="' . self::js_version . '/resources/Window.js" type="text/javascript"></script>';
	}

	static function date()
	{
		echo '<script src="' . self::js_version . '/resources/date.js" type="text/javascript"></script>';
	}

	static function viewport()
	{
		echo '<script src="' . self::js_version . '/resources/DragDrop.js" type="text/javascript"></script>';
		echo '<script src="' . self::js_version . '/resources/viewport.js" type="text/javascript"></script>';
	}
}

?>