import { createApp } from 'vue';
import { createPinia } from 'pinia';

import App from '@/App.vue';
import { registerUnauthorizedHandler } from '@/api/client';
import { router } from '@/router';
import { useAuthStore } from '@/stores/auth';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// When any request comes back 401, reset the session and send the user to login.
const auth = useAuthStore(pinia);
registerUnauthorizedHandler(() => {
    auth.reset();

    if (router.currentRoute.value.name !== 'login') {
        router.push({ name: 'login' });
    }
});

app.mount('#app');
