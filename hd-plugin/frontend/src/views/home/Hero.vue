<script lang="ts" setup>
import { t } from '@/locales'
import { baseImageUrl } from '@/utils/request/axios';
import {  NButton} from 'naive-ui'
import { usePredictionStore } from '@/store'
const predictionStore = usePredictionStore()
import Hero from '@/assets/hero/dt.jpg'

async function goStart() {
  predictionStore.currentStep = 0

}
</script>

<template>
  <div class="flex flex-row   justify-around  items-center">
    <div class="">
      <div class="relative">
        <img class=" h-96" :src="baseImageUrl+ Hero.split('/').pop()" />
      </div>
    </div>
    <div>      
      <div class="font-bold text-blue-800 text-4xl py-4">{{ t('app.headApp') }}</div>
      <div class="text-xl py-4 w-96">{{ t('app.description') }}</div>
      <NButton style="width: 70%; padding-top: 30px; padding-bottom: 30px; background-color: #208000;" @click="goStart" type="primary">
        {{ t('common.start') }}
      </NButton>
    </div>
  </div>
</template>
