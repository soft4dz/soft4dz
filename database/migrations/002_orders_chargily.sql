-- Ajoute le mode de paiement Chargily Pay (à exécuter une fois si la base existe déjà)
ALTER TABLE `orders` MODIFY `payment_method` ENUM('cib','edahabia','bank_transfer','free','chargily') NOT NULL DEFAULT 'bank_transfer';
