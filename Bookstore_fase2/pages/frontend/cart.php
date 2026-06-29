<?php

require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/CartDAO.php';

// Solo utenti loggati possono accedere al carrello
if (!is_logged_in()) {
    header('Location: login.php?redirect=' . urlencode('cart.php'));
    exit;
}

$cartDao = new CartDAO();
$cartId = $cartDao->getOrCreateCart($_SESSION['user_id']);
$flashMsg = '';

// Aggiornamento quantità
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $itemId  = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $newQty  = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    if ($itemId && $newQty !== false) {
        $cartDao->updateQuantity($itemId, $newQty);
    }
    header('Location: cart.php');
    exit;
}

// Rimozione articolo
if (isset($_GET['action']) && $_GET['action'] === 'remove') {
    $itemId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($itemId) {
        $cartDao->removeItem($itemId);
    }
    header('Location: cart.php');
    exit;
}

$items = $cartDao->getCartItems($cartId);
$cartCount = $cartDao->countItems($cartId);

// Costruzione righe tabella e calcolo totale
$grandTotal = 0.0;
$itemsHtml = '';

foreach ($items as $item) {
    $itemTotal = (float)$item['price'] * (int)$item['quantity'];
    $grandTotal += $itemTotal;

    $title = htmlspecialchars($item['title']);
    $price = number_format((float)$item['price'], 2, ',', '.');
    $itemTotalFmt = number_format($itemTotal, 2, ',', '.');
    $stock = (int)$item['stock'];
    $id = (int)$item['id'];

    // Selezione immagine
    $img = !empty($item['cover_image'])
        ? '../../uploads/covers/' . $item['cover_image']
        : '../../skins/frontend/Fruitables/img/1778664566_book cover 1.png';

    $itemsHtml .= "<tr>
        <td>
            <div class='d-flex align-items-center gap-3'>
                <img src='$img' alt='$title' style='width:50px; height:65px; object-fit:cover; border-radius:4px;'>
                <span class='fw-semibold'>$title</span>
            </div>
        </td>
        <td class='text-center'>€$price</td>
        <td class='text-center' style='min-width:140px;'>
            <form method='POST' action='cart.php' class='d-flex align-items-center justify-content-center gap-2'>
                <input type='hidden' name='item_id' value='$id'>
                <input type='hidden' name='update_qty' value='1'>
                <input type='number' name='quantity' value='{$item['quantity']}' min='1' max='$stock'
                    class='form-control form-control-sm text-center' style='width:60px;'
                    onchange='this.form.submit()'>
            </form>
        </td>
        <td class='text-center fw-bold'>€$itemTotalFmt</td>
        <td class='text-center'>
            <a href='cart.php?action=remove&id=$id' class='btn btn-sm rounded-circle bg-light border'
                onclick=\"return confirm('Rimuovere questo libro dal carrello?')\">
                <i class='fa fa-times text-danger'></i>
            </a>
        </td>
    </tr>";
}

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/cart');

$contentTpl->setContent('items_html', $itemsHtml);
$contentTpl->setContent('grand_total', number_format($grandTotal, 2, ',', '.'));
$contentTpl->setContent('checkout_disabled', empty($items) ? 'disabled' : '');

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('flash', $flashMsg);
$tpl->setContent('if_logged', $_SESSION['username']);
$tpl->setContent('if_admin', has_service('admin_panel') ? '1' : '');
$tpl->setContent('cart_count', $cartCount);

echo $tpl->get();
?>
