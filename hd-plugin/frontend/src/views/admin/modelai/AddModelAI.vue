<script setup lang="ts">
import { VNodeChild, computed, h, ref } from 'vue';
import { NForm, NInput, NButton, FormInst, NAvatar, SelectOption, NInputGroup, NInputGroupLabel } from 'naive-ui';
import { t } from '@/locales';
import { useMessage, NGrid, NFormItemGi, NSelect } from 'naive-ui';
import { useDashboardStore } from '@/store'
const dashboardStore = useDashboardStore()
import { useLanguage } from '@/hooks/useLanguage'

const { language } = useLanguage()
const message = useMessage();
const formRef = ref<FormInst | null>(null);
const loading = ref(false);
const model = ref<Dashboard.ModelAI>({
  name: '',
  githubUrl: '',
  description: '',
  version: '',
  status: true,
  createdAt: '',
  updatedAt: '',
});

const selectOption = dashboardStore.listPlants.map(plant => ({
  label: plant.translations[language.name === 'ar-DZ' ? 1 : 0],
  value: plant.id,
  disabled: false,
}));

const formData = new FormData();
const rules = {
  name: [{ required: true, message: t('common.nameRequired'), trigger: ['input', 'blur'] }],
  plantId: [{ required: true, message: t('common.plantRequired'), trigger: ['input', 'blur'] }],
  githubUrl: [{ required: true, message: t('dashboard.urlRequired'), trigger: ['input', 'blur'] }],
};
async function handleAdded() {
  try {
    loading.value = true;
    formData.append('plantId', model.value.plantId);
    formData.append('name', model.value.name);
    formData.append('status', model.value.status);
    formData.append('githubUrl', model.value.githubUrl);

    await dashboardStore.insertActionModelAI(model.value, formData);
    loading.value = false;
    message.success(t('common.addSuccess'));
    dashboardStore.showModelAddModelAI = false
  } catch (error: any) {
    loading.value = false;
    console.error(t('common.addFailed'), error.message);
    message.error(t('common.addFailed'), error.message);
  }
}



function isButtonDisabled() {
  if (
    model.value.name === '' ||
    model.value.githubUrl === '' ||
   
    !model.value.plantId  ||

  githubUrlStatus.value === 'error'
  
  ) {
    return true;
  }
  return false;
}

const renderLabel: (option: SelectOption) => VNodeChild = (option) => {
  return h(
    'div',
    {
      style: {
        display: 'flex',
        alignItems: 'center',
      },
    },
    [
      h(NAvatar, {
        src: dashboardStore.listPlants.find((plant) => plant.id === option.value)?.image,
        round: true,
        size: 22,
        style: {
          marginRight: '4px'
        }
      }),
      h(
        'span',
        {
          style: {
            marginLeft: '8px',
          },
        },
        option.label as string,
      ),
    ],
  );
};

function isValidGitHubUrl(url: string): boolean {
  // Regular expression to match GitHub repository URL
  var gitHubUrlPattern = /^https?:\/\/github.com\/[^\/]+\/[^\/]+/;
  return gitHubUrlPattern.test(url);
}

const githubUrlStatus = computed(() => {
  return isValidGitHubUrl(model.value.githubUrl) ? 'success' : 'error';
});


</script>

<template>
  <div class="border-none shadow-none flex flex-col gap-2 p-2 rounded-lg">

    <div class="post-heading mb-1">
      <div class="gtext text-2xl font-bold underlined">{{ t('dashboard.addModelAI') }}</div>
    </div>
    <NForm ref="formRef" :model="model" :rules="rules" size="large">
      <div>
        <NGrid :cols="4" :span="24" :x-gap="24">

          <NFormItemGi :span="12" path="plantId" :label="t('dashboard.plant')">
            <NSelect filterable trigger="hover" v-model:value="model.plantId" :options="selectOption"
              :render-label="renderLabel">
              <NButton>{{ t('dashboard.plant') }}</NButton>
            </NSelect>
          </NFormItemGi>

          <NFormItemGi :span="12" path="name" :label="t('dashboard.nameModelAI')">
            <NInput v-model:value="model.name" :placeholder="t('dashboard.nameModelAI')" clearable
              @keyup.enter="handleAdded" />
          </NFormItemGi>

          <NFormItemGi :span="12" path="githubUrl" :label="t('dashboard.urlGithub')">

            <NInput v-model:value="model.githubUrl" :input-props="{ type: 'url' }" @change="isValidGitHubUrl"
              @input="isValidGitHubUrl" :status="githubUrlStatus"
              placeholder="https://github.com/shahd1995913/models_world_of_plants_2022/blob/main/Orange_leaves/model"
              clearable @keyup.enter="handleAdded" />
          </NFormItemGi>
        </NGrid>
      </div>

      <div style="display: flex; justify-content: flex-end">
        <NButton type="primary" style="width:100%;" size="large" :loading="loading" :disabled="isButtonDisabled()"
          @click="handleAdded">
          {{ t('dashboard.addModelAI') }}
        </NButton>
      </div>

    </NForm>
  </div>
</template>
