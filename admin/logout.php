<?php
require_once '../config.php';
require_once './auth.php';

logoutAdmin();
header('Location: /admin/login.php');
exit();
