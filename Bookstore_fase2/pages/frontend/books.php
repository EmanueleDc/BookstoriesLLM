<?php

require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/BookDAO.php';
require_once '../../dao/CategoryDAO.php';

$bookDao = new BookDAO();
$categoryDao = new CategoryDAO();

// Parametri ricerca e filtro categoria
$searchQuery = trim($_GET['search'] ?? '');
$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: null;

$books = $bookDao->search($searchQuery, $categoryId);
$categories = $categoryDao->getAll();

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/books');

// Popoliamo le categorie nella sidebar
foreach ($categories as $cat) {
    $contentTpl->setContent('cat_id', $cat['id']);
    $contentTpl->setContent('cat_name', htmlspecialchars($cat['name']));
}

// Preserviamo la categoria selezionata nel form di ricerca
$keepCategory = $categoryId ? "<input type='hidden' name='category' value='{$categoryId}'>" : '';
$contentTpl->setContent('keep_category', $keepCategory);
$contentTpl->setContent('search_value', htmlspecialchars($searchQuery));

// Filtri attivi: mostriamo il banner solo se c'è una ricerca o categoria attiva
$activeFilterHtml = '';
if (!empty($searchQuery) || $categoryId) {
    $activeFilterHtml = "<div class='mb-4 d-flex align-items-center gap-3'>";
    if (!empty($searchQuery)) {
        $activeFilterHtml .= "<span>Risultati per: <strong class='text-primary'>" . htmlspecialchars($searchQuery) . "</strong></span>";
    }
    $activeFilterHtml .= "<a href='books.php' class='btn btn-sm btn-outline-secondary'>Annulla &times;</a></div>";
}
$contentTpl->setContent('active_filter_html', $activeFilterHtml);

// Popoliamo i libri
if (empty($books)) {
    $contentTpl->setContent('no_results_html', "<div class='col-12 text-center py-5'><p class='text-muted fs-5'>Nessun libro trovato.</p></div>");
} else {
    $contentTpl->setContent('no_results_html', '');
    foreach ($books as $book) {
        $imagePath = !empty($book['cover_image'])
            ? '../../uploads/covers/' . $book['cover_image']
            : '../../skins/frontend/Fruitables/img/1778664566_book cover 1.png';

        $contentTpl->setContent('book_id', $book['id']);
        $contentTpl->setContent('book_title', htmlspecialchars($book['title']));
        $contentTpl->setContent('book_price', number_format((float)$book['price'], 2, ',', '.'));
        $contentTpl->setContent('book_image', $imagePath);
        $contentTpl->setContent('book_author', htmlspecialchars($book['authors'] ?? ''));
    }
}

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('if_logged', is_logged_in() ? $_SESSION['username'] : '');
$tpl->setContent('if_admin', has_service('admin_panel') ? '1' : '');
$tpl->setContent('cart_count', '0');
$tpl->setContent('flash', '');

echo $tpl->get();
?>
