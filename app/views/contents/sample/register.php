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
  <h1><?= $this->escapeHTML($title) ?></h1>

  <form action="./register" method="post" class="validation-test-form">
    <input type="hidden" name="token" value="<?= $this->generateToken() ?>">

    <fieldset>
      <legend>Register</legend>
      <div>
        <?php foreach (array(
          'id' => array('type' => 'text'),
          'email' => array('type' => 'text'),
          'pass' => array('type' => 'password')) as $name => $field): ?>

          <div class="field clearfix">
            <div class="field-name">
              <?= $name ?>:
            </div>
            <div class="field-input">
              <input type="<?= $field['type'] ?>" name="<?= $name ?>" value="<?=
                $field['type'] === 'password' ? '' : $this->escapeHTML(${$name}) ?>">
            </div>
            <div class="field-error">
              <?= $this->escapeHTML($this->getValidationError($name)) ?>

            </div>
          </div>
        <?php endforeach ?>

        <input type="submit">
      </div>
    </fieldset>
  </form>

  <p>
    <a href="./login">login</a>
  </p>
</body>
</html>