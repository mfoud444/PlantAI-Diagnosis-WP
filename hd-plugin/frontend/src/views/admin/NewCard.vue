<script setup lang="ts">
import { ref } from 'vue';
import { NForm, NInput, NButton, FormInst, UploadCustomRequestOptions } from 'naive-ui';
import { t } from '@/locales';
import { useMessage,NAvatarGroup  , NDropdown,NAvatar , NTooltip, NImage, NSwitch} from 'naive-ui';
import { useDashboardStore } from '@/store'
import { useLanguage } from '@/hooks/useLanguage'

import { defineProps } from 'vue'

const options = [
  {
    name: 'Leonardo DiCaprio',
    src: 'https://gw.alipayobjects.com/zos/antfincdn/aPkFc8Sj7n/method-draw-image.svg'
  },
  {
    name: 'Jennifer Lawrence',
    src: 'https://07akioni.oss-cn-beijing.aliyuncs.com/07akioni.jpeg'
  },
  {
    name: 'Audrey Hepburn',
    src: 'https://gw.alipayobjects.com/zos/antfincdn/aPkFc8Sj7n/method-draw-image.svg'
  },
  {
    name: 'Anne Hathaway',
    src: 'https://07akioni.oss-cn-beijing.aliyuncs.com/07akioni.jpeg'
  },
  {
    name: 'Taylor Swift',
    src: 'https://gw.alipayobjects.com/zos/antfincdn/aPkFc8Sj7n/method-draw-image.svg'
  }
]

const createDropdownOptions = (options: Array<{ name: string; src: string }>) =>
  options.map((option) => ({
    key: option.name,
    label: option.name
  }))

import { SvgIcon } from '@/components/common';
const { language } = useLanguage()
const dashboardStore = useDashboardStore()
const message = useMessage();
const formRef = ref<FormInst | null>(null);
const loading = ref(false);
const badgeType = loading.value ? 'success' : 'error';
</script>

<template>
  <div class="border-none  flex flex-col gap-8  rounded-lg bg-rose-100 w-96   shadow-sm justify-between" >
<div class="p-4 text-center">
    <NImage class="rounded-full mx-auto slide-in-fwd-center" width="100" height="100" lazy :src="options[0].src"
                preview-disabled>
                <template #placeholder>
                    <div class="flex items-center justify-center">
                        <div class="loader1"></div>
                    </div>
                </template>
            </NImage>
<div class=" flex justify-between items-center">

 <div class=" gtext font-bold text-3xl"> Apple </div>
 <div class="px-4 inline-flex items-center justify-between gap-1  text-xl font-bold rounded-3xl bg-red-300 text-red-800">
    <SvgIcon icon="carbon:dot-mark"     />
  <span class="">  close</span>
  
</div>
</div>

  <NAvatarGroup :options="options" :size="40" :max="3">
    <template #avatar="{ option: { name, src } }">
      <NTooltip>
        <template #trigger>
          <NAvatar :src="src" />
        </template>
        {{ name }}
      </NTooltip>
    </template>
    <template #rest="{ options: restOptions, rest }">
      <NDropdown :options="createDropdownOptions(restOptions)" placement="top">
        <NAvatar>+{{ rest }}</NAvatar>
      </NDropdown>
    </template>
  </NAvatarGroup>


</div>



    <div class=" grid grid-cols-3  gap-4 bg-blue-600 w-full p-4">
 

      <NButton
      style="background-color: #208000;"
        @click="dashboardStore.showModelAddDiseases = true"
        type="primary"
     
      >
        <div class="flex gap-2  items-center">
          <SvgIcon
            icon="fa-solid:disease"
            class=" text-base"
          />
          <div class="hidden md:block">{{ t('dashboard.addDiseases') }}</div>
        </div>

      </NButton>


      <NButton
      style="background-color: #208000;"
        @click="dashboardStore.showModelAddModelAI = true"
        type="primary"
    
      >
        <div class="flex gap-2   items-center">
          <SvgIcon
            icon="eos-icons:ai-healing"
            class=" text-base text-white"
          />
          <div class="hidden md:block">{{ t('dashboard.modelAI') }}</div>
        </div>

      </NButton>

      <NButton
      style="background-color: red; color:white;"
        strong
        secondary
        type="error"
  
      >
        <div class="flex gap-2 items-center">
          <SvgIcon
            icon="fluent:delete-32-regular"
            class=" text-base"
          />
          <div class="hidden md:block">{{ t('common.delete') }}</div>
        </div>

      </NButton>
</div>
  </div>
</template>