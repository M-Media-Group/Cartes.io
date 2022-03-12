
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.timeago = require('timeago.js');

import Vue, { VueConstructor } from 'vue';

import Notifications from 'vue-notification'
declare global {
    interface Window {
        timeago: any;
        Vue: any;
    }
}

declare module 'vue/types/vue' {
    interface VueConstructor {
        moment: any;
    }
}

window.Vue = Vue;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i, 'lazy').keys();

files.forEach((file) => {
    Vue.component(file.split('/').pop().split('.')[0], () => import(`${file}`));
});
// Vue.component('example-component', require('./components/ExampleComponent.vue').default);

Vue.component('chart-line-component', () => import('./components/ChartLineComponent.js'));
Vue.component('chart-pie-component', () => import('./components/ChartPieComponent.js'));

Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue').default
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue').default
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue').default
);

/** Vue Filters Start */
Vue.filter('truncate', function (text, length, suffix) {
    if (!text) {
        return "No description available";
    }
    if (text.length > length) {
        return text.substring(0, length) + suffix;
    } else {
        return text;
    }
});
/** Vue Filters End */

Vue.use(require('vue-moment'));
Vue.use(Notifications);
/**

 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});
