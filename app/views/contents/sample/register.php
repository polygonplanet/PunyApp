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

.validation-test-form > fieldset {
  display: inline-block;
}

.field {
  margin: 0.5em 1em;
}

.field-name {
  float: left;
  width: 60px;
}

.field-input {
  float: left;
}

.field-error {
  float: left;
  color: red;
  margin-left: 1em;
}

.clearfix:after {
  content: ".";
  display: block;
  width: 0;
  height: 0;
  max-width: 1px;
  max-height: 1px;
  font-size: 0;
  line-height: 0;
  clear: both;
  overflow: hidden;
  visibility: hidden;
}
</style>
</head>
<body>
  <h1><?php echo $title ?></h1>

  <form action="<?php echo $base_uri . 'sample/register' ?>" method="post" class="validation-test-form">
    <input type="hidden" name="token" value="<?php echo $this->generateToken() ?>">

    <fieldset>
      <legend>Register</legend>
      <div>
        <?php foreach (array(
          'id' => array('type' => 'text'),
          'email' => array('type' => 'text'),
          'pass' => array('type' => 'password')) as $name => $field): ?>

          <div class="field clearfix">
            <div class="field-name">
              <?php echo $name ?>:
            </div>
            <div class="field-input">
              <input type="<?php echo $field['type'] ?>" name="<?php echo $name ?>" value="<?php
                echo $field['type'] === 'password' ? '' : ${$name} ?>">
            </div>
            <div class="field-error">
              <?php echo $this->getValidationError($name) ?>

            </div>
          </div>
        <?php endforeach ?>

        <input type="submit">
      </div>
    </fieldset>
  </form>

  <p>
    <a href="<?php echo $base_uri . 'sample/login' ?>">login</a>
  </p>
</body>
</html>