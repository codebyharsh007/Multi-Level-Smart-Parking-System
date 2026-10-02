-- ============================================================
-- Multi-Level Smart Parking System
-- Demo Database for GitHub / Hackathon
-- ============================================================
-- IMPORTANT:
-- This file contains ONLY dummy/demo data.
-- No real customer, staff, email, mobile, DL or personal data.
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- FLOOR DETAILS
-- ============================================================

DROP TABLE IF EXISTS `floor_details`;

CREATE TABLE `floor_details` (
  `floor_id` int(11) NOT NULL AUTO_INCREMENT,
  `floor_name` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `floor_vehicle_type` enum('2 Wheeler','3 Wheeler','4 Wheeler') NOT NULL,
  `floor_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `Record_entry_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`floor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `floor_details`
(`floor_id`, `floor_name`, `capacity`, `floor_vehicle_type`, `floor_status`, `Record_entry_date`)
VALUES
(1, 'Ground Floor', 50, '4 Wheeler', 'Active', '2026-09-01 09:00:00'),
(2, 'First Floor', 100, '2 Wheeler', 'Active', '2026-09-01 09:00:00'),
(3, 'Second Floor', 60, '3 Wheeler', 'Active', '2026-09-01 09:00:00');

-- ============================================================
-- SETTINGS
-- ============================================================

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('emergency_mode', '0');

-- ============================================================
-- PARKING PRICE
-- ============================================================

DROP TABLE IF EXISTS `parking_price`;

CREATE TABLE `parking_price` (
  `pprice_id` int(11) NOT NULL AUTO_INCREMENT,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `vehicle_type` enum('2 Wheeler','3 Wheeler','4 Wheeler') NOT NULL,
  `price_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `record_entry_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`pprice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `parking_price`
(`pprice_id`, `price`, `duration`, `vehicle_type`, `price_status`, `record_entry_date`)
VALUES
(1, 10.00, 'First 1 Hour', '2 Wheeler', 'Active', '2026-09-01 09:00:00'),
(2, 5.00, 'Every Additional Hour', '2 Wheeler', 'Active', '2026-09-01 09:00:00'),
(3, 15.00, 'First 1 Hour', '3 Wheeler', 'Active', '2026-09-01 09:00:00'),
(4, 8.00, 'Every Additional Hour', '3 Wheeler', 'Active', '2026-09-01 09:00:00'),
(5, 50.00, 'First 1 Hour', '4 Wheeler', 'Active', '2026-09-01 09:00:00'),
(6, 25.00, 'Every Additional Hour', '4 Wheeler', 'Active', '2026-09-01 09:00:00');

-- ============================================================
-- STAFF DETAILS
-- ============================================================

DROP TABLE IF EXISTS `staff_details`;

CREATE TABLE `staff_details` (
  `staff_id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_name` varchar(100) NOT NULL,
  `staff_mobile_no` varchar(10) NOT NULL,
  `staff_gender` enum('Male','Female','Other') NOT NULL,
  `staff_dob` date NOT NULL,
  `proof_type` enum('Aadhaar','PAN Card') NOT NULL,
  `proof_number` varchar(20) NOT NULL,
  `staff_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `staff_entry_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `staff_type` enum('Admin','Staff','Entry Operator','Exit Operator') NOT NULL DEFAULT 'Staff',
  `staff_password` varchar(255) NOT NULL,
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `uq_mobile` (`staff_mobile_no`),
  UNIQUE KEY `uq_proof` (`proof_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demo login credentials:
-- Admin: Demo Admin / DemoAdmin@123
-- Staff: Demo Staff / DemoStaff@123
--
-- Passwords are stored as PHP password_hash() values.

INSERT INTO `staff_details`
(`staff_id`, `staff_name`, `staff_mobile_no`, `staff_gender`, `staff_dob`, `proof_type`, `proof_number`, `staff_status`, `staff_entry_date`, `staff_type`, `staff_password`)
VALUES
(1, 'Demo Admin', '9000000001', 'Male', '1990-01-01', 'Aadhaar', 'DEMOADMIN001', 'Active', '2026-09-01 09:00:00', 'Admin', '$2y$12$m.ICsBEQ3TXJna6y0Bb6a.kgTWk2hhK1tDGmXAtHOTykkZBI.4O/S'),
(2, 'Demo Staff', '9000000002', 'Male', '1995-05-12', 'PAN Card', 'DEMOSTAFF001', 'Active', '2026-09-01 09:00:00', 'Staff', '$2y$12$6j1C2SzpPvPIYCWIc2SL.eAzGxyObl6n.ImK6/.eWGUlaTRPD2FHS');

-- ============================================================
-- PARKING DETAILS
-- ============================================================

DROP TABLE IF EXISTS `parking_detail`;

CREATE TABLE `parking_detail` (
  `p_id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_no` varchar(20) NOT NULL,
  `vehicle_type` enum('2 Wheeler','3 Wheeler','4 Wheeler') NOT NULL,
  `vehicle_enter_time` datetime NOT NULL,
  `dl_no` varchar(20) NOT NULL,
  `mobile_no` varchar(10) NOT NULL,
  `floor_id` int(11) NOT NULL,
  `slot_no` varchar(20) NOT NULL,
  `staff_id_entry` int(11) NOT NULL,
  `vehicle_exit_time` datetime DEFAULT NULL,
  `staff_id_exit` int(11) DEFAULT NULL,
  `parking_duration` time DEFAULT NULL,
  `parking_charge` decimal(10,2) DEFAULT 0.00,
  `payment_mode` enum('Cash','UPI','Card') DEFAULT NULL,
  `record_entry_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`p_id`),
  KEY `idx_floor_slot` (`floor_id`,`slot_no`),
  KEY `idx_open` (`floor_id`,`vehicle_exit_time`),
  KEY `fk_pd_staffI` (`staff_id_entry`),
  KEY `fk_pd_staffO` (`staff_id_exit`),
  CONSTRAINT `fk_pd_floor` FOREIGN KEY (`floor_id`) REFERENCES `floor_details` (`floor_id`),
  CONSTRAINT `fk_pd_staffI` FOREIGN KEY (`staff_id_entry`) REFERENCES `staff_details` (`staff_id`),
  CONSTRAINT `fk_pd_staffO` FOREIGN KEY (`staff_id_exit`) REFERENCES `staff_details` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dummy parking history only.

INSERT INTO `parking_detail`
(`p_id`, `vehicle_no`, `vehicle_type`, `vehicle_enter_time`, `dl_no`, `mobile_no`, `floor_id`, `slot_no`, `staff_id_entry`, `vehicle_exit_time`, `staff_id_exit`, `parking_duration`, `parking_charge`, `payment_mode`, `record_entry_date`, `email`)
VALUES
(1, 'UP32DEMO01', '2 Wheeler', '2026-09-28 10:00:00', 'DLDEMO0001', '9000000011', 2, '2-001', 2, '2026-09-28 11:15:00', 2, '01:15:00', 10.00, 'Cash', '2026-09-28 04:30:00', NULL),
(2, 'UP32DEMO02', '4 Wheeler', '2026-09-28 11:00:00', 'DLDEMO0002', '9000000012', 1, '1-001', 2, '2026-09-28 13:30:00', 2, '02:30:00', 100.00, 'UPI', '2026-09-28 05:30:00', NULL),
(3, 'UP32DEMO03', '3 Wheeler', '2026-09-29 09:30:00', 'DLDEMO0003', '9000000013', 3, '3-001', 2, '2026-09-29 10:40:00', 2, '01:10:00', 15.00, 'Card', '2026-09-29 04:00:00', NULL),
(4, 'UP32DEMO04', '2 Wheeler', '2026-09-29 14:00:00', 'DLDEMO0004', '9000000014', 2, '2-002', 2, '2026-09-29 16:00:00', 2, '02:00:00', 15.00, 'UPI', '2026-09-29 08:30:00', NULL),
(5, 'UP32DEMO05', '4 Wheeler', '2026-09-30 10:30:00', 'DLDEMO0005', '9000000015', 1, '1-002', 2, '2026-09-30 12:00:00', 2, '01:30:00', 75.00, 'Cash', '2026-09-30 05:00:00', NULL);

-- ============================================================
-- AUTO_INCREMENT
-- ============================================================

ALTER TABLE `floor_details`
  MODIFY `floor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `parking_price`
  MODIFY `pprice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `staff_details`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `parking_detail`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

SET FOREIGN_KEY_CHECKS = 1;

COMMIT;
