<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	96.09
//-------------------------

class TreeModulesclass
{
	/**
	 * 
	 * @param array $dataTable : this array should have at least id,parentid,text and id should be unique
	 */
	static function MakeHierarchyArray($dataTable)
	{
		$nodes = array();
		$refArr = array();
		for($i=0; $i<count($dataTable); $i++)
		{
			$node = $dataTable[$i];
			$node["leaf"] = "true";
			$node["level"] = 1;
			if($node["parentid"] == "0")
			{
				$nodes[] = $node;
				$refArr[$node['id']] = & $nodes[ count($nodes)-1 ];				
			}
			else
			{
				$parent = & $refArr[ $node["parentid"] ];
				if (!isset($parent["children"])) {
					$parent["children"] = array();
					$parent["leaf"] = "false";
				}
				
				$node["level"] = $parent["level"]*1+1;
				$parent["children"][] = $node;
				$refArr[$node["id"]] = & $parent["children"][ count($parent["children"])-1 ];
			}
		}
		return $nodes;
	}
}
?>
