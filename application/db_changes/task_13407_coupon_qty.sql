ALTER table z_coupon_good ADD column min_qty tinyint not null DEFAULT 1;
alter table z_coupon_good drop primary key;
alter table z_coupon_good add primary key (coupon_id, good_id, min_qty);
