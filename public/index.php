<?php
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\OrderController;
use App\Controllers\UserController;
use App\Controllers\BlogController;
use App\Controllers\CategoryController;
use App\Controllers\SearchController;
use App\Controllers\PageController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\OrdersController;
use App\Controllers\Admin\StockController;
use App\Controllers\Admin\DeliveriesController;

require __DIR__ . '/../app/bootstrap.php';

$router = new Router();

$router->add('GET', '/', [new HomeController(), 'index']);
$router->add('GET', '/login', [new AuthController(), 'showLogin']);
$router->add('POST', '/login', [new AuthController(), 'login']);
$router->add('GET', '/register', [new AuthController(), 'showRegister']);
$router->add('POST', '/register', [new AuthController(), 'register']);
$router->add('GET', '/logout', [new AuthController(), 'logout']);
$router->add('GET', '/urun/([a-z0-9\-]+)', [new ProductController(), 'show']);
$router->add('GET', '/kategori/([a-z0-9\-]+)', [new CategoryController(), 'show']);
$router->add('GET', '/search', [new SearchController(), 'index']);
$router->add('GET', '/cart', [new CartController(), 'index']);
$router->add('POST', '/cart/add', [new CartController(), 'add']);
$router->add('POST', '/cart/update', [new CartController(), 'update']);
$router->add('POST', '/cart/coupon', [new CartController(), 'applyCoupon']);
$router->add('GET', '/cart/remove/([0-9\-]+)', [new CartController(), 'remove']);
$router->add('GET', '/checkout', [new CheckoutController(), 'index']);
$router->add('POST', '/checkout', [new CheckoutController(), 'process']);
$router->add('GET', '/order/(\d+)', [new OrderController(), 'show']);
$router->add('GET', '/panel', [new UserController(), 'profile']);
$router->add('POST', '/panel/profile', [new UserController(), 'updateProfile']);
$router->add('GET', '/panel/orders', [new UserController(), 'orders']);
$router->add('GET', '/panel/balance', [new UserController(), 'balance']);
$router->add('POST', '/panel/balance/topup', [new UserController(), 'topup']);
$router->add('GET', '/panel/tickets', [new UserController(), 'tickets']);
$router->add('GET', '/panel/sessions', [new UserController(), 'sessions']);
$router->add('POST', '/panel/password', [new UserController(), 'updatePassword']);
$router->add('GET', '/blog', [new BlogController(), 'index']);
$router->add('GET', '/blog/([a-z0-9\-]+)', [new BlogController(), 'show']);
$router->add('GET', '/sayfa/([a-z0-9\-]+)', [new PageController(), 'show']);
$router->add('GET', '/admin', [new DashboardController(), 'index']);
$router->add('GET', '/admin/orders', [new OrdersController(), 'index']);
$router->add('GET', '/admin/stock', [new StockController(), 'index']);
$router->add('POST', '/admin/stock/upload', [new StockController(), 'upload']);
$router->add('GET', '/admin/deliveries', [new DeliveriesController(), 'index']);
$router->add('POST', '/admin/deliveries/assign/(\d+)', [new DeliveriesController(), 'assign']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
