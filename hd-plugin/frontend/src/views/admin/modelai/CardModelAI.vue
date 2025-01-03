<script setup lang='ts'>
import { ref, computed } from 'vue'

import { useBasicLayout } from '@/hooks/useBasicLayout';
import { NEllipsis } from 'naive-ui'

interface Props {
  row: Dashboard.ModelAI
}

const props = defineProps<Props>()
const row = computed(() => props.row)
const version = computed(() => row.value.version)
const badgeType = row.value.status ? 'success' : 'error';
const { isMobile } = useBasicLayout()
</script>

<template>
  <div class="flex gap-4 item-center">


    <div class="flex flex-col">
      <div class="font-bold text-base">
        <NEllipsis :line-clamp="1">
          {{ row.name }}
          <template #tooltip>
            <div class="w-36">
              {{ row.name }}
            </div>
          </template>
        </NEllipsis>
      </div>


      <!-- <div class="">
        <NEllipsis :line-clamp="1">
          version :  {{ version }}
          <template #tooltip>
            <div class="w-36">
            {{ version}}
            </div>
          </template>
        </NEllipsis>
      </div> -->
</div>
  </div>
</template>
