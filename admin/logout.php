<?php
require_once '../config.php';
require_once './auth.php';

logoutAdmin();
header('Location: /CircleUp/admin/login.php');
exit();
