<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/PublisherDAO.php';

require_service('admin_panel');

$publisherDao = new PublisherDAO();
$errorMsg = '';
$publisherData = ['id' => '', 'name' => '', 'website' => ''];

$isEdit = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $loadedPublisher = $publisherDao->read((int)$_GET['id']);
    if ($loadedPublisher) {
        $publisherData = $loadedPublisher;
    } else {
        header('Location: publishers.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (empty($name)) {
        $errorMsg = "Il nome dell'editore è obbligatorio.";
    } else {
        $dataToSave = [
            'name' => $name,
            'website' => $website
        ];

        if ($id) {
            $publisherDao->update($id, $dataToSave);
        } else {
            $publisherDao->create($dataToSave);
        }
        header('Location: publishers.php');
        exit;
    }

    if (!empty($errorMsg)) {
        $publisherData = array_merge($publisherData, $_POST);
    }
}

$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/publisher_edit');

$content->setContent('form_title', $isEdit ? 'Modifica Editore' : 'Aggiungi Nuovo Editore');
$content->setContent('query_string', $isEdit ? "?id={$publisherData['id']}" : '');
$content->setContent('publisher_id', htmlspecialchars($publisherData['id'] ?? ''));
$content->setContent('publisher_name', htmlspecialchars($publisherData['name'] ?? ''));
$content->setContent('publisher_website', htmlspecialchars($publisherData['website'] ?? ''));

$errorHtml = '';
if (!empty($errorMsg)) {
    $errorHtml = "<div class='alert alert-danger m-3'>$errorMsg</div>";
}
$content->setContent('error_message_html', $errorHtml);

$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
