<?php

class dataReader
{
	static function getJsonData($dataSource, $countOfRows = "", $callback = "", $message = "")
	{
		//header("content-type: application/javascript");
		
		if($callback == "" && isset($_GET["callback"]))
			$callback = $_GET["callback"];
		
		if($dataSource instanceof ADORecordSet)
			$dataSource = $dataSource->GetRows();
			
		if($countOfRows == "")
			$countOfRows = count($dataSource);
		
		if(!preg_match('/^([A-Za-z0-9]|[\.])*$/i', $callback))
			return "";		
		
		$str = $callback . '({"totalCount":"' . (int)$countOfRows . '","' .
				'rows":' . json_encode($dataSource);
		
		if($message !== "")
		{
			if(is_array($message))
				$str .= ',"message":' . json_encode ($message);
			else
				$str .= ',"message":"' . $message . '"';
		}
			
		$str .= '});';

		return $str;
	}

	static function makeOrder()
	{
		if(!isset($_REQUEST["sort"]))
			return "";
		$arr = json_decode(stripslashes($_REQUEST["sort"]));
		
		$str = " order by ";
		for($i=0; $i < count($arr); $i++)
		{
			if(!preg_match('/^([A-Za-z0-9]|[\._])*$/i', $arr[$i]->property))
				return "";
			if(strtolower($arr[$i]->direction) != "asc" && strtolower($arr[$i]->direction) != "desc")
				return "";
			
			if($arr[$i]->property != "")
				$str .= $arr[$i]->property . " " . $arr[$i]->direction . ",";
		}
		$str = substr($str, 0, strlen($str)-1);
		
		return $str == " order by" ? "" : $str;
	}

	/**
	 * return "new Ext.data.Store(..." for Store in Ext
	 *
	 * @param string $url example: testPage.php?task=readData
	 * @param string $fields example: 'id','name','count'
	 */
	static function OLDMakeStoreObject($url,$fields)
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
	
	/**
	 * return "new Ext.data.Store(..." for Store in Ext
	 *
	 * @param string $url example: testPage.php?task=readData
	 * @param string $fields example: 'id','name','count'
	 */
	static function MakeStoreObject($url,$fields)
	{
		echo "new Ext.data.Store({
				pageSize: 10,
				model:  Ext.define(Ext.id(), {
					extend: 'Ext.data.Model',
					fields:[" . $fields . "]
				}),
				remoteSort: true,
				proxy:{
					type: 'jsonp',
					url: '" . $url . "',
					reader: {
						root: 'rows',
						totalProperty: 'totalCount'
					}
				}
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