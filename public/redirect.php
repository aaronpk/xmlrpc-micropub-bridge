<?php
chdir('..');
require('vendor/autoload.php');

session_start();
IndieAuth\Client::$clientID = Config::$base;
IndieAuth\Client::$redirectURL = Config::$base.'/redirect.php';

list($user, $error) = IndieAuth\Client::complete($_GET);

if($error) {
  echo "<p>Error: ".$error['error']."</p>";
  echo "<p>".$error['error_description']."</p>";
} else {
  $_SESSION['user'] = $user['me'];
  $_SESSION['access_token'] = $user['access_token'];
  header('Location: /?complete');
}
