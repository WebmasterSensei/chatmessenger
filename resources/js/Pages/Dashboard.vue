<template>
    <div class="flex flex-col md:flex-row h-screen bg-gray-100">
        <!-- Mobile header/chat list toggle -->
        <div class="md:hidden flex items-center justify-between p-4 bg-white border-b border-gray-300">
            <h1 class="text-xl font-semibold">
                {{ activeChat ? activeChat.name : 'Messages' }}
            </h1>
            <button @click="showChatList = !showChatList" class="p-2 rounded-md text-gray-600 hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Chat List - hidden on mobile when chat is selected -->
        <div class="w-full md:w-1/5 border-r border-gray-300 bg-white transition-all duration-300 ease-in-out" :class="{
            'hidden md:block': activeChat && !showChatList,
            'block': !activeChat || showChatList,
            'absolute inset-0 z-10 md:relative': activeChat
        }">
            <ChatList @select-chat="handleSelectChat" :online-user="getOnlineUsers" />
        </div>

        <!-- Chat Window - full screen on mobile when chat is selected -->
        <div class="flex-1 bg-white" :class="{
            'hidden md:block': !activeChat,
            'block': activeChat
        }">
            <ChatWindow v-if="activeChat" :chat="activeChat" @back="showChatList = true" />
            <div v-else class="flex items-center justify-center h-full text-gray-500">
                <div class="text-center p-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <h2 class="text-xl font-medium mb-2">Select a chat</h2>
                    <p class="text-gray-600">Choose a conversation from the list to start messaging</p>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/5 border-l border-gray-300 bg-white transition-all duration-300 ease-in-out" :class="{
            'hidden md:block': activeChat && !showChatList,
            'block': !activeChat || showChatList,
        }">
            <Profile v-if="isSelected" @select-chat="handleSelectChat" :chat="activeChat" />
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ChatList from './Chat/ChatList.vue';
import Profile from './Chat/Profile.vue';
import ChatWindow from './Chat/ChatWindow.vue';
import { useOnlineUsersStore } from '@/stores/online-store'
import { usePage } from "@inertiajs/vue3";

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

const getOnlineUsers = computed(() =>
    onlineUsersStore.getOnlineUsers
);


const activeChat = ref(null);
const showChatList = ref(false);
const isSelected = ref(false);

const handleSelectChat = (chat) => {
    isSelected.value = true;
    activeChat.value = chat;
    showChatList.value = false;
};

</script>
