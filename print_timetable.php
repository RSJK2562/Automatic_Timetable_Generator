<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Initialize variables
$timetable_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$timetable = null;
$slots = [];
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
$time_slots = [
    '8:00 - 9:00', '9:00 - 10:00', '10:00 - 11:00', '11:00 - 12:00',
    '12:00 - 13:00', '13:00 - 14:00', '14:00 - 15:00', '15:00 - 16:00'
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Timetable - <?php echo $timetable['name']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .timetable-info {
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .course-slot {
            padding: 5px;
        }
        .course-name {
            font-weight: bold;
        }
        .course-code {
            font-size: 0.9rem;
        }
        .course-teacher, .course-room {
            font-size: 0.8rem;
            color: #666;
        }
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">Print Timetable</button>
        <button onclick="window.close()">Close</button>
    </div>
    
    <h1>Timetable: <?php echo $timetable['name']; ?></h1>
    <div class="timetable-info">
        <p>Generated on: <?php echo date('F d, Y H:i', strtotime($timetable['created_at'])); ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <?php foreach ($days as $day): ?>
                    <th><?php echo ucfirst($day); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($time_slots as $slot_index => $slot): ?>
                <tr>
                    <td><?php echo $slot; ?></td>
                    <?php foreach ($days as $day): ?>
                        <td>
                            <?php if (isset($slots[$day][$slot_index])): ?>
                                <div class="course-slot">
                                    <div class="course-name"><?php echo $slots[$day][$slot_index]['course_name']; ?></div>
                                    <div class="course-code"><?php echo $slots[$day][$slot_index]['course_code']; ?></div>
                                    <div class="course-teacher"><?php echo $slots[$day][$slot_index]['teacher_name']; ?></div>
                                    <div class="course-room">Room: <?php echo $slots[$day][$slot_index]['room_number']; ?></div>
                                </div>
                            <?php else: ?>
                                &nbsp;
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>