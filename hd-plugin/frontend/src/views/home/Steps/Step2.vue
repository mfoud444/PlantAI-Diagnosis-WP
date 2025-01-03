<script
    setup
    lang="ts"
>
import { computed } from 'vue';
import { t } from '@/locales';
import { usePredictionStore } from '@/store'
import { SvgIcon } from '@/components/common'
import { NImage, NProgress } from 'naive-ui'
const predictionStore = usePredictionStore()
const correctDisease = computed(() => predictionStore.correctDisease)
const correctPredictedProbability = computed(() => predictionStore.correctPredictedProbability)
const imgSrc = computed(() => predictionStore.imgSrc)
console.log(imgSrc.value)
</script>

<template>
    <div class=" p-4 rounded w-full flex flex-col md:grid md:grid-cols-2 gap-5 text-base h-full">

        <div>
        <div class="">

            <NImage
                class="rounded-lg slide-in-fwd-center"
                width="250"
                height="250"
                lazy
                :src="imgSrc"
                preview-disabled
            >
                <template #placeholder>
                    <div class="flex items-center justify-center">
                        <div class="loader1"></div>
                    </div>
                </template>
            </NImage>
        </div>


        <div class="flex items-center gap-2">
            <div class="text-base font-bold">{{ t('app.resultPrediction') }}</div>
            <NProgress
             type="circle"  
            style="width: 50px; margin: 0 8px 12px 0"  
            :percentage="correctPredictedProbability?.toFixed(0)">
                <div style="text-align: center">{{correctPredictedProbability?.toFixed(0)}}%</div>
            </NProgress>
        </div>


    

        <div class="flex items-center gap-2">
            <div class=" font-bold text-xl"> {{ t('app.itDisease') }}</div>
            <div class=" bg-blue-500 rounded-full px-2 text-white">{{ correctDisease?.name }}</div>
        </div>


    </div>

<div class="flex flex-col justify-around">

        <div class=" flex flex-col gap-2">
            <div class=" text-yellow-500 font-bold flex items-center gap-3 ">
                <div>
                    <SvgIcon icon="pajamas:status-health" />
                </div>
                <div class="text-xl">
                    {{ t('app.healthStatus') }}
                </div>
            </div>
            <div class=" font-bold">
                {{ correctDisease?.name }}
            </div>
            <div>
           {{ correctDisease?.healthCondition }}
            </div>
        </div>



        <div class=" flex flex-col gap-2">
            <div class=" text-yellow-500 font-bold flex items-center gap-3 ">
                <div>
                    <SvgIcon icon="material-symbols:description" />
                </div>
                <div class="text-xl">
                    {{ t('app.descriptionOfTheDisease') }}
                    
                </div>
            </div>
            <div>
             
             {{ correctDisease?.description }}
            </div>
        </div>

        </div>

    </div>
</template>
