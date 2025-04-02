# Automatic Timetable Generator - User Manual

## Table of Contents
1. [Introduction](#introduction)
2. [System Requirements](#system-requirements)
3. [Installation](#installation)
4. [Getting Started](#getting-started)
5. [Managing Courses](#managing-courses)
6. [Managing Teachers](#managing-teachers)
7. [Managing Rooms](#managing-rooms)
8. [Generating Timetables](#generating-timetables)
9. [Viewing and Exporting Timetables](#viewing-and-exporting-timetables)
10. [Troubleshooting](#troubleshooting)
11. [FAQ](#faq)

## Introduction
The Automatic Timetable Generator is a web-based application designed to create conflict-free timetables for educational institutions. It takes into account teacher availability, room capacity, and course requirements to automatically generate optimal schedules.

This system helps educational administrators save time and eliminate scheduling conflicts by automating the complex task of timetable creation.

## System Requirements
- Web server with PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, or Edge recommended)
- Minimum 1GB of server RAM
- 100MB of disk space

## Installation
1. **Database Setup**:
   - Create a new MySQL database named `timetable_generator`
   - Import the included `database.sql` file:
     ```
     mysql -u username -p timetable_generator < database.sql
     ```

2. **File Configuration**:
   - Copy all project files to your web server's document root or a subdirectory
   - Edit `includes/config.php` to set your database connection details:
     ```php
     $db_host = 'localhost'; // Change if your database is on a different server
     $db_user = 'root';      // Your database username
     $db_pass = '';          // Your database password
     $db_name = 'timetable_generator';
     ```

3. **Permissions**:
   - Ensure the web server has read and write permissions to all project directories
   - Recommended: Set 755 permissions for directories and 644 for files

4. **Access the Application**:
   - Open your web browser and navigate to the URL where you installed the application
   - Default admin credentials:
     - Username: admin
     - Password: admin123

## Getting Started
After installation, you'll need to set up your data before generating timetables:

1. First, add teachers with their availability
2. Next, add classrooms/rooms with their capacity
3. Then, add courses and assign them to teachers
4. Finally, generate your timetable

## Managing Courses
Courses represent the subjects taught at your institution. Each course has a name, code, credit hours, and an assigned teacher.

### Adding a Course
1. Navigate to the "Courses" tab in the main menu
2. Fill in the required information:
   - **Course Name**: The full name of the course (e.g., "Introduction to Computer Science")
   - **Course Code**: A unique identifier for the course (e.g., "CS101")
   - **Credit Hours**: The number of hours per week (1-6) the course meets
   - **Teacher**: Select from the dropdown list of available teachers
3. Click "Add Course" to save

### Editing a Course
1. In the course list, find the course you want to edit
2. Click the "Edit" button in the Actions column
3. Modify the course details as needed
4. Click "Update Course" to save changes

### Deleting a Course
1. In the course list, find the course you want to remove
2. Click the "Delete" button in the Actions column
3. Confirm deletion when prompted

**Note**: You cannot delete a course if it's already used in an existing timetable.

## Managing Teachers
Teachers are the instructors who conduct courses. You can set their availability for different days and time slots.

### Adding a Teacher
1. Navigate to the "Teachers" tab in the main menu
2. Fill in the required information:
   - **Teacher Name**: Full name of the teacher
   - **Email**: Valid email address for contact
   - **Phone**: Optional contact number
   - **Availability**: Check boxes for times when the teacher is available to teach
3. Click "Add Teacher" to save

### Setting Teacher Availability
The availability grid allows you to specify exactly when a teacher can conduct classes:
- Each row represents a time slot
- Each column represents a day of the week (Monday-Friday)
- Check the boxes for times when the teacher is available
- Leave boxes unchecked for times when the teacher is unavailable

### Editing a Teacher
1. In the teacher list, find the teacher you want to edit
2. Click the "Edit" button in the Actions column
3. Modify the teacher details and availability as needed
4. Click "Update Teacher" to save changes

### Deleting a Teacher
1. In the teacher list, find the teacher you want to remove
2. Click the "Delete" button in the Actions column
3. Confirm deletion when prompted

**Note**: You cannot delete a teacher if they are assigned to a course.

## Managing Rooms
Rooms represent the physical spaces where classes are conducted. Each room has a capacity and type.

### Adding a Room
1. Navigate to the "Rooms" tab in the main menu
2. Fill in the required information:
   - **Room Number**: A unique identifier for the room (e.g., "A101")
   - **Capacity**: The maximum number of students the room can accommodate
   - **Room Type**: Select from the dropdown (Lecture Hall, Lab, Classroom, Seminar Room)
3. Click "Add Room" to save

### Editing a Room
1. In the room list, find the room you want to edit
2. Click the "Edit" button in the Actions column
3. Modify the room details as needed
4. Click "Update Room" to save changes

### Deleting a Room
1. In the room list, find the room you want to remove
2. Click the "Delete" button in the Actions column
3. Confirm deletion when prompted

**Note**: You cannot delete a room if it's already used in an existing timetable.

## Generating Timetables
The timetable generation process automatically creates a schedule based on the data you've entered.

### Creating a New Timetable
1. Navigate to the "Generate Timetable" tab in the main menu
2. Enter a name for your timetable (e.g., "Fall Semester 2025")
3. Click "Generate Timetable"
4. The system will process and redirect you to the view page when complete

### How the Algorithm Works
The timetable generator uses a constraint-based algorithm that:
1. Prioritizes courses with more credit hours
2. Respects teacher availability
3. Avoids room conflicts
4. Distributes courses evenly across the week when possible
5. Ensures no teacher is scheduled for two courses at the same time

If there are constraints that cannot be satisfied (e.g., not enough available time slots for all courses), the system will generate a partial timetable and show warnings about unallocated courses.

## Viewing and Exporting Timetables
After generation, you can view, print, and export your timetables.

### Viewing a Timetable
1. Navigate to the "View Timetable" tab in the main menu
2. Select a timetable from the dropdown list
3. The timetable will display as a grid showing all scheduled courses

### Understanding the Timetable Display
- **Rows**: Time slots (8:00 AM - 4:00 PM)
- **Columns**: Days of the week (Monday-Friday)
- **Each cell** contains:
  - Course name
  - Course code
  - Teacher name
  - Room number

### Printing a Timetable
1. While viewing a timetable, click the "Print" button
2. A printer-friendly version will open in a new window
3. Use your browser's print function or the automatic print dialog

### Exporting a Timetable
1. While viewing a timetable, click the "Export" button
2. The timetable will be downloaded as a CSV file
3. This file can be opened in spreadsheet applications like Microsoft Excel or Google Sheets

### Deleting a Timetable
1. In the "Generate Timetable" page, find the timetable in the list
2. Click the "Delete" button
3. Confirm deletion when prompted

## Troubleshooting

### Common Issues and Solutions

#### Database Connection Errors
- Verify database credentials in `includes/config.php`
- Ensure MySQL service is running
- Check if the database exists and has the correct tables

#### Missing or Incomplete Timetables
- Ensure you have added enough rooms to accommodate all courses
- Check teacher availability to make sure there are enough time slots
- Verify that courses have valid credit hours

#### Unallocated Courses
If you see warnings about unallocated courses after generation:
- Review teacher availability and increase available time slots
- Add more rooms if room conflicts are occurring
- Reduce credit hours for some courses if the total exceeds available slots

#### Page Not Found Errors
- Ensure all files are uploaded to the correct directory
- Check file permissions
- Verify that your web server supports PHP

## FAQ

### Q: How many courses can the system handle?
A: The system can theoretically handle unlimited courses, but performance may decrease with very large numbers (500+). For optimal performance, we recommend keeping it under 200 courses per timetable.

### Q: Can I specify which room to use for a specific course?
A: The current version automatically assigns rooms based on availability. Future versions may include manual room assignments.

### Q: Can I manually edit a generated timetable?
A: The current version does not support manual editing after generation. If you need changes, adjust your courses, teachers, or rooms, and generate a new timetable.

### Q: How does the system handle lunch breaks?
A: The system does not automatically schedule lunch breaks. You can reserve this time by making all teachers unavailable during your preferred lunch hour.

### Q: Can I generate timetables for different departments separately?
A: Yes, simply create separate timetables with different names for each department.

### Q: What should I do if a teacher's availability changes mid-semester?
A: Update the teacher's availability and generate a new timetable. Be aware this may significantly change the schedule.

---

© 2025 Automatic Timetable Generator | All Rights Reserved