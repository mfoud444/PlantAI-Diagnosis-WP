<script setup lang='ts'>
import {  computed } from 'vue'
import { useBasicLayout } from '@/hooks/useBasicLayout';
import {  NAvatar, NBadge, NEllipsis } from 'naive-ui'
import { useLanguage } from '@/hooks/useLanguage'

const { language } = useLanguage()


interface Props {
  row: Dashboard.Diseases
}

const props = defineProps<Props>()
const row = computed(() => props.row)
const imageUrl = computed(() => row.value.image)
const name = computed(() => row.value.translationsName[language.value.name === 'ar-DZ' ? 1 : 0])

const badgeType = row.value.status ? 'success' : 'error';
const { isMobile } = useBasicLayout()
</script>

<template>
  <div class="flex gap-4 item-center">
    <NBadge
      dot
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
