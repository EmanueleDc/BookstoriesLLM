<?php

require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/BookDAO.php';
require_once '../../dao/CategoryDAO.php';

$bookDao = new BookDAO();
$categoryDao = new CategoryDAO();

$featuredBooks = $bookDao->getFeatured(8);
$latestBooks = $bookDao->getLatest(4);
$categories = $categoryDao->getAll();

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/home');

// 1. Categories (Match order in home.html)
foreach ($categories as $cat) {
    $contentTpl->setContent('cat_id', $cat['id']);
    $contentTpl->setContent('cat_name', htmlspecialchars($cat['name']));
}

// 2. Featured Books
foreach ($featuredBooks as $book) {
    $imagePath = !empty($book['cover_image'])
        ? '../../uploads/covers/' . $book['cover_image']
        : '../../skins/frontend/Fruitables/img/1778664566_book cover 1.png';

    $contentTpl->setContent('book_id', $book['id']);
    $contentTpl->setContent('book_title', htmlspecialchars($book['title']));
    $contentTpl->setContent('book_price', number_format((float)$book['price'], 2, ',', '.'));
    $contentTpl->setContent('book_image', $imagePath);
    $contentTpl->setContent('book_author', htmlspecialchars($book['authors'] ?? ''));
}

// 3. Latest Books
foreach ($latestBooks as $book) {
    $imagePath = !empty($book['cover_image'])
        ? '../../uploads/covers/' . $book['cover_image']
        : '../../skins/frontend/Fruitables/img/1778664566_book cover 1.png';

    $contentTpl->setContent('latest_id', $book['id']);
    $contentTpl->setContent('latest_title', htmlspecialchars($book['title']));
    $contentTpl->setContent('latest_price', number_format((float)$book['price'], 2, ',', '.'));
    $contentTpl->setContent('latest_image', $imagePath);
    $contentTpl->setContent('latest_author', htmlspecialchars($book['authors'] ?? ''));
}

if (is_logged_in()) {
    $contentTpl->setContent('if_logged', $_SESSION['username']);
    $contentTpl->setContent('logged_user_name', htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username']));
    $contentTpl->setContent('if_not_logged', '');
} else {
    $contentTpl->setContent('if_logged', '');
    $contentTpl->setContent('if_not_logged', '1');
}

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('if_logged', is_logged_in() ? $_SESSION['username'] : '');
$tpl->setContent('if_admin', has_service('admin_panel') ? '1' : '');
$tpl->setContent('cart_count', '0');
$tpl->setContent('flash', '');

echo $tpl->get();
?>
