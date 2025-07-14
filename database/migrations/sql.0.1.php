<?php 
 // -- nbap_service --
$sql = array();
$sql[] = "CREATE TABLE `{prefix}appointment_group` (
	 `id` bigint(20) NOT NULL AUTO_INCREMENT,
	 `appointment_id` bigint(20) DEFAULT NULL,
	 `capacity` int(11) DEFAULT NULL,
	 `detail_data` text DEFAULT NULL,
	 PRIMARY KEY (`id`),
	 UNIQUE KEY `unique_appointment` (`appointment_id`),
	 CONSTRAINT `appointment_group_appointment_id_fk` FOREIGN KEY (`appointment_id`) REFERENCES `{prefix}appointment` (`id`) ON DELETE CASCADE
) {charset_collate};";

$sql[] = "CREATE TABLE `{prefix}service_group` (
	 `id` bigint(20) NOT NULL AUTO_INCREMENT,
	 `service_id` bigint(20) DEFAULT NULL,
	 `capacity_min` int(11) DEFAULT NULL,
	 `capacity_max` int(11) DEFAULT NULL,
	 PRIMARY KEY (`id`),
	 UNIQUE KEY `unique_service` (`service_id`),
	 CONSTRAINT `service_group_service_id_fk` FOREIGN KEY (`service_id`) REFERENCES `{prefix}service` (`id`) ON DELETE CASCADE
){charset_collate};";

$sql[] = "CREATE TABLE `{prefix}staff_service_group` (
	 `id` bigint(20) NOT NULL AUTO_INCREMENT,
	 `staff_id` bigint(20) DEFAULT NULL,
	 `service_id` bigint(20) DEFAULT NULL,
	 `capacity_min` int(11) DEFAULT NULL,
	 `capacity_max` int(11) DEFAULT NULL,
	 PRIMARY KEY (`id`),
	 UNIQUE KEY `unique_staff_service` (`staff_id`,`service_id`),
	 KEY `staff_service_group_service_id_fk` (`service_id`),
	 CONSTRAINT `staff_service_group_service_id_fk` FOREIGN KEY (`service_id`) REFERENCES `{prefix}service` (`id`) ON DELETE CASCADE,
	 CONSTRAINT `staff_service_group_staff_id_fk` FOREIGN KEY (`staff_id`) REFERENCES `{prefix}staff` (`id`) ON DELETE CASCADE
){charset_collate};";

return $sql;