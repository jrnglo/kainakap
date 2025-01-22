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

// Fetch job data
$jobsQuery = "SELECT * FROM jobs";
$jobsResult = $connection->query($jobsQuery);

// Check for errors in query execution
if (!$jobsResult) {
    die("Error executing query: " . $connection->error);
}

// Close connection
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posted Jobs - KAINAKAP</title>
    <link rel="stylesheet" href="https://bootswatch.com/5/sandstone/bootstrap.min.css">
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
            background-color: #2d7487;
            z-index: 1000;
            top: 0;
        }
        .content {
            margin-top: 70px;
            padding: 1rem;
            flex: 1;
        }
        .card {
            margin-bottom: 20px;
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
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="job_listings.php">Job Listings</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="content">
    <div class="container mt-5">
        <div class="row">
            <?php if ($jobsResult->num_rows > 0): ?>
                <?php while ($row = $jobsResult->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card border-primary" style="max-width: 20rem;">
                            <div class="card-header"><strong><?php echo htmlspecialchars($row['title']); ?></strong></div>
                            <div class="card-body">
                                <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                                <p class="card-text"><strong>Position:</strong> <?php echo htmlspecialchars($row['job_position']); ?></p>
                                <p class="card-text"><strong>Requirements:</strong> <?php echo htmlspecialchars($row['requirements']); ?></p>
                                <form method="post" action="apply.php">
                                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <button type="submit" class="btn btn-primary" name="apply">Apply</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No jobs found.</p>
            <?php endif; ?>
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
