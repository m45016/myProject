document.addEventListener('click', (e) => {
  if (e.target.classList.contains('setChange')) {
    let isAdmin = e.target.parentNode.parentNode.children[0].children[0].children[0].checked;
    let maxStorage = e.target.parentNode.parentNode.children[1].children[0].value;
    let idUser = e.target.parentNode.parentNode.parentNode.children[0].children[0].innerText;

    console.log(isAdmin);

    let form = new FormData();

    form.append('isAdmin', isAdmin);
    form.append('maxStorage', maxStorage);
    form.append('idUser', idUser);

    let url = '/action/updateUserData.php';

    fetch(url, {
      method: 'POST',
      body: form
    })
      .then(response => response.text())
      .then(data => {
        if (data !== '0') {
          alert('Не удалось изменить данные пользователя');
          return 1;
        }

        alert('Данные пользователя изменены');
        document.getElementById('findUser').click();

      }).catch(() => {
        alert("Ошибка подключения к серверу");
      });
  }
});

document.getElementById('findUser').addEventListener('click', (e) => {
  e.preventDefault();
  let form = new FormData(document.getElementById('form'));
  let url = '/action/getUser.php';

  fetch(url, {
    method: 'POST',
    body: form
  })
    .then(response => response.json())
    .then(data => {
      let user = null;
      let container = document.getElementsByClassName('container')[0];
      if(data['error'] === null){
          user = `<div class="user">
          <div class="rowData">ID: <span class='idUser'>${data['data']['id_user']}</span></div>
          <div class="rowData">Имя пользователя: <span class='nameUser'>${data['data']['login']}</span></div>
          <div class="rowData">Email: <span class='EmailUser'>${data['data']['email']}</span></div>
          <div class="rowData">Права администратора: <span class='isAdmin'>${data['data']['isAdminText']}</span></div>
          <div class="rowData">Сводбодное место в хранилище: <span class='freeStorage'>${data['data']['freeSize']}</span></div>
          <div class="rowData">Занятое место в хранилище: <span class='sizeStorage'>${data['data']['sizeStorage']}</span></div>
          <div class="rowData">Максимальный размер хранилища: <span class='maxStorage'>${data['data']['maxSizeStorage']}</span></div>
          <div class="actionUser">
            <div>Сделать админом: <span><input type="checkbox" class="setAdmin" ${data['data']['isAdminCheckBox']}> </span></div>
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
      }
      else{ 
        user = `<div class="text-center">${data['error']}</div>`;
      }

      container.innerHTML = '';
      container.insertAdjacentHTML('beforeend',user);

    })
    .catch((e)=>{
      alert("Ошибка подключения к серверу.");
      console.log(e);
    });

})