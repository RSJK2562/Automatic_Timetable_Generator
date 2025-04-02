<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Initialize variables
$teacher_id = $name = $email = $phone = '';
$availability = [
    'monday' => [], 'tuesday' => [], 'wednesday' => [], 
    'thursday' => [], 'friday' => []
];
$errors = [];
$success_message = '';

// Time slots for availability
$time_slots = [
    '8:00 - 9:00', '9:00 - 10:00', '10:00 - 11:00', '11:00 - 12:00',
    '12:00 - 13:00', '13:00 - 14:00', '14:00 - 15:00', '15:00 - 16:00'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    if (empty($name)) {
        $errors[] = 'Teacher name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Process availability (convert to JSON for storage)
    $availability = [
        'monday' => isset($_POST['monday']) ? $_POST['monday'] : [],
        'tuesday' => isset($_POST['tuesday']) ? $_POST['tuesday'] : [],
        'wednesday' => isset($_POST['wednesday']) ? $_POST['wednesday'] : [],
        'thursday' => isset($_POST['thursday']) ? $_POST['thursday'] : [],
        'friday' => isset($_POST['friday']) ? $_POST['friday'] : []
    ];
    
    $availability_json = json_encode($availability);
    
    // If no errors, save to database
    if (empty($errors)) {
        // Check if we're updating or inserting
        if (isset($_POST['teacher_id']) && !empty($_POST['teacher_id'])) {
            $teacher_id = (int)$_POST['teacher_id'];
            $sql = "UPDATE teachers SET 
                    name = ?, 
                    email = ?, 
                    phone = ?, 
                    availability = ?
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $name, $email, $phone, $availability_json, $teacher_id);
            
            if ($stmt->execute()) {
                $success_message = 'Teacher updated successfully';
                // Reset form fields if not editing
                if (!isset($_GET['edit'])) {
                    $teacher_id = $name = $email = $phone = '';
                    $availability = [
                        'monday' => [], 'tuesday' => [], 'wednesday' => [], 
                        'thursday' => [], 'friday' => []
                    ];
                }
            } else {
                $errors[] = 'Error updating teacher: ' . $conn->error;
            }
        } else {
            $sql = "INSERT INTO teachers (name, email, phone, availability) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $phone, $availability_json);
            
            if ($stmt->execute()) {
                $success_message = 'Teacher added successfully';
                // Reset form fields
                $teacher_id = $name = $email = $phone = '';
                $availability = [
                    'monday' => [], 'tuesday' => [], 'wednesday' => [], 
                    'thursday' => [], 'friday' => []
                ];
            } else {
                $errors[] = 'Error adding teacher: ' . $conn->error;
            }
        }
    }
}

// Handle edit request
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $sql = "SELECT * FROM teachers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $teacher = $result->fetch_assoc();
        $teacher_id = $teacher['id'];
        $name = $teacher['name'];
        $email = $teacher['email'];
        $phone = $teacher['phone'];
        
        // Parse availability from JSON
        if (!empty($teacher['availability'])) {
            $availability = json_decode($teacher['availability'], true);
        }
    }
}

// Handle delete request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // Check if teacher is assigned to any courses first
    $check_sql = "SELECT COUNT(*) as count FROM courses WHERE teacher_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $errors[] = 'Cannot delete teacher as they are assigned to courses';
    } else {
        $sql = "DELETE FROM teachers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $success_message = 'Teacher deleted successfully';
        } else {
            $errors[] = 'Error deleting teacher: ' . $conn->error;
        }
    }
}

// Get all teachers
$sql = "SELECT * FROM teachers ORDER BY name";
$result = $conn->query($sql);
$teachers = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container">
    <main>
        <h2 class="form-title"><?php echo $teacher_id ? 'Edit Teacher' : 'Add New Teacher'; ?></h2>
        
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
            <form method="post" action="teachers.php">
                <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">
                
                <div class="form-group">
                    <label for="name">Teacher Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>">
                </div>
                
                <div class="form-group">
                    <label>Availability:</label>
                    <div class="availability-grid">
                        <table class="availability-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Monday</th>
                                    <th>Tuesday</th>
                                    <th>Wednesday</th>
                                    <th>Thursday</th>
                                    <th>Friday</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($time_slots as $index => $slot): ?>
                                    <tr>
                                        <td><?php echo $slot; ?></td>
                                        <td>
                                            <input type="checkbox" name="monday[]" value="<?php echo $index; ?>" 
                                                <?php echo in_array((string)$index, $availability['monday']) ? 'checked' : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" name="tuesday[]" value="<?php echo $index; ?>" 
                                                <?php echo in_array((string)$index, $availability['tuesday']) ? 'checked' : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" name="wednesday[]" value="<?php echo $index; ?>" 
                                                <?php echo in_array((string)$index, $availability['wednesday']) ? 'checked' : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" name="thursday[]" value="<?php echo $index; ?>" 
                                                <?php echo in_array((string)$index, $availability['thursday']) ? 'checked' : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" name="friday[]" value="<?php echo $index; ?>" 
                                                <?php echo in_array((string)$index, $availability['friday']) ? 'checked' : ''; ?>>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn"><?php echo $teacher_id ? 'Update Teacher' : 'Add Teacher'; ?></button>
                    <?php if ($teacher_id): ?>
                        <a href="teachers.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <h2 class="table-title">Teacher List</h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teachers)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No teachers found. Add a teacher above.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td><?php echo $teacher['name']; ?></td>
                                <td><?php echo $teacher['email']; ?></td>
                                <td><?php echo $teacher['phone']; ?></td>
                                <td class="action-buttons">
                                    <a href="teachers.php?edit=<?php echo $teacher['id']; ?>" class="btn btn-small btn-edit">Edit</a>
                                    <a href="teachers.php?delete=<?php echo $teacher['id']; ?>" class="btn btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this teacher?')">Delete</a>
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