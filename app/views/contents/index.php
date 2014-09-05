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
  <p>
    <?php echo $this->escapeHTML($description) ?>

  </p>

  <p>
    <a href="./sample/login">Sample login form</a>
  </p>
</body>
</html>