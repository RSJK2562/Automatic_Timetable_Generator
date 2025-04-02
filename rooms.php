<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Initialize variables
$room_id = $room_number = $capacity = $room_type = '';
$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $room_number = trim($_POST['room_number']);
    $capacity = (int)$_POST['capacity'];
    $room_type = trim($_POST['room_type']);
    
    if (empty($room_number)) {
        $errors[] = 'Room number is required';
    }
    
    if ($capacity <= 0) {
        $errors[] = 'Capacity must be greater than 0';
    }
    
    if (empty($room_type)) {
        $errors[] = 'Room type is required';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        // Check if we're updating or inserting
        if (isset($_POST['room_id']) && !empty($_POST['room_id'])) {
            $room_id = (int)$_POST['room_id'];
            $sql = "UPDATE rooms SET 
                    room_number = ?, 
                    capacity = ?, 
                    room_type = ?
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisi", $room_number, $capacity, $room_type, $room_id);
            
            if ($stmt->execute()) {
                $success_message = 'Room updated successfully';
                // Reset form fields if not editing
                if (!isset($_GET['edit'])) {
                    $room_id = $room_number = $capacity = $room_type = '';
                }
            } else {
                $errors[] = 'Error updating room: ' . $conn->error;
            }
        } else {
            $sql = "INSERT INTO rooms (room_number, capacity, room_type) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sis", $room_number, $capacity, $room_type);
            
            if ($stmt->execute()) {
                $success_message = 'Room added successfully';
                // Reset form fields
                $room_id = $room_number = $capacity = $room_type = '';
            } else {
                $errors[] = 'Error adding room: ' . $conn->error;
            }
        }
    }
}

// Handle edit request
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $sql = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc();
        $room_id = $room['id'];
        $room_number = $room['room_number'];
        $capacity = $room['capacity'];
        $room_type = $room['room_type'];
    }
}

// Handle delete request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // Check if room is used in any timetable first
    $check_sql = "SELECT COUNT(*) as count FROM timetable WHERE room_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $errors[] = 'Cannot delete room as it is used in timetable';
    } else {
        $sql = "DELETE FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $success_message = 'Room deleted successfully';
        } else {
            $errors[] = 'Error deleting room: ' . $conn->error;
        }
    }
}

// Get all rooms
$sql = "SELECT * FROM rooms ORDER BY room_number";
$result = $conn->query($sql);
$rooms = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}

// Room types
$room_types = ['Lecture Hall', 'Lab', 'Classroom', 'Seminar Room'];

// Include header
include 'includes/header.php';
?>

<div class="container">
    <main>
        <h2 class="form-title"><?php echo $room_id ? 'Edit Room' : 'Add New Room'; ?></h2>
        
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
            <form method="post" action="rooms.php">
                <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                
                <div class="form-group">
                    <label for="room_number">Room Number:</label>
                    <input type="text" id="room_number" name="room_number" value="<?php echo $room_number; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="capacity">Capacity:</label>
                    <input type="number" id="capacity" name="capacity" value="<?php echo $capacity; ?>" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="room_type">Room Type:</label>
                    <select id="room_type" name="room_type" required>
                        <option value="">-- Select Room Type --</option>
                        <?php foreach ($room_types as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo $room_type == $type ? 'selected' : ''; ?>>
                                <?php echo $type; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn"><?php echo $room_id ? 'Update Room' : 'Add Room'; ?></button>
                    <?php if ($room_id): ?>
                        <a href="rooms.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <h2 class="table-title">Room List</h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Capacity</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No rooms found. Add a room above.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?php echo $room['room_number']; ?></td>
                                <td><?php echo $room['capacity']; ?></td>
                                <td><?php echo $room['room_type']; ?></td>
                                <td class="action-buttons">
                                    <a href="rooms.php?edit=<?php echo $room['id']; ?>" class="btn btn-small btn-edit">Edit</a>
                                    <a href="rooms.php?delete=<?php echo $room['id']; ?>" class="btn btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this room?')">Delete</a>
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