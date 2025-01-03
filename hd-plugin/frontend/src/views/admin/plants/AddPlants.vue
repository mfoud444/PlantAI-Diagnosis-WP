<script setup lang="ts">
import { ref } from 'vue';
import { NForm, NInput, NButton, FormInst, UploadCustomRequestOptions } from 'naive-ui';
import { t } from '@/locales';
import { useMessage, NGrid, NFormItemGi, NUpload, NSwitch} from 'naive-ui';
import { useDashboardStore } from '@/store'
import { useLanguage } from '@/hooks/useLanguage'

const { language } = useLanguage()
const dashboardStore = useDashboardStore()
const message = useMessage();
const formRef = ref<FormInst | null>(null);
const loading = ref(false);
const model = ref<Dashboard.Plant>({
  image: '',
  name:'',
  status: true,
  createdAt: '',
  updatedAt: '',
  languageCodes: ["en", "ar"],
  translations: ["", ""]
});
const formData = new FormData();
const rules = {
  name: [{ required: true, message: t('common.nameRequired'), trigger: ['input', 'blur'] }],
  translations: [{ required: true, message: t('dashboard.nameDiseasesRequired'), trigger: ['input', 'blur'] }],
  image: [{ required: true, message: t('common.imageRequired'), trigger: ['input', 'blur'] }],

};

async function handleAdded() {
  try {
    loading.value = true;
    formData.append('status', model.value.status.toString());
    formData.append('language_codes', JSON.stringify(model.value.languageCodes));
    formData.append('translations', JSON.stringify(model.value.translations)); 
    model.value.name = model.value.translations[language.value.name === 'ar-DZ' ? 1 : 0];
    await dashboardStore.insertActionPlant(model.value, formData);
    loading.value = false;
    message.success(t('common.addSuccess'));
    dashboardStore.showModelAdd = false
  } catch (error: any) {
    loading.value = false;
    console.error(t('common.addFailed'), error.message);
    message.error(t('common.addFailed'), error.message);
  }
}

const customRequest = async ({ file, data, onProgress, onFinish, onError }: UploadCustomRequestOptions) => {
  try {
    if (file.file !== null) {
      formData.append('image', file.file);
      model.value.image = file.file
    }
    onFinish()
  } catch (error: any) {
    console.log(error)
    message.error(error.message);
    onError();
  }
}

function isButtonDisabled() {
  if (
 
    model.value.translations[0] === '' ||
    model.value.translations[1] === '' ||
    !model.value.image 
  ) {
    return true;
  }
  return false;
}
</script>

<template>
  <div class="border-none shadow-none flex flex-col gap-2 p-2 rounded-lg">
    <div class="post-heading mb-1">
      <div class="gtext text-2xl font-bold underlined">{{ t('dashboard.addPlants') }}</div>
    </div>
    <NForm ref="formRef" :model="model" :rules="rules" size="large">
      <div>
        <NGrid :cols="4" :span="24" :x-gap="24">
          <NFormItemGi :span="12" path="image" :label="t('common.image')">
            <NUpload accept="image/*" list-type="image-card" :max=1 path="image" :custom-request="customRequest">
            </NUpload>
          </NFormItemGi>
          <template v-for="(lang, index) in model.languageCodes" :key="index">
            <NFormItemGi :span="12" :label="t('dashboard.namePlant') + ' (' + lang + ')'">
              <NInput
              :dir="lang == 'ar' ? 'rtl' : ''"
               v-model:value="model.translations[index]" :placeholder="t('dashboard.namePlant')" clearable @keyup.enter="handleAdded" />
            </NFormItemGi>
          </template>
          <NFormItemGi :span="12" path="state" :label="t('common.state')">
            <NSwitch v-model:value="model.status" size="large" />
          </NFormItemGi>
        </NGrid>
      </div>
      <div style="display: flex; justify-content: flex-end">
        <NButton type="primary" style="width:100%;" size="large" :loading="loading" :disabled="isButtonDisabled()" @click="handleAdded">
          {{ t('dashboard.addPlants') }}
        </NButton>
      </div>
    </NForm>
  </div>
</template>