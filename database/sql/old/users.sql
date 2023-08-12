ALTER TABLE `users` ADD `draft_mobile` VARCHAR(100) NULL DEFAULT NULL AFTER `mobile`, ADD `draft_email` VARCHAR(256) NULL DEFAULT NULL AFTER `draft_mobile`;
