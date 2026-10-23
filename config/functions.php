
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/database.php';



/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function e(?string $v): string
{
    return htmlspecialchars(
        (string) $v,
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

function redirect(string $url): never
{
    // Reject line breaks to prevent header injection.
    if (preg_match('/[\r\n]/', $url)) {
        throw new InvalidArgumentException(
            'Invalid redirect URL.'
        );
    }

    /*
     * Redirects must happen before HTML output.
     * Do not hide this problem with output buffering.
     */
    if (headers_sent($file, $line)) {
        error_log(
            "Redirect failed: output started in {$file} on line {$line}. URL: {$url}"
        );

        exit(
            'Redirect failed because page output has already started. ' .
            'Move the redirect logic before the HTML header.'
        );
    }

    header('Location: ' . $url);
    exit;
}

/*
|--------------------------------------------------------------------------
| Flash Messages
|--------------------------------------------------------------------------
*/

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message
    ];
}

function flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];

    unset($_SESSION['flash']);

    return $messages;
}

/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' .
        e(csrf_token()) .
        '">';
}

function verify_csrf(): void
{
    $sessionToken = $_SESSION['csrf'] ?? '';
    $postedToken = $_POST['csrf'] ?? '';

    if (
        !is_string($sessionToken) ||
        !is_string($postedToken) ||
        $sessionToken === '' ||
        !hash_equals($sessionToken, $postedToken)
    ) {
        http_response_code(419);

        exit(
            'Invalid security token. Please refresh the page and try again.'
        );
    }
}

/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

function current_user(): ?array
{
    $user = $_SESSION['user'] ?? null;

    return is_array($user) ? $user : null;
}

function logged_in(): bool
{
    return current_user() !== null;
}

/*
|--------------------------------------------------------------------------
| Authentication Guards
|--------------------------------------------------------------------------
*/

function require_login(): void
{
    if (!logged_in()) {
        redirect('login.php');
    }
}

function require_role(string $role): void
{
    if (
        !logged_in() ||
        (current_user()['role'] ?? '') !== $role
    ) {
        redirect(
            $role === 'admin'
                ? 'login.php'
                : '../login.php'
        );
    }
}

/*
|--------------------------------------------------------------------------
| Database Helpers
|--------------------------------------------------------------------------
*/

function db_one(
    string $sql,
    string $types = '',
    ...$params
): ?array {
    global $db;

    $stmt = $db->prepare($sql);

    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc() ?: null;
}

function db_all(
    string $sql,
    string $types = '',
    ...$params
): array {
    global $db;

    $stmt = $db->prepare($sql);

    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    return $stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);
}

function db_exec(
    string $sql,
    string $types = '',
    ...$params
): int {
    global $db;

    $stmt = $db->prepare($sql);

    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    return $stmt->affected_rows;
}

/*
|--------------------------------------------------------------------------
| Allowed Table Row Counts
|--------------------------------------------------------------------------
*/

function count_rows(string $table): int
{
    global $db;

    $allowed = [
        'users',
        'doctors',
        'patients',
        'appointments',
        'departments',
        'services',
        'reviews',
        'messages',
        'faqs'
    ];

    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    $result = $db->query(
        "SELECT COUNT(*) AS c FROM `$table`"
    );

    return (int) $result->fetch_assoc()['c'];
}

/*
|--------------------------------------------------------------------------
| Request Helpers
|--------------------------------------------------------------------------
*/

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/*
|--------------------------------------------------------------------------
| Image Upload
|--------------------------------------------------------------------------
*/

function upload_image(
    string $field,
    string $folder
): ?string {
    if (
        !isset($_FILES[$field]) ||
        !is_array($_FILES[$field]) ||
        ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    if (
        ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE)
        !== UPLOAD_ERR_OK
    ) {
        throw new RuntimeException(
            'Image upload failed. Please select the image again.'
        );
    }

    $tmp = $_FILES[$field]['tmp_name'] ?? '';
    $size = (int) ($_FILES[$field]['size'] ?? 0);

    if (!is_uploaded_file($tmp)) {
        throw new RuntimeException(
            'Invalid uploaded image.'
        );
    }

    if ($size <= 0 || $size > 3 * 1024 * 1024) {
        throw new RuntimeException(
            'Image must be smaller than 3MB.'
        );
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);

    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($map[$mime])) {
        throw new RuntimeException(
            'Only JPG, PNG and WEBP images are allowed.'
        );
    }

    // Restrict upload directory names.
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $folder)) {
        throw new RuntimeException(
            'Invalid upload folder.'
        );
    }

    $name = bin2hex(random_bytes(12)) . '.' . $map[$mime];

    $dir = __DIR__ . '/../uploads/' . $folder;

    if (
        !is_dir($dir) &&
        !mkdir($dir, 0755, true) &&
        !is_dir($dir)
    ) {
        throw new RuntimeException(
            'The upload folder could not be created.'
        );
    }

    if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
        throw new RuntimeException(
            'The selected image could not be saved. Please try again.'
        );
    }

    return 'uploads/' . $folder . '/' . $name;
}

/*
|--------------------------------------------------------------------------
| Password Helpers
|--------------------------------------------------------------------------
*/

function password_ok(
    string $plain,
    string $hash
): bool {
    return password_verify($plain, $hash);
}

function safe_password_hash(string $plain): string
{
    return password_hash(
        $plain,
        PASSWORD_DEFAULT
    );
}

/*
|--------------------------------------------------------------------------
| Form Old Values
|--------------------------------------------------------------------------
*/

function old_or(
    string $key,
    string $default = ''
): string {
    $value = $_POST[$key] ?? $default;

    if (!is_string($value) && !is_numeric($value)) {
        $value = $default;
    }

    return e((string) $value);
}
