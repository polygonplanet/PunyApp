<!DOCTYPE html>
<html>
<head>
<meta charset="<?php echo $this->getCharset() ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $title ?></title>
<link rel="stylesheet" href="<?php echo $this->currentPath('css/sample.css') ?>">
</head>
<body>
  <h1><?php echo $title ?></h1>

  <h2>Home</h2>

  <div>
    <ul>
      <li>id: <?php echo isset($user, $user['userid']) ? $user['userid'] : '?' ?></li>
      <li>email: <?php echo isset($user, $user['email']) ? $user['email'] : '?' ?></li>
    </ul>
  </div>

  <p>
    <a href="<?php echo $this->currentPath('sample/logout') ?>">logout</a>
  </p>
</body>
</html>