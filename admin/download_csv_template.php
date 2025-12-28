<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="users_bulk_upload_template.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['firstName', 'lastName', 'email', 'username', 'password', 'mobileNumber', 'gender', 'dob']);
fputcsv($output, ['John', 'Doe', 'john@example.com', 'john123', 'password123', '9876543210', '1', '2000-05-15']);
fputcsv($output, ['Jane', 'Smith', 'jane@example.com', 'jane456', 'password456', '9876543211', '2', '2001-08-20']);
fputcsv($output, ['Mike', 'Johnson', 'mike@example.com', 'mike789', 'password789', '9876543212', '1', '1999-03-10']);

fclose($output);
exit;
?>
