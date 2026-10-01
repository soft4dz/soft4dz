-- Connexion sociale Google / Facebook (à exécuter une fois si la base existe déjà)
ALTER TABLE `users`
  MODIFY `password_hash` VARCHAR(255) NULL,
  ADD COLUMN `oauth_provider` VARCHAR(20) NULL AFTER `remember_token`,
  ADD COLUMN `oauth_id` VARCHAR(191) NULL AFTER `oauth_provider`,
  ADD UNIQUE INDEX `idx_oauth` (`oauth_provider`, `oauth_id`);
