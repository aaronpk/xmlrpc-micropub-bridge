<?php
chdir('..');
require('vendor/autoload.php');

if(!isset($_POST['url'])) {
  die('Missing URL');
}

if(!preg_match('~~', $_POST['url'])) {
  echo "<p>Error: You must enter an https URL for this proxy to work.</p>";
  die();
}

session_start();

IndieAuth\Client::$clientID = Config::$base;
IndieAuth\Client::$redirectURL = Config::$base.'/redirect.php';

// Pass the user's URL and your requested scope to the client.
// If you are writing a Micropub client, you should include at least the "create" scope.
// If you are just trying to log the user in, you can omit the second parameter.

list($authorizationURL, $error) = IndieAuth\Client::begin($_POST['url'], 'create');

// Check whether the library was able to discover the necessary endpoints
if($error) {
  echo "<p>Error: ".$error['error']."</p>";
  echo "<p>".$error['error_description']."</p>";
} else {
  // Redirect the user to their authorization endpoint
  header('Location: '.$authorizationURL);
}

