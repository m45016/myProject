<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/assets/css/admin.css">
  <link rel="stylesheet" href="/assets/css/modalWindow.css">
  <title>Админ панель AeroFiles</title>
</head>

<body>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php' ?>
  <main>
    <h1>Админ панель</h1>
    <form id="form">
      <label for="login">Поиск пользователя</label>
      <input type="text" id="login" name='login' placeholder="<?= $user['data']['login'] ?>" value="<?= $user['data']['login'] ?>">
      <input type="submit" id="findUser" class='btn' value="Поиск">
    </form>
    <div class='container'>
      <?php if (is_null($user['error'])): ?>
        <div class="user">
          <div class="rowData">ID: <span class='idUser'><?= $user['data']['id_user'] ?></span></div>
          <div class="rowData">Имя пользователя: <span class='nameUser'><?= $user['data']['login'] ?></span></div>
          <div class="rowData">Email: <span class='EmailUser'><?= $user['data']['email'] ?></span></div>
          <div class="rowData">Права администратора: <span class='isAdmin'><?= $user['data']['isAdminText'] ?></span></div>
          <div class="rowData">Сводбодное место в хранилище: <span class='freeStorage'><?= $user['data']['freeSize'] ?></span></div>
          <div class="rowData">Занятое место в хранилище: <span class='sizeStorage'><?= $user['data']['sizeStorage'] ?></span></div>
          <div class="rowData">Максимальный размер хранилища: <span class='maxStorage'><?= $user['data']['maxSizeStorage'] ?></span></div>
          <div class="actionUser">
            <div>Сделать админом: <span><input type="checkbox" class="setAdmin" <?= $user['data']['isAdminCheckBox'] ?>></span></div>
            <div>Установить размер хранилища: <select class='setMaxStorage'>
                <option value="No Change">No Change</option>
                <option value="5 МБ">5 Мб</option>
                <option value="10 МБ">10 Мб</option>
                <option value="50 МБ">50 Мб</option>
                <option value="100 МБ">100 Мб</option>
                <option value="500 МБ">500 Мб</option>
                <option value="10 ГБ">10 Гб</option>
                <option value="25 ГБ">25 Гб</option>
                <option value="50 ГБ">50 Гб</option>
                <option value="75 ГБ">75 Гб</option>
                <option value="100 ГБ">100 Гб</option>
                <option value="250 ГБ">250 Гб</option>
                <option value="500 ГБ">500 Гб</option>
                <option value="750 ГБ">750 Гб</option>
                <option value="1 ТБ">1 ТБ</option>
              </select>
            </div>
            <div><button class='setChange'>Изменить данные</button></div>
          </div>
        </div>
      <?php else: ?>
        <div class="text-center"><?= $user['error'] ?></div>
      <?php endif; ?>
    </div>
  </main>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php' ?>
  <script type='module' src="/assets/js/admin.js"></script>
</body>

</html>