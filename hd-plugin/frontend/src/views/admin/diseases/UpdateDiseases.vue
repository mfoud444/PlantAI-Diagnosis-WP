<script setup lang="ts">
import { VNodeChild, computed, h, ref, watch } from 'vue';
import { NForm, NInput, NButton, FormInst, UploadCustomRequestOptions, SelectOption, NAvatar, UploadFileInfo } from 'naive-ui';
import { t } from '@/locales';
import { useMessage, NGrid, NFormItemGi, NUpload, NSwitch, NSelect } from 'naive-ui';
import { useDashboardStore} from '@/store'
const dashboardStore = useDashboardStore()
const message = useMessage();
const formRef = ref<FormInst | null>(null);
const loading = ref(false);
import { useLanguage } from '@/hooks/useLanguage'

const { language } = useLanguage()
interface Props {
    row: Dashboard.Diseases
}
const props = defineProps<Props>()


const formData = new FormData();
const initialModelRef = ref<Dashboard.Diseases>({ ...props.row });
const model = ref<Dashboard.Diseases>({ ...props.row });


const rules = {
  name: [{ required: true, message: t('common.nameRequired'), trigger: ['input', 'blur'] }],
  plant: [{ required: true, message: t('common.plantRequired') }],
  image: [{ required: true, message: t('common.imageRequired'), trigger: ['input', 'blur'] }],
  healthCondition: [{ required: true, message: t('fashboard.healthConditionRequired'), trigger: ['input', 'blur'] }],
  description: [{ required: true, message: t('dashboard.descriptionRequired'), trigger: ['input', 'blur'] }],

};
const selectOption = dashboardStore.listPlants.map(plant => ({
  label: plant.translations[language.value.name === 'ar-DZ' ? 1 : 0],
  value: plant.id,
  disabled: false,
}));
async function handleAdded() {
  try {
    loading.value = true;
    formData.append('id', model.value.id!);
    formData.append('plantId', model.value.plantId!); 
    formData.append('language_codes', JSON.stringify(model.value.languageCodes));
    formData.append('translations_name', JSON.stringify(model.value.translationsName)); 
    formData.append('translations_description', JSON.stringify(model.value.translationsDescription)); 
    formData.append('translations_health_condition', JSON.stringify(model.value.translationsHealthCondition)); 
    formData.append('status', model.value.status.toString()); 
    formData.append('productIds', JSON.stringify(model.value.productIds));
    model.value.name = model.value.translationsName[language.value.name === 'ar-DZ' ? 1 : 0];
    model.value.description = model.value.translationsDescription[language.value.name === 'ar-DZ' ? 1 : 0];
    model.value.healthCondition = model.value.translationsHealthCondition[language.value.name === 'ar-DZ' ? 1 : 0];
    
    await dashboardStore.updateActionDisease(model.value, formData);
    loading.value = false;
    message.success(t('common.editSuccess'));
    dashboardStore.showModelUpdateDiseases = false
  } catch (error: any) {
    loading.value = false;
    console.error(t('common.editFailed'), error.message);
    message.error(t('common.editFailed'), error.message);
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
  }
  catch (error: any) {
    console.log(error)
    message.error(error.message);
    onError();
  }

}

function isButtonDisabled() {
  return false;
  // return (
  //       !model.value.name ||
  //       !model.value.description ||
  //       !model.value.healthCondition ||
  //       !model.value.image ||
  //       (model.value.name === initialModelRef.value.name &&
  //       model.value.description  ===  initialModelRef.value.description &&
  //       model.value.healthCondition  === initialModelRef.value.healthCondition &&
  //        model.value.status=== initialModelRef.value.status &&
  //        model.value.image === initialModelRef.value.image)
  //   );
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
                  src: dashboardStore.listPlants.find((plant) =>plant.id === option.value)?.image,
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

const previewFileList = ref<UploadFileInfo[]>([
    {
        id: 'pp',
        name: model.value.image || '',
        status: 'finished',
        url: model.value.image
    },
])




import {  NText, NTag, SelectRenderTag, SelectRenderLabel } from 'naive-ui'
import { get } from '@/utils/request'

const loadingSearch = ref(false)
const options = ref<SelectOption[]>(
  model.value.productIds && model.value.productIds.length > 0 
    ? model.value.products.map(product => ({
        label: product.name,
        value: product.id,
        image: product.image,
        description: product.description
      }))
    : []
);




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
    loadingSearch.value = true
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
    loadingSearch.value = false
  } catch (error: any) {
    loadingSearch.value = false
    console.log("error", error)
    throw error
  }
}



</script>

<template>
  <div class="border-none shadow-none flex flex-col gap-2 p-2 rounded-lg">

    <div class="post-heading mb-1">
      <div class="gtext text-2xl font-bold underlined">{{ t('dashboard.editDisease') }}</div>
    </div>
    <NForm
      ref="formRef"
      :model="model"
      :rules="rules"
      size="large"
   
    >
      <div>
        <NGrid :span="24" :x-gap="24">

        <NFormItemGi
              :span="12"
              path="plant"
              :label="t('dashboard.plant')"
            >
              <NSelect
                filterable
                trigger="hover"
                v-model:value="model.plantId"
                :options="selectOption"
                :render-label="renderLabel"
              >
                <NButton>{{ t('dashboard.plant') }}</NButton>
              </NSelect>
            </NFormItemGi>

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

           <!-- Loop for language selection -->
  <template v-for="(languageCode, index) in model.languageCodes" :key="index">
    <NFormItemGi
      :span="12"
      :path="`translationsName[${index}]`"
      :label="`${t('dashboard.nameDiseases')} (${languageCode})`"
    >
      <NInput
        v-model:value="model.translationsName[index]"
        :placeholder="`${t('dashboard.nameDiseases')} (${languageCode})`"
        clearable
        :dir="languageCode == 'ar' ? 'rtl' : ''"
        @keydown.enter.prevent
      />
    </NFormItemGi>
    
    <NFormItemGi
      :span="12"
      :path="`translationsDescription[${index}]`"
      :label="`${t('dashboard.desDiseases')} (${languageCode})`"
    >
      <NInput
        v-model:value="model.translationsDescription[index]"
        :placeholder="`${t('dashboard.desDiseases')} (${languageCode})`"
        clearable
        :dir="languageCode == 'ar' ? 'rtl' : ''"
        @keydown.enter.prevent
        type="textarea"
        :autosize="{ minRows: 2, maxRows: 5 }"
      />
    </NFormItemGi>

    <NFormItemGi
      :span="12"
      :path="`translationsHealthCondition[${index}]`"
      :label="`${t('dashboard.healthCondition')} (${languageCode})`"
    >
      <NInput
        v-model:value="model.translationsHealthCondition[index]"
        :placeholder="`${t('dashboard.healthCondition')} (${languageCode})`"
        clearable
        :dir="languageCode == 'ar' ? 'rtl' : ''"
        @keyup.enter="handleAdded"
        type="textarea"
        :autosize="{ minRows: 2, maxRows: 5 }"
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
        <NButton
          type="primary"
          style="width:100%;"
          size="large"
          :loading="loading"
          :disabled="isButtonDisabled()"
          @click="handleAdded"
        >
          {{ t('dashboard.editDisease') }}
        </NButton>
      </div>

    </NForm>
  </div>
</template>