<?php

class dataReader
{
	static function getJsonData($dataSource, $countOfRows, $callback)
	{
		if($dataSource instanceof ADORecordSet)
			$dataSource = $dataSource->GetRows();
/*
		$dataSource2 = array_slice($dataSource, $start, $limit);

		$str = $callback . '({"totalCount":"' . count($dataSource) . '","' .
				'rows":' . json_encode($dataSource2) . '});';

		return $str;*/
			
		if($countOfRows == "")
			$countOfRows = count($dataSource);
			
		$str = $callback . '({"totalCount":"' . $countOfRows . '","' .
				'rows":' . json_encode($dataSource) . '});';

		return $str;
	}

	static function makeOrder()
	{
		$arr = json_decode($_REQUEST["sort"]);
		$str = " order by ";
		for($i=0; $i < count($arr); $i++)
			$str .= $arr[$i]["property"] . " " . $arr[$i]["direction"] . ",";
		$str = str_split($str, strlen($str)-1);
		return $str;
	}

	/**
	 * return "new Ext.data.Store(..." for Store in Ext
	 *
	 * @param string $url example: testPage.php?task=readData
	 * @param string $fields example: 'id','name','count'
	 */
	static function MakeStoreObject($url,$fields)
	{
		echo "new Ext.data.Store({
		        proxy: new Ext.data.ScriptTagProxy({
		            url: '" . $url . "'
		        }),       
		        reader: new Ext.data.JsonReader({
		            root: 'rows',
		            totalProperty: 'totalCount'}, 
		            [" . $fields . "])
		    	});";
	}
	
	static function MakeStoreObject_Data($data,$fields)
	{
		if($data instanceof ADORecordSet)
			$data = $data->GetRows();
			
		if(count($data) == 0)
			$varData = "";
		else
		{
			$keys = array_keys($data[0]);
			$fields2 = ',' .$fields .',';
			$varData = "";
			for($i=0; $i<count($data); $i++)
			{
				$varData .= "[";
	
				for($j=0; $j<count($keys); $j++)
					if(strpos($fields2,',\''.$keys[$j].'\',')!== false)
						$varData .= "'".$data[$i][$keys[$j]]."',";
	
				$varData = substr($varData, 0, strlen($varData)-1);
				$varData = $varData . "],"; 
			}
			$varData = substr($varData, 0, strlen($varData)-1);
		}		
		echo "new Ext.data.SimpleStore({
		    	fields : [" . $fields . "],
		    	data : [" . $varData . "]
		    });";
	}
	
}

?>