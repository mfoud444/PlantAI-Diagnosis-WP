<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { t } from '@/locales';
import { useMessage, NUpload, UploadCustomRequestOptions, NModal, NButton, NResult, NImage } from 'naive-ui';
import { useAppStore, useDashboardStore, usePredictionStore } from '@/store'
import axios from 'axios';
import * as tf from '@tensorflow/tfjs';
import Tips from './Tips.vue'
import { useBasicLayout } from '@/hooks/useBasicLayout';
import { baseImageUrl } from '@/utils/request/axios';

import { SvgIcon } from '@/components/common';
import modelJson from './model.json'
const appStore = useAppStore()
const showModal = ref(false)
const nextStep = ref(false)
const { isMobile } = useBasicLayout()
const predictionStore = usePredictionStore()
const dashboardStore = useDashboardStore()
const loading = ref(false);
const loadingPrediction = ref<boolean>(false);
const notFoundDiagnosis = ref<boolean>(false);
const imageNotPlant = ref<boolean>(false);
const message = useMessage()
const currentPlant = computed(() => predictionStore.currentPlants)
const currentPlantId = currentPlant.value?.id;

const currentDiseases = computed(() => {
    if (currentPlantId !== undefined) {
        const currentDisease = dashboardStore.getListDiseasesByIdPlant(currentPlantId);

        return currentDisease
    } else {
        return [];
    }
});


const labels = computed(() => {
    if (currentPlant.value) {
        const diseases = currentDiseases.value
        let listLabels = []
        for (const disease of diseases) {
            listLabels.push(disease.name)
        }
        return listLabels;
    }
    return ['']

})

const modelAI = computed(() => {
    if (currentPlant.value) {
        return dashboardStore.getListModelAIByIdPlant(currentPlant.value.id!);
    }
})

let predictionResult = ref(null);
function correctLabelPrediction(prediction: { [key: string]: number }) {
    const maxKey = Object.keys(prediction).reduce((maxKey, currentKey) => {
        return prediction[currentKey] > prediction[maxKey] ? currentKey : maxKey;
    })
    const missingKeys = Object.keys(prediction).filter((key) => !labels.hasOwnProperty(key));
    if (missingKeys.length > 0) {
        console.error(`Missing labels for keys: ${missingKeys.join(', ')}`);
        // return { label: "", maxValue: -Infinity }; 
    }

    const correctLabel = labels.value[maxKey];
    const maxValue = prediction[maxKey] * 100;

    console.log(`Predicted label: ${correctLabel} (key: ${maxKey})  ${maxValue} `);

    predictionStore.correctDisease = currentDiseases.value[maxKey]
    console.log("predictionStore.correctDisease ", predictionStore.correctDisease)
    predictionStore.correctPredictedProbability = maxValue

}


async function predict(ImageFile:any) {
    try {

        if (modelAI.value?.modelJsonFile && modelAI.value?.modelWeightsFile) {
            console.log("ddd", modelAI.value.modelJsonFile, modelAI.value?.modelWeightsFile)


            const modelPlantorNot = await tf.loadLayersModel(baseImageUrl + "model.json");

            const img = new Image();
            img.src = URL.createObjectURL(ImageFile);
           
            await new Promise(resolve => {
                img.onload = resolve;
            });

            const tensor = tf.browser.fromPixels(img).resizeNearestNeighbor([224, 224]).toFloat();
            const offset = tf.scalar(127.5);
            const normalized = tensor.sub(offset).div(offset).expandDims();
            const predictionPlantorNot = await modelPlantorNot.predict(normalized).data();
            console.log("prediction", predictionPlantorNot[0])
            if (predictionPlantorNot[0] > 0.5) {
                const model = await tf.loadLayersModel(modelAI.value?.modelJsonFile);
                const prediction = await model.predict(normalized).data();
                correctLabelPrediction(prediction)
                console.log(prediction)
                return prediction;
            } else {
                throw new Error('Prediction is less than or equal to 50%');

            }

        } else {
            throw new Error('Error predicting: ');
        }
    } catch (error: any) {
        console.log(error.message)
        throw new Error('Error predicting: ' + error.message);
    }
}


const customRequest = async ({
    file,
    data,
    onProgress,
    onFinish,
    onError
}: UploadCustomRequestOptions) => {

    predictionResult.value = null
    loading.value = true;
    loadingPrediction.value = true;
    const formData = new FormData();
    const img = new Image();
    if (data) {
        Object.keys(data).forEach((key) => {
            formData.append(key, data[key as keyof UploadCustomRequestOptions['data']]);
        });
    }
    if (file.file !== null) {
       
            img.src = URL.createObjectURL(file.file as File);
            predictionStore.imgSrc = img.src;
          
        formData.append('image', file.file);

    }

    try {
        // await loadMetadata();
        const prediction = await predict(file.file as File);
        predictionResult.value = prediction;
        console.log(predictionResult.value)

        formData.append('plant_id', currentPlantId!);
        formData.append('correct_disease_id', predictionStore.correctDisease?.id!);
        console.log("predictionStore.correctDisease?.id", predictionStore.correctDisease?.id)
        console.log('plant_id', currentPlantId!)
        console.log('model_id', modelAI.value?.id!)

        if (predictionStore.correctPredictedProbability) {
            formData.append('prediction_result_value', predictionStore.correctPredictedProbability?.toFixed(0));
            console.log('prediction_result_value', predictionStore.correctPredictedProbability?.toFixed(0))
        }

        formData.append('user_id', appStore.userId);
        formData.append('model_id', modelAI.value?.id!);
        if (typeof predictionStore.correctDisease?.id !== "undefined") {
            console.log("predictionStore.correctDisease?.id", predictionStore.correctDisease?.id)
            await predictionStore.insertActionHistory(formData)
            loading.value = false;
            loadingPrediction.value = false;
            predictionStore.currentStep = 2

        } else {
            notFoundDiagnosis.value = true
            loading.value = false;
            loadingPrediction.value = false;
        }



    } catch (error: any) {
        if (error.message == 'Error predicting: Prediction is less than or equal to 50%') {

            imageNotPlant.value = true;

            loading.value = false;
            loadingPrediction.value = false;
        } else {
            console.log('error.message)', error.message);
            message.error(error.message);
            loading.value = false;
            loadingPrediction.value = false;
        }
    }
}



async function getDataAsync() {
    try {
        loading.value = true
        await dashboardStore.getAllDiseases(currentPlant.value?.id)
        await dashboardStore.getAllModelAI()
        loading.value = false
    } catch (error: any) {
        message.error(t('common.errorSomeThing') + " " + error.message);
        console.error(t('common.errorSomeThing'), error.message);
        loading.value = false
    }
}



onMounted(async () => {
    if( currentDiseases.value.length > 0 &&  modelAI.value ){
        loading.value = false 
      
    } else{
        getDataAsync();
    }
  
})


function getDescription() {
    if (notFoundDiagnosis.value) {
        return t('common.notFoundDiagnosis');
    } else if (imageNotPlant.value) {
        return t('common.imageNotPlant');
    } else {
        // Return a default description if neither notFoundDiagnosis nor imageNotPlant is true
        return t('common.defaultDescription');
    }
}
const imgSrc = computed(() => predictionStore.imgSrc)
</script>

<template>

    <div v-if="(!loading)" class=" md:grid md:grid-cols-2 rounded  items-center">
        <template v-if="!notFoundDiagnosis && !imageNotPlant">
            <div class="flex flex-col gap-2 self-start p-4">
                <div class="post-heading mb-1">
                    <div class="gtext text-2xl font-bold underlined">{{ t('steps.titleStep1') }}</div>
                </div>
                <div class=" self-start flex flex-col gap-2 my-4">


                    <div v-if="isMobile" class=" bg-red-100 rounded-lg px-3 py-2 text-base">
                        <NButton @click="showModal = true">
                            {{ t('tips.howTakeImage') }}
                        </NButton>
                    </div>


                    <NUpload accept="image/*" :max=1 path="image" :custom-request="customRequest">

                        <NButton style="background-color: #208000; padding: 25px 10px;  width: 300px ; font-weight: bold;" type="primary">
                            {{ t('common.uploadImage') }}
                            <template #icon>
                                <SvgIcon icon="mage:image-upload" class="text-3xl" />
                            </template>
                        </NButton>
                    </NUpload>
                </div>
            </div>

            <template v-if="isMobile">
                <NModal v-model:show="showModal" preset="dialog" :title="t('tips.header')">
                    <Tips />
                </NModal>
            </template>


            <template v-if="!isMobile">
                <Tips />
            </template>
        </template>

        <div v-if="notFoundDiagnosis || imageNotPlant"
            class="flex flex-col gap-6 justify-center items-center col-span-2 py-8">
            <NResult status="info" :title="t('common.sorry')" :description="getDescription()">
                <template #footer>
                    <div class="space-y-7 flex flex-col justify-center">
                        <NImage class="rounded-lg slide-in-fwd-center self-center" width="250" height="250" lazy
                            :src="imgSrc" preview-disabled>
                            <template #placeholder>
                                <div class="flex items-center justify-center">
                                    <div class="loader1"></div>
                                </div>
                            </template>
                        </NImage>
                        <NButton style="background-color: #208000;"
                            @click="notFoundDiagnosis = false; imageNotPlant = false" type="primary">
                            {{ t('common.tryAgain') }}
                        </NButton>
                    </div>
                </template>

            </NResult>
            <Tips v-if="imageNotPlant" />
        </div>


    </div>

    <div v-if="loadingPrediction || loading" class="flex flex-col justify-center items-center h-full">

        <div class="">


        </div>

        <div class=" space-y-7" v-if="loadingPrediction">
           
            <NImage class="rounded-lg slide-in-fwd-center" width="250" height="250" lazy :src="imgSrc" preview-disabled>
                <template #placeholder>
                    <div class="flex items-center justify-center">
                        <div class="loader1"></div>
                    </div>
                </template>
            </NImage>
            <div class="text-2xl font-bold gtext">{{ t('app.diagnosisProgress') }}</div>
        </div>

        <div v-if="loading" class="spinner mt-8"></div>
    </div>




</template>
