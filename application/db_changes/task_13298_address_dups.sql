# убиваем у адресов пробелы

UPDATE z_user_address SET city = TRIM(city);
UPDATE z_user_address SET street = TRIM(street);
UPDATE z_user_address SET house = TRIM(house);
UPDATE z_user_address SET kv = TRIM(kv);

# находим и убиваем адреса, ни разу не использованные в заказах
CREATE TABLE `bad_addr` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO bad_addr
  select a.id  from z_user_address a left join z_order_data od on (a.id = od.address_id) where od.address_id is null;

DELETE FROM z_user_address WHERE id IN (select id from bad_addr);

# поиск дублей внутри одного юзера
SELECT a2.id FROM `z_user_address` a1
  JOIN z_user_address a2 on
  (a1.id > a2.id and a1.user_id = a2.user_id and a1.city = a2.city and a1.street = a2.street and a1.house = a2.house and a1.kv = a2.kv)