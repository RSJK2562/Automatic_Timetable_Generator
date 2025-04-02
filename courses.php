<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Initialize variables
$course_id = $course_name = $course_code = $credit_hours = $teacher_id = '';
$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $credit_hours = (int)$_POST['credit_hours'];
    $teacher_id = (int)$_POST['teacher_id'];

    if (empty($course_name)) {
        $errors[] = 'Course name is required';
    }

    if (empty($course_code)) {
        $errors[] = 'Course code is required';
    }

    if ($credit_hours <= 0 || $credit_hours > 6) {
        $errors[] = 'Credit hours must be between 1 and 6';
    }

    if (empty($teacher_id)) {
        $errors[] = 'Please select a teacher';
    }

    // If no errors, save to database
    if (empty($errors)) {
        // Check if we're updating or inserting
        if (isset($_POST['course_id']) && !empty($_POST['course_id'])) {
            $course_id = (int)$_POST['course_id'];
            $sql = "UPDATE courses SET 
                    course_name = ?, 
                    course_code = ?, 
                    credit_hours = ?, 
                    teacher_id = ?
                    WHERE id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiii", $course_name, $course_code, $credit_hours, $teacher_id, $course_id);

            if ($stmt->execute()) {
                $success_message = 'Course updated successfully';
                // Reset form fields
                $course_id = $course_name = $course_code = $credit_hours = $teacher_id = '';
            } else {
                $errors[] = 'Error updating course: ' . $conn->error;
            }
        } else {
            $sql = "INSERT INTO courses (course_name, course_code, credit_hours, teacher_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $course_name, $course_code, $credit_hours, $teacher_id);

            if ($stmt->execute()) {
                $success_message = 'Course added successfully';
                // Reset form fields
                $course_id = $course_name = $course_code = $credit_hours = $teacher_id = '';
            } else {
                $errors[] = 'Error adding course: ' . $conn->error;
            }
        }
    }
}

// Handle edit request
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $sql = "SELECT * FROM courses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
        $course_id = $course['id'];
        $course_name = $course['course_name'];
        $course_code = $course['course_code'];
        $credit_hours = $course['credit_hours'];
        $teacher_id = $course['teacher_id'];
    }
}

// Handle delete request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];

    // Check if course is used in any timetable first
    $check_sql = "SELECT COUNT(*) as count FROM timetable WHERE course_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();

    if ($row['count'] > 0) {
        $errors[] = 'Cannot delete course as it is used in timetable';
    } else {
        $sql = "DELETE FROM courses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $delete_id);

        if ($stmt->execute()) {
            $success_message = 'Course deleted successfully';
        } else {
            $errors[] = 'Error deleting course: ' . $conn->error;
        }
    }
}

// Get all courses
$sql = "SELECT c.*, t.name as teacher_name 
        FROM courses c
        LEFT JOIN teachers t ON c.teacher_id = t.id
        ORDER BY c.course_name";
$result = $conn->query($sql);
$courses = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Get all teachers for dropdown
$sql = "SELECT id, name FROM teachers ORDER BY name";
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
        <h2 class="form-title"><?php echo $course_id ? 'Edit Course' : 'Add New Course'; ?></h2>

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
            <form method="post" action="courses.php">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

                <div class="form-group">
                    <label for="course_name">Course Name:</label>
                    <input type="text" id="course_name" name="course_name" value="<?php echo $course_name; ?>" required>
                </div>

                <div class="form-group">
                    <label for="course_code">Course Code:</label>
                    <input type="text" id="course_code" name="course_code" value="<?php echo $course_code; ?>" required>
                </div>

                <div class="form-group">
                    <label for="credit_hours">Credit Hours:</label>
                    <select id="credit_hours" name="credit_hours" required>
                        <option value="">-- Select --</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $credit_hours == $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="teacher_id">Teacher:</label>
                    <select id="teacher_id" name="teacher_id" required>
                        <option value="">-- Select Teacher --</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher['id']; ?>" <?php echo $teacher_id == $teacher['id'] ? 'selected' : ''; ?>>
                                <?php echo $teacher['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn"><?php echo $course_id ? 'Update Course' : 'Add Course'; ?></button>
                    <?php if ($course_id): ?>
                        <a href="courses.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <h2 class="table-title">Course List</h2>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Course Code</th>
                        <th>Credit Hours</th>
                        <th>Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No courses found. Add a course above.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo $course['course_name']; ?></td>
                                <td><?php echo $course['course_code']; ?></td>
                                <td><?php echo $course['credit_hours']; ?></td>
                                <td><?php echo $course['teacher_name']; ?></td>
                                <td class="action-buttons">
                                    <a href="courses.php?edit=<?php echo $course['id']; ?>" class="btn btn-small btn-edit">Edit</a>
                                    <a href="courses.php?delete=<?php echo $course['id']; ?>" class="btn btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
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