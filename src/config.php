<?php
const DB_SERVER = 'localhost';
const DB_NAME = 'sdi-knotus';
const DB_USERNAME = 'root';
const DB_PASSWORD = '';

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    echo '<script>alert("Connection failed")</script>';
}