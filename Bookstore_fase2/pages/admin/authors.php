<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/AuthorDAO.php';

require_service('admin_panel');

$authorDao = new AuthorDAO();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $authorDao->delete((int)$_GET['delete']);
    header('Location: authors.php');
    exit;
}

$authors = $authorDao->getAll();

$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/authors');

$authorsListHtml = "";
if (empty($authors)) {
    $authorsListHtml = "<tr><td colspan='4' class='text-center'>Nessun autore trovato.</td></tr>";
} else {
    foreach ($authors as $author) {
        $id = htmlspecialchars($author['id']);
        $name = htmlspecialchars($author['first_name'] . ' ' . $author['last_name']);

        $authorsListHtml .= "<tr>
            <td><span class='text-muted'>$id</span></td>
            <td><strong>$name</strong></td>
            <td>
                <a href='author_edit.php?id=$id' class='btn btn-sm btn-primary'>Modifica</a>
                <a href='authors.php?delete=$id' class='btn btn-sm btn-danger' onclick='return confirm(\"Sei sicuro di voler eliminare questo autore?\");'>Elimina</a>
            </td>
        </tr>";
    }
}

$content->setContent('authors_list_html', $authorsListHtml);
$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
