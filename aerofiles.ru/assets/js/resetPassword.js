const STATUS_REQUEST = { 'success': '0', 'emailError': '1', 'dataError': '2' };
const URL_REQUEST = 'action/resetPasswordUser.php';

document.getElementsByName('resetPassword_btn')[0].addEventListener('click', (e) => {

  let email = document.getElementsByName('email')[0].value;
  let pass = document.getElementsByName('password')[0].value;
  let form = document.getElementsByClassName('form')[0];
  let message = document.getElementsByClassName('message')[0];

  message.innerText = "";

  if (email.length !== 0 &&
    pass.length !== 0 &&
    /(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}/.test(pass) &&
    email.includes('@') &&
    email[email.length - 1] !== '@'
  ) {

    e.preventDefault();

    message.innerText = "Получение данных от сервера";

    fetch(URL_REQUEST, {
      method: "POST",
      body: new FormData(form)
    })
      .then(result => result.text())
      .then(data => {

        switch (data) {

          case STATUS_REQUEST['success']:
            message.innerText = "Пароль изменен";
            location = "auth.php";
            break;
          case STATUS_REQUEST['emailError']:
            message.innerText = "Пользователя с таким email нет";
            break;
          default:
            message.innerText = `Ошибка: ${data}.`;

        }

      }).catch(()=>{
      message.innerHTML = "Ошибка подключения к серверу";
    });

  }

});