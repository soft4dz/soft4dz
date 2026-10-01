<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class BlogController extends Controller {
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function index(): void {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $this->db->paginate(
            "SELECT bp.id, bp.title, bp.slug, bp.excerpt, bp.image, bp.views_count, bp.published_at, u.name AS author
             FROM blog_posts bp JOIN users u ON u.id=bp.author_id
             WHERE bp.status='published' ORDER BY bp.published_at DESC",
            [], $page, 9
        );
        $this->view('blog.index', $result);
    }

    public function show(string $slug): void {
        $post = $this->db->fetch(
            "SELECT bp.*, u.name AS author, u.avatar AS author_avatar
             FROM blog_posts bp JOIN users u ON u.id=bp.author_id
             WHERE bp.slug=? AND bp.status='published'", [$slug]
        );
        if (!$post) $this->abort(404);
        $this->db->query("UPDATE blog_posts SET views_count=views_count+1 WHERE id=?", [$post['id']]);
        $related = $this->db->fetchAll(
            "SELECT id,title,slug,image,published_at FROM blog_posts WHERE id!=? AND status='published' ORDER BY RAND() LIMIT 3",
            [$post['id']]
        );
        $this->view('blog.show', compact('post', 'related'));
    }
}
