<?php

require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/BookDAO.php';
require_once '../../dao/UserDAO.php';
require_once '../../dao/CategoryDAO.php';
require_once '../../dao/OrderDAO.php';

// Protezione area admin
require_service('admin_panel');

$bookDao = new BookDAO();
$userDao = new UserDAO();
$categoryDao = new CategoryDAO();
$orderDao = new OrderDAO();

$totalBooks = $bookDao->getTotalBooks();
$totalUsers = $userDao->getTotalUsers();
$totalOrders = $orderDao->getTotalOrders();
$totalCategories = $categoryDao->getTotalCategories();
$lowStockCount = $bookDao->getLowStockCount();
$recentOrders = $orderDao->getRecentOrders(5);

// Ricavi
$revenueWeekly = $orderDao->getRevenueByPeriod(7);
$revenueMonthly = $orderDao->getRevenueByPeriod(30);
$revenueAnnual = $orderDao->getRevenueByPeriod(365);

$recentOrdersHtml = '';
foreach ($recentOrders as $order) {
    $status = $order['status'];
    $badgeClass = 'bg-secondary'; // Default

    switch ($status) {
        case 'pending': $badgeClass = 'bg-warning'; break;
        case 'processing': $badgeClass = 'bg-azure'; break;
        case 'shipped': $badgeClass = 'bg-purple'; break;
        case 'delivered': $badgeClass = 'bg-success'; break;
        case 'cancelled': $badgeClass = 'bg-danger'; break;
    }

    $statusHtml = "<span class='badge $badgeClass text-white'>" . htmlspecialchars(ucfirst($status)) . "</span>";

    $recentOrdersHtml .= '<tr>' .
        '<td>#' . htmlspecialchars($order['id']) . '</td>' .
        '<td>' . htmlspecialchars($order['username']) . '</td>' .
        '<td>' . number_format((float)$order['total_amount'], 2, ',', '.') . ' €</td>' .
        '<td>' . $statusHtml . '</td>' .
        '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) . '</td>' .
    '</tr>';
}

$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/dashboard');

$content->setContent('total_books', $totalBooks);
$content->setContent('total_users', $totalUsers);
$content->setContent('total_orders', $totalOrders);
$content->setContent('total_categories', $totalCategories);
$content->setContent('low_stock_count', $lowStockCount);
$content->setContent('current_date', date('d/m/Y'));
$content->setContent('revenue_weekly', number_format($revenueWeekly, 2, ',', '.'));
$content->setContent('revenue_monthly', number_format($revenueMonthly, 2, ',', '.'));
$content->setContent('revenue_annual', number_format($revenueAnnual, 2, ',', '.'));
$content->setContent('recent_orders_rows', $recentOrdersHtml);

$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
