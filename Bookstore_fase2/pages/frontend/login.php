<?php

require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/UserDAO.php';
require_once '../../dao/CartDAO.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$userDao = new UserDAO();

$redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_URL) ?: '';
$loginFlash = '';
$registerFlash = '';
$loginUsername = '';
$registerUsername = '';
$registerEmail = '';
$registerFirstName = '';
$registerLastName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $redirect = $_POST['redirect'] ?? $redirect;

    if ($action === 'login') {
        $loginUsername = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($loginUsername === '' || $password === '') {
            $loginFlash = "<div class='alert alert-danger'>Compila username e password.</div>";
        } else {
            if (login($loginUsername, $password, $userDao)) {
                $target = $redirect !== '' ? $redirect : 'index.php';
                header('Location: ' . $target);
                exit;
            }

            $loginFlash = "<div class='alert alert-danger'>Username o password errati.</div>";
        }
    }

    if ($action === 'register') {
        $registerUsername = trim($_POST['username'] ?? '');
        $registerEmail = trim($_POST['email'] ?? '');
        $registerFirstName = trim($_POST['first_name'] ?? '');
        $registerLastName = trim($_POST['last_name'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if ($registerUsername === '' || $registerEmail === '' || $password === '' || $confirmPassword === '') {
            $registerFlash = "<div class='alert alert-danger'>Compila tutti i campi obbligatori.</div>";
        } elseif (!filter_var($registerEmail, FILTER_VALIDATE_EMAIL)) {
            $registerFlash = "<div class='alert alert-danger'>Inserisci un indirizzo email valido.</div>";
        } elseif ($password !== $confirmPassword) {
            $registerFlash = "<div class='alert alert-danger'>Le password non corrispondono.</div>";
        } elseif ($userDao->getByUsername($registerUsername)) {
            $registerFlash = "<div class='alert alert-danger'>Username già utilizzato.</div>";
        } elseif ($userDao->getByEmail($registerEmail)) {
            $registerFlash = "<div class='alert alert-danger'>Email già utilizzata.</div>";
        } else {
            $userId = $userDao->create([
                'username' => $registerUsername,
                'email' => $registerEmail,
                'password' => hash_password($password),
                'first_name' => $registerFirstName,
                'last_name' => $registerLastName,
            ]);

            if ($userId > 0) {
                login($registerUsername, $password, $userDao);
                header('Location: profile.php');
                exit;
            }

            $registerFlash = "<div class='alert alert-danger'>Errore durante la registrazione. Riprova.</div>";
        }
    }
}

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/login');

$contentTpl->setContent('login_flash', $loginFlash);
$contentTpl->setContent('register_flash', $registerFlash);
$contentTpl->setContent('login_username', htmlspecialchars($loginUsername));
$contentTpl->setContent('register_username', htmlspecialchars($registerUsername));
$contentTpl->setContent('register_email', htmlspecialchars($registerEmail));
$contentTpl->setContent('register_first_name', htmlspecialchars($registerFirstName));
$contentTpl->setContent('register_last_name', htmlspecialchars($registerLastName));
$contentTpl->setContent('redirect', htmlspecialchars($redirect));

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('if_logged', is_logged_in() ? $_SESSION['username'] : '');
$tpl->setContent('if_admin', has_service('admin_panel') ? '1' : '');
$tpl->setContent('cart_count', is_logged_in() ? (new CartDAO())->countItems((new CartDAO())->getOrCreateCart($_SESSION['user_id'])) : '0');
$tpl->setContent('flash', '');

echo $tpl->get();
