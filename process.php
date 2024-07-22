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

// Function to securely hash passwords
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Process sign-up form submission
if (isset($_POST['signup'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = hashPassword($_POST['password']);

    $sql = "INSERT INTO users (first_name, last_name, email, password)
            VALUES ('$first_name', '$last_name', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}


// Start session
session_start();

// Process login form submission
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare statement to check if email exists in users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    // Prepare statement to check if email exists in members table
    $stmt = $conn->prepare("SELECT * FROM member WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $member_result = $stmt->get_result();

    if ($user_result->num_rows == 1 && $member_result->num_rows == 1) {
        // Email exists in both tables, redirect to member portal
        $_SESSION['email'] = $email; // Store email in session
        header("Location: member-portal.php");
        exit();
    } elseif ($user_result->num_rows == 1) {
        // Email exists only in users table, redirect to pre-membership portal
        $_SESSION['email'] = $email; // Store email in session
        header("Location: pre-membership-portal.php");
        exit();
    } else {
        // Invalid email or password
        $_SESSION['error'] = "Invalid email or password";
        header("Location: index.php"); // Redirect back to login form
        exit();
    }

    $stmt->close();
}


$conn->close();
?>
