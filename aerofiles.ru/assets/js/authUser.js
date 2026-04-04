'use strict';

import API from "./apiModule.js";

document.getElementsByName('auth_btn')[0].addEventListener('click', async (e) => {

  let login = document.getElementsByName('login')[0].value;
  let password = document.getElementsByName('password')[0].value;
  let message = document.getElementsByClassName('message')[0];

  message.innerText = "";

  if (login.length !== 0 && password.length !== 0) {

    e.preventDefault();

    message.innerText = "Получение данных от сервера";

    let json = {
      login,
      password,
      auth: true
    };

    json = JSON.stringify(json);

    try{

      let response = await API.send('auth','authUser', json);

      message.innerText = 'Переход в приложение';
      location = "app.php";

    }catch(e){
      message.innerText = `Ошибка: ${e.message}`;
    }

  }

});