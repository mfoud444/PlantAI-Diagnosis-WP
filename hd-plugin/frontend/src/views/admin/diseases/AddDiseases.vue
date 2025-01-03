<script setup lang="ts">
import { VNodeChild, h, ref } from 'vue';
import { NForm, NInput, NButton, FormInst, UploadCustomRequestOptions, SelectOption, NAvatar } from 'naive-ui';
import { t } from '@/locales';
import { useMessage, NGrid, NFormItemGi, NUpload, NSwitch, NSelect } from 'naive-ui';
import { useDashboardStore } from '@/store'
import { useLanguage } from '@/hooks/useLanguage'


const { language } = useLanguage()
const dashboardStore = useDashboardStore()
const message = useMessage();
const formRef = ref<FormInst | null>(null);
const loading = ref(false);
const model = ref<Dashboard.Diseases>({
  name: '',
  image: '',
  description: '',
  keyLabel: null,
  healthCondition: '',
  languageCodes: ["en", "ar"],
  translationsName: ["", ""],
  translationsDescription: ["", ""],
  translationsHealthCondition: ["", ""],
  status: true,
  productIds:[],
  products:[],
  createdAt: '',
  updatedAt: '',
});
const formData = new FormData();
const rules = {
  name: [{ required: true, message: t('common.nameRequired'), trigger: ['input', 'blur'] }],
  plantId: [{ required: true, message: t('common.plantRequired'), trigger: ['input', 'blur'] }],
  image: [{ required: true, message: t('common.imageRequired'), trigger: ['input', 'blur'] }],
  healthCondition: [{ required: true, message: t('dashboard.healthConditionRequired'), trigger: ['input', 'blur'] }],
  description: [{ required: true, message: t('dashboard.descriptionRequired'), trigger: ['input', 'blur'] }],
  keyLabel: [{ required: true, message: t('dashboard.keyLabelRequired'), trigger: ['input', 'blur'] }],
  translationsName: [{ required: true, message: t('dashboard.nameDiseasesRequired'), trigger: ['input', 'blur'] }],
  translationsDescription: [{ required: true, message: t('dashboard.desDiseasesRequired'), trigger: ['input', 'blur'] }],
  translationsHealthCondition: [{ required: true, message: t('dashboard.healthConditionRequired'), trigger: ['input', 'blur'] }],
};

const selectOption = dashboardStore.listPlants.map(plant => ({
  label: plant.translations[language.value.name === 'ar-DZ' ? 1 : 0],
  value: plant.id,
  disabled: false,
}));

async function handleAdded() {
  try {
    loading.value = true;
    formData.append('plantId', model.value?.plantId);
    formData.append('language_codes', JSON.stringify(model.value.languageCodes));
    formData.append('translations_name', JSON.stringify(model.value.translationsName));
    formData.append('translations_description', JSON.stringify(model.value.translationsDescription));
    formData.append('translations_health_condition', JSON.stringify(model.value.translationsHealthCondition));
    formData.append('productIds', JSON.stringify(model.value.productIds));
    model.value.name = model.value.translationsName[language.value.name === 'ar-DZ' ? 1 : 0];
    model.value.description = model.value.translationsDescription[language.value.name === 'ar-DZ' ? 1 : 0];
    model.value.healthCondition = model.value.translationsHealthCondition[language.value.name === 'ar-DZ' ? 1 : 0];
    formData.append('status', model.value.status.toString());
    if (model.value.keyLabel){
      formData.append('keyLabel', model.value.keyLabel.toString());
    }
   

    await dashboardStore.insertActionDiseases(model.value, formData);
    loading.value = false;
    message.success(t('common.addSuccess'));
    dashboardStore.showModelAddDiseases = false
  } catch (error: any) {
    loading.value = false;
    console.error(t('common.addFailed'), error.message);
    message.error(t('common.addFailed'), error.message);
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
    onFinish()
  } catch (error: any) {
    console.log(error)
    message.error(error.message);
    onError();
  }
}
function isButtonDisabled() {
  if (
    model.value.translationsName[0] === '' ||
    model.value.translationsDescription[0] === '' ||
    model.value.translationsHealthCondition[0] === '' ||
    model.value.translationsName[1] === '' ||
    model.value.translationsDescription[1] === '' ||
    model.value.translationsHealthCondition[2] === '' ||
    !model.value.image || 
    !model.value.plantId ||
    model.value.keyLabel === null
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


import {  NText, NTag, SelectRenderTag, SelectRenderLabel } from 'naive-ui'
import { get } from '@/utils/request'

const loadingSearch = ref(false)
const options = ref<SelectOption[]>([])



const renderMultipleSelectTag: SelectRenderTag = ({ option, handleClose }) => {
  return h(
    NTag,
    {
      style: {
        padding: '0 6px 0 4px'
      },
      round: true,
      closable: true,
      onClose: (e) => {
        e.stopPropagation()
        handleClose()
      }
    },
    {
      default: () =>
        h(
          'div',
          {
            style: {
              display: 'flex',
              alignItems: 'center'
            }
          },
          [
            h(NAvatar, {
              src: option.image,
              round: true,
              size: 22,
              style: {
                marginRight: '4px'
              }
            }),
            option.label
          ]
        )
    }
  )
}

const renderLabelProduct: SelectRenderLabel = (option) => {
  return h(
    'div',
    {
      style: {
        display: 'flex',
        alignItems: 'center'
      }
    },
    [
      h(NAvatar, {
        src: option.image,
        round: true,
        size: 'small'
      }),
      h(
        'div',
        {
          style: {
            marginLeft: '12px',
            padding: '4px 0'
          }
        },
        [
          h('div', null, [option.label]),
          h(
            NText,
            { depth: 3, tag: 'div' },
            {
              default: () => option.description
            }
          )
        ]
      )
    ]
  )
}

async function handleSearch(query: string) {
  try {
    if (!query.length) {
      options.value = []
      return
    }
    lloadingSearch.value = true
    const   data = {
        'search': query 
        
        }
    const response = await get<any[]>({ url: 'product-search/', data})
    console.log("options.value", response)
    options.value = response.map(product => ({
      label: product.name,
      value: product.id,
      image: product.image,
      description: product.description
    }))
    lloadingSearch.value = false
  } catch (error: any) {
    lloadingSearch.value = false
    console.log("error", error)
    throw error
  }
}
</script>

<template>
  <div class="border-none shadow-none flex flex-col gap-2 p-2 rounded-lg">
    <div class="post-heading mb-1">
      <div class="gtext text-2xl font-bold underlined">{{ t('dashboard.addDiseases') }}</div>
    </div>
    <NForm ref="formRef" :model="model" :rules="rules" size="large" label-placement="top">
      <div>
        <NGrid :span="24" :x-gap="24">
          <NFormItemGi :span="12" path="plantId" :label="t('dashboard.plant')">
            <NSelect filterable trigger="hover" v-model:value="model.plantId" :options="selectOption" :render-label="renderLabel">
              <NButton>{{ t('dashboard.plant') }}</NButton>
            </NSelect>
          </NFormItemGi>
          <NFormItemGi :span="12" path="image" :label="t('common.image')">
            <NUpload accept="image/*" list-type="image-card" :max="1" path="image" :custom-request="customRequest">
            </NUpload>
          </NFormItemGi>
          <template v-for="(languageCode, index) in model.languageCodes" :key="index">
            <NFormItemGi :span="12" :path="`translationsName[${index}]`" :label="`${t('dashboard.nameDiseases')} (${languageCode})`">
              <NInput v-model:value="model.translationsName[index]" :placeholder="`${t('dashboard.nameDiseases')} (${languageCode})`" clearable :dir="languageCode == 'ar' ? 'rtl' : ''" @keydown.enter.prevent />
            </NFormItemGi>
            <NFormItemGi :span="12" :path="`translationsDescription[${index}]`" :label="`${t('dashboard.desDiseases')} (${languageCode})`">
              <NInput v-model:value="model.translationsDescription[index]" :placeholder="`${t('dashboard.desDiseases')} (${languageCode})`" clearable :dir="languageCode == 'ar' ? 'rtl' : ''" @keydown.enter.prevent type="textarea" :autosize="{ minRows: 2, maxRows: 5 }" />
            </NFormItemGi>
            <NFormItemGi :span="12" :path="`translationsHealthCondition[${index}]`" :label="`${t('dashboard.healthCondition')} (${languageCode})`">
              <NInput v-model:value="model.translationsHealthCondition[index]" :placeholder="`${t('dashboard.healthCondition')} (${languageCode})`" clearable :dir="languageCode == 'ar' ? 'rtl' : ''" @keyup.enter="handleAdded" type="textarea" :autosize="{ minRows: 2, maxRows: 5 }" />
            </NFormItemGi>
          </template>
          <NFormItemGi :span="12" path="keyLabel" :label="t('dashboard.keyLabel')">
            <NInput v-model:value="model.keyLabel" :placeholder="t('dashboard.keyLabel')" clearable @keydown.enter.prevent />
          </NFormItemGi>
          <NFormItemGi :span="12" path="status" :label="t('common.state')">
            <NSwitch v-model:value="model.status" size="large" />
          </NFormItemGi>
          <NFormItemGi :span="24" path="products" :label="t('common.products')">
          
            <NSelect
      multiple
      :options="options"
      :render-label="renderLabelProduct"
      :render-tag="renderMultipleSelectTag"
      filterable
      v-model:value="model.productIds"
      :placeholder="t('common.searchProducts')"
      :loading="loadingSearch"
      clearable
      remote
      :clear-filter-after-select="false"
      @search="handleSearch"
    />
          </NFormItemGi>
        </NGrid>
      </div>
      <div style="display: flex; justify-content: flex-end">
        <NButton type="primary" style="width: 100%;" size="large" :loading="loading" :disabled="isButtonDisabled()" @click="handleAdded">
          {{ t('dashboard.addDisease') }}
        </NButton>
      </div>
    </NForm>
  </div>
</template>
