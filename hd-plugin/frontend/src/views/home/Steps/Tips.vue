<script
    setup
    lang="ts"
>

import { t } from '@/locales';
import { SvgIcon } from '@/components/common'
import { NGrid, NGridItem, NImage, NBadge } from 'naive-ui';
import { baseImageUrl } from '@/utils/request/axios';
import Advance from '@/assets/advance.png'
import warringImage1 from '@/assets/wirring1.png'
import warringImage2 from '@/assets/wirring2.png'
import warringImage3 from '@/assets/wirring3.png'
import warringImage4 from '@/assets/wirring4.png'
const advance =   { title: t('tips.advance'), image: baseImageUrl+  Advance.split('/').pop() }

const items = [
  { title: t('tips.wirring1'), image: baseImageUrl+ warringImage1.split('/').pop() },
  { title: t('tips.wirring2'), image: baseImageUrl+  warringImage2.split('/').pop() },
  { title: t('tips.wirring3'), image: baseImageUrl+ warringImage3.split('/').pop() },
  { title:  t('tips.wirring4'), image: baseImageUrl+ warringImage4.split('/').pop() },
];
</script>

<template>
 
<div class=" p-2">

<div class="font-bold text-center py-4  text-3xl gtext mb-2">
                {{ t('tips.header') }}
            </div>
        

    <NGrid
        :x-gap="12"
        :y-gap="8"
        :cols="2"
    >
    <NGridItem :span="2">
      <div class="flex flex-col justify-center items-center gap-2">
        <NBadge type="success">
          <template #value>
        <SvgIcon icon="icon-park-outline:correct"   /> 
      </template>
        <NImage
          class="rounded-lg slide-in-fwd-center"
          width="100"
          height="100"
          lazy
          :src="advance.image"
          preview-disabled
        >
    
  


          <template #placeholder>
            <div class="flex items-center justify-center">
              <div class="loader1"></div>
            </div>
          </template>
        </NImage>
      </NBadge>
        <div class="text-center">{{ advance.title }}</div>
      </div>
    </NGridItem>



    <NGridItem v-for="(item, index) in items" :key="index">
      <div class="flex flex-col justify-center items-center gap-2">
        <NBadge>
          <template #value>
        <SvgIcon icon="mdi:cancel-outline"  /> 
      </template>
        <NImage
          class="rounded-lg slide-in-fwd-center"
          width="100"
          height="100"
          lazy
          :src="item.image"
          preview-disabled
        >
    
  


          <template #placeholder>
            <div class="flex items-center justify-center">
              <div class="loader1"></div>
            </div>
          </template>
        </NImage>
      </NBadge>
        <div class=" text-center">{{ item.title }}</div>
      </div>
    </NGridItem>
    </NGrid>
  </div>

</template>