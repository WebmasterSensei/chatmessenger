<script setup>
import { useOnlineUsersStore } from '@/stores/online-store'
import { usePage } from "@inertiajs/vue3";

import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
const onlineUsersStore = useOnlineUsersStore();

const { setOnlineUsers, addOnlineUser, removeOnlineUser } = onlineUsersStore;

const page = usePage().props;

onMounted(() => {
    if (page.auth) {
        window.Echo
            .join('online.users')
            .here((users) => setOnlineUsers(users))
            .joining(async (user) => addOnlineUser(user))
            .leaving(async (user) => removeOnlineUser(user));
    }
});

onBeforeUnmount(() => {
    if (page.auth) {
        window.Echo.leaveAllChannels();
    }
});
</script>

<template>

            <main>
                <slot />
            </main>
</template>
