<?php
$servername = "mysql-2ebb450b-joseacebuche2-654b.i.aivencloud.com";
$username = "avnadmin";
$password = "AVNS_TyqsgCbni0Iy057SyHC";
$database = "kainakap";
$port = "17284";

// Create connection
$connection = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Handle Apply button click
if (isset($_POST['apply'])) {
    $jobId = $_POST['job_id'];
    $jobQuery = "SELECT * FROM jobs WHERE id = '$jobId'";
    $jobResult = $connection->query($jobQuery);

    if ($jobResult && $jobResult->num_rows > 0) {
        $jobData = $jobResult->fetch_assoc();
        $jobTitle = $jobData['title'];
        $jobPosition = $jobData['position'];
        $jobEmail = $jobData['email'];

        $email = $_POST['email']; // Assuming email is provided in the form
        $memberQuery = "SELECT * FROM user WHERE email = '$email'";
        $memberResult = $connection->query($memberQuery);

        if ($memberResult && $memberResult->num_rows > 0) {
            $memberData = $memberResult->fetch_assoc();
            // Extract relevant data for insertion into `applicant` table
            $firstName = $memberData['first_name'];
            // ... (Other member data fields)
            $profileFile = $memberData['profile_file'];
            // ... (Other member data files)

            // Insert into `applicant` table
            $insertQuery = "INSERT INTO `applicant` 
            (`first_name`, `middle_name`, `last_name`, `email`, `phone_number`, `address`, `city`, `state`, `zip_code`, 
             `disability`, `skills`, `training_attended`, `hobbies`, `job_preference`, `disability_capability`, 
             `employment_history`, `need_assistive_device`, `sss_beneficiary`, `gsis_beneficiary`, `philhealth_beneficiary`, 
             `profile_file`, `resume_file`, `pwd_id_file`, `brgy_residence_certificate_file`, `medical_file`, 
             `medical_certificate_file`, `proof_of_disability_file`, `job_title`, `job_position`, `job_email`)
            VALUES 
            ('$firstName', '$middleName', '$lastName', '$email', '$phoneNumber', '$address', '$city', '$state', '$zipCode', 
             '$disability', '$skills', '$trainingAttended', '$hobbies', '$jobPreference', '$disabilityCapability', 
             '$employmentHistory', '$needAssistiveDevice', '$sssBeneficiary', '$gsisBeneficiary', '$philhealthBeneficiary', 
             '$profileFile', '$resumeFile', '$pwdIdFile', '$brgyResidenceCertificateFile', '$medicalFile', 
             '$medicalCertificateFile', '$proofOfDisabilityFile', '$jobTitle', '$jobPosition', '$jobEmail')";

            if ($connection->query($insertQuery) === TRUE) {
                echo "Application submitted successfully!";
            } else {
                echo "Error: " . $insertQuery . "<br>" . $connection->error;
            }
        } else {
            echo "Member data not found.";
        }
    } else {
        echo "Job data not found.";
    }
}

// Fetch posts data for sidebar
$postsData = [];
$postsQuery = "SELECT 
          user.id, 
          user.firstName, 
          user.middleName, 
          user.lastName, 
          user.suffix, 
          user.age, 
          user.birthdate, 
          user.birthplace, 
          user.gender, 
          user.height, 
          user.weight, 
          user.phone, 
          user.houseno, 
          user.street, 
          user.baranggay, 
          user.city, 
          user.province, 
          user.region, 
          user.zipcode, 
          user.religion, 
          user.citizenship, 
          user.civil, 
          user.email, 
          user.verification_status, 
          user_files.profile_photo_url, 
          user_files.resume_url, 
          user_files.pwd_id_url, 
          user_files.brgy_residence_certificate_url, 
          user_files.medical_certificate_url,
          user_files.proof_of_disability_url,
          user_files.valid_id_url
        FROM user
        JOIN user_files ON user.id = user_files.userId"; // Adjust the query as needed
$postsResult = $connection->query($postsQuery);

if ($postsResult && $postsResult->num_rows > 0) {
    $postsData = $postsResult->fetch_all(MYSQLI_ASSOC);
}

// Close connection
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KAINAKAP - Admin Panel</title>
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
  color: #333;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px; /* Optional: Add margin to the top of the table */
}

th, td {
    border: 1px solid #ddd;
    padding: 12px; /* Increased padding for better spacing */
    text-align: left;
    white-space: nowrap; /* Prevent text from wrapping */
    overflow: hidden; /* Hide overflow text */
    text-overflow: ellipsis; /* Add ellipsis (...) for overflow text */
}

th {
    background-color: #f2f2f2;
    font-weight: bold; /* Make table headers bold */
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
        <p class="navbar-brand" href="#">KAINAKAP</p>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarColor01">
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
    <?php if (!empty($postsData)): ?>
        <?php foreach ($postsData as $post): ?>
            <img src="<?php echo htmlspecialchars($post['profile_photo_url']); ?>" alt="Post Image">
            <div class="caption">
                <a style="color: #F7EFED;"><?php echo htmlspecialchars($post['email']); ?></a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts found.</p>
    <?php endif; ?>
    <input class="form-control me-sm-2" type="search" placeholder="Search">
    <a href="#"><i class="bi bi-house"></i> Home</a>
    <a href="#" class="active"><i class="bi bi-briefcase"></i> My Jobs</a>
    <a href="messages.php"><i class="bi bi-chat-left"></i> Messages</a>
    <a href="user.php"><i class="bi bi-person"></i> User</a>
    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-left"></i> Log Out</a>
</div>
<div class="content">
    <div class="container mt-5">
        <div class="row"><br><br>
        <?php
// Re-establish the connection to fetch job data
$connection = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// SQL query to fetch related jobs based on member's email
$sql = "SELECT * FROM jobs";
// Execute query
$result = $connection->query($sql);

// Check for errors in query execution
if (!$result) {
    die("Error executing query: " . $connection->error);
}

// Check if there are rows returned
if ($result->num_rows > 0) {
    // Loop through each row of data
    while ($row = $result->fetch_assoc()) {
        // Output HTML for each job card
        ?>
        <div class="col-md-4 mb-4">
            <div class="card border-primary" style="max-width: 20rem;">
                <div class="card-header"><strong><?php echo htmlspecialchars($row['title']); ?></strong></div>
                <div class="card-body">
                    <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                    <p class="card-text"><strong>Position:</strong> <?php echo htmlspecialchars($row['job_position']); ?></p>
                    <p class="card-text"><strong>Requirements:</strong> <?php echo htmlspecialchars($row['requirements']); ?></p>
                    <form method="post">
                        <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <button type="submit" class="btn btn-primary" name="apply">Apply</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo "No jobs found.";
}

// Close connection
$connection->close();
?>

        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.2.0/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        var disclaimer = document.querySelector("img[alt='www.000webhost.com']");
        if(disclaimer) {
            disclaimer.remove();
        }
    });
</script>
</body>
</html>
