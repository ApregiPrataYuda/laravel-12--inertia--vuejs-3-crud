// import './bootstrap'
//  import 'bootstrap/dist/css/bootstrap.min.css'
//  import 'bootstrap/dist/js/bootstrap.bundle.min.js'
//  import '@fortawesome/fontawesome-free/css/all.css';

// import { createApp, h } from 'vue'
// import { createInertiaApp } from '@inertiajs/vue3'

// createInertiaApp({
//   resolve: name => {
//     const pages = import.meta.glob('./Pages/**/*.vue') 
//     return pages[`./Pages/${name}.vue`]()   // <--- harus () karena async function
//   },
//   setup({ el, App, props, plugin }) {
//     createApp({ render: () => h(App, props) })
//       .use(plugin)
//       .mount(el)
//   },
// })


import './bootstrap'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import '@fortawesome/fontawesome-free/css/all.css';

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'  // <- import Pinia

const pinia = createPinia()  // <- buat instance Pinia

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue')
    return pages[`./Pages/${name}.vue`]() // async function
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)    // pasang plugin Inertia
      .use(pinia)     // pasang Pinia sebelum mount
      .mount(el)
  },
})
