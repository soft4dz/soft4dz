<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->requireAuth();
    }

    public function index(): void {
        $uid    = Auth::id();
        $recent = $this->db->fetchAll(
            "SELECT o.id, o.order_number, o.total, o.status, o.payment_status, o.created_at,
                    COUNT(oi.id) AS item_count
             FROM orders o
             JOIN order_items oi ON oi.order_id = o.id
             WHERE o.user_id = ?
             GROUP BY o.id ORDER BY o.created_at DESC LIMIT 5",
            [$uid]
        );

        $totalSpent = $this->db->count(
            "SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = ? AND payment_status = 'paid'", [$uid]
        );

        $openTickets = $this->db->count(
            "SELECT COUNT(*) FROM tickets WHERE user_id = ? AND status IN ('open','replied')", [$uid]
        );

        $activeSubs = $this->db->fetchAll(
            "SELECT s.*, sp.name AS plan_name, sp.billing_cycle FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             WHERE s.user_id = ? AND s.status = 'active'", [$uid]
        );

        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [$uid]
        );

        $this->view('dashboard.index', compact('recent', 'totalSpent', 'openTickets', 'activeSubs', 'notifications'), 'dashboard');
    }

    public function orders(): void {
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->db->paginate(
            "SELECT o.*, COUNT(oi.id) AS item_count
             FROM orders o
             JOIN order_items oi ON oi.order_id = o.id
             WHERE o.user_id = ?
             GROUP BY o.id ORDER BY o.created_at DESC",
            [Auth::id()], $page, 10
        );
        $this->view('dashboard.orders', $result, 'dashboard');
    }

    public function orderDetail(string $id): void {
        $order = $this->db->fetch(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [(int)$id, Auth::id()]
        );
        if (!$order) $this->abort(404);

        $items = $this->db->fetchAll(
            "SELECT oi.*, p.image FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?",
            [$order['id']]
        );

        $this->view('dashboard.order_detail', compact('order', 'items'), 'dashboard');
    }

    public function subscriptions(): void {
        $subs = $this->db->fetchAll(
            "SELECT s.*, sp.name, sp.billing_cycle, sp.features, p.name AS product_name
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             LEFT JOIN products p ON p.id = sp.product_id
             WHERE s.user_id = ?
             ORDER BY s.created_at DESC",
            [Auth::id()]
        );
        $this->view('dashboard.subscriptions', compact('subs'), 'dashboard');
    }

    public function tickets(): void {
        $tickets = $this->db->fetchAll(
            "SELECT t.*, COUNT(tr.id) AS reply_count
             FROM tickets t
             LEFT JOIN ticket_replies tr ON tr.ticket_id = t.id
             WHERE t.user_id = ?
             GROUP BY t.id ORDER BY t.updated_at DESC",
            [Auth::id()]
        );
        $this->view('dashboard.tickets', compact('tickets'), 'dashboard');
    }

    public function createTicket(): void {
        if (!Auth::verifyCsrf($this->input('_csrf', ''))) $this->back();

        $ticketNo = 'TKT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $this->db->insert('tickets', [
            'ticket_no' => $ticketNo,
            'user_id'   => Auth::id(),
            'order_id'  => $this->input('order_id') ?: null,
            'subject'   => trim($this->input('subject', '')),
            'body'      => trim($this->input('body', '')),
            'priority'  => $this->input('priority', 'normal'),
            'status'    => 'open',
        ]);
        setFlash('success', "Ticket $ticketNo créé avec succès.");
        $this->redirect('/dashboard/tickets');
    }

    public function ticketDetail(string $id): void {
        $ticket = $this->db->fetch("SELECT * FROM tickets WHERE id = ? AND user_id = ?", [(int)$id, Auth::id()]);
        if (!$ticket) $this->abort(404);

        $replies = $this->db->fetchAll(
            "SELECT tr.*, u.name, u.avatar, u.role FROM ticket_replies tr
             JOIN users u ON u.id = tr.user_id
             WHERE tr.ticket_id = ? ORDER BY tr.created_at ASC",
            [$ticket['id']]
        );
        $this->view('dashboard.ticket_detail', compact('ticket', 'replies'), 'dashboard');
    }

    public function replyTicket(string $id): void {
        $ticket = $this->db->fetch("SELECT * FROM tickets WHERE id = ? AND user_id = ?", [(int)$id, Auth::id()]);
        if (!$ticket) $this->abort(404);

        $this->db->insert('ticket_replies', [
            'ticket_id' => $ticket['id'],
            'user_id'   => Auth::id(),
            'body'      => trim($this->input('body', '')),
            'is_staff'  => 0,
        ]);
        $this->db->update('tickets', ['status' => 'open'], 'id = ?', ['id' => $ticket['id']]);
        setFlash('success', 'Réponse envoyée.');
        $this->redirect('/dashboard/tickets/' . $ticket['id']);
    }

    public function notifications(): void {
        $uid = Auth::id();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $this->db->paginate(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC",
            [$uid],
            $page,
            20
        );
        $unreadCount = (int)$this->db->count(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
            [$uid]
        );
        $this->view('dashboard.notifications', array_merge($result, compact('unreadCount')), 'dashboard');
    }

    public function markNotificationRead(string $id): void {
        if (!Auth::verifyCsrf($this->input('_csrf', ''))) $this->back();
        $uid = Auth::id();
        $notif = $this->db->fetch("SELECT id, is_read, url FROM notifications WHERE id = ? AND user_id = ?", [(int)$id, $uid]);
        if (!$notif) {
            setFlash('error', 'Notification introuvable.');
            $this->redirect('/dashboard/notifications');
        }
        if ((int)$notif['is_read'] === 0) {
            $this->db->update('notifications', ['is_read' => 1], 'id = ?', ['id' => (int)$id]);
        }
        $next = trim((string)($this->input('next', '') ?: '' ));
        if ($next !== '' && str_starts_with($next, '/')) {
            $this->redirect($next);
        }
        $this->redirect($notif['url'] ?: '/dashboard/notifications');
    }

    public function markAllNotificationsRead(): void {
        if (!Auth::verifyCsrf($this->input('_csrf', ''))) $this->back();
        $uid = Auth::id();
        $this->db->query(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0",
            [$uid]
        );
        setFlash('success', 'Toutes les notifications sont marquées comme lues.');
        $this->redirect('/dashboard/notifications');
    }

    public function profile(): void {
        $user = Auth::user();
        $this->view('dashboard.profile', compact('user'), 'dashboard');
    }

    public function updateProfile(): void {
        if (!Auth::verifyCsrf($this->input('_csrf', ''))) $this->back();
        $data = ['name' => trim($this->input('name', '')), 'phone' => trim($this->input('phone', ''))];
        $password = $this->input('new_password', '');
        if ($password) {
            if (strlen($password) < 8) {
                setFlash('error', 'Mot de passe trop court.');
                $this->back();
            }
            $data['password_hash'] = \App\Core\Auth::hashPassword($password);
        }
        $this->db->update('users', $data, 'id = ?', ['id' => Auth::id()]);
        setFlash('success', 'Profil mis à jour.');
        $this->redirect('/dashboard/profile');
    }
}
