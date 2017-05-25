//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//--------------------------- 

var DateModule = {};

DateModule.IsDateGreater = function (DateValue1, DateValue2)
{
	var DaysDiff;
	Date1 = new Date(DateValue1);
	Date2 = new Date(DateValue2);
	DaysDiff = Math.floor((Date1.getTime() - Date2.getTime())/(1000*60*60*24));
	
	if(DaysDiff > 0)
		return true;
	else
		return false;
};
	   
DateModule.IsDateLess = function (DateValue1, DateValue2)
{
	var DaysDiff;
	Date1 = new Date(DateValue1);
	Date2 = new Date(DateValue2);
	DaysDiff = Math.floor((Date1.getTime() - Date2.getTime())/(1000*60*60*24));
	
	if(DaysDiff <= 0)
		return true;
	else
		return false;
};

DateModule.IsDateEqual = function (DateValue1, DateValue2)
{
	var DaysDiff;
	Date1 = new Date(DateValue1);
	Date2 = new Date(DateValue2);
	DaysDiff = Math.floor((Date1.getTime() - Date2.getTime())/(1000*60*60*24));
	
	if(DaysDiff == 0)
		return true;
	else
		return false;
};

