<?php
session_start();
session_unset();
session_destroy();
header("Location: a-login.php");
exit();
?>
