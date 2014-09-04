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
  <p>
    <?= $this->escapeHTML($description) ?>

  </p>

  <p>
    <a href="./sample/login">Sample login form</a>
  </p>
</body>
</html>