<?php
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function clean_text($value) {
    return trim(strip_tags((string)$value));
}

function safe_field($value) {
    $value = clean_text($value);
    return strpos($value, '|') === false ? $value : '';
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . h($_SESSION['csrf_token']) . '">';
}

function require_csrf() {
    $token = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Your session expired. Please go back, refresh the page, and try again.');
    }
}

function password_matches($password, $stored) {
    $info = password_get_info($stored);
    return !empty($info['algo']) ? password_verify($password, $stored) : hash_equals((string)$stored, (string)$password);
}

function db_connect() {
    if (strpos(DB_HOST, 'XXX') !== false || strpos(DB_USER, 'XXXXXXXX') !== false || DB_PASSWORD === 'YOUR_HOSTING_ACCOUNT_PASSWORD') {
        http_response_code(503);
        die('Setup required: enter your InfinityFree MySQL details in includes/config.php, then import database/medinest.sql.');
    }
    mysqli_report(MYSQLI_REPORT_OFF);
    $connection = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$connection) {
        http_response_code(503);
        die('Database connection unavailable. Check includes/config.php and import database/medinest.sql in InfinityFree phpMyAdmin.');
    }
    mysqli_set_charset($connection, 'utf8mb4');
    return $connection;
}

function table_for_file($file) {
    $tables = array('users.txt' => 'users', 'products.txt' => 'products', 'orders.txt' => 'orders', 'audit.txt' => 'audit_log');
    return isset($tables[$file]) ? $tables[$file] : null;
}

function read_rows($file) {
    $queries = array(
        'users.txt' => 'SELECT id, fullname, email, password, address, contact, role FROM users ORDER BY id',
        'products.txt' => 'SELECT id, name, category, description, price, stock, type FROM products ORDER BY id',
        'orders.txt' => 'SELECT id, user_id, order_date, total, payment_method, status FROM orders ORDER BY id DESC',
        'audit.txt' => 'SELECT id, activity_date, user_name, activity FROM audit_log ORDER BY id DESC LIMIT 250'
    );
    if (!isset($queries[$file])) return array();
    $connection = db_connect();
    $result = mysqli_query($connection, $queries[$file]);
    $rows = array();
    if ($result) {
        while ($row = mysqli_fetch_row($result)) $rows[] = $row;
        mysqli_free_result($result);
    }
    mysqli_close($connection);
    return $rows;
}

function append_row($file, $values) {
    $sql = array(
        'users.txt' => 'INSERT INTO users (id, fullname, email, password, address, contact, role) VALUES (?, ?, ?, ?, ?, ?, ?)',
        'products.txt' => 'INSERT INTO products (id, name, category, description, price, stock, type) VALUES (?, ?, ?, ?, ?, ?, ?)',
        'orders.txt' => 'INSERT INTO orders (user_id, order_date, total, payment_method, status) VALUES (?, ?, ?, ?, ?)',
        'audit.txt' => 'INSERT INTO audit_log (activity_date, user_name, activity) VALUES (?, ?, ?)'
    );
    if (!isset($sql[$file])) return false;
    if ($file === 'orders.txt' || $file === 'audit.txt') $values = array_slice($values, 1);
    $connection = db_connect();
    $stmt = mysqli_prepare($connection, $sql[$file]);
    $escaped = array();
    foreach ($values as $value) $escaped[] = safe_field($value);
    $types = str_repeat('s', count($escaped));
    mysqli_stmt_bind_param($stmt, $types, ...$escaped);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
    return $ok;
}

function save_rows($file, $rows) {
    $table = table_for_file($file);
    if ($table === null) return false;
    $connection = db_connect();
    if ($file === 'users.txt') {
        $stmt = mysqli_prepare($connection, 'UPDATE users SET fullname=?, email=?, password=?, address=?, contact=?, role=? WHERE id=?');
        $ok = true;
        foreach ($rows as $row) {
            $id = (int)$row[0];
            mysqli_stmt_bind_param($stmt, 'ssssssi', $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $id);
            if (!mysqli_stmt_execute($stmt)) { $ok = false; break; }
        }
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        return $ok;
    }
    mysqli_begin_transaction($connection);
    $ok = mysqli_query($connection, 'DELETE FROM `' . $table . '`');
    if ($ok) {
        mysqli_commit($connection);
        mysqli_close($connection);
        foreach ($rows as $row) if (!append_row($file, $row)) return false;
        return true;
    }
    mysqli_rollback($connection);
    mysqli_close($connection);
    return false;
}

function find_row($file, $index, $value) {
    foreach (read_rows($file) as $row) {
        if (isset($row[$index]) && (string)$row[$index] === (string)$value) return $row;
    }
    return array();
}

function signed_in() { return isset($_SESSION['user_id']); }
function is_admin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }

function audit($action) {
    $user = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';
    append_row('audit.txt', array(time(), date('Y-m-d H:i:s'), $user, $action));
}

function cart_count() {
    $total = 0;
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $quantity) $total += max(0, (int)$quantity);
    }
    return $total;
}

function redirect_box($url, $message) {
    echo '<div class="notice success">' . h($message) . '</div>';
    echo '<meta http-equiv="refresh" content="1;url=' . h($url) . '">';
}

function place_order($userId, $method, $cart) {
    $connection = db_connect();
    mysqli_begin_transaction($connection);
    try {
        $total = 0.0;
        $items = array();
        $productStmt = mysqli_prepare($connection, 'SELECT name, price, stock FROM products WHERE id = ? FOR UPDATE');
        foreach ($cart as $id => $quantity) {
            $id = (int)$id; $quantity = (int)$quantity;
            if ($id < 1 || $quantity < 1) continue;
            mysqli_stmt_bind_param($productStmt, 'i', $id);
            mysqli_stmt_execute($productStmt);
            mysqli_stmt_bind_result($productStmt, $name, $price, $stock);
            if (!mysqli_stmt_fetch($productStmt) || $stock < $quantity) throw new Exception('One or more products no longer have enough stock.');
            mysqli_stmt_free_result($productStmt);
            $items[] = array($id, $quantity);
            $total += (float)$price * $quantity;
        }
        mysqli_stmt_close($productStmt);
        if (!$items) throw new Exception('Your cart is empty.');
        $date = date('Y-m-d H:i:s');
        $status = 'Pending';
        $orderStmt = mysqli_prepare($connection, 'INSERT INTO orders (user_id, order_date, total, payment_method, status) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($orderStmt, 'isdss', $userId, $date, $total, $method, $status);
        if (!mysqli_stmt_execute($orderStmt)) throw new Exception('The order could not be saved.');
        $orderId = mysqli_insert_id($connection);
        mysqli_stmt_close($orderStmt);
        $stockStmt = mysqli_prepare($connection, 'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
        foreach ($items as $item) {
            mysqli_stmt_bind_param($stockStmt, 'iii', $item[1], $item[0], $item[1]);
            if (!mysqli_stmt_execute($stockStmt) || mysqli_stmt_affected_rows($stockStmt) !== 1) throw new Exception('Stock changed while placing the order.');
        }
        mysqli_stmt_close($stockStmt);
        mysqli_commit($connection);
        mysqli_close($connection);
        return array('ok' => true, 'id' => $orderId, 'total' => $total);
    } catch (Exception $e) {
        mysqli_rollback($connection);
        mysqli_close($connection);
        return array('ok' => false, 'message' => $e->getMessage());
    }
}
