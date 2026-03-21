const STATUS_REQUEST = {'success':'0','userError':'1','emailError':'2','dataError':'3'};
const URL_REQUEST = 'action/regUser.php';

document.getElementsByName('reg_btn')[0].addEventListener('click',(e)=>{

  let message = document.getElementsByClassName('message')[0];
  let pass = document.getElementsByName('password')[0].value;
  let rep_pass = document.getElementsByName('rep_password')[0].value;
  let login = document.getElementsByName('login')[0].value;
  let email = document.getElementsByName('email')[0].value;
  let form = document.getElementsByClassName('form')[0];

  message.innerText = "";

  if(pass !== rep_pass){

    e.preventDefault();
    message.innerHTML = "Пароли должны<br>совпадать";
    return STATUS_REQUEST['dataError'];

  }
  if(login.length > 2 &&
     rep_pass.length !=0 && 
     email.length != 0 &&
     /(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}/.test(pass) &&
     email.includes('@') &&
     email[email.length-1] !== '@'
    ){
    e.preventDefault();
    
    message.innerText = "Получение данных от сервера";
    
    fetch(URL_REQUEST,{
      method: "POST",
      body: new FormData(form)
    })
    .then(result=>result.text())
    .then(data=>{
      switch (data){
        case STATUS_REQUEST['success']:
          message.innerText = "Регистрация успешна";
          location = 'auth.php';
          break;
        case STATUS_REQUEST['userError']:
          message.innerHTML = `Пользователь с логином ${login} уже существует.<br>Измените логин`;
          break;
        case STATUS_REQUEST['emailError']:
          message.innerHTML = `Пользователь с Email ${email} уже существует.<br>Измените Email`;
          break;
        default:
          message.innerText = `Ошибка: ${data}.`;
      }
    }).catch(()=>{
      message.innerHTML = "Ошибка подключения к серверу";
    });

  }


});