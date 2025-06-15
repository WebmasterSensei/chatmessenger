<script setup lang="ts">
import { data } from "autoprefixer";
import axios from "axios";
import { ref, onMounted, watchEffect, computed, watch, toRaw, defineExpose } from "vue";
import { usePage, router } from "@inertiajs/vue3";
const props = defineProps<{
    onlineUser: []
}>();

interface Chat {
    id: number;
    name: string;
    avatar: string;
    lastMessage: string;
    time: string;
    unread: number;
    online?: boolean;
}


const emit = defineEmits<{
    (e: 'selectChat', chat: Chat): void
}>();

const users = ref();

const doSomething = () => {
    fetchUsers();
}

defineExpose({ doSomething })

const fetchUsers = async () => {

    try {
        const { data } = await axios.post(route('get.users'));

        // console.log(data);

        const onlineIds = props.onlineUser.map((user: any) => user.id);
        users.value = data.data.map((user: any) => {
            return {
                ...user,
                online: onlineIds.includes(user.id),
            };
        });
    } catch (error) {
        console.error('Error fetching users:', error);
    }
}

// const isOnline = computed(() => )

onMounted(() => {
    fetchUsers();
})

watch(() => props.onlineUser, (newVal) => {
    if (newVal) {
        fetchUsers();
    }
}, { immediate: true });



const selectChat = async (chat: Chat) => {
    const { data } = await axios.get(route('get.message'), {
        params: {
            id: chat?.id,
            online: chat.online
        }
    });
    chat.unread = 0; // Mark as read when selected
    emit('selectChat', data);
    fetchUsers();
};

const profile = () => {
    router.get(route('profile.edit'))
}
const isSearch = ref(false);

const isOpen = () => {
    isSearch.value = !isSearch.value;
}

const form = ref<{
    search: string
}>({
    search: '' // Initialize with empty string or your default value
})

watch(
    () => form.value.search, // Proper access to ref value
    async () => {
        const { data } = await axios.get(route('search.users'), {
            params: {
                search: form.value.search
            }
        })

        const onlineIds = props.onlineUser.map((user: any) => user.id);
        users.value = data.data.map((user: any) => {
            return {
                ...user,
                online: onlineIds.includes(user.id),
            };
        });
    },
    { deep: true }
)

</script>

<template>
    <div class="p-4 h-full flex flex-col bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Messages</h1>
            <div class="flex items-center space-x-1">
                <button @click="isOpen"
                    class="p-2 rounded-full text-gray-500 hover:bg-gray-100/50 transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <button @click="profile"
                    class="p-2 rounded-full text-gray-500 hover:bg-gray-100/50 transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M12 15.5A3.5 3.5 0 0 1 8.5 12 3.5 3.5 0 0 1 12 8.5a3.5 3.5 0 0 1 3.5 3.5 3.5 3.5 0 0 1-3.5 3.5m7.43-2.53c.04-.32.07-.64.07-.97 0-.33-.03-.66-.07-1l2.11-1.63c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.31-.61-.22l-2.49 1c-.52-.39-1.06-.73-1.69-.98l-.37-2.65A.506.506 0 0 0 14 2h-4c-.25 0-.46.18-.5.42l-.37 2.65c-.63.25-1.17.59-1.69.98l-2.49-1c-.22-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64L4.57 11c-.04.34-.07.67-.07 1 0 .33.03.65.07.97l-2.11 1.66c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1.01c.52.4 1.06.74 1.69.99l.37 2.65c.04.24.25.42.5.42h4c.25 0 .46-.18.5-.42l.37-2.65c.63-.26 1.17-.59 1.69-.99l2.49 1.01c.22.08.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.66z" />
                    </svg>
                </button>
            </div>
        </div>
        <a-input v-if="isSearch" placeholder="Search person..." v-model:value="form.search" class="
  w-full
  px-4 py-2
  border border-gray-300 rounded-lg
  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
  transition-all duration-200
  text-gray-700 placeholder-gray-400
  bg-white
  hover:border-gray-400
  disabled:bg-gray-100 disabled:cursor-not-allowed
  shadow-sm
  mb-2
"></a-input>
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <div v-for="user in users" :key="user.id" @click="selectChat(user)"
                :class="{ 'bg-gray-100': user.countMess, 'bg-white': !user.countMess }"
                class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100">

                <!-- Avatar with online indicator -->
                <div class="relative mr-3">
                    <img v-if="user?.image" :src="user.image" class="w-10 h-10 rounded-full object-cover"
                        :class="{ 'ring-2 ring-green-500': user.online }">
                    <img v-else :src="user?.gender == 'Male' ? '/storage/default/boy.jpg' : '/storage/default/girl.jpg'"
                        class="w-10 h-10 rounded-full object-cover" :class="{ 'ring-2 ring-green-500': user.online }">

                    <span v-if="user.online"
                        class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                </div>

                <!-- User info -->
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center">
                        <div class="flex justify-between w-full">
                            <div class="font-medium text-gray-900">{{ user.name }}</div>
                            <div v-if="user.countMess > 0"
                                class="bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-medium">
                                {{ user.countMess }}
                            </div>
                        </div>
                        <span class="text-xs text-gray-500 whitespace-nowrap ml-2">{{ user.time }}</span>
                    </div>
                    <div class="flex justify-between items-center mt-1">
                        <p class="text-sm text-gray-500 truncate pr-2">{{ user.lastMessage }}</p>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 4px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4b5563;
}
</style>
