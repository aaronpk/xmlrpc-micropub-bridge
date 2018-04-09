<?php
chdir('..');
require('vendor/autoload.php');

session_start();

unset($_SESSION['user']);
unset($_SESSION['access_token']);
session_destroy();

header('Location: /');
