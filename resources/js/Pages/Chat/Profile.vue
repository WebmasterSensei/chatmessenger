<template>
    <div class="group before:hover:scale-95 before:hover:w-80 before:hover:h-44 before:hover:rounded before:transition-all before:duration-500 before:content-['']
        before:w-80 before:h-24 before:rounded before:bg-gradient-to-bl from-sky-200
         via-orange-200 to-orange-700 before:absolute before:top-0 w-80 h-72 relative
          bg-slate-50 flex flex-col items-center justify-center gap-2 text-center rounded overflow-hidden">

        <div
            class="w-28 h-28 bg-blue-700 mt-8 rounded-full border-4 border-slate-50 z-10 group-hover:scale-150 group-hover:-translate-x-24  group-hover:-translate-y-20 transition-all duration-500">
            <img class="w-28 h-28 rounded-full" src="/storage/default/nature.jpg" alt="">
        </div>
        <div class="z-10  group-hover:-translate-y-10 transition-all duration-500">
            <span class="text-2xl font-semibold">{{ chat?.user?.name }}</span>
            <p>{{ chat?.user?.email }}</p>
        </div>
        <a class="bg-blue-500 px-4 py-1 text-slate-50 rounded-md z-10 hover:scale-125 transition-all duration-500 hover:bg-blue-500"
            href="#">Folow</a>
    </div>
    <!-- {{ chat.data }} -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-1 p-3">
        <div v-for="item in image">
            <img :src="item.attachment" alt="Image 1" class="w-full h-auto rounded-md shadow" />
        </div>
    </div>


</template>

<script setup>
import { computed } from 'vue';


const props = defineProps({
    chat: Object
});
const image = computed(() => {
  return props.chat?.data?.filter((data) => data.attachment !== "") || [];
});
</script>
