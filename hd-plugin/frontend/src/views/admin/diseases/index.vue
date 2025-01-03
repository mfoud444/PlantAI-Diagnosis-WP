<script
    setup
    lang='ts'
>
import { t } from '@/locales';
import List from './List.vue'
import { useDashboardStore } from '@/store'

import { onMounted, ref,computed } from 'vue';
const dashboardStore = useDashboardStore()
const showModelAdd =  computed(() => dashboardStore.showModelAdd)  
const plants = computed(() => dashboardStore.listPlants)
const loading = ref(true)
onMounted(async () => {
  await dashboardStore.getAllPlantAction()
  loading.value = false
  console.log(plants.value)
})


</script>

<template>
    <List/>

</template>

