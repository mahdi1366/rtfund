<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	89.11
//-------------------------

class CurrencyModulesclass
{
	private static $number_array = array(
		1 => 'يک',
		2 => 'دو',
		3 => 'سه',
		4 => 'چهار',
		5 => 'پنج',
		6 => 'شش',
		7 => 'هفت',
		8 => 'هشت',
		9 => 'نه',

		10 => 'ده',
		11 => 'يازده',
		12 => 'دوازده',
		13 => 'سيزده',
		14 => 'چهارده',
		15 => 'پانزده',
		16 => 'شانزده',
		17 => 'هفده',
		18 => 'هيجده',
		19 => 'نوزده',

		20 => 'بيست',
		30 => 'سي',
		40 => 'چهل',
		50 => 'پنجاه',
		60 => 'شصت',
		70 => 'هفتاد',
		80 => 'هشتاد',
		90 => 'نود',

		100 => 'يکصد',
		200 => 'دويست',
		300 => 'سيصد',
		400 => 'چهارصد',
		500 => 'پانصد',
		600 => 'ششصد',
		700 => 'هفتصد',
		800 => 'هشتصد',
		900 => 'نهصد'
		);

    private static $extend = array(
		0 => '',
		1 => 'هزار',
		2 => 'ميليون',
		3 => 'ميليارد',
		4 => 'تريليون');
	
	static function toCurrency($value)
	{
		$value = number_format(abs($value),0,NUMBER_DECIMAL_POINT, NUMBER_THOUSANDS_POINT);
	    if ($value < 0)
	    {
	    	$value = str_replace('N',$value, NUMBER_NEGATIVE_VIEW);
	    }
	    return $value;
	}

	/**
	 * این تابع مبلغ را به حروف بر می گرداند
	 * @param int $value
	 * @return string
	 */
	public static function CurrencyToString($value)
	{
		if ($value == "" || $value == 0)
                return 'صفر';
        $extend  = self::$extend;
        $counter = 0;

        $number_string = '';
        while ($value > 0) {
                $three_digit_number = 0;
				//$three_digit_number = ($value % 1000);
				$three_digit_number = strlen($value)<=3 ? $value : substr($value, strlen($value)-3);
                $value = floor($value / 1000);
                if ($three_digit_number > 0) {
                        $three_digit_string = self::ConvertThreeDigitNumberToString($three_digit_number);
                        $temp_string = '';
                        if ($counter > 0)
                                $temp_string .= ' ';
                        //if ($counter == 1 && ($three_digit_number%10) == 1) /*   'يکهزار'  */
                        //        $temp_string .= $three_digit_string . $extend[$counter];
                        $temp_string .= $three_digit_string . ' ' . $extend[$counter];
                        if ($counter > 0 && $number_string > '')
                                $temp_string .= ' و ';
                        $number_string =  $temp_string . $number_string;
                }
                $counter++;
        }


        return $number_string;
	}

	private static function ConvertThreeDigitNumberToString($three_digit_number)
	{
        if ($three_digit_number == 0)
                return '';
        $number_array = self::$number_array;
        $three_digit_string = '';
        if ($three_digit_number > 99) {
                $three_digit_string = $number_array[floor($three_digit_number / 100) * 100];
                $three_digit_number %= 100;
        }

        if ($three_digit_number > 0) {
                if ($three_digit_string > '')
                        $three_digit_string .= ' و ';
                if ($three_digit_number < 20) {
                        $three_digit_string .= $number_array[$three_digit_number*1];
                }
                else {
                        $three_digit_string .= $number_array[floor($three_digit_number / 10) * 10];
                        $three_digit_number %= 10;
                        if ($three_digit_number > 0) {
                                if ($three_digit_string > '')
                                        $three_digit_string .= ' و ';
                                $three_digit_string .= $number_array[$three_digit_number];
                        }
                }
        }
        return $three_digit_string;
	}
}
?>
