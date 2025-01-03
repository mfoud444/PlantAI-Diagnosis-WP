<script setup lang="ts">
import { ref } from 'vue';
import { NForm, NInput, NButton, FormInst, UploadCustomRequestOptions, UploadFileInfo } from 'naive-ui';
import { t } from '@/locales';
import { useMessage, NGrid, NFormItemGi, NUpload, NSwitch} from 'naive-ui';
import { useDashboardStore } from '@/store';
import { useLanguage } from '@/hooks/useLanguage';

const dashboardStore = useDashboardStore();
const message = useMessage();
const formRef = ref<FormInst | null>(null);
const loading = ref(false);
const { language } = useLanguage();

interface Props {
    row: Dashboard.Plant
}
const props = defineProps<Props>()

const formData = new FormData();
const initialModelRef = ref<Dashboard.Plant>({
    id: props.row.id,
    name: props.row.name,
    status: props.row.status,
    image: props.row.image,
    image_url: props.row.image_url,
    createdAt: props.row.createdAt,
    updatedAt: props.row.updatedAt,
    languageCodes:  props.row.languageCodes,
    translations:  props.row.translations
});

const model = ref<Dashboard.Plant>({
    id: props.row.id,
    name: props.row.name,
    status: props.row.status,
    image: props.row.image,
    image_url: props.row.image_url,
    createdAt: props.row.createdAt,
    updatedAt: props.row.updatedAt,
    languageCodes:  props.row.languageCodes,
    translations:  props.row.translations
});

const rules = {
    name: [{ required: true, message: t('common.nameRequired'), trigger: ['input', 'blur'] }],
    image: [{ required: true, message: t('common.imageRequired'), trigger: ['input', 'blur'] }],
};

async function handleUpdate() {
    try {
        loading.value = true;
        formData.append('id', model.value.id!);
        formData.append('name', model.value.name);
        formData.append('status', model.value.status.toString());
        formData.append('language_codes', JSON.stringify(model.value.languageCodes));
        formData.append('translations', JSON.stringify(model.value.translations)); 
        await dashboardStore.updateActionPlant(model.value, formData);
        loading.value = false;
        message.success(t('common.updateSuccess'));
        dashboardStore.showModelUpdatePlant = false;
    } catch (error: any) {
        loading.value = false;
        console.error(t('common.updateFailed'), error.message);
        message.error(t('common.updateFailed'), error.message);
    }
}

const customRequest = async ({
    file,
    data,
    onProgress,
    onFinish,
    onError
}: UploadCustomRequestOptions) => {
    try {
      if (file.file !== null) {
      formData.append('image', file.file);
      model.value.image = file.file
    }
        onFinish();
    } catch (error: any) {
        console.log(error);
        message.error(error.message);
        onError();
    }
};

const previewFileList = ref<UploadFileInfo[]>([
    {
        id: 'pp',
        name: model.value.image || '',
        status: 'finished',
        url: model.value.image
    },
]);

function isButtonDisabled() {
    return (
false
        // !model.value.image
        // (model.value.translations === initialModelRef.value.translations &&
        //  model.value.status === initialModelRef.value.status &&
        //  model.value.image === initialModelRef.value.image)
    );
}
</script>

<template>
  <div class="border-none shadow-none flex flex-col gap-2 p-2 rounded-lg">

    <div class="post-heading mb-1">
      <div class="gtext text-2xl font-bold underlined">{{ t('dashboard.editPlants') }}</div>
    </div>
    <NForm
      ref="formRef"
      :model="model"
      :rules="rules"
      size="large"
    >
      <div>
        <NGrid
          :cols="4"
          :span="24"
          :x-gap="24"
        >
          <NFormItemGi
            :span="12"
            path="image"
            :label="t('common.image')"
          >
            <NUpload
            accept="image/*"
              list-type="image-card"
              :max=1
              path="image"
              :custom-request="customRequest"
              :default-file-list="previewFileList"
            
            >
            </NUpload>
          </NFormItemGi>
          <!-- Loop through languageCodes and translations -->
          <template v-for="(lang, index) in model.languageCodes" :key="index">
            <NFormItemGi
              :span="12"
              :label="t('dashboard.namePlant') + ' (' + lang + ')'"
            >
              <NInput
              :dir="lang == 'ar' ? 'rtl' : ''"
                v-model:value="model.translations[index]"
                :placeholder="t('dashboard.namePlant')"
                clearable
                @keyup.enter="handleUpdate"
              />
            </NFormItemGi>
          </template>

          <NFormItemGi
            :span="12"
            path="state"
            :label="t('common.state')"
          >
            <NSwitch
              v-model:value="model.status"
              size="large"
            />
          </NFormItemGi>

        </NGrid>
      </div>

      <div style="display: flex; justify-content: flex-end">
        <NButton
          type="primary"
          style="width:100%;"
          size="large"
          :loading="loading"
          :disabled="isButtonDisabled()"
          @click="handleUpdate"
        >
          {{ t('dashboard.editPlants') }}
        </NButton>
      </div>

    </NForm>
  </div>
</template>
