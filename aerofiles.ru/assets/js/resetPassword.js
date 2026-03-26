
'use strict';

import API from './apiModule.js';

document.getElementsByName('resetPassword_btn')[0].addEventListener('click', async (e) => {

  let email = document.getElementsByName('email')[0].value;
  let password = document.getElementsByName('password')[0].value;
  let message = document.getElementsByClassName('message')[0];

  message.innerText = "";

  if (
    email.length !== 0 &&
    /(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}/.test(password) &&
    email.includes('@') &&
    email[email.length - 1] !== '@'
  ) {

    e.preventDefault();

    message.innerText = "Получение данных от сервера";

    let json = {
      email,
      password,
      resPass: true
    };

    json = JSON.stringify(json);

    try {
      let response = await API.send('auth','resetPasswordUser', json);

      if(response !== true){
        throw new Error('Получены не корректные данные');
      }

      message.innerText = "Пароль изменен";
      location = "auth.php";

    } catch (e) {
      message.innerText = `Ошибка: ${e.message}`;
    }
  }
});