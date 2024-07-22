<?php
session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['member_id'])) {
        $action = $_POST['action'];
        $memberId = $_POST['member_id'];

        // Validate the action
        if (in_array($action, ['verify', 'reject'])) {
            $status = ($action === 'verify') ? 'verified' : 'rejected';

            // Update status in the database
            $updateSql = "UPDATE user SET verification_status = ? WHERE id = ?";
            $updateStmt = $connection->prepare($updateSql);
            if ($updateStmt) {
                $updateStmt->bind_param('ss', $status, $memberId); // Changed 'si' to 'ss'
                if ($updateStmt->execute()) {
                    header("location:admin.php");
                    exit();
                } else {
                    echo "Error updating status: " . $updateStmt->error;
                }
                $updateStmt->close();
            } else {
                echo "Error preparing statement: " . $connection->error;
            }
        } else {
            echo "Invalid action.";
        }
    } else {
        echo "Action or member_id not set.";
    }
}

// Query to fetch data
$sql = "SELECT 
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
        JOIN user_files ON user.id = user_files.userId";


$stmt = $connection->prepare($sql);

// Check if the statement was prepared successfully
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($connection->error));
}

// Execute the statement
$stmt->execute();

// Get the result set from the executed statement
$result = $stmt->get_result();

// Check for query execution errors
if ($result === false) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
}

// Fetch data from the result set
$postsData = $result->fetch_all(MYSQLI_ASSOC);

// Close the statement and connection
$stmt->close();
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
  color: #F7EFED;
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
<p><i class="bi bi-person-check"></i> Admin Panel</p>
    <a href="admin.php" class="active"><i class="bi bi-house"></i> Member</a>
    <a href="job-offer.php"><i class="bi bi-briefcase"></i> Jobs</a>
    <a href="message.php"><i class="bi bi-chat-left"></i> Messages</a>
</div>
<div class="content">
<div class="container mt-5">
  <div class="row">
  <table id="memberTable">
        <thead>
          <tr>
            <th>Status</th>
            <th>Profile Image</th>
            <th>Email</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Suffix</th>
            <th>Age</th>
            <th>Brithday</th>
            <th>Birthplace</th>
            <th>Gender</th>
            <th>Height</th>
            <th>Weight</th>
            <th>Phone</th>
            <th>House No.</th>
            <th>Street</th>
            <th>Baranggay</th>
            <th>City</th>
            <th>Province</th>
            <th>Region</th>
            <th>Zip Code</th>
            <th>Religion</th>
            <th>Citizenship</th>
            <th>Civil</th>
            <th>PWD ID</th>
            <th>BRGY Cert.</th>
            <th>Medical Cert</th>
            <th>Proof of Disability</th>
            <th>Valid ID</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
            <?php foreach ($postsData as $post): ?>
                <tr>
  <td class="caption">
    <a><?php echo $post['verification_status']; ?></a>
  </td>
  <td class="profile">
    <img src="<?php echo $post['profile_photo_url']; ?>" alt="Profile Image">
  </td>
  <td class="caption">
    <a href="message.php?email=<?php echo urlencode($post['email']); ?>">
      <?php echo htmlspecialchars($post['email']); ?>
    </a>
  </td>
  <td class="caption">
    <a><?php echo $post['firstName']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['middleName']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['lastName']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['suffix']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['age']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['birthdate']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['birthplace']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['gender']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['height']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['weight']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['phone']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['houseno']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['street']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['baranggay']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['city']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['province']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['region']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['zipcode']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['religion']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['citizenship']; ?></a>
  </td>
  <td class="caption">
    <a><?php echo $post['civil']; ?></a>
  </td>
  <td class="profile">
    <img src="<?php echo $post['pwd_id_url']; ?>" alt="PWD ID" onclick="showFullScreen('<?php echo $post['pwd_id_url']; ?>')">
  </td>
  <td class="profile">
    <img src="<?php echo $post['brgy_residence_certificate_url']; ?>" alt="BRGY Cert." onclick="showFullScreen('<?php echo $post['brgy_residence_certificate_url']; ?>')">
  </td>
  <td class="profile">
    <img src="<?php echo $post['medical_certificate_url']; ?>" alt="Medical Cert" onclick="showFullScreen('<?php echo $post['medical_certificate_url']; ?>')">
  </td>
  <td class="profile">
    <img src="<?php echo $post['proof_of_disability_url']; ?>" alt="Medical Cert" onclick="showFullScreen('<?php echo $post['proof_of_disability_url']; ?>')">
  </td>
  <td class="profile">
    <img src="<?php echo $post['valid_id_url']; ?>" alt="Medical Cert" onclick="showFullScreen('<?php echo $post['valid_id_url']; ?>')">
  </td>
  <td>
    <form action="" method="post" style="display: inline;">
      <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($post['id']); ?>">
      <button type="submit" name="action" value="verify" class="btn btn-success">Verify</button>
    </form>
    <form action="" method="post" style="display: inline;">
      <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($post['id']); ?>">
      <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
    </form>
  </td>
</tr>

            <?php endforeach; ?>
        </tbody>
      </table>
    <div class="overlay" onclick="closeFullScreen(event)">
        <img id="fullScreenImg" src="" alt="Full Screen Image">
    </div>
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
    <script>
        function showFullScreen(imageSrc) {
            var fullScreenImg = document.getElementById('fullScreenImg');
            fullScreenImg.src = imageSrc;
            document.querySelector('.overlay').style.display = 'flex';
        }

        function closeFullScreen(event) {
            if (event.target.classList.contains('overlay')) {
                document.querySelector('.overlay').style.display = 'none';
            }
        }
    </script>
<script>
    document.getElementById("searchInput").addEventListener("input", function() {
        let keyword = this.value.trim().toLowerCase(); // Get search keyword

        let rows = document.getElementById("memberTable").getElementsByTagName("tbody")[0].rows; // Get all table rows

        // Loop through each row
        for (let row of rows) {
            let cells = row.getElementsByClassName("caption"); // Get all <td class="caption"> cells in this row
            let showRow = false; // Flag to determine if row should be shown

            // Loop through each caption cell
            for (let cell of cells) {
                let text = cell.innerText.trim().toLowerCase(); // Get text content of the cell

                // Check if keyword is found in the cell content
                if (text.includes(keyword)) {
                    showRow = true;
                    break; // Exit loop if keyword is found
                }
            }

            // Show or hide row based on keyword match
            if (showRow) {
                row.style.display = ""; // Show row
            } else {
                row.style.display = "none"; // Hide row
            }
        }
    });
</script>
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
</body>
</html>
