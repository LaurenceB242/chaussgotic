<?php
require_once 'assets/functions.php';

$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;