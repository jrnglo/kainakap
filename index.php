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
:root {
  --primary-color: #2d7487;
  --secondary-color: #374151;
  --background-color: #F8F9FA;
  --text-light: #F7EFED;
  --verified-color: #2E7D32;
  --rejected-color: #CC0000;
  --pending-color: #0055B8;
}

body {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  margin: 0;
  background-color: var(--background-color);
  font-family: 'Segoe UI', system-ui, sans-serif;
}

.navbar {
  position: fixed;
  width: 100%;
  background-color: var(--primary-color);
  z-index: 1000;
  top: 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.side-nav {
  position: fixed;
  top: 56px;
  left: 0;
  display: flex;
  flex-direction: column;
  width: 10%;
  min-width: 200px;
  background-color: var(--secondary-color);
  height: calc(100vh - 56px);
  z-index: 999;
  padding: 1rem 0;
  margin: 0;
  box-sizing: border-box;
  gap: 0.5rem; /* Space between nav items */
}

.side-nav a, 
.side-nav input {
  color: var(--text-light);
  padding: 0.75rem 1.5rem;
  margin: 0.25rem 0;
  transition: all 0.2s ease;
}

.side-nav a.active {
  background-color: rgba(255, 255, 255, 0.1);
  border-left: 4px solid var(--primary-color);
}

.side-nav a:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

/* Changed content margin and padding */
.content {
  margin-left: 10%;  /* Reduced from 15% to match sidebar width */
  padding: 2rem 2rem 2rem 0.5rem; /* Further reduced left padding */
  flex: 1;
  box-sizing: border-box;
}

/* Added table container for better control */
.table-container {
  width: 105%;
  margin-right: 1rem;
  overflow-y: auto;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Adjusted table alignment */
table {
  width: calc(100% - 1rem); /* Account for container margin */
  margin-left: 0;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* First column alignment fix */
td:first-child,
th:first-child {
  padding-left: 15px !important;
}

/* Last column spacing */
td:last-child,
th:last-child {
  padding-right: 20px;
}

th, td {
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

td img {
    width: 100px !important;       /* Fixed width */
    height: 100px !important;      /* Fixed height */
    object-fit: cover;             /* Crop to fit */
    object-position: center;       /* Center the image */
    margin: 0 auto;               /* Center horizontally */
    display: block;               /* Remove inline spacing */
}

.status {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
}

.verified { 
  background-color: #e8f5e9;
  color: var(--verified-color);
}

.rejected {
  background-color: #ffebee;
  color: var(--rejected-color);
}

.not-verified {
  background-color: #e3f2fd;
  color: var(--pending-color);
}

.modal-content {
  border-radius: 12px;
}

.modal-header {
  border-bottom: none;
  padding: 1.5rem;
}

.modal-body {
  padding: 1.5rem;
}

/* Legend Styles */
.status-legend {
  font-size: 0.9rem;
}

.color-dot {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  margin-right: 6px;
  vertical-align: middle;
}

.color-dot.verified { background-color: #2E7D32; }
.color-dot.rejected { background-color: #CC0000; }
.color-dot.not-verified { background-color: #0055B8; }

.legend-item {
  display: flex;
  align-items: center;
  gap: 5px;
}
#rejectionReasonText ul {
  list-style-type: disc;
  padding-left: 20px;
  margin: 0;
}

#rejectionReasonText li {
  margin-bottom: 8px;
  line-height: 1.5;
  color:rgb(0, 0, 0);
}
.verified { color: #2E7D32; }
.rejected { color: #CC0000; }
.not-verified { color: #0055B8; }
.color-dot {
  width: 16px;
  height: 16px;
  margin-right: 0.5rem;
}

.overlay img {
  max-width: 80%;
  max-height: 80vh;
  border-radius: 8px;
}

@media (max-width: 992px) {
  .side-nav {
    width: 100%;
    height: auto;
    position: relative;
    top: 0;
  }
  
  .content {
    margin-left: 0;
    padding: 1rem;
  }
  
  table {
    display: block;
    overflow-x: auto;
  }
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
  <div class="table-container">
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
<td>
  <?php 
  // Define rejection messages
  $reason_messages = [
    'Profile Image' => 'Profile Image is invalid or doesn\'t match requirements',
    'Email' => 'Email address is invalid or doesn\'t match our records',
    'First Name' => 'First name contains invalid characters or doesn\'t match documentation',
    'Middle Name' => 'Middle name contains discrepancies',
    'Last Name' => 'Last name doesn\'t match supporting documents',
    'Suffix' => 'Suffix is incorrect or mismatched',
    'Age' => 'Age verification failed or doesn\'t match birthdate',
    'Birthdate' => 'Birthdate is invalid or inconsistent with other records',
    'Birthplace' => 'Birthplace information is incomplete or mismatched',
    'Gender' => 'Gender information is inconsistent',
    'Height' => 'Height measurement is invalid or implausible',
    'Weight' => 'Weight information is inconsistent',
    'Phone' => 'Phone number verification failed',
    'Address' => 'Address validation failed or contains inconsistencies',
    'Region' => 'Region information doesn\'t match location data',
    'Zip Code' => 'Zip code is invalid for the provided address',
    'Religion' => 'Religious affiliation contains discrepancies',
    'Citizenship' => 'Citizenship documentation is invalid',
    'Civil Status' => 'Civil status verification failed',
    'PWD ID' => 'PWD ID is expired or invalid',
    'BRGY Cert.' => 'Barangay certificate is incomplete or invalid',
    'Medical Cert' => 'Medical certificate is expired or insufficient',
    'Proof of Disability' => 'Disability proof documentation is inadequate',
    'Valid ID' => 'Government ID is expired or unreadable'
  ];
  
if($post['verification_status'] === 'rejected' && !empty($post['reason'])): 
    $reasons = explode(', ', $post['reason']);
    $display_reasons = [];
    
    foreach($reasons as $reason) {
      $display_reasons[] = $reason_messages[trim($reason)] ?? $reason . ' is invalid';
    }
  ?>
    <span class="rejection-reason" 
          style="cursor: pointer; color: #cc0000;"
          data-reason="<?php echo htmlspecialchars(implode('||', $display_reasons)) ?>">
      <?php echo $post['verification_status']; ?>
    </span>
  <?php else: ?>
    <?php echo $post['verification_status']; ?>
  <?php endif; ?>
</td>
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
        <!-- Added hidden action field -->
        <input type="hidden" name="action" value="reject">
        
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
<!-- Reason Modal -->
<div class="modal fade" id="reasonModal" tabindex="-1" aria-labelledby="reasonModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reasonModalLabel">Rejection Reasons</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="rejectionReasonText"></p>
        
        <!-- Status Legend -->
        <div class="status-legend mt-4 border-top pt-3">
          <h6 class="mb-2">Status Colors:</h6>
          <div class="d-flex gap-3">
            <div class="legend-item">
              <span class="color-dot verified"></span>
              Verified
            </div>
            <div class="legend-item">
              <span class="color-dot rejected"></span>
              Rejected
            </div>
            <div class="legend-item">
              <span class="color-dot not-verified"></span>
              Pending Verification
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
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

document.querySelectorAll('.rejection-reason').forEach(element => {
  element.addEventListener('click', () => {
    const reasons = element.dataset.reason.split('||');
    const modalBody = document.getElementById('rejectionReasonText');
    
    // Clear previous content
    modalBody.innerHTML = '';
    
    // Create list elements
    const list = document.createElement('ul');
    reasons.forEach(reason => {
      const li = document.createElement('li');
      li.textContent = reason;
      li.style.marginBottom = '8px';
      list.appendChild(li);
    });
    
    modalBody.appendChild(list);
    new bootstrap.Modal(document.getElementById('reasonModal')).show();
  });
});

</script>
</div>
</body>
</html>
