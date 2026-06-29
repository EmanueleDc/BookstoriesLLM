<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/PublisherDAO.php';

require_service('admin_panel');

$publisherDao = new PublisherDAO();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $publisherDao->delete((int)$_GET['delete']);
    header('Location: publishers.php');
    exit;
}

$publishers = $publisherDao->getAll();

$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/publishers');

$publishersListHtml = "";
if (empty($publishers)) {
    $publishersListHtml = "<tr><td colspan='4' class='text-center'>Nessun editore trovato.</td></tr>";
} else {
    foreach ($publishers as $publisher) {
        $id = htmlspecialchars($publisher['id']);
        $name = htmlspecialchars($publisher['name']);
        $website = htmlspecialchars($publisher['website'] ?? '-');

        $publishersListHtml .= "<tr>
            <td><span class='text-muted'>$id</span></td>
            <td><strong>$name</strong></td>
            <td>$website</td>
            <td>
                <a href='publisher_edit.php?id=$id' class='btn btn-sm btn-primary'>Modifica</a>
                <a href='publishers.php?delete=$id' class='btn btn-sm btn-danger' onclick='return confirm(\"Sei sicuro di voler eliminare questo editore?\");'>Elimina</a>
            </td>
        </tr>";
    }
}

$content->setContent('publishers_list_html', $publishersListHtml);
$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
