import { computed } from 'vue'
import { enUS, arDZ } from 'naive-ui'
import { useAppStore } from '@/store'
import { setLocale } from '@/locales'

export function useLanguage() {
  const appStore = useAppStore()

  const language = computed(() => {
    switch (appStore.language) {
      case 'en-US':
        setLocale('en-US')
        return enUS

      case 'ar-DZ':
          setLocale('ar-DZ')
          return arDZ
      default:
        setLocale('en-US')
        return enUS
    }
  })

  return { language }
}
