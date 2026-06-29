<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/AuthorDAO.php';

require_service('admin_panel');

$authorDao = new AuthorDAO();
$errorMsg = '';
$authorData = ['id' => '', 'first_name' => '', 'last_name' => '', 'biography' => ''];

$isEdit = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $loadedAuthor = $authorDao->read((int)$_GET['id']);
    if ($loadedAuthor) {
        $authorData = $loadedAuthor;
    } else {
        header('Location: authors.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $biography = trim($_POST['biography'] ?? '');
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (empty($firstName) || empty($lastName)) {
        $errorMsg = "Nome e Cognome sono obbligatori.";
    } else {
        $dataToSave = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'biography' => $biography
        ];

        if ($id) {
            $authorDao->update($id, $dataToSave);
        } else {
            $authorDao->create($dataToSave);
        }
        header('Location: authors.php');
        exit;
    }

    if (!empty($errorMsg)) {
        $authorData = array_merge($authorData, $_POST);
    }
}

$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/author_edit');

$content->setContent('form_title', $isEdit ? 'Modifica Autore' : 'Aggiungi Nuovo Autore');
$content->setContent('query_string', $isEdit ? "?id={$authorData['id']}" : '');
$content->setContent('author_id', htmlspecialchars($authorData['id'] ?? ''));
$content->setContent('author_first_name', htmlspecialchars($authorData['first_name'] ?? ''));
$content->setContent('author_last_name', htmlspecialchars($authorData['last_name'] ?? ''));
$content->setContent('author_biography', htmlspecialchars($authorData['biography'] ?? ''));

$errorHtml = '';
if (!empty($errorMsg)) {
    $errorHtml = "<div class='alert alert-danger m-3'>$errorMsg</div>";
}
$content->setContent('error_message_html', $errorHtml);

$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
