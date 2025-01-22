<?php
$servername = "mysql-2ebb450b-joseacebuche2-654b.i.aivencloud.com";
$username = "avnadmin";
$password = "AVNS_TyqsgCbni0Iy057SyHC";
$database = "kainakap";
$port = "17284";

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all necessary POST variables are set
    if (isset($_POST['name'], $_POST['contact_info'], $_POST['skills'], $_POST['experience'], $_POST['qualifications'], $_POST['availability'])) {
        
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO skill_assessments (name, contact_info, skills, experience, qualifications, availability, date_submitted) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        // Bind parameters
        $stmt->bind_param("ssssss", $name, $contact_info, $skills, $experience, $qualifications, $availability);
        
        // Set parameters from POST data
        $name = $_POST['name'];
        $contact_info = $_POST['contact_info'];
        $skills = $_POST['skills'];
        $experience = $_POST['experience'];
        $qualifications = $_POST['qualifications'];
        $availability = $_POST['availability'];
        
        // Execute the statement
        if ($stmt->execute()) {
            // Success
            $message = '
            <div class="alert alert-dismissible alert-primary">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <strong>Success!</strong> Assessment Successfully Submitted.
            </div>';
        } else {
            // Failure or handle accordingly
            $message = '
            <div class="alert alert-dismissible alert-danger">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <strong>Error!</strong> <a href="#" class="alert-link">Something went wrong</a> and try submitting again.
            </div>';
            // Print SQL error
            echo "Error: " . $stmt->error;
        }
        
        // Close statement
        $stmt->close();
    } else {
        // Handle case where not all POST variables are set
        $message = '
        <div class="alert alert-dismissible alert-danger">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <strong>Error!</strong> <a href="#" class="alert-link">Please fill in all required fields.</a>
        </div>';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAINAKAP - Skill Assessment</title>
    <link rel="stylesheet" href="https://bootswatch.com/5/sandstone/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.0.1/dist/css/multi-select-tag.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.0.1/tailwind.min.css'>
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        .navbar {
            position: fixed;
            width: 100%;
            background-color: #2d7487; /* New background color */
            z-index: 1000;
            top: 0;
        }

        .side-nav {
            position: fixed;
            top: 56px; /* Height of the navbar */
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 14%;
            background-color: #374151;
            height: calc(100vh - 56px); /* Full height minus navbar */
            z-index: 999;
        }

        .side-nav a {
            color: #F7EFED;
            text-decoration: none;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: flex-start; /* Align content to the left */
            font-size: 15px;
            width: 100%; /* Ensure full width for alignment */
            padding: 0.5rem; /* Add padding for clickable area */
        }

        .side-nav input {
            color: #F7EFED;
            text-decoration: none;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: flex-start; /* Align content to the left */
            font-size: 15px;
            width: 100%; /* Ensure full width for alignment */
            padding: 0.5rem; /* Add padding for clickable area */
        }

        .content {
            margin-left: 15%; /* Width of the sidebar */
            padding: 1rem;
            flex: 1;
        }

        i {
            margin-right: 5px;
        }

        /* Default styles for navigation links */
        a {
            display: block; /* Ensures each link takes up full width */
            padding: 10px;
            text-decoration: none;
            color: #000; /* Default text color */
        }

        /* Styles for inactive links */
        a:not(.active) {
            color: #999; /* Grey out inactive links */
        }

        /* Styles for active link */
        a.active {
            color: #F7EFED; /* Active link text color */
            background-color: rgba(255, 255, 255, 0.1); /* Example background color for active link */
        }

        a:hover {
            color: #F7EFED;
            background-color: rgba(255, 255, 255, 0.1); /* Slight white background color on hover */
        }

        /* Hover effect for logout link */
        .nav-link:hover {
            color: #F7EFED;
            background-color: rgba(255, 255, 255, 0.1); /* Slight white background color on hover */
        }

        .nav-link {
            margin-top: auto; /* Push nav-links to the bottom */
        }

        img {
            width: 40%;
            display: block;
            border-radius: 100%; /* Apply rounded corners to create a circular shape */
        }

        /* Custom CSS for spacing */
        .row {
            margin-top: 20px; /* Top margin for the entire row of cards */
        }

        .col-md-4 {
            margin-bottom: 20px; /* Bottom margin for each card column */
        }

        .card {
            height: 100%; /* Ensure each card stretches to full height */
        }

        .card-body {
            height: 100%; /* Ensure card body stretches to full height */
            display: flex;
            flex-direction: column;
        }

        .card-text {
            flex-grow: 1; /* Allow card text to expand within the card body */
        }

        .btn {
            margin-top: auto; /* Push button to the bottom of card body */
        }
        p{
            display: block; /* Ensures each link takes up full width */
            padding: 10px;
            color: #F7EFED;
        }
        .profile img {
            max-width: 80px;
            height: auto;
            cursor: pointer;
        }

        .caption a {
            color: #0066cc;
            text-decoration: none;
        }

        /* Optional: Add hover effect to table rows */
        tbody tr:hover {
            background-color: #f5f5f5;
        }

        /* Optional: Style for action buttons */
        .btn {
            margin-right: 5px;
            cursor: pointer;
            padding: 6px 12px;
            font-size: 14px;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }

        .profile img {
            max-width: 100px;
            height: auto;
            cursor: pointer; /* Change cursor to pointer on hover to indicate clickability */
        }
        .caption a {
            color: #0066cc;
            text-decoration: none;
        }
        .memb {
            text-align: center; /* Center align text */
        }
        .profile img {
            max-width: 100px;
            height: auto;
            cursor: pointer; /* Change cursor to pointer on hover to indicate clickability */
        }
        .caption a {
            color: #0066cc;
            text-decoration: none;
        }
        .memb {
            text-align: center; /* Center align text */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
    <div class="container-fluid">
        <p class="navbar-brand" href="#">KAINAKAP</p>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarColor01">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Add relevant navigation items -->
            </ul>
        </div>
    </div>
</nav>
<div class="side-nav"><br>
<p><i class="bi bi-person-check"></i> Admin Panel</p>
<a href="admin.php"><i class="bi bi-house"></i> Member</a>
    <a href="skill-assessment.php" class="active"><i class="bi bi-briefcase"></i> Skill Assessment</a>
    <a href="message.php"><i class="bi bi-chat-left"></i> Messages</a>
</div>
<div class="content">
    <div class="container">
        <h2 class="mb-4">Skill Assessment Submission</h2>
        <?= $message; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="contact_info" class="form-label">Contact Information</label>
                <input type="text" class="form-control" id="contact_info" name="contact_info" required>
            </div>
            <div class="mb-3">
                <label for="skills" class="form-label">Skills</label>
                <textarea class="form-control" id="skills" name="skills" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="experience" class="form-label">Experience</label>
                <textarea class="form-control" id="experience" name="experience" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="qualifications" class="form-label">Qualifications</label>
                <textarea class="form-control" id="qualifications" name="qualifications" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="availability" class="form-label">Availability</label>
                <textarea class="form-control" id="availability" name="availability" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
