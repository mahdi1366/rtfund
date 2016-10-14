<?

/* * *****************************************************************************************
 *
 * Class Name : ReportGenerator
 *
 * *******************************************************************************************
 *
 * Script to generate report from a valid my sql connection.
 * user have to supply which fields he want to display in table.
 * All properties are changable.
 *
 */

class ReportGenerator {

	const TempFolderAddress = "/tmp/temp.xls";
	//const TempFolderAddress = "d:/webserver/temp/temp.xls";
	
	public $mysql_resource;
	public $header;
	public $foolter;
	public $fields = array();
	public $arrayName = array();
	public $fieldRenders = array();
	public $columns = array();
	public $cellpad;
	public $cellspace;
	public $border;
	public $width;
	public $modified_width;
	public $header_color;
	public $header_textcolor;
	public $header_alignment;
	public $body_color;
	public $summaryRow_color;
	public $body_textcolor;
	public $body_alignment;
	public $surrounded;
	public $page_size; //(paging) تعداد رديف ها در هر صفحه
	public $paging; // (paging) صفحه بند
	
	private $EnableSumRow = false;
	private $pageCount = 1;
	private $pageRecordCounter = 0;
	private $AllRowCount = 0;
	private $curGroup = "";
	private $PrevRecord = null;
	
	public $excel = false;
	public $rowNumber = true;
	public $rowColorRender = "";
	public $headerContent = "";
	public $footerContent = "";
	
	public $groupField = "";
	public $groupLabel = false;
	public $groupLabelRender = "";
	public $groupPerPage = false;
	
	public $showPageOfPage = false;
	
	public $VerticalSumColumn = array();
	
	public function ReportGenerator() {
		$this->border = "1";
		$this->cellpad = "1";
		$this->cellspace = "0";
		$this->width = "100%";
		$this->header_color = "#BDD3EF";
		$this->header_textcolor = "#15428B";
		$this->summaryRow_color = "#E2E2E2";
		$this->header_alignment = "right";
		$this->body_color = "#FFFFFF";
		$this->body_textcolor = "#000000";
		$this->body_alignment = "right";
		$this->surrounded = true;
		$this->modified_width = "100%";

		$this->page_size = "-1";
		$this->paging = false;
	}

	static function array_sort($array, $key, $order = SORT_ASC) {
		$new_array = array();
		$sortable_array = array();

		if (count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $key) {
							$sortable_array[$k] = $v2;
						}
					}
				} else {
					$sortable_array[$k] = $v;
				}
			}

			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
					break;
				case SORT_DESC:
					arsort($sortable_array);
					break;
			}

			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}

		return $new_array;
	}

	public function generateReport() {
		
		if ($this->mysql_resource instanceof ADORecordSet)
			$this->mysql_resource = $this->mysql_resource->GetRows();

		if (!is_array($this->mysql_resource) && !$this->mysql_resource instanceof PDOStatement)
			die("User doesn't supply any valid mysql resource after executing query result");

		if (count($this->mysql_resource) == 0)
			die("<center><span style='font-size:11px;font-family:tahoma;'>" . "گزارش مورد نظر فاقد اطلاعات می باشد." . "</span></center>");

		if ($this->excel)
		{
			$this->ExcelGeneration();
			die();
		}

		//........................ draw header .......................
		
		$this->drawHeader(true);

		//.................. fill the table with data  ......................
		$index = 0;
		
		if (is_array($this->mysql_resource)) {
			if ($this->groupField != "")
				$this->mysql_resource = self::array_sort($this->mysql_resource, $this->groupField);

			$this->AllRowCount = count($this->mysql_resource);
			foreach ($this->mysql_resource as $row) {
				$this->GroupOperation($index, $row);
				$this->DrawRow($row, $index);
				$index++;
				$this->PrevRecord = $row;
			}
		} else {
			$this->AllRowCount = $this->mysql_resource->RowCount();
			while ($row = $this->mysql_resource->fetch()) {
				$this->GroupOperation($index, $row);
				$this->DrawRow($row, $index);
				$index++;
				$this->PrevRecord = $row;
			}
		}

		$this->DrawFooter();
		
		$this->RowSpaning();
	}

	function ExcelGeneration() {
		
		$worksheet = "";
		require_once 'excel.php';
		require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
		require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";

		$workbook = new writeexcel_workbook(self::TempFolderAddress);
		$worksheet = & $workbook->addworksheet("Sheet1");
		$heading = & $workbook->addformat(array('align' => 'center', 'bold' => 1, 'bg_color' => 'blue', 'color' => 'white'));

		if ($this->rowNumber)
			$worksheet->write(0, 0, "ردیف", $heading);

		for ($i = 0; $i < count($this->columns); $i++) {
			$worksheet->write(0, $i + ($this->rowNumber ? 1 : 0), $this->columns[$i]->header, $heading);
			if ($this->columns[$i]->HaveSum != -1)
				$this->EnableSumRow = true;
		}
		if (is_array($this->mysql_resource)) {
			for ($index = 0; $index < count($this->mysql_resource); $index++) {
				$row = $this->mysql_resource[$index];
				if ($this->rowNumber)
					$worksheet->write($index + 1, 0, ($index + 1));

				for ($i = 0; $i < count($this->columns); $i++) {
					$val = "";
					 if(!empty($this->columns[$i]->renderFunction))
						eval("\$val = " . $this->columns[$i]->renderFunction . "(\$row,\$row[\$this->columns[\$i]->field]);");
					else 
						$val = $row[$this->columns[$i]->field];

					$worksheet->write($index + 1, $i + ($this->rowNumber ? 1 : 0), $val);
				}
			}
		} else {
			$index = 0;
			while ($row = $this->mysql_resource->fetch()) {
				if ($this->rowNumber)
					$worksheet->write($index + 1, 0, ($index + 1));

				for ($i = 0; $i < count($this->columns); $i++) {
					$val = "";
					/* if(!empty($this->columns[$i]->renderFunction))
						eval("\$val = " . $this->columns[$i]->renderFunction . "(\$row,\$row[\$this->columns[\$i]->field]);");
						else */
					$val = $row[$this->columns[$i]->field];

					$worksheet->write($index + 1, $i + ($this->rowNumber ? 1 : 0), $val);
				}
				$index++;
			}
		}
		$workbook->close();

		header("Content-type: application/ms-excel");
		header("Content-disposition: inline; filename=excel.xls");
		echo file_get_contents(self::TempFolderAddress);
		unlink(self::TempFolderAddress);
		die();
	}

	function drawHeader($reportTitle) {
		
		$field_count = count($this->columns);
		
		$GroupHeaderFlag = false;
		for ($i = 0; $i < $field_count; $i++) {
			if(!empty($this->columns[$i]->GroupHeader))
			{
				$GroupHeaderFlag = true;
				break;
			}
		}
		//..............................................
		
		echo "<table id='page_" . $this->pageCount . "' width='$this->width' 
				style='font-family:tahoma;border-collapse: collapse'  border='$this->border'
				cellspacing='$this->cellspace' cellpadding='$this->cellpad'>";
		
		if($reportTitle)
		{
			echo "<caption>";
			if ($this->headerContent != "")
			{
				$this->pageRecordCounter++;
				$this->pageRecordCounter++;
				echo $this->headerContent;
			}
			else if ($this->header != "") {
				echo "<center><span style='font-family:b titr;'><b>" . $this->header . "</b></span><center>";
				echo "<P></P>";
				$this->pageRecordCounter++;
			}
			echo "</caption>";
		}
		
		echo "<tr bgcolor = '$this->header_color'>";

		// row number ---------------------------
		if ($this->rowNumber)
			echo "<td " . ($GroupHeaderFlag ? "rowspan=2" : "") . 
				" align='center' style='padding:2px' border='$this->border' height='21px'><font
				color = '$this->header_textcolor' style='font-size:11px'><b>&nbsp;ردیف</b></font></th>";
		//---------------------------------------
		// Draw Header
		
		$secondRow = "";
		$currentGroup = "";
		for ($i = 0; $i < $field_count; $i++) {
			
			if($this->columns[$i]->hidden)
				continue;
			
			if($GroupHeaderFlag)
			{
				$rowspan = !empty($this->columns[$i]->GroupHeader) ? "" : "rowspan=2";
				if($rowspan == "")
				{
					if($currentGroup != $this->columns[$i]->GroupHeader)
					{
						$index = 0;
						$currentGroup = $this->columns[$i]->GroupHeader;
						while($i+$index < $field_count)
						{
							if($this->columns[$i+$index]->GroupHeader == $currentGroup)
								$index++;
							else
								break;
						}
						$colspan = "colspan=" . $index;

						echo "<td $colspan align='center' 
						style='padding:2px' border='$this->border' height='21px'><font
						color = '$this->header_textcolor' style='font-size:11px'><b>&nbsp;" . 
						$this->columns[$i]->GroupHeader . "</b></font></td>";
					}
					$secondRow .= "<td align='center' 
						style='padding:2px' border='$this->border' height='21px'><font
						color = '$this->header_textcolor' style='font-size:11px'><b>&nbsp;" . 
						$this->columns[$i]->header . "</b></font></td>";
				}
				else
				{
					echo "<td $rowspan align='" . ($this->columns[$i]->align == "" ? $this->header_alignment : 
						$this->columns[$i]->align). 
						"' style='padding:2px' border='$this->border' height='21px'><font
						color = '$this->header_textcolor' style='font-size:11px'><b>&nbsp;" . 
						$this->columns[$i]->header . "</b></font></td>";
				}
			}
			else
			{
				echo "<td align='" . ($this->columns[$i]->align == "" ? $this->header_alignment : 
						$this->columns[$i]->align). 
						"' style='padding:2px' border='$this->border' height='21px'><font
						color = '$this->header_textcolor' style='font-size:11px'><b>&nbsp;" . 
						$this->columns[$i]->header . "</b></font></td>";
			}
			
			if ($this->columns[$i]->HaveSum != -1)
				$this->EnableSumRow = true;
		}
		echo "</tr>";
		if($secondRow != "")
			echo "<tr bgcolor = '$this->header_color'>" . $secondRow . "</tr>";
		
		$this->pageRecordCounter++;
		$this->pageRecordCounter++;
	}

	/**
	 *
	 * @param text $header عنوان ستون 
	 * @param text $field فیلد مربوطه
	 * @param text $renderFunction 
	 * @param array $renderParams پارامترهای اضافی برای تابع رندر
	 * @return \ReportColumn 
	 */
	function addColumn($header, $field, $renderFunction = "", $renderParams = array()) {
		
		$obj = new ReportColumn($header, $field, $renderFunction, $renderParams);
		$this->columns[] = $obj;
		return $obj;
	}

	function GroupOperation($index, $row) {

		if($this->groupField == "")
			return;
		
		if ($index == 0)
		{
			$this->curGroup = $row[$this->groupField];
			if($this->groupLabel)
			{
				$str = $row[$this->groupField];
				if($this->groupLabelRender != "")
					eval("\$str = " . $this->groupLabelRender . "(\$row,\$index);");
				
				echo "<tr><td style=font-size:11px;font-weight:bold;height:21px; colspan=" . count($this->columns) . ">" . $str . "</td></tr>";
			}
		}

		if ($this->groupField != "" && $this->curGroup != $row[$this->groupField]) {
			if ($this->groupPerPage) {
				$this->DrawFooter();

				$this->pageRecordCounter = 0;
				echo "<div style='page-break-after: always;'>&nbsp;</div>";

				$this->drawHeader(true);
			} else {
				$this->pageRecordCounter++;
				$this->DrawSummaryRow();
				echo "</table><br>";
				
				$this->drawHeader(false);
			}
			
			if($this->groupLabel)
			{
				$str = $row[$this->groupField];
				if($this->groupLabelRender != "")
					eval("\$str = " . $this->groupLabelRender . "(\$row,\$index);");
				
				echo "<tr><td style=font-size:11px;font-weight:bold;height:21px; colspan=" . count($this->columns) . ">" . $str . "</td></tr>";
			}

			$this->EmptySummaryRow();
			$this->curGroup = $row[$this->groupField];
		}
	}

	function DrawFooter() {
		
		$this->pageCount++;
		$this->DrawSummaryRow();
		
		if ($this->showPageOfPage)
		{
			echo "<tr><td style=font-family:tahoma;font-size:11px; colspan=" . count($this->columns) . " align=left>";
			echo "صفحه " . $this->pageCount . "</td></tr>";
			$this->pageRecordCounter++;
		}
		if ($this->footerContent != "")
		{
			echo "<tr><td colspan=" . count($this->columns) . " >";
			echo $this->footerContent;
			echo "</td></tr>";
			$this->pageRecordCounter++;
		}
		
		echo "</table>";
	}

	function DrawRow($row, $index) {
		
		$color = $this->body_color;
		if ($this->rowColorRender != "")
			eval("\$color = " . $this->rowColorRender . "(\$row);");

		echo "<tr align = '$this->body_alignment' bgcolor = '$color'>";

		// row number ----------------------------
		if ($this->rowNumber) {
			echo "<td height=21px style='padding:2px;direction:" . $this->columns[0]->direction . "' 
						border='$this->border' align='" . $this->columns[0]->align . "'>
				<font color = '$this->body_textcolor' style='font-size:11px'>&nbsp;";
			echo ($index + 1) . "</font></td>";
		}
		//----------------------------------------
		for ($i = 0; $i < count($this->columns); $i++) {
			//Now Draw Data
			
			$this->columns[$i]->direction = "rtl";
			
			echo "<td height=21px id='col_" . $this->columns[$i]->field . "_" . ($index + 1) .  "' 
				style='padding:2px;direction:" . $this->columns[$i]->direction . ";" . $this->columns[$i]->style . 
					($this->columns[$i]->hidden ? ";display:none;" : "") . 
					(strpos($this->columns[$i]->field,"VerticalSum_") !== false ? "background-color:" . $this->summaryRow_color : "") .
					"' border='$this->border' align='" . $this->columns[$i]->align . "'>
				<font color = '$this->body_textcolor' style='font-size:11px'>&nbsp;";

			$val = "";
			if (!empty($this->columns[$i]->renderFunction)) {
				eval("\$val = " . $this->columns[$i]->renderFunction . "(\$row,\$row[\$this->columns[\$i]->field],\$this->columns[\$i]->renderParams);");
			}
			else if(strpos($this->columns[$i]->field, "VerticalSum_") === false) {
				$val = $row[$this->columns[$i]->field];
			}

			echo $val . "</font></td>";

			//---------------- summary Row ----------------
			$col = $this->columns[$i];
			/*@ $col ReportColumn */
			if (isset($col->HaveSum) && $col->HaveSum != -1)
			{
				if($col->rowspaning && $this->PrevRecord)
				{
					if($row[$col->field] == $this->PrevRecord[$col->field])
					{
						$flag = true;
						foreach($col->rowspanByFields as $field)
						{	
							if($row[$field] != $this->PrevRecord[$field])
								$flag = false;
						}
						if($flag)
							continue;
					}
				}
			
				if($this->columns[$i]->SummaryOfRender && !empty($this->columns[$i]->renderFunction))
				{
					eval("\$val = " . $this->columns[$i]->renderFunction . "(\$row,\$row[\$this->columns[\$i]->field],\$this->columns[\$i]->renderParams);");
					$this->columns[$i]->HaveSum = ($this->columns[$i]->HaveSum * 1) + (str_replace(",", "", $val) * 1);
				}
				else
					$this->columns[$i]->HaveSum = ($this->columns[$i]->HaveSum * 1) + ($row[$this->columns[$i]->field] * 1);
			}
			//---------------------------------------------
		}
		echo "</tr>";
		$this->pageRecordCounter++;

		if ($this->paging == true && $this->pageRecordCounter >= $this->page_size) {
			if ($index + 1 < $this->AllRowCount) {
				$this->DrawFooter();

				$this->pageRecordCounter = 0;

				echo "<div style='page-break-after: always;'>&nbsp;</div><br>";

				$this->drawHeader(true);
			}
		}
	}

	function DrawSummaryRow() {
		
		if ($this->EnableSumRow) {
			echo "<tr align = '$this->body_alignment' bgcolor = '$this->summaryRow_color'>";
			if ($this->rowNumber)
				echo "<td style='padding:2px;color:$this->body_textcolor'></td>";

			for ($i = 0; $i < count($this->columns); $i++) {
				
				if($this->columns[$i]->hidden)
					continue;
				
				if ($this->columns[$i]->HaveSum != -1) {
					echo "<td id='sum_" . $this->columns[$i]->field . "' height=21px style='font-size:11px;font-weight:bold;direction:ltr;
						padding:2px;color:$this->body_textcolor' border='$this->border' align='" . $this->columns[$i]->align . "'>";

					//---------- SumRender -----------
					$val = "";
					if (!empty($this->columns[$i]->SumRener)) {
						eval("\$val = " . $this->columns[$i]->SumRener . "(\$this->columns[\$i]->HaveSum, \$this->columns);");
					} else {
						$val = $this->columns[$i]->HaveSum;
					}
					if (is_int($val))
						echo number_format($val, 0);
					else if (is_float($val))
						echo number_format($val, 2);					 
					else
						echo $val;
					//-------------------------------
					echo "</td>";
				}
				else {
					echo "<td height=21px style='padding-right:2px' border='0'>&nbsp;</td>";
				}
			}
			echo "</tr>";
			$this->pageRecordCounter++;
		}
	}

	function EmptySummaryRow() {
		for ($i = 0; $i < count($this->columns); $i++) {
			if ($this->columns[$i]->HaveSum != -1) {
				$this->columns[$i]->HaveSum = 0;
			}
		}
	}

	function RowSpaning(){
		
		echo "<script>";
		$field_count = count($this->columns);
		for ($i = 0; $i < $field_count; $i++) {
			if($this->columns[$i]->rowspaning)
			{
				if(strpos($this->columns[$i]->field,"VerticalSum_") === false)
				{
					echo "
						//----------------------------------------------------------
						var elems = document.getElementsByTagName('td');
						var cnt = 0;
						var value = null;
						var firstElem = null;
						var groupValues = {
						" . 
						implode(" : null,", $this->columns[$i]->rowspanByFields) . (count($this->columns[$i]->rowspanByFields)>0 ? " : null" : "") .
						"
						}

						for(i=0; i<elems.length; i++)
							if(elems[i].id.indexOf('" . $this->columns[$i]->field . "') != -1)
							{
								var RowIndex = elems[i].id.replace('col_" . $this->columns[$i]->field . "_','');
								if (RowIndex != parseInt(RowIndex))
									continue;
								if(elems[i].innerHTML == value && elems[i].parentNode.parentNode.parentNode.id == firstElem.parentNode.parentNode.parentNode.id
								";
						foreach($this->columns[$i]->rowspanByFields as $field)
							echo " && groupValues." . $field . " == " . "document.getElementById('col_" . $field . "_' + RowIndex).innerHTML";

						echo	"
								)
								{
									cnt++;
									elems[i].style.display = 'none';
								}
								else
								{
									if(firstElem)
										firstElem.rowSpan = cnt;

									firstElem = elems[i];
									cnt=1;
									value = elems[i].innerHTML;
									";
									foreach($this->columns[$i]->rowspanByFields as $field)
										echo "groupValues." . $field . " = document.getElementById('col_" . $field . "_' + RowIndex).innerHTML;";
						echo"
								}
							}
						if(firstElem)
							firstElem.rowSpan = cnt;
					";
				}
				else
				{
					$amountField = preg_split('/_/', $this->columns[$i]->field);
					$amountField = $amountField[2];
					echo "
						//----------------------------------------------------------
						var elems = document.getElementsByTagName('td');
						var cnt = 0;
						var value = 0;
						var firstElem = null;
						var groupValues = {
						" . 
						implode(" : null,", $this->columns[$i]->rowspanByFields) . (count($this->columns[$i]->rowspanByFields)>0 ? " : null" : "") .
						"
						}

						for(i=0; i<elems.length; i++)
							if(elems[i].id.indexOf('" . $this->columns[$i]->field . "') != -1)
							{
								var RowIndex = elems[i].id.replace('col_" . $this->columns[$i]->field . "_','');
								if (RowIndex != parseInt(RowIndex))
									continue;
								if(1==1 ";
								foreach($this->columns[$i]->rowspanByFields as $field)
									echo " && groupValues." . $field . " == " . "document.getElementById('col_" . $field . "_' + RowIndex).innerHTML";

						echo	"
								)
								{
									cnt++;
									value += document.getElementById('col_" . $amountField . "_' + RowIndex).childNodes[1].childNodes[0].data.replace(/,/g,'')*1;
									elems[i].style.display = 'none';
								}
								else
								{
									if(firstElem)
									{
										firstElem.rowSpan = cnt;
										formatter = Intl.NumberFormat();
										firstElem.innerHTML = formatter.format(value);
									}
									firstElem = elems[i];
									value = document.getElementById('col_" . $amountField . "_' + RowIndex).childNodes[1].childNodes[0].data.replace(/,/g,'')*1;
									cnt=1;									
									";
									foreach($this->columns[$i]->rowspanByFields as $field)
										echo "groupValues." . $field . " = document.getElementById('col_" . $field . "_' + RowIndex).innerHTML;";
						echo"
								}
							}
						if(firstElem)
						{
							firstElem.rowSpan = cnt;
							formatter = Intl.NumberFormat();
							firstElem.innerHTML = formatter.format(value);
						}
					";
				}
				
			}
		}
		echo "</script>";
	}
	
	function AddVerticalSumColumn($fieldsArray, $amountField){
		
		$index = 0;
		foreach($this->columns as $col)
			if(strpos($col->field, "VerticalSum_") !== false)
				$index++;
		
		$obj = new ReportColumn("", "VerticalSum_" . $index . "_" . $amountField);
		$obj->rowspaning = true;
		$obj->rowspanByFields = $fieldsArray;
		$this->columns[] = $obj;
		return $obj;
	}
}

class ReportColumn {

	public $header;
	public $GroupHeader = "";
	public $field;
	
	public $renderFunction;
	public $renderParams;
	
	public $align;
	public $HaveSum;
	public $SumRener;
	public $direction;
	public $SummaryOfRender;
	
	
	public $rowspaning = false;
	public $rowspanByFields = array();
	
	public $hidden = false;
	public $style = "";

	public function ReportColumn($header, $field, $renderFunction = "", $renderParams = "") {
		$this->header = $header;
		$this->field = $field;
		$this->renderFunction = $renderFunction;
		$this->renderParams = $renderParams;
		$this->HaveSum = -1;
		$this->align = "right";
		$this->direction = "rtl";
		$this->SumRener = "";
	}

	public function EnableSummary($SummaryOfRender = false) {
		$this->HaveSum = 0;
		$this->direction = "ltr";
		$this->SummaryOfRender = $SummaryOfRender;
	}

}

?>