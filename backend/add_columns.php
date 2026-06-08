<?php
$conn = new mysqli('localhost', 'root', '', 'gad_submission_system');
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$conn->query("ALTER TABLE activity_design ADD COLUMN remarks TEXT DEFAULT NULL");
$conn->query("ALTER TABLE activity_design ADD COLUMN accomplishment_deadline DATE DEFAULT NULL");
$conn->query("ALTER TABLE accomplishment_report ADD COLUMN remarks TEXT DEFAULT NULL");

echo "Added remarks and deadline columns successfully.\n";
?>
