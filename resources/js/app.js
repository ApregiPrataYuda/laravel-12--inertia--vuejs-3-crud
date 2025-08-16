import './bootstrap'
 import 'bootstrap/dist/css/bootstrap.min.css'
 import 'bootstrap/dist/js/bootstrap.bundle.min.js'
 import '@fortawesome/fontawesome-free/css/all.css';

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue') 
    return pages[`./Pages/${name}.vue`]()   // <--- harus () karena async function
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
