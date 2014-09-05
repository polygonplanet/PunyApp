<!DOCTYPE html>
<html>
<head>
<meta charset="<?php echo $this->escapeHTML($charset) ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $this->escapeHTML($title) ?></title>
<style>
body {
  font-family: tahoma, sans-serif;
  margin: 1.5em;
}
</style>
</head>
<body>
  <h1><?php echo $this->escapeHTML($title) ?></h1>

  <h2>Home</h2>

  <div>
    <ul>
      <li>id: <?php echo isset($user, $user['userId']) ? $this->escapeHTML($user['userId']) : '?' ?></li>
      <li>email: <?php echo isset($user, $user['email']) ? $this->escapeHTML($user['email']) : '?' ?></li>
    </ul>
  </div>

  <p>
    <a href="./logout">logout</a>
  </p>
</body>
</html>