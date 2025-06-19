<script setup>
import { usePage, router } from "@inertiajs/vue3";
import { ref, computed, onMounted, watch, nextTick, onBeforeUnmount } from "vue";
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import axios from "axios";
const props = defineProps({
    chat: Object
})

dayjs.extend(relativeTime)

const page = usePage().props;

const messages = ref([]);

const typingIndicator = ref(false);

const getMessage = async () => {
    const { data } = await axios.get(route('get.message'), {
        params: {
            id: props.chat?.id,
        }
    });
    messages.value = data;
}
const sent = ref(false);
const typingTimeout = ref(null);

const sendTyping = () => {
    window.Echo.private(`message.${props.chat?.user?.id}`).whisper('typings', {
        typing: true,
    });

    clearTimeout(typingTimeout.value);

    typingTimeout.value = setTimeout(() => {
        sent.value = true;
        window.Echo.private(`message.${props.chat?.user?.id}`).whisper('typings', {
            typing: false,
        });
    }, 1000);
};

const emit = defineEmits(['fetch-users']);

const setupEcho = () => {
    window.Echo.private(`message.${page.auth.user.id}`)
        .listen(".message-event", (e) => {
            const messageIndex = props?.chat?.data?.findIndex(
                msg => msg.id === e.message.id
            );

            if (messageIndex === -1) {
                props?.chat?.data?.push(e.message);
                emit('fetch-users')

            } else if (props.chat && props.chat.data) {
                props.chat.data[messageIndex] = e.message;
                emit('fetch-users')

            }

            // nextTick(() => scrollToBottom());
        })
        .listenForWhisper('typings', (e) => {
            typingIndicator.value = e.typing;
        });
}

onMounted(() => {
    scrollToBottom();
    getMessage();
    setupEcho();
})
const chatContainer = ref(null);

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};



// Automatically scroll when chat data updates
watch(() => props.chat?.data, async () => {

    getMessage();
    scrollToBottom()
}, {
    deep: true
});


const fileList = ref([]);

const handleChangeFile = () => {
    sendMessage();
}



const newMessage = ref('')

const sendMessage = async () => {
    try {
        if (!newMessage.value.trim() && fileList.value.length === 0) return;
        const formData = new FormData();
        formData.append('id', props.chat?.user?.id);
        formData.append('message', newMessage.value);
        if (fileList.value.length > 0) {
            formData.append('file', fileList.value[0].originFileObj || fileList.value[0]);
        }

        const { data } = await axios.post(route('send.message'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        props.chat.data.push(data.data);
        emit('fetch-users')

        newMessage.value = '';
        fileList.value = [];
        sent.value = true;
    } catch (error) {
        console.error('Failed to send message:', error);
    }
};

const profile = () => {
    router.get(route('profile'))
}
</script>

<template>
    <div v-if="chat" class="h-full flex flex-col">
        <!-- Chat header -->
        <div class="p-4 border-b border-gray-300 flex items-center justify-between bg-white">
            <div class="flex">
                <img v-if="chat.user.image != null && chat?.user.image != ''" :src="'storage/' + chat.user.image"
                    class="w-10 h-10 rounded-full mr-3 object-cover border-2 border-white group-hover:border-gray-50 shadow"
                    :class="{ 'border-green-500': chat?.user?.isOnline == 'true' }">
                <img v-else
                    :src="chat?.user?.gender == 'Male' ? '/storage/default/boy.jpg' : '/storage/default/girl.jpg'"
                    class="w-10 h-10 rounded-full mr-3 object-cover border-2 border-white group-hover:border-gray-50 shadow"
                    :class="{ 'border-green-500': chat?.user?.isOnline == 'true' }">
                <div>
                    <h2 class="font-semibold">{{ chat?.user?.name }}</h2>
                    <p class="text-xs text-gray-500">{{ chat?.user?.isOnline == 'true' ? 'Online' : 'Offline' }}</p>
                </div>
            </div>
            <div class="flex">
                <!-- <div @click="profile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor"
                            d="M12 15.5A3.5 3.5 0 0 1 8.5 12 3.5 3.5 0 0 1 12 8.5a3.5 3.5 0 0 1 3.5 3.5 3.5 3.5 0 0 1-3.5 3.5m7.43-2.53c.04-.32.07-.64.07-.97 0-.33-.03-.66-.07-1l2.11-1.63c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.31-.61-.22l-2.49 1c-.52-.39-1.06-.73-1.69-.98l-.37-2.65A.506.506 0 0 0 14 2h-4c-.25 0-.46.18-.5.42l-.37 2.65c-.63.25-1.17.59-1.69.98l-2.49-1c-.22-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64L4.57 11c-.04.34-.07.67-.07 1 0 .33.03.65.07.97l-2.11 1.66c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1.01c.52.4 1.06.74 1.69.99l.37 2.65c.04.24.25.42.5.42h4c.25 0 .46-.18.5-.42l.37-2.65c.63-.26 1.17-.59 1.69-.99l2.49 1.01c.22.08.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.66z" />
                    </svg>
                </div> -->
                <!-- <img src="/storage/iconssvg/settings.svg" class="w-10 h-10 rounded-full mr-3 cursor-pointer"> -->
            </div>
        </div>


        <!-- Messages -->
        <div ref="chatContainer" class="flex-1 p-4 overflow-y-auto bg-gray-50 dark:bg-gray-200 scrollbar-hidden">
            <div class="">
                <div class="max-w-xs mx-auto overflow-hidden text-center p-6">
                    <!-- Avatar in the center -->
                    <div class="relative mx-auto w-24 h-24 mb-4">
                        <img v-if="chat.user.image != null && chat?.user.image != ''"
                            class="w-full h-full rounded-full object-cover border-4 border-blue-100"
                            :src="'storage/' + chat.user.image" alt="Profile picture">
                        <img v-else class="w-full h-full rounded-full object-cover border-4 border-blue-100"
                            :src="chat?.user?.gender == 'Male' ? '/storage/default/boy.jpg' : '/storage/default/girl.jpg'"
                            alt="Profile picture">

                        <span :class="chat?.user?.isOnline == 'true' ? 'bg-green-500' : 'bg-gray-400'"
                            class="absolute bottom-0 right-0 w-5 h-5  rounded-full border-2 border-white"></span>
                    </div>

                    <!-- Name and email -->
                    <div class="mb-1">
                        <h2 class="text-xl font-semibold text-gray-800">{{ chat.user.name }}</h2>
                        <p class="text-sm text-gray-500">{{ chat.user.email }}</p>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center justify-center space-x-1 mt-2">
                        <span class="w-2 h-2  rounded-full"
                            :class="chat?.user?.isOnline == 'true' ? 'bg-green-500' : 'bg-gray-400'"></span>
                        <span class="text-xs text-gray-500">{{ chat?.user?.isOnline == 'true' ? 'Online' : 'Offline'
                        }}</span>
                    </div>

                    <!-- Optional action buttons -->

                </div>

                <div class="mt-4 mb-2">
                    <div class="flex items-center text-gray-500">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="px-4 text-sm">write a message</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>

                </div>

            </div>
            <div v-for="message in chat?.data" :key="message.id"
                :class="{ 'justify-end': message.sender_id === page?.auth?.user.id, 'justify-start': message.sender_id !== page?.auth?.user.id }"
                class="flex mb-4 gap-2">

                <!-- Avatar (only shown for received messages) -->
                <div v-if="message.sender_id !== page?.auth?.user.id" class="flex-shrink-0">
                    <img :src="message.avatar || '/storage/default/avatar.png'"
                        class="w-8 h-8 rounded-full object-cover mt-1" :alt="message.senderName">
                </div>

                <!-- Message bubble -->
                <div class="flex flex-col"
                    :class="{ 'items-end': message.sender_id === page?.auth?.user.id, 'items-start': message.sender_id !== page?.auth?.user.id }">
                    <!-- Sender name (only for received messages) -->
                    <span v-if="message.sender_id !== page?.auth?.user.id"
                        class="text-xs text-gray-500 dark:text-gray-400 mb-1 ml-1">
                        {{ chat.user.name }}
                    </span>
                    <!-- {{ message.attachment }} -->

                    <div class="flex gap-2" v-if="message.attachment != null && message.attachment != ''">
                        <!-- Message bubble -->
                        <div :class="{

                        }" class="py-2 px-4 max-w-xs md:max-w-md lg:max-w-lg">
                            <a-image style="height: 200px; width: 100%;" :src="'/storage/' + message.attachment"
                                class="rounded-lg w-20 h-20"></a-image>
                        </div>

                    </div>
                    <div class="flex gap-2" v-else>
                        <div :class="{
                            'bg-blue-500 text-white rounded-[25px]': message.sender_id === page?.auth?.user.id,
                            'bg-white dark:bg-gray-100 rounded-[25px]': message.sender_id !== page?.auth?.user.id
                        }" class="rounded-lg py-2 px-4 max-w-xs md:max-w-md lg:max-w-lg shadow">
                            <p class="break-words text-sm">{{ message.attachment != null && message.attachment != '' ?
                                '' : message.message }}</p>
                        </div>
                    </div>

                    <!-- Timestamp -->
                    <span class="text-[11px]" :class="{
                        'text-gray-400 mr-4': message.sender_id === page?.auth?.user.id,
                        'text-gray-500 dark:text-gray-400 ml-2': message.sender_id !== page?.auth?.user.id,
                    }">
                        {{ dayjs(message.created_at).fromNow() }}
                    </span>

                    <!-- Read receipt (only for sent messages) -->
                    <div v-if="message.sender_id === page?.auth?.user.id"
                        class="text-xs text-gray-400 dark:text-gray-500 -mt-3">
                        <svg v-if="message.sent === 1 || message.isSeen === 0" xmlns="http://www.w3.org/2000/svg"
                            class="h-3 w-3 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <div v-else-if="message.isSeen === 1">
                            <img :src="message.avatar || 'data:image/png;base64,...'" alt="Avatar"
                                class="h-2 w-3 rounded-full object-cover" />
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Message input -->
        <div class="p-4 border-t border-gray-300 bg-white">
            <div v-if="typingIndicator" class="ml-2 mb-2">
                <div class="flex items-center gap-1.5" v-if="chat.user.id === chat?.user?.id">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 rounded-full bg-blue-500 animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 rounded-full bg-blue-500 animate-bounce" style="animation-delay: 150ms">
                        </div>
                        <div class="w-2 h-2 rounded-full bg-blue-500 animate-bounce" style="animation-delay: 300ms">
                        </div>
                    </div>
                    <span class="text-sm text-gray-500 font-medium">{{ chat.user.name }} is typing...</span>
                </div>
            </div>
            <div class="flex">

                <a-upload v-model:file-list="fileList" name="file" :show-upload-list="false"
                    :before-upload="() => false" @change="handleChangeFile">
                    <a-button size="large">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16.24 7.76L9.88 14.12a3 3 0 104.24 4.24l6.36-6.36a5 5 0 10-7.07-7.07l-7.07 7.07" />
                        </svg>
                    </a-button>
                </a-upload>

                <input v-model="newMessage" @input="sendTyping" @keyup.enter="sendMessage" type="text"
                    placeholder="Type a message..."
                    class="flex-1 border border-gray-300 rounded-l-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    ref="messageInput" />
                <button @click="sendMessage" class="border border-gray-400 text-black px-4 rounded-r-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div v-else class="h-full flex items-center justify-center bg-gray-50">
        <div class="text-center">
            <h2 class="text-xl font-semibold text-gray-600 mb-2">Select a chat to start messaging</h2>
            <p class="text-gray-500">Choose from your existing conversations</p>
        </div>
    </div>
</template>
<style scoped>
.scrollbar-hidden::-webkit-scrollbar {
    display: none;
}

.scrollbar-hidden {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
