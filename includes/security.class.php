<?php
/**
 * MijSys CMS - Klasa Bezpieczeństwa
 * 
 * @package MijSysCMS
 * @version 1.0
 */

class Security {
    
    /**
     * Generuje token CSRF
     * @return string
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Waliduje token CSRF
     * @param string $token
     * @return bool
     */
    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Usuwa token CSRF po użyciu
     */
    public static function removeCSRFToken() {
        unset($_SESSION['csrf_token']);
    }
    
    /**
     * Sanityzuje dane wejściowe
     * @param mixed $data
     * @return mixed
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
        } else {
            $data = trim($data);
            $data = stripslashes($data);
        }
        return $data;
    }
    
    /**
     * Sanityzuje dane wyjściowe (zapobiega XSS)
     * @param string $data
     * @return string
     */
    public static function sanitizeOutput($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Hashuje hasło
     * @param string $password
     * @return string
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Weryfikuje hasło
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Zapobiega XSS
     * @param string $string
     * @return string
     */
    public static function preventXSS($string) {
        return htmlspecialchars(strip_tags($string), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Waliduje email
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Waliduje URL
     * @param string $url
     * @return bool
     */
    public static function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Generuje bezpieczny losowy string
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Szyfruje dane
     * @param string $data
     * @param string $key
     * @return string
     */
    public static function encrypt($data, $key = SECURITY_SALT) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * Deszyfruje dane
     * @param string $data
     * @param string $key
     * @return string|false
     */
    public static function decrypt($data, $key = SECURITY_SALT) {
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    }
    
    /**
     * Sprawdza siłę hasła
     * @param string $password
     * @return array
     */
    public static function checkPasswordStrength($password) {
        $strength = 0;
        $length = strlen($password);
        
        if ($length >= 8) $strength++;
        if ($length >= 12) $strength++;
        if (preg_match('/[a-z]/', $password)) $strength++;
        if (preg_match('/[A-Z]/', $password)) $strength++;
        if (preg_match('/[0-9]/', $password)) $strength++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;
        
        $levels = ['weak', 'fair', 'good', 'strong', 'very strong'];
        $index = min(floor($strength / 1.5), 4);
        
        return [
            'score' => $strength,
            'level' => $levels[$index],
            'percentage' => min(($strength / 6) * 100, 100)
        ];
    }
    
    /**
     * Rate limiting - sprawdza liczbę prób
     * @param string $identifier (np. email lub IP)
     * @param int $maxAttempts
     * @param int $timeWindow w sekundach
     * @return bool true jeśli przekroczono limit
     */
    public static function isRateLimited($identifier, $maxAttempts = MAX_LOGIN_ATTEMPTS, $timeWindow = LOGIN_LOCKOUT_TIME) {
        $key = 'rate_limit_' . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }
        
        $data = $_SESSION[$key];
        
        // Reset jeśli minął czas blokady
        if (time() - $data['first_attempt'] > $timeWindow) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
            return false;
        }
        
        // Sprawdź czy przekroczono limit
        if ($data['attempts'] >= $maxAttempts) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Zwiększa licznik prób
     * @param string $identifier
     */
    public static function incrementRateLimit($identifier) {
        $key = 'rate_limit_' . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }
        
        $_SESSION[$key]['attempts']++;
    }
    
    /**
     * Resetuje licznik prób
     * @param string $identifier
     */
    public static function resetRateLimit($identifier) {
        $key = 'rate_limit_' . md5($identifier);
        unset($_SESSION[$key]);
    }
    
    /**
     * Czyści dane wejściowe HTML (dla edytora WYSIWYG)
     * @param string $html
     * @return string
     */
    public static function sanitizeHTML($html) {
        // Dozwolone tagi HTML
        $allowed_tags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><code><pre><table><thead><tbody><tr><th><td><div><span>';
        
        // Usuń niedozwolone tagi
        $html = strip_tags($html, $allowed_tags);
        
        // Dodatkowo można użyć biblioteki HTML Purifier dla większego bezpieczeństwa
        
        return $html;
    }
}