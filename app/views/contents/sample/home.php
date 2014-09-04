<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $this->escapeHTML($title) ?></title>
<style>
body {
  font-family: tahoma, sans-serif;
  margin: 1.5em;
}
</style>
</head>
<body>
  <h1><?= $this->escapeHTML($title) ?></h1>

  <h2>Home</h2>

  <div>
    <ul>
      <li>id: <?= isset($user, $user['userId']) ? $this->escapeHTML($user['userId']) : '?' ?></li>
      <li>email: <?= isset($user, $user['email']) ? $this->escapeHTML($user['email']) : '?' ?></li>
    </ul>
  </div>

  <p>
    <a href="./logout">logout</a>
  </p>
</body>
</html>