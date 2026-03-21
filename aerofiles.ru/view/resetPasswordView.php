<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Сброс пароля пользователя">
  <title>Сброс пароля AeroFiles</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/form.css">
</head>

<body>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php' ?>
  <main>
    <div class="formContainer">
      <form class="form">
        <div class="formBlock">
          <h2 class="headerForm">Установка нового пароля</h2>
        </div>
        <div class="formBlock">
          <label>Email</label>
          <input type="email" title="Введите email который был введен при регистрации" name='email' required>
        </div>
        <div class="formBlock">
          <label>Новый пароль</label>
          <input type="text" name='password' pattern="(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}" title="Пароль должен содержать только латинские буквы и цифры.&#010;В пароле должно быть не менее 8 символов" required>
        </div>
        <div class="formBlock">
          <input type="submit" class='btn' name='resetPassword_btn' value="Установить новый&#10; пароль">
        </div>
        <div class="formBlock hidden">
          <input type="text" name='resPass' value="resetPassword">
        </div>
      </form>
      <div class='message'></div>
    </div>
  </main>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php' ?>
  <script src="assets/js/resetPassword.js"></script>
</body>

</html>