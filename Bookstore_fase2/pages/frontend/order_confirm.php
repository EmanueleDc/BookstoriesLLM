<?php

require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/CartDAO.php';

if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    header('Location: index.php');
    exit;
}

$cartDao   = new CartDAO();
$cartId    = $cartDao->getOrCreateCart($_SESSION['user_id']);
$cartCount = $cartDao->countItems($cartId);

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/order_confirm');

$contentTpl->setContent('order_id', $orderId);

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('flash', '');
$tpl->setContent('if_logged', $_SESSION['username']);
$tpl->setContent('if_admin', has_service('admin_panel') ? '1' : '');
$tpl->setContent('cart_count', $cartCount);

echo $tpl->get();
?>
