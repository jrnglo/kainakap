<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "kainakap";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all necessary POST variables are set
    if(isset($_POST['title'], $_POST['description'], $_POST['requirements'], $_POST['position'], $_POST['preference'])) {
        
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO jobs (title,company_name, description, requirements, position, preference, date_posted) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        // Bind parameters
        $stmt->bind_param("sssss", $title,$company_name, $description, $requirements, $position, $preference);
        
        // Set parameters from POST data
        $title = $_POST['title'];
        $company_name = $_POST['company_name'];
        $description = $_POST['description'];
        $requirements = $_POST['requirements'];
        $position = $_POST['position'];
        $preference = $_POST['preference'];
        
        // Execute the statement
        if ($stmt->execute()) {
            // Success
            $message = '
            <div class="alert alert-dismissible alert-primary">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <strong>Success!</strong> Message Successfully Sent.
            </div>';
        } else {
            // Failure or handle accordingly
            $message = '
            <div class="alert alert-dismissible alert-danger">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <strong>Error!</strong> <a href="#" class="alert-link">Something went wrong</a> and try submitting again.
            </div>';
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
    <title>KAINAKAP - Job Posting</title>
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
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .overlay img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }
  </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="container-fluid">
            <p class="navbar-brand">KAINAKAP</p>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                    </li>
                    <li class="nav-item">
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="side-nav"><br>
    <p><i class="bi bi-person-check"></i> Admin Panel</p>
        <a href="admin.php"><i class="bi bi-house"></i> Member</a>
        <a href="job-offer.php" class="active"><i class="bi bi-briefcase"></i> Jobs</a>
        <a href="message.php"><i class="bi bi-chat-left"></i> Messages</a>
    </div>
    <div class="content">
        <div class="container mt-5">
            <div class="row">
                <table id="memberTable">
                    <thead>
                        <tr>
                            <th colspan="2" class="memb">Job Information</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <form id="progress-form" class="p-4 progress-form" action="" method="post" lang="en" novalidate enctype="multipart/form-data">
                <div class="form__field">

<div class="mt-3 form__field">
<label for="job-title">
    Title
    <span data-required="true" aria-hidden="true">*</span>
  </label>
  <input class="form-control" id="job-title" type="text" name="title" required>
</div>

<div class="mt-3 form__field">
<label for="company-name">
    Company Name
    <span data-required="true" aria-hidden="true">*</span>
  </label>
  <input class="form-control" id="company-name" type="text" name="company-name" required>
</div>

<div class="mt-3 form__field">
  <label for="job-description">
    Description
    <span data-required="true" aria-hidden="true">*</span>
  </label>
  <textarea class="form-control" id="job-description" name="description" rows="4" required></textarea>
</div>

<div class="mt-3 form__field">
  <label for="job-requirements">
    Requirements
    <span data-required="true" aria-hidden="true">*</span>
  </label>
  <textarea class="form-control" id="job-requirements" name="requirements" rows="4" required></textarea>
</div>

<div class="mt-3 form__field">
  <label for="job-position">
    Position
    <span data-required="true" aria-hidden="true">*</span>
  </label>
  <input class="form-control" id="job-position" type="text" name="position" required>
</div>

<div class="mt-3 form__field">
  <label for="disability">
    Type of Disability
    <span data-required="true" aria-hidden="true">*</span>
  </label>
  <select class="form-control" id="disability" name="disability[]" multiple required>
    <option value="Blindness">Blindness</option>
    <option value="Low Vision">Low Vision</option>
    <option value="Deafness">Deafness</option>
    <option value="Hearing Impairment">Hearing Impairment</option>
    <option value="Mobility Impairment">Mobility Impairment</option>
    <option value="Wheelchair Use">Wheelchair Use</option>
    <option value="Cerebral Palsy">Cerebral Palsy</option>
    <option value="Muscular Dystrophy">Muscular Dystrophy</option>
    <option value="Amputation">Amputation</option>
    <option value="Spinal Cord Injury">Spinal Cord Injury</option>
    <option value="Paralysis">Paralysis</option>
    <option value="Multiple Sclerosis">Multiple Sclerosis</option>
    <option value="Parkinson's Disease">Parkinson's Disease</option>
    <option value="Autism Spectrum Disorder">Autism Spectrum Disorder</option>
    <option value="Down Syndrome">Down Syndrome</option>
    <option value="Intellectual Disability">Intellectual Disability</option>
    <option value="Cognitive Impairment">Cognitive Impairment</option>
    <option value="Dyslexia">Dyslexia</option>
    <option value="Attention Deficit Hyperactivity Disorder (ADHD)">Attention Deficit Hyperactivity Disorder (ADHD)</option>
    <option value="Post-Traumatic Stress Disorder (PTSD)">Post-Traumatic Stress Disorder (PTSD)</option>
  </select>
</div>

<div class="mt-3 form__field">
  <label for="job-preference">
    Preference
    <span data-required="true" aria-hidden="true">*</span>
  </label>
  <input class="form-control" id="job-preference" type="text" name="preference" required>
</div>
                    <div class="d-flex flex-column-reverse flex-sm-row align-items-center justify-content-center justify-content-sm-end mt-4 mt-sm-5">
                        <button type="submit" class="btn btn-primary" value="Insert Job">Insert</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.0.1/dist/js/multi-select-tag.js"></script>
    <script src="./script.js"></script>
    <script>
    new MultiSelectTag('disability')
</script>
        <!-- / End Progress Form -->
    </div>
    <!-- partial -->
</body>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&display=swap');

:root {

  --light-blue-100: 199, 84%, 55%; /* #2bb0ed */
  --light-blue-500: 202, 83%, 41%; /* #127fbf */
  --light-blue-900: 204, 96%, 27%; /* #035388 */

  --blue-100:       210, 22%, 49%; /* #627d98 */
  --blue-500:       209, 34%, 30%; /* #334e68 */
  --blue-900:       209, 61%, 16%; /* #102a43 */

  --gray-100:       210, 36%, 96%; /* #f0f4F8 */
  --gray-300:       212, 33%, 89%; /* #d9e2ec */
  --gray-500:       210, 31%, 80%; /* #bcccdc */
  --gray-700:       211, 27%, 70%; /* #9fb3c8 */
  --gray-900:       209, 23%, 60%; /* #829ab1 */

  --white:          0, 0%, 100%;   /* #ffffff */

  --font-family-sans-serif: "Montserrat", sans-serif;

  --space-multiplier:  0.8;

  --content-max-width: 140rem;

  --grid-spacer-width: 1.5rem;
  --grid-column-count: 12;

}
  body {
  background-color: hsl(var(--gray-100));
  color: hsl(var(--blue-900));
}
        .card { 
        border: 1px solid #ccc;
        padding: 10px;
        margin: 0 auto;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width:80%;
    }
    .custom-file-upload-hidden {
  display: none;
  visibility: hidden;
  position: absolute;
  left: -9999px;
}

.custom-file-upload {
  display: block;
  width: auto;
  font-size: 16px;
  margin-top: 30px;
}
.custom-file-upload label {
  display: block;
  margin-bottom: 5px;
}

.file-upload-wrapper {
  position: relative;
  margin-bottom: 5px;
}

.file-upload-input {
  width: 300px;
  color: #fff;
  font-size: 16px;
  padding: 11px 17px;
  border: none;
  background-color: #c0392b;
  -moz-transition: all 0.2s ease-in;
  -o-transition: all 0.2s ease-in;
  -webkit-transition: all 0.2s ease-in;
  transition: all 0.2s ease-in;
  float: left;
  /* IE 9 Fix */
}
.file-upload-input:hover, .file-upload-input:focus {
  background-color: #ab3326;
  outline: none;
}

.file-upload-button {
  cursor: pointer;
  display: inline-block;
  color: #fff;
  font-size: 16px;
  text-transform: uppercase;
  padding: 11px 20px;
  border: none;
  margin-left: -1px;
  background-color: #962d22;
  -moz-transition: all 0.2s ease-in;
  -o-transition: all 0.2s ease-in;
  -webkit-transition: all 0.2s ease-in;
  transition: all 0.2s ease-in;
}
.file-upload-button:hover {
  background-color: #6d2018;
}
</style>
<script>
        document.addEventListener('DOMContentLoaded', () => {
            var disclaimer = document.querySelector("img[alt='www.000webhost.com']");
            if(disclaimer) {
                disclaimer.remove();
            }
        });
    </script>
</html>
