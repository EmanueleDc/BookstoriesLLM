<?php

require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/UserDAO.php';
require_once '../../dao/OrderDAO.php';
require_once '../../dao/CartDAO.php';

if (!is_logged_in()) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$userDao = new UserDAO();
$orderDao = new OrderDAO();

$user = $userDao->getById((int) $_SESSION['user_id']);
$orders = $orderDao->getUserOrders((int) $_SESSION['user_id']);

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/profile');

$contentTpl->setContent('user_name', htmlspecialchars($user['first_name'] . ' ' . $user['last_name']));
$contentTpl->setContent('user_username', htmlspecialchars($user['username']));
$contentTpl->setContent('user_email', htmlspecialchars($user['email']));
$contentTpl->setContent('user_created_at', htmlspecialchars($user['created_at'] ?? ''));

$ordersHtml = '';
if (empty($orders)) {
    $ordersHtml = "<tr><td colspan='4' class='text-center'>Nessun ordine ancora effettuato.</td></tr>";
} else {
    foreach ($orders as $order) {
        $ordersHtml .= "<tr>";
        $ordersHtml .= "<td>#" . (int)$order['id'] . "</td>";
        $ordersHtml .= "<td>€" . number_format((float)$order['total_amount'], 2, ',', '.') . "</td>";
        $ordersHtml .= "<td>" . htmlspecialchars($order['status']) . "</td>";
        $ordersHtml .= "<td>" . htmlspecialchars($order['created_at']) . "</td>";
        $ordersHtml .= "</tr>";
    }
}

$contentTpl->setContent('orders_html', $ordersHtml);

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('if_logged', is_logged_in() ? $_SESSION['username'] : '');
$tpl->setContent('if_admin', has_service('admin_panel') ? '1' : '');
$tpl->setContent('cart_count', (new CartDAO())->countItems((new CartDAO())->getOrCreateCart($_SESSION['user_id'])));
$tpl->setContent('flash', '');

echo $tpl->get();
