<template>
    <div class="flex justify-end">
        <Button @click="() => router.get(route('dashboard'))">
            Back to Messenger
        </Button>
    </div>
    <div class="h-full flex justify-center items-center">
        <!-- {{ page.auth.user.image != null && page.auth.user.image != '' }} -->
        <Image v-if="page.auth.user.image != null && page.auth.user.image != ''" style="height: 200px; width: 200px;"
            :src="'/storage/' + page.auth.user.image" class="rounded-full" alt="image" />
        <Image v-else style="height: 200px; width: 200px;"
            :src="page.auth.user.gender == 'Male' ? '/storage/default/boy.jpg' : '/storage/default/girl.jpg'"
            class="rounded-full" alt="image" />
        <Upload v-model:file-list="fileList" name="file" :show-upload-list="false" :before-upload="() => false"
            @change="handleChangeFile">
            <EditOutlined
                style="font-size: 30px; display: relative; cursor: pointer; padding: 10px; border: 1px solid gray; border-radius: 100%;" />
        </Upload>

    </div>
</template>
<script setup>
import { Upload, Image, message, Button } from 'ant-design-vue';
import { EditOutlined } from '@ant-design/icons-vue';
import { ref, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';

const fileList = ref([]);
const page = usePage().props;

const handleChangeFile = async (info) => {
    try {
        const formData = new FormData();
        formData.append('id', page.auth?.user?.id);
        if (fileList.value.length > 0) {
            formData.append('file', fileList.value[0].originFileObj || fileList.value[0]);
        }
        const { data } = await axios.post(route('upload.image'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
    }catch{

    }finally{
        window.location.reload();
    }
};
</script>
