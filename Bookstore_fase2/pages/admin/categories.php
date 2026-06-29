<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/CategoryDAO.php';

require_service('admin_panel');

$categoryDao = new CategoryDAO();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $categoryDao->delete((int)$_GET['delete']);
    header('Location: categories.php');
    exit;
}

$categories = $categoryDao->getAll();

$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/categories');

$categoriesListHtml = "";
if (empty($categories)) {
    $categoriesListHtml = "<tr><td colspan='3' class='text-center'>Nessuna categoria trovata.</td></tr>";
} else {
    foreach ($categories as $category) {
        $id = htmlspecialchars($category['id']);
        $name = htmlspecialchars($category['name']);

        $categoriesListHtml .= "<tr>
            <td><span class='text-muted'>$id</span></td>
            <td><strong>$name</strong></td>
            <td>
                <a href='category_edit.php?id=$id' class='btn btn-sm btn-primary'>Modifica</a>
                <a href='categories.php?delete=$id' class='btn btn-sm btn-danger' onclick='return confirm(\"Sei sicuro di voler eliminare questa categoria?\");'>Elimina</a>
            </td>
        </tr>";
    }
}

$content->setContent('categories_list_html', $categoriesListHtml);
$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
