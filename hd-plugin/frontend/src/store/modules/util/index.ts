import { defineStore } from 'pinia'
import { store } from '@/store/helper'
type UtilState = {
    code: number
}
function getDefaultUtil(): UtilState {
    return {
        code: 1
    }
}
export const useUtilStore = defineStore('util-store', {
    state: (): UtilState => getDefaultUtil(),

})

export function useAppStoreWithOut1() {
    return useUtilStore(store)
}
