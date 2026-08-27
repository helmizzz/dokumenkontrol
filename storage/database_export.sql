-- Hasil Export dari SQLite ke MySQL
SET FOREIGN_KEY_CHECKS=0;

-- Dumping data for table `roles`
TRUNCATE TABLE `roles`;
INSERT INTO `roles` (`id`, `role_name`) VALUES ('1', 'Superadmin');
INSERT INTO `roles` (`id`, `role_name`) VALUES ('2', 'Admin');
INSERT INTO `roles` (`id`, `role_name`) VALUES ('3', 'User');

-- Dumping data for table `departments`
TRUNCATE TABLE `departments`;
INSERT INTO `departments` (`id`, `dept_name`, `status`, `dept_code`) VALUES ('1', 'IT', '1', NULL);
INSERT INTO `departments` (`id`, `dept_name`, `status`, `dept_code`) VALUES ('2', 'HRD', '1', NULL);
INSERT INTO `departments` (`id`, `dept_name`, `status`, `dept_code`) VALUES ('3', 'Finance', '1', NULL);

-- Dumping data for table `years`
TRUNCATE TABLE `years`;
INSERT INTO `years` (`id`, `year_value`, `status`) VALUES ('1', '2021', '1');

-- Dumping data for table `users`
TRUNCATE TABLE `users`;
INSERT INTO `users` (`id`, `username`, `password`, `role_id`, `dept_id`, `full_name`, `status`) VALUES ('1', 'admin', '$2y$10$yzsuU8bJFyGlxhQQ/lc2LO3.lxJRQF7nRXDjm2ezG29d6s2auKd4.', '1', '1', 'admin', '1');
INSERT INTO `users` (`id`, `username`, `password`, `role_id`, `dept_id`, `full_name`, `status`) VALUES ('2', 'ituser1', '$2y$10$ZiN/uKMWdGDqOft5Y03SqeA6cmamba2nbAL9MgT0/wnLqva2rIVgy', '3', '1', 'ituser1', '1');
INSERT INTO `users` (`id`, `username`, `password`, `role_id`, `dept_id`, `full_name`, `status`) VALUES ('3', 'adminit', '$2y$10$T5xceTpJWMeud9nmS55c4.bIMlyV7a59IbtLV1XUTGT7a8AE1At5O', '2', '1', 'adminit', '1');

-- Dumping data for table `documents`
TRUNCATE TABLE `documents`;
INSERT INTO `documents` (`id`, `doc_number`, `title`, `file_path`, `is_public`, `year_id`, `month_value`, `dept_id`, `uploaded_by`, `created_at`, `updated_at`) VALUES ('1', 'SOP-HRD-001', 'Dokumen HRD', 'DOC_6a476560180217.99263324.pdf', '1', '1', '8', '1', '1', '2026-07-03 07:31:44', NULL);

-- Dumping data for table `activity_logs`
TRUNCATE TABLE `activity_logs`;
INSERT INTO `activity_logs` (`id`, `user_id`, `action_type`, `module`, `description`, `created_at`) VALUES ('1', '1', 'CREATE', 'Tahun', 'Menambahkan master tahun baru: 2021', '2026-07-03 07:31:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action_type`, `module`, `description`, `created_at`) VALUES ('2', '1', 'CREATE', 'Dokumen', 'Mengunggah dokumen baru bernomor: SOP-HRD-001', '2026-07-03 07:31:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action_type`, `module`, `description`, `created_at`) VALUES ('3', '1', 'CREATE', 'User Management', 'Mendaftarkan user baru: ituser1 (ituser1)', '2026-07-03 07:41:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action_type`, `module`, `description`, `created_at`) VALUES ('4', '1', 'CREATE', 'User Management', 'Mendaftarkan user baru: adminit (adminit)', '2026-07-03 07:43:15');

-- Dumping data for table `document_shares`
TRUNCATE TABLE `document_shares`;
INSERT INTO `document_shares` (`id`, `document_id`, `token`, `password_hash`, `expires_at`, `created_by`, `created_at`) VALUES ('1', '1', '59195778b4289d1d7cc6a09649ca5083', '$2y$10$4Sk07ZehuDUQ/TVLxSrgGeyyTbxO6lByjlHeJ1eImFJJsISpgf9mK', '2026-07-03 18:58:00', '1', '2026-07-03 08:55:13');

SET FOREIGN_KEY_CHECKS=1;
