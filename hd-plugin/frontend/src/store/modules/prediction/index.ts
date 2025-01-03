import { defineStore } from 'pinia';
import {  post } from '@/utils/request'
function initState(): Prediction.StatePrediction {
  return {
    currentPlants: null,
    currentStep: null,
    correctDisease: null,
    correctPredictedProbability: null,
    imgSrc: '',
    products :[]
  }
}


export const usePredictionStore = defineStore('prediction-store', {
  state: (): Prediction.StatePrediction => initState(),
  actions: {

    async insertActionHistory(formData: FormData, historyUser?: Dashboard.HistoryUser,): Promise<void> {
      try {
        // console.log(historyUser)
        
        const response = await post({
          url: 'add-detection-history/',
          data: formData
        })

        console.log("$status_param", response)

      } catch (error: any) {
        console.error('Error inserting History', error.message);
        throw error;
      }
    },
  },
});
