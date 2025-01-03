<script
    setup
    lang='ts'
>
import { useDashboardStore } from '@/store'
import { ref, onMounted, computed } from 'vue'
import { SvgIcon } from '@/components/common';
const dashboardStore = useDashboardStore()
const loading = ref(true)
import { t } from '@/locales';
import {

  useMessage
} from 'naive-ui'

const data = computed(() => dashboardStore.statistics)
const message = useMessage();
async function getDataAsync() {
  try {
    loading.value = true
    await dashboardStore.getStatisticsAction()
    loading.value = false

  } catch (error: any) {
    message.error(t('common.errorSomeThing') + " " + error.message);
    console.error(t('common.errorSomeThing'), error.message);

    loading.value = false

  }
}



onMounted(async () => {

  if(dashboardStore.listPlants.length == 0 ){
    getDataAsync();
  } else {
    loading.value = true
    loading.value = false
 
  }


})
const Information = computed(() => [
{
      title: t('dashboard.totalPlant'),
      total: data.value?.plants,
      icon: 'ph:plant-fill'
    },
    {
      title: t('dashboard.totalDiseases'),
      total: data.value?.diseases,
      icon: 'fa-solid:disease'
    },
    {
      title: t('dashboard.totalModel'), 
      total: data.value?.modelAi,
      icon: 'eos-icons:ai-healing'
    },
    {
      title:  t('dashboard.totalOperation'),  
      total: data.value?.history,
      icon: 'material-symbols:history'
    }
    
]
  )

</script>

<template>
    <div class="flex mt-8 gap-4 items-center flex-wrap justify-center">


      <div
        v-if="loading"
        class="flex flex-col justify-center items-center h-full"
    >

        <div class="spinner mt-8"></div>
    </div>

      <div v-else v-for="info in Information" :key="info.title" class="flex flex-col">
        <div class="bg-green-500 h-[150px] text-white rounded-lg w-64 p-4 flex flex-col gap-4">
          <div class="flex gap-4 text-base  md:text-2xl items-center font-bold">
            <SvgIcon :icon="info.icon" class="text-base" />
            <div>{{ info.title }}</div>
          </div>
          <div class="text-2xl bg-white text-blue-900 rounded-xl  md:text-3xl text-center font-bold">{{ info.total }}</div>
        </div>
      </div>

    </div>
  </template>
