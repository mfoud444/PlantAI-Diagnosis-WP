<script setup lang='ts'>
import { ref, onMounted, onUpdated, computed } from 'vue'
import { NResult, NButton } from 'naive-ui'
import { t } from '@/locales';
import { get } from '@/utils/request';
import Product from './Product.vue'
import { useDashboardStore, usePredictionStore } from '@/store'
const predictionStore = usePredictionStore()
const products = computed(() => predictionStore.products)
const loading = ref(true)
const correctDisease = computed(() => predictionStore.correctDisease)
async function getProduct(): Promise<void> {
    try {

    const data = {
            'disease_id': correctDisease.value.id
        }

      
        const response = await get<any>({
            url: 'product-disease/',
            data
        })
        predictionStore.products = response

        console.log("products", response)
        loading.value = false
    } catch (error: any) {
        loading.value = false
        console.log("error", error)
        throw error;
    }
}
onMounted(async () => {
    if(predictionStore.products.length === 0){
        getProduct();
    } else{
        loading.value = false
    }

})

onUpdated(async () => {
    if(predictionStore.products.length === 0){
        getProduct();
    } else{
        loading.value = false
    }
})
</script>
<template>


    <div v-if="loading" class="flex flex-col justify-center items-center min-h-[50vh]">
        <div class="spinner mt-8"></div>
    </div>


    <div v-if="!loading && products.length === 0" class="p-4 rounded w-full flex flex-col text-base min-h-[50vh]">
        <NResult status="info" :title="t('common.sorry')" :description="t('common.notFoundAnyProduct')">
            <template #footer>
                <NButton @click="getProduct()" type="primary">
                    {{ t('common.back') }}
                </NButton>
            </template>

        </NResult>
    </div>

    <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-5">
        <template v-for="(product, index) in products">

            <Product :row="product" />
        </template>
    </div>


</template>
