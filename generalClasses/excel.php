<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	90.08
//-------------------------
class Excel
{
	private static function HtmlTableToArray($tableStr)
	{
		$array = array();

		$tableStr = preg_replace('/<\/table>/', '', $tableStr);
		$tableStr = preg_replace('/<table([^>]*>)/', '', $tableStr);

		$rows = preg_split('/<tr([^>]*>)/', $tableStr);

		for($i=1; $i< count($rows); $i++)
		{
			$array[$i-1] = array();

			$rows[$i] = preg_replace("/<\/tr>/", "", $rows[$i]);
			$cells =  preg_split('/<td([^\>]*>)/', $rows[$i]);

			for($j=1; $j<count($cells); $j++)
			{
				$cells[$j] = preg_replace("/<\/td>/", "", $cells[$j]);
				$cells[$j] = preg_replace("/&nbsp;/", "", $cells[$j]);

				$array[$i-1][$j-1] = $cells[$j];
			}
		}
		return $array;
	}
	
	static function HtmlTableToCSV($tableStr)
	{
		$csv = "";
		$array = self::HtmlTableToArray($tableStr);
		for($i=0; $i< count($array); $i++)
		{
			if(!is_array($array[$i]))
			{
				$csv .= $array[$i] . "\r\n";
				continue;
			}
			for($j=0; $j<count($array[$i]); $j++)
			{
				$csv .= $array[$i][$j] . "\t";
			}
			$csv = substr($csv, 0, strlen($csv)-1) . "\r\n";
		}
		return $csv;
	}

	static function ArrayToCSV($data)
	{
		function cleanData(&$str) 
		{
			$str = preg_replace("/\t/", "\\t", $str);
			$str = preg_replace("/\r?\n/", "\\n", $str);
		}
		
		$csv = "";
		$flag = false;
		foreach($data as $row)
		{
			if(!$flag)
			{
				# display field/column names as first row
				$csv .= implode("\t", array_keys($row)) . "\r\n";
				$flag = true;
			}
			array_walk($row, 'cleanData');
			$csv .= implode("\t", array_values($row)) . "\r\n";
		}
		
		return $csv;
	}

	static function HtmlTableToExcel($tableStr)
	{
		$array = self::HtmlTableToArray($tableStr);
		return self::ArrayToExcel($array, "test2.xls");
	}

	static function ArrayToExcel($array) 
	{
		require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
		require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";

		$workbook = new writeexcel_workbook("/tmp/test.xls");
		$worksheet = $workbook->addworksheet($worksheetname);
		$heading = $workbook->addformat(array('align' => 'center', 'bold' => 1, 'bg_color' => 'black', 'color' => 'white'));
		
		// heading
		$i = 0;
		$h = array();
		foreach(array_keys($array[0]) as $val)
		{
			$worksheet->write(0, $i++, $val, $heading);
			array_push($h, $val);
		}

		// values
		for ($i=0; $i < count($array); $i++)
			for ($j=0; $j < count($h); $j++)
				$worksheet->write($i+1, $j, $array[$i][$h[$j]]);

		$workbook->close();

		header("Content-type: application/zip");
		header("Content-disposition: inline; filename=excel.xls");
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header("Pragma: public");
		echo file_get_contents("/tmp/test.xls");
		unlink("/tmp/test.xls");
		die();
	}
}
	
?>