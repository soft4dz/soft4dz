<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class ApiController extends Controller {
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function searchProducts(): void {
        $q       = trim($_GET['q'] ?? '');
        $limit   = min(20, (int)($_GET['limit'] ?? 8));
        $results = [];
        if (strlen($q) >= 2) {
            $results = $this->db->fetchAll(
                "SELECT id, name, slug, price, sale_price, image
                 FROM products
                 WHERE status='active' AND name LIKE ?
                 ORDER BY sales_count DESC LIMIT $limit",
                ["%$q%"]
            );
        }
        $this->json(['results' => $results]);
    }

    public function cartCount(): void {
        $this->json(['count' => cartCount()]);
    }

    public function aiChat(): void {
        $message = trim($_POST['message'] ?? '');
        if (!$message) $this->json(['error' => 'Message vide'], 400);

        // Simple rule-based AI + context
        $reply = $this->generateAIResponse($message);
        $this->json(['reply' => $reply]);
    }

    public function stats(): void {
        if (!Auth::isAdmin()) $this->json(['error' => 'Unauthorized'], 403);
        $this->json([
            'total_revenue' => $this->db->fetch("SELECT COALESCE(SUM(total),0) AS v FROM orders WHERE payment_status='paid'")['v'],
            'total_orders'  => $this->db->count("SELECT COUNT(*) FROM orders"),
            'total_users'   => $this->db->count("SELECT COUNT(*) FROM users WHERE role='customer'"),
        ]);
    }

    private function generateAIResponse(string $msg): string {
        $msg = mb_strtolower($msg);
        $responses = [
            ['keywords' => ['livraison','délai','combien de temps'], 'reply' => "La livraison des produits numériques est instantanée après confirmation du paiement. Pour les virements bancaires, comptez 1-24h ouvrables. 📦"],
            ['keywords' => ['paiement','payer','cib','edahabia','virement'], 'reply' => "Nous acceptons CIB, Edahabia (BaridiMob) et le virement bancaire. Pour les cartes, le traitement est immédiat. Pour les virements, veuillez joindre le reçu. 💳"],
            ['keywords' => ['remboursement','rembourser','retour'], 'reply' => "Notre politique de remboursement couvre 7 jours après l'achat pour les produits défectueux. Ouvrez un ticket de support avec votre numéro de commande. 🔄"],
            ['keywords' => ['mot de passe','connexion','compte'], 'reply' => "Pour réinitialiser votre mot de passe, cliquez sur 'Mot de passe oublié' sur la page de connexion. Vous recevrez un email dans quelques minutes. 🔑"],
            ['keywords' => ['prix','combien coûte','tarif'], 'reply' => "Nos prix sont affichés en Dinars Algériens (DZD) sur chaque produit. Consultez notre catalogue pour voir toutes nos offres actuelles. 💰"],
            ['keywords' => ['netflix','spotify','canva','office'], 'reply' => "Nous proposons des comptes et abonnements premium à des prix imbattables. Vérifiez notre catalogue pour les disponibilités actuelles ! 🎬"],
            ['keywords' => ['contact','parler','humain','agent'], 'reply' => "Je vous mets en relation avec notre équipe ! Ouvrez un ticket dans votre dashboard ou écrivez à support@soft4dz.com. Temps de réponse moyen: 2h. 👨‍💼"],
        ];
        foreach ($responses as $r) {
            foreach ($r['keywords'] as $kw) {
                if (str_contains($msg, $kw)) return $r['reply'];
            }
        }
        return "Merci pour votre message ! Je ne suis pas sûr de comprendre votre demande. Pouvez-vous reformuler ou ouvrir un ticket de support pour une aide personnalisée ? 😊";
    }
}
