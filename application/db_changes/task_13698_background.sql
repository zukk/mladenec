ALTER TABLE `z_config`
ADD COLUMN `link_left`  varchar(225) NOT NULL AFTER `emails`,
ADD COLUMN `link_right`  varchar(255) NOT NULL AFTER `link_left`,
ADD COLUMN `image_id`  int(11) NOT NULL AFTER `link_right`;

