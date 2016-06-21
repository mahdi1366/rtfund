/*----------------------------hrmstotal ( payments and payment_items )------------------------------*/
use hrmstotal;

delete p.*
         from payments p inner join staff s
                             on p.staff_id = s.staff_id
                   where  p.payment_type = 1 and s.person_type = 10
;



insert into
          payments (staff_id , pay_year ,pay_month ,writ_id ,writ_ver ,start_date, end_date ,payment_type, message, bank_id, account_no, state )
          ( select staff_id , pay_year ,pay_month ,writ_id ,writ_ver ,start_date, end_date ,payment_type, message, bank_id, account_no, state
          from hrmr.payments
          where  payment_type = 1 ) ; 


delete p.*
         from payment_items p inner join staff s
                                    on p.staff_id = s.staff_id

                   where  p.payment_type = 1 and s.person_type = 10 ;

insert into payment_items (row_id , pay_year , pay_month , staff_id , salary_item_type_id ,  pay_value ,  get_value , param1 ,  param2 ,
          param3 ,  param4 , param5 , param6 ,cost_center_id , payment_type , debt_total_id ,
          debt_ledger_id , debt_tafsili_id , debt_tafsili2_id , cred_total_id , cred_ledger_id , cred_tafsili_id , cred_tafsili2_id ,
          diff_get_value , diff_pay_value , diff_param1 , diff_param2 , diff_param3 , diff_param4 , diff_param5 , diff_param6 , diff_value_coef ,
          param7 , diff_param7 , diff_param1_coef ,diff_param2_coef , diff_param3_coef , diff_param4_coef , diff_param5_coef , diff_param6_coef ,
          diff_param7_coef , param8 ,param9, diff_param8 ,diff_param9 ,diff_param8_coef, diff_param9_coef)( select @i := @i + 1 , pay_year , pay_month , staff_id , salary_item_type_id ,  pay_value ,  get_value , param1 ,  param2 ,
          param3 ,  param4 , param5 , param6 ,cost_center_id , payment_type , debt_total_id ,
          debt_ledger_id , debt_tafsili_id , debt_tafsili2_id , cred_total_id , cred_ledger_id , cred_tafsili_id , cred_tafsili2_id ,
          diff_get_value , diff_pay_value , diff_param1 , diff_param2 , diff_param3 , diff_param4 , diff_param5 , diff_param6 , diff_value_coef ,
          param7 , diff_param7 , diff_param1_coef ,diff_param2_coef , diff_param3_coef , diff_param4_coef , diff_param5_coef , diff_param6_coef ,
          diff_param7_coef , param8 ,param9, diff_param8 ,diff_param9 ,diff_param8_coef, diff_param9_coef 
          from hrmr.payment_items ,(select @i:=0) t
          where payment_type = 1 ) ; 
/*--------------------------------------------------------------------------------------------------*/

