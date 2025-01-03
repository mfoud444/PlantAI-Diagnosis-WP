<script setup lang='ts'>
import { computed, useAttrs } from 'vue'
import { Icon } from '@iconify/vue'

interface Props {
  icon?: string
}

defineProps<Props>()

const attrs = useAttrs()
const defaultClass = ''
const bindAttrs = computed<{ class: string; style: string }>(() => ({
  class: `${defaultClass} ${(attrs.class as string) || ''}`,
  style: (attrs.style as string) || '',
}))
</script>

<template>

    <Icon
      :icon="icon"
      v-bind="bindAttrs"
    />
 </template>
