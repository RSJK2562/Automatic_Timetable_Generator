<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Initialize variables
$errors = [];
$success_message = '';
$timetable_id = '';
$timetable_name = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $timetable_name = trim($_POST['timetable_name']);
    
    if (empty($timetable_name)) {
        $errors[] = 'Timetable name is required';
    }
    
    // Get all necessary data
    $courses = [];
    $teachers = [];
    $rooms = [];
    $time_slots = [
        '8:00 - 9:00', '9:00 - 10:00', '10:00 - 11:00', '11:00 - 12:00',
        '12:00 - 13:00', '13:00 - 14:00', '14:00 - 15:00', '15:00 - 16:00'
    ];
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    
    // Get courses
    $sql = "SELECT c.*, t.name as teacher_name 
            FROM courses c
            JOIN teachers t ON c.teacher_id = t.id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    } else {
        $errors[] = 'No courses found. Please add courses first.';
    }
    
    // Get teachers with availability
    $sql = "SELECT * FROM teachers";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Parse availability
            $row['availability_array'] = !empty($row['availability']) ? 
                json_decode($row['availability'], true) : 
                [];
            $teachers[$row['id']] = $row;
        }
    } else {
        $errors[] = 'No teachers found. Please add teachers first.';
    }
    
    // Get rooms
    $sql = "SELECT * FROM rooms";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rooms[] = $row;
        }
    } else {
        $errors[] = 'No rooms found. Please add rooms first.';
    }
    
    // If no errors, generate timetable
    if (empty($errors)) {
        // Create a new timetable entry
        $sql = "INSERT INTO timetables (name, created_at) VALUES (?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $timetable_name);
        
        if ($stmt->execute()) {
            $timetable_id = $conn->insert_id;
            
            // Initialize timetable grid
            $timetable = [];
            foreach ($days as $day) {
                foreach ($time_slots as $slot_index => $slot) {
                    $timetable[$day][$slot_index] = [
                        'booked' => false,
                        'course_id' => null,
                        'teacher_id' => null,
                        'room_id' => null
                    ];
                }
            }
            
            // Generate timetable using constraint-based algorithm
            $allocated_courses = [];
            
            // Sort courses by credit hours (descending) to allocate larger courses first
            usort($courses, function($a, $b) {
                return $b['credit_hours'] - $a['credit_hours'];
            });
            
            foreach ($courses as $course) {
                $teacher_id = $course['teacher_id'];
                $credit_hours = $course['credit_hours'];
                $allocated_hours = 0;
                
                // Try to spread course across different days
                $days_to_try = $days;
                shuffle($days_to_try); // Randomize days for better distribution
                
                foreach ($days_to_try as $day) {
                    if ($allocated_hours >= $credit_hours) {
                        break; // Course fully allocated
                    }
                    
                    // Get teacher availability for this day
                    $teacher_availability = isset($teachers[$teacher_id]['availability_array'][$day]) ? 
                        $teachers[$teacher_id]['availability_array'][$day] : [];
                    
                    // Convert to integers (they may be stored as strings)
                    $teacher_availability = array_map('intval', $teacher_availability);
                    
                    // Try each time slot
                    foreach ($time_slots as $slot_index => $slot) {
                        if ($allocated_hours >= $credit_hours) {
                            break; // Course fully allocated
                        }
                        
                        // Skip if teacher not available
                        if (!in_array($slot_index, $teacher_availability)) {
                            continue;
                        }
                        
                        // Skip if slot already booked
                        if ($timetable[$day][$slot_index]['booked']) {
                            continue;
                        }
                        
                        // Find suitable room
                        $room_id = null;
                        foreach ($rooms as $room) {
                            // Check if room already booked for this slot
                            $room_booked = false;
                            foreach ($timetable[$day] as $time_slot) {
                                if ($time_slot['booked'] && $time_slot['room_id'] == $room['id']) {
                                    $room_booked = true;
                                    break;
                                }
                            }
                            
                            if (!$room_booked) {
                                $room_id = $room['id'];
                                break;
                            }
                        }
                        
                        if ($room_id) {
                            // Allocate course to this slot
                            $timetable[$day][$slot_index] = [
                                'booked' => true,
                                'course_id' => $course['id'],
                                'teacher_id' => $teacher_id,
                                'room_id' => $room_id
                            ];
                            
                            // Save to database
                            $sql = "INSERT INTO timetable_slots 
                                    (timetable_id, day, time_slot, course_id, teacher_id, room_id) 
                                    VALUES (?, ?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("isiiii", $timetable_id, $day, $slot_index, $course['id'], $teacher_id, $room_id);
                            $stmt->execute();
                            
                            $allocated_hours++;
                            
                            if (!isset($allocated_courses[$course['id']])) {
                                $allocated_courses[$course['id']] = 0;
                            }
                            $allocated_courses[$course['id']]++;
                        }
                    }
                }
            }
            
            // Check for unallocated courses
            $unallocated_courses = [];
            foreach ($courses as $course) {
                $allocated = isset($allocated_courses[$course['id']]) ? $allocated_courses[$course['id']] : 0;
                if ($allocated < $course['credit_hours']) {
                    $unallocated_courses[] = $course['course_name'] . " (allocated {$allocated}/{$course['credit_hours']} hours)";
                }
            }
            
            if (!empty($unallocated_courses)) {
                $errors[] = 'Warning: Some courses could not be fully allocated due to constraints:';
                foreach ($unallocated_courses as $course) {
                    $errors[] = "- " . $course;
                }
            }
            
            $success_message = 'Timetable generated successfully.';
            
            // Redirect to view the generated timetable
            header("Location: view.php?id={$timetable_id}");
            exit;
        } else {
            $errors[] = 'Error creating timetable: ' . $conn->error;
        }
    }
}

// Get existing timetables
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
        <h2 class="form-title">Generate New Timetable</h2>
        
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
        
        <!-- Show success message if any -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="post" action="generate.php">
                <div class="form-group">
                    <label for="timetable_name">Timetable Name:</label>
                    <input type="text" id="timetable_name" name="timetable_name" value="<?php echo $timetable_name; ?>" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Generate Timetable</button>
                </div>
            </form>
        </div>
        
        <h2 class="table-title">Existing Timetables</h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Timetable Name</th>
                        <th>Created On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($timetables)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">No timetables found. Generate a new timetable above.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($timetables as $timetable): ?>
                            <tr>
                                <td><?php echo $timetable['name']; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($timetable['created_at'])); ?></td>
                                <td class="action-buttons">
                                    <a href="view.php?id=<?php echo $timetable['id']; ?>" class="btn btn-small">View</a>
                                    <a href="delete_timetable.php?id=<?php echo $timetable['id']; ?>" class="btn btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this timetable?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php
include 'includes/footer.php';
?>