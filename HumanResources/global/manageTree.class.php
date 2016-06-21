<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.03
//---------------------------

class manage_tree extends PdoDataAccess
{
	public $node_id_field;
	public $node_title_field;
	public $parent_node_id_field;
	public $table_name;
	

	function get_parent_list($node_id, $with_own=true)
	{
		$query = " SELECT * FROM " . $this->table_name . " WHERE " . $this->node_id_field . "=" . $node_id;
		$temp = parent::runquery($query);
				
		if(count($temp) == 0)
			return array();
	 	$result = $temp[0];
	   	$nodetitle = $result[$this->node_title_field];
		
		$path = ($result['parent_path'] == "" || $result['parent_path'] == NULL || $result['parent_path'] == 0  )  ? "" : $result['parent_path'] . ",";
		if($with_own)
			$path .= $node_id;
		else if($path != "")
			$path = substr($path, 0, strlen($path)-1);

		$parent_node_id = $result[$this->parent_node_id_field];
		$nodes = array();
		
		if(trim($path) != '' && strlen($path) > 0)
		{
			$query = "SELECT * FROM $this->table_name 
							WHERE $this->node_id_field IN (" . $path . ") 
								ORDER BY FIND_IN_SET($this->node_id_field , '" . $path . "')";
		
			$temp =  parent::runquery($query);

			$path_arr = preg_split('/,/', $path);
			$path_arr = array_flip($path_arr);

			for($i=0 ; $i< count($temp); $i++)
			{
				$result = $temp[$i];
				
				//echo $temp[$i][$this->node_id_field]."*****<br>" ; 
				
				$nodes[$path_arr[$temp[$i][$this->node_id_field]]]= array(
					$this->node_id_field => $result[$this->node_id_field],
					$this->parent_node_id_field => $result[$this->parent_node_id_field],
					$this->node_title_field => $result[$this->node_title_field]);
			}
		}
                 /*if($_SESSION['UserID'] == 'jafarkhani')
                                {
					$dt = current($nodes);
					
					 echo $dt['parent_ouid']."ererer" ; die();
                                   print_r($nodes);echo  "vwwwahed" ; die();
                                    
                                }*/
		return $nodes;
	}

	function get_node_path_title($node_id, $with_own=true, $separator=' - ')
	{
		
		
		$nodes = $this->get_parent_list($node_id, $with_own);

		$sep = '';
		$addrs = '' ;
		
				
		foreach($nodes as $node)
		{
			$addrs .= $sep . $node[$this->node_title_field];
			$sep = $separator;
		}
		
		return $addrs;
	}

}
?>
