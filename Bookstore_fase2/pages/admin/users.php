<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/UserDAO.php';

// Protezione area admin
require_service('admin_panel');

$userDao = new UserDAO();
$notificationHtml = '';

// Gestione eliminazione utente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $userId = filter_input(INPUT_POST, 'delete_user_id', FILTER_VALIDATE_INT);

    if ($userId && $userId !== $_SESSION['user_id']) {
        if ($userDao->deleteUser($userId)) {
            $notificationHtml = "<div class='alert alert-success m-3'>Utente eliminato con successo.</div>";
        } else {
            $notificationHtml = "<div class='alert alert-danger m-3'>Errore durante l'eliminazione dell'utente.</div>";
        }
    } else {
        $notificationHtml = "<div class='alert alert-warning m-3'>Non puoi eliminare l'utente connesso.</div>";
    }
}

// Gestione assegnazione gruppo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['group_id'])) {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);

    if ($userId && $groupId) {
        if ($userDao->setPrimaryGroup($userId, $groupId)) {
            $notificationHtml = "<div class='alert alert-success m-3'>Gruppo assegnato con successo.</div>";
        } else {
            $notificationHtml = "<div class='alert alert-danger m-3'>Errore durante l'assegnazione del gruppo.</div>";
        }
    }
}

$users = $userDao->getAllUsers();
$groups = $userDao->getAllGroups();

$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/users');

$usersListHtml = "";
if (empty($users)) {
    $usersListHtml = "<tr><td colspan='7' class='text-center'>Nessun utente trovato.</td></tr>";
} else {
    foreach ($users as $user) {
        $id = htmlspecialchars($user['id']);
        $username = htmlspecialchars($user['username']);
        $email = htmlspecialchars($user['email']);
        $createdAt = date('d/m/Y H:i', strtotime($user['created_at']));
        
        // Recuperiamo i gruppi di questo utente (per semplicità prendiamo il primo se c'è)
        $userGroups = $userDao->getUserGroups($id);
        $currentGroupHtml = "<span class='text-muted'>Nessuno</span>";
        if (!empty($userGroups)) {
            $groupNames = array_column($userGroups, 'name');
            $currentGroupHtml = "<span class='badge bg-blue'>".htmlspecialchars(implode(', ', $groupNames))."</span>";
        }

        // Creiamo la select per i gruppi
        $selectOptions = "<option value=''>- Seleziona -</option>";
        foreach ($groups as $group) {
            $selectOptions .= "<option value='{$group['id']}'>" . htmlspecialchars($group['name']) . "</option>";
        }

        $deleteButton = '';
        if ($id !== (int)$_SESSION['user_id']) {
            $deleteButton = "<form action='users.php' method='POST' onsubmit='return confirm(\'Sei sicuro di eliminare questo utente?\');' class='m-0'>"
                . "<button type='submit' name='delete_user_id' value='$id' class='btn btn-sm btn-danger'>Elimina</button>"
                . "</form>";
        }

        $usersListHtml .= "<tr>
            <td><span class='text-muted'>$id</span></td>
            <td><strong>$username</strong></td>
            <td>$email</td>
            <td>$createdAt</td>
            <td>$currentGroupHtml</td>
            <td>
                <form action='users.php' method='POST' class='d-flex gap-2'>
                    <input type='hidden' name='user_id' value='$id'>
                    <select name='group_id' class='form-select form-select-sm' required>
                        $selectOptions
                    </select>
                    <button type='submit' class='btn btn-sm btn-primary'>Assegna</button>
                </form>
            </td>
            <td>$deleteButton</td>
        </tr>";
    }
}

$content->setContent('users_list_html', $usersListHtml);
$content->setContent('notification_html', $notificationHtml);

$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
