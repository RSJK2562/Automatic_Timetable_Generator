<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <li><a href="index" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="courses" class="<?php echo basename($_SERVER['PHP_SELF']) == 'courses' ? 'active' : ''; ?>">Courses</a></li>
                    <li><a href="teachers" class="<?php echo basename($_SERVER['PHP_SELF']) == 'teachers' ? 'active' : ''; ?>">Teachers</a></li>
                    <li><a href="rooms" class="<?php echo basename($_SERVER['PHP_SELF']) == 'rooms' ? 'active' : ''; ?>">Rooms</a></li>
                    <li><a href="generate" class="<?php echo basename($_SERVER['PHP_SELF']) == 'generate' ? 'active' : ''; ?>">Generate Timetable</a></li>
                    <li><a href="view" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view' ? 'active' : ''; ?>">View Timetable</a></li>
                </ul>
            </nav>
        </header>