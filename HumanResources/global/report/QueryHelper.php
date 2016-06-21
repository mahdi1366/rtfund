<?php
//---------------------------
// programmer:	jafarkhani
// create Date:	89.11
//---------------------------

class QueryHelper
{
	/**
	 * make where statement of a list of checkboxes
	 * example return string : " $fieldName in(val1, val2, '')"
	 */
	static function makeWhereOfCheckboxList($fieldName, $prefixName)
	{
		$items = "";
		$issetCheck = false;
		
		$postKeys = array_keys($_POST);
		for($i=0; $i< count($_POST); $i++)
		{
			if(strpos($postKeys[$i],$prefixName) !== false)
			{
				$issetCheck = true;
				$id = substr($postKeys[$i], strlen($prefixName));
				$items .= $id . ",";
			}
		}
		$items .= " ''";

		return !$issetCheck ? "" : $fieldName . " in(" . $items . ")";
	}

	static function makeBasicInfoJoin($BasicTypeID, $alias, $field)
	{
		return "left join Basic_Info $alias
			on($alias.TypeID=" . $BasicTypeID . " AND $field=$alias.InfoID)";
	}

	static function MK_org_units($ouid, $with_sub_ouids = true, $prefix='o')
	{
				
		$return = array("where"=> "", "param" => array());

		if($ouid != -1 && $ouid != "")
		{
			if($with_sub_ouids)
			{
				$return["where"] = " (".$prefix.".parent_path like :ouid2 OR
							 ".$prefix.".parent_path like :ouid3 OR
							 ".$prefix.".ouid=:ouid OR
							 ".$prefix.".parent_ouid = :ouid)";

				$return["param"][":ouid"] = $ouid;
				$return["param"][":ouid2"] = "%," . $ouid . ",%";
				$return["param"][":ouid3"] = "%" . $ouid . ",%";
			}
			else
			{
				$return["where"] = " ".$prefix.".ouid = :ouid";
				$return["param"][":ouid"] = $ouid;
			}
		}

		return $return;
	}
}
?>
