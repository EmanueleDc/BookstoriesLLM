<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/CategoryDAO.php';

require_service('admin_panel');

$categoryDao = new CategoryDAO();
$errorMsg = '';
$categoryData = ['id' => '', 'name' => '', 'description' => ''];

$isEdit = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $loadedCategory = $categoryDao->read((int)$_GET['id']);
    if ($loadedCategory) {
        $categoryData = $loadedCategory;
    } else {
        header('Location: categories.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (empty($name)) {
        $errorMsg = "Il nome della categoria è obbligatorio.";
    } else {
        $dataToSave = [
            'name' => $name,
            'description' => $description
        ];

        if ($id) {
            $categoryDao->update($id, $dataToSave);
        } else {
            $categoryDao->create($dataToSave);
        }
        header('Location: categories.php');
        exit;
    }

    if (!empty($errorMsg)) {
        $categoryData = array_merge($categoryData, $_POST);
    }
}

$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/category_edit');

$content->setContent('form_title', $isEdit ? 'Modifica Categoria' : 'Aggiungi Nuova Categoria');
$content->setContent('query_string', $isEdit ? "?id={$categoryData['id']}" : '');
$content->setContent('category_id', htmlspecialchars($categoryData['id'] ?? ''));
$content->setContent('category_name', htmlspecialchars($categoryData['name'] ?? ''));
$content->setContent('category_description', htmlspecialchars($categoryData['description'] ?? ''));

$errorHtml = '';
if (!empty($errorMsg)) {
    $errorHtml = "<div class='alert alert-danger m-3'>$errorMsg</div>";
}
$content->setContent('error_message_html', $errorHtml);

$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
