<?php

require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/BookDAO.php';
require_once '../../dao/CartDAO.php';

// Validazione ID libro nell'URL
$bookId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$bookId) {
    header('Location: books.php');
    exit;
}

$bookDao = new BookDAO();
$book = $bookDao->getFullDetail($bookId);

if (!$book) {
    header('Location: books.php');
    exit;
}

$cartDao = new CartDAO();
$cartCount = 0;
$flashMsg = '';

// Gestione aggiunta al carrello
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=' . urlencode('book.php?id=' . $bookId));
        exit;
    }

    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1;
    $quantity = max(1, min($quantity, (int)$book['stock']));

    $cartId = $cartDao->getOrCreateCart($_SESSION['user_id']);
    $cartDao->addItem($cartId, $bookId, $quantity);

    $flashMsg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <i class='fas fa-check-circle me-2'></i>Libro aggiunto al carrello!
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                 </div>";
}

// Conta articoli carrello per badge nella navbar
if (is_logged_in()) {
    $cartId = $cartDao->getOrCreateCart($_SESSION['user_id']);
    $cartCount = $cartDao->countItems($cartId);
}

// Costruzione HTML del form (o messaggio esaurimento)
$cartFormHtml = '';
if ((int)$book['stock'] > 0) {
    $cartFormHtml = "<form action='book.php?id={$bookId}' method='POST'>
        <input type='hidden' name='add_to_cart' value='1'>
        <div class='input-group quantity mb-4' style='width: 160px;'>
            <div class='input-group-btn'>
                <button type='button' class='btn btn-sm btn-minus rounded-circle bg-light border' onclick='changeQty(-1)'>
                    <i class='fa fa-minus'></i>
                </button>
            </div>
            <input type='text' name='quantity' id='book-qty'
                class='form-control form-control-sm text-center border-0'
                value='1' readonly
                data-max='" . (int)$book['stock'] . "' inputmode='numeric'>
            <div class='input-group-btn'>
                <button type='button' class='btn btn-sm btn-plus rounded-circle bg-light border' onclick='changeQty(1)'>
                    <i class='fa fa-plus'></i>
                </button>
            </div>
        </div>
        <button type='submit' class='btn border border-secondary rounded-pill px-4 py-2 mb-4 text-primary'>
            <i class='fa fa-shopping-bag me-2 text-primary'></i>Aggiungi al carrello
        </button>
    </form>";
} else {
    $cartFormHtml = "<p class='badge bg-danger text-white fs-6 mb-4'>Esaurito</p>";
}

$stockBadgeHtml = (int)$book['stock'] > 0
    ? "<span class='badge bg-success text-white mb-3'>Disponibile (" . (int)$book['stock'] . " in stock)</span>"
    : "<span class='badge bg-danger text-white mb-3'>Esaurito</span>";

$imagePath = !empty($book['cover_image'])
    ? '../../uploads/covers/' . $book['cover_image']
    : '../../skins/frontend/Fruitables/img/1778664566_book cover 1.png';

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/book');

$contentTpl->setContent('book_id', $book['id']);
$contentTpl->setContent('book_title', htmlspecialchars($book['title']));
$contentTpl->setContent('book_image', $imagePath);
$contentTpl->setContent('book_author', htmlspecialchars($book['authors'] ?? 'N/D'));
$contentTpl->setContent('book_category', htmlspecialchars($book['categories'] ?? 'N/D'));
$contentTpl->setContent('book_isbn', htmlspecialchars($book['isbn']));
$contentTpl->setContent('book_year', htmlspecialchars($book['publication_year'] ?? 'N/D'));
$contentTpl->setContent('book_publisher', htmlspecialchars($book['publisher_name'] ?? 'N/D'));
$contentTpl->setContent('book_price', number_format((float)$book['price'], 2, ',', '.'));
$contentTpl->setContent('book_description', nl2br(htmlspecialchars($book['description'] ?? '')));
$contentTpl->setContent('book_stock', (int)$book['stock']);
$contentTpl->setContent('stock_badge_html', $stockBadgeHtml);
$contentTpl->setContent('cart_form_html', $cartFormHtml);

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('if_logged', is_logged_in() ? $_SESSION['username'] : '');
$tpl->setContent('if_admin', has_service('admin_panel') ? '1' : '');
$tpl->setContent('cart_count', $cartCount);
$tpl->setContent('flash', $flashMsg);

echo $tpl->get();
?>
