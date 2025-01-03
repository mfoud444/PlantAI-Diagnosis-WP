<script setup lang='ts'>
import {  computed } from 'vue'
import { useBasicLayout } from '@/hooks/useBasicLayout';
import { NAvatar, NBadge, NEllipsis } from 'naive-ui'
import { useLanguage } from '@/hooks/useLanguage'

const { language } = useLanguage()
interface Props {
  row: Dashboard.Plant
}

const props = defineProps<Props>()
const name = computed(() => row.value.translations[language.value.name === 'ar-DZ' ? 1 : 0])
const row = computed(() => props.row)
const imageUrl = computed(() => {
  if (typeof row.value.image === 'string' && row.value.image !== '') {
    return row.value.image;
  }
  // If the conditions are not met, you might want to return a default image URL or null
  return false; // Or null, or any other default value
});

console.log(props.row)
const badgeType = row.value.status ? 'success' : 'error';
const { isMobile } = useBasicLayout()
</script>

<template>
  <div 
  class="flex flex-wrap items-center  gap-4 "    
  :class="isMobile ? 'flex-col  ' : '' ">
    <NBadge
      dot
      v-if="imageUrl"
      :processing=false
      :type='badgeType'
    >
      <NAvatar
        round
        size="medium"
        :src="imageUrl"
      />
    </NBadge>

    <div class="flex flex-col">
      <div class="font-bold text-base">
        <NEllipsis :line-clamp="1">
          {{ name }}
          <template #tooltip>
            <div class="w-36">
              {{ name }}
            </div>
          </template>
        </NEllipsis>
      </div>
</div>
  </div>
</template>
