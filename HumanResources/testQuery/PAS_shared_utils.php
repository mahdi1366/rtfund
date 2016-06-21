<?php
	// این فایل در شاخه staff هم وجود دارد
	// تغییراتی که در این فایل داده می شود در فایل بالا هم داده شود
	require_once('PASDefinitions.inc.php');
	class PASUtils
	{
		// آیا وعده غذایی ذکر شده در تاریخ ارسال شده تعطیل بوده یا خیر
		function IsFoodHoliday($ThisDate, $FoodType)
		{
			$mysql = dbclass::getInstance();
			$res = $mysql->Execute("select * from pas.FoodHolidays where HolidayDate='".$ThisDate."' and FoodType='".$FoodType."'");
			if($arr_res=$res->FetchRow())
				return true;		
			return false;
		}
		
		// شیفتهای کاری یک فرد را از لیست کشیک او استخراج می کند
		function CreateShiftsList($CurPersonID, $SelectedYear, $SelectedMonth)
		{
			$ret = array();
			$mysql = dbclass::getInstance();
			if($SelectedMonth<7)
				$MonthDay = 31;
			else if($SelectedMonth<12)
				$MonthDay = 30;
			else if ($SelectedYear % 4 == 3) {
				$MonthDay = 30;
			} else if ($SelectedYear % 4 != 3) {
				$MonthDay = 29;
			}
			if($SelectedMonth<10)
				$CMonth = "0".$SelectedMonth;
			else
				$CMonth = $SelectedMonth;
			$Enter = 0;
			$WorkTime = 0;
			$k = 0;
			for($m=1; $m<=$MonthDay; $m++)
			{
				if($m<10)
					$CDay = "0".$m;
				else
					$CDay = $m;
				$CurDateMiladi = PASUtils::GetMiladiDate($SelectedYear, $SelectedMonth, $m);
				$CurDate  = mktime(0, 0, 0, substr($CurDateMiladi, 4, 2), substr($CurDateMiladi, 6, 2), substr($CurDateMiladi, 0, 4));
				
				$res = $mysql->Execute("select * from pas.PersonEnterExitMaps where MapYear='".$SelectedYear."' and MapMonth='".$SelectedMonth."' and MapDay='".$m."' and PersonID='".$CurPersonID."'");
				if($rec = $res->FetchRow())
				{
					//$Enter = 0;
					for($i=0; $i<1440; $i++)
					{
						if(substr($rec["MapArray"], $i, 1)=="1")
						{
							if($Enter==0 && ($i==0 || ($i>0 && substr($rec["MapArray"], $i-1, 1)=="0")) )
							{
								$ret[$k]["StartTime"] = $i;
								$ret[$k]["Day"] = $CDay;
							}
							$Enter = 1;
							$WorkTime++;
						}
						else 
						{
							if($i>0 && substr($rec["MapArray"], $i-1, 1)=="1")
							{
								$ret[$k]["WorkTime"] = $WorkTime;
								$ret[$k]["EndTime"] = $i;
								$k++;
								$WorkTime = 0;
							}
							$Enter = 0;
						}
					}
				}
			}
			// اگر آخرین روز ماه همچنان خروج نداشت
			if($Enter==1)
			{
				// برای اولین روز ماه بعد چک می کند خروج چه زمانی است
				if($SelectedMonth==12)
				{
					$res = $mysql->Execute("select * from pas.PersonEnterExitMaps where MapYear='".($SelectedYear+1)."' and MapMonth='1' and MapDay='1' and PersonID='".$CurPersonID."'");
				}
				else
					$res = $mysql->Execute("select * from pas.PersonEnterExitMaps where MapYear='".$SelectedYear."' and MapMonth='".($SelectedMonth+1)."' and MapDay='1' and PersonID='".$CurPersonID."'");
				if($rec = $res->FetchRow())
				{
					for($i=0; $i<1440; $i++)
					{
						if(substr($rec["MapArray"], $i, 1)=="0")
						{
							$ret[$k]["WorkTime"] = $WorkTime;
							$ret[$k]["EndTime"] = $i;
							$k++;
							break;
						}
						else
							$WorkTime++;
					}
				}
				else
				{
					$ret[$k]["WorkTime"] = $WorkTime;
					$ret[$k]["EndTime"] = 0;
					$k++;
				}
			}
			
			return $ret;		
		}
		
		// میزان موظفی یک فرد را بر اساس لوح کشیک او در یک ماه بر می گرداند
		function GetDutyMinutesInMonth($CurPersonID, $SelectedYear, $SelectedMonth)
		{
			$WorkTime = 0;
			$mysql = dbclass::getInstance();
			if($SelectedMonth<7)
				$MonthDay = 31;
			else
				$MonthDay = 30;
			$k = 0;
			for($m=1; $m<=$MonthDay; $m++)
			{
				$res = $mysql->Execute("select * from pas.PersonEnterExitMaps where MapYear='".$SelectedYear."' and MapMonth='".$SelectedMonth."' and MapDay='".$m."' and PersonID='".$CurPersonID."'");
				if($rec = $res->FetchRow())
				{
					for($i=0; $i<1440; $i++)
					{
						if(substr($rec["MapArray"], $i, 1)=="1")
							$WorkTime++;
					}
				}
			}
			return $WorkTime;		
		}
		
		
		// تبدیل ارقام رشته ورودیبه ارقام فارسی و برگرداندن آن
		function ChangeDigitToFarsi($st)
		{
			$st = str_replace("1", "۱", $st);
			$st = str_replace("2", "۲", $st);
			$st = str_replace("3", "۳", $st);
			$st = str_replace("4", "۴", $st);
			$st = str_replace("5", "۵", $st);
			$st = str_replace("6", "۶", $st);
			$st = str_replace("7", "۷", $st);
			$st = str_replace("8", "۸", $st);
			$st = str_replace("9", "۹", $st);
			$st = str_replace("0", "۰", $st);
			return $st;
		}
		
		// تعداد روزهای کاری یک ماه را بر می گرداند - یعنی روزهای تعطیل را از کل روزهای ماه کم می کند
		// نکته: روزهای بعد از روز جاری را حساب نمی کند
		function GetWorkingDaysOfMonth($Year, $Month)
		{
			$now = date("Ymd");
			$yy = substr($now,0,4); 
			$mm = substr($now,4,2); 
			$dd = substr($now,6,2);
			$CurrentDay = $yy."/".$mm."/".$dd;
			
			$WorkingDay = 0;
			$MonthDay = 30;
			if($Month<7)
				$MonthDay = 31;
			if($Month==12)
				$MonthDay = 29;
			for($i=1; $i<=$MonthDay; $i++)
			{
				$Day = $i;
				$CurDateMiladi = PASUtils::GetMiladiDate($Year, $Month, $Day);
				$CurDate  = mktime(0, 0, 0, substr($CurDateMiladi, 4, 2), substr($CurDateMiladi, 6, 2), substr($CurDateMiladi, 0, 4));
				if(substr($CurDateMiladi, 0, 4)."/".substr($CurDateMiladi, 4, 2)."/".substr($CurDateMiladi, 6, 2)>$CurrentDay)
					break;
			
				if(!PASUtils::IsEndWeekVacation($CurDateMiladi) && !PASUtils::IsHoliday($CurDateMiladi))
				{
					$WorkingDay++;
					//if($_SESSION["UserID"]=="omid")
					//	echo $Day."<br>";
				}
			}
			return $WorkingDay;
		}
		
		// تاریخ روزهای تعطیل را به صورت لیستی جدا شده با کاما بر می گرداند
		function GetWorkingDaysOfMonthInAList($Year, $Month)
		{
			$now = date("Ymd");
			$yy = substr($now,0,4); 
			$mm = substr($now,4,2); 
			$dd = substr($now,6,2);
			$CurrentDay = $yy."/".$mm."/".$dd;
			$ret = "";
			$WorkingDay = 0;
			$MonthDay = 30;
			if($Month<7)
				$MonthDay = 31;
			if($Month==12)
				$MonthDay = 29;
			for($i=1; $i<=$MonthDay; $i++)
			{
				$Day = $i;
				$CurDateMiladi = PASUtils::GetMiladiDate($Year, $Month, $Day);
				$CurDate  = mktime(0, 0, 0, substr($CurDateMiladi, 4, 2), substr($CurDateMiladi, 6, 2), substr($CurDateMiladi, 0, 4));
				if(substr($CurDateMiladi, 0, 4)."/".substr($CurDateMiladi, 4, 2)."/".substr($CurDateMiladi, 6, 2)>$CurrentDay)
					break;
			
				if(PASUtils::IsEndWeekVacation($CurDateMiladi) || PASUtils::IsHoliday($CurDateMiladi))
				{
					if($ret!="")
						$ret .= ", ";
					$ret .= "'".substr($CurDateMiladi, 0, 4)."-".substr($CurDateMiladi, 4, 2)."-".substr($CurDateMiladi, 6, 2)."'";
				}
			}
			return $ret;
		}
		
		// نوع مرخصی را برای یک فرد در یک روز بر می گرداند
		function GetLeaveType($PersonID, $CurDate)
		{
			$mysql = dbclass::getInstance(config::$db_servers["slave"]["host"], config::$db_servers["slave"]["report_user"], config::$db_servers["slave"]["report_pass"]);
			$res = $mysql->Execute("select * from pas.DailyLeaves where PersonID='".$PersonID."' and FromDate<='".$CurDate."' and ToDate>'".$CurDate."'");
			if($arr_res=$res->FetchRow())
			{
				$LeaveType = $arr_res["LeaveType"];
				if($LeaveType=="1")
					return "استحقاقی";
				else
					return "استعلاجی";
			}
			return "";
		}
		
		// بر اساس تقویم کاری انتخابی و شماره روز مشخص می کند آن روز کاری است یا خیر
		function IsWorkingDayAccordingToCalendar($CalendarID, $DayNo)
		{
			$mysql = dbclass::getInstance(config::$db_servers["slave"]["host"], config::$db_servers["slave"]["report_user"], config::$db_servers["slave"]["report_pass"]);
			$ret = "";
			$res = $mysql->Execute("select * from pas.CalendarDetails where calendarID='$CalendarID' and dayNo='$DayNo'");
			if($arr_res=$res->FetchRow())
				return true;
			return false;
		}
		
		// ایجاد لیست انواع کاری به صورت آپشن های یک کمبو باکس
		function CreateEnterExitTypesOptions($DefaultValue)
		{
			$mysql = dbclass::getInstance();
			$ret = "";
			$res = $mysql->Execute("select * from pas.EnterExitTypes");
			while($arr_res=$res->FetchRow())
			{
				$ret .= "<option value='".$arr_res["EnterExitTypeID"]."' ";
				if($arr_res["EnterExitTypeID"]==$DefaultValue)
					$ret .= " selected ";				
				$ret .= ">".$arr_res["title"];
			}
			return 	$ret;	
		}
		
		// 	ایجاد لیست تقویمهای کاری به صورت آپشن های یک کمبو باکس
		function CreateCalendarsOptions($DefaultValue)
		{
			$mysql = dbclass::getInstance();
			$ret = "";
			$res = $mysql->Execute("select * from pas.Calendars");
			while($arr_res=$res->FetchRow())
			{
				$ret .= "<option value='".$arr_res["CalendarID"]."' ";
				if($arr_res["CalendarID"]==$DefaultValue)
					$ret .= " selected ";				
				$ret .= ">".$arr_res["title"];
			}
			return 	$ret;	
		}
		
		// ایجاد لیست گروه های کاری به صورت آپشن های یک کمبو باکس
		function CreateWorkGroupOptions($DefaultValue)
		{
			$mysql = dbclass::getInstance();
			$ret = "";
			$res = $mysql->Execute("select * from pas.WorkGroups where WorkUnitCode='".$_SESSION["UserGroup"]."'");
			while($arr_res=$res->FetchRow())
			{
				$ret .= "<option value='".$arr_res["WorkGroupID"]."' ";
				if($arr_res["WorkGroupID"]==$DefaultValue)
					$ret .= " selected ";				
				$ret .= ">".$arr_res["title"];
			}
			return 	$ret;	
		}
		
		function CreateThisUnitPersonelOptions($DefaultValue)
		{
			$PersonList = "<option value=0>-";
			$mysql = dbclass::getInstance();
			$UserGroup = $_SESSION['UserGroup']; 
			$res = $mysql->Execute("select persons.PersonID, plname, pfname from hrmstotal.persons JOIN pas.PersonSettings using (PersonID) where WorkUnitCode='".$UserGroup."' order by plname, pfname");
			while($arr_res=$res->FetchRow())
			{
				$PersonList .= "<option value='".$arr_res["PersonID"]."' ";
				if($arr_res["PersonID"]==$DefaultValue)
					$PersonList .= " selected ";
				$PersonList .= ">".$arr_res["plname"]." ".$arr_res["pfname"];
			}
			return 	$PersonList;	
		}
		// نمودار کاری در یک روز را با گرفتن آرایه آن بر می گرداند - فرم گرافیکی
		function GetADayStatusChart($CurDate, $DateArray, $FromHour, $ToHour)
		{
			$ret = "<table border=1 cellspacing=0 style='font-family: tahoma; font-size: 8px'><tr><td>$CurDate</td>";
			for($i=$FromHour; $i<=$ToHour; $i++)
			{
				for($k=0; $k<4; $k++)
				{
					$sw = 0;
					for($j=$i*60+$k*15; $j<$i*60+$k*15+14; $j++)
					{
						if($DateArray[$j]==1)
							$sw = 1;
						else if($DateArray[$j]==2)
							$sw = 2;
						else if($DateArray[$j]==3)
							$sw = 3;
					}
					if($sw==0)
						$ret .= "<td>";
					else if($sw==1)
						$ret .= "<td bgcolor=#cccccc>";
					else if($sw==2)
						$ret .= "<td bgcolor=#cccc00>";
					else if($sw==3)
						$ret .= "<td bgcolor=#00cccc>";
					$ret .= "&nbsp;</td>";
				}
			}
			$ret .= "</tr></table>";
			return $ret;
		}
		
		// با گرفتن مقدار معادل یک فیلد تاریخ-ساعت مقدار آن را به دقیقه بر می گرداند
		function GetMinutes($DateTime)
		{
			return substr($DateTime, 11, 2)*60+substr($DateTime, 14, 2);
		}
		
		// شماره رکوردی مربوط به یک روز کاربر را که در آن رکورد ورود ثبت شده ولی خروج ثبت نشده را بر می گرداند
		// اگر چنین رکوردی پیدا نشد صفر بر می گرداند
		//فرمت تاریخ به میلادی و به صورت 20080428		
		function FindEnterWithoutExitRecord($PersonID, $SelectedDate)
		{
			$FromDate = substr($SelectedDate,0,4)."-".substr($SelectedDate,4,2)."-".substr($SelectedDate,6,2)." 00:00:01";
			$ToDate = substr($SelectedDate,0,4)."-".substr($SelectedDate,4,2)."-".substr($SelectedDate,6,2)." 23:59:0";
			$mysql = dbclass::getInstance();
			$query = "select * from pas.EnterExitTimes where PersonID='".$PersonID."' and EnterTime between '".$FromDate."' and '".$ToDate."' and ExitTime='0000-00-00 00:00:00'";
			$res = $mysql->Execute($query);
			if($arr_res=$res->FetchRow())
				return $arr_res["EnterExitTimeID"];
			return 0;
		}
		
		// 
		//تعداد ورود و خروج در یک روز را بر می گرداند
		//فرمت تاریخ به میلادی و به صورت 20080428
		function GetNumberOfEnterExitInADate($personID, $SelectDate)
		{
			$FromDate = substr($SelectedDate,0,4)."-".substr($SelectedDate,4,2)."-".substr($SelectedDate,6,2)." 00:00:01";
			$ToDate = substr($SelectedDate,0,4)."-".substr($SelectedDate,4,2)."-".substr($SelectedDate,6,2)." 23:59:0";
			$mysql = dbclass::getInstance(config::$db_servers["slave"]["host"], config::$db_servers["slave"]["report_user"], config::$db_servers["slave"]["report_pass"]);
			$query = "select count(*) AS TotalCount from pas.EnterExitTimes where PersonID='".$PersonID."' and EnterTime between '".$FromDate."' and '".$ToDate."'";
			$res = $mysql->Execute($query);
			if($arr_res=$res->FetchRow())
				return $arr_res["TotalCount"];
			return 0;
		}
		
		// برای یک شخص که شیفتی ۲۴ ساعته است روز را در یک آرایه بر می گرداند
		// این آرایه 1440 سلول دارد که به تعداد دقایق روز می باشد و در صورتیکه شخص در آن زمان (دقیقه) حضور داشته باشد
		// یا مرخصی و یا ماموریت باشد آن سلول عدد ۱ در غیر اینصورت عدد صفر دارد
		// فرمت تاریخ به میلادی و به صورت 20080428
		// DayStatus:  وضعیت روز را مشخص می کند که یکی از موارد زیر است:
		// StartWorkingDay - امروز روز کاری (شروع کار) است
		// EndWorkingDay: امروز روز پایان کاری است (دیروز روز شروع کاری بوده)
		// Vacation: تعطیل
		function CreateADateEnterExitStatusFor24HOURShift($PersonID, $SelectedDate, $DayStatus)
		{
			
			$FromDate = substr($SelectedDate,0,4)."-".substr($SelectedDate,4,2)."-".substr($SelectedDate,6,2)." 00:00:01";
			$ToDate = substr($SelectedDate,0,4)."-".substr($SelectedDate,4,2)."-".substr($SelectedDate,6,2)." 23:59:0";
			
			$ToDayArray = array();
			$ToDayArray["TotalPresentTime"] = 0;
			for($i=0; $i<=1440; $i++)
				$ToDayArray["DayTimes"][$i] = 0;
			$mysql = dbclass::getInstance(config::$db_servers["slave"]["host"], config::$db_servers["slave"]["report_user"], config::$db_servers["slave"]["report_pass"]);
			$query = "select EnterTime, ExitTime from pas.EnterExitTimes where PersonID='".$PersonID."'	and EnterTime between '".$FromDate."' and '".$ToDate."' order by EnterTime";
			$res = $mysql->Execute($query);
			$EnterExitCount = 0;
			while($arr_res=$res->FetchRow())
			{
				$EnterExitCount++;
				/*
				 برای مواردی که شخص وارد شده ولی خروج ندارد آخر وقت را خروج در نظر می گیرد
				*/
				$EndTime = 1440;
				if($arr_res["ExitTime"]!="0000-00-00 00:00:00")
					$EndTime = PASUtils::GetMinutes($arr_res["ExitTime"]);
				
				for($i=PASUtils::GetMinutes($arr_res["EnterTime"]); $i<=$EndTime && $i<=1440; $i++)
					$ToDayArray["DayTimes"][$i] = 1;

				if($DayStatus=="EndWorkingDay")
					$ToDayArray["DayTimes"][PASUtils::GetMinutes($arr_res["EnterTime"])] = 8;
			}
			$ToDayArray["EnterExitCount"] = $EnterExitCount;
			$query = "select *, FromHour*60+FromMin as FromMinutes, ToHour*60+ToMin as ToMinutes from pas.HourlyLeaves where PersonID='".$PersonID."' and LeaveDate='".$SelectedDate."'";
			$res = $mysql->Execute($query);
			$LeavesCount = 0;
			while($arr_res=$res->FetchRow())
			{
				$LeavesCount++;
				for($i=$arr_res["FromMinutes"]; $i<=$arr_res["ToMinutes"]; $i++)
					$ToDayArray["DayTimes"][$i] = 2;
			}
			$ToDayArray["LeavesCount"] = $LeavesCount;			
			$query = "select *, FromHour*60+FromMin as FromMinutes, ToHour*60+ToMin as ToMinutes from pas.HourlyMissions where PersonID='".$PersonID."' and MissionDate='".$SelectedDate."'";
			$res = $mysql->Execute($query);
			$MissionCount = 0;
			while($arr_res=$res->FetchRow())
			{
				$MissionCount++;
				for($i=$arr_res["FromMinutes"]; $i<=$arr_res["ToMinutes"]; $i++)
					$ToDayArray["DayTimes"][$i] = 3;
			}
			$ToDayArray["MissionCount"] = $LeavesCount;
			//در صورتیکه روز خاتمه کاری باشد چون ساعت ورود اولیه در ساعت صفر بامداد ثبت نشده است پس ورود و خروجها بالعکس است بنابراین وضعیت
			//دقتیق روز هم باید بالعکس شود
			if($DayStatus=="EndWorkingDay" && $EnterExitCount>0)
			{
				for($i=0; $i<=1440; $i++)
				{
					if($ToDayArray["DayTimes"][$i]==0)
						$ToDayArray["DayTimes"][$i] = 1;
					else if($ToDayArray["DayTimes"][$i]==1)
						$ToDayArray["DayTimes"][$i] = 0;
					else if($ToDayArray["DayTimes"][$i]==8)
						$ToDayArray["DayTimes"][$i] = 1;
				}
			}
			for($i=0; $i<=1440; $i++)
			{
				if($ToDayArray["DayTimes"][$i]==1)
					$ToDayArray["TotalPresentTime"]++;
			}
			return 	$ToDayArray;
		}
		
		// در یک روز وضعیت حضور و غیاب شخص را مشخص می کند - در یک آرایه بر می گرداند
		// این آرایه 1440 سلول دارد که به تعداد دقایق روز می باشد و در صورتیکه شخص در آن زمان (دقیقه) حضور داشته باشد
		// یا مرخصی و یا ماموریت باشد آن سلول عدد ۱ در غیر اینصورت عدد صفر دارد
		// فرمت تاریخ به میلادی و به صورت 20080428
		// پارامتر آخر مشخص می کند در صورتیکه خروج برای ورودی ثبت نشده باشد آخر وقت اداری را به عنوان خروج در نظر بگیرد یا آخر روز کاری را
		function CreateADateEnterExitStatus($PersonID, $SelectedDate, $DefaultExitAtOfficialEndTime = TRUE)
		{
			$FromDate = substr($SelectedDate,0,4)."-".substr($SelectedDate,4,2)."-".substr($SelectedDate,6,2)." 00:00:01";
			$ToDate = substr($SelectedDate,0,4)."-".substr($SelectedDate,4,2)."-".substr($SelectedDate,6,2)." 23:59:0";
			
			$ToDayArray = array();
			$ToDayArray["TotalPresentTime"] = 0;
			$TotalPresentTime = 0;
			for($i=0; $i<=1440; $i++)
				$ToDayArray["DayTimes"][$i] = 0;
			//$mysql = dbclass::getInstance(config::$db_servers["slave"]["host"], config::$db_servers["slave"]["report_user"], config::$db_servers["slave"]["report_pass"]);		
				// چندین مورد مشکل بوجود آمد از غیر همزمان بودن داده ها بنابراین خط بالا کامنت شد و از سرور اصلی کیوری زده شد			
			$mysql = dbclass::getInstance();
			$query = "select EnterTime, ExitTime from pas.EnterExitTimes where PersonID='".$PersonID."'	and EnterTime between '".$FromDate."' and '".$ToDate."'";
			$res = $mysql->Execute($query);
			$EnterExitCount = 0;
			while($arr_res=$res->FetchRow())
			{
				$EnterExitCount++;
				/*
				 برای مواردی که شخص وارد شده ولی خروج ندارد آخر وقت را خروج در نظر می گیرد
				 */
				if($DefaultExitAtOfficialEndTime)
				{
					//$EndTime = OFFICIAL_END_TIME;
					// اگر کسی خروج ثبت نکرده بود به صورت اتومات همان ورود او را به عنوان خروج در نظر بگیرد
					$EndTime = PASUtils::GetMinutes($arr_res["EnterTime"]);
				}
				else
					$EndTime = 1440;
					
				if($arr_res["ExitTime"]!="0000-00-00 00:00:00")
					$EndTime = PASUtils::GetMinutes($arr_res["ExitTime"]);
				
				for($i=PASUtils::GetMinutes($arr_res["EnterTime"]); $i<=$EndTime && $i<=1440; $i++)
				{
					$ToDayArray["DayTimes"][$i] = 1;
					$TotalPresentTime++;
				}
			}
			
			$ToDayArray["EnterExitCount"] = $EnterExitCount;
			$query = "select *, FromHour*60+FromMin as FromMinutes, ToHour*60+ToMin as ToMinutes from pas.HourlyLeaves where PersonID='".$PersonID."' and LeaveDate='".$SelectedDate."'";
			$res = $mysql->Execute($query);
			$LeavesCount = 0;
			while($arr_res=$res->FetchRow())
			{
				$LeavesCount++;
				for($i=$arr_res["FromMinutes"]; $i<=$arr_res["ToMinutes"]; $i++)
				{
					if($ToDayArray["DayTimes"][$i]==0)
						$ToDayArray["DayTimes"][$i] = 2;
				}
			}
			$ToDayArray["LeavesCount"] = $LeavesCount;
			$query = "select *, FromHour*60+FromMin as FromMinutes, ToHour*60+ToMin as ToMinutes from pas.HourlyMissions where PersonID='".$PersonID."' and MissionDate='".$SelectedDate."'";
			$res = $mysql->Execute($query);
			$MissionCount = 0;
			while($arr_res=$res->FetchRow())
			{
				$MissionCount++;
				for($i=$arr_res["FromMinutes"]; $i<=$arr_res["ToMinutes"]; $i++)
				{
					if($ToDayArray["DayTimes"][$i]==0)
						$ToDayArray["DayTimes"][$i] = 3;
				}
			}
			$ToDayArray["MissionCount"] = $MissionCount;
			$ToDayArray["TotalPresentTime"] = $TotalPresentTime;
			//for($i=420; $i<450; $i++)
			//	echo $ToDayArray["DayTimes"][$i];
			//echo "<br>";
			
			return 	$ToDayArray;
		}

		
		// خروجی آرایه ای است دو بعدی که بعد اول آن ایندکس و بعد دوم آن یکی از موارد زیر است:
		// Date: تاریخ روز
		// Start: از دقیقه
		// End: تا دقیقه
		function CreateAbsentTimesArrayInMonth($PersonID, $Year, $Month)
		{
			$now = date("Ymd"); 
			$yy = substr($now,0,4); 
			$mm = substr($now,4,2); 
			$dd = substr($now,6,2);
			$CurrentDay = $yy."/".$mm."/".$dd;
			$k = 0;
			$ret = array();
			$MonthDay = 30;
			if($Month<7)
				$MonthDay = 31;
			for($Day=1; $Day<=$MonthDay; $Day++)
			{
				$CurDateMiladi = PASUtils::GetMiladiDate($Year, $Month, $Day);
		
				$CurDate  = mktime(0, 0, 0, substr($CurDateMiladi, 4, 2), substr($CurDateMiladi, 6, 2), substr($CurDateMiladi, 0, 4));
				if(substr($CurDateMiladi, 0, 4)."/".substr($CurDateMiladi, 4, 2)."/".substr($CurDateMiladi, 6, 2)>$CurrentDay)
					break;
				
				if(PASUtils::IsEndWeekVacation($CurDateMiladi) || PASUtils::IsHoliday($CurDateMiladi) || PASUtils::HasDailyLeave($PersonID, $CurDateMiladi) || PASUtils::HasDailyMission($PersonID, $CurDateMiladi))
					continue;						
				$DateArray = PASUtils::CreateADateEnterExitStatus($PersonID, $CurDateMiladi);
				$Start = 0;
				for($i=OFFICIAL_START_TIME; $i<=OFFICIAL_END_TIME; $i++)
				{
					if($DateArray["DayTimes"][$i]==0)
					{
						if($Start==0)
						{
							$Start = $i; 
						}
					}
					else
					{
						if($Start>0 && $Start!=$i)
						{
							$End = $i;
							$ret[$k]["Date"] = $Year."/".$Month."/".$Day;
							$ret[$k]["MiladiDate"] = $CurDateMiladi;
							$ret[$k]["FromHour"] = (int)($Start/60);
							$ret[$k]["FromMin"] = $Start%60;
							$ret[$k]["ToHour"] = (int)($End/60);
							$ret[$k]["ToMin"] = $End%60;
							$k++;
							$Start = 0;
							$End = 0;
						}
					}
				}
				if($Start>0 && $Start!=OFFICIAL_END_TIME)
				{
					$End = OFFICIAL_END_TIME;
					$ret[$k]["Date"] = $Year."/".$Month."/".$Day;
					$ret[$k]["MiladiDate"] = $CurDateMiladi;
					$ret[$k]["FromHour"] = (int)($Start/60);
					$ret[$k]["FromMin"] = $Start%60;
					$ret[$k]["ToHour"] = (int)($End/60);
					$ret[$k]["ToMin"] = $End%60;
					$k++;
				}
			}
			return $ret;
		}
		
	// خروجی آرایه ای است دو بعدی که بعد اول آن ایندکس و بعد دوم آن یکی از موارد زیر است:
		// Start: از دقیقه
		// End: تا دقیقه
		function CreateAbsentTimesArrayInDay($PersonID, $CurDateMiladi)
		{
			$mysql = dbclass::getInstance();
			$ret = array();
			$k = 0;

			$FarsiDate = shdate(substr($CurDateMiladi, 0, 4)."-".substr($CurDateMiladi, 4, 2)."-".substr($CurDateMiladi, 6, 2))."<br>";
			$dd = substr($FarsiDate, 0, 2);
			$mm = substr($FarsiDate, 3, 2);
			$CurYear = "13".substr($FarsiDate, 6, 2);
			
			$query = "select EnterExitTypeID, StartTime, EndTime from pas.PersonSettings JOIN pas.EnterExitTypes using (EnterExitTypeID) where PersonID='".$PersonID."'";
			$res = $mysql->Execute($query);
			$rec = $res->FetchRow();
			$StartTime = $rec["StartTime"]; // در حالت پیش فرض ساعات شروع و پایان را بر اساس نوع کاری دایمی تعریف شده برای فرد را در نظر می گیرد
			$EndTime = $rec["EndTime"];
			
			// بررسی می کند آیا در روز ذکر شده نوع کاری موقتی به فرد منتسب شده است یا خیر
			$query = "select * from pas.PersonSpecialMonthesEnterExitType 
										JOIN hrmstotal.persons using (PersonID)
										JOIN pas.EnterExitTypes using (EnterExitTypeID)   
										where SelectedYear='".$CurYear."' and SelectedMonth='".$mm."' and FromDay<=".$dd." and ToDay>=".$dd." and PersonID='".$PersonID."' order by PersonSpecialEnterExitTypeID DESC";
			$res = $mysql->Execute($query);
			// اگر نوع کاری موقتی برای آن روز برای شخص در نظر گرفته شده بود ساعت شروع و پایان را بر اساس آن در نظر می گیرد
			if($rec = $res->FetchRow())
			{
				$StartTime = $rec["StartTime"];
				$EndTime = $rec["EndTime"];
			}
			if(PASUtils::IsEndWeekVacation($CurDateMiladi) || PASUtils::IsHoliday($CurDateMiladi) || PASUtils::HasDailyLeave($PersonID, $CurDateMiladi) || PASUtils::HasDailyMission($PersonID, $CurDateMiladi))
				return $ret;						
			$DateArray = PASUtils::CreateADateEnterExitStatus($PersonID, $CurDateMiladi);
			$Start = 0;
			for($i=$StartTime; $i<=$EndTime; $i++)
			{
				if($DateArray["DayTimes"][$i]==0)
				{
					if($Start==0)
					{
						$Start = $i; 
					}
				}
				else
				{
					if($Start>0 && $Start!=$i)
					{
						$End = $i;
						$ret[$k]["FromHour"] = (int)($Start/60);
						$ret[$k]["FromMin"] = $Start%60;
						$ret[$k]["ToHour"] = (int)($End/60);
						$ret[$k]["ToMin"] = $End%60;
						$k++;
						$Start = 0;
						$End = 0;
					}					
				}
			}
			if($Start>0 && $Start!=$EndTime)
			{
				$End = $EndTime;
				$ret[$k]["FromHour"] = (int)($Start/60);
				$ret[$k]["FromMin"] = $Start%60;
				$ret[$k]["ToHour"] = (int)($End/60);
				$ret[$k]["ToMin"] = $End%60;
				$k++;
			}			
			return $ret;
		}
		
		// اطلاعات نوع کاری شخص ر ا با توجه به سال و ماه بر می گرداند
		// در صورتیکه حالت خاص برای آن ماه ثبت نشده بود مقدار پیش فرض شخص برگردانده می شود
		// اطلاعات نوع کاری در یک آرایه بر گردانده می شود که ایندکسهای آن عبارتند از:
		// id: کد نوع کاری
		// StartTime: زمان شروع کار به دقیقه
		// EndTime: زمان پایان کار به دقیقه
		// JobStatus: نوع کار - OFFICIAL - PARTTIME - 24HOUR
		// title: عنوان نوع کاری
		// CalendarID: کد تقویم کاری منتسب
		// CalendarTitle: عنوان تقویم کاری منتسب
		function GetEnterExitTypeAndCalendarInfo($PersonID, $year, $month)
		{
			$EnterExitType = array();
			for($i=1; $i<32; $i++)
			{
				$EnterExitType[$i]["id"] = 1;
				$EnterExitType[$i]["JobStatus"] = 'OFFICIAL';
				$EnterExitType[$i]["StartTime"] = 420;
				$EnterExitType[$i]["EndTime"] = 870;
				$EnterExitType[$i]["title"] = 'اداری';
				$EnterExitType[$i]["CalendarID"] = '0';
				$EnterExitType[$i]["CalendarTitle"] = '-';
				$EnterExitType[$i]["CalculateExtraWorkBeforeStartTime"] = 'YES';
				$EnterExitType[$i]["ExtraWorkUpperBound"] = 0;
				$EnterExitType[$i]["CalculateExtraWorkInThursday"] = "YES";
				$EnterExitType[$i]["CalculateExtraWorkInFriday"] = "YES";
				$EnterExitType[$i]["CalculateExtraWorkInHolidays"] = "YES";
				$EnterExitType[$i]["ThursdayStartTime"] = 0;
				$EnterExitType[$i]["ThursdayEndTime"] = 24*60;
				$EnterExitType[$i]["MaxExtraWorkInHolidays"] = 24*60;
				$EnterExitType[$i]["ExtraWorkStartPoint"] = 0;
			}
			$mysql = dbclass::getInstance(config::$db_servers["slave"]["host"], config::$db_servers["slave"]["report_user"], config::$db_servers["slave"]["report_pass"]);
			$ret = "";
			$res = $mysql->Execute("select *, Calendars.title as CalendarTitle, EnterExitTypes.title as ETitle from pas.PersonSettings
									LEFT JOIN pas.EnterExitTypes using (EnterExitTypeID)
									LEFT JOIN pas.Calendars using (CalendarID)  
									where PersonID='".$PersonID."'");
			if($arr_res=$res->FetchRow())
			{
				for($i=1; $i<32; $i++)
				{
					$EnterExitType[$i]["id"] = $arr_res["EnterExitTypeID"];
					$EnterExitType[$i]["JobStatus"] = $arr_res["JobStatus"];
					$EnterExitType[$i]["StartTime"] = $arr_res["StartTime"];
					$EnterExitType[$i]["EndTime"] = $arr_res["EndTime"];
					$EnterExitType[$i]["title"] = $arr_res["ETitle"];
					$EnterExitType[$i]["CalendarID"] = $arr_res["CalendarID"];
					$EnterExitType[$i]["CalendarTitle"] = $arr_res["CalendarTitle"];
					$EnterExitType[$i]["CalculateExtraWorkBeforeStartTime"] = $arr_res["CalculateExtraWorkBeforeStartTime"];
					$EnterExitType[$i]["ExtraWorkUpperBound"] = $arr_res["ExtraWorkUpperBound"];
					$EnterExitType[$i]["CalculateExtraWorkInThursday"] = $arr_res["CalculateExtraWorkInThursday"];
					$EnterExitType[$i]["CalculateExtraWorkInFriday"] = $arr_res["CalculateExtraWorkInFriday"];
					$EnterExitType[$i]["CalculateExtraWorkInHolidays"] = $arr_res["CalculateExtraWorkInHolidays"];
					$EnterExitType[$i]["ThursdayStartTime"] = $arr_res["ThursdayStartTime"];
					$EnterExitType[$i]["ThursdayEndTime"] = $arr_res["ThursdayEndTime"];
					$EnterExitType[$i]["MaxExtraWorkInHolidays"] = $arr_res["MaxExtraWorkInHolidays"];
					$EnterExitType[$i]["ExtraWorkStartPoint"] = $arr_res["ExtraWorkStartPoint"];
				}
			}
			$res = $mysql->Execute("select *, Calendars.title as CalendarTitle, EnterExitTypes.title as ETitle  from pas.PersonSpecialMonthesEnterExitType 
									LEFT JOIN pas.EnterExitTypes using (EnterExitTypeID) 
									LEFT JOIN pas.Calendars using (CalendarID)  
									where PersonID='".$PersonID."' and SelectedYear='".$year."' and SelectedMonth='".$month."' order by PersonSpecialEnterExitTypeID");
			while($arr_res=$res->FetchRow())
			{
				for($i=$arr_res["FromDay"]; $i<=$arr_res["ToDay"]; $i++)
				{
					$EnterExitType[$i]["id"] = $arr_res["EnterExitTypeID"];
					$EnterExitType[$i]["JobStatus"] = $arr_res["JobStatus"];
					$EnterExitType[$i]["StartTime"] = $arr_res["StartTime"];
					$EnterExitType[$i]["EndTime"] = $arr_res["EndTime"];
					$EnterExitType[$i]["title"] = $arr_res["ETitle"];
					$EnterExitType[$i]["CalendarID"] = $arr_res["CalendarID"];
					$EnterExitType[$i]["CalendarTitle"] = $arr_res["CalendarTitle"];
					$EnterExitType[$i]["CalculateExtraWorkBeforeStartTime"] = $arr_res["CalculateExtraWorkBeforeStartTime"];
					$EnterExitType[$i]["ExtraWorkUpperBound"] = $arr_res["ExtraWorkUpperBound"];
					$EnterExitType[$i]["CalculateExtraWorkInThursday"] = $arr_res["CalculateExtraWorkInThursday"];
					$EnterExitType[$i]["CalculateExtraWorkInFriday"] = $arr_res["CalculateExtraWorkInFriday"];
					$EnterExitType[$i]["CalculateExtraWorkInHolidays"] = $arr_res["CalculateExtraWorkInHolidays"];
					$EnterExitType[$i]["ThursdayStartTime"] = $arr_res["ThursdayStartTime"];
					$EnterExitType[$i]["ThursdayEndTime"] = $arr_res["ThursdayEndTime"];
					$EnterExitType[$i]["MaxExtraWorkInHolidays"] = $arr_res["MaxExtraWorkInHolidays"];
					$EnterExitType[$i]["ExtraWorkStartPoint"] = $arr_res["ExtraWorkStartPoint"];
				}
			}
			return 	$EnterExitType;
		}
		
		// مشخص می کند یک بازه تاریخی چند روز است
		function GetDaysCount($FromDate, $ToDate)
		{
			$FromDate2  = substr($FromDate, 4, 2)."/".substr($FromDate, 6, 2)."/".substr($FromDate, 0, 4);
			$ToDate2  = substr($ToDate, 4, 2)."/".substr($ToDate, 6, 2)."/".substr($ToDate, 0, 4);
			$DaysCount = (strtotime($ToDate2)-strtotime($FromDate2))/86400+1;
			return $DaysCount;
		}

		// لیست انواع خاص ورود و خروج را برای یک فرد بر می گرداند
		function GetPersonSpecialEnterExitTypes($PersonID)
		{
			$ret = array();
			$mysql = dbclass::getInstance();
			$query = "select *, Calendars.title as CalendarTitle, EnterExitTypes.title as ETitle  from pas.PersonSpecialMonthesEnterExitType 
									LEFT JOIN pas.EnterExitTypes using (EnterExitTypeID) 
									LEFT JOIN pas.Calendars using (CalendarID)  
									where PersonID='".$PersonID."'  order by PersonSpecialEnterExitTypeID DESC";

			$res = $mysql->Execute($query);
			$i=0;
			while($rec=$res->FetchRow())
			{
				$ret[$i]["EnterExitTypeID"] = $rec["EnterExitTypeID"];
				$ret[$i]["JobStatus"] = $rec["JobStatus"];	
				$ret[$i]["StartTime"] = $rec["StartTime"];
				$ret[$i]["EndTime"] = $rec["EndTime"];
				$ret[$i]["title"] = $rec["ETitle"];
				$ret[$i]["CalendarID"] = $rec["CalendarID"];
				$ret[$i]["CalendarTitle"] = $rec["CalendarTitle"];
				$ret[$i]["CalculateExtraWorkBeforeStartTime"] = $rec["CalculateExtraWorkBeforeStartTime"];
				$ret[$i]["ExtraWorkUpperBound"] = $rec["ExtraWorkUpperBound"];
				$ret[$i]["CalculateExtraWorkInThursday"] = $rec["CalculateExtraWorkInThursday"];
				$ret[$i]["CalculateExtraWorkInFriday"] = $rec["CalculateExtraWorkInFriday"];
				$ret[$i]["SelectedYear"] = $rec["SelectedYear"];
				$ret[$i]["SelectedMonth"] = $rec["SelectedMonth"];
				$ret[$i]["FromDay"] = $rec["FromDay"];
				$ret[$i]["ToDay"] = $rec["ToDay"];
				$ret[$i]["CalculateExtraWorkInHolidays"] = $rec["CalculateExtraWorkInHolidays"];
				$ret[$i]["ThursdayStartTime"] = $rec["ThursdayStartTime"];
				$ret[$i]["ThursdayEndTime"] = $rec["ThursdayEndTime"];
				$ret[$i]["MaxExtraWorkInHolidays"] = $rec["MaxExtraWorkInHolidays"];
				$ret[$i]["ExtraWorkStartPoint"] = $rec["ExtraWorkStartPoint"];
				$i++;
			}
			return $ret;
		}

		// نوع ورود و خروج پیش فرض برای یک فرد بر می گرداند
		function GetPersonDefaultEnterExitType($PersonID)
		{
			$ret = array();
			$ret["EnterExitTypeID"] = 1;
			$ret["JobStatus"] = 'OFFICIAL';	
			$ret["StartTime"] = 420;
			$ret["EndTime"] = 870;
			$ret["title"] = 'اداری';
			$ret["CalendarID"] = 0;
			$ret["CalendarTitle"] = '-';
			$ret["CalculateExtraWorkBeforeStartTime"] = 'YES';
			$ret["MaxExtraWorkInHolidays"] = 24*60;
			$ret["ThursdayStartTime"] = 0;
			$ret["ThursdayEndTime"] = 24*60;
			$ret["ExtraWorkStartPoint"] = 0;
			$mysql = dbclass::getInstance();
			$res = $mysql->Execute("select *, Calendars.title as CalendarTitle, EnterExitTypes.title as ETitle from pas.PersonSettings
									LEFT JOIN pas.EnterExitTypes using (EnterExitTypeID)
									LEFT JOIN pas.Calendars using (CalendarID)  
									where PersonID='".$PersonID."'");
			if($rec=$res->FetchRow())
			{
				$ret["EnterExitTypeID"] = $rec["EnterExitTypeID"];
				$ret["JobStatus"] = $rec["JobStatus"];	
				$ret["StartTime"] = $rec["StartTime"];
				$ret["EndTime"] = $rec["EndTime"];
				$ret["title"] = $rec["ETitle"];
				$ret["CalendarID"] = $rec["CalendarID"];
				$ret["CalendarTitle"] = $rec["CalendarTitle"];
				$ret["CalculateExtraWorkBeforeStartTime"] = $rec["CalculateExtraWorkBeforeStartTime"];
				$ret["ExtraWorkUpperBound"] = $rec["ExtraWorkUpperBound"];
				$ret["CalculateExtraWorkInThursday"] = $rec["CalculateExtraWorkInThursday"];
				$ret["CalculateExtraWorkInFriday"] = $rec["CalculateExtraWorkInFriday"];
				$ret["CalculateExtraWorkInHolidays"] = $rec["CalculateExtraWorkInHolidays"];
				$ret["ThursdayStartTime"] = $rec["ThursdayStartTime"];
				$ret["ThursdayEndTime"] = $rec["ThursdayEndTime"];
				$ret["MaxExtraWorkInHolidays"] = $rec["MaxExtraWorkInHolidays"];
				$ret["ExtraWorkStartPoint"] = $rec["ExtraWorkStartPoint"];
			}
			return $ret;
		}
		
		//خروجی آرایه ای است که وضعیت را در هر روز مشخص می کند
		// این آرایه دو بعدی است که بعد اول آن شماره روز در ماه و بعد دوم آن یکی از موارد زیر است:
		// Tardiness: تاخیر
		// Haste: تعجیل
		// Absent: غیبت
		// LeaveTime: مرخصی ساعتی
		// ExtraWorkTime: اضافه کار
		// WorkTime:  - تنها ساعاتی که در بازه مجاز ورود و خروج بوده محسوب می شود - ساعت کاری
		// TotalPresentTime: کل ساعت حضور
		function CalculateAPersonStatusInAMounth($PersonID, $Year, $Month)
		{
			if($Month<7)
				$ToDay = 31;
			else if($Month<12)
				$ToDay = 30;
			else 
			{
				$r = $Year%33;
				if($r==1 || $r==5 || $r==13 || $r==17 || $r==22 || $r==26 || $r==30)
					$ToDay = 30; // کبیسه
				else
					$ToDay = 29; 
			}
			$FromDate = PASUtils::GetMiladiDate($Year, $Month, 1);
			$ToDate = PASUtils::GetMiladiDate($Year, $Month, $ToDay);
			return PASUtils::CalculateAPersonStatusInARange($PersonID, $FromDate, $ToDate);
		}
		
		function CalculateAPersonSummaryStatusInAMonth($PersonID, $Year, $Month, $ValidFloat, $ValidTardiness, $ValidHaste)
		{
			if($Month<7)
				$ToDay = 31;
			else if($Month<12)
				$ToDay = 30;
			else 
			{ 
				$r = $Year%33;
				if($r==1 || $r==5 || $r==13 || $r==17 || $r==22 || $r==26 || $r==30)
					$ToDay = 30; // کبیسه
				else
					$ToDay = 29; 
			}
			$FromDate = PASUtils::GetMiladiDate($Year, $Month, 1);
			$ToDate = PASUtils::GetMiladiDate($Year, $Month, $ToDay);
			return PASUtils::CalculateAPersonSummaryStatusInARange($PersonID, $FromDate, $ToDate, $ValidFloat, $ValidTardiness, $ValidHaste);
		}
		
		// تولید لیست کارمندان مجاز به استفاده در سیستم حضور و غیاب در یک واحد
		function GetPersonListOptions($UnitCode, $DefaultValue, $AllowAll = TRUE)
		{
			$PersonList = "";
			if($AllowAll)
				$PersonList = "<option value='0'>-";
			$mysql = dbclass::getInstance();
			$res = $mysql->Execute("select * from pas.ActivePersons where UnitCode='".$UnitCode."' order by PLName, PFName");
			while($arr_res=$res->FetchRow())
			{
				$PersonList .= "<option value='".$arr_res["PersonID"]."' ";
				if($DefaultValue==$arr_res["PersonID"])
					$PersonList .= " selected ";
				$PersonList .= ">".$arr_res["PLName"]." ".$arr_res["PFName"];
			}
			return 	$PersonList;
		}
		
		// تولید لیست انتظامات مجاز به استفاده از سیستم حضور و غیاب
		function GetGardsListOptions($DefaultValue, $AllowAll = TRUE)
		{
			$PersonList = "";
			if($AllowAll)
				$PersonList = "<option value='0'>-";
			$mysql = dbclass::getInstance();
			$res = $mysql->Execute("select * from pas.ActivePersons JOIN pas.WorkGroups using (WorkGroupID) where ExitAutomaticInHour24='YES' order by PLName, PFName");
			while($arr_res=$res->FetchRow())
			{
				$PersonList .= "<option value='".$arr_res["PersonID"]."' ";
				if($DefaultValue==$arr_res["PersonID"])
					$PersonList .= " selected ";
				$PersonList .= ">".$arr_res["PLName"]." ".$arr_res["PFName"];
			}
			return 	$PersonList;
		}

		// تولید لیست انتظامات مجاز به استفاده از سیستم حضور و غیاب
		function GetShiftPersonsThatAreNotGardListOptions($DefaultValue, $AllowAll = TRUE)
		{
			$PersonList = "";
			if($AllowAll)
				$PersonList = "<option value='0'>-";
			$mysql = dbclass::getInstance();
			$res = $mysql->Execute("select * from pas.ActivePersons JOIN pas.WorkGroups using (WorkGroupID) where ExitAutomaticInHour24='YES' order by PLName, PFName");
			while($arr_res=$res->FetchRow())
			{
				$PersonList .= "<option value='".$arr_res["PersonID"]."' ";
				if($DefaultValue==$arr_res["PersonID"])
					$PersonList .= " selected ";
				$PersonList .= ">".$arr_res["PLName"]." ".$arr_res["PFName"];
			}
			return 	$PersonList;
		}
		
		function FarsiDayNumberInWeek($EnglishDayName)
		{
			if($EnglishDayName=="Friday")
				return 7;
			if($EnglishDayName=="Thursday")
				return 6;
			if($EnglishDayName=="Wednesday")
				return 5;
			if($EnglishDayName=="Tuesday")
				return 4;
			if($EnglishDayName=="Monday")
				return 3;
			if($EnglishDayName=="Sunday")
				return 2;
			if($EnglishDayName=="Saturday")
				return 1;
		}
		
		function FarsiDayName($EnglishDayName)
		{
			if($EnglishDayName=="Friday")
				return "جمعه";
			if($EnglishDayName=="Thursday")
				return "پنجشنبه";
			if($EnglishDayName=="Wednesday")
				return "چهارشنبه";
			if($EnglishDayName=="Tuesday")
				return "سه شنبه";
			if($EnglishDayName=="Monday")
				return "دو شنبه";
			if($EnglishDayName=="Sunday")
				return "یکشنبه";
			if($EnglishDayName=="Saturday")
				return "شنبه";
		}
		
		function GetMonthName($month)
		{
			if($month==1)
				return "فروردین";
			if($month==2)
				return "اردیبهشت";
			if($month==3)
				return "خرداد";
			if($month==4)
				return "تیر";
			if($month==5)
				return "مرداد";
			if($month==6)
				return "شهریور";
			if($month==7)
				return "مهر";
			if($month==8)
				return "آبان";
			if($month==9)
				return "آذر";
			if($month==10)
				return "دی";
			if($month==11)
				return "بهمن";
			if($month==12)
				return "اسفند";
			return "";
		}
		
		// آیا روز ذکر شده جزو تعطیلات رسمی است؟ (تعطیلات آخر هفته محسوب نمی شود)
		function IsHoliday($ThisDate)
		{
			$mysql = dbclass::getInstance(config::$db_servers["slave"]["host"], config::$db_servers["slave"]["report_user"], config::$db_servers["slave"]["report_pass"]);
			$res = $mysql->Execute("select * from pas.holidays where HolidayDate='".$ThisDate."'");
			if($arr_res=$res->FetchRow())
				return true;		
			return false;
		}
		
		// با گرفتن کد شخص و سال و ماه مربوطه وضعیت کاری در آن سال و ماه را بر می گرداند
		// اگر یک وضعیت اختصاصی آن ماه تعریف نشده باشد وضعیت پیش فرض شخص را بر می گرداند
		function GetJobStatus($PersonID, $SelectedYear, $SelectedMonth)
		{
			$mysql = dbclass::getInstance();
			$query = "select JobStatus from PersonSpecialMonthesEnterExitType JOIN EnterExitTypes using (EnterExitTypeID) ";
			$query .= "where PersonID='".$PersonID."' and SelectedYear='".$SelectedYear."' and SelectedMonth='".$SelectedMonth."'";
			$res = $mysql->Execute($query);
			if($arr_res=$res->FetchRow())
			{
				return $arr_res["JobStatus"];
			}
			$query = "select JobStatus from PersonSettings JOIN EnterExitTypes using (EnterExitTypeID) ";
			$query .= "where PersonID='".$PersonID."' ";
			$res = $mysql->Execute($query);
			if($arr_res=$res->FetchRow())
			{
				return $arr_res["JobStatus"];
			}
			return "";
		}
		
		// با گرفتن سال و ماه و روز تاریخ میلادی می سازد به صورت رشته ای
		function GetMiladiDate($Year, $Month, $Day)
		{
			$Year = $Year-1300;
			if($Month<10 && strlen($Month)==1)
				$Month = "0".$Month;
			if($Day<10 && strlen($Day)==1)
				$Day = "0".$Day;
				
			return xdate($Year."/".$Month."/".$Day);
		}
		
		// آیا تاریخ ذکر شده در تعطیلات آخر هفته قرار دارد؟
		// فرمت تاریخ: 20090512
		function IsEndWeekVacation($CurDate)
		{
			$CurDate2  = mktime(0, 0, 0, substr($CurDate, 4, 2), substr($CurDate, 6, 2), substr($CurDate, 0, 4));
			if(date("l", $CurDate2)=="Friday" || date("l", $CurDate2)=="Thursday")
				return true;
			else
				return false;
		}
		
		// ایا روز ذکر شده جمعه است
		// فرمت تاریخ: 20090512
		function IsFriday($CurDate)
		{
			$CurDate2  = mktime(0, 0, 0, substr($CurDate, 4, 2), substr($CurDate, 6, 2), substr($CurDate, 0, 4));
			if(date("l", $CurDate2)=="Friday")
				return true;
			else
				return false;
		}

		// ایا روز ذکر شده پنجشنبه است
		// فرمت تاریخ: 20090512
		function IsThursday($CurDate)
		{
			$CurDate2  = mktime(0, 0, 0, substr($CurDate, 4, 2), substr($CurDate, 6, 2), substr($CurDate, 0, 4));
			if(date("l", $CurDate2)=="Thursday")
				return true;
			else
				return false;
		}
		
		// آیا دو بازه ساعتی ذکر شده با هم تداخل دارند؟
		function IsHourRangeOverlap($From1, $To1, $From2, $To2)
		{
			if($From1>$From2)
			{
				$TempFrom1 = $From1;
				$TempTo1= $To1;
				$From1 = $From2;
				$To1 = $To2;
				$From2 = $TempFrom1;
				$To2 = $TempTo1;
			}
			if($From2>=$To1)
				return false;
			else
				return true;
		}
		
		// در یک روز برای کلیه مرخصیهای ساعتی چک می کند بازه جدید تداخل نداشته باشد
		function IsOverlapInHourlyLeaves($PersonID, $CurDate, $NewFrom, $NewTo)
		{
			$mysql = dbclass::getInstance();
			$res = $mysql->Execute("select * from pas.HourlyLeaves where PersonID='".$PersonID."' and LeaveDate='".$CurDate."'");
			while($arr_res=$res->FetchRow())
			{
				$From1 = $arr_res["FromHour"]*60+$arr_res["FromMin"];
				$To1 = $arr_res["ToHour"]*60+$arr_res["ToMin"];
				if(PASUtils::IsHourRangeOverlap($From1, $To1, $NewFrom, $NewTo))
				{
					return true;
				}
			}
			return false;
		}
		
		// ایا در روز ذکر شده شخص مرخصی روزانه دارد؟
		// توجه: مرخصیهای روزانه از تاریخ تا تاریخ ثبت شده و تاریخ انتها مرخصی محسوب نمی شود	
		function HasDailyLeave($PersonID, $CurDate)
		{
			//echo "select * from pas.DailyLeaves where PersonID='".$PersonID."' and FromDate<='".$CurDate."' and ToDate>'".$CurDate."'"."<br>";
			$mysql = dbclass::getInstance();
			$res = $mysql->Execute("select * from pas.DailyLeaves where PersonID='".$PersonID."' and FromDate<='".$CurDate."' and ToDate>'".$CurDate."'");
			if($arr_res=$res->FetchRow())
				return true;
			else
				return false;
		}

		// ایا در روز ذکر شده شخص ماموریت روزانه دارد؟
		// توجه: ماموریتهای روزانه از تاریخ تا تاریخ ثبت شده و تاریخ انتها ماموریت محسوب نمی شود	
		function HasDailyMission($PersonID, $CurDate)
		{
			$mysql = dbclass::getInstance(config::$db_servers["slave"]["host"], config::$db_servers["slave"]["report_user"], config::$db_servers["slave"]["report_pass"]);
			$res = $mysql->Execute("select * from pas.DailyMissions where PersonID='".$PersonID."' and FromDate<='".$CurDate."' and ToDate>'".$CurDate."'");
			if($arr_res=$res->FetchRow())
				return true;
			else
				return false;
		}
		
		//  - فرمت به صورت 2008-01-12 - روز قبل را بر می گرداند
		function GetPreviousDate($CurDate)
		{
			$CurDate=shdate($CurDate);
			//echo $CurDate."<br>";
			$CurDay = substr($CurDate,0,2);
			$CurMonth = substr($CurDate,3,2);
			$CurYear = substr($CurDate,6,2);
			
			$CurDay--;
			if($CurDay==0)
			{
				$CurMonth--;
				if($CurMonth==0)
				{
					$CurYear--;
					$CurMonth = 12;
					$CurDay = 29;
				}
				else if($CurMonth<7)
					$CurDay = 31;
				else 
					$CurDay = 30;
			}
			if(strlen($CurDay)<2)
				$CurDay = "0".$CurDay;
			if(strlen($CurMonth)<2)
				$CurMonth = "0".$CurMonth;
			$CurDate = xdate($CurYear."/".$CurMonth."/".$CurDay);
			return substr($CurDate,0,4)."-".substr($CurDate,4,2)."-".substr($CurDate,6,2);
		}
		
		//  - فرمت به صورت 2008-01-12 - روز بعد را بر می گرداند
		function GetNextDate($CurDate)
		{
			$CurDate=shdate($CurDate);
			$CurDay = substr($CurDate,0,2);
			$CurMonth = substr($CurDate,3,2);
			$CurYear = substr($CurDate,6,2);
		
			if($CurMonth<7)
			{
				$CurDay++;
				if($CurDay>31)
				{
					$CurDay = 1;
					$CurMonth++;
				}
			}
			else
			{
				$CurDay++;
				if($CurDay>30)
				{
					$CurDay = 1;
					$CurMonth++;
					if($CurMonth>12)
					{
						$CurMonth=1;
						$CurYear++;
					}
				}
			}
			if(strlen($CurDay)<2)
				$CurDay = "0".$CurDay;
			if(strlen($CurMonth)<2)
				$CurMonth = "0".$CurMonth;
			$CurDate = xdate($CurYear."/".$CurMonth."/".$CurDay);
			return substr($CurDate,0,4)."-".substr($CurDate,4,2)."-".substr($CurDate,6,2);
		}
		
		// آیا در بازه ذکر شده مرخصی روزانه یا ساعتی وجود دارد؟
		function HasAnyLeaveInPeriod($PersonID, $FromDate, $ToDate)
		{
			$CurDate = $FromDate;
			$i=0;
			while($CurDate<>$ToDate && $i<30)
			{
				if(PASUtils::HasDailyLeave($PersonID, $CurDate))
					return true;
				$i++;
				$CurDate = PASUtils::GetNextDate($CurDate);
			}
			return false;
		}
		
		// با گرفتن یک زمان به دقیقه چک می کند داخل ساعت کاری اداری هست یا خیر
		function IsInOfficialTime($CurTimeInMinutes)
		{
			if(OFFICIAL_START_TIME<=$CurTimeInMinutes && OFFICIAL_END_TIME>=$CurTimeInMinutes)
				return true;
			else
				return false;
		}
		
		// آیا ساعت ورود و خروج ذکر شده با ساعات ورود و خروج ثبت شده دارای تداخل می باشد یا خیر
		function IsOverlapTimeExistsInEnterExitTimes($CurID, $PersonID, $EnterTime, $ExitTime)
		{
			if($CurID!="")
				$cond = " and EnterExitTimeID<>'".$CurID."'";
			else
				$cond = "";
			$mysql = dbclass::getInstance();
			// تعداد رکوردهای غیر متداخل را بدست می آورد
			// ممکن است خود رکورد فعلی هم جزو آنها باشد یا نباشد
			$query = "select count(*) as TotalCount from pas.EnterExitTimes where PersonID='".$PersonID."' and (ExitTime<='".$EnterTime."' or EnterTime>='".$ExitTime."')";
			$res = $mysql->Execute($query);
			if($arr_res=$res->FetchRow())
				$c1 = $arr_res["TotalCount"];
			// تعداد کل رکوردها را دست می آورد
			$query = "select count(*) as TotalCount from pas.EnterExitTimes where PersonID='".$PersonID."' ".$cond;
			$res = $mysql->Execute($query);
			if($arr_res=$res->FetchRow())
				$c2 = $arr_res["TotalCount"];
			if($c1<$c2)
				return true;
			return false;
		}
		
		// اولین مورد دارای تداخل با زمان ذکر شده را بر می گرداند
		function GetOverlapTime($CurID, $PersonID, $EnterTime, $ExitTime)
		{
			$mysql = dbclass::getInstance();
			$query = "select EnterExitTimeID, g2jF(EnterTime) as gEnterTime, g2jF(ExitTime) as gExitTime, substr(EnterTime, 12, 5) as StartTime, substr(ExitTime, 12,5) as EndTime from 
						pas.EnterExitTimes where PersonID='".$PersonID."' and ((EnterTime<='".$EnterTime."' and EnterTime>='".$EnterTime."') or (EnterTime<='".$ExitTime."' and ExitTime>='".$ExitTime."'))";
			$res = $mysql->Execute($query);
			//return "زمان ورود: ".$rec["gEnterTime"]." - زمان خروج: ".$rec["StartTime"]." - ".$rec["gExitTime"]." ".$rec["EndTime"]." (".$rec["EnterExitTimeID"].")";
			if($rec=$res->FetchRow())
				return "زمان ورود: ".$rec["gEnterTime"]." ".$rec["StartTime"]." - زمان خروج: ".$rec["gExitTime"]." ".$rec["EndTime"]." ";
			else
				return "-";
		}
		
		// لیست ورود و خروجها را در یک رشته بر می گرداند در انتهای هر ورود و خروج <br> گذاشته می شود
		function CreateEnterExitList($PersonID, $SelectedDate)
		{
			$ret = "";
			$FromDate = substr($SelectedDate,0,4)."-".substr($SelectedDate,4,2)."-".substr($SelectedDate,6,2)." 00:00:01";
			$ToDate = substr($SelectedDate,0,4)."-".substr($SelectedDate,4,2)."-".substr($SelectedDate,6,2)." 23:59:0";
			
			$mysql = dbclass::getInstance();
			$res = $mysql->Execute("select * from pas.EnterExitTimes where PersonID='".$PersonID."' and EnterTime between '".$FromDate."' and '".$ToDate."'");
			//if($_SESSION["UserID"]=="omid")
			//	echo "select * from pas.EnterExitTimes where PersonID='".$PersonID."' and EnterTime between '".$FromDate."' and '".$ToDate."'";
			
			$i = 0;
			while($arr_res=$res->FetchRow())
			{
				if($i>0)
					$ret .= "<br>";
				$ret .= substr($arr_res["EnterTime"],11,5)." - ".substr($arr_res["ExitTime"],11,5);
				$i++;
			}
			return $ret;
		}
		
		function ShowTimeInHourAndMinute($TotalMinutes)
		{
			$h = (int)($TotalMinutes/60);
			$m = $TotalMinutes%60;
			if($h<10)
				$h = "0".$h;
			if($m<10)
				$m = "0".$m;
			return $h.":".$m;
		}
		
		function ShowTimeInHourAndMinuteOrEmpty($TotalMinutes)
		{
			$h = (int)($TotalMinutes/60);
			$m = $TotalMinutes%60;
			if($h<10)
				$h = "0".$h;
			if($m<10)
				$m = "0".$m;
			if($h.":".$m!="00:00")
				return $h.":".$m;
			else
				return "-";
		}
		
		//  تعیین می کند در آن روز شخص چه نوع کاری داشته است
		// پیش فرض نوع کاری و همچنین لیست انواع ورود و خروج اختصاصی تعریف شده به صورت یک لیست و روز مربوطه پارارمترهای این تابع هستند
		// اطلاعات نوع کاری در یک آرایه بر گردانده می شود که ایندکسهای آن عبارتند از:
		// id: کد نوع کاری
		// StartTime: زمان شروع کار به دقیقه
		// EndTime: زمان پایان کار به دقیقه
		// JobStatus: نوع کار - OFFICIAL - PARTTIME - 24HOUR
		// title: عنوان نوع کاری
		// CalendarID: کد تقویم کاری منتسب
		// CalendarTitle: عنوان تقویم کاری منتسب
		function GetEnterExitTypeInDay($EnterExitDefaultType, $EnterExitSpecialList, $CurDate)
		{
			$ret = array();
			$CurDate=shdate($CurDate);
			$CurDay = substr($CurDate,0,2);
			$CurMonth = substr($CurDate,3,2);
			$CurYear = substr($CurDate,6,2);
			$ret["id"] = $EnterExitDefaultType["EnterExitTypeID"];
			$ret["JobStatus"] = $EnterExitDefaultType["JobStatus"];
			$ret["StartTime"] = $EnterExitDefaultType["StartTime"];
			$ret["EndTime"] = $EnterExitDefaultType["EndTime"];
			$ret["title"] = $EnterExitDefaultType["title"];
			$ret["CalendarID"] = $EnterExitDefaultType["CalendarID"];
			$ret["CalendarTitle"] = $EnterExitDefaultType["CalendarTitle"];
			$ret["CalculateExtraWorkBeforeStartTime"] = $EnterExitDefaultType["CalculateExtraWorkBeforeStartTime"];
			$ret["ExtraWorkUpperBound"] = $EnterExitDefaultType["ExtraWorkUpperBound"];
			$ret["CalculateExtraWorkInThursday"] = $EnterExitDefaultType["CalculateExtraWorkInThursday"];
			$ret["CalculateExtraWorkInFriday"] = $EnterExitDefaultType["CalculateExtraWorkInFriday"];
			$ret["CalculateExtraWorkInHolidays"] = $EnterExitDefaultType["CalculateExtraWorkInHolidays"];
			$ret["ThursdayStartTime"] = $EnterExitDefaultType["ThursdayStartTime"];
			$ret["ThursdayEndTime"] = $EnterExitDefaultType["ThursdayEndTime"];
			$ret["MaxExtraWorkInHolidays"] = $EnterExitDefaultType["MaxExtraWorkInHolidays"];
			$ret["ExtraWorkStartPoint"] = $EnterExitDefaultType["ExtraWorkStartPoint"];
			
			//echo $CurDate."<br>";
			for($i=0; $i<count($EnterExitSpecialList); $i++)
			{
				$StartRange = $EnterExitSpecialList[$i]["SelectedYear"]*10000+$EnterExitSpecialList[$i]["SelectedMonth"]*100+$EnterExitSpecialList[$i]["FromDay"];
				$EndRange = $EnterExitSpecialList[$i]["SelectedYear"]*10000+$EnterExitSpecialList[$i]["SelectedMonth"]*100+$EnterExitSpecialList[$i]["ToDay"];
				$SelectedPoint = (1300+$CurYear)*10000+$CurMonth*100+$CurDate;
				// اگر تاریخ انتخابی در بین بازه تعریف شده یک نوع ورود و خروج خاص برای فرد بود آن را بر می گرداند
				if($SelectedPoint>=$StartRange && $SelectedPoint<=$EndRange)
				{
					//echo $StartRange."-".$EndRange."<br>";
					$ret["id"] = $EnterExitSpecialList[$i]["EnterExitTypeID"];
					$ret["JobStatus"] = $EnterExitSpecialList[$i]["JobStatus"];
					$ret["StartTime"] = $EnterExitSpecialList[$i]["StartTime"];
					$ret["EndTime"] = $EnterExitSpecialList[$i]["EndTime"];
					$ret["title"] = $EnterExitSpecialList[$i]["title"];
					$ret["CalendarID"] = $EnterExitSpecialList[$i]["CalendarID"];
					$ret["CalendarTitle"] = $EnterExitSpecialList[$i]["CalendarTitle"];
					$ret["CalculateExtraWorkBeforeStartTime"] = $EnterExitSpecialList[$i]["CalculateExtraWorkBeforeStartTime"];
					$ret["ExtraWorkUpperBound"] = $EnterExitSpecialList[$i]["ExtraWorkUpperBound"];
					$ret["CalculateExtraWorkInThursday"] = $EnterExitSpecialList[$i]["CalculateExtraWorkInThursday"];
					$ret["CalculateExtraWorkInFriday"] = $EnterExitSpecialList[$i]["CalculateExtraWorkInFriday"];
					$ret["CalculateExtraWorkInHolidays"] = $EnterExitSpecialList[$i]["CalculateExtraWorkInHolidays"];
					$ret["ThursdayStartTime"] = $EnterExitSpecialList[$i]["ThursdayStartTime"];
					$ret["ThursdayEndTime"] = $EnterExitSpecialList[$i]["ThursdayEndTime"];
					$ret["MaxExtraWorkInHolidays"] = $EnterExitSpecialList[$i]["MaxExtraWorkInHolidays"];
					$ret["ExtraWorkStartPoint"] = $EnterExitSpecialList[$i]["ExtraWorkStartPoint"];
					return $ret;
				}
			}
			//echo $CurDate." -> ".$ret["title"]."<br>";
			return $ret;				
		}

		//خروجی آرایه ای است که وضعیت را در هر روز مشخص می کند
		// این آرایه دو بعدی است که بعد اول آن شماره روز در ماه و بعد دوم آن یکی از موارد زیر است:
		// علاوه بر محاسبه و برگرداندن نتیجه این متد نتیجه را برای استفاده بعدی در بانک اطلاعاتی هم ذخیره می کند
		// Tardiness: تاخیر
		// Haste: تعجیل
		// Absent: غیبت
		// LeaveTime: مرخصی ساعتی
		// ExtraWorkTime: اضافه کار
		// WorkTime:  - تنها ساعاتی که در بازه مجاز ورود و خروج بوده محسوب می شود - ساعت کاری
		// TotalPresentTime: کل ساعت حضور
		function CalculateAPersonStatusInARange($PersonID, $FromDate, $ToDate)
		{
			$MapEnterExit = array();
			$now = date("Ymd"); 
			$yy = substr($now,0,4); 
			$mm = substr($now,4,2); 
			$dd = substr($now,6,2);
			$CurrentDay = $yy."/".$mm."/".$dd;
			$mysql = dbclass::getInstance();
			
			$MonthStatusArray = array();
			$FromDate = substr($FromDate,0,4)."-".substr($FromDate,4,2)."-".substr($FromDate,6,2);
			$ToDate = substr($ToDate,0,4)."-".substr($ToDate,4,2)."-".substr($ToDate,6,2);
			// می خواهیم خود آخرین روز هم در محاسبات باشد
			$ToDate = PASUtils::GetNextDate($ToDate);
			$EnterExitSpecialList = PASUtils::GetPersonSpecialEnterExitTypes($PersonID);
			$DefaultEnterExitType = PASUtils::GetPersonDefaultEnterExitType($PersonID);
			//echo $PersonID." (2-2)<br>";
			$DaysCount=0;
			$IsHoliday = false;
			$IsFriday = false;
			$IsThursday = false;

			// Fromdate with '-' and CurDate widthout '-'
			while($FromDate!=$ToDate)
			{
				$IsHoliday = false;
				$IsFriday = false;
				$IsThursday = false;
				$IsLeave = "NO";
				
				$DaysCount++;
				$FarsiCurDate = shdate($FromDate);
				
				$CurYear = "13".substr($FarsiCurDate,6,2);
				// روز جاری در تاریخ شمسی
				$Day = substr($FarsiCurDate,0,2);
				// ماه جاری در تاریخ شمسی
				$Month = substr($FarsiCurDate,3,2);
				
				$CurDate = substr($FromDate, 0, 4).substr($FromDate, 5, 2).substr($FromDate, 8, 2);

				if(PASUtils::IsFriday($CurDate))
					$IsFriday = true;
				if(PASUtils::IsThursday($CurDate))
					$IsThursday = true;
				if(PASUtils::IsHoliday($CurDate))
					$IsHoliday = true;
				
				// اگر قبلا نتیجه محاسبه آن روز موجود بود از آن استفاده می کند
				$res = $mysql->Execute("select * from pas.DailyCalculationSummary where PersonID='".$PersonID."' and CalculatedDate='".$FromDate."'");
				if($arr_res=$res->FetchRow())
				{
					$MonthStatusArray[$DaysCount]["TotalPresentTime"] = $arr_res["PresentTime"];
					$MonthStatusArray[$DaysCount]["Tardiness"]  = $arr_res["TardinessTime"];
					$MonthStatusArray[$DaysCount]["Absent"] = $arr_res["AbsentTime"];
					$MonthStatusArray[$DaysCount]["Haste"] = $arr_res["HasteTime"];
					$MonthStatusArray[$DaysCount]["ExtraWorkTime"] = $arr_res["ExtraWorkTime"];
					$MonthStatusArray[$DaysCount]["ExtraWorkBeforeStartTime"] = $arr_res["ExtraWorkBeforeStartTime"];
					$MonthStatusArray[$DaysCount]["WorkTime"] = $arr_res["WorkTime"];
					$MonthStatusArray[$DaysCount]["LeaveTime"] = $arr_res["LeaveTime"];
					$MonthStatusArray[$DaysCount]["Mission"] = $arr_res["MissionTime"];
				}
				else
				{
					if(PASUtils::HasDailyLeave($PersonID, $CurDate))
						$IsLeave = "YES";
					
					$TotalPresentTime = 0;
					$IsStartWorkingDay = false;
					$IsEndWorkingDay = false;
					$DayStatus = "Vacation";
					// برای روز جاری مشخص می کند که نوع ورود و خروج برای فرد چه تعریف شده است
					unset($EnterExitType);	
					unset($DateArray);		
					$EnterExitType = PASUtils::GetEnterExitTypeInDay($DefaultEnterExitType, $EnterExitSpecialList, $FromDate);
					//if($_SESSION["PersonID"]=="201309")
					//	echo $FromDate." - ".$EnterExitType["title"]."<br>";				
					
					if($EnterExitType["JobStatus"]=="SHIFT") // در صورتیکه نوع کاری شیفت باشد لوح ششیفت تعریف شده برای آن روز را استخراج می کند
					{
						$res = $mysql->Execute("select * from pas.PersonEnterExitMaps where PersonID='".$PersonID."' and MapYear='".$CurYear."' and MapMonth='".$Month."' and MapDay='".$Day."'");
						if($rec = $res->FetchRow())
						{
							//echo $rec["MapArray"];
							for($i=0; $i<1440; $i++)
								$MapEnterExit[$i] = substr($rec["MapArray"], $i, 1);
						}
						else
						{
							for($i=0; $i<1440; $i++)
								$MapEnterExit[$i] = "0";
						}
					}
					
					// در صورتیکه نوع کاری شیفتی ۲۴ ساعته باشد تعیین می کند که روز جاری روز شروع کاری است یا روز اختمام کاری یا هیچکدام
					if($EnterExitType["JobStatus"]=="24HOUR")
					{
						if(PASUtils::IsWorkingDayAccordingToCalendar($EnterExitType["CalendarID"], $Day))
						{
							$IsStartWorkingDay = true;
							$DayStatus = "StartWorkingDay";
						}
						else if(PASUtils::IsWorkingDayAccordingToCalendar($EnterExitType["CalendarID"], $Day-1))
						{
							$IsEndWorkingDay = true;
							$DayStatus = "EndWorkingDay";
						}
						$DateArray = PASUtils::CreateADateEnterExitStatusFor24HOURShift($PersonID, $CurDate, $DayStatus);
					}				
					else if($EnterExitType["JobStatus"]=="SHIFT")
					{
						// آرایه حضور و غیاب در روز مشابه اداریها استخراج می شود فقط اگر خروج ثبت نشده بود پیش فرض آخر روز را خروج در نظر می گیرد
						$DateArray = PASUtils::CreateADateEnterExitStatus($PersonID, $CurDate, FALSE);
					}
					else
						$DateArray = PASUtils::CreateADateEnterExitStatus($PersonID, $CurDate);
					
					$Tardiness = 0;
					
					if($EnterExitType["JobStatus"]=="24HOUR")
						$MonthStatusArray[$DaysCount]["TotalPresentTime"] = $DateArray["TotalPresentTime"];
					else
						$MonthStatusArray[$DaysCount]["TotalPresentTime"] = $DateArray["TotalPresentTime"];
					
					// محاسبه تاخیر
					// در صورتیکه نوع کاری اداری باشد تاخیر محاسبه می شود
					if($EnterExitType["JobStatus"]=="OFFICIAL")
					{
						for($i=$EnterExitType["StartTime"]; $i<$EnterExitType["EndTime"]; $i++)
						{
							if($DateArray["DayTimes"][$i]>0)
								break;
							$Tardiness++;
						}
					}
					//echo $Tardiness."<br>";
					// اگر شیفتی ۲۴ ساعته بود در صورتیکه روز کاری باشد تاخیر را به گونه ای دیگر محاسبه می کند
					if($EnterExitType["JobStatus"]=="24HOUR")
					{
						// در صورتیکه روز شروع کاری باشد تاخیر محاسبه می شود
						if($IsStartWorkingDay)
						{
							for($i=$EnterExitType["StartTime"]; $i<1440; $i++)
							{
								if($DateArray["DayTimes"][$i]>0)
									break;
								$Tardiness++;
							}
						}
					}
					
					$MonthStatusArray[$DaysCount]["Tardiness"] = $Tardiness;
					
					if($EnterExitType["JobStatus"]=="24HOUR")
					{
						//در صورتیکه کل زمان کاری تاخیر باشد یعتی شخص حضور نداشته و به جای تاخیر غیبت محسوب می شود
						if($IsStartWorkingDay && $Tardiness==1440-$EnterExitType["StartTime"])
						{
							$Tardiness = 0;
							$Haste = 0;
							$Absent = 1440-$EnterExitType["StartTime"];
							$MonthStatusArray[$DaysCount]["Tardiness"] = 0;
							$MonthStatusArray[$DaysCount]["Absent"] = $Absent;
							$MonthStatusArray[$DaysCount]["Haste"] = $Haste;
						}
						else
						{
							$Haste = 0;
							// محاسبه تعجیل
							// در صورتی محاسبه می شود که دیروز روز کاری باشد
							if($IsEndWorkingDay)
							{
								for($i=$EnterExitType["EndTime"]; $i>0; $i--)
								{
									if($DateArray["DayTimes"][$i]>0)
										break;
									$Haste++;
								}
								$MonthStatusArray[$DaysCount]["Haste"] = $Haste;
							}
							else
								$MonthStatusArray[$DaysCount]["Haste"] = 0;
							
							// در صورتیکه کل مدت تعجیل باشد در اینصورت تماما غیبت محسوب می شود و تعجیل را صفر می کند
							if($IsEndWorkingDay && $Haste==$EnterExitType["StartTime"])
							{
								$MonthStatusArray[$DaysCount]["Haste"] =  0;
								$MonthStatusArray[$DaysCount]["Absent"] =  $Haste;
							}
							else
							{
								//محاسبه غیبت 
								$AbsentFlag = 0;
								$Absent = -1*($Haste+$Tardiness);
								// اگر روز قبل روز کاری باشد یا اینکه امروز روز کاری باشد نحوه محاسبه غیبت برای شیفتی ۲۴ ساعته متفاوت است
								if($IsEndWorkingDay)
								{
									for($i=0; $i<=$EnterExitType["EndTime"]; $i++)
									{
										if($DateArray["DayTimes"][$i]==0)
										{
											$Absent++;
											$AbsentFlag = 1;
										}
									}
								}
								else if($IsStartWorkingDay)
								{
									for($i=$EnterExitType["StartTime"]; $i<=1440; $i++)
									{
										if($DateArray["DayTimes"][$i]==0)
										{
											$Absent++;
											$AbsentFlag = 1;
										}
									}
								}
								
								 // در صورتیکه فضای خالی وجود داشته باشد به دلیل نیم دقیقه های سلول اول و آخر یک دقیقه به کل غیبت باید اضافه شود
								if($AbsentFlag==1 && $Absent>0)
									$Absent++;
								$MonthStatusArray[$DaysCount]["Absent"] = $Absent;
							}
							
						}
				
					}
					else if($EnterExitType["JobStatus"]=="OFFICIAL")
					{
						// اگر میزان تاخیر برابر کل زمان کاری باشد دیگر آن را تاخیر حساب نمی کند و غیبت در نظر می گیرد
						if($Tardiness==$EnterExitType["EndTime"]-$EnterExitType["StartTime"])
						{
							$Tardiness = 0;
							$Haste = 0;
							$Absent = $EnterExitType["EndTime"]-$EnterExitType["StartTime"];
							$MonthStatusArray[$DaysCount]["Tardiness"] = 0;
							$MonthStatusArray[$DaysCount]["Absent"] = $Absent;
							$MonthStatusArray[$DaysCount]["Haste"] = $Haste;
						}
						else
						{
							$Haste = 0;
							// محاسبه تعجیل
							for($i=$EnterExitType["EndTime"]; $i>$EnterExitType["StartTime"]; $i--)
							{
								if($DateArray["DayTimes"][$i]>0)
									break;
								$Haste++;
							}
							$MonthStatusArray[$DaysCount]["Haste"] = $Haste;
							
							//محاسبه غیبت 
							$AbsentFlag = 0;
							$Absent = -1*($Haste+$Tardiness);
							for($i=$EnterExitType["StartTime"]; $i<=$EnterExitType["EndTime"]; $i++)
							{
								if($DateArray["DayTimes"][$i]==0)
								{
									$Absent++;
									$AbsentFlag = 1;
								}
							}
	
							 // در صورتیکه فضای خالی وجود داشته باشد به دلیل نیم دقیقه های سلول اول و آخر یک دقیقه به کل غیبت باید اضافه شود
							if($AbsentFlag==1 && $Absent>0)
								$Absent++;
							$MonthStatusArray[$DaysCount]["Absent"] = $Absent;
	
						}
					}
					else if($EnterExitType["JobStatus"]=="SHIFT")
					{
						
						$Absent = 0;
						$MonthStatusArray[$DaysCount]["Tardiness"] = 0;
						for($i=1; $i<1440; $i++)
						{
							// اگر طبق لوح کشیک باید حضور می داشته و حضور نداشته باشد غیبت محسوب می شود
							if($DateArray["DayTimes"][$i]==0 && $MapEnterExit[$i]=="1")
								$Absent++;
						}
						$MonthStatusArray[$DaysCount]["Absent"] = $Absent;
						$MonthStatusArray[$DaysCount]["Haste"] = 0;					
					}
					else if($EnterExitType["JobStatus"]=="PARTTIME") // در صورتیکه پاره وقت باشد اصلا تاخیر و تعجیل و غیبت محاسبه نمی شود
					{
						$MonthStatusArray[$DaysCount]["Tardiness"] = 0;
						$MonthStatusArray[$DaysCount]["Absent"] = 0;
						$MonthStatusArray[$DaysCount]["Haste"] = 0;					
					}
					
					// اضافه کار و کارکرد و مرخصی
					// ماموریت ساعتی هم به صورت جدا و هم داخل کارکرد و اضافه کار وجود دارد
					$WorkTime = 0;
					$ExtraWorkTime = 0;
					$LeaveTime = 0;
					$Mission = 0;
					$ExtraWorkBeforeStartTime = 0;
					//echo "*".$EnterExitType["MaxExtraWorkInHolidays"]."*<br>";
					$LastMinIsExtraWork = false;

					for($i=1; $i<1440; $i++)
					{
						if($DateArray["DayTimes"][$i]>0)
						{
							if($DateArray["DayTimes"][$i]==3)
								$Mission++;
							
							if($EnterExitType["JobStatus"]=="OFFICIAL")
							{
								if($i>=$EnterExitType["StartTime"] && $i<=$EnterExitType["EndTime"])
								{
									if($DateArray["DayTimes"][$i]==1 || $DateArray["DayTimes"][$i]==3)
										$WorkTime++;
									else if($DateArray["DayTimes"][$i]==2)
										$LeaveTime++;
									
									if($IsThursday)
									{
										// برای پنجشنبه ها بازه مجاز ذکر شده را هم به عنوان اضافه کار محسوب می کند
										if($i>=$EnterExitType["ThursdayStartTime"] && $i<=$EnterExitType["ThursdayEndTime"])
										{
											$WorkTime--;
											$ExtraWorkTime++;
										} 
									}
								}
								else // خارج از محدوده مجاز کار اداری - یعنی به صورت پیش فرض اضافه کار
								{
									// در صورتیکه اضافه کار اول وقت مجاز بود یا اینکه زمان بعد از اول وقت بود
									if($EnterExitType["CalculateExtraWorkBeforeStartTime"]=="YES" || $i>=$EnterExitType["StartTime"])
									{
										// اگر جمعه ها اضافه کار مجاز بود یا اینکه امروز جمعه نبود
										if($EnterExitType["CalculateExtraWorkInFriday"]=="YES" || !$IsFriday)
										{
											// اگر پنجشنبه ها اضافه کار مجاز بود یا اینکه امروز پنجشنبه نبود
											if($EnterExitType["CalculateExtraWorkInThursday"]=="YES" || !$IsThursday)
											{		
												// اگر روزهای تعطیل غیر پنجشنبه و جمعه ها اضافه کار مجاز بود یا اینکه امروز تعطیل رسمی نبود
												if($EnterExitType["CalculateExtraWorkInHolidays"]=="YES" || !$IsHoliday)
												{		
													// در صورتیکه حد بالای محاسبه اضافه کار وجود نداشت یا در محدوده پایینتر از آن حد قرار داشتیم			
													if($EnterExitType["ExtraWorkUpperBound"]==0 || $i<=$EnterExitType["ExtraWorkUpperBound"])
													{
														// اگر روز تعطیل نبود باید از زمان شروع اضافه کار گذشته باشد تا آن را محاسبه کند																				
														if($i>=$EnterExitType["ExtraWorkStartPoint"] || $IsFriday || $IsThursday || $IsHoliday)
														{ 														
															if($DateArray["DayTimes"][$i]==1 || $DateArray["DayTimes"][$i]==3)
															{   
																if($IsThursday)
																{
																	// برای روزهای پنجشنبه چک می کند بازه اضافه کار مجاز باشد
																	if($i>=$EnterExitType["ThursdayStartTime"] && $i<=$EnterExitType["ThursdayEndTime"])
																	{
																		$ExtraWorkTime++;
																		if($i<$EnterExitType["StartTime"])
																			$ExtraWorkBeforeStartTime++;
																	}
																}
																else
																{
																	$ExtraWorkTime++;
																	if($i<$EnterExitType["StartTime"])
																		$ExtraWorkBeforeStartTime++;
																}
															}
														}	
													}
												}
											}
										}
									} // طبق درخواست آقای دبیری به شماره 14071 برای روزهای تعطیل محدودیت شروع و پایان برای اضافه کار نداریم
									else if($IsFriday || $IsThursday || $IsHoliday)
									{
										if($DateArray["DayTimes"][$i]==1 || $DateArray["DayTimes"][$i]==3)
										{
												if($IsFriday && $EnterExitType["CalculateExtraWorkInFriday"]=="YES")
												{
													$ExtraWorkTime++;
												}
												else if($IsThursday && $EnterExitType["CalculateExtraWorkInThursday"]=="YES")
												{
													// برای روزهای پنجشنبه بازه مجاز هم چک می شود
													if($i>=$EnterExitType["ThursdayStartTime"] && $i<=$EnterExitType["ThursdayEndTime"]) 
														$ExtraWorkTime++;
												}
												else if($IsHoliday && $EnterExitType["CalculateExtraWorkInHolidays"]=="YES")
												{
													
													$ExtraWorkTime++;
												}
										}
									}
								}
							}
							else if($EnterExitType["JobStatus"]=="24HOUR")
							{
								if($IsStartWorkingDay)
								{
									if($i>=$EnterExitType["StartTime"])
									{
										if($DateArray["DayTimes"][$i]==1 || $DateArray["DayTimes"][$i]==3)
											$WorkTime++;
										else if($DateArray["DayTimes"][$i]==2)
											$LeaveTime++;
									}
									else
									{
										if($DateArray["DayTimes"][$i]==1 || $DateArray["DayTimes"][$i]==3)
										{
											$ExtraWorkTime++;
										}
									}
								}
								else if($IsEndWorkingDay)
								{
									if($i<=$EnterExitType["EndTime"])
									{
										if($DateArray["DayTimes"][$i]==1 || $DateArray["DayTimes"][$i]==3)
											$WorkTime++;
										else if($DateArray["DayTimes"][$i]==2)
											$LeaveTime++;
									}
									else
									{
										if($DateArray["DayTimes"][$i]==1 || $DateArray["DayTimes"][$i]==3)
											$ExtraWorkTime++;
									}
								}
								else // در صورتیکه نه روز شروع کاری بود نه روز خاتمه کاری
								{
									if($DateArray["DayTimes"][$i]==1 || $DateArray["DayTimes"][$i]==3)
											$ExtraWorkTime++;
								}
							}
							else if($EnterExitType["JobStatus"]=="SHIFT") // برای شیفتیها - محاشبه زمان کاری - مرخصی و اضافه کاری
							{
								if($DateArray["DayTimes"][$i]==1 || $DateArray["DayTimes"][$i]==3)
								{
									$WorkTime++;
								}
								// اگر طبق لوح کشیک نباید حضور می داشته و حضور داشته جزو اضافه کار محسوب می شود
								if(($DateArray["DayTimes"][$i]=="1" || $DateArray["DayTimes"][$i]=="3") && $MapEnterExit[$i]=="0")
								{
									$CalcExtra = true;
									/*
									// در اینجا کنترل می شود که زمان اضافه کار از زمان اولین نقطه مربوط به لوح کشیک حداقل فاصله ای داشته باشد
									// برای مواردی که طرف کمی زودتر یا دیرتر از شیفت کارت زده است
									// این زمان ۲۰ دقیقه در نظر گرفته شده است
									$AllowedTelorance = 20;
									$CalcExtra = false;
									if($LastMinIsExtraWork) // اگر این دقیقه ادامه اضافه کار از قبل باشد
										$CalcExtra = true;
									else 
									{
										$ProgrammedMapStartedSoonerThanNMinute = false;
										for($k=$i; $k<$i+$AllowedTelorance && $k<1440; $k++)
										{
											if($MapEnterExit[$k]=="1")
											{
												$ProgrammedMapStartedSoonerThanNMinute = true;
												break;
											}
										}
										$DistanceFromEndOfMap = 0;
										$ProgrammedMapEndedSoonerThanNMinute = false;
										for($k=$i; $k>$i-$AllowedTelorance && $k>0; $k--)
										{
											$DistanceFromEndOfMap++;
											if($MapEnterExit[$k]=="1")
											{
												$ProgrammedMapEndedSoonerThanNMinute = true;
												break;
											}
										}

										if(!$ProgrammedMapStartedSoonerThanNMinute && !$ProgrammedMapEndedSoonerThanNMinute)
											$CalcExtra = true;
										else if($ProgrammedMapEndedSoonerThanNMinute)
										{
											// در صورتیکه اضافه کار به انتهای لوح کشیک نزدیک باشد بررسی می کند آیا در آینده ادامه پیدا کرده که آن را محاسبه کند در غیر اینصورت محاسبه نکند
											if($DateArray["DayTimes"][$i+($AllowedTelorance-$ProgrammedMapEndedSoonerThanNMinute)]=="1")
												$CalcExtra = true;
										}
										
									}
									*/
									
									if($CalcExtra)
									{
										$LastMinIsExtraWork = true;
										$ExtraWorkTime++;
									}
									else
										$LastMinIsExtraWork = false;
								}
								else
									$LastMinIsExtraWork = false;
								if($DateArray["DayTimes"][$i]==2)
								{
									//**********************
									$LeaveTime++;
								}
							}
							else // برای پاره وقتها 
							{
								
								if($DateArray["DayTimes"][$i]==1 || $DateArray["DayTimes"][$i]==3)
									$WorkTime++;
								else if($DateArray["DayTimes"][$i]==2)
								{
									//if($_SESSION["PersonID"]=="201309")
									//	echo $DateArray["DayTimes"][$i];
									$LeaveTime++;
								}
							}
						}


					}
					
					/*********************/
					//if($_SESSION["PersonID"]=="201309")
					//	echo $LeaveTime."<br>";
					
					// به تعداد ورود و خروجها باید یک دقیقه از کارکرد کم شود (دقیقه شروع محسوب نمی شود)
					if($WorkTime>0)
					{
							$WorkTime-=$DateArray["EnterExitCount"];
							$MonthStatusArray[$DaysCount]["TotalPresentTime"]-=$DateArray["EnterExitCount"];					
					}	
					$MonthStatusArray[$DaysCount]["ExtraWorkTime"] = $ExtraWorkTime;
					
					$MonthStatusArray[$DaysCount]["ExtraWorkBeforeStartTime"] = $ExtraWorkBeforeStartTime;
					//echo "<br>";
					$MonthStatusArray[$DaysCount]["WorkTime"] = $WorkTime;
					$MonthStatusArray[$DaysCount]["LeaveTime"] = $LeaveTime;
					//if($_SESSION["PersonID"]=="201309")
					//	echo $DaysCount."->".$MonthStatusArray[$DaysCount]["LeaveTime"]."<br>";
					$MonthStatusArray[$DaysCount]["Mission"] = $Mission;
					
					//if($IsFriday || $IsThursday || $IsHoliday)
					//	echo $DaysCount." ".$MonthStatusArray[$DaysCount]["ExtraWorkTime"]." - ".$MonthStatusArray[$DaysCount]["ExtraWorkBeforeStartTime"]."<br>";
											
					// نتایج محاسبه شده را در بانک خطلاصه محاسبات ذخیره می کند
					// نتایجی که مربوط به روز جاری و بعد از آن هستند ذخیره نخواهند شد
					if(substr($FromDate, 0, 4)."/".substr($FromDate, 5, 2)."/".substr($FromDate, 8, 2)<$CurrentDay)
					{
						// اگر قبلا نتیجه محاسبه آن روز موجود بود دوباره ذخیره نمی کند
						$res = $mysql->Execute("select * from pas.DailyCalculationSummary where PersonID='".$PersonID."' and CalculatedDate='".$FromDate."'");
						if(!($arr_res=$res->FetchRow()))
						{
							$IsHolidayDesc = "NO";
							if($IsHoliday || $IsThursday || $IsFriday)
								$IsHolidayDesc = "YES";
							$query = "insert into pas.DailyCalculationSummary 
							(PersonID, CalculatedDate, PresentTime, TardinessTime, AbsentTime, HasteTime, ExtraWorkTime, WorkTime, LeaveTime, 
							MissionTime, EnterExitTypeID, ExtraWorkBeforeStartTime, IsLeave, IsHoliday) values (";
							$query .= "'".$PersonID."', '".$FromDate."', ";
							$query .= "'".$MonthStatusArray[$DaysCount]["TotalPresentTime"]."', '";
							$query .= $MonthStatusArray[$DaysCount]["Tardiness"]."', '";
							$query .= $MonthStatusArray[$DaysCount]["Absent"]."', '";
							$query .= $MonthStatusArray[$DaysCount]["Haste"]."', '";
							$query .= $MonthStatusArray[$DaysCount]["ExtraWorkTime"]."', '";
							$query .= $MonthStatusArray[$DaysCount]["WorkTime"]."', '";
							$query .= $MonthStatusArray[$DaysCount]["LeaveTime"]."', '";
							$query .= $MonthStatusArray[$DaysCount]["Mission"]."', '";
							$query .= $EnterExitType["id"]."', '";
							$query .= $MonthStatusArray[$DaysCount]["ExtraWorkBeforeStartTime"]."', '".$IsLeave."', '".$IsHolidayDesc."')";
							$mysql->Execute($query);
						}
					}
					
				}
				/*************/
				$FromDate=PASUtils::GetNextDate($FromDate);
				if($DaysCount>1000)
					break;
				//echo $DaysCount.") ".$MonthStatusArray[$DaysCount]["Absent"]."<br>";
			}
			//echo "<font color=red>".$DaysCount."</font><br>";
			//echo $PersonID." (2-3)<br>";
			return $MonthStatusArray;
			
		}
		
		// خلاصه کارکرد یک فرد را در یک بازه تاریخی بر می گرداند
		// حاصل در یک آرایه با اندیسهای زیر برگشت داده می شود
		// ExtraWorkTime: اضافه کاری
		// WorkTime: ساعت حضور در ساعت مجاز کاری
		// PresentTime: کل زمان حضور
		// LeaveTime: مرخصی ساعتی
		// Mission: ماموریت ساعتی
		// Tardiness: تاخیر
		// Absent: غیبت بین وقت
		// Haste: تعجیل
		// DailyLeaves: مجموع مرخصیهای روزانه - استحقاقی و استعلاجی
		// DailyOfficialLeaves: مرخصی روزانه استحقاقی
		// DailyCureLeaves: مرخصی روزانه استعلاجی
		// MissionDate: مامورت روزانه
		function CalculateAPersonSummaryStatusInARange($PersonID, $FromDate, $ToDate, $ValidFloat, $ValidTardiness, $ValidHaste)
		{
			$mysql = dbclass::getInstance();
			$EnterExitSpecialList = PASUtils::GetPersonSpecialEnterExitTypes($PersonID);
			$DefaultEnterExitType = PASUtils::GetPersonDefaultEnterExitType($PersonID);
			$now = date("Ymd"); 
			$yy = substr($now,0,4); 
			$mm = substr($now,4,2); 
			$dd = substr($now,6,2);
			$CurrentDay = $yy."/".$mm."/".$dd;
			$ret = array();
			$ret["ExtraWorkTime"] = 0;
			$ret["WorkTime"] = 0;
			$ret["PresentTime"] = 0;
			$ret["LeaveTime"] = 0;
			$ret["Mission"] = 0;
			$ret["Tardiness"] = 0;
			$ret["Absent"] = 0;
			$ret["Haste"] = 0;
			$ret["DailyLeaves"] = 0;
			$ret["DailyOfficialLeaves"] = 0;
			$ret["DailyCureLeaves"] = 0;
			$ret["MissionDays"] = 0;
			$ret["ExtraWorkBeforeStartTime"] = 0;
			
			//echo $PersonID." (1)<br>";
			$MonthStatus = PASUtils::CalculateAPersonStatusInARange($PersonID, $FromDate, $ToDate);
			//echo $PersonID." (2)<br>";
			$HolidayCount = 0;
			$LeaveCount = 0;
			$OfficialLeaveCount = 0;
			$CureLeaveCount = 0;
			$MissionCount = 0;
			$DaysCount = 0;
			
			$CurDateMiladi = substr($FromDate,0,4)."-".substr($FromDate,4,2)."-".substr($FromDate,6,2);
			$ToDateMiladi = substr($ToDate,0,4)."-".substr($ToDate,4,2)."-".substr($ToDate,6,2);
			// می خواهیم خود آخرین روز هم در محاسبات در نظر گرفته شود
			$ToDateMiladi = PASUtils::GetNextDate($ToDateMiladi);
			$i=1;
			while($CurDateMiladi!=$ToDateMiladi)
			{
				$IsFriday = PASUtils::IsFriday(substr($CurDateMiladi, 0, 4).substr($CurDateMiladi, 5, 2).substr($CurDateMiladi, 8, 2));
				$IsThursday = PASUtils::IsThursday(substr($CurDateMiladi, 0, 4).substr($CurDateMiladi, 5, 2).substr($CurDateMiladi, 8, 2));
				
//زمان کاری را به دقیقه برای هر روز محاسبه می کند.
				$res = $mysql->Execute("select (eet.EndTime - eet.StartTime) WorkTime
							from pas.DailyCalculationSummary dcs
							     INNER JOIN pas.EnterExitTypes eet
								   ON (dcs.EnterEXitTYpeID = eet.EnterEXitTYpeID)
							where PersonID='".$PersonID."' AND
							      CalculatedDate = '".$CurDateMiladi."'");
				if($arr_res=$res->FetchRow())	{
				    $WorkTime = $arr_res["WorkTime"];
				}				$CurDate  = mktime(0, 0, 0, substr($CurDateMiladi, 5, 2), substr($CurDateMiladi, 7, 2), substr($CurDateMiladi, 0, 4));
				if(substr($CurDateMiladi, 0, 4)."/".substr($CurDateMiladi, 5, 2)."/".substr($CurDateMiladi, 8, 2)>$CurrentDay)
					break;
				$FarsiCurDate = shdate($CurDateMiladi);
				// روز جاری در تاریخ شمسی
				$Day = substr($FarsiCurDate,0,2);
				// ماه جاری در تاریخ شمسی
				$Month = substr($FarsiCurDate,3,2);
				// سال جاری در تاریخ شمسی
				$Year = substr($FarsiCurDate,6,2);
				// برای روز جاری مشخص می کند که نوع ورود و خروج برای فرد چه تعریف شده است
				unset($EnterExitType);
				$EnterExitType = PASUtils::GetEnterExitTypeInDay($DefaultEnterExitType, $EnterExitSpecialList, $CurDateMiladi);
				
				$HolidayFlag = 0;
				$LeaveFlag = 0;
				$MissionFlag = 0;
				$CurDateWithoutDash = substr($CurDateMiladi,0,4).substr($CurDateMiladi, 5,2).substr($CurDateMiladi, 8, 2);
				$IsHoliday = PASUtils::IsHoliday($CurDateWithoutDash);
				if(PASUtils::IsEndWeekVacation($CurDateWithoutDash) || $IsHoliday)
				{
					$HolidayFlag = 1;
					$HolidayCount++;
				}
				// else
				// دستور فوق کامنت شد چون در بعضی موارد تعطیلی افراد روی تعطیلی رسمی نیست و باید مرخصی آنها محسوب شود مثل انتظامات
				if($EnterExitType["JobStatus"]=="PARTTIME" || $EnterExitType["JobStatus"]=="SHIFT" || $HolidayFlag==0)
				{
					if(PASUtils::HasDailyLeave($PersonID, $CurDateWithoutDash))
					{
						$LeaveFlag = 1;
						$LeaveCount++;
						$LeaveType = PASUtils::GetLeaveType($PersonID, $CurDateWithoutDash);
						if($LeaveType=="استحقاقی")
							$OfficialLeaveCount++;
						else
							$CureLeaveCount++;
					}
				}
				if(PASUtils::HasDailyMission($PersonID, $CurDateWithoutDash))
				{
					$MissionFlag = 1;
					$MissionCount++;
				}
				$CalculateTardinessAndHasteAndAbsent = false;
				if($HolidayFlag==0  && $LeaveFlag==0 && $MissionFlag==0)
					$CalculateTardinessAndHasteAndAbsent = true;
				if($LeaveFlag==0 && $MissionFlag==0 && $EnterExitType["JobStatus"]=="24HOUR")
					$CalculateTardinessAndHasteAndAbsent = true;
				// برای روزهای تعطیل برای شیفتیها مرخصی را حساب نمی کند
				if($CalculateTardinessAndHasteAndAbsent) // اگر روز تعطیل نبود و مرخصی تمام روز نبود تاخیر و تعجیل و غیبت محاسبه می شود
				{
					//echo $Month."-".$Day."<br>";
					// اعمال شناوری در محاسبات
					if($ValidFloat>0 && $MonthStatus[$i]["Tardiness"]>0 && $MonthStatus[$i]["Tardiness"]<=$ValidFloat)
					{
						if($MonthStatus[$i]["ExtraWorkTime"]>0)
						{
							$ex = $MonthStatus[$i]["ExtraWorkTime"];
							$MonthStatus[$i]["ExtraWorkTime"] = $MonthStatus[$i]["ExtraWorkTime"] - $MonthStatus[$i]["Tardiness"];
							if($MonthStatus[$i]["ExtraWorkTime"]<0)
								$MonthStatus[$i]["ExtraWorkTime"] = 0;
							$MonthStatus[$i]["Tardiness"] = $MonthStatus[$i]["Tardiness"] - $ex - $MonthStatus[$i]["LeaveTime"];
							if($MonthStatus[$i]["Tardiness"]<0)
								$MonthStatus[$i]["Tardiness"] = 0;
						}
						else /*if($MonthStatus[$i]["TotalPresentTime"]>$WorkTime-$ValidFloat) */
						{
							// در حالت خاص مثلا تابستان که اضافه کار نداریم شناوری به معنای این است که ساعت کار بیشتر از ۷ ساعت و نیم شود
							// در صورتی این بخش را اجرا می کند که شخص حداقل ساعت کاری بیش از ۷ ساعت و نیم منهای تاخیر شناوری داشته باشد
							$MonthStatus[$i]["Tardiness"] = $WorkTime - $MonthStatus[$i]["TotalPresentTime"] - $MonthStatus[$i]["LeaveTime"] - $MonthStatus[$i]["Mission"];
							if ($_SESSION['UserID'] == "ghahremani") {
							    echo $CurDateMiladi."curdate</br>";
							    echo $WorkTime."worktime</br>";
							    echo $MonthStatus[$i]["TotalPresentTime"]."TotalPresentTime</br>";
							    echo $MonthStatus[$i]["LeaveTime"]."LeaveTime</br>";
							    echo $MonthStatus[$i]["Mission"]."Mission</br>";
							    echo $MonthStatus[$i]["Tardiness"]."tardiness</br>";
							    echo "------------------------------</br>";
							}
							if($MonthStatus[$i]["Tardiness"]<0)
								$MonthStatus[$i]["Tardiness"] = 0;
						}
						
					}
					$ret["ExtraWorkTime"] += $MonthStatus[$i]["ExtraWorkTime"];
					$ret["ExtraWorkBeforeStartTime"] += $MonthStatus[$i]["ExtraWorkBeforeStartTime"];
					$ret["WorkTime"] += $MonthStatus[$i]["WorkTime"];
					$ret["LeaveTime"] += $MonthStatus[$i]["LeaveTime"];
					$ret["Mission"] += $MonthStatus[$i]["Mission"];
					if ($MonthStatus[$i]["Tardiness"]>$ValidTardiness) {
						$ret["Tardiness"] += $MonthStatus[$i]["Tardiness"];
					} 
					$ret["Absent"] += $MonthStatus[$i]["Absent"];
					//echo $MonthStatus[$i]["Absent"]."<br>";
					if($MonthStatus[$i]["Haste"]>$ValidHaste)
						$ret["Haste"] += $MonthStatus[$i]["Haste"];
				}
				else if($LeaveFlag==1)
				{
					// به درخواست کارگزینی از این پس برای روزهای مرخصی اضافه کار محاسبه نمی شود
					//$ret["ExtraWorkTime"] += $MonthStatus[$i]["ExtraWorkTime"]+$MonthStatus[$i]["WorkTime"];
					
					$ret["ExtraWorkBeforeStartTime"] += $MonthStatus[$i]["ExtraWorkBeforeStartTime"];
					$ret["WorkTime"] += $MonthStatus[$i]["ExtraWorkTime"]+$MonthStatus[$i]["WorkTime"];
					$ret["LeaveTime"] += $MonthStatus[$i]["LeaveTime"];
				}
				else // در روزهای تعطیل یا مرخصی تمام وقت زمان کاری کلا اضافه کار محسوب می شود
				{
					if($EnterExitType["JobStatus"]=="OFFICIAL" && $MissionFlag==0)
					{
						// در صورتی اینکار را انجام می دهد که طبق تعریف نوع کاری اضافه کار برای روزهای تعطیل پنجشنبه و جمعه حساب بشود
						if(($IsFriday && $EnterExitType["CalculateExtraWorkInFriday"]=="YES") || ($IsThursday && $EnterExitType["CalculateExtraWorkInThursday"]=="YES") || ($IsHoliday && $EnterExitType["CalculateExtraWorkInHolidays"]=="YES"))						
						{
							
							if($IsThursday)
							{
								// در صورتیکه پنجشنبه باشد بازه مجاز برای آن چک شده است و در محاسبه اضافه کار همان روز در نظر گرفته شده بنابراین اضافه کردن ساعت کاری نیازی نیست؟
								$ret["ExtraWorkTime"] += $MonthStatus[$i]["ExtraWorkTime"];
								$ret["ExtraWorkBeforeStartTime"] += $MonthStatus[$i]["ExtraWorkBeforeStartTime"];
							}
							else
							{
								$ret["ExtraWorkTime"] += $MonthStatus[$i]["ExtraWorkTime"]+$MonthStatus[$i]["WorkTime"];
								$ret["ExtraWorkBeforeStartTime"] += $MonthStatus[$i]["ExtraWorkBeforeStartTime"];
							}
						}
						$ret["WorkTime"] += $MonthStatus[$i]["ExtraWorkTime"]+$MonthStatus[$i]["WorkTime"];
					}
					else if($EnterExitType["JobStatus"]=="SHIFT")
					{
						// برای شیفتیها فرقی نمی کند تعطیل باشد یا نه چون اضافه حضور بر طبق لوح کشیک است
						$ret["ExtraWorkTime"] += $MonthStatus[$i]["ExtraWorkTime"];
						// برای شیفتیها حتی روزهای تعطیل هم می تواند ماموریت ساعتی ثبت شود چون ممکن است طبق لوح کشیک موظف به آمدن باشند						
						$ret["Mission"] += $MonthStatus[$i]["Mission"];
						// برای شیفتیها روزهای تعطیل هم می تواند مرخصی ساعتی وجود داشته باشد
						$ret["LeaveTime"] += $MonthStatus[$i]["LeaveTime"];
					}
				}
				$ret["PresentTime"] += $MonthStatus[$i]["TotalPresentTime"];
				
				
				$CurDateMiladi=PASUtils::GetNextDate($CurDateMiladi);
				//echo $CurDateMiladi." - ".$ToDateMiladi." - ".$PersonID."<br>";
				if($i>1000)
					break;
				$i++;
			}
			//echo $PersonID." (3)<br>";
			$ret["DailyLeaves"] = $LeaveCount;
			$ret["DailyOfficialLeaves"] = $OfficialLeaveCount;
			$ret["DailyCureLeaves"] = $CureLeaveCount;
			$ret["MissionDays"] = $MissionCount;
			unset($MonthStatus);
			return $ret;
		}
		
	}
?>