<script lang="ts" setup>
import Hero from './Hero.vue'
import ApkLink from './ApkLink.vue'
import ListPlants from './ListPlants.vue'
import Steps from './Steps/index.vue'
import { usePredictionStore } from '@/store'
import { computed } from 'vue'
const predictionStore = usePredictionStore()
const current =  computed(() => predictionStore.currentStep)
const  currentPlants = computed(() => predictionStore.currentPlants) 
</script>

<template>
  <div class="container pb-1 pt-8  dark:bg-violet-950" >
      <main class="relative flex min-h-[70vh] flex-col gap-4">
        <template v-if="current === null">
          <Hero/>
          <ApkLink/>
        </template>
        <ListPlants v-if="current === 0 && currentPlants === null"/>
        <Steps v-if="currentPlants !== null" />
      </main>
  </div>

</template>
