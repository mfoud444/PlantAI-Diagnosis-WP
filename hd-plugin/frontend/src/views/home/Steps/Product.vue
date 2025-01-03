<script setup lang='ts'>
import {  computed } from 'vue'
import { t } from '@/locales';
import { NImage,  NBadge } from 'naive-ui'

interface Props {
    row: Dashboard.Product
}

const props = defineProps<Props>()
const row = computed(() => props.row)
const badgeSales = row.value.onSale ? 'Sales' : '';

</script>
<template>
    <div class=" flex flex-col p-4 max-w-[370px]  bg-green-200 rounded-lg items-center gap-4">

        <NBadge :value="badgeSales" :processing=false type="warning">
            <NImage class="rounded-lg slide-in-fwd-center" width="250" height="250" lazy :src="row.image"
                preview-disabled>
                <template #placeholder>
                    <div class="flex items-center justify-center">
                        <div class="loader1"></div>
                    </div>
                </template>
            </NImage>
        </NBadge>

        <div class=" font-bold text-xl">{{ row.name }}</div>
        <div class=" font-bold text-xl text-left" v-html="row.priceHtml"></div>

        <a :href="row.permalink" class="no-underline rounded-lg p-2  mx-4  font-bold text-center w-52 text-white hover:text-yellow-300 cursor-pointer" style="background-color: #208000;">
      {{ t('common.addToCart') }}
    </a>

    </div>
</template>
