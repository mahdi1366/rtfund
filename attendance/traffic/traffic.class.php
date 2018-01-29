<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 95.02
//---------------------------

class ATN_traffic extends OperationClass
{
	const TableName = "ATN_traffic";
	const TableKey = "TrafficID";
	
	public $TrafficID;
	public $PersonID;
	public $TrafficDate;
	public $TrafficTime;
	public $IsSystemic; 
	public $IsActive;
	public $RequestID;
	
	static function Get($where = '', $whereParams = array()) {
		
		$query = "select t.*,s.ShiftTitle , s.FromTime,s.ToTime
			
			from ATN_traffic t
			left join ATN_PersonShifts ps on(ps.IsActive='YES' AND t.PersonID=ps.PersonID AND TrafficDate between FromDate AND ToDate)
			left join ATN_shifts s on(ps.ShiftID=s.ShiftID)
			where 1=1 " . $where;
		
		return parent::runquery_fetchMode($query, $whereParams);		
	}
	
	static function Compute($StartDate, $EndDate, $PersonID, $admin=true, &$returnStr=""){
		
		require_once getenv("DOCUMENT_ROOT") . '/attendance/baseinfo/shift.class.php';
		
		$StartDateParam = $StartDate;
		$holidays = ATN_holidays::Get(" AND TheDate between ? AND ? order by TheDate", 
				array($StartDate, $EndDate));
		$holidayRecord = $holidays->fetch();
		
		$rules = ATN_settings::Get(" AND :d >= StartDate AND :d <= EndDate", 
				array(":d" => $StartDate));
		if($rules->rowCount() == 0)
		{
			ExceptionHandler::PushException("قوانین حضور و غیاب در بازه زمانی فوق تعریف نشده است");
			return false;
		}
		$rule = $rules->fetch();

		$query = "select * from (

				select '' ReqType, TrafficDate,concat(substr(TrafficTime,1,5),':00') TrafficTime,
					s.ShiftTitle,s.FromTime,s.ToTime,ExceptFromTime,ExceptToTime,'' EndTime
				from ATN_traffic t
				left join ATN_PersonShifts ps on(ps.IsActive='YES' AND t.PersonID=ps.PersonID AND 
					TrafficDate between FromDate AND ToDate)
				left join ATN_requests r on(ReqType='CHANGE_SHIFT' and ReqStatus=".ATN_STEPID_CONFIRM." and r.FromDate=TrafficDate
				  and t.PersonID=r.PersonID)
				left join ATN_shifts s on(ifnull(r.ShiftID,ps.ShiftID)=s.ShiftID)
				where t.IsActive='YES' AND t.PersonID=:p AND TrafficDate>= :sd AND TrafficDate <= :ed 

				union All

				select tr.ReqType, tr.FromDate,tr.StartTime,s.ShiftTitle,s.FromTime,s.ToTime
					,ExceptFromTime,ExceptToTime, tr.EndTime
				from ATN_requests tr
				left join ATN_PersonShifts ps on(ps.IsActive='YES' AND tr.PersonID=ps.PersonID 
					AND tr.FromDate between ps.FromDate AND ps.ToDate)
				left join ATN_requests r on(r.ReqType='CHANGE_SHIFT' and r.ReqStatus=".ATN_STEPID_CONFIRM." and r.FromDate=tr.FromDate
				  and tr.PersonID=r.PersonID)
				left join ATN_shifts s on(ifnull(r.ShiftID,ps.ShiftID)=s.ShiftID)
				where tr.PersonID=:p AND tr.ToDate is null AND tr.ReqType not in('EXTRA' ,'CHANGE_SHIFT')
					AND tr.ReqStatus=".ATN_STEPID_CONFIRM." AND tr.FromDate>= :sd and tr.FromDate <= :ed 

			)t2
			order by  TrafficDate,TrafficTime,ReqType";
		$dt = PdoDataAccess::runquery($query, array(":p" => $PersonID, ":sd" => $StartDate, ":ed" => $EndDate));
		//print_r(ExceptionHandler::PopAllExceptions());
		if($_SESSION["USER"]["UserName"] = "admin")
		{
			//echo PdoDataAccess::GetLatestQueryString();die();
		}
		//............ Reset wrong hourly off and mission requests .............
		$currentDate = $dt[0]["TrafficDate"];
		$index = 0;
		for($i=0; $i<count($dt); $i++)
		{
			
			if($dt[$i]["ReqType"] == "" || $dt[$i]["ReqType"] == "CORRECT" )
			{
				if($currentDate == $dt[$i]["TrafficDate"])
					$index++;
				else
					$index = 1;
			}				
				
			if($dt[$i]["ReqType"] == "OFF" || $dt[$i]["ReqType"] == "MISSION")
			{
				if($index == 1 && $i+1 < count($dt) && $dt[$i+1]["TrafficDate"] == $currentDate && 
						$dt[$i]["TrafficTime"] < $dt[$i+1]["TrafficTime"])
				{
					$dt[$i]["TrafficTime"] = $dt[$i+1]["TrafficTime"];
					$temp = $dt[$i+1];
					$dt[$i+1] = $dt[$i];
					$dt[$i] = $temp;
				}
			} 
			$currentDate = $dt[$i]["TrafficDate"];
		}		
		//........................ create days array ..................

		$index = 0;
		$returnArr = array();
		while($StartDate <= $EndDate)
		{
			if($index < count($dt) && $StartDate == $dt[$index]["TrafficDate"])
			{
				while($index < count($dt) && $StartDate == $dt[$index]["TrafficDate"])
					$returnArr[] = $dt[$index++];

				$StartDate = DateModules::AddToGDate($StartDate, 1);	
				continue;
			}

			$shiftRecord = ATN_PersonShifts::GetShiftOfDate($PersonID, $StartDate);

			$returnArr[] = array(
				"TrafficID" => "", 
				"ReqType" => "",
				"TrafficDate" => $StartDate , 
				"ShiftTitle" => $shiftRecord["ShiftTitle"], 
				"FromTime" => $shiftRecord["FromTime"], 
				"ToTime" => $shiftRecord["ToTime"], 
				"ExceptFromTime" => $shiftRecord["ExceptFromTime"], 
				"ExceptToTime" => $shiftRecord["ExceptToTime"], 
				"TrafficTime" => "");
			$StartDate = DateModules::AddToGDate($StartDate, 1);
		}
		//------------ holidays ------------------
		for($i=0; $i<count($returnArr); $i++)
		{
			$holiday = false;
			$holidayTitle = "تعطیل";
			if(FridayIsHoliday && DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") == "5")
				$holiday = true;
			if(ThursdayIsHoliday && DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") == "4")
				$holiday = true;

			if($holidayRecord && $holidayRecord["TheDate"] == $returnArr[$i]["TrafficDate"])
			{
				$holidayTitle .= $holidayRecord["details"] != "" ? "(" . $holidayRecord["details"] . ")" : "";
				$holiday = true;
				$holidayRecord = $i+1 <count($returnArr) && 
					$returnArr[$i]["TrafficDate"] == $returnArr[$i+1]["TrafficDate"] 
					? $holidayRecord : $holidays->fetch();
			}

			$returnArr[$i]["holiday"] = $holiday;
			$returnArr[$i]["holidayTitle"] = $holidayTitle;
		}
		//...........................................................
		$SUM = array(
			"PersonID" => $PersonID,
			"StartDate" => $StartDateParam,
			"EndDate" => $EndDate,
			"absence" => 0,
			"attend"=> 0,
			"firstAbsence" => 0,
			"lastAbsence" => 0,
			"extra" => 0,
			"Off" => 0,
			"mission" => 0,
			"DailyOff_1" => 0,
			"DailyOff_2" => 0,
			"DailyOff_3" => 0,
			"DailyMission" => 0,
			"DailyAbsence" => 0,
			"LegalExtra" => 0,
			"AllowedExtra" => 0
		);

		for($i=0; $i < count($returnArr); $i++)
		{
			if(!$returnArr[$i]["holiday"])
			{
				//........... Daily off and mission ...................
				$requests = PdoDataAccess::runquery("
					select t.*, InfoDesc OffTypeDesc from ATN_requests t
						left join BaseInfo on(TypeID=20 AND InfoID=OffType)
					where ReqType in('DayOFF','DayMISSION') AND ReqStatus=".ATN_STEPID_CONFIRM." AND 
						PersonID=:p AND FromDate <= :td 
						AND if(ToDate is not null, ToDate >= :td, 1=1)
					order by ToDate desc,StartTime asc
				", array(
					":p" => $PersonID,
					":td" => $returnArr[$i]["TrafficDate"]
				));

				if(count($requests) > 0)
				{
					if($requests[0]["ReqType"] == "DayOFF")
					{
						$returnStr .= 
							"<td>" . DateModules::$JWeekDays[ DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") ] . "</td>
							<td>" . DateModules::miladi_to_shamsi($returnArr[$i]["TrafficDate"]) . "</td>
							<td colspan=8> مرخصی " . $requests[0]["OffTypeDesc"] . "<td></tr>";
						$SUM["DailyOff_" . $requests[0]["OffType"] ]++;

						$currentDay = $returnArr[$i]["TrafficDate"];
						while($i < count($returnArr) && $currentDay == $returnArr[$i]["TrafficDate"])
							$i++;
						$i--;
						continue;
					}
					if($requests[0]["ReqType"] == "DayMISSION")
					{
						$returnStr .= 
							"<td>" . DateModules::$JWeekDays[ DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") ] . "</td>
							<td>" . DateModules::miladi_to_shamsi($returnArr[$i]["TrafficDate"]) . "</td>
							<td colspan=8> ماموریت " . $requests[0]["MissionSubject"] . "<td></tr>";
						$SUM["DailyMission"]++;

						$currentDay = $returnArr[$i]["TrafficDate"];
						while($i < count($returnArr) && $currentDay == $returnArr[$i]["TrafficDate"])
							$i++;
						$i--;
						continue;
					}
				}
			}
			//....................................................
			if( DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "l") == "Thursday")
			{
				$returnArr[$i]["FromTime"] = $returnArr[$i]["ExceptFromTime"];
				$returnArr[$i]["ToTime"] = $returnArr[$i]["ExceptToTime"];
			}
			//....................................................

			$returnStr .= "<tr>
				<td>" . DateModules::$JWeekDays[ DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "N") ] . "</td>";

			$returnStr .= "<td><a class=link onclick=TraceTrafficObj.TrafficList('" . 
				$returnArr[$i]["TrafficDate"] . "')>" . 
				DateModules::miladi_to_shamsi($returnArr[$i]["TrafficDate"]) . "</a></td>";

			$returnStr .= "<td>" . ($returnArr[$i]["holiday"] ? $returnArr[$i]["holidayTitle"] : $returnArr[$i]["ShiftTitle"]) . "</td>
				<td>";

			$firstAbsence = 0;
			$Off = 0;	
			$mission = 0;
			$index = 1;
			$totalAttend = 0;
			$currentDay = $returnArr[$i]["TrafficDate"];
			$startOff = 0;
			$endOff = 0;
			$extra = 0;
			$durAbsense = 0;

			if($returnArr[$i]["TrafficTime"] != "")
			{
				if(strtotime($returnArr[$i]["TrafficTime"]) > strtotime($returnArr[$i]["FromTime"]))
					$firstAbsence = strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i]["FromTime"]);
				else
				{
					if(strtotime($returnArr[$i+1]["TrafficTime"]) < strtotime($returnArr[$i]["FromTime"]))
						$extra += strtotime($returnArr[$i+1]["TrafficTime"]) - strtotime($returnArr[$i]["TrafficTime"]);
					else
						$extra += strtotime($returnArr[$i]["FromTime"]) - strtotime($returnArr[$i]["TrafficTime"]);
				}
			}

			while($i < count($returnArr) && $currentDay == $returnArr[$i]["TrafficDate"])
			{
				//....................................................
				if( DateModules::GetWeekDay($returnArr[$i]["TrafficDate"], "l") == "Thursday")
				{
					$returnArr[$i]["FromTime"] = $returnArr[$i]["ExceptFromTime"];
					$returnArr[$i]["ToTime"] = $returnArr[$i]["ExceptToTime"];
				}
				//....................................................
				if($returnArr[$i]["ReqType"] == "OFF" || $returnArr[$i]["ReqType"] == "MISSION")
				{
					$ReqDesc = $returnArr[$i]["ReqType"] == "OFF" ? "مرخصی ساعتی" : "ماموریت ساعتی";
					$returnStr .= "<span data-qtip='$ReqDesc' style=color:red>" . 
						substr($returnArr[$i]["TrafficTime"],0,5) .
						" - " . substr($returnArr[$i]["EndTime"],0,5) . "</span>";
					$index++;
					//-------------------------------------
					$startOff = strtotime($returnArr[$i]["TrafficTime"]);
					$endOff = strtotime($returnArr[$i]["EndTime"]);
					
					if($returnArr[$i]["ReqType"] == "OFF")
						$Off += $endOff - $startOff;
					else
						$mission += $endOff - $startOff;
				}
				else 
				{
					$returnStr .= substr($returnArr[$i]["TrafficTime"],0,5);
					
					if($index % 2 == 0)
					{
						$totalAttend += strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i-1]["TrafficTime"]);

						if(strtotime($returnArr[$i]["TrafficTime"]) > strtotime($returnArr[$i]["ToTime"]))
						{
							if(strtotime($returnArr[$i-1]["TrafficTime"]) > strtotime($returnArr[$i]["ToTime"]))
								$extra += strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i-1]["TrafficTime"]);
							else
								$extra += strtotime($returnArr[$i]["TrafficTime"]) - strtotime($returnArr[$i-1]["ToTime"]);
						}
					}	
					else
					{
						if($i>0 && $returnArr[$i]["TrafficDate"] == $returnArr[$i-1]["TrafficDate"]
							&& $returnArr[$i]["TrafficTime"] > $returnArr[$i-1]["TrafficTime"]
							&& $returnArr[$i]["TrafficTime"] < $returnArr[$i]["ToTime"]
							&& $returnArr[$i-1]["TrafficTime"] < $returnArr[$i]["ToTime"])
						{
							if($returnArr[$i-1]["ReqType"] != "OFF" && $returnArr[$i-1]["ReqType"] != "MISSION")
								$durAbsense += strtotime($returnArr[$i]["TrafficTime"]) - 
									strtotime($returnArr[$i-1]["TrafficTime"]);
						}
					}
				}
				$returnStr .= $index % 2 == 0 ? "<br>" : " - ";
				$index++;
				$i++;
			}
			$i--;

			$lastAbsence = 0;
			if($returnArr[$i]["TrafficTime"] != "")
			{
				if($returnArr[$i]["EndTime"] != "")
				{
					if(strtotime($returnArr[$i]["EndTime"]) < strtotime($returnArr[$i]["ToTime"]))
						$lastAbsence = strtotime($returnArr[$i]["ToTime"]) - strtotime($returnArr[$i]["EndTime"]);
				}
				else if(strtotime($returnArr[$i]["TrafficTime"]) < strtotime($returnArr[$i]["ToTime"]))
					$lastAbsence = strtotime($returnArr[$i]["ToTime"]) - strtotime($returnArr[$i]["TrafficTime"]);
			}
			$ShiftDuration = strtotime($returnArr[$i]["ToTime"]) - strtotime($returnArr[$i]["FromTime"]);
			//$extra = ($totalAttend > $ShiftDuration) ? $totalAttend - $ShiftDuration  : 0;

			$Absence = ($totalAttend + $Off + $mission) < $ShiftDuration ? 
					$ShiftDuration + $extra - $totalAttend - $Off - $mission + $durAbsense : $durAbsense;
			$extra = $extra < 0 ? 0 : $extra;
			if($returnArr[$i]["holiday"])
			{
				$extra = $totalAttend;
				$lastAbsence = 0;
				$firstAbsence = 0;
				$Absence = 0;
				$Off = 0;
			}
			else
			{
				if($Absence == $ShiftDuration)
					$SUM["DailyAbsence"]++;

				//------------------- telorance -------------------------
				if($rule["telorance"]*1 > 0)
				{
					$teloranceSec = $rule["telorance"]*60;
					if($firstAbsence > 0 && $firstAbsence <= $teloranceSec)
					{
						if($extra > 0)
						{
							$min = min($firstAbsence,$extra);
							$extra -= $min;
							$firstAbsence -= $min;
						}
						if($lastAbsence > 0)
						{
							$min = min($firstAbsence,$lastAbsence);
							$lastAbsence -= $min;
							$firstAbsence -= $min;
						}
						//if($firstAbsence > 0)
						//	$lastAbsence = $firstAbsence;
						//$firstAbsence = 0;
					}
				}
				//--------------------------------------------------------
			}
		
			$SUM["absence"] += $Absence;
			$SUM["attend"] += $totalAttend;
			$SUM["firstAbsence"] += $firstAbsence;
			$SUM["lastAbsence"] += $lastAbsence;
			$SUM["extra"] += $extra;
			$SUM["Off"] += $Off;
			$SUM["mission"] += $mission;
			
			//----------------- extra computations ------------------
			$SUM["LegalExtra"] += min($extra, $rule["MaxDayExtra"]*3600);
			
			$requests = PdoDataAccess::runquery("
				select t.* from ATN_requests t
				where ReqType='EXTRA' AND ReqStatus=".ATN_STEPID_CONFIRM." AND PersonID=:p AND FromDate = :d 						
			", array(
				":p" => $PersonID,
				":d" => $returnArr[$i]["TrafficDate"]
			));
			if(count($requests) > 0)
				$SUM["AllowedExtra"] += $requests[0]["ConfirmExtra"];
			else
				$SUM["AllowedExtra"] += min($extra, $rule["MaxDayExtra"]*3600);
			//-------------------------------------------------------
			
			$totalAttend = TimeModules::SecondsToTime($totalAttend);
			$firstAbsence = TimeModules::SecondsToTime($firstAbsence);
			$lastAbsence = TimeModules::SecondsToTime($lastAbsence);
			$Absence = TimeModules::SecondsToTime($Absence);
			$extra = TimeModules::SecondsToTime($extra);
			$Off = TimeModules::SecondsToTime($Off);
			$mission = TimeModules::SecondsToTime($mission);	
		
			$link = "<a href='javascript:void(0)' ".
					"onclick=\"TraceTrafficObj.CreateRequest('".$returnArr[$i]["TrafficDate"];
			
			$returnStr .= "</td><td class=attend>" . TimeModules::ShowTime($totalAttend) . "</td>
				<td class=extra>" . TimeModules::ShowTime($extra) . "</td>
				<td class=off>" . TimeModules::ShowTime($Off) . "</td>
				<td class=mission>" . TimeModules::ShowTime($mission) . "</td>
				<td class=sub>" . $link . "','firstAbsence')\" >" . TimeModules::ShowTime($firstAbsence) . "</a></td>
				<td class=sub>" . $link . "','lastAbsence')\" >" . TimeModules::ShowTime($lastAbsence) . "</a></td>
				<td class=sub>" . $link . "','Absence')\" >" . TimeModules::ShowTime($Absence) . "</a></td>
				</tr>";
		}
		
		return $SUM;
	}
	
}

class ATN_requests extends OperationClass
{
	const TableName = "ATN_requests";
	const TableKey = "RequestID";
	
	public $RequestID;
	public $PersonID;
	public $ReqDate;
	public $FromDate;
	public $ToDate;
	public $StartTime;
	public $EndTime;
	public $ReqType;
	public $ReqStatus;
	public $details;
	public $RealExtra;
	public $LegalExtra;
	public $ConfirmExtra;
	public $ShiftID;
	
	public $MissionPlace;
	public $MissionSubject;
	public $MissionStay;
	public $GoMean;
	public $ReturnMean;
	public $OffType;
	public $OffPersonID;
	
	public $SurveyPersonID;
	public $SurveyDate;
	public $SurveyDesc;
	
	public $IsArchive;
	
	public $_fullname;
	public $_GoMeanDesc;
	public $_ReturnMeanDesc;
	
	function __construct($id = '') {
		
		$this->DT_ReqDate = DataMember::CreateDMA(InputValidation::Pattern_DateTime);
		$this->DT_FromDate = DataMember::CreateDMA(InputValidation::Pattern_Date);
		$this->DT_ToDate = DataMember::CreateDMA(InputValidation::Pattern_Date);
		$this->DT_SurveyDate = DataMember::CreateDMA(InputValidation::Pattern_DateTime);
		
		$this->DT_StartTime = DataMember::CreateDMA(InputValidation::Pattern_Time);
		$this->DT_EndTime = DataMember::CreateDMA(InputValidation::Pattern_Time);
		
		parent::FillObject($this, "select t.*,concat(fname,' ',lname) _fullname,
				bf1.InfoDesc _GoMeanDesc,bf2.InfoDesc _ReturnMeanDesc
			from ATN_requests t join BSC_persons using(PersonID)
				left join BaseInfo bf1 on(bf1.TypeID=21 AND bf1.InfoID=GoMean)
				left join BaseInfo bf2 on(bf2.TypeID=21 AND bf2.InfoID=ReturnMean)
			where RequestID=?", array($id));
	}
	
	static function Get($where = '', $whereParams = array()) {
		
		$query = "select t.*,concat(p1.fname,' ',p1.lname) fullname,
				bf1.InfoDesc GoMeanDesc,bf2.InfoDesc ReturnMeanDesc,bf3.InfoDesc OffTypeDesc,
				concat(p2.fname,' ',p2.lname) OffFullname,
				concat(p3.fname,' ',p3.lname) SurveyFullname,
				sh.ShiftTitle,
				sh.FromTime ShiftFromTime,
				sh.ToTime ShiftToTime,
				sp.FlowID,
				sp.StepDesc
				
			from ATN_requests t
			join BSC_persons p1 using(PersonID)
			left join BSC_persons p2 on(OffPersonID=p2.PersonID)
			left join BSC_persons p3 on(SurveyPersonID=p3.PersonID)
			left join BaseInfo bf1 on(bf1.TypeID=21 AND bf1.InfoID=GoMean)
			left join BaseInfo bf2 on(bf2.TypeID=21 AND bf2.InfoID=ReturnMean)
			left join BaseInfo bf3 on(bf3.TypeID=20 AND bf3.InfoID=OffType)
			
			join Baseinfo bf4 on(bf4.TypeID=11 AND bf4.param4=t.ReqType)
			join WFM_flows fl on(fl.ObjectType=bf4.InfoID)
			join WFM_FlowSteps sp on(sp.FlowID=fl.FlowID AND sp.StepID=t.ReqStatus)
			
			left join ATN_shifts sh using(ShiftID)
			where 1=1 " . $where;
		
		return parent::runquery_fetchMode($query, $whereParams);		
	}
}

?>
