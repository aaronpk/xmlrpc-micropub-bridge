<?php
chdir('..');
require('vendor/autoload.php');

if(preg_match('~/([^/]+)/xmlrpc~', $_SERVER['REQUEST_URI'], $match)) {
  $domain = $match[1];
  include('xmlrpc.php');
  die();
}

if(preg_match('~/([^/]+)~', $_SERVER['REQUEST_URI'], $match)) {
  $domain = $match[1];
}

session_start();

?>
<!DOCTYPE html>
<html lang="en-US" class="no-js no-svg">
<head>
  <meta charset="UTF-8">
  <title>XMLRPC to Micropub Bridge</title>
  <meta name='robots' content='noindex,follow' />
</head>
<body>

  <h2>XML-RPC to Micropub Bridge</h2>

  <?php if(!isset($_SESSION['user'])): ?>
    <p>Log in to get started.</p>

    <form action="/login.php" method="post">
      <input type="url" name="url">
      <input type="submit" value="Log In">
    </form>

    <p>Your website will need to support <a href="https://www.w3.org/TR/micropub/#json-syntax">JSON Micropub requests</a> since XML-RPC clients typically send HTML contents.</p>

  <?php else: ?>

    <?php
    $domain = parse_url($_SESSION['user'], PHP_URL_HOST);
    ?>

    <p>Add this tag to your home page.</p>

    <pre><code><?= htmlspecialchars('<link rel="EditURI" type="application/rsd+xml" title="RSD" href="'.Config::$base.'/'.$domain.'/xmlrpc?rsd" />') ?></code></pre>

    <p>When configuring an app with XML-RPC, use the following as the username and password:</p>

    <table>
      <tr>
        <td>Username</td>
        <td><code><?= $domain ?></code></td>
      </tr>
      <tr>
        <td>Password</td>
        <td><code><?= htmlspecialchars($_SESSION['access_token']) ?></code></td>
      </tr>
    </table>

    <p>Make sure your website supports <a href="https://www.w3.org/TR/micropub/#json-syntax">JSON Micropub requests</a> since XML-RPC clients typically send HTML contents.</p>

    <p><a href="/logout.php">Log Out</a></p>
  <?php endif; ?>

</body>
</html>
