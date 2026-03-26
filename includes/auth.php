<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: /benedetti-rent-a-car/admin/login.php");
    exit();
}
?>