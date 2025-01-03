<script setup lang='ts'>
import { ref, computed, onMounted } from 'vue'
import { NImage, useMessage, NResult,NSkeleton } from 'naive-ui'
import { t } from '@/locales';
import { useDashboardStore, usePredictionStore } from '@/store'
const loading = ref(true)
const notFoundPlant = ref(false)
const dashboardStore = useDashboardStore()
const predictionStore = usePredictionStore()
const message = useMessage();
const plants = computed(() => {
    const listPlants: Dashboard.Plant[] = []
    dashboardStore.listPlants.forEach((plant) => {
        const modelAI = dashboardStore.getListModelAIByIdPlant(plant.id!);
        if (modelAI) {
            listPlants.push(plant)
        }
    })

    return listPlants

})
function setCurrentPlant(plant: Dashboard.Plant) {
    predictionStore.currentPlants = plant
    predictionStore.currentStep = 1
}
async function getDataAsync() {
    try {
        await dashboardStore.getAllPlantAction()
        await dashboardStore.getAllModelAI()
        loading.value = true
        loading.value = false
        if (plants.value.length <= 0) {
            notFoundPlant.value = true
        }
    } catch (error: any) {
        message.error(t('common.errorSomeThing') + " " + error.message);
        console.error(t('common.errorSomeThing'), error.message);
        loading.value = false

    }
}
onMounted(async () => {
    getDataAsync();
})

</script>

<template>

    <div class="font-bold text-2xl text-center  md:text-3xl gtext mb-3">
        {{ t('steps.whatIsPlant') }}
    </div>

    <div class="flex flex-wrap justify-center  items-center gap-4">

        <div class="spinner mt-8" v-if="loading"></div>

        <div v-for="plant in plants" :key="plant.id" @click="setCurrentPlant(plant)"
            class="flex w-48 h-56 cursor-pointer flex-col  justify-around gap-4 item-center bg-green-100 px-4 py-2 rounded-lg">

            <NImage v-if="typeof plant.image === 'string' && plant.image !== ''" class="rounded-full mx-auto slide-in-fwd-center" width="100" height="100" lazy :src="plant.image"
                preview-disabled>
                <template #placeholder>
                    <!-- <NSkeleton  :width="300" height="150px" :sharp="false" size="medium" /> -->
                    <div class="flex items-center justify-center">
              <div class="loader1"></div>
            </div>
                </template>
            </NImage>


            <div class="font-bold text-center text-xl">
                {{ plant.name }}
            </div>


        </div>
    </div>


    <div v-if="notFoundPlant" class="flex flex-col gap-6 justify-center items-center col-span-2 py-8">
        <NResult status="info" :title="t('common.sorry')" :description="t('common.notFoundPlant')">
        </NResult>
    </div>

</template>
