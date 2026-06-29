<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/BookDAO.php';
require_once '../../dao/AuthorDAO.php';
require_once '../../dao/CategoryDAO.php';
require_once '../../dao/PublisherDAO.php';

// Protezione area admin
require_service('admin_panel');

$bookDao = new BookDAO();
$authorDao = new AuthorDAO();
$categoryDao = new CategoryDAO();
$publisherDao = new PublisherDAO();

$errorMsg = '';
$bookData = [
    'id' => '', 'title' => '', 'isbn' => '', 'price' => '', 
    'stock' => '0', 'publication_year' => '', 'description' => '', 'cover_image' => '',
    'publisher_id' => '', 'author_ids' => [], 'category_ids' => []
];

$isEdit = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $loadedBook = $bookDao->read((int)$_GET['id']);
    if ($loadedBook) {
        $bookData = $loadedBook;
        $bookData['author_ids'] = $bookDao->getAuthorIds((int)$_GET['id']);
        $bookData['category_ids'] = $bookDao->getCategoryIds((int)$_GET['id']);
    } else {
        header('Location: books.php');
        exit;
    }
}

// Gestione form sottomesso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Raccolta dati
    $title = trim($_POST['title'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $pubYear = filter_input(INPUT_POST, 'publication_year', FILTER_VALIDATE_INT);
    $description = trim($_POST['description'] ?? '');
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $currentImage = $_POST['current_image'] ?? '';
    $publisherId = $_POST['publisher_id'] ?? null;
    // Riceviamo l'id singolo dalla tendina e lo trasformiamo in array per il DAO
    $authorIds = !empty($_POST['author_id']) ? [$_POST['author_id']] : [];
    $categoryIds = $_POST['category_ids'] ?? [];

    // Validazione Lato Server
    if (empty($title)) {
        $errorMsg = "Il titolo è obbligatorio.";
    } elseif (empty($isbn) || !preg_match('/^[0-9\-]+$/', $isbn)) {
        $errorMsg = "Inserisci un codice ISBN valido.";
    } elseif ($price === false || $price <= 0) {
        $errorMsg = "Inserisci un prezzo valido (maggiore di zero).";
    } elseif ($stock === false || $stock < 0) {
        $errorMsg = "Lo stock non può essere negativo.";
    } else {
        $coverImage = $currentImage;

        // Gestione Upload Immagine Sicuro
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['cover_image']['tmp_name'];
            $fileName = basename($_FILES['cover_image']['name']);
            $fileSize = $_FILES['cover_image']['size'];
            $fileType = mime_content_type($tmpName);
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($fileType, $allowedTypes)) {
                $errorMsg = "Formato immagine non valido. Solo JPG, PNG o WEBP.";
            } elseif ($fileSize > $maxSize) {
                $errorMsg = "L'immagine è troppo grande. Massimo 2MB.";
            } else {
                // Generazione nome univoco
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = uniqid('cover_') . '.' . $ext;
                $uploadDir = '../../uploads/covers/';
                
                if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
                    $coverImage = $newFileName;
                    // Se stavamo modificando, potremmo eliminare la vecchia immagine qui
                    if ($isEdit && !empty($currentImage) && file_exists($uploadDir . $currentImage)) {
                        unlink($uploadDir . $currentImage);
                    }
                } else {
                    $errorMsg = "Errore durante il salvataggio dell'immagine.";
                }
            }
        }

        if (empty($errorMsg)) {
            $dataToSave = [
                'title' => $title,
                'isbn' => $isbn,
                'price' => $price,
                'stock' => $stock,
                'publication_year' => $pubYear,
                'description' => $description,
                'cover_image' => $coverImage,
                'publisher_id' => $publisherId ?: null,
                'author_ids' => $authorIds,
                'category_ids' => $categoryIds
            ];

            if ($id) {
                $bookDao->update($id, $dataToSave);
            } else {
                $bookDao->create($dataToSave);
            }
            
            header('Location: books.php');
            exit;
        }
    }
    
    // Ricarichiamo i dati postati in caso di errore
    if (!empty($errorMsg)) {
        $bookData = array_merge($bookData, $_POST);
        $bookData['author_ids'] = $authorIds;
        $bookData['category_ids'] = $categoryIds;
    }
}

// Rendering
$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/book_edit');

$content->setContent('form_title', $isEdit ? 'Modifica Libro' : 'Aggiungi Nuovo Libro');
$content->setContent('query_string', $isEdit ? "?id={$bookData['id']}" : '');
$content->setContent('book_id', htmlspecialchars($bookData['id'] ?? ''));
$content->setContent('book_title', htmlspecialchars($bookData['title'] ?? ''));
$content->setContent('book_isbn', htmlspecialchars($bookData['isbn'] ?? ''));
$content->setContent('book_price', htmlspecialchars($bookData['price'] ?? ''));
$content->setContent('book_stock', htmlspecialchars($bookData['stock'] ?? ''));
$content->setContent('book_year', htmlspecialchars($bookData['publication_year'] ?? ''));
$content->setContent('book_description', htmlspecialchars($bookData['description'] ?? ''));
$content->setContent('book_image', htmlspecialchars($bookData['cover_image'] ?? ''));

// Editori dropdown
$publishers = $publisherDao->getAll();
$publisherOptions = '<option value="">Seleziona Editore</option>';
foreach ($publishers as $p) {
    $selected = ($p['id'] == $bookData['publisher_id']) ? 'selected' : '';
    $publisherOptions .= "<option value='{$p['id']}' $selected>".htmlspecialchars($p['name'])."</option>";
}
$content->setContent('publisher_options', $publisherOptions);

// Autori dropdown (singola selezione come richiesto)
$authors = $authorDao->getAll();
$authorOptions = '<option value="">Seleziona Autore</option>';
foreach ($authors as $a) {
    // Verifichiamo se l'id dell'autore è tra quelli associati al libro
    $selected = in_array($a['id'], $bookData['author_ids']) ? 'selected' : '';
    $fullName = htmlspecialchars($a['first_name'] . ' ' . $a['last_name']);
    $authorOptions .= "<option value='{$a['id']}' $selected>$fullName</option>";
}
$content->setContent('author_options', $authorOptions);

// Categorie (multiple select)
$categories = $categoryDao->getAll();
$categoriesHtml = "";
foreach ($categories as $c) {
    $checked = in_array($c['id'], $bookData['category_ids']) ? 'checked' : '';
    $categoriesHtml .= "
    <label class='form-check form-check-inline'>
        <input class='form-check-input' type='checkbox' name='category_ids[]' value='{$c['id']}' $checked>
        <span class='form-check-label'>".htmlspecialchars($c['name'])."</span>
    </label>";
}
$content->setContent('categories_checkboxes', $categoriesHtml);

$errorHtml = '';
if (!empty($errorMsg)) {
    $errorHtml = "<div class='alert alert-danger m-3'>$errorMsg</div>";
}
$content->setContent('error_message_html', $errorHtml);

$currentImgHtml = '';
if (!empty($bookData['cover_image'])) {
    $currentImgHtml = "<div class='mb-2'><img src='../../uploads/covers/".htmlspecialchars($bookData['cover_image'])."' style='max-height: 100px; border-radius: 4px;'></div>";
}
$content->setContent('current_image_html', $currentImgHtml);

$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
