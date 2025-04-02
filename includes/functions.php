<?php
/**
 * Display alert message
 * 
 * @param string $message Message to display
 * @param string $type Type of alert (success, error, warning)
 * @return string HTML for alert
 */
function displayAlert($message, $type = 'success') {
    return '<div class="alert alert-' . $type . '">' . $message . '</div>';
}

/**
 * Sanitize input data
 * 
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to another page
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Get day name from numeric day
 * 
 * @param int $day_num Day number (0 for Monday, etc.)
 * @return string Day name
 */
function getDayName($day_num) {
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    return isset($days[$day_num]) ? ucfirst($days[$day_num]) : '';
}

/**
 * Format a timestamp
 * 
 * @param string $timestamp Timestamp
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($timestamp, $format = 'M d, Y H:i') {
    return date($format, strtotime($timestamp));
}
?>