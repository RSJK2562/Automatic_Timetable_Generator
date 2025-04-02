<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Automatic Timetable Generator">
    <meta name="keywords" content="Automatic Timetable Generator">
    <meta name="author" content="Ravi Gautam">
    <title>Automatic Timetable Generator</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Automatic Timetable Generator</h1>
            <nav>
                <ul>
                    <li><a href="index" class="active">Home</a></li>
                    <li><a href="courses">Courses</a></li>
                    <li><a href="teachers">Teachers</a></li>
                    <li><a href="rooms">Rooms</a></li>
                    <li><a href="generate">Generate Timetable</a></li>
                    <li><a href="view">View Timetable</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="welcome">
                <h2>Welcome to the Automatic Timetable Generator</h2>
                <p>This application helps you create conflict-free timetables for educational institutions.</p>
                <div class="features">
                    <div class="feature-card">
                        <h3>Add Courses</h3>
                        <p>Add and manage courses with their credit hours and instructors.</p>
                        <a href="courses" class="btn">Manage Courses</a>
                    </div>
                    <div class="feature-card">
                        <h3>Add Teachers</h3>
                        <p>Add teachers along with their availability and subject expertise.</p>
                        <a href="teachers" class="btn">Manage Teachers</a>
                    </div>
                    <div class="feature-card">
                        <h3>Add Rooms</h3>
                        <p>Manage classrooms and their capacity for different classes.</p>
                        <a href="rooms" class="btn">Manage Rooms</a>
                    </div>
                    <div class="feature-card">
                        <h3>Generate Timetable</h3>
                        <p>Generate optimized timetables based on your inputs and constraints.</p>
                        <a href="generate" class="btn">Generate Now</a>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2025 Automatic Timetable Generator</p>
        </footer>
    </div>

    <script src="js/script.js"></script>
</body>

</html>