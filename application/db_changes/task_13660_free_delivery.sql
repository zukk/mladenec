ALTER TABLE `z_order_data`
ADD COLUMN `mkad_action`  int(1) NULL DEFAULT 0 AFTER `mkad`;

ALTER TABLE `z_order_data`
ADD COLUMN `mkad_real`  int(1) NULL DEFAULT 0 AFTER `mkad_action`;