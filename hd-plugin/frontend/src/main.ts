import { createApp } from 'vue'
import App from './App.vue'
import { setupI18n } from './locales'
import { setupAssets, setupScrollbarStyle } from './plugins'
import { setupStore } from './store'
import { setupRouter } from './router'
import { useAppStore } from '@/store'
async function bootstrap() {
  const app = createApp(App)
  setupAssets()

  setupScrollbarStyle()

  setupStore(app)

  setupI18n(app)

  await setupRouter(app)
  const appStore = useAppStore()

  const rootElement = document.getElementById('app')
  if (rootElement) {
    const lang = rootElement.getAttribute('data-lang')
    const userId = rootElement.getAttribute('data-user-id')

    if (userId ) {
      appStore.userId = userId
    }
    if (lang) {
      if( lang === 'ar') {
        appStore.setLanguage('ar-DZ')
      } else {
        appStore.setLanguage('en-US')
      }
    
    }
//console lang
    console.log("lang1",lang)
  }
  app.mount('#app')
}

bootstrap()
