
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import ListEmails from './components/list-emails.vue';
import ShowEmail from './components/show-email.vue';
import ShowEmailBody from './components/show-email-body.vue';
import IFrame from './components/i-frame.vue';
import Automations from './components/automations.vue';
import Statistics from './components/statistics.vue';

Vue.component('list-emails', ListEmails);
Vue.component('show-email', ShowEmail);
Vue.component('show-email-body', ShowEmailBody);
Vue.component('i-frame', IFrame);
Vue.component('automations', Automations);
Vue.component('statistics', Statistics);

const app = new Vue({
    el: '#app'
});
