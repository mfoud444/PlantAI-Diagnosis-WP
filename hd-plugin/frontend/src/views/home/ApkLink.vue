<script lang="ts" setup>
import GooglePlay from '@/assets/googleplay.png'
import App_store_ARA from '@/assets/app_store_ARA.webp'
import mobileApp from '@/assets/mobileApp.svg'
import { t } from '@/locales'
import { baseImageUrl } from '@/utils/request/axios'
</script>
<template>
  <div class=" bg-purple-200 dark:bg-rose-400 rounded-lg  gap-y-8 py-8 place-content-center justify-items-center place-items-center w-full">

  <div
    class="grid px-8 grid-cols-2  gap-y-8 py-8 place-content-center justify-items-center place-items-center w-full">

    <div class="flex flex-col justify-around">
      <div class=" font-bold  text-base lg:text-3xl pb-2 gtext">
        {{  t('app.apkHead')}}
      </div>

      <div class=" font-bold text-base  ">
        {{ t('app.apkDes') }}

      </div>
  
    </div>


    <img class=" w-48" :src="baseImageUrl +mobileApp.split('/').pop()" alt="mobileApp">


  </div>

  <div class=" flex gap-8 justify-center mt-4">
        <a href="https://play.google.com/store/apps/details?id=com.slyd4r.worldofplants" target="_blank">
          <img class="w-36 h-22" :src="GooglePlay" alt="Google Play">
        </a>
        <a href="https://apps.apple.com/us/app/%D8%B9%D8%A7%D9%84%D9%85-%D8%A7%D9%84%D9%86%D8%A8%D8%A7%D8%AA%D8%A7%D8%AA/id6479358008" target="_blank">
          <img class="w-36 h-22" :src="baseImageUrl + App_store_ARA.split('/').pop()" alt="App_store_ARA">
        </a>
    
      </div>
    </div>
</template>