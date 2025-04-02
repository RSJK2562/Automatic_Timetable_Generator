<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid timetable ID';
    redirect('generate.php');
}

$timetable_id = (int)$_GET['id'];

// Delete timetable and its slots (slots will be deleted automatically due to ON DELETE CASCADE)
$sql = "DELETE FROM timetables WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $timetable_id);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Timetable deleted successfully';
} else {
    $_SESSION['error'] = 'Error deleting timetable: ' . $conn->error;
}

redirect('generate.php');
?>