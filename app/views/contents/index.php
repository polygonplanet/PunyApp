<!DOCTYPE html>
<html>
<head>
<meta charset="<?php echo $charset ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $title ?></title>
<style>
body {
  font-family: tahoma, sans-serif;
  margin: 1.5em;
}
</style>
</head>
<body>
  <h1><?php echo $title ?></h1>
  <p>
    <?php echo $description ?>

  </p>

  <p>
    <a href="<?php echo $base_uri . 'sample/login' ?>">Sample login form</a>
  </p>
</body>
</html>