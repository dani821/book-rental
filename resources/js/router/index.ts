import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';

import { APP_NAME } from '@/lib/constants';
import { useAuthStore } from '@/stores/auth';

declare module 'vue-router' {
    interface RouteMeta {
        requiresAuth?: boolean;
        requiresGuest?: boolean;
        requiresAdmin?: boolean;
        title?: string;
    }
}

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        redirect: { name: 'dashboard' },
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/auth/LoginView.vue'),
        meta: { requiresGuest: true, title: 'Sign in' },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/auth/RegisterView.vue'),
        meta: { requiresGuest: true, title: 'Create account' },
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/views/DashboardView.vue'),
        meta: { requiresAuth: true, title: 'Dashboard' },
    },
    {
        path: '/account/password',
        name: 'account.password',
        component: () => import('@/views/account/UpdatePasswordView.vue'),
        meta: { requiresAuth: true, title: 'Account' },
    },
    {
        path: '/books',
        name: 'books',
        component: () => import('@/views/BooksView.vue'),
        meta: { requiresAuth: true, title: 'Books' },
    },
    {
        path: '/books/:id',
        name: 'books.show',
        component: () => import('@/views/BookDetailView.vue'),
        meta: { requiresAuth: true, title: 'Book' },
    },
    {
        path: '/rentals',
        name: 'rentals',
        component: () => import('@/views/RentalsView.vue'),
        meta: { requiresAuth: true, title: 'Rentals' },
    },
    {
        path: '/users',
        name: 'users',
        component: () => import('@/views/UsersView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Users' },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/views/NotFoundView.vue'),
    },
];

export const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    await auth.initialize();

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login', query: to.fullPath === '/dashboard' ? undefined : { redirect: to.fullPath } };
    }

    if (to.meta.requiresGuest && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }

    if (to.meta.requiresAdmin && !auth.isAdmin) {
        return { name: 'dashboard' };
    }

    return true;
});

router.afterEach((to) => {
    document.title = to.meta.title ? `${to.meta.title} · ${APP_NAME}` : APP_NAME;
});
