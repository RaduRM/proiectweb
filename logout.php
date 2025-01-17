<?php
session_start();

//distrugem sesiunea
session_unset();
session_destroy();

// ducem mai departe pe login.php
header("Location: index.php");
exit;
?>