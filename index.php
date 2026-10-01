<?php
declare(strict_types=1);

// Bootstrap
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Helpers/functions.php';

// Autoload
spl_autoload_register(function (string $class): void {
    $file = ROOT_PATH . '/' . str_replace(['App\\', '\\'], ['app/', '/'], $class) . '.php';
    if (file_exists($file)) require_once $file;
});

// Session
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

\App\Core\Locale::init();

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/html; charset=UTF-8');
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Router
$router = new App\Core\Router();

// ── PUBLIC ROUTES ──────────────────────────────────────────
$router->get('/', [App\Controllers\HomeController::class, 'index']);
$router->get('/products', [App\Controllers\ProductController::class, 'index']);
$router->get('/products/{slug}', [App\Controllers\ProductController::class, 'show']);
$router->get('/categories/{slug}', [App\Controllers\ProductController::class, 'byCategory']);
$router->get('/services', [App\Controllers\ServiceController::class, 'index']);
$router->post('/services/request', [App\Controllers\ServiceController::class, 'request']);
$router->get('/blog', [App\Controllers\BlogController::class, 'index']);
$router->get('/blog/{slug}', [App\Controllers\BlogController::class, 'show']);
$router->get('/contact', [App\Controllers\PageController::class, 'contact']);
$router->post('/contact', [App\Controllers\PageController::class, 'sendContact']);
$router->get('/about', [App\Controllers\PageController::class, 'about']);
$router->get('/comment-ca-marche', [App\Controllers\PageController::class, 'howItWorks']);
$router->get('/lang/{locale}', [App\Controllers\PageController::class, 'switchLocale']);

// ── AUTH ROUTES ────────────────────────────────────────────
$router->get('/login', [App\Controllers\AuthController::class, 'loginForm']);
$router->post('/login', [App\Controllers\AuthController::class, 'login']);
$router->get('/admin/login', [App\Controllers\AuthController::class, 'adminLoginForm']);
$router->post('/admin/login', [App\Controllers\AuthController::class, 'adminLogin']);
$router->get('/register', [App\Controllers\AuthController::class, 'registerForm']);
$router->post('/register', [App\Controllers\AuthController::class, 'register']);
$router->get('/logout', [App\Controllers\AuthController::class, 'logout']);
$router->get('/forgot-password', [App\Controllers\AuthController::class, 'forgotForm']);
$router->post('/forgot-password', [App\Controllers\AuthController::class, 'forgot']);
$router->get('/reset-password/{token}', [App\Controllers\AuthController::class, 'resetForm']);
$router->post('/reset-password', [App\Controllers\AuthController::class, 'reset']);
$router->get('/auth/{provider}/redirect', [App\Controllers\SocialAuthController::class, 'redirectToProvider']);
$router->get('/auth/{provider}/callback', [App\Controllers\SocialAuthController::class, 'callback']);

// ── CART ROUTES ────────────────────────────────────────────
$router->get('/cart', [App\Controllers\CartController::class, 'index']);
$router->post('/cart/add', [App\Controllers\CartController::class, 'add']);
$router->post('/cart/update', [App\Controllers\CartController::class, 'update']);
$router->post('/cart/remove', [App\Controllers\CartController::class, 'remove']);
$router->post('/cart/coupon', [App\Controllers\CartController::class, 'applyCoupon']);
$router->get('/checkout', [App\Controllers\CheckoutController::class, 'index']);
$router->post('/checkout', [App\Controllers\CheckoutController::class, 'process']);
$router->get('/checkout/success/{order}', [App\Controllers\CheckoutController::class, 'success']);
$router->get('/checkout/chargily-return', [App\Controllers\CheckoutController::class, 'chargilyReturn']);
$router->get('/tickets/{id}', [App\Controllers\CheckoutController::class, 'downloadTicket']);
$router->post('/checkout/upload-proof', [App\Controllers\CheckoutController::class, 'uploadProof']);
$router->post('/webhook/chargily', [App\Controllers\ChargilyWebhookController::class, 'handle']);

// ── USER DASHBOARD ─────────────────────────────────────────
$router->get('/dashboard', [App\Controllers\DashboardController::class, 'index']);
$router->get('/dashboard/orders', [App\Controllers\DashboardController::class, 'orders']);
$router->get('/dashboard/orders/{id}', [App\Controllers\DashboardController::class, 'orderDetail']);
$router->get('/dashboard/subscriptions', [App\Controllers\DashboardController::class, 'subscriptions']);
$router->get('/dashboard/tickets', [App\Controllers\DashboardController::class, 'tickets']);
$router->post('/dashboard/tickets', [App\Controllers\DashboardController::class, 'createTicket']);
$router->get('/dashboard/tickets/{id}', [App\Controllers\DashboardController::class, 'ticketDetail']);
$router->post('/dashboard/tickets/{id}/reply', [App\Controllers\DashboardController::class, 'replyTicket']);
$router->get('/dashboard/notifications', [App\Controllers\DashboardController::class, 'notifications']);
$router->post('/dashboard/notifications/read-all', [App\Controllers\DashboardController::class, 'markAllNotificationsRead']);
$router->post('/dashboard/notifications/{id}/read', [App\Controllers\DashboardController::class, 'markNotificationRead']);
$router->get('/dashboard/profile', [App\Controllers\DashboardController::class, 'profile']);
$router->post('/dashboard/profile', [App\Controllers\DashboardController::class, 'updateProfile']);

// ── ADMIN PANEL ────────────────────────────────────────────
$router->get('/admin', [App\Controllers\AdminController::class, 'index']);
$router->get('/admin/products', [App\Controllers\AdminController::class, 'products']);
$router->get('/admin/products/create', [App\Controllers\AdminController::class, 'createProduct']);
$router->post('/admin/products/create', [App\Controllers\AdminController::class, 'storeProduct']);
$router->get('/admin/products/{id}/edit', [App\Controllers\AdminController::class, 'editProduct']);
$router->post('/admin/products/{id}/edit', [App\Controllers\AdminController::class, 'updateProduct']);
$router->post('/admin/products/{id}/delete', [App\Controllers\AdminController::class, 'deleteProduct']);
$router->get('/admin/products/{id}/keys', [App\Controllers\AdminController::class, 'productKeys']);
$router->post('/admin/products/{id}/keys', [App\Controllers\AdminController::class, 'storeProductKeys']);
$router->post('/admin/products/{id}/keys/{key_id}/delete', [App\Controllers\AdminController::class, 'deleteProductKey']);
$router->get('/admin/licenses', [App\Controllers\AdminController::class, 'licenses']);
$router->get('/admin/coupons', [App\Controllers\AdminController::class, 'coupons']);
$router->get('/admin/coupons/create', [App\Controllers\AdminController::class, 'createCoupon']);
$router->post('/admin/coupons/create', [App\Controllers\AdminController::class, 'storeCoupon']);
$router->get('/admin/coupons/{id}/edit', [App\Controllers\AdminController::class, 'editCoupon']);
$router->post('/admin/coupons/{id}/edit', [App\Controllers\AdminController::class, 'updateCoupon']);
$router->post('/admin/coupons/{id}/delete', [App\Controllers\AdminController::class, 'deleteCoupon']);
$router->get('/admin/reviews', [App\Controllers\AdminController::class, 'reviews']);
$router->post('/admin/reviews/{id}/approve', [App\Controllers\AdminController::class, 'approveReview']);
$router->post('/admin/reviews/{id}/reject', [App\Controllers\AdminController::class, 'rejectReview']);
$router->get('/admin/orders', [App\Controllers\AdminController::class, 'orders']);
$router->get('/admin/orders/{id}', [App\Controllers\AdminController::class, 'orderDetail']);
$router->post('/admin/orders/{id}/validate', [App\Controllers\AdminController::class, 'validatePayment']);
$router->post('/admin/orders/{id}/status', [App\Controllers\AdminController::class, 'updateOrderStatus']);
$router->post('/admin/orders/{id}/resend-ticket', [App\Controllers\AdminController::class, 'resendTicket']);
$router->get('/admin/users', [App\Controllers\AdminController::class, 'users']);
$router->get('/admin/users/{id}', [App\Controllers\AdminController::class, 'userDetail']);
$router->post('/admin/users/{id}/update', [App\Controllers\AdminController::class, 'updateUser']);
$router->get('/admin/tickets', [App\Controllers\AdminController::class, 'tickets']);
$router->get('/admin/tickets/{id}', [App\Controllers\AdminController::class, 'ticketDetail']);
$router->post('/admin/tickets/{id}/reply', [App\Controllers\AdminController::class, 'replyTicket']);
$router->post('/admin/tickets/{id}/status', [App\Controllers\AdminController::class, 'updateTicketStatus']);
$router->get('/admin/services', [App\Controllers\AdminController::class, 'services']);
$router->get('/admin/services/{id}', [App\Controllers\AdminController::class, 'serviceDetail']);
$router->post('/admin/services/{id}', [App\Controllers\AdminController::class, 'updateService']);
$router->get('/admin/blog', [App\Controllers\AdminController::class, 'blog']);
$router->get('/admin/blog/create', [App\Controllers\AdminController::class, 'createBlog']);
$router->post('/admin/blog/create', [App\Controllers\AdminController::class, 'storeBlog']);
$router->get('/admin/blog/{id}/edit', [App\Controllers\AdminController::class, 'editBlog']);
$router->post('/admin/blog/{id}/edit', [App\Controllers\AdminController::class, 'updateBlog']);
$router->post('/admin/blog/{id}/delete', [App\Controllers\AdminController::class, 'deleteBlog']);
$router->get('/admin/analytics', [App\Controllers\AdminController::class, 'analytics']);
$router->get('/admin/settings', [App\Controllers\AdminController::class, 'settings']);
$router->post('/admin/settings', [App\Controllers\AdminController::class, 'updateSettings']);
$router->get('/admin/categories', [App\Controllers\AdminController::class, 'categories']);
$router->get('/admin/categories/create', [App\Controllers\AdminController::class, 'createCategory']);
$router->post('/admin/categories/create', [App\Controllers\AdminController::class, 'storeCategory']);
$router->get('/admin/categories/{id}/edit', [App\Controllers\AdminController::class, 'editCategory']);
$router->post('/admin/categories/{id}/edit', [App\Controllers\AdminController::class, 'updateCategory']);
$router->post('/admin/categories/{id}/delete', [App\Controllers\AdminController::class, 'deleteCategory']);

// ── API ENDPOINTS ──────────────────────────────────────────
$router->get('/api/products/search', [App\Controllers\ApiController::class, 'searchProducts']);
$router->get('/api/cart/count', [App\Controllers\ApiController::class, 'cartCount']);
$router->post('/api/ai/chat', [App\Controllers\ApiController::class, 'aiChat']);
$router->get('/api/analytics/stats', [App\Controllers\ApiController::class, 'stats']);

// Dispatch
$router->dispatch(
    $_SERVER['REQUEST_URI'],
    $_SERVER['REQUEST_METHOD']
);
