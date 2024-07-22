<?php
session_start();

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

// Function to securely hash passwords
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Process sign-up form submission
if (isset($_POST['signup'])) {
    $company_name = $_POST['company_name'];
    $agency_name = $_POST['agency_name'];
    $email = $_POST['email'];
    $password = hashPassword($_POST['password']);

    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO companies (company_name, agency_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $company_name, $agency_name, $email, $password);

    if ($stmt->execute()) {
        header("Location: index.php?signup=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Process login form submission
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare statement to check users table
    $stmt_users = $conn->prepare("SELECT * FROM companies WHERE email=?");
    $stmt_users->bind_param("s", $email);
    $stmt_users->execute();
    $user_result = $stmt_users->get_result();

    if ($user_result->num_rows == 1) {
        // Fetch company details
        $company = $user_result->fetch_assoc();
        $hashed_password = $company['password'];

        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, set session variables
            $_SESSION['company_name'] = $company['company_name'];
            $_SESSION['agency_name'] = $company['agency_name'];
            $_SESSION['email'] = $email;
            $_SESSION['logged_in'] = true; // Optional: Use for additional checks
            
            // Redirect to admin dashboard or wherever needed
            header("Location: admin.php");
            exit();
        } else {
            // Invalid password
            $_SESSION['error'] = "Invalid email or password";
            header("Location: admin.php"); // Redirect back to login form
            exit();
        }
    } else {
        // Invalid email
        $_SESSION['error'] = "Invalid email or password";
        header("Location: admin.php"); // Redirect back to login form
        exit();
    }

    $stmt_users->close();
}

// Close database connection
$conn->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KAINAKAP - Admin Panel</title>
  <link href='https://fonts.googleapis.com/css?family=Titillium+Web:400,300,600' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
  <link rel="stylesheet" href="./login/style.css">
  <style>
    /* Hide the forgot password form initially */
    #forgot-password {
      display: none;
      transition: .5s ease;
    }
  </style>
</head>
<body>
<div class="form">
  <ul class="tab-group">
    <h1>ADMIN PANEL</h1>
    <li class="tab active"><a href="#signup">Sign Up</a></li>
    <li class="tab"><a href="#login">Log In</a></li>
  </ul>
  
  <div class="tab-content">
    <div id="signup">   
      <h1>Admin Sign Up</h1>
      
      <form action="" method="post">
      
        <div class="field-wrap">
          <label for="company_name">Company Name</label>
          <input type="text" name="company_name" id="company_name" required autocomplete="off" />
        </div>
        
        <div class="field-wrap">
          <label for="agency_name">Agency Name</label>
          <input type="text" name="agency_name" id="agency_name" required autocomplete="off" />
        </div>

        <div class="field-wrap">
          <label for="signup-email">Company Email</label>
          <input type="email" name="email" id="signup-email" required autocomplete="off"/>
        </div>
        
        <div class="field-wrap">
          <label for="signup-password">Password</label>
          <input type="password" name="password" id="signup-password" required autocomplete="off"/>
        </div>
        
        <button type="submit" name="signup" class="button button-block">Get Started</button>
        
      </form>
    </div>
    
    <div id="login">   
      <h1>Admin Log In</h1>
      
      <form action="" method="post">
      
        <div class="field-wrap">
          <label for="login-email">Company Email</label>
          <input type="email" name="email" id="login-email" required autocomplete="off"/>
        </div>
        
        <div class="field-wrap">
          <label for="login-password">Password</label>
          <input type="password" name="password" id="login-password" required autocomplete="off"/>
        </div>
        
        <p class="forgot"><a href="#" id="forgot-password-link">Forgot Password?</a></p>
        
        <button type="submit" name="login" class="button button-block">Log In</button>
        
      </form>
    </div>
  </div><!-- tab-content -->
</div> <!-- /form -->

<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src="./login/script.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const forgotPasswordLink = document.getElementById('forgot-password-link');
    const loginForm = document.getElementById('login');
    const forgotPasswordForm = document.getElementById('forgot-password');
    
    forgotPasswordLink.addEventListener('click', (e) => {
      e.preventDefault();
      loginForm.style.display = 'none';
      forgotPasswordForm.style.display = 'block';
    });
  });
</script>
</body>
</html>
