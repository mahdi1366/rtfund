<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.06
//---------------------------

class reportGenerator_columns extends PdoDataAccess
{
	public $column_id;
	public $parent_id;
	public $col_name;
	public $field_name;
	public $basic_type_id;
	public $table_id;
	public $search_mode;
	public $basic_info_table;
	public $check_value;
	public $check_text;
	public $renderer;

	static function GetAll($where = "")
	{
		$query = "SELECT r1.*, r2.col_name as parent_name, t.table_name
				FROM rpt_columns r1 left join rpt_columns r2 on(r1.parent_id = r2.column_id AND r2.table_id=0)
					left join rpt_tables t on(r1.table_id=t.table_id)

		";
		$query .= ($where != "") ? " where " . $where : "";

		return parent::runquery($query);
	}

	function Add()
	{
		return parent::insert("rpt_columns", $this);
	}

	function Edit()
	{
		return parent::update("rpt_columns", $this, "column_id=" . $this->column_id);
	}

}
?>
