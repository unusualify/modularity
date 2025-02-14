import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { ALERT } from '@/store/mutations'

export default {
  install: (app, opts) => {
    window.Pusher = Pusher;

    if(false) {
      window.Echo = new Echo({
          broadcaster: 'reverb',
          key: import.meta.env.VITE_REVERB_APP_KEY || 'reverb-app-key',
          wsHost: import.meta.env.VITE_REVERB_HOST || 'reverb-host',
          wsPort: import.meta.env.VITE_REVERB_PORT || '8080',
          wssPort: import.meta.env.VITE_REVERB_PORT || '8080',
          forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
          enabledTransports: ['ws', 'wss'],
      });

      app.config.globalProperties.$echo = window.Echo;

      window.Echo.connected = false;

      // window.Echo.connector.pusher.connection.bind('connected', () => {
      //     Echo.isActive = true;
      //     console.log('Echo connected', Echo.isActive);
      // });
      // window.Echo.connector.pusher.connection.bind('unavailable', () => {
      //   Echo.isActive = false;
      //   console.log('Echo unavailable', Echo.isActive);
      // });
      window.Echo.connector.pusher.connection.bind('state_change', (states) => {
        if( states.current === 'connected') {
          Echo.connected = true;
          // console.log('Echo state changed to:', states.current);
        } else {
          Echo.connected = false;
          // console.log('Echo state changed to:', states.current);
        }
      });


      window.Echo.private(`models.1`)
        .listen('.modularity.model.created', (e) => {
          // commit(ALERT.SET_ALERT, { message: resp.data.message, variant: resp.data.variant })
            app._component.store.commit(ALERT.SET_ALERT, { message: 'model created', variant: 'success', location: 'top' })
            // console.log('private model created', e);
        })
        .listen('.modularity.model.updated', (e) => {
          // commit(ALERT.SET_ALERT, { message: resp.data.message, variant: resp.data.variant })
            app._component.store.commit(ALERT.SET_ALERT, { message: 'model updated', variant: 'success', location: 'top' })
            // console.log('private model created', e);
        });

      window.Echo.channel('model')
        .listen('.modularity.model.created', (e) => {
            const data = typeof e === 'string' ? JSON.parse(e) : e;
            console.log('model created', data);
            // console.log('private model created', app._component.store, app);

        })
        .listen('.modularity.model.updated', (e) => {
            const data = typeof e === 'string' ? JSON.parse(e) : e;
            console.log('model updated', data);
            // console.log('private model created', app._component.store, app);

        });
      // window.Echo.channel('ModelCreatedEvent')
      //   .listen('model.created', (e) => {
      //       console.log('model created', e);
      //   });
    }
  }
}
