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

.error {
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

  <?php if ($error): ?>

    <p class="error"><?= $this->escapeHTML($error) ?></p>
  <?php endif ?>

  <form action="./login" method="post" class="validation-test-form">
    <input type="hidden" name="token" value="<?= $this->generateToken() ?>">

    <fieldset>
      <legend>Login</legend>
      <div>
        <?php foreach (array(
          'id' => array('type' => 'text'),
          'pass' => array('type' => 'password')) as $name => $field): ?>

          <div class="field clearfix">
            <div class="field-name">
              <?= $name ?>:
            </div>
            <div class="field-input">
              <input type="<?= $field['type'] ?>" name="<?= $name ?>" value="">
            </div>
          </div>
        <?php endforeach ?>

        <input type="submit">
      </div>
    </fieldset>
  </form>

  <p>
    <a href="./register">register</a>
  </p>
</body>
</html>