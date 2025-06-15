import { defineStore } from 'pinia';

export const useOnlineUsersStore = defineStore('onlineUsers', {
    state: () => ({
        onlineUsers: [],
    }),
    getters: {
        getOnlineUsers: (state) => state.onlineUsers,
    },
    actions: {
        setOnlineUsers(users) {
            this.onlineUsers = users;
        },
        addOnlineUser(user) {
            this.onlineUsers.push(user);
        },
        removeOnlineUser(user) {
            this.onlineUsers = this.onlineUsers.filter(
                (onlineUser) => onlineUser.id !== user.say
            );
        },
    },
});
