<?php
/**
 * General Classes 
 * @author S.Mahdizadeh
 * Date : 1389-10-15
 */
class DataMember{
	
	const DT_INT = 1;
	const DT_FLOAT = 2;
	const DT_FA_EN_ALPHA = 3;
	const DT_FA_EN_NUM = 4;
	const DT_FA_EN_ALPHANUM = 5;
	const DT_FA_ALPHA = 6;
	const DT_FA_NUM = 7;
	const DT_FA_ALPHANUM = 8;
	const DT_EN_ALPHA = 9;
	const DT_EN_ALPHANUM = 10;
	const DT_EMAIL = 11;
	const DT_DATETIME =12;
	const DT_DATE = 14;
	const DT_NATIONAL_ID =15;
	const DT_TEL = 16;
	const DT_TIME = 17;
	
	public static function CreateDMA($DataType, $defaultValue = null, $NotNull = false)
	{
		return array("DataType" => $DataType,
					 "NotNull" => $NotNull,
					 "defaultValue" => $defaultValue);
	}

	/**
	 * @param  DMA $DMA produced by DataMember::CreateDMA()
	 * @param  $val value 
	 * @return boolean (true/false)
	 */
	static function IsValid($DMA , $val)
	{
		if(!is_array($DMA))
			return false;
		if((!isset($val) || $val == "") && $DMA["NotNull"])
			return false;

		switch ($DMA["DataType"])
		{
			case self::DT_INT : 
				return is_int((int)$val);
			
			case self::DT_FLOAT :
				return is_float($val);				
		}

		return true;
	}

	static function GetDefaultValue($DMA)
	{
		if(is_array($DMA))
			return $DMA["defaultValue"];
	}

	static function GetNotNullValue($DMA)
	{
		return $DMA["NotNull"] == "1" || $DMA["NotNull"] == true ? true : false;
	}

}
?>