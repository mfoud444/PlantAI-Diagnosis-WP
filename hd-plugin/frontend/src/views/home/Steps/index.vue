<script setup lang="ts">
import { t } from '@/locales'
import { ref, defineProps, computed, watch } from 'vue'
import { StepsProps, NButton, NButtonGroup, NSpace, NSteps, NStep } from 'naive-ui'
import { SvgIcon } from '@/components/common'
import Step1 from './Step1.vue'
import Step2 from './Step2.vue'
import Step3 from './Step3.vue'
const props = defineProps(['initialStep', 'initialStatus'])
import { usePredictionStore } from '@/store'
import { useBasicLayout } from '@/hooks/useBasicLayout'
const predictionStore = usePredictionStore()
const steps = computed(() => [
    { title: t('steps.titleStep1'), description: t('steps.descriptionStep1'), icon: "arcticons:photo-pro" },
    { title: t('steps.titleStep2'), description: t('steps.descriptionStep2'), icon: "material-symbols:diagnosis-outline" },
    { title: t('steps.titleStep3'), description: t('steps.descriptionStep3'), icon: "icon-park:medicine-bottle-one" }

])

const current = ref(predictionStore.currentStep)
const currentStatus = ref<StepsProps['status']>(props.initialStatus)

const { isMobile } = useBasicLayout()
function next() {
    if (current.value === null) { predictionStore.currentStep = 1 }
    else if (current.value >= 3) { predictionStore.currentStep = null }
    else {
        if (predictionStore.currentStep !== null) {
            predictionStore.currentStep++
        }


    }
}

function prev() {
    if (current.value === 0) predictionStore.currentStep = null
    else if (current.value === null) predictionStore.currentStep = 3
    else {
        if (predictionStore.currentStep !== null) {
            predictionStore.currentStep--
        }

    }
}

const getDisplayStep = computed(() => {
    if (predictionStore.currentStep === 1) {
        return true
    }

    else {
        return false
    }
});

watch(() => predictionStore.currentStep, (newValue) => {
    current.value = newValue;
});
</script>
<template>
    <div class="flex flex-col gap-2">
        <div class="mb-3">
            <NSteps v-if="!isMobile" :vertical="isMobile" v-model:current="current" :status="currentStatus">
                <template v-for="(step, index) in steps" :key="index">
                    <NStep :disabled="getDisplayStep" :title="step.title" :description="step.description">
                        <template #icon>
                            <SvgIcon :icon="step.icon" />
                        </template>
                    </NStep>

                </template>

            </NSteps>
        </div>


        <div>
            <Step1 v-if="current == 1" />
            <Step2 v-if="current == 2" />
            <Step3 v-if="current == 3" />

        </div>

        <NSpace v-if="current !== 1">
            <NButtonGroup>
                <NButton :disabled="current === 1" @click="prev">
                    <template #icon>
                        <SvgIcon icon="icon-park-outline:left" />
                    </template>
                </NButton>
                <NButton :disabled="(current === 3 || current === 1)" @click="next">
                    <template #icon>
                        <SvgIcon icon="icon-park-outline:right" class="h-5 w-5 md:w-7 md:h-7" />
                    </template>
                </NButton>
            </NButtonGroup>
        </NSpace>
    </div>
</template>