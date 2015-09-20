//---------------------------
// programmer:	Jafarkhani
// create Date:	88.07
//---------------------------

function BindDropDown(slaveElement, slaveDataArr, filterValue1, filterValue2, filterValue3)
{
	combo = slaveElement;
	var tmpdata = slaveDataArr;
	//eval("var tmpdata = " + slaveDataArr + ";");

	while(combo.options.length != 0)
		combo.remove(0);

	if(tmpdata[0]["id"] == -1)
		combo.options.add(new Option(tmpdata[0]["text"], tmpdata[0]["id"]));

	for(i=0; i<tmpdata.length; i++)
	{
		if(tmpdata[i]["master1"] == filterValue1)
		{
			if(filterValue2 && filterValue2 != "")
			{
				if(tmpdata[i]["master2"] == filterValue2)
				{
					if(filterValue3 && filterValue3 != "")
					{
						if(tmpdata[i]["master3"] == filterValue3)
						{
							combo.options.add(new Option(tmpdata[i]["text"], tmpdata[i]["id"]));
						}
					}
					else
						combo.options.add(new Option(tmpdata[i]["text"], tmpdata[i]["id"]));
				}
			}
			else
				combo.options.add(new Option(tmpdata[i]["text"], tmpdata[i]["id"]));
		}
	}
	combo.disabled = false;
	combo.selectedIndex = 0;
}

function Ext_BindDropDown(extMasterComboID, extSlaveComboID, masterField)
{
	master = Ext.getCmp(extMasterComboID);
	slave = Ext.getCmp(extSlaveComboID);

	slave.getStore().filterBy(function(record,id){
		if(record.get(masterField) == master.getValue()) 
			return true;
		return false;
	});
	slave.enable();
	//slave.setValue(slave.store.getAt(0).data.value);
}

function direct_BindDropDown(MasterElemID, SlaveElemID, MasterDataArray, SlaveDataArray)
{
	if(Ext.getCmp(MasterElemID))
	{
		master = Ext.getCmp(MasterElemID);
		slave = Ext.getCmp(SlaveElemID);

		/*slave.getStore().removeAll();
		if(master.store.indexOfId(master.getValue()) == -1)
			return;
		for(i=0; i<SlaveDataArray.length; i++)
		{
			if(SlaveDataArray[i]["master1"] == MasterDataArray[master.store.indexOfId(master.getValue())]["id"])
			{
				slave.store.add([new Ext.data.Record({"value": SlaveDataArray[i]["id"],"text": SlaveDataArray[i]["text"]})]);
			}
		}*/
		slave.getStore().filter("master1",master.getValue());
		slave.enable();
		slave.setValue(slave.store.getAt(0).data.value);
	}
	else
	{
		var master = document.getElementById(MasterElemID);
		var slave = document.getElementById(SlaveElemID);

		while(slave.options.length != 0)
			slave.remove(0);

		if(MasterDataArray[master.selectedIndex]["id"] == -1)
			return;
		for(i=0; i<SlaveDataArray.length; i++)
		{
			if(SlaveDataArray[i]["master"] == MasterDataArray[master.selectedIndex]["id"])
			{
				slave.options.add(new Option(SlaveDataArray[i]["text"], SlaveDataArray[i]["id"]));
			}
		}
		slave.disabled = false;
		slave.selectedIndex = 0;
	}
	
}

function showLOV(pageName, width, height)
{
	/*var val;
	var feature = "menubar:no,toolbar=no,status=no,center=yes,height=" +height+ ",width=" +width +
			",locationbar=no,top=" + ((screen.availHeight-height)/2) + ",left=" + ((screen.availWidth-width)/2) ;
		val = window.open(pageName, "", feature);*/
	
	var feature = "help:no;status:no;center:yes;dialogHeight:" +height+ ";dialogWidth:" +width +
		";locationbar:no;menubar:no;dialogTop:" + ((screen.availHeight-height)/2) + ";dialogLeft:" + ((screen.availWidth-width)/2) ;
	val = showModalDialog(pageName, "", feature);
	
	
	if (val)
		return val;

	return "";
}

//******************* added by jafarkhani 89.01 **********************
function PriceEncode(value)
{
	var returnStr = "";
	value = value.toString();

	for(index=1,i=value.length-1; i>=0; i--,index++)
	{
		returnStr = value.charAt(i) + returnStr;
		if(index == 3 && i != 0)
		{
			returnStr = "," + returnStr;
			index = 0;
		}
	}
	if(returnStr == "")
		return 0;
	return returnStr;
}

function PriceDecode(value)
{
	return value.toString().replace(/,/g,"");
}

//---------------------- added by Jafarkhani at 89.11 ----------------

function ConvertThreeDigitNumberToString(three_digit_number)
{
	if (three_digit_number == 0)
		return '';
	
	value_array = {
		1 : 'يک',
		2 : 'دو',
		3 : 'سه',
		4 : 'چهار',
		5 : 'پنج',
		6 : 'شش',
		7 : 'هفت',
		8 : 'هشت',
		9 : 'نه',

		10 : 'ده',
		11 : 'يازده',
		12 : 'دوازده',
		13 : 'سيزده',
		14 : 'چهارده',
		15 : 'پانزده',
		16 : 'شانزده',
		17 : 'هفده',
		18 : 'هيجده',
		19 : 'نوزده',

		20 : 'بيست',
		30 : 'سي',
		40 : 'چهل',
		50 : 'پنجاه',
		60 : 'شصت',
		70 : 'هفتاد',
		80 : 'هشتاد',
		90 : 'نود',

		100 : 'يکصد',
		200 : 'دويست',
		300 : 'سيصد',
		400 : 'چهارصد',
		500 : 'پانصد',
		600 : 'ششصد',
		700 : 'هفتصد',
		800 : 'هشتصد',
		900 : 'نهصد'
	};

	three_digit_string = '';

	if (three_digit_number > 99)
	{
		three_digit_string = value_array[Math.floor(three_digit_number / 100) * 100];
		three_digit_number %= 100;
	}

	if (three_digit_number > 0) {
		if (three_digit_string > '')
				three_digit_string += ' و ';
		if (three_digit_number < 20)
		{
			three_digit_string += value_array[three_digit_number];
		}
		else
		{
			three_digit_string += value_array[Math.floor(three_digit_number / 10) * 10];
			three_digit_number %= 10;
			if (three_digit_number > 0)
			{
					if (three_digit_string > '')
							three_digit_string += ' و ';
					three_digit_string += value_array[three_digit_number];
			}
		}
	}
	return three_digit_string;
}

function CurrencyToString(value)
{
	if (value == 0)
		return 'صفر';

	extend  = {
		0 : '',
		1 : 'هزار',
		2 : 'ميليون',
		3 : 'ميليارد',
		4 : 'تريليون'};

	counter = 0;

	value_string = '';
	while (value > 0)
	{
		three_digit_number = 0;
		three_digit_number = value % 1000;
		value = Math.floor(value / 1000);
		if (three_digit_number > 0)
		{
			three_digit_string = ConvertThreeDigitNumberToString(three_digit_number);
			temp_string = '';
			if (counter > 0)
				temp_string += ' ';
			if (counter == 1 && (three_digit_number%10 == 1)) /*   'يکهزار'  */
				temp_string += three_digit_string + extend[counter];
			else temp_string += three_digit_string + ' ' + extend[counter];

			if (counter > 0 && value_string > '')
				temp_string += ' و ';
			value_string =  temp_string + value_string;
		}
		counter++;
	}
    return value_string;
}

//---------------------- added by Jafarkhani at 90.02 ----------------

function ShowExceptions(element, errorsObject)
{
	element.innerHTML = "";
		
	if(errorsObject.errors != "")
		Ext.message.error(element,'خطا',errorsObject.errors,'98%');

	if(errorsObject.warnings != "")
		Ext.message.warning(element,'هشدار',errorsObject.warnings,'98%');

	if(errorsObject.messages != "")
		Ext.message.message(element,'پیغام',errorsObject.messages,'98%');
}