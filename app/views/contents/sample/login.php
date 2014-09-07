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

  <?php if ($error): ?>

    <p class="error"><?php echo $error ?></p>
  <?php endif ?>

  <form action="<?php echo $this->currentPath('sample/login') ?>" method="post" class="validation-test-form">
    <input type="hidden" name="token" value="<?php echo $this->generateToken() ?>">

    <fieldset>
      <legend>Login</legend>
      <div>
        <?php foreach (array(
          'id' => array('type' => 'text'),
          'pass' => array('type' => 'password')) as $name => $field): ?>

          <div class="field clearfix">
            <div class="field-name">
              <?php echo $name ?>:
            </div>
            <div class="field-input">
              <input type="<?php echo $field['type'] ?>" name="<?php echo $name ?>" value="">
            </div>
          </div>
        <?php endforeach ?>

        <input type="submit">
      </div>
    </fieldset>
  </form>

  <p>
    <a href="<?php echo $this->currentPath('sample/register') ?>">register</a>
  </p>
</body>
</html>