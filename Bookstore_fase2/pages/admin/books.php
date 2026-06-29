<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/BookDAO.php';

// Protezione area admin
require_service('admin_panel');

$bookDao = new BookDAO();

// Gestione Eliminazione
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $bookDao->delete((int)$_GET['delete']);
    header('Location: books.php');
    exit;
}

$books = $bookDao->getAll();

$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/books');

// Costruiamo le righe della tabella
$booksListHtml = "";
if (empty($books)) {
    $booksListHtml = "<tr><td colspan='6' class='text-center'>Nessun libro trovato.</td></tr>";
} else {
    foreach ($books as $book) {
        $id = htmlspecialchars($book['id']);
        $title = htmlspecialchars($book['title']);
        $isbn = htmlspecialchars($book['isbn']);
        $price = number_format((float)$book['price'], 2, ',', '.');
        $stock = (int)$book['stock'];
        
        $badgeClass = $stock > 0 ? 'bg-success' : 'bg-danger';
        
        $booksListHtml .= "<tr>
            <td><span class='text-muted'>$id</span></td>
            <td><strong>$title</strong></td>
            <td>$isbn</td>
            <td>€ $price</td>
            <td><span class='badge $badgeClass text-white'>$stock</span></td>
            <td>
                <a href='book_edit.php?id=$id' class='btn btn-sm btn-primary'>Modifica</a>
                <a href='books.php?delete=$id' class='btn btn-sm btn-danger' onclick='return confirm(\"Sei sicuro di voler eliminare questo libro?\");'>Elimina</a>
            </td>
        </tr>";
    }
}

$content->setContent('books_list_html', $booksListHtml);
$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
