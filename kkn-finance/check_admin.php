<?php
require 'db.php';
$res = mysqli_query($conn, "SELECT id,nama,nim,email,role,password FROM users WHERE role='admin' LIMIT 1");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    if ($row) {
        print_r($row);
    } else {
        echo "NO_ADMIN";
    }
} else {
    echo "QUERY_ERROR: " . mysqli_error($conn);
}
