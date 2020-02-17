import Vue from 'vue';
import Router from 'vue-router';

Vue.use(Router);

export default new Router({
	routes: [
		{
			name: 'home',
			path: '/',
			component: require('./components/LinkComponent').default
		},
		{
			path: '*',
			component: require('./components/404Component').default
		}
	]
});