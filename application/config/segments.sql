# сегментация пользователей, всех сразу
# учитываем только тех кто бывал на сайте (last_visit > 0)

TRUNCATE user_segment;

UPDATE z_user SET segments_recount_ts = unix_timestamp() WHERE last_visit > 0;

INSERT INTO user_segment (`user_id`, `pregnant`, `pregnant_terms`, `last_visit`, arpu, last_order, last_order_sum, orders_count, orders_sum)
SELECT u.id, u.pregnant, u.pregnant_terms, FROM_UNIXTIME(u.last_visit), if(u.qty > 0, u.`sum`/u.qty,  0), o.created, o.price, u.qty, u.`sum`
FROM z_user u LEFT JOIN z_order o ON (u.last_order = o.id)
WHERE last_visit > 0;

# сумма заказов кгт
INSERT INTO user_segment (user_id, sum_big)
SELECT u.id, SUM(`og`.`price` * `og`.`quantity`) as `sum_big` FROM z_order o
JOIN z_user u ON (o.user_id = u.id)
JOIN z_order_good og ON (og.order_id = o.id)
JOIN z_good g ON (g.big = 1 AND g.id = og.good_id)
WHERE o.status = 'F' AND u.last_visit > 0
 GROUP BY u.id
 ON DUPLICATE KEY UPDATE sum_big = VALUES(sum_big);

# сумма заказов подгузов
INSERT INTO user_segment (user_id, sum_diaper)
SELECT u.id, SUM(`og`.`price` * `og`.`quantity`) as `sum_diaper` FROM z_order o
JOIN z_user u ON (o.user_id = u.id)
JOIN z_order_good og ON (og.order_id = o.id)
JOIN z_good g ON (g.section_id = 29798 AND g.id = og.good_id)
WHERE o.status = 'F' AND u.last_visit > 0
 GROUP BY u.id
 ON DUPLICATE KEY UPDATE sum_diaper = VALUES(sum_diaper);

# сумма заказов еды
INSERT INTO user_segment (user_id, sum_eat)
  SELECT u.id, SUM(`og`.`price` * `og`.`quantity`) as `sum_eat` FROM z_order o
    JOIN z_user u ON (o.user_id = u.id)
    JOIN z_order_good og ON (og.order_id = o.id)
    JOIN z_good g ON (g.section_id IN (29065,98670,29150,28985,29293,28935,29253,29051,29138,29413,28968,28962) AND g.id = og.good_id)
  WHERE o.status = 'F' AND u.last_visit > 0
  GROUP BY u.id
ON DUPLICATE KEY UPDATE sum_eat = VALUES(sum_eat);

# сумма заказов игрушек
INSERT INTO user_segment (user_id, sum_toy)
  SELECT u.id, SUM(`og`.`price` * `og`.`quantity`) as `sum_toy` FROM z_order o
    JOIN z_user u ON (o.user_id = u.id)
    JOIN z_order_good og ON (og.order_id = o.id)
    JOIN z_good g ON (g.section_id IN (29585,31542,31541,116972,29562,31341,116970,57185,43630,116971,116969,88586,116957,119577) AND g.id = og.good_id)
  WHERE o.status = 'F' AND u.last_visit > 0
  GROUP BY u.id
ON DUPLICATE KEY UPDATE sum_toy = VALUES(sum_toy);

# сумма заказов ср-в по уходу
INSERT INTO user_segment (user_id, sum_care)
  SELECT u.id, SUM(`og`.`price` * `og`.`quantity`) as `sum_care` FROM z_order o
    JOIN z_user u ON (o.user_id = u.id)
    JOIN z_order_good og ON (og.order_id = o.id)
    JOIN z_good g ON (g.section_id IN (28856,28628,28783,28719,28836,53850,28682,28704) AND g.id = og.good_id)
  WHERE o.status = 'F' AND u.last_visit > 0
  GROUP BY u.id
ON DUPLICATE KEY UPDATE sum_care = VALUES(sum_care);

# сумма заказов одежды
INSERT INTO user_segment (user_id, sum_dress)
  SELECT u.id, SUM(`og`.`price` * `og`.`quantity`) as `sum_dress` FROM z_order o
    JOIN z_user u ON (o.user_id = u.id)
    JOIN z_order_good og ON (og.order_id = o.id)
    JOIN z_good g ON (g.section_id IN (105926,105927,105928,105929,115704,98250) AND g.id = og.good_id)
  WHERE o.status = 'F' AND u.last_visit > 0
  GROUP BY u.id
ON DUPLICATE KEY UPDATE sum_dress = VALUES(sum_dress);

UPDATE user_segment SET `buy_big` = 1 where sum_big > 0;
UPDATE user_segment SET `buy_diaper` = 1 where sum_diaper > 0;
UPDATE user_segment SET `buy_eat` = 1 where sum_eat > 0;
UPDATE user_segment SET `buy_toy` = 1 where sum_toy > 0;
UPDATE user_segment SET `buy_care` = 1 where sum_care > 0;
UPDATE user_segment SET `buy_dress` = 1 where sum_dress > 0;

# использовал купоны
INSERT INTO user_segment (user_id, sert_use)
  SELECT u.id, 1 FROM z_order o
    JOIN z_user u ON (o.user_id = u.id)
  WHERE o.status = 'F' AND u.last_visit > 0 AND o.coupon_id > 0
  GROUP BY u.id
ON DUPLICATE KEY UPDATE sert_use = VALUES(sert_use);

# дети
INSERT INTO user_segment (user_id, childs, has_boy, has_girl, child_birth_max, child_birth_min)
SELECT user_id, GROUP_CONCAT( DISTINCT c.birth ORDER BY c.birth) as childs, IF (MAX(c.sex) = 1, 1, 0) as has_boy, IF (MIN(c.sex) = 0, 1, 0) as has_girl,
  MAX(c.birth) as child_birth_max, MIN(c.birth) as child_birth_min
FROM z_user_child c
  JOIN z_user u on (u.id = c.user_id)
  WHERE u.last_visit > 0
GROUP BY user_id
ON DUPLICATE KEY UPDATE childs = VALUES(childs),
  has_boy = VALUES(has_boy), has_girl = VALUES(has_girl),
  child_birth_max = VALUES(child_birth_max), child_birth_min = VALUES(child_birth_min);



