<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, watch, nextTick } from "vue";
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
            id: props.chat?.id
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

const setupEcho = () => {
    window.Echo.private(`message.${page.auth.user.id}`)
        .listen(".message-event", (e) => {
            const messageIndex = props?.chat?.data?.findIndex(
                msg => msg.id === e.message.id
            );

            if (messageIndex === -1) {
                props?.chat?.data?.push(e.message);

            } else if (props.chat && props.chat.data) {
                props.chat.data[messageIndex] = e.message;

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

const newMessage = ref('')

const sendMessage = async () => {
    try {
        if (!newMessage.value.trim()) return; // Don't send empty messages

        const { data } = await axios.post(route('send.message'), {
            id: props.chat?.user?.id,
            message: newMessage.value.trim()
        });
        props.chat.data.push(data.data);
        // Clear the input after sending
        newMessage.value = '';
        sent.value = true;
    } catch (error) {
        console.error('Failed to send message:', error);
        // You might want to show an error message to the user here
    }
};
</script>

<template>
    <div v-if="chat" class="h-full flex flex-col">
        <!-- Chat header -->
        <div class="p-4 border-b border-gray-300 flex items-center justify-between bg-white">
            <div class="flex">
                <img :src="chat?.user?.avatar ? chat?.user?.avatar : '/storage/default/avatar.png'"
                    class="w-10 h-10 rounded-full mr-3">
                <div>
                    <h2 class="font-semibold">{{ chat?.user?.name }}</h2>
                    <p class="text-xs text-gray-500">Online</p>
                </div>
            </div>
            <div class="flex">
                <img src="/storage/iconssvg/settings.svg" class="w-10 h-10 rounded-full mr-3 cursor-pointer">
            </div>
        </div>

        <!-- Messages -->
        <div ref="chatContainer" class="flex-1 p-4 overflow-y-auto bg-gray-50 dark:bg-gray-200 scrollbar-hidden">
            <div v-for="message in chat?.data" :key="message.id"
                :class="{ 'justify-end': message.recipient === chat?.user?.id, 'justify-start': message.sender_id === page?.auth?.user.id }"
                class="flex mb-4 gap-2">

                <!-- Avatar (only shown for received messages) -->
                <div v-if="message.sender_id === chat?.user?.id" class="flex-shrink-0">
                    <img :src="message.avatar || '/storage/default/avatar.png'"
                        class="w-8 h-8 rounded-full object-cover mt-1" :alt="message.senderName">
                </div>

                <!-- Message bubble -->
                <div class="flex flex-col"
                    :class="{ 'items-end': message.recipient === chat?.user?.id, 'justify-start': message.sender_id === page?.auth?.user.id }">
                    <!-- Sender name (only for received messages) -->
                    <span v-if="message.sender_id === chat?.user?.id"
                        class="text-xs text-gray-500 dark:text-gray-400 mb-1 ml-1">
                        {{ chat.user.name }}
                    </span>

                    <div class="flex gap-2">
                        <!-- Message bubble -->
                        <div :class="{
                            'bg-blue-500 text-white rounded-[25px]': message.sender_id === page?.auth?.user.id,
                            'bg-white dark:bg-gray-100 rounded-[25px]': message.sender_id !== page?.auth?.user.id
                        }" class="rounded-lg py-2 px-4 max-w-xs md:max-w-md lg:max-w-lg shadow">
                            <p class="break-words text-sm">{{ message.message }}</p>

                        </div>




                        <!-- Seen status (only for sent messages) -->


                    </div>
                    <span class="text-[11px]" :class="{
                        'text-gray-400 mr-4': message.sender_id === page?.auth?.user.id,
                        'text-gray-500 dark:text-gray-400 ml-2': message.sender_id !== page?.auth?.user.id,
                    }">
                        {{ dayjs(message.created_at).fromNow() }}
                    </span>
                    <div v-if="message.sender_id === page.auth.user.id"
                        class="text-xs text-gray-400 dark:text-gray-500 -mt-3">
                        <svg v-if="message.sent === 1 || message.isSeen === 0" xmlns="http://www.w3.org/2000/svg"
                            class="h-3 w-3 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <div v-else-if="message.isSeen === 1">
                            <img :src="message.avatar || 'data:image/png;base64,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'"
                                alt="Avatar" class="h-2 w-3 rounded-full object-cover" />
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
                <input v-model="newMessage" @input="sendTyping" @keyup.enter="sendMessage" type="text"
                    placeholder="Type a message..."
                    class="flex-1 border border-gray-300 rounded-l-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button @click="sendMessage"
                    class="bg-blue-500 text-white px-4 rounded-r-lg hover:bg-blue-600 focus:outline-none">
                    Send
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
