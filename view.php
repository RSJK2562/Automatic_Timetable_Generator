<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Initialize variables
$errors = [];
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
        $errors[] = 'Timetable not found';
    }
}

// Get all timetables for dropdown
$sql = "SELECT * FROM timetables ORDER BY created_at DESC";
$result = $conn->query($sql);
$timetables = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timetables[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container">
    <main>
        <!-- Show errors if any -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Timetable selection form -->
        <div class="timetable-actions">
            <form method="get" action="view.php" class="timetable-select-form">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="id">Select Timetable:</label>
                    <select id="id" name="id" onchange="this.form.submit()">
                        <option value="">-- Select --</option>
                        <?php foreach ($timetables as $t): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo $timetable_id == $t['id'] ? 'selected' : ''; ?>>
                                <?php echo $t['name']; ?> (<?php echo date('M d, Y', strtotime($t['created_at'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            
            <?php if ($timetable): ?>
                <div class="timetable-actions-buttons">
                    <a href="print_timetable.php?id=<?php echo $timetable_id; ?>" class="btn" target="_blank">Print</a>
                    <a href="export_timetable.php?id=<?php echo $timetable_id; ?>" class="btn">Export</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($timetable): ?>
            <div class="timetable">
                <div class="timetable-header">
                    <h2><?php echo $timetable['name']; ?></h2>
                    <p>Generated on: <?php echo date('F d, Y H:i', strtotime($timetable['created_at'])); ?></p>
                </div>
                
                <div class="table-container">
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
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                Please select a timetable to view.
            </div>
        <?php endif; ?>
    </main>
</div>

<?php
include 'includes/footer.php';
?>