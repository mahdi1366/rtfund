<?php
//---------------------------
// programmer:	jafarkhani
// create Date:	90.04
//---------------------------

class rp_reports extends PdoDataAccess
{
	public $report_id;
	public $report_title;
	public $query;
	public $conditions;
	public $refer_page;
	
	function __construct($report_id = "")
	{
		if($report_id != "")
		{
			$dt = rp_reports::select("report_id=" . $report_id);

			if(count($dt) != 0)
			{
				$this->report_id = $dt[0]["report_id"];
				$this->report_title = $dt[0]["report_title"];
				$this->query = $dt[0]["query"];
				$this->conditions = $dt[0]["conditions"];
				$this->refer_page = $dt[0]["refer_page"];
			}
		}
		else
		{
			$this->report_id = "";
			$this->report_title = "";
			$this->query = "";
			$this->conditions = "";
			$this->refer_page = "";
		}
	}

	public static function select($where = "")
	{
		$query = "SELECT * FROM rp_reports";
		$query .= ($where != "") ? " where " . $where : "";

		return parent::runquery($query);
	}

	public function Add()
	{
		return parent::insert("rp_reports", $this);
	}

	public function Edit()
	{
		return parent::update("rp_reports", $this, "report_id=:rptid", array(":rptid" => $this->report_id));
	}

	public static function remove($report_id)
	{
		if(parent::delete("rp_report_columns", "report_id=?", array($report_id)) === false )
			return false;
		if( parent::delete("rp_reports", "report_id=?", array($report_id)) === false )
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $report_id;
		$daObj->TableName = "rp_reports,rp_report_columns";
		$daObj->execute();

		return true;
	}

	public static function LastID()
	{
		return parent::GetLastID("rp_reports", "report_id");
	}

	private static function RecursiveMakeNode($row)
	{
		$returnVal = '"id":"' . $row["ColumnID"] . '",
					"text":"' . $row["colName"] . '",
					"fieldName":"' . $row["fieldName"] . '",';

		$dt = parent::runquery("select * from rp_columns where parentID=" . $row["ColumnID"]);
		if(count($dt) != 0)
		{
			$returnVal .= '"children":[';
			for($j=0; $j < count($dt); $j++)
			{
				$returnVal .= "{" . ReportGenerator::RecursiveMakeNode($dt[$j]) . "},";
			}
			$returnVal = substr($returnVal,0,strlen($returnVal)-1) . "]";
		}
		else
		{
			$returnVal .= '"leaf":true';
		}
		return $returnVal;
	}

	private static function RemoveNumbers($value)
	{
		for($i=0; $i < strlen($value); $i++)
		{
			if(!is_nan($value[$i]))
			{
				str_replace($value[$i],"",$value);
				$i--;
			}
		}
		return $value;

	}
}

class rp_report_columns extends PdoDataAccess
{
	public $row_id;
	public $report_id;
	public $parent_path;
	public $column_id;
	public $used_type;
	public $summary_type;
	public $field;
	public $field_title;
	public $base_evaluate;

	public function Add()
	{
		return parent::insert("rp_report_columns", $this);
	}

	public function Edit()
	{
		return parent::update("rp_report_columns", $this, "report_id=:rptid AND row_id=:row", 
			array(":rptid" => $this->report_id, ":row" => $this->row_id));
	}

	public static function remove($row_id)
	{
		if(parent::delete("rp_report_columns", "row_id=?", array($row_id)) === false )
			return false;

		return true;
	}
}

?>
