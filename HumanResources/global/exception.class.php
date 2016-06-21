<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.07
//---------------------------
define("HR_WARNING","warning");
define("HR_ERROR","error");

define("ERROR_CORRECT_IS_NOT_COMPLETED","روال اصلاح قبلي به صورت ناقص انجام شده است . بنابراين امکان صدور حکم براي اين فرد وجود ندارد");
define("ERROR_FOR_RETIRED_PERSONS_CAN_NOT_ISSUE_WRIT",
	"به دليل اينکه وضعيت شخص بازنشسته ، بازخريد ، استعفا ، انتقال ، انفصال دائم و يا اخراج  مي باشد صدور حکم ميسر نمي باشد.");
define("ERROR_STAFF_ID_NOT_FOUND","هيچ كارمندي با شماره شناسايي فوق موجود نمي باشد");
define("EXECUTE_DATE_OF_NORMAL_WRIT_CANT_BEFORE_LAST_ONE_ERR","تاريخ اجراي حكم عادي نبايد قبل از آخرين حكم صادره باشد");
define("ERROR_PERSON_EDUCATIONS_NOT_SET","شخص داراي هيچگونه سابقه تحصيلي نمي باشد");
define("ERROR_NO_EDUCATION_LEVEL","");
define("ER_SEND_LETTER_NO_IS_REAPEATED","شماره دبيرخانه %0% در حکم شماره %1% استفاده شده است.");
define("ERR_ONDUTY_DAY_VALUE","روز سنوات خدمت بايد حداکثر 29 باشد.");
define("ERR_ONDUTY_MONTH_VALUE","ماه سنوات خدمت حداکثر بايد 11 باشد.");
define("POST_IS_NOT_VALID","شماره پست نامعتبر می باشد .");
define("POST_EXPIRE","پست مورد نظر در این تاریخ نا معتبر است");
define("WRIT_PAY_DATE_MUST_BE_GREATHER_OR_EQUAL_ISSUE_DATE","تاريخ پرداخت بايد بزرگتر مساوي تاريخ صدور باشد .");
define("NOT_ALLOW_ERR_RUSTICATION","به علت اينكه وضعيت استخدامي فرد اخراج مي باشد شما قادر به ايجاد حكم جديد نمي باشيد.");
define("NOT_ALLOW_ERR_RE_BUY","به علت اينكه وضعيت استخدامي فرد بازخريد مي باشد شما قادر به ايجاد حكم جديد نمي باشيد.");
define("TEMPORARY_FREE_WRIT_WR","این پست موقتا آزاد می باشد.");
define("BASE_SALARY_CALC_ERR","خطا در محاسبه حقوق مبنا.");
define("UNKNOWN_JOB_GROUP","گروه کاري مورد نظر تعريف نشده است.");
define("UNKNOWN_GROUP1_ANNUAL_RATE","نرخ سنوات روزمزد گروه 1 تعریف نشده است.");
define("SALARY_COEF_NOT_FOUND","ضريب حقوق تعريف نشده است.");
define("WRIT_SALARY_ITEM_NOT_FOUND","آيتم حقوقي مورد نظر پيدا نشد.");
define('RAY_EXTRA_CALC_ERR','خطا در محاسبه کار با اشعه.');
define('PROFESSOR_ABSORB_COEF_NOT_FOUND','ضريب فوق العاده جذب هيات علمي تعريف نشده است.');
define('PROFESSOR_ABSORB_EXTRA_CALC_ERR','خطا در محاسبه فوق العاده جذب هيات علمي.');
define('PROFESSOR_WHEATHER_COEF_NOT_FOUND','ضريب بدي آب و هواي هيات علمي تعريف نشده است.');
define('PROFESSOR_WHEATHER_EXTRA_CALC_ERR','خطا در محاسبه فوق العاده بدي آب و هواي هيات علمي.');
define('PROFESSOR_HOME_EXTRA_CALC_ERR','خطا در محاسبه حق مسکن.');
define('CAN_NOT_DELETE_SALARY_ITEMS' , 'خطا در حذف اقلام حقوقي حکم.');
define('TEHRAN_PROFESSOR_ABSORB_COEF_NOT_FOUND' , 'ضريب فوق العاده جذب براي تهران تعريف نشده است.');
define('SPECIAL_EXTRA_NOT_FOUND' , 'فوق العاده مخصوص براي اين شخص تعريف نشده است');
define('PARTICULAR_EXTRA_COEF_NOT_FOUND' , 'ضريب فوق العاده ويژه هيات علمي تعريف نشده است.');
define('PARTICULAR_EXTRA_CALC_ERR' , 'خطا در محاسبه فوق العاده ويژه.');
define('RIAL_COEF_NOT_FOUND' , 'ضريب ريالي تعريف نشده است.');
define('WHEATHER_COEF_NOT_FOUND' , 'ضريب بدي آب و هوا تعريف نشده است.');
define('WHEATHER_ITEM_CALC_ERR' , 'خطا در محاسبه فوق العاده بدي آب و هوا.');
define('WRONG_EVALUATION_SCORE' , 'امتياز ارزيابي صحيح نيست');
define("POST_HAS_DETERMINED_ERR","اين پست به شماره شناسايي '%0%' اختصاص داده شده است.");
define('CANNT_RELEASE_WRIT_POST', 'اين پست از طريق حكم به فرد داده شده است و امكان آزاد كردن آن از اين طريق وجود ندارد');
define('MIN_SALARY_NOT_FOUND', 'حداقل حقوق تعريف نشده است.');
define('EDUCATION_LEVEL_MUST_BE_MS','مقطع تحصيلي بايد کارشناسي ارشد ، معادل کارشناسي ارشد يا بالاتر باشد.');
define('JOB_FIELD_MUST_BE_EDUC_RESEARCH', 'رشته شغلي بايد آموزشي پژوهشي باشد.');
define('SPECIAL_EXTRA_COEF_NOT_FOUND', 'ضريب فوق العاده مخصوص تعريف نشده است.');
define('SPECIAL_EXTRA_CALC_ERR', 'خطا در محاسبه فوق العاده مخصوص');
define('MATCH_DIFFERENCE_IS_ZERO' , 'تفاوت تطبيق صفر است');
define('WORK_PLACE_COEF_NOT_FOUND', 'ضريب فوق العاده محل خدمت تعريف نشده است.');
define('JOB_ITEM_CALC_ERR', 'خطا در محاسبه فوق العاده شغل');
define('HIGH_JOB_COEF_NOT_FOUND', 'ضريب فوق العاده شغل برجسته پيدا نشد.');
define('ABSORB_ITEM_CALC_ERR', 'خطا در محاسبه فوق العاده جذب.');
define('NOT_ENOUGH_SCORE_ERR', 'امتياز شخص براي تخصيص فوق العاده ويژه کافي نمي باشد');
define('SEVEN_PERCENT_ABSORB_ITEM_CALC_ERR', 'خطا در محاسبه فوق العاده جذب 7%');
define('WORK_SHIFT_ITEM_CALC_ERR', 'خطا در محاسبه فوق العاده نوبت کاري');
define('SPECIAL_ABSORB_EXTRA_ITEM_CALC_ERR', 'خطا در محاسبه فوق العاده جذب هيات امنا');
define('DIFF_MATCH_ITEM_CALC_ERR', 'خطا در محاسبه تفاوت تطبیق');
define('EXCEED_OF_END_SEND_LETTER_NO_ERR', 'شماره هاي دبيرخانه به اتمام رسيده است.');
define('TOO_MANY_ERR', 'توقف به خاطر تعداد خطاي زياد.');
define('WRIT_CAN_NOT_DELETE', 'به دليل اينکه حکم در محاسبه حقوق استفاده شده است و يا در وضعيت مجاز نمي باشد، امکان حذف آن وجود ندارد.');
define('ER_CANNT_DELETE_CORREDTED_WRIT', 'از اين حکم در صدور حکم اصلاحي استفاده شده است و امکان حذف آن وجود ندارد .');
define('ER_CANNT_DELETE_NEW_WRITS', 'امکان حذف احکام جديد (به جز آخرين حکم) وجود ندارد .');
define('ER_DATE_RANGE_OVERLAP', 'بازه تاريخي ثبت شده اشتباه مي باشد و با تاريخهاي قبلي تداخل دارد .');
define('ER_PERSON_DONT_SAVE', 'ذخیره اطلاعات فرد مورد نظر با خطا مواجه شد.');
define('ER_ISSUE_DATE_IS_NOT_VALID', 'تاریخ صدور حکم نمی تواند بعد از تاریخ جاری باشد.');
define('ER_CONSTRAINT', 'به دليل داشتن رديف امکان حذف وجود ندارد.');
define('ER_CONTRACT_DATE', 'تاریخ اجرا در بازه زمانی قرارداد نمی باشد.');
define('LACK_COEF_NOT_FOUND', "ضریب مناطق کمتر توسعه یافته تعریف نشده است.");
define('LACK_ITEM_CALC_ERR', "خطا در محاسبه ضریب مناطق کمتر توسعه یافته");
define('WRIT_NOT_FOUND' ,'حکمي با شماره فوق موجود نمي باشد يا شما به آن دسترسي نداريد.');
define('UNKNOWN_JOB_SALARY','مزد شغل تعریف نشده است.');
define('SINGLES_CANT_GET_CHILD_LAW','حق عايله مندي به افراد مجرد تعلق نمي گيرد.');
define('UNKNOWN_HOME_EXTRA','حق مسکن تعريف نشده است.');
define('UNKNOWN_FOOD_EXTRA','حق خواروبار روزمزد بيمه اي تعريف نشده است.');
define('EXIST_OPEN_WRIT','در این تاریخ اجرا حکم دیگری وجود دارد که هنوز قابل ویرایش است.');
define('PARAM_CAN_NOT_DELETE','به علت اینکه این پارامتر حقوقی دارای اطلاعات وابسته می باشدقابل حذف نمی باشد.');
define('NOT_FAMILY_RESPONSIBLE','این فرد سرپرست خانواده نمی باشد.');
define('ZERO_INCLUDED_CHILDREN','این فرد فاقد فرزند تحت تکفل می باشد.');
define('TAX_CAN_NOT_DELETE','به علت اینکه این جدول مالیاتی دارای اطلاعات وابسته می باشد ، امکان حذف آن وجود ندارد.');
define('STAFF_TAX_CAN_NOT_DELETE','به علت اینکه این جدول مالیاتی برای فردی ثبت گردیده امکان حذف آن وجود ندارد .');
define('START_AND_END_DATE_MUST_NOT_BE_NULL','لطفا تاريخ شروع و پايان را وارد کنيد.');
define('ER_WITHOUT_SACRIFIC_DEVOTION_HISTORY','به دلیل اینکه شخص جانباز نیست امکان ثبت فیلد «مدت قابل قبول بازنشستگی که کسورات آن پرداخت شده» وجود ندارد.');
define('ER_PAIED_DURATION_OVER_DEVOTION_DURATION','مدت قابل قبول بازنشستگی که کسور آن  پرداخت شده از مدت رزمندگی نباید بیشتر باشد.');
define('ER_PERSON_DEVOTIONS_AND_WRITS_COINCIDENT','مدت ایثارگری با خدمت در سازمان نباید همپوشانی داشته باشد.');
define('ER_PERSON_DEVOTIONS_AND_MILITARY_SERVICE_COINCIDENT','مدت ایثارگری نباید با خدمت نظام وظیفه همپوشانی داشته باشد.');
define('START_DATE_MUST_NOT_BE_NULL','لطفا تاريخ شروع جانبازي را وارد کنيد.');
define('NOT_DEFINE_HEIAT_OMANA_PARAM','لطفا مبلغ فوق العاده جذب هیئت امنا را تعریف نمایید.');
define('IS_AVAILABLE','اطلاعات مربوط به این قلم قبلا با اولویت %0%  اضافه گردیده است .'); 

//--------------- SALARY SYSTEM EXCEPTION DEFINITIONS -------------------
define('ER_CAN_NOT_RUN_PAYMENT_CALC','در حال حاضر فرايند محاسبه حقوق توسط کاربر %0% در حال اجراست. لطفا %1% ثانيه بعد مجددا سعي کنيد.');

?>