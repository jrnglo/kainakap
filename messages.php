<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "kainakap";

// Create connection
$connection = new mysqli($servername, $username, $password, $database);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Retrieve member data based on session email
$email = $_SESSION['email'];
$alert = '';

// Query to select user data using prepared statement
$userQuery = "SELECT * FROM users WHERE email=?";
$stmtUser = $connection->prepare($userQuery);
$stmtUser->bind_param("s", $email);
$stmtUser->execute();
$userResult = $stmtUser->get_result();

// Check if exactly one user is found
if ($userResult->num_rows === 1) {
    $userData = $userResult->fetch_assoc();

    // Query to select posts for the logged-in user using prepared statement
    $postQuery = "SELECT * FROM member WHERE email=?";
    $stmtPost = $connection->prepare($postQuery);
    $stmtPost->bind_param("s", $email);
    $stmtPost->execute();
    $postResult = $stmtPost->get_result();

    // Initialize array to store posts data
    $postsData = [];
    if ($postResult->num_rows > 0) {
        while ($row = $postResult->fetch_assoc()) {
            $postsData[] = $row;
        }
    }

    // Query to select messages for the logged-in user using prepared statement
    $messageQuery = "SELECT id, subject, text, sender, date, time FROM message WHERE email=?";
    $stmtMessage = $connection->prepare($messageQuery);
    $stmtMessage->bind_param("s", $email);
    $stmtMessage->execute();
    $messageResult = $stmtMessage->get_result();

    // Initialize array to store message data
    $messagesData = [];
    if ($messageResult->num_rows > 0) {
        while ($row = $messageResult->fetch_assoc()) {
            $messagesData[] = $row;
        }
    }

} else {
    // Handle case where no user is found or multiple users with the same email
    $userData = null;
    $postsData = [];
    $messagesData = [];
}

// Handle form submission to insert new message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['sender'], $_POST['subject'], $_POST['text'])) {
    $email = htmlspecialchars($_POST['email']);
    $sender = htmlspecialchars($_POST['sender']);
    $subject = htmlspecialchars($_POST['subject']);
    $text = htmlspecialchars($_POST['text']);
    $current_date = date("Y-m-d");
    $current_time = date("H:i:s");

    // Prepare statement for insertion into 'message' table
    $stmtInsert = $connection->prepare("INSERT INTO message (email, sender, subject, text, date, time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtInsert->bind_param("ssssss", $email, $sender, $subject, $text, $current_date, $current_time);

    // Execute insertion
    if ($stmtInsert->execute()) {
        $alert = '<div class="alert alert-dismissible alert-primary">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    Message Successfully Sent.
                  </div>';
    } else {
        $alert = '<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <strong>Error!</strong> Failed to send message.
                  </div>';
    }

    $stmtInsert->close();
}

// Handle form submission to delete messages
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $message_id = $_POST['delete'];

    // Prepare statement for deletion from 'message' table
    $stmtDelete = $connection->prepare("DELETE FROM message WHERE id = ?");
    $stmtDelete->bind_param("i", $message_id);

    // Execute deletion
    if ($stmtDelete->execute()) {
        $alert = '<div class="alert alert-dismissible alert-primary">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    Message Successfully Deleted.
                  </div>';
    } else {
        $alert = '<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <strong>Error!</strong> Failed to delete message.
                  </div>';
    }

    $stmtDelete->close();

    // Redirect back to the same page to reflect changes
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Close prepared statements
$stmtUser->close();
$stmtPost->close();
$stmtMessage->close();

// Close connection
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAINAKAP - Pre-Membership Portal</title>
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
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
    <div class="container-fluid">
        <p class="navbar-brand" href="#">KAINAKAP</p>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01"
                aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
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
    <?php foreach ($postsData as $post): ?>
        <img src="uploads/<?php echo $post['profile_file']; ?>" alt="Post Image">
        <div class="caption">
            <a style="color: #F7EFED;"><?php echo $post['email']; ?></a>
        </div>
    <?php endforeach; ?>
    <input class="form-control me-sm-2" type="search" placeholder="Search">
    <a href="#"><i class="bi bi-house"></i> Home</a>
    <a href="member-portal.php"><i class="bi bi-briefcase"></i> My Jobs</a>
    <a href="#"><i class="bi bi-file-earmark"></i> Resume</a>
    <a href="messages.php"  class="active"><i class="bi bi-chat-left"></i> Messages</a>
    <a href="user.php"><i class="bi bi-person"></i> User</a>
    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-left"></i> Log Out</a>
</div>
<div class="content">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Messages
                    </div>
                    <div class="card-body">
                        <?php
                        // Display messages
                        if (!empty($messagesData)) {
                            foreach ($messagesData as $message) {
                                ?>
                                <div class="container">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($message['subject']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($message['text']); ?></p>
                                        <p class="card-text"><small class="text-muted">From: <?php echo htmlspecialchars($message['sender']); ?></small></p>
                                        <p class="card-text"><small class="text-muted">Date: <?php echo htmlspecialchars($message['date']); ?> | Time: <?php echo htmlspecialchars($message['time']); ?></small></p>
                                    <!-- Form to delete message -->
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <input type="hidden" name="delete" value="<?php echo $message['id']; ?>">
    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
</form>

                            </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<p>No messages found.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <form id="progress-form" class="p-4 progress-form" action="" method="post" lang="en" novalidate enctype="multipart/form-data">
                    <?php echo $alert; ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Send To:</label>
                        <input type="email" id="email" name="email" class="form-control" value="kainakap@gmail.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="sender" class="form-label">Sender Email:</label>
                        <input type="email" id="sender" name="sender" class="form-control" value="<?php echo $email ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject:</label>
                        <input type="text" id="subject" name="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="text" class="form-label">Message:</label>
                        <textarea id="text" name="text" rows="4" class="form-control" required></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Send Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.2.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        var disclaimer = document.querySelector("img[alt='www.000webhost.com']");
        if (disclaimer) {
            disclaimer.remove();
        }
    });
</script>
</body>
</html>

