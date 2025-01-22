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

// Query to list tables
$sql = "SHOW TABLE";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "Table: " . $row["Tables_in_$dbname"] . "<br>";
    }
} else {
    echo "0 results";
}

$conn->close();
?>
