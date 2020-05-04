import Vue from "vue";
import VueRouter from "vue-router";

// import ProfileContentComponent from '../components/home/page/body/content/profile/ProfileContentComponent.vue'
// import TrackingContentComponent from '../components/home/page/body/content/tracking/TrackingContentComponent.vue'
// import LanguageAdminContentComponent from '../components/home/page/body/content/language/LanguageAdminContentComponent.vue'
import ProfileComponent from '../components/ProfileComponent.vue'
import LoginComponent from '../components/auth/LoginComponent.vue'

Vue.use(VueRouter);

const routes = [
    {
        path: '/login',
        component: LoginComponent,
        name: "login",
    },
    {
        path: '/profile',
        component: ProfileComponent,
        name: "profile",
    },
    {
        path: '*', 
        redirect: '/profile'
    }
    // {
    //     path: '/tracking',
    //     component: TrackingContentComponent,
    //     name: "tracking",
    // },
    // {
    //     path: '/language',
    //     component: LanguageAdminContentComponent,
    //     name: 'language',
    // },
]

const router = new VueRouter({
    routes,
    linkActiveClass: "active",
    mode: "history"
});

router.beforeEach((to, from, next) => {
    if (localStorage.getItem('token') == null) {
        next('/login')
    } else {
        axios.interceptors.request.use(
          (config) => {
            let token = localStorage.getItem('token');
        
            if (token) {
              config.headers['Accept'] = 'application/json';
              config.headers['Authorization'] = `Bearer ${ token }`;
            }
        
            return config;
          }, 
        
          (error) => {
            return Promise.reject(error);
          }
        );

        next('/profile')
    }
  })

export default router;
