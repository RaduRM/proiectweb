<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trivia_db";

// creare conexiune
$conn = new mysqli($servername, $username, $password, $dbname);

// verificare conexiune
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}
?>