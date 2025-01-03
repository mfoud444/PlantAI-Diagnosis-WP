<script setup lang="ts">
import { NConfigProvider } from 'naive-ui'
import { NaiveProvider } from '@/components/common'
import { useTheme } from '@/hooks/useTheme'
import { useLanguage } from '@/hooks/useLanguage'
const { theme, themeOverrides } = useTheme()
const { language } = useLanguage()
</script>

<template>
   
  <NConfigProvider

    :theme="theme"
    :theme-overrides="themeOverrides"
    :locale="language"
    :dir="language.name === 'ar-DZ' ? 'rtl' : 'ltr'"
  >
    <NaiveProvider>
      <RouterView />
    </NaiveProvider>
  </NConfigProvider>

</template>
