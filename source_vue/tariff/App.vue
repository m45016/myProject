<template>
  <componentTariffUser v-if='tariffName!==null' :tariffname='tariffName'></componentTariffUser>
    <h3>Тарифы</h3>
    <div class="container">
      <componentTariffs v-if='tariffError===null' :tariffs='tariffs'></componentTariffs>
      <componentTariffError v-else :tarifferror='tariffError'></componentTariffError>
    </div>
</template>

<script>

import API from './mixins/apiModule.js';
import { modal } from './mixins/modalWindow.js';

// компоненты
import componentTariffUser from './components/tariffUser.vue';
import componentTariffs from './components/tariffs.vue';
import componentTariffError from './components/tariffError.vue';


export default {
  components: {
    componentTariffError,
    componentTariffUser,
    componentTariffs
  },
  
  data() {
  return {
    tariffError: null,
    tariffs: null,
    tariffName: null,
    API,
    modal
  }
},
async created() {
  try {

    let response = await this.API.send('tariff', 'getTariffs');

    this.tariffName = response['tariff_name'];
    this.tariffs = response['tariffs'];

  } catch (e) {
    this.tariffError = e.message;
  }
},
methods: {
  async setTariff(e) {
    if (e.target.classList.contains('setTariff')) {

      let nameTariff = e.target.parentNode.parentNode.children[0].innerText.toLowerCase();

      let json = {
        nameTariff
      };

      json = JSON.stringify(json);

      try {
        let response = await this.API.send('tariff', 'setTariff', json);

        if (!response.success) {
          throw new Error('Не удалось изменить тариф');
        }

        if (response.goToReg) {
          await this.modal.alert(response.message);
          location = 'reg.php';
          return 0;
        }

        let newTariff = response.newTariff;
        await this.modal.alert('Тариф изменен');
        this.tariffName = newTariff;

      } catch (e) {
        await this.modal.alert(`Ошибка: ${e.message}`);
      }
    }
  }
},
provide() {
  return {
    APP: {
      setTariff: this.setTariff
    }
  }
}
}
</script>