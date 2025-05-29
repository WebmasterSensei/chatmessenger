<script setup lang="ts">
import { data } from "autoprefixer";
import axios from "axios";
import { ref, onMounted, watchEffect, computed, watch, toRaw } from "vue";

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


const fetchUsers = async () => {

  try {
    const { data } = await axios.post(route('get.users'), {
      data: props.onlineUser
    });
    users.value = data;
  } catch (error) {
    console.error('Error fetching users:', error);
  }
}

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
            id: chat?.id
        }
    });
    chat.unread = 0; // Mark as read when selected
    emit('selectChat', data);
};
</script>

<template>
    <div class="p-4 h-full flex flex-col bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Messages</h1>
            <button class="p-2 rounded-full text-gray-500 hover:bg-gray-100/50 transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>

        <div class="space-y-1.5 flex-1 overflow-y-auto pr-1 custom-scrollbar">
            <div v-for="user in users?.data" :key="user.id" @click="selectChat(user)"
                class="flex items-center p-3 border-2 hover:bg-gray-50 rounded-xl cursor-pointer transition-all duration-200 active:scale-[0.98] group">
                <div class="relative">
                    <img :src="user.avatar ? user.avatar : '/storage/default/avatar.png'"
                        class="w-9 h-9 rounded-full mr-3 object-cover border-2 border-white group-hover:border-gray-50 shadow"
                        :class="{ 'border-emerald-400': user.online }">
                    <span v-if="user.online"
                        class="absolute bottom-0 right-3 w-2.5 h-2.5 bg-emerald-400 rounded-full border-2 border-white group-hover:border-gray-50"></span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center">
                        <h3 class="font-medium text-gray-900 truncate">{{ user.name }}</h3>
                        <span class="text-xs text-gray-400 whitespace-nowrap ml-2">{{ user.time }}</span>
                    </div>
                    <p class="text-sm text-gray-500 truncate">{{ user.lastMessage }}</p>
                </div>

                <div v-if="user.unread > 0"
                    class="ml-2 bg-blue-400 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-medium shadow-inner">
                    {{ user.unread }}
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
