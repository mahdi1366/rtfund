<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 97.02
//---------------------------

class NTC_alarms extends OperationClass
{
	const TableName = "NTC_alarms";
	const TableKey = "AlarmID";
	
	public $AlarmID;
	public $AlarmTitle;
	public $days;
	public $compute;
	public $ObjectID;
	public $ReceiverField;
	public $context;
	public $SendType;
	public $GroupLetter;
}

?>
