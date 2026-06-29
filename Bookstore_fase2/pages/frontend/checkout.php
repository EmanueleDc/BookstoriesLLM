<?php

require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/CartDAO.php';
require_once '../../dao/OrderDAO.php';
require_once '../../dao/AddressDAO.php';

// Solo utenti loggati possono fare checkout
if (!is_logged_in()) {
    header('Location: login.php?redirect=' . urlencode('checkout.php'));
    exit;
}

$cartDao    = new CartDAO();
$orderDao   = new OrderDAO();
$addressDao = new AddressDAO();

$userId = $_SESSION['user_id'];
$cartId = $cartDao->getOrCreateCart($userId);
$items  = $cartDao->getCartItems($cartId);

// Carrello vuoto: impossibile fare checkout
if (empty($items)) {
    header('Location: cart.php');
    exit;
}

// Conferma ordine (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['terms'])) {
    // Validazione dati spedizione
    $addressLine1 = trim($_POST['address_line1'] ?? '');
    $city         = trim($_POST['city'] ?? '');
    $zipCode      = trim($_POST['zip_code'] ?? '');
    $country      = trim($_POST['country'] ?? 'Italia');

    // Dati pagamento (fittizi)
    $cardName     = trim($_POST['card_name'] ?? '');
    $cardNumber   = trim($_POST['card_number'] ?? '');
    $cardExpiry   = trim($_POST['card_expiry'] ?? '');
    $cardCvv      = trim($_POST['card_cvv'] ?? '');

    if (empty($addressLine1) || empty($city) || empty($zipCode)) {
        $errorMsg = "Compila tutti i campi dell'indirizzo di spedizione.";
    } elseif (empty($cardName) || empty($cardNumber) || empty($cardExpiry) || empty($cardCvv)) {
        $errorMsg = "Inserisci i dati della carta per procedere al pagamento.";
    } elseif (strlen(str_replace(' ', '', $cardNumber)) < 16) {
        $errorMsg = "Il numero della carta non è valido.";
    } else {
        // Simulazione validazione pagamento fittizio
        $paymentSuccess = true;

        if ($paymentSuccess) {
            // Calcolo totale lato server
            $grandTotal = 0.0;
            $orderItems = [];
            foreach ($items as $item) {
                $lineTotal   = (float)$item['price'] * (int)$item['quantity'];
                $grandTotal += $lineTotal;
                $orderItems[] = [
                    'book_id'    => $item['book_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['price'],
                ];
            }

            try {
                // 1. Salviamo l'indirizzo nel DB
                $addressId = $addressDao->create([
                    'user_id' => $userId,
                    'address_line1' => $addressLine1,
                    'city' => $city,
                    'zip_code' => $zipCode,
                    'country' => $country
                ]);

                // 2. Salviamo l'ordine nel DB con la transazione del DAO
                $orderId = $orderDao->createOrder($userId, $addressId, $grandTotal, $orderItems);

                // Svuotiamo il carrello
                $cartDao->clearCart($cartId);

                header('Location: order_confirm.php?id=' . $orderId);
                exit;

            } catch (\Exception $e) {
                $errorMsg = "Si è verificato un errore durante la creazione dell'ordine. Riprova.";
            }
        }
    }
}

// Costruzione righe riepilogo ordine
$grandTotal    = 0.0;
$orderItemsHtml = '';
foreach ($items as $item) {
    $lineTotal  = (float)$item['price'] * (int)$item['quantity'];
    $grandTotal += $lineTotal;

    $orderItemsHtml .= "<tr>
        <td>" . htmlspecialchars($item['title']) . "</td>
        <td class='text-center'>€" . number_format((float)$item['price'], 2, ',', '.') . "</td>
        <td class='text-center'>" . (int)$item['quantity'] . "</td>
        <td class='text-center'>€" . number_format($lineTotal, 2, ',', '.') . "</td>
    </tr>";
}

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/checkout');

$contentTpl->setContent('order_items_html', $orderItemsHtml);
$contentTpl->setContent('grand_total', number_format($grandTotal, 2, ',', '.'));

$cartCount = $cartDao->countItems($cartId);

$flashMsg = !empty($errorMsg)
    ? "<div class='alert alert-danger'>" . htmlspecialchars($errorMsg) . "</div>"
    : '';

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('flash', $flashMsg);
$tpl->setContent('if_logged', $_SESSION['username']);
$tpl->setContent('if_admin', has_service('admin_panel') ? '1' : '');
$tpl->setContent('cart_count', $cartCount);

echo $tpl->get();
?>
