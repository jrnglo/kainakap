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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'], $_POST['member_id'])) {
      $action = $_POST['action'];
      $memberId = $_POST['member_id'];
      $reasons = isset($_POST['reason']) && is_array($_POST['reason']) ? $_POST['reason'] : [];

      // Debugging output
      echo "Action: $action<br>";
      echo "Member ID: $memberId<br>";
      echo "Reasons: ";
      print_r($reasons);  // Debugging purposes

      // Ensure memberId is a valid string (for UUID) or integer
      $memberId = trim($memberId);
      if (empty($memberId) || !preg_match('/^[a-f0-9-]{36}$/i', $memberId)) {
          echo "Invalid member ID.";
          exit();
      }

      // Validate the action
      if (in_array($action, ['verify', 'reject'])) {
          // Prepare the reasons for storage (implode if any reasons are provided)
          $reasonsList = implode(', ', $reasons);
          $status = ($action === 'verify') ? 'verified' : 'rejected';

          // Debugging output
          echo "Status: $status<br>";
          echo "Reasons List: $reasonsList<br>";

          // Update status and reason in the database
          $updateSql = "UPDATE user SET verification_status = ?, reason = ? WHERE id = ?";
          $updateStmt = $connection->prepare($updateSql);

          if ($updateStmt) {
              // Bind parameters ('sss' - string, string, string for status, reason, and memberId)
              $updateStmt->bind_param('sss', $status, $reasonsList, $memberId); 
              if ($updateStmt->execute()) {
                  header("Location: admin.php"); // Redirect after success
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
          user.reason, 
          user_files.profile_photo_url, 
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

.side-nav a, .side-nav input {
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
.modal-body {
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
    width: 100%; /* Ensure images fit within their container */
    height: auto; /* Maintain aspect ratio */
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

p {
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
    width: 80px; /* Set a fixed width for the images */
    height: 80px; /* Set a fixed height for the images */
    object-fit: cover; /* Cover the container while maintaining aspect ratio */
    display: block; /* Ensure images are block-level elements */
}
.image-thumbnail {
    width: 100px; /* Adjust as needed */
    height: 100px; /* Adjust as needed */
    object-fit: cover;
    cursor: pointer; /* Indicate that the image is clickable */
}

.caption a {
    color: #0066cc;
    text-decoration: none;
}

.memb {
    text-align: center; /* Center align text */
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
.close-btn {
      position: absolute;
      top: 20px;
      right: 20px;
      background-color: rgba(255, 255, 255, 0.7); /* Semi-transparent white */
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 20px; /* Smaller font size for a minimalist look */
      color: rgba(0, 0, 0, 0.6); /* Subtle color for the '×' */
      transition: background-color 0.3s, color 0.3s; /* Smooth transition */
    }
    .close-btn:hover {
      background-color: rgba(255, 255, 255, 1); /* Solid white on hover */
      color: black; /* Darker color for '×' on hover */
    }
    .verified {
    background-color: #d4edda; /* Light green */
}

.rejected {
    background-color: #f8d7da; /* Light red */
}

.not-verified {
    background-color: #fff3cd; /* Light yellow */
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
            </ul>
        </div>
    </div>
</nav>
<div class="side-nav"><br>
<p><i class="bi bi-person-check"></i> Admin Panel</p>
<a href="admin.php" class="active"><i class="bi bi-house"></i> Member</a>
    <a href="skill-assessment.php"><i class="bi bi-briefcase"></i> Skill Assessment</a>
</div>
<div class="content">
<div class="container mt-5">
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
            <th>Birthdate</th>
            <th>Birthplace</th>
            <th>Gender</th>
            <th>Height</th>
            <th>Weight</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Region</th>
            <th>Zip Code</th>
            <th>Religion</th>
            <th>Citizenship</th>
            <th>Civil Status</th>
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
        <?php
            $statusClass = '';
            if ($post['verification_status'] === 'verified') {
                $statusClass = 'verified';
            } elseif ($post['verification_status'] === 'rejected') {
                $statusClass = 'rejected';
            } else {
                $statusClass = 'not-verified';
            }
        ?>
        <tr class="<?php echo $statusClass; ?>">
          <td><?php echo $post['verification_status']; ?></td>
          <td><img src="<?php echo $post['profile_photo_url']; ?>" alt="Profile Image"></td>
          <td><a href="message.php?email=<?php echo urlencode($post['email']); ?>"><?php echo htmlspecialchars($post['email']); ?></a></td>
          <td><?php echo $post['firstName']; ?></td>
          <td><?php echo $post['middleName']; ?></td>
          <td><?php echo $post['lastName']; ?></td>
          <td><?php echo $post['suffix']; ?></td>
          <td><?php echo $post['age']; ?></td>
          <td><?php echo $post['birthdate']; ?></td>
          <td><?php echo $post['birthplace']; ?></td>
          <td><?php echo $post['gender']; ?></td>
          <td><?php echo $post['height']; ?></td>
          <td><?php echo $post['weight']; ?></td>
          <td><?php echo $post['phone']; ?></td>
          <td><?php echo $post['houseno'] . ' ' . $post['street'] . ', ' . $post['baranggay'] . ', ' . $post['city'] . ', ' . $post['province']; ?></td>
          <td><?php echo $post['region']; ?></td>
          <td><?php echo $post['zipcode']; ?></td>
          <td><?php echo $post['religion']; ?></td>
          <td><?php echo $post['citizenship']; ?></td>
          <td><?php echo $post['civil']; ?></td>
          <td><img src="<?php echo $post['pwd_id_url']; ?>" alt="PWD ID"></td>
          <td><img src="<?php echo $post['brgy_residence_certificate_url']; ?>" alt="Barangay Certificate"></td>
          <td><img src="<?php echo $post['medical_certificate_url']; ?>" alt="Medical Certificate"></td>
          <td><img src="<?php echo $post['proof_of_disability_url']; ?>" alt="Proof of Disability"></td>
          <td><img src="<?php echo $post['valid_id_url']; ?>" alt="Valid ID"></td>
          <td>
            <form id="verifyForm-<?php echo $post['id']; ?>" action="" method="post">
              <input type="hidden" name="member_id" value="<?php echo $post['id']; ?>">
              <input type="hidden" name="reason" id="reason-<?php echo $post['id']; ?>" value="">
              <button type="submit" name="action" value="verify" class="btn btn-info">Verify</button>
              <button type="button" class="btn btn-danger" onclick="confirmReject('<?php echo $post['id']; ?>')">Reject</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<!-- Reject Confirmation Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectModalLabel">Confirm Rejection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="rejectForm" method="post" action="">
        <div class="modal-body">
          <h5 class="modal-title">Are you sure you want to reject this member?</h5>
          <h5 class="modal-title">Select the reason for rejection:</h5>
          <div class="row">
            <!-- Column 1 -->
            <div class="col-md-4">
              <div class="form-check">
                <input type="checkbox" id="profileImage" name="reason[]" value="Profile Image" class="form-check-input">
                <label for="profileImage" class="form-check-label">Profile Image</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="email" name="reason[]" value="Email" class="form-check-input">
                <label for="email" class="form-check-label">Email</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="firstName" name="reason[]" value="First Name" class="form-check-input">
                <label for="firstName" class="form-check-label">First Name</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="middleName" name="reason[]" value="Middle Name" class="form-check-input">
                <label for="middleName" class="form-check-label">Middle Name</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="lastName" name="reason[]" value="Last Name" class="form-check-input">
                <label for="lastName" class="form-check-label">Last Name</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="suffix" name="reason[]" value="Suffix" class="form-check-input">
                <label for="suffix" class="form-check-label">Suffix</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="age" name="reason[]" value="Age" class="form-check-input">
                <label for="age" class="form-check-label">Age</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="birthdate" name="reason[]" value="Birthdate" class="form-check-input">
                <label for="birthdate" class="form-check-label">Birthdate</label>
              </div>
            </div>
            <!-- Column 2 -->
            <div class="col-md-4">
              <div class="form-check">
                <input type="checkbox" id="birthplace" name="reason[]" value="Birthplace" class="form-check-input">
                <label for="birthplace" class="form-check-label">Birthplace</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="gender" name="reason[]" value="Gender" class="form-check-input">
                <label for="gender" class="form-check-label">Gender</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="height" name="reason[]" value="Height" class="form-check-input">
                <label for="height" class="form-check-label">Height</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="weight" name="reason[]" value="Weight" class="form-check-input">
                <label for="weight" class="form-check-label">Weight</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="phone" name="reason[]" value="Phone" class="form-check-input">
                <label for="phone" class="form-check-label">Phone</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="address" name="reason[]" value="Address" class="form-check-input">
                <label for="address" class="form-check-label">Address</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="region" name="reason[]" value="Region" class="form-check-input">
                <label for="region" class="form-check-label">Region</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="zipCode" name="reason[]" value="Zip Code" class="form-check-input">
                <label for="zipCode" class="form-check-label">Zip Code</label>
              </div>
            </div>
            <!-- Column 3 -->
            <div class="col-md-4">
              <div class="form-check">
                <input type="checkbox" id="religion" name="reason[]" value="Religion" class="form-check-input">
                <label for="religion" class="form-check-label">Religion</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="citizenship" name="reason[]" value="Citizenship" class="form-check-input">
                <label for="citizenship" class="form-check-label">Citizenship</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="civilStatus" name="reason[]" value="Civil Status" class="form-check-input">
                <label for="civilStatus" class="form-check-label">Civil Status</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="pwdID" name="reason[]" value="PWD ID" class="form-check-input">
                <label for="pwdID" class="form-check-label">PWD ID</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="brgyCert" name="reason[]" value="BRGY Cert." class="form-check-input">
                <label for="brgyCert" class="form-check-label">BRGY Cert.</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="medicalCert" name="reason[]" value="Medical Cert" class="form-check-input">
                <label for="medicalCert" class="form-check-label">Medical Cert</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="proofOfDisability" name="reason[]" value="Proof of Disability" class="form-check-input">
                <label for="proofOfDisability" class="form-check-label">Proof of Disability</label>
              </div>
              <div class="form-check">
                <input type="checkbox" id="validID" name="reason[]" value="Valid ID" class="form-check-input">
                <label for="validID" class="form-check-label">Valid ID</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" id="confirmRejectButton">Reject</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function confirmReject(memberId) {
    // Set the member ID in the modal form
    document.getElementById('modalMemberId').value = memberId;
    // Show the modal
    var rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    rejectModal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script>
let rejectMemberId = null;

function confirmReject(memberId) {
  rejectMemberId = memberId;
  document.querySelectorAll('.btn-secondary').forEach(btn => btn.classList.remove('btn-primary'));
  var rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
  rejectModal.show();
}

document.getElementById('confirmRejectButton').addEventListener('click', function () {
  if (rejectMemberId) {
    const form = document.getElementById('rejectForm'); // Ensure this is the correct form ID
    const memberIdInput = document.createElement('input');
    memberIdInput.type = 'hidden';
    memberIdInput.name = 'member_id';
    memberIdInput.value = rejectMemberId;
    form.appendChild(memberIdInput);
    form.submit();
  }
});
</script>
</div>
</body>
</html>
