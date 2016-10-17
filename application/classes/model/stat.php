<?php

class Model_Stat extends  ORM {

    protected $_table_name = 'z_stat';

    protected $_table_columns = [
        'id' => '',  'sdate' => '', 'new' => '', 'new_card' => '', 'sum' => '', 'sum_card' => '', 'complete' => '', 'complete_card' => '',
		'complete_sum' => '', 'complete_sum_card' => '', 'cancel' => '', 'cancel_card' => '', 'cancel_sum' => '', 'cancel_sum_card' => ''
    ];
	
	public static function updateStat()
    {
		$from = '2010-01-01';
		$to = date('Y-m-d');

		DB::query(Database::UPDATE, "TRUNCATE z_stat")->execute();
		DB::query(Database::INSERT, "
			INSERT INTO `z_stat` (
			  `sdate`,`new`,`new_card`,`sum`,`sum_card`,`complete`,`complete_card`,`complete_sum`,`complete_sum_card`,
			  `cancel`,`cancel_card`,`cancel_sum`,`cancel_sum_card`) (
			SELECT 
				DATE(sent) as sdate,
				COUNT(*) as new,
				SUM(IF(pay_type = ".Model_Order::PAY_CARD.", 1, 0)) as new_card,

				SUM(price + price_ship) as sum,
				SUM(IF(pay_type = ".Model_Order::PAY_CARD.", price + price_ship, 0)) as sum_card,

				SUM(IF(status = 'F', 1, 0)) as complete,
				SUM(IF(status = 'F' AND pay_type = ".Model_Order::PAY_CARD.", 1, 0)) as complete_card,

				SUM(IF(status = 'F', price + price_ship, 0)) as complete_sum,
				SUM(IF(status = 'F' AND pay_type = ".Model_Order::PAY_CARD.", price + price_ship, 0)) as complete_sum_card,

				SUM(IF(status = 'X', 1, 0)) as cancel,
				SUM(IF(status = 'X' AND pay_type = ".Model_Order::PAY_CARD.", 1, 0)) as cancel_card,

				SUM(IF(status = 'X', price + price_ship, 0)) as cancel_sum,
				SUM(IF(status = 'X' AND pay_type = ".Model_Order::PAY_CARD.", price + price_ship, 0)) as cancel_sum_card

			FROM  `z_order`
			WHERE created > '".$from."'
			GROUP BY 1
			ORDER BY 1 ASC )
		")->execute();
		DB::query(Database::UPDATE, "TRUNCATE z_stat_monthly")->execute();
		DB::query(Database::INSERT, "
			INSERT INTO `z_stat_monthly` (
			  `sdate`,`new`,`new_card`,`sum`,`sum_card`,`complete`,`complete_card`,`complete_sum`,
			  `complete_sum_card`,`cancel`,`cancel_card`,`cancel_sum`,`cancel_sum_card`) (
			SELECT 
				DATE(CONCAT(YEAR(sdate), '-', MONTH(sdate), '-01')) as sdate,
				SUM(new) as new,
				SUM(new_card) as new_card,

				SUM(sum) as sum,
				SUM(sum_card) as sum_card,

				SUM(complete) as complete,
				SUM(complete_card) as complete_card,

				SUM(complete_sum) as complete_sum,
				SUM(complete_sum_card) as complete_sum_card,

				SUM(cancel) as cancel,
				SUM(cancel_card) as cancel_card,

				SUM(cancel_sum) as cancel_sum,
				SUM(cancel_sum_card) as cancel_sum_card

			FROM  `z_stat`
			GROUP BY 1
			ORDER BY 1 DESC )
		")->execute();
	}
}