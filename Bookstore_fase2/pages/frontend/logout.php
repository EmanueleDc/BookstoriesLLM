<?php

require_once '../../include/auth_functions.php';

if (is_logged_in()) {
    logout();
}

header('Location: index.php');
exit;
