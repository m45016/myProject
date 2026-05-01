import {modal} from './modalWindow.js';
import API from './apiModule.js';

document.querySelector('form input[type="submit"]').addEventListener('click', async (e) => {
  
  let balance = document.querySelector('input[name="balance"]').valueAsNumber;

  if (!(balance < 10) && !(balance > 30000) && !isNaN(balance)) {
    e.preventDefault();
    let json = {
      balance
    };

    json = JSON.stringify(json);

    try{
      let response = await API.send('profile','addBalance',json);
      
      if(!response?.success){
        throw new Error('Не удалось пополнить баланс');
      }

      await modal.alert('Баланс пополнен');
      document.querySelector('.balance').children[1].innerText = `${response.balance} руб.`;

    }catch(e){
      await modal.alert(`Ошибка: ${e.message}`);
    }
  }
});