
const URL_REQUEST = '/api';

/**
 * @param {string} handler
 * @param {string} action 
 * @param {string|null} json
 * @returns {object}
 */
async function send(handler, action, json = null) {
  let url = `${URL_REQUEST}/${handler}/${action}.php`;
  let response = await fetch(url, {
    method: "POST",
    body: json
  });

  if (!response.ok) {
    throw new Error(`${response.status} ${response.statusText}`);
  }

  response = await response.json();

  if(response?.data === undefined || response?.error === undefined){
    throw new Error('Получена не корректная структура данных');
  }

  if (typeof response.error === 'string') {
    throw new Error(response.error);
  }

  return response['data'];

}

export default {send};