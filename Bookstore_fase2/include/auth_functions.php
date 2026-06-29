<?php

// Assicuriamoci che la sessione non sia già stata avviata prima di chiamare session_start()
if (session_status() === PHP_SESSION_NONE) {
    // Impostiamo alcuni parametri di sicurezza per i cookie di sessione prima di avviare
    ini_set('session.cookie_httponly', 1); // Previene accessi XSS al cookie di sessione
    ini_set('session.use_only_cookies', 1); // Forza l'uso dei cookie per la sessione (più sicuro)
    session_start();
}

/**
 * Genera l'hash sicuro di una password prima di salvarla nel database (fase di registrazione).
 * 
 * @param string $password
 * @return string L'hash generato
 */
function hash_password(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Tenta di effettuare il login di un utente.
 * In caso di successo, salva in sessione i dati dell'utente e l'array dei suoi servizi.
 * 
 * @param string $username
 * @param string $password Password in chiaro
 * @param UserDAO $userDao Istanza del DAO per le query
 * @return bool True se il login ha successo, False altrimenti
 */
function login(string $username, string $password, UserDAO $userDao): bool {
    // verifyCredentials usa già password_verify al suo interno
    $user = $userDao->verifyCredentials($username, $password);
    
    if ($user) {
        // Rigeneriamo l'ID di sessione per prevenire attacchi di Session Fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        // Carichiamo subito i servizi dell'utente in sessione per l'Access Control
        $services = $userDao->getUserServices($user['id']);
        $_SESSION['services'] = $services;
        
        return true;
    }
    
    return false;
}

/**
 * Effettua il logout distruggendo completamente la sessione corrente.
 */
function logout(): void {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Controlla se l'utente attuale è loggato.
 * 
 * @return bool
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Controlla se l'utente corrente ha i permessi per un determinato servizio.
 * Implementazione del pattern Role-Based / Service-Based Access Control.
 * 
 * @param string $serviceName Il nome del servizio richiesto (es. 'admin_panel', 'manage_books')
 * @return bool
 */
function has_service(string $serviceName): bool {
    if (!is_logged_in() || !isset($_SESSION['services'])) {
        return false;
    }
    
    return in_array($serviceName, $_SESSION['services']);
}

/**
 * Protegge una pagina bloccando l'accesso se l'utente non ha il servizio richiesto.
 * Se non è autorizzato, reindirizza a una pagina di errore o al login.
 * 
 * @param string $serviceName
 * @param string $redirectUrl Dove mandare l'utente se non è autorizzato
 */
function require_service(string $serviceName, string $redirectUrl = '/login.php'): void {
    if (!has_service($serviceName)) {
        header("Location: " . $redirectUrl);
        exit;
    }
}
