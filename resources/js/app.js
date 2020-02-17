require('./bootstrap');

import Vue from 'vue';
import moment from 'moment';
import router from './routes.js';
import Element from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import 'vue-multiselect/dist/vue-multiselect.min.css';

Vue.use(Element, { size: 'small', zIndex: 3000 });

const app = new Vue({
	el: '#app',
	router
});