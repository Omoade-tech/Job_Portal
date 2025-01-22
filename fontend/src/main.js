// import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Navbar from './components/Navbar.vue'
import Search from './components/Search.vue'
import Footer from './components/Footer.vue'
import App from './App.vue'
import router from './router'

const app = createApp(App)
// const authStore = useAuthStore()
// authStore.init();

app.use(createPinia())
app.component('Navbar', Navbar)
app.component('Search', Search)
app.component('Footer', Footer)
app.use(router)

app.mount('#app')
