const STATUS_REQUEST = {'success':'0','userError':'1','passwordError': '2','dataError':'3'};
const URL_REQUEST = 'action/authUser.php';

document.getElementsByName('auth_btn')[0].addEventListener('click',(e)=>{

  let login = document.getElementsByName('login')[0].value;
  let pass = document.getElementsByName('password')[0].value;
  let form = document.getElementsByClassName('form')[0];
  let message = document.getElementsByClassName('message')[0];

  message.innerText = "";

  if(login.length !== 0 && pass.length !== 0){

    e.preventDefault();

    message.innerText = "Получение данных от сервера";

    fetch(URL_REQUEST,{
      method: "POST",
      body: new FormData(form)
    })
    .then(result=>result.text())
    .then(data=>{

      switch(data){

        case STATUS_REQUEST['success']:
          message.innerText = "Переход в приложение";
          location = "app.php";
          break;
        case STATUS_REQUEST['userError']:
          message.innerText = "Пользователь не найден.";
          break;
        case STATUS_REQUEST['passwordError']:
          message.innerText = "Пароль неверный.";
          break;
        default:
          message.innerText = `Ошибка: ${data}.`;

      }

    }).catch(()=>{
      message.innerHTML = "Ошибка подключения к серверу.";
    });

  }

});