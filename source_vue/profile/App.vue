<template>
    <componentUser v-if='userError===null' :user='user'></componentUser>
    <componentUserError v-else :usererror='userError'></componentUserError>
</template>

<script>

import API from './mixins/apiModule.js';
import { modal } from './mixins/modalWindow.js';
import {Datetime} from './mixins/datetimeModule.js';

// компоненты
import componentUser from './components/user.vue';
import componentUserError from './components/userError.vue';

export default {
  components: {
    componentUser,
    componentUserError
  },
  
  data() {
    return {
      userError: '',
      user: null,
      API,
      modal
    }
  },
  async created() {
    try {
      let response = await this.API.send('profile', 'getProfile');
      this.user = response;
      if (this.user !== null) {
        let datetime = new Datetime();
        datetime.setDate(this.user['date_payment']);
        this.user['date_payment'] = datetime.getDate();

        datetime.setDate(this.user['tariffValidTo']);
        this.user['tariffValidTo'] = datetime.getDate();
        this.userError = null;
      }
    } catch (e) {
      this.userError = `Ошибка: ${e.message}`;
    }
  },
  methods: {
    async paymentTariff() {
      try {

        let response = await this.API.send('tariff', 'paymentTariff');

        if (!response) {
          throw new Error('Не удалось оплатить тариф');
        }

        await this.modal.alert('Тариф оплачен');
        location = '';

      } catch (e) {
        await this.modal.alert(`Ошибка: ${e.message}`);
      }
    }
  },
  provide() {
    return {
      APP: {
        paymentTariff: this.paymentTariff
      }
    }
  }
}
</script>