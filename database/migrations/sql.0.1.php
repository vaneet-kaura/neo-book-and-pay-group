<?php 
 // -- nbap_service --
$sql = array();
$sql[] = "CREATE TABLE `{prefix}service_group` ( 
	`id` BIGINT NOT NULL AUTO_INCREMENT , 
	`service_id` BIGINT DEFAULT NULL , 
	`capacity_min` INT DEFAULT NULL , 
	`capacity_max` INT DEFAULT NULL , 
	PRIMARY KEY (`id`)
){charset_collate};";

$sql[] = "CREATE TABLE `{prefix}staff_service_group` ( 
	`id` BIGINT NOT NULL AUTO_INCREMENT , 
	`staff_id` BIGINT DEFAULT NULL , 
	`service_id` BIGINT DEFAULT NULL , 
	`capacity_min` INT DEFAULT NULL , 
	`capacity_max` INT DEFAULT NULL , 
	PRIMARY KEY (`id`)
){charset_collate};";

$sql[] = "CREATE TABLE `{prefix}appointment_group` ( 
	`id` BIGINT NOT NULL AUTO_INCREMENT , 
	`appointment_id` BIGINT DEFAULT NULL , 
	`capacity` INT DEFAULT NULL , 
	`detail_data` TEXT DEFAULT NULL , 
	PRIMARY KEY (`id`)
){charset_collate};";

$sql[] = "ALTER TABLE `{prefix}service_group` ADD  CONSTRAINT `service_group_service_id_fk` FOREIGN KEY (`service_id`) REFERENCES `{prefix}service`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$sql[] = "ALTER TABLE `{prefix}appointment_group` ADD  CONSTRAINT `appointment_group_appointment_id_fk` FOREIGN KEY (`appointment_id`) REFERENCES `{prefix}appointment`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$sql[] = "ALTER TABLE `{prefix}staff_service_group` ADD  CONSTRAINT `staff_service_group_service_id_fk` FOREIGN KEY (`service_id`) REFERENCES `{prefix}service`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$sql[] = "ALTER TABLE `{prefix}staff_service_group` ADD  CONSTRAINT `staff_service_group_staff_id_fk` FOREIGN KEY (`staff_id`) REFERENCES `{prefix}staff`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$sql[] = "ALTER TABLE {prefix}staff_service_group ADD UNIQUE KEY unique_staff_service (staff_id, service_id);";


return $sql;
