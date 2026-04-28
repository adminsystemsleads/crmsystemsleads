import axios from 'axios'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Echo usa protocolo Pusher, aunque el backend sea Reverb
window.Pusher = Pusher

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,

  // usaremos tu mismo dominio y Nginx proxya /app/ hacia 8081
  wsHost: import.meta.env.VITE_REVERB_SERVER_HOST || window.location.hostname,
  wsPort: Number(import.meta.env.VITE_REVERB_SERVER_PORT || 443),
  wssPort: Number(import.meta.env.VITE_REVERB_SERVER_PORT || 443),

  forceTLS: true,
  enabledTransports: ['ws', 'wss'],
  disableStats: true,
})
