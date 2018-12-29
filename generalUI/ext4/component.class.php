<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	86.09.18
//-------------------------

class CHECKBOXLIST extends common_component
{
	public $datasource;
	public $idfield;
	public $textfield;
	public $valuefield;
	public $checkStyle;
	public $checkEvent;
	public $textStyle;
	public $textEvent;
	public $Allchecked;
	
	public $EnableCheckAllButton = false;
	public $columnCount = 1;

	private static $checkCounter = 1;
	
	function bind_checkboxlist($selectedIds = "")
	{
		
		if($this->datasource instanceof ADORecordSet)
		{
			$this->datasource = $this->datasource->GetRows();
		}
		
		$str = "<table style='width:100%'>";
		
		if($this->EnableCheckAllButton)
			$str .= "<tr><td colspan='" . $this->columnCount . "'>
						<input type='checkbox' onclick='Allcheck_" . self::$checkCounter . "(this);'> انتخاب همه
					</td></tr>";
		
		$str .= "<tr>";
		$colIndex = 1;
		$ids = "";
		
		for($i=0; $i < count($this->datasource); $i++, $colIndex++)
		{
			$id = parent::bind_GetFeilds($this->datasource[$i], $this->idfield);
			$ids .= "\"" . $id . "\","; 
			$value = parent::bind_GetFeilds($this->datasource[$i], $this->valuefield);
			
			$ISselected = ($selectedIds != "") ? array_search($id , $selectedIds) : false;
			$ISselected = ($ISselected === 0) ? true : $ISselected;

			$str .= "<td><input type='checkbox' name='$id' id='$id' value='$value' ";
			$str .= ($ISselected || $this->Allchecked) ? "checked" : "";
			$str .= " style='$this->checkStyle' $this->checkEvent>
						<span id='$id' style='$this->textStyle' $this->textEvent>"
						. parent::bind_GetFeilds($this->datasource[$i], $this->textfield) . "</span></td>";
			
			if($colIndex == $this->columnCount)
			{
				$str .= "</tr><tr>";
				$colIndex = 0;
			}				
		}
		$str .= "</tr></table>";
		
		//.......................................
		if($this->EnableCheckAllButton)
		{
			$ids = substr($ids, 0, strlen($ids)-1);
			$str .= "<script>
					var AllcheckArray_" . self::$checkCounter . " = new Array($ids);
					function Allcheck_" . self::$checkCounter . "(elem)
					{
						for(i=0; i< AllcheckArray_" . self::$checkCounter . ".length; i++)
							document.getElementById(AllcheckArray_" . self::$checkCounter . "[i]).checked = 
								(elem.checked) ? true : false;
					}
					</script>";
		}
		//.......................................
		self::$checkCounter++;
		return $str;
	}
}

class DROPDOWN extends common_component
{
	public $datasource;
	public $valuefield;
	public $textfield;
	public $Style;
	public $Event;
	public $id;
	public $formName;
	
	function bind_dropdown($selectedID = "")
	{
		if($this->datasource instanceof ADORecordSet)
			$this->datasource = $this->datasource->GetRows();
			
		$str = "<select class='x-form-text'
					name='" . $this->id . "'
					id='" . $this->id . "'
					" . $this->Style . " " . $this->Event . ">";

		for($i=0; $i < count($this->datasource); $i++)
		{
			$temp = parent::bind_GetFeilds($this->datasource[$i], $this->valuefield);

			$str .= "<option value='" . $temp . "' ";
			$str .= ($selectedID == $temp) ? " selected " : "";
			$str .= ">" . parent::bind_GetFeilds($this->datasource[$i], $this->textfield) . "</option>";
		}
		
		$str .= "</select>";
		return $str;
	}
}

class AutoComplete_DROPDOWN extends DROPDOWN
{
	public $width;
	public $withStoreObject = false;
	
	function bind_dropdown_returnObjects($selectedID = "")
	{
		/*if($this->withStoreObject)
		{*/
			if($this->datasource instanceof ADORecordSet)
				$this->datasource = $this->datasource->GetRows();

			$return["combo"] = "<input type='text' id='ext_".$this->id."' value='".$selectedID."'>";

			$return["extCombo"] = "
				new Ext.form.ComboBox({
					mode: 'local',
					hiddenName: '$this->id',
					store: new Ext.data.SimpleStore({
						fields: ['" . implode("','", array_keys($this->datasource[0])) . "'],
						data : ".parent::PHPArray_to_JSSimpleArray($this->datasource)."
					}),
					typeAhead: true,
					displayField : '" . str_replace("%", "", $this->textfield) . "',
					valueField : '" . str_replace("%", "", $this->valuefield) . "',
					applyTo : " . (!empty($this->formName) ? "document.forms['$this->formName'].ext_".$this->id : "ext_" . $this->id) . ",
					triggerAction: 'all',";
			$return["extCombo"] .= ($this->width != "") ? "width: " . $this->width . ", minListWidth: " . $this->width . "," : "";
			$return["extCombo"] .= "forceSelection:true
				})";

			return $return;
		/*}
		else
		{
			$return["combo"] = parent::bind_dropdown($selectedID);

			$return["extCombo"] = "
				new Ext.form.ComboBox({
				typeAhead: true,
				triggerAction: 'all',";
			$return["extCombo"] .= (!empty($this->formName)) ? "transform:document.forms['$this->formName'].$this->id," :
				"transform: '$this->id',";
			$return["extCombo"] .= ($this->width != "") ? "width: " . $this->width . ", minListWidth: " . $this->width . "," : "";
			$return["extCombo"] .= "forceSelection:true
				})";

			return $return;
		}	*/
	}

	function bind_dropdown($selectedID = "")
	{
		$return = "<div id='ext_" . $this->id . "'/><script>
			new Ext.form.ComboBox({
			typeAhead: true,
			mode: 'local',
			hiddenName: '$this->id',
			renderTo : 'ext_" . $this->id . "',
        	triggerAction: 'all',
            store: new Ext.data.SimpleStore({
				fields: ['" . implode("','", array_keys($this->datasource[0])) . "'],
				data : ".parent::PHPArray_to_JSSimpleArray($this->datasource)."
			}),
			value : '" . $selectedID . "',
			displayField : '" . str_replace("%", "", $this->textfield) . "',
			valueField : '" . str_replace("%", "", $this->valuefield) . "',";
        $return .= ($this->width != "") ? "width: '" . $this->width . "', minListWidth: '" . $this->width . "'," : "";
        $return .= "forceSelection:true
		});</script>";
    	
    	return $return;
	}
}

class MaserDetail_DROPDOWN extends common_component
{
	public $Master_datasource;
	public $Master_valuefield;
	public $Master_valuefield2;
	public $Master_valuefield3;
	public $Master_textfield;
	public $Master_id;
	public $Master_Style;
	public $Master_Width;
	public $Master_Event;
	public $Master_formName;
	
	public $Detail_datasource;
	public $Detail_storeUrl;
	public $Detail_valuefield;
	public $Detail_textfield;
	public $Detail_masterfield;
	public $Detail_masterfield2;
	public $Detail_masterfield3;
	public $Detail_id;
	public $Detail_Style;
	public $Detail_Event;
	
	function bind_dropdown(&$masterOutput, &$detailOutput, $MasterSelectedID = "", $DetailSelectedID = "")
	{
		//$this->Master_Width = ($this->Master_Width == "") ? 100 : $this->Master_Width;
		
		$this->Master_textfield = str_replace("%", "", $this->Master_textfield);
		$this->Master_valuefield = str_replace("%", "", $this->Master_valuefield);
		$masterOutput = "<input type='text' id='app_$this->Master_id'>";
		$masterKeys = "'" . implode("','", array_keys($this->Master_datasource[0])) . "'";
		//-----------------------------------------------------------------------------------
		$detailOutput = "<select name='" . $this->Detail_id . "' id='" . $this->Detail_id . "' class='x-form-text x-form-field'" . 
						$this->Detail_Style . " " . $this->Detail_Event . "></select>";
		//-----------------------------------------------------------------------------------
		
		$detailOutput .= "<script>
			Ext.onReady(function(){ 
				
				" . $this->Detail_id . "_EXTData = " . parent::PHPArray_to_JSArray($this->Detail_datasource, $this->Detail_textfield, 
					$this->Detail_valuefield, $this->Detail_masterfield, $this->Detail_masterfield2, $this->Detail_masterfield3) . ";
					
				var " . $this->Master_id . "_EXTData = " . parent::PHPArray_to_JSSimpleArray($this->Master_datasource) . ";
								
	        	var extCombo_$this->Master_id = new Ext.form.ComboBox({
					id:'ext_$this->Master_id',
					typeAhead: true,
					hiddenName: '$this->Master_id',
					store: new Ext.data.SimpleStore({
						fields: [$masterKeys],
				        data : " . $this->Master_id . "_EXTData 
				    }),
					minListWidth : 150,
					listAlign: 'tr-br',  
					displayField: '$this->Master_textfield',
					valueField: '$this->Master_valuefield',  
		        	triggerAction: 'all',
		        	forceSelection:true,
		        	applyTo: 'app_$this->Master_id',
		        	mode: 'local',";
		        	
		$detailOutput .= ($this->Master_Width != "") ? "width: $this->Master_Width ,minListWidth: $this->Master_Width," : "";
		$detailOutput .= "
		        	listeners: {
						'render' : function(){
								this.doQuery(this.allQuery, true);
								this.setValue('$MasterSelectedID');
								document.getElementById('$this->Detail_id').value = '$DetailSelectedID';
						},
		        		'select' : function(combo, record, index){
        					BindDropDown(document.getElementById('$this->Detail_id'),
								".$this->Detail_id."_EXTData, record.get('$this->Master_valuefield'),
        						record.get('$this->Master_valuefield2'),record.get('$this->Master_valuefield3'));
						},
						'afterSetValue' : function(combo, record){
							if(record)
							{
								BindDropDown(document.getElementById('$this->Detail_id'),
									".$this->Detail_id."_EXTData, record.get('$this->Master_valuefield'),
        							record.get('$this->Master_valuefield2'),record.get('$this->Master_valuefield3'));

								document.getElementById('$this->Detail_id').value = '$DetailSelectedID';
							}
						}		
					}
				});";
			$detailOutput .= "});</script>";
		//-----------------------------------------------------------------------------------
		return $detailOutput;
	}

	function bind_dropdown_returnObjects($MasterSelectedID = "", $DetailSelectedID = "")
	{
		//$this->Master_Width = ($this->Master_Width == "") ? 100 : $this->Master_Width;
		$returnArr = array();
		$this->Master_textfield = str_replace("%", "", $this->Master_textfield);
		$this->Master_valuefield = str_replace("%", "", $this->Master_valuefield);

		$masterOutput = "<input type='text' id='app_$this->Master_id'>";
		$returnArr["masterCombo"] = $masterOutput;

		$masterKeys = "'" . implode("','", array_keys($this->Master_datasource[0])) . "'";
		//-----------------------------------------------------------------------------------
		$detailOutput = "<select name='" . $this->Detail_id . "' id='" . $this->Detail_id . "' class='x-form-text x-form-field'" .
						$this->Detail_Style . " " . $this->Detail_Event . "></select>";
		$returnArr["detailCombo"] = $detailOutput;
		//-----------------------------------------------------------------------------------
		$returnArr["masterExtCombo"] = "new Ext.form.ComboBox({
						typeAhead: true,
						applyTo: this.get('app_".$this->Master_id."'),
						mode: 'local',
						form: '$this->Master_formName',
						minListWidth : 150,
						listAlign: 'tr-br',
						displayField: '$this->Master_textfield',
						valueField: '$this->Master_valuefield',
						triggerAction: 'all',
						hiddenName: '$this->Master_id',
						forceSelection:true,
						" . (($this->Master_Width != "") ? "width: $this->Master_Width ,minListWidth: $this->Master_Width," : "") . "

						listeners: {
							'render' : function(){
								this.doQuery(this.allQuery, true);
								if(Ext.isString(this.form))
									this.form = document.getElementById(this.form);
								";
		$returnArr["masterExtCombo"] .= (!empty($this->Detail_storeUrl)) ?
								"Ext.Ajax.request({
									url : '".$this->Detail_storeUrl."',method : 'post',
									success : function(response){
										eval('this.detailData = ' + response.responseText + ';');
										this.setValue('$MasterSelectedID');
										var record = this.findRecord(this.valueField, '$MasterSelectedID');
										if(record)
										{
											BindDropDown(this.form.$this->Detail_id,
												this.detailData, record.get('$this->Master_valuefield'),
												record.get('$this->Master_valuefield2'),record.get('$this->Master_valuefield3'));
											this.form.$this->Detail_id.value = '$DetailSelectedID';
										}										
									}.createDelegate(this)
								});" : "
								this.setValue('$MasterSelectedID');
								var record = this.findRecord(this.valueField, '$MasterSelectedID');
								if(record)
								{
									BindDropDown(this.form.$this->Detail_id,
										this.detailData, record.get('$this->Master_valuefield'),
										record.get('$this->Master_valuefield2'),record.get('$this->Master_valuefield3'));
									this.form.$this->Detail_id.value = '$DetailSelectedID';
								}";
		$returnArr["masterExtCombo"] .="},
							'select' : function(combo, record, index){
								BindDropDown(this.form.$this->Detail_id,
									this.detailData, record.get('$this->Master_valuefield'),
									record.get('$this->Master_valuefield2'),record.get('$this->Master_valuefield3'));
							}/*,
							'afterSetValue' : function(combo, record){
								if(record)
								{
									BindDropDown(this.form.$this->Detail_id,
									this.detailData, record.get('$this->Master_valuefield'),
									record.get('$this->Master_valuefield2'),record.get('$this->Master_valuefield3'));

									this.form.$this->Detail_id.value = '$DetailSelectedID';
								}
							}*/
						},
						store: new Ext.data.SimpleStore({
							fields: [$masterKeys],
							data : ".parent::PHPArray_to_JSSimpleArray($this->Master_datasource)."
						})".
						(!empty($this->Detail_datasource) ? ",detailData : " . parent::PHPArray_to_JSArray($this->Detail_datasource, $this->Detail_textfield,
								$this->Detail_valuefield, $this->Detail_masterfield, $this->Detail_masterfield2, $this->Detail_masterfield3) : "") .
						
					"})";
		//-----------------------------------------------------------------------------------
		return $returnArr;
	}

}

class common_component
{
	function bind_GetFeilds($row, $string)
	{
		$value = "";
		$check = false;
		$temp = "";
		for ($j=0; $j<strlen($string); $j++)
		{
			if($string[$j] == '%')
			{
				if(!$check)
				{
					$check = true;
					$temp = "";
				}
				else
				{
					$check = false;
					$value .=  $row[$temp];
					$temp = "";
				}
			}
			else
			{
				if($check)
					$temp .= $string[$j];

				else
					$value .= $string[$j];
			}
		}
		return $value;
	}

	/**
	 * this function convert a 2D php array to a 2D javascript array
	 * @param string $textField : in javascript array replace the namm of this field with 'id' in each array record 
	 * @param string $valueField : in javascript array replace the namm of this field with 'text' in each array record
	 * @param string $masterField : in javascript array replace the namm of this field with 'master' in each array record
	 */
	static function PHPArray_to_JSArray($datasource, $textField = "", $valueField = "",
		$masterField1 = "", $masterField2 = "", $masterField3 = "")
	{
		$text = str_replace("%", "", $textField);
		$value = str_replace("%", "", $valueField);
		$master1 = str_replace("%", "", $masterField1);
		$master2 = str_replace("%", "", $masterField2);
		$master3 = str_replace("%", "", $masterField3);
		
		if(count($datasource) == 0)
			return "[]";

		$keys = array_keys($datasource);

		$str = "[";
		for($i=0; $i<count($datasource); $i++)
		{
			if(is_array($datasource[$i]))
			{
				$str .= "{";
				$RowKeys = array_keys($datasource[$i]);
				for($j=0; $j<count($RowKeys); $j++)
				{
					$key = "";
					if($RowKeys[$j] === $text)
						$key = "text";
					else if($RowKeys[$j] === $value)
						$key = "id";
					else if($RowKeys[$j] === $master1)
						$key = "master1";
					else if($RowKeys[$j] === $master2)
						$key = "master2";
					else if($RowKeys[$j] === $master3)
						$key = "master3";
					else 
						$key = $RowKeys[$j];
					
					$str .= "'" . $key . "':'" . $datasource[$i][$RowKeys[$j]] . "',";
				}
				$str = substr($str,0,strlen($str)-1) . "},";				
			}
			else
			{
				$str .= $keys[$i] . ":'" . $datasource[$i] . "',";
			}			
		}
		$str = substr($str,0,strlen($str)-1) . "]";
		return $str;
	}

	static function PHPArray_to_JSSimpleArray($datasource)
	{
		if(!is_array($datasource))
			return "[]";
		
		if(count($datasource) == 0)
			return "[]";
			
		$output = "[ ";
		for($i=0; $i<count($datasource); $i++)
		{
			if(is_array($datasource[$i])){
				$RowKeys = array_keys($datasource[$i]);
				$output .= "[";
				for($j=0; $j< count($RowKeys); $j++)
					$output .= "'" . $datasource[$i][$RowKeys[$j]] . "',";
				$output = substr($output,0,strlen($output)-1) . "],";	
			}
			else
				$output .= "'" . $datasource[$i] . "',";	
		}
		$output = substr($output,0,strlen($output)-1) . "]";
		return $output;
	}
	
	static function PHPArray_to_JSObject($datasource)
	{
		if(count($datasource) == 0)
			return "{}";
			
		$output = "";
		foreach ($datasource as $key => $value)
		{
			if($output == "")
				$output = !is_numeric($key) ? "{" : "[";
				
			$output .= !is_numeric($key) ? $key . ":" : "";
			if(is_array($value))
				$output .= self::PHPArray_to_JSObject($value);
			else
				$output .= "'" . $value . "'";
			$output .= ",";
		}
		$output = substr($output,0,strlen($output)-1) . (!is_numeric($key) ? "}" : "]");
		return $output;
	}
	static function PHPObject_to_JSObject($obj)
	{
		if(!$obj)
			return "{}";
			
		$output = "";
		$obj = (array) $obj;
		foreach ($obj as $key => $value)
		{
			if(is_array($value) || $value === null)
				continue;
			if($output == "")
				$output = !is_numeric($key) ? "{" : "[";
				
			$output .= !is_numeric($key) ? $key . ":" : "";
			$output .= "'" . $value . "'";
			$output .= ",";
		}
		$output = substr($output,0,strlen($output)-1) . (!is_numeric($key) ? "}" : "]");
		return $output;
	}

}

define('NUMBER_DECIMAL_POINT',	 '.');
define('NUMBER_THOUSANDS_POINT', ',');
define('NUMBER_NEGATIVE_VIEW',	 'N-'); // N- or (N)


class String{
	
	static function lastIndexOf($haystack , $needle){
		
		$str = strrev($haystack);
		$index = strpos($str, $needle);
		if($index === false)
			return false;
		
		return strlen($haystack) - $index - 1;
	}
	
	static function ellipsis($value, $length, $word = true) {
		
		if ($value && strlen($value) > $length)
		{
			if ($word) {
				$vs = substr($value, 0, $length - 2);
				$index = max(array(	self::lastIndexOf($vs, " "), 
									self::lastIndexOf($vs, "."), 
									self::lastIndexOf($vs, "?"), 
									self::lastIndexOf($vs, "!")));
				if ($index !== false)
					return substr($value, 0, $index) . "...";
				else
					return substr($value, 0, $length - 3) . "...";
			}
			return substr($value, 0, $length - 3) . "...";
		}
		return $value;
	}
}

?>