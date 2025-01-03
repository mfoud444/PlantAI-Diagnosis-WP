import { ss } from '@/utils/storage'

const LOCAL_NAME = 'appSetting'

export type Theme = 'light' | 'dark' | 'auto'

export type Language = 'zh-CN' |'ar-DZ'| 'zh-TW' | 'en-US' | 'ko-KR' | 'ru-RU' 

export interface AppState {
  siderCollapsed: boolean
  theme: Theme
  language: Language
  labelPlacement:string
  userId:string
  
}

export function defaultSetting(): AppState {
  return { 
    siderCollapsed: false,
     theme: 'light',
      language: 'en-US',
      labelPlacement:"top",
      userId:""
    }
}

export function getLocalSetting(): AppState {
  const localSetting: AppState | undefined = ss.get(LOCAL_NAME)
  return { ...defaultSetting(), ...localSetting }
}

export function setLocalSetting(setting: AppState): void {
  ss.set(LOCAL_NAME, setting)
}
