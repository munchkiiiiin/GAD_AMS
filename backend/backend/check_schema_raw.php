<?php
$conn = new mysqli('localhost', 'root', '', 'gad_submission_system');
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$result = $conn->query("DESCRIBE activity_design");
echo "Activity Design Columns:\n";
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

$result = $conn->query("DESCRIBE accomplishment_report");
echo "\nAccomplishment Report Columns:\n";
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
