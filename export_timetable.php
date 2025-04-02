<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Initialize variables
$timetable_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$timetable = null;
$slots = [];
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
$time_slots = [
    '8:00 - 9:00',
    '9:00 - 10:00',
    '10:00 - 11:00',
    '11:00 - 12:00',
    '12:00 - 13:00',
    '13:00 - 14:00',
    '14:00 - 15:00',
    '15:00 - 16:00'
];

// Get timetable details
if ($timetable_id) {
    $sql = "SELECT * FROM timetables WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $timetable_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $timetable = $result->fetch_assoc();

        // Get timetable slots
        $sql = "SELECT ts.*, c.course_name, c.course_code, t.name as teacher_name, r.room_number 
                FROM timetable_slots ts
                JOIN courses c ON ts.course_id = c.id
                JOIN teachers t ON ts.teacher_id = t.id
                JOIN rooms r ON ts.room_id = r.id
                WHERE ts.timetable_id = ?
                ORDER BY ts.day, ts.time_slot";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $timetable_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $slots[$row['day']][$row['time_slot']] = $row;
            }
        }
    } else {
        die("Timetable not found");
    }
} else {
    die("Invalid timetable ID");
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $timetable['name'] . '.csv"');

// Create CSV output
$output = fopen('php://output', 'w');

// Add headers
fputcsv($output, array_merge(['Time'], array_map('ucfirst', $days)));

// Add data rows
foreach ($time_slots as $slot_index => $slot) {
    $row = [$slot];

    foreach ($days as $day) {
        if (isset($slots[$day][$slot_index])) {
            $cell = $slots[$day][$slot_index]['course_name'] . ' (' . $slots[$day][$slot_index]['course_code'] . ')' .
                ' - ' . $slots[$day][$slot_index]['teacher_name'] .
                ' - Room: ' . $slots[$day][$slot_index]['room_number'];
            $row[] = $cell;
        } else {
            $row[] = '';
        }
    }

    fputcsv($output, $row);
}

fclose($output);
exit;
