'use strict';

import API from "./apiModule.js";
import { modal } from "./modalWindow.js";

document.addEventListener('click', async (e) => {
  if (e.target.classList.contains('setChange')) {
    let isAdmin = e.target.parentNode.parentNode.children[0].children[0].children[0].checked;
    let maxStorage = e.target.parentNode.parentNode.children[1].children[0].value;
    let idUser = Number(e.target.parentNode.parentNode.parentNode.children[0].children[0].innerText);

    let json = {
      isAdmin,
      maxStorage,
      idUser
    };

    json = JSON.stringify(json);

    try {
      let response = await API.send('admin', 'updateUserData', json);

      if (response !== true) {
        throw new Error('Данные не корректны');
      }

      await modal.alert('Данные пользователя изменены');
      document.getElementById('findUser').click();

    } catch (e) {
      await modal.alert(e.message);
    }
  }
});

document.getElementById('findUser').addEventListener('click', async (e) => {
  e.preventDefault();

  let url = '/action/getUser.php';

  let login = document.querySelector("input[name='login']").value;
  let container = document.getElementsByClassName('container')[0];

  let json = {
    login
  };

  json = JSON.stringify(json);

  let user = null;

  try {

    let response = await API.send('admin', 'getUser', json);

    if (
      response?.id_user === undefined ||
      response?.login === undefined ||
      response?.email === undefined ||
      response?.isAdminText === undefined ||
      response?.freeSize === undefined ||
      response?.sizeStorage === undefined ||
      response?.maxSizeStorage === undefined ||
      response?.isAdminCheckBox === undefined
    ) {
      throw new Error('Получены не корректные данные');
    }

    user = `<div class="user">
          <div class="rowData">ID: <span class='idUser'>${response.id_user}</span></div>
          <div class="rowData">Имя пользователя: <span class='nameUser'>${response.login}</span></div>
          <div class="rowData">Email: <span class='EmailUser'>${response.email}</span></div>
          <div class="rowData">Права администратора: <span class='isAdmin'>${response.isAdminText}</span></div>
          <div class="rowData">Сводбодное место в хранилище: <span class='freeStorage'>${response.freeSize}</span></div>
          <div class="rowData">Занятое место в хранилище: <span class='sizeStorage'>${response.sizeStorage}</span></div>
          <div class="rowData">Максимальный размер хранилища: <span class='maxStorage'>${response.maxSizeStorage}</span></div>
          <div class="actionUser">
            <div>Сделать админом: <span><input type="checkbox" class="setAdmin" ${response.isAdminCheckBox}> </span></div>
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
        </div>`;

  } catch (e) {
    user = `<div class="text-center">Ошибка: ${e.message}</div>`;
  }

  container.innerHTML = '';
  container.insertAdjacentHTML('beforeend', user);

});