<?php
//---------------------------
// programmer:	jafarkhani
// create Date:	88.07.15
//---------------------------

define('OPEN_WRIT_WITHOUT_CALC_END_DATE', '1994-03-20');
define('OPEN_WRIT_WITH_CALC_END_DATE', '2006-01-20');
define('TRANSFER_WRIT_EXE_DATE', '2005-09-23'); //احکامي که تاريخ صدورشان از 1/7/84 بزرگتر است منتقل مي شوند

/*define("HR_HRMS","1");
define("HR_SHERKATI","2");*/

define('HR_PROFESSOR',        1);		//هيات علمي
define('HR_EMPLOYEE',         2);		//کارمندان
define('HR_WORKER',           3);		//روزمزدبيمه اي
define('HR_BURSE',            4);		//بورسيه
define('HR_CONTRACT' ,        5);     // قراردادی
define('HR_RETIRED',		   	10);	//بازنشسته
define('PERSON_TYPE_ALL',  	100);	//همه
  
define('WRIT_PERSONAL', 1); //وضعيت احکامي که در اختيار کارگزيني هستند
define('WRIT_MIDDLE', 2); // وضعيت احکامي که به حالت مياني منتقل شده اند
define('WRIT_SALARY', 3);//وضعيت احکامي که به سيستم حقوق منتقل شده اند 
//.............................................................................................
define('MUST_PAY_YES',	1);
define('MUST_PAY_NO',	0);
//.............................................................................................
define('HISTORY_ONLY', 1);
//.............................................................................................
 //يادآوري شد؟
 define('REMEMBERED',1);
//.............................................................................................
define('WRIT_NOT_CORRECTED',0);
define('WRIT_CORRECTING',1);
define('WRIT_CORRECT_COMPLETED',2); 
//.............................................................................................
define('EMP_MODE_PRACTITIONER',                             1);
define('EMP_MODE_ENGAGEMENT',                               2);
define('EMP_MODE_LEAVE_WITHOUT_SALARY',                     3);
define('EMP_MODE_EDUCATIONAL_MISSION',                      4);
define('EMP_MODE_ENVOY_WITH_MISSION',                       5);
define('EMP_MODE_ENVOY_TO',                                 6);
define('EMP_MODE_ENVOY_FROM',                               16);
define('EMP_MODE_CONVEYANCE',                               7);
define('EMP_MODE_TEMPORARY_BREAK',                          8);
define('EMP_MODE_PERMANENT_BREAK',                          9);
define('EMP_MODE_OBLIGING',                                10);
define('EMP_MODE_BREAKAWAY',                               11);
define('EMP_MODE_RUSTICATION',                             12);
define('EMP_MODE_RETIRE',                                  13);
define('EMP_MODE_RE_BUY',                                  14);
define('EMP_MODE_WITHOUT_SALARY',                          15);
define('EMP_MODE_INTERNAL_STUDY_OPPORTUNITY',              16);
define('EMP_MODE_RUSTICATION_EXTERNAL_STUDY_OPPORTUNITY',  17);
define('EMP_MODE_INTERNAL_STUDY_MISSION',                  18);
define('EMP_MODE_EXTERNAL_STUDY_MISSION',                  19);
define('EMP_MODE_LEAVE_WITH_SALARY',		   21);

//.............................................................................................
define('IN_EMPLOYEES',1);
define('IN_SALARY',2);
define('DELETE_IN_EMPLOYEES',3);
define('DELETE_IN_SALARY',4);
//.............................................................................................
define('NORMAL', 1); //بيمه عادي
define('FIRST_SURPLUS',   2);  //بيمه مازاد1
define('SECOND_SURPLUS',  3);  //بيمه مازاد2
define('SOCIAL_SUPPLY',  4); // تامین اجتماعی
define('NORMAL2', 5);      // بیمه عادی2
define('WHITOUT_INSURE' , 10);// بدون بیمه
//.............................................................................................
//ثبت سیستم 
define('REGISTER_LOAN_FLOW_TYPE',	1);   
//فیش حقوقی
define('CALCULATE_FICHE_FLOW_TYPE',	2);
//گردش دستی
define('REGISTER_NEW_FLOW_TYPE',	3);
//گردش خودکار
define('REGISTER_AUTOMATIC_NEW_FLOW_TYPE',	4);

//.............................................................................................

//اثر سنواتي
define('COMPUTED',        1);
define('HALF_COMPUTED',   2);
define('NOT_COMPUTED',    3);
define('DOUBLE_COMPUTED', 4);
//.............................................................................................
define('UNDERAGE_SUPPORT_CAUSE',       1);
define('SUPERANNUATION_SUPPORT_CAUSE', 2);
define('BACHELORHOOD_SUPPORT_CAUSE',   3);
define('ACQUIREMENT_SUPPORT_CAUSE',    4);
define('OBSOLETE_SUPPORT_CAUSE',       5);
define('OTHER_SUPPORT_CAUSE',          6);
define('WIFE_SUPPORT_CAUSE',           7);
define('ATTENDANT_SUPPORT_CAUSE',      8);
define('STAFF_SUPPORT_CAUSE',          9);
//.............................................................................................
define('FREE_POST_EFFECT',            1);
define('TEMPORARY_FREE_POST_EFFECT',  2);
define('DETERMINE_POST_EFFECT',       3);
define('CHANGE_POST_EFFECT',          4);
//.............................................................................................
define('EMP_STATE_SOLDIER_CONTRACTUAL',     1);
define('EMP_STATE_ONUS_SOLDIER_CONTRACTUAL',10);
define('EMP_STATE_CONTRACTUAL',             2);
define('EMP_STATE_PROBATIONAL_CEREMONIOUS', 3);
define('EMP_STATE_APPROVED_CEREMONIOUS',    4);
define('EMP_STATE_ARBITRARILY',             5);
define('EMP_STATE_ATTENDANCE',              6);
define('EMP_STATE_TEACHING_RIGHT',          7);
define('EMP_STATE_INSURE_DAILY',            8);
define('EMP_STATE_HR_SURVEY',               9);
define('EMP_STATE_NONE_GOVERNMENT', 90);
define('EMP_STATE_WITHOUT_SALARY', 95);
define('EMP_STATE_HALF_TIME', 97);

//.............................................................................................
define('EDUCATION_LEVEL_FIFTH_GRADE', 	      112);
define('EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL', 117);
define('EDUCATION_LEVEL_SEVENTH_GRADE',       120);
define('EDUCATION_LEVEL_CYCLE',  		  	  122);
define('EDUCATION_LEVEL_THIRTH_HONARESTAN',   134);
define('EDUCATION_LEVEL_DIPLOMA',             200);
define('EDUCATION_LEVEL_ALL_DIPLOMA',         201);
define('EDUCATION_LEVEL_THREE_YEAR_HIGH_SCHOOL',   203);
define('EDUCATION_LEVEL_THREE_YEAR_HIGH_SCHOOL_WITHOUT_SCORE', 204);
define('EDUCATION_LEVEL_HIGH_DIPLOMA',        300);
define('EDUCATION_LEVEL_EQUAL_HIGH_DIPLOMA',  301);
define('EDUCATION_LEVEL_BS',                  400);
define('EDUCATION_LEVEL_EQUAL_BS',            401);
define('EDUCATION_LEVEL_MS',                  500);
define('EDUCATION_LEVEL_EQUAL_MS',            501);
define('EDUCATION_LEVEL_HOZE_LEVEL3_EQUAL_MS' , 502);
define('EDUCATION_LEVEL_EQUAL_PHD',           602);
define('EDUCATION_LEVEL_PHD',                 603);
define('EDUCATION_LEVEL_DOCTORATE',           600);
define('EDUCATION_LEVEL_DOCTORAL',            604);

define('SCIENCE_LEVEL_MS', 	2);
define('SCIENCE_LEVEL_PHD',	3);
//.............................................................................................
define('SALARY_COMPUTE_TYPE_CONSTANT',  1);
define('SALARY_COMPUTE_TYPE_MULTIPLY',  2);
define('SALARY_COMPUTE_TYPE_FUNCTION',  3);
//.............................................................................................
define('FULL_TIME',      1);
define('HALF_TIME',      2);
define('FULL_TIME_PLAN', 3);
define('QUARTER_TIME' , 4 ) ; 
//.............................................................................................
define('MASTERSHIP',          5);	//استاد
define('LECTURESHIP',         4);	//دانشيار
define('MASTERSTROKE',        3);	//استاديار
define('EDUCATOR',            2);	//مربي
define('INSTRUCTOR_EDUCATOR', 1);	//مربي آموزشيار
//.............................................................................................
define('DEVOTION_TYPE_FIGHTER',         1);
define('DEVOTION_TYPE_PRISONER',        2);
define('DEVOTION_TYPE_WOUNDED',         3);
define('DEVOTION_TYPE_WAR_TEACH',       4);
define('DEVOTION_TYPE_MARTYR_FAMILY',   5);
define('DEVOTION_TYPE_DEVOTED_FAMILY',  6);
//.............................................................................................
define('TEHRAN_STATE_ID',  17);
define('TEHRAN_CITY_ID',   40);
//.............................................................................................
define('ORGUNIT_DATA_ENTRY',  1);
define('ORGUNIT_CONFIRM',     2);
define('CENTER_CONFIRM',      3);
//define('COMPUTED',            4);
//.............................................................................................
define('AUTOMATIC', 		 0);
define('USER_DATA_ENTRY',  1);
//.............................................................................................
define('OWN',       1);
define('FATHER',    2);
define('MOTHER',    3);
define('WIFE',      4);
define('BOY',       5);
define('DAUGHTER',  6);
//.............................................................................................
define('MARITAL_STATUS_SINGLE',     1);
define('MARITAL_STATUS_WITH_WIFE',  2);
define('MARITAL_STATUS_DIVORCE',    3);
define('MARITAL_STATUS_DEAD_WIFE',  4);
//.............................................................................................
define('FIGHTING_DEVOTION',             1);
define('FREEDOM_DEVOTION',              2);
define('SACRIFICE_DEVOTION',            3);
define('WAR_REGION_TEACHING_DEVOTION',  4);
define('BEHOLDER_FAMILY_DEVOTION',      5); // خانواده شهید
define('DEVOTION_FAMILY',               6);
define('DEVOTION_THERAPY_DURATION',     7);
define('ASHOURA_BATTALION',             8);
define('WAR_REGION_WORK_DEVOTION',		9);
//.............................................................................................
define('SALARY_ITEM_COMPUTE_PLACE_WRIT',     1);
define('SALARY_ITEM_COMPUTE_PLACE_PAYMENT',  2);
//............................................................................................. 
    //محل استفاده از پارامتر حقوقي در سيستم
	define('ITEM_AVAILABLE_LOAN',		  1);	// وام
	define('ITEM_AVAILABLE_BENEFIT',	  2);		//مزاياي ثابت، موردي و گروهي
	define('ITEM_AVAILABLE_FRACTION_CASE',3);	//	كسور موردي و گروهي
	define('ITEM_AVAILABLE_FRACTION',	  4);	// كسور ثابت
	define('ITEM_AVAILABLE_NONE',	  	  5);	// هيچكدام
//.............................................................................................
	define('LOAN',1); // وام
	define('FIX_FRACTION',2); // کسور ثابت
    define('FIX_BENEFIT',3); // مزایای ثابت
    define('GUARANTIED_LOAN',	4);	// وامهای ضمانت شده
//.............................................................................................
define('BENEFIT_CUT',3);	// قطع 
define('BENEFIT_PAY',1);								//حقوق و مزايا کامل پرداخت مي شود.				
define('ONLY_CONTINUES_SALARY_PAY',2);				//فقط حقوق و مزاياي مستمر پرداخت مي شود.
define('ONLY_BASE_SALARY_PAY',30);						//فقط حقوق مبنا پرداخت مي شود.
define('CONTINUES_SALARY_RETIRED_HALF_BENEFIT_PAY',4);	//حقوق و مزاياي مستمر و کسور بازنشستگي نصف و مزايا کامل پرداخت مي شود.
define('CONTINUES_SALARY_HALF_BENEFIT_RETIRED_PAY',5);	//حقوق و مزاياي مستمر نصف و مزايا و کسور بازنشستگي کامل پرداخت مي شود.

define('BENEFIT_WITHOUT_EXTRAWORK',7);					//حقوق و مزايا کامل بدون اضافه کار پرداخت مي شود.
define('CONTINUES_SALARY_HALF_BENEFIT_FRACTION_PAY',8);	//حقوق و مزاياي مستمر نصف و مزايا و کسورات کامل پرداخت مي شود.
define('BENEFIT_EXIT_IN_WRIT_NOT_EXIST_IN_PAYMENT',9);	//حقوق و مزاياي کامل در حکم منظور مي شوداما پرداخت نمي شود - از ليست حقوق حذف.
//.............................................................................................
define('NATIVE',    0);
define('ADVENTIVE', 1);
//.............................................................................................
define('EDUCATIONAL',1);
define('RESEARCH',   2);
//define('OFFICIAL',   3);
//.............................................................................................
define("WRIT_BASE_MONTH_LENGTH",30);  // مبنای تعداد روز اقلام حکم.//
//.............................................................................................
define('BASE_SALARY_MULTIPLICAND',	1 ); //حقوق مبنا  
define('SALARY_MULTIPLICAND',	2); //حقوق
define('CONTINUES_SALARY_MULTIPLICAND',	3); // حقوق و مزایای مستمر
//.............................................................................................
//انواع اقلام حقوقي هيات علمي

define('SIT_PROFESSOR_BASE_SALARY',					1);	// حقوق ماهانه
define('SIT_PROFESSOR_SPECIAL_EXTRA',				6);	//فوق العاده مخصوص
define('SIT_PROFESSOR_CHILDREN_RIGHT',				9);	 //حق اولاد هيات علمي
define('SIT_PROFESSOR_ADAPTION_DIFFERENCE',			11); //تفاوت تطبيق هيات علمي
define('SIT_PROFESSOR_WORK_WITH_RAY_EXTRA',			20);//فوق العاده کار با اشعه هيات علمي
define('SIT_PROFESSOR_ABSOPPTION_EXTRA',			22); //فوق العاده جذب هيات علمي
define('SIT_PROFESSOR_BAD_WEATHER_EXTRA',			24); //فوق العاده بدي آب و هواي هيات علمي
define('SIT_PROFESSOR_MANAGMENT_EXTRA',				28); //فوق العاده مديريت هيات علمي
define('SIT_PROFESSOR_EXCLUDE_MANAGEMENT_EXTRA',       561);   //فوق العاده مديريت خارج شمول هيات علمي
define('SIT_STAFF_EXCLUDE_MANAGEMENT_EXTRA',           562);   //فوق العاده مديريت خارج شمول کارمندان 
define('SIT_PROFESSOR_HOUSING_RIGHT',				29);  //حق مسکن هيات علمي
define('SIT_PROFESSOR_CHILD_RIGHT',                 32); //حق عايله مندي هيات علمي
define('SIT_PROFESSOR_DEVOTION_EXTRA',              33);  //فوق العاده ايثارگري هيات علمي
define('SIT_PROFESSOR_TEACHING_RIGHT',              40);//حق التدريس هيات علمي
define('SIT_PROFESSOR_MISSION',                     42);//ماموريت هيات علمي
define('SIT_PROFESSOR_HEIAT_OMANA_SPECIAL_EXTRA',	44);//فوق العاده جذب هيات امنا
define('SIT_PROFESSOR_REMEDY_SERVICES_INSURE',		143);//بيمه خدمات درماني هيات علمي
define('SIT_PROFESSOR_TAX',                         146);//ماليات هيات علمي
define('SIT_PROFESSOR_RETIRED',                     149);//بازنشستگي هيات علمي
define('SIT_PROFESSOR_HANDSEL',                     163);//عيدي و پاداش هيات علمي
define('SIT_PROFESSOR_FOR_BYLAW_15_3015',           168);//بابت ب 15/3015
define('SIT_PROFESSOR_PARTICULAR_EXTRA',            186);//فوق العاده ويژه هيات علمي
define('PROFESSOR_FIRST_MONTH_MOGHARARY',           9911);//مقرري ماه اول هيات علمي
define('SIT_PROFESSOR_COLLECTIVE_SECURITY_INSURE',  9920);//بيمه تامين اجتماعي هيات علمي
define('RETURN_FIRST_MONTH_MOGHARARY',  524); // برگشت مقرری ماه اول برای جانبازان
define('SIT_RETURN_INSURE_AND_RETIRED_WOUNDED_PERSONS',506); // برگشت بیمه و بازنشستگی جانبازان
define('SIT1_BASE_SALARY',              01);//حقوق مبنا
define('SIT1_SPECIAL_EXTRA',            06);//فوق العاده مخصوص
//..............................................................................................
//انواع اقلام حقوقي كارمندان

define('SIT_STAFF_BASE_SALARY',						   02);//حقوق مبنا
define('SIT_STAFF_ANNUAL_INC',						   04);//افزايش سنواتي
define('SIT_STAFF_CHILD_RIGHT',                        7); //حق عايله مندي هيات کارمند
define('SIT_STAFF_CHILDREN_RIGHT',                     10);//حق اولاد کارمندان
define('SIT_STAFF_ADAPTION_DIFFERENCE',                12); //تفاوت تطبيق کارمندان
define('SIT_STAFF_FACILITIES_VITIOSITY_EXTRA',         14); //فوق العاده محروميت تسهيلات کارمندان
define('SIT_STAFF_DEPRIVED_REGIONS_ABSOPPTION_EXTRA',  15);//فوق العاده جذب مناطق محروم کارمندان
define('SIT_STAFF_DUTY_LOCATION_EXTRA',                16); //فوق العاده محل خدمت کارمندان
define('SIT_STAFF_JOB_EXTRA',                          17);  //فوق العاده شغل کارمندان
define('SIT_STAFF_DOMINANT_JOB_EXTRA',                 18); //فوق العاده شغل برجسته کارمندان
define('SIT_STAFF_WORK_WITH_RAY_EXTRA',                19); //فوق العاده کار با اشعه کارمندان
define('SIT_STAFF_ABSOPPTION_EXTRA',                   21); //فوق العاده جذب کارمندان
define('SIT_STAFF_HARD_WORK_EXTRA',                    23); //فوق العاده سختي کار کارمندان
define('SIT_STAFF_BAD_WEATHER_EXTRA',                  25);  //فوق العاده بدي آب و هواي کارمندان
define('SIT_STAFF_SHIFT_EXTRA',                        26); //فوق العاده نوبت کاري کارمندان
define('SIT_STAFF_REMEDY_SERVICES_INSURE',             38); //بيمه خدمات درماني کارمندان
define('SIT_EXTRA_WORK',							   39);//اضافه کار
define('SIT_STAFF_SENTRY_RIGHT',					   41);//حق کشيک
define('SIT_STAFF_MISSION',                            43);  //ماموريت کارمندان
define('SIT_STAFF_BAND6',                            45); //بند 6 دستورالعمل
define('SIT_MATCH_DIFF',							   56 ); // تفاوت تطبیق
define('SIT_STAFF_COLLECTIVE_SECURITY_INSURE',         144); //بيمه تامين اجتماعي کارمندان
define('SIT_STAFF_TAX',                                147);//ماليات کارمندان
define('SIT_STAFF_RETIRED',                            150); //بازنشستگي کارمندان
define('SIT_EMPLOYEE_HANDSEL',                         164);//عيدي و پاداش کارمندان
define('SIT_EMPLOYEE_SPECIAL_EXTRA',                   166); //فوق العاده ويژه کارمندان
define('SIT_EMPLOYEE_SEVEN_PERCENT_ABSORB_EXTRA',      167);//فوق العاده جذب 7%
define('SIT_STAFF_MIN_PAY',                            183);  //حداقل دريافتي کارمندان
define('SIT_STAFF_HEIAT_OMANA_SPECIAL_EXTRA',          284);  //فوق العاده جذب هيات امناي کارمندان
define('SIT_STAFF_ADJUST_EXTRA',          			   285); //فوق العاده تعديل
define('SIT_STAFF_ABSORB_EXTRA_8_9',				   286); //فوق العاده جذب بندهاي 8 و 9
define('SIT_STAFF_DEVOTION_ANNUAL_INC',				   287);  //افزايش سنواتي تشويقي ايثارگري
define('STAFF_FIRST_MONTH_MOGHARARY',                  9915);//مقرري ماه اول کارمندان
define('SIT_STAFF_HORTATIVE_EXTRA_WORK',               9921); //اضافه کار تشويقي کارمندان
define('SIT_STAFF_EXTRA_WORK',    39); //اضافه کار عادی کارمندان 
define('SIT2_BASE_SALARY',              02);//حقوق مبنا
define('SIT2_ANNUAL_INC',               04);//افزايش سنواتي
define('SIT2_MATCH_DIFFERENCE',         12);//تفاوت تطبيق - تفاوت همترازي

//................................................................................................
  //انواع اقلام حقوقي روزمزد بيمه اي

define('SIT3_BASE_SALARY',							   03);//حقوق مبنا
define('SIT_WORKER_BASE_SALARY',                       3);//مزد مبناي روزمزد بيمه اي
define('SIT_WORKER_CHILD_RIGHT',                       8);//حق عايله مندي روزمزد بيمه اي
define('SIT_STAFF_EQUALITY_DIFFERENCE',                13);//تفاوت همترازي کارمندان
define('SIT_WORKER_SHIFT_EXTRA',                       27);//فوق العاده نوبت کاري روزمزد بيمه اي
define('SIT_WORKER_HOUSING_RIGHT',                     30);//حق مسکن روزمزد بيمه اي
define('SIT_WORKER_FOOD_RIGHT',                        31);//حق خواروبار روزمزد بيمه اي
define('SIT_WORKER_COLLECTIVE_SECURITY_INSURE',        145);//بيمه تامين اجتماعي روزمزد بيمه اي
define('SIT_WORKER_TAX',                               148);//ماليات روزمزد بيمه اي
define('SIT_WORKER_EXTRA_WORK',                        152);//اضافه کار روزمزد بيمه اي
define('SIT_WORKER_FIX_FRACTION',                      162);//كسور ثابت براي روزمزد بيمهاي
define('SIT_WORKER_HANDSEL',                           165);//عيدي و پاداش روزمزد بيمه اي
define('SIT_WORKER_ANNUAL_INC',                        283);//افزايش سنواتي کارکنان روزمزد دانشگاه فردوسي
define('SIT_WORKER_HORTATIVE_EXTRA_WORK',              9922);//اضافه کار روزمزد بيمه اي
define('SIT_WORKER_DEVOTION_EXTRA',					   9969);// فوق العاده ايثارگري روزمزد بيمه اي
define('SPT_MAX_DAILY_SALARY_INSURE_INCLUDE',      32);//حداکثر دستمزد روزانه مشول بيمه
define('SPT_SOCIAL_SUPPLY_INSURE_EMPLOYER_VALUE',  20);//نرخ بيمه تامين اجتماعي - سهم کارفرما
define('SPT_UNEMPLOYMENT_INSURANCE_VALUE',         31);//نرخ بيمه تامين اجتماعي - بيمه بيکاري
define('SPT_SOCIAL_SUPPLY_INSURE_PERSON_VALUE',    201);//نرخ بيمه تامين اجتماعي - سهم بيمه شده


// انواع اقلام حقوقی قراردادی ها

define('SIT5_STAFF_BASE_SALARY',						602);//حقوق مبنا
define('SIT5_STAFF_ANNUAL_INC',							604);//افزايش سنواتي
define('SIT5_STAFF_ADAPTION_DIFFERENCE',				612);//تفاوت تطبیق قرارداد یکساله
define('SIT5_STAFF_JOB_EXTRA',							617);// فوق العاده شغل قرارداد یکساله
define('SIT5_STAFF_DOMINANT_JOB_EXTRA',					618);// فوق العاده شغل برجسته قرارداد یکساله
define('SIT5_STAFF_ABSOPPTION_EXTRA',                   621);//فوق العاده جذب افراد قراردادی
define('SIT5_EMPLOYEE_SPECIAL_EXTRA',                   766);// فوق العاده ویژه افراد قراردادی
define('SIT5_EMPLOYEE_SEVEN_PERCENT_ABSORB_EXTRA',      767);// فوق العاده جذب افراد قراردادی
define('SIT5_STAFF_MIN_PAY',							783); //حداقل دریافتی قرارداد یکساله
define('SIT_STAFF_DEFINED_ANNUAL_INC',					885);//افزايش سنواتي کارکنان یکساله دانشگاه فردوسی
define('SIT5_STAFF_EXTRA_ADJUST',                       9982);// فوق العاده تعدیل افراد قراردادی
define('SIT5_STAFF_EXTRA_WORK',							639);//اضافه کار عادی قراردادی ها
define('SIT5_STAFF_HORTATIVE_EXTRA_WORK',               10021); //اضافه کار تشويقي کارمندان
define('SIT5_WORKER_EXTRA_WORK',                        752); //اضافه کار روزمزد بيمه اي
define('SIT5_WORKER_HORTATIVE_EXTRA_WORK',              10022); //اضافه کار روزمزد بيمه اي

define('SIT5_STAFF_TAX',                               747); // مالیات قراردادی 
define('SIT5_STAFF_COLLECTIVE_SECURITY_INSURE',    744); // بیمه تامین اجتماعی قراردادی 
define('SIT5_STAFF_RETIRED',                            750); // بازنشستگی 
define('SIT5_STAFF_FIRST_MONTH_MOGHARARY',                  10015); // مقرری ماه اول 


// person_type =100
define('SIT_WORKER_RETIRED',                           151); //بازنشستگي روزمزد بيمه اي
define('EMPLOYEE_FIRST_MONTH_MOGHARARY_DEBT',          9933);// بدهی مقرری ماه اول 
//.............................................................................................
//ضریب های مربوط به قوانین سال 91
define('SPT_EDULEVEL_COAF', 8); //ضریب مدرک تحصیلی
define('SPT_BASE_GRADE', 2); //عدد مبنا در حقوق پایه و رتبه
define('SPT_EXTRA_SHOGHL', 3); //ضریب فوق العاده شغل
define('SPT_EXTRA_JAZB', 4); //ضریب فوق العاده جذب
define('SPT_EXTRA_VIJHE', 5); //ضریب فوق العاده ویژه
define('SPT_COEF_SUPERVISION' ,63 ) ; // درصد سرپرستی

define('SPT_EXTRA_SUPERVISION_SHOGHL', 6); //ضریب سرپرستی فوق العاده شغل
define('SPT_EXTRA_SUPERVISION_JAZB', 7); //ضریب سرپرستی فوق العاده جذب

//.............................................................................................
define('SIT_AGE_AND_ACCIDENT_INSURE_1' , 				 282);
define('SIT_AGE_AND_ACCIDENT_INSURE_2' ,				 9971);
define('SPT_NORMAL_INSURE_VALUE', 	             21);//نرخ بيمه عادي
define('SPT_NORMAL2_INSURE_VALUE', 	             25);//نرخ بيمه عادي2
define('SPT_FIRST_SURPLUS_INSURE_VALUE',           22);//نرخ بيمه مازاد 1
define('SPT_SECOND_SURPLUS_INSURE_VALUE',          23);//نرخ بيمه مازاد 2
define('SPT_HANDSEL_VALUE',                        24);//مبلغ عيدي و پاداش
define('IRAN_INSURE', 					             9919); // بیمه تکمیلی ایران
define('SPT_RETIREMENT_VALUE',                     19);//نرخ بازنشستگي
define('SPT_RETIREMENT_EMPLOYER_VALUE',            18);//نرخ بازنشستگي سهم سازمان
//.............................................................................................
define('SPT_RIAL_COEF', 1);//ضریب ریالی
define('SPT_SALARY_COEF', 2);//ضريب حقوقي
define('SPT_MIN_SALARY', 3);//حداقل حقوق - كارمندان
define('SPT_WORK_PLACE_COEF', 4);// ضریب فوق العاده محل خدمت
define('SPT_WHEATHER_COEF', 5);//ضریب بدی آب وهوا
define('SPT_NEW_WHEATHER_COEF',64) ; // ضریب جدید بدی آب و هوا 
define('SPT_LACK_COEF', 54);//ضریب مناطق کمتر توسعه یافته
//.............................................................................................	
define('SPT_JOB_SALARY', 6);//مزد شغل گروه روزمزد
define('SPT_GROUP1_ANNUAL_RATE', 62);//نرخ سنوات روزمزد گروه1 روزمزد
define('SPT_PROFESSOR_HOME_EXTRA', 7);//حق مسكن هيات علمي
define('SPT_HOME_EXTRA', 8);//حق مسكن روزمزد
define('SPT_FOOD_EXTRA', 9);//حق خواروبار روزمزد
define('SPT_BASE_VALUE', 34);//حداقل حقوق با توجه به مدرک تحصیلی
define('SPT_EDUC_VALUE', 35);// فوق العاده مدرک تحصیلی
define('SPT_HEIAT_OMANA_ABSORBTION_VALUE',  40);//مبلغ فوق العاده جذب هیات امنا - کارمندان
define('SPT_SAYER_MAZAYA1',       12); //سایر مزایا1
define('SPT_SAYER_MAZAYA2',       13);//سایر مزایا2
//.............................................................................................
define('SPT_FACILITY_PRIVATION_COEF', 	10);//ضریب فوق العاده محرومیت تسهیلات
define('SPT_PRIVATED_ZONE_ABSORB_COEF', 11);//ضریب فوق العاده جذب مناطق محروم
define('SPT_PROFESSOR_WHEATHER_COEF',   12);//ضريب بدي آب و هوا - هيات علمي
define('SPT_ABSORB_COEF', 				13);
define('SPT_PROFESSOR_ABSORB_COEF',     15);//ضريب فوق العاده جذب - هيات علمي
define('SPT_SPECIAL_EXTRA_COEF',        16);//ضريب فوق العاده مخصوص - هيات علمي
define('SPT_JOB_COEF', 					17);

define('SPT_PARTICULAR_EXTRA',          30);//فوق العاده ويژه هيات علمي
define('SPT_PARTICULAR_EXTRA_NEW',      36);//فوق العاده ویژه هیئت علمی جدید
define('MAX_SPT_PARTICULAR_EXTRA_NEW',  37);//سقف فوق العاده ویژه هیئت علمی

define('SPT_PROFESSOR_RIAL_COEF',       33);//ضريب ريالي هيات علمي
//.............................................................................................
//define('WRIT_SIGNATURE_POST_OWNER','عبدالحمید رضایی رکن آبادی');
define('WRIT_SIGNATURE_POST_OWNER','ابوالقاسم ساقی')  ;
define('WRIT_SIGNATURE_POST_TITLE','مدیر کارگزینی و رفاه دانشگاه');
//.............................................................................................
define("UNDER_DIPLOMA_LEVEL",1);
define("DIPLOMA_LEVEL",2);
define("HIGH_DIPLOMA_LEVEL",3);
define("BS_LEVEL",4);
define("MS_LEVEL",5);
define("PHD_LEVEL",6);
//.............................................................................................
//نوع فيش - عادي
define('NORMAL_PAYMENT',1);
//نوع فيش - عيدي و پاداش
define('HANDSEL_PAYMENT',2);
//.............................................................................................
    //وضعيت محاسبه حقوق
define('PAYMENT_STATE_NORMAL' , 1); //محاسبه حقوق در حالت معمولي
define('PAYMENT_STATE_FINAL'  , 2); //محاسبه قطعي شده است
//.............................................................................................
define('EXTRA_WORK_LIST',         1); // اضافه کار
define('TEACHING_LIST',           2); // حق التدریس
define('SENTRY_LIST',             3); // حق کشیک
define('PAY_GET_LIST',            4); // مزایای موردی
define('GROUP_PAY_GET_LIST',      5); // مزایای موردی گروهی 
define('DEC_PAY_GET_LIST',        6); // کسور موردی
define('GROUP_DEC_PAY_GET_LIST',  7); // کسور موردی گروهی
define('VALUE_EXTRA_WORK_LIST',   8); // اضافه کار مبلغی
define('MISSION_LIST', 9);  // ماموریت
define('WORK_SHEET_LIST',  		  1000); //علت اينکه اين عدد 1000 انتخاب شده اين است که براي محاسبه
    										 //حقوق براي استخراج تعداد روز کارکرد از اين عدد به عنوان ماکزيم لستفاده مي شود
    										 //لذا هيچ توعي در جدول pay_get_list نبايد بزرگتر از اين عدد تعريف شود
//.............................................................................................
define('PROFESSIONAL_WITHOUT_CERTIFY',293); //خبرگان بدون مدرک دانشگاهي


$SUMMARY_NAMES = array("sum" => "مجموع", "count" => "تعداد",	"avg" => "میانگین",	"max" => "ماکزیمم",	"min" => "مینیمم");

define('BENEFIT',1); //قلم حقوقي از نوع حقوق است
define('FRACTION',2); //قلم حقوقي از نوع کسور است

//............................................................................................

define('CREDIT_TOPIC_1', 1) ; //فصل یک 
define('CREDIT_TOPIC_OTHER', 99) ; // سایر فصول 

define('SUBTRACT_TYPE_LOAN', 1);
define('SUBTRACT_TYPE_FIX_FRACTION', 2);
define('SUBTRACT_TYPE_FIX_BENEFIT', 3);



?>