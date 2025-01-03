import { defineStore } from 'pinia';

import { get, post, del, put } from '@/utils/request'

function initState(): Dashboard.DashboardState {
  return {
    listPlants: [],
    currentPlant: {
      name: '',
      image: '',
      status: true,
      createdAt: '',
      updatedAt: '',
      languageCodes: [],
      translations: []
    },
    showModelAdd: false,
    showModelAddDiseases: false,
    showModelAddModelAI:false,

    showModelUpdatePlant: false,
    showModelUpdateDiseases: false,

  
    listDiseases: [],
    listModelAI:[],

    statistics:null,
    listHistoryUser:[],

  }
}


export const useDashboardStore = defineStore('dashboard-store', {
  state: (): Dashboard.DashboardState => initState(),
  actions: {

     getPlantById(Id: string): Dashboard.Plant {
      try {
        const plant = this.listPlants.find((plant) => plant.id === Id);
        if (plant) {
          return plant;
        } else {
          throw "Not Founded Plan";
        }
      } catch (error: any) {
        console.log("error", error)
        throw error;
      }
    },

    getListDiseasesByIdPlant(Id: string): Dashboard.Diseases[] {
      try {
        const diseases = this.listDiseases.filter((disease) => disease.plantId === Id);
        if (diseases.length > 0) {
          return diseases;
        } else {
         return []
        }
      } catch (error: any) {
        console.log("error", error)
        throw error;
      }
    },

    getListModelAIByIdPlant(Id: string): Dashboard.ModelAI | null {
      try {
        const modelai = this.listModelAI.find((modelai) => modelai.plantId === Id);
      
        // If model AI is found, return it
        if (modelai) {
          return modelai;
        } else {
          return null; 
        }
      } catch (error) {
        console.error('Error fetching model AI:', error);
        throw error; 
      }
    },

    async getAllPlantAction(): Promise<void> {
      try {

        const response = await get<Dashboard.Plant[]>({
          url: 'plants/',
        })
        console.log("plant", response)
        this.listPlants = response
      } catch (error: any) {
        console.log("error", error)
        throw error;
      }
    },

    async getAllPlantAdminAction(): Promise<void> {
      try {

        const response = await get<Dashboard.Plant[]>({
          url: 'plants-t/',
        })
        console.log("plant", response)
        this.listPlants = response
      } catch (error: any) {
        console.log("error", error)
        throw error;
      }
    },

    async getAllHistoryUserAction(): Promise<void> {
      try {

        const response = await get<any>({
          url: 'detection-history/',
        })
        console.log("HistoryUser", response)
        this.listHistoryUser = response
      } catch (error: any) {
        console.log("error", error)
        throw error;
      }
    },

    async getStatisticsAction(): Promise<void> {
      try {

        const response = await get<Dashboard.Statistics>({
          url: 'statistics/',
        })
        console.log("plant", response)
        this.statistics = response
      } catch (error: any) {
        console.log("error", error)
        throw error;
      }
    },

    async getAllDiseases(plantId?: string): Promise<void> {
      try {

      const   data = {
          'plantId': plantId
        }
        const response = await get<Dashboard.Diseases[]>({
          url: 'diseases/',
          data
        })
        console.log("rdise", response)
        this.listDiseases = response
      } catch (error: any) {
        console.log("error", error)
        throw error;
      }
    },

    async getAllDiseasesAdmin(plantId?: string): Promise<void> {
      try {

      const   data = {
          'plantId': plantId
        }
        const response = await get<Dashboard.Diseases[]>({
          url: 'diseases-t/',
          data
        })
        console.log("rdise", response)
        this.listDiseases = response
      } catch (error: any) {
        console.log("error", error)
        throw error;
      }
    },

       async getAllModelAI(): Promise<void> {
      try {

        const response = await get<Dashboard.ModelAI[]>({
          url: 'models/',
        })
        this.listModelAI = response
        console.log("response models", response)
      } catch (error: any) {
        console.log("error", error)
        throw error;
      }
    },

    async insertActionPlant(newPlant: Dashboard.Plant, formData: FormData): Promise<void> {
      try {
        console.log(newPlant)
        const response = await post({
          url: 'add-plant/',
          data: formData
        })
        console.error('Error response', response);
        newPlant.id = response.id
        newPlant.image = response.image
console.log("$pooooo", response)
        this.listPlants = [ newPlant, ...this.listPlants,];
      } catch (error: any) {
        console.error('Error inserting Plants/', error.message);
        throw error;
      }
    },

    async updateActionPlant(plantToUpdate: Dashboard.Plant, formData: FormData): Promise<void> {
      try {
        console.log(plantToUpdate)
        console.log(formData)
        const response = await post<Dashboard.Plant>({
          url: `edit-plant/`,
          data: formData
        });
        
     
        const plant = this.listPlants.find((plant) => plant.id === plantToUpdate.id);
        if (plant) {
          if(response.image){
            plant.image = response.image
          }
   
        plant.status = response.status
        plant.translations= response.translations
        } else {
          throw "Not Founded Plan";
        }
        console.log("Updated plant:", plantToUpdate);
      } catch (error: any) {
        console.error('Error updating plant:', error.message);
        throw error;
      }
    },


    async updateActionDisease(diseaseToUpdate: Dashboard.Diseases, formData: FormData): Promise<void> {
      // try {
        // console.log(diseaseToUpdate)
        console.log(formData)
        const response = await post<Dashboard.Diseases>({
          url: `edit-disease/`,
          data: formData
        });
        
     
        let  diseaseIndex = this.listDiseases.findIndex((disease) => disease.id === diseaseToUpdate.id);
        if (diseaseIndex) {
     
          this.listDiseases[diseaseIndex] = {...this.listDiseases[diseaseIndex] , ...response}
        } else {
          throw "Not Founded Disease";
        }
        console.log("Updated Disease:", diseaseToUpdate);
        console.log("Updated Diseasejhh:", response);
        console.log(" this.listDiseases[diseaseIndex]",  this.listDiseases[diseaseIndex]);
      // } catch (error: any) {
      //   console.error('Error updating Disease:', error.message);
      //   throw error;
      // }
    },

    async deletePlantsAction(plantId: string): Promise<void> {
      try {
        await del({
          url: `delete-plant/${Number(plantId)}`,
        });
        this.listPlants = this.listPlants.filter((plant) => plant.id !== plantId);
      } catch (error: any) {
        console.error('Error deleting plant', error.message);
        throw error;
      }
    },

    async insertActionDiseases(newPlant: Dashboard.Diseases, formData: FormData): Promise<void> {
      try {
        console.log(newPlant)
        const response = await post({
          url: 'add-disease/',
          data: formData
        })
        console.log('response', response);
        newPlant.id = response.id
        newPlant.image = response.image

        this.listDiseases = [newPlant , ...this.listDiseases];
      } catch (error: any) {
        console.error('Error inserting Plants/', error.message);
        throw error;
      }
    },

    async insertActionModelAI(newModelAI: Dashboard.ModelAI, formData: FormData): Promise<void> {
      try {
        console.log(newModelAI)
        const response = await post({
          url: 'add-model-ai/',
          data: formData
        })
        console.log('response', response);
        newModelAI.id = response.id
        newModelAI.modelJsonFile = response.modelJsonFile;
        newModelAI.modelWeightsFile = response.modelWeightsFile;
        newModelAI.modelMetaFile = response.modelMetaFile;
        newModelAI.modelClassFile = response.modelClassFile;

        this.listModelAI = [ newModelAI, ...this.listModelAI];
      } catch (error: any) {
        console.error('Error inserting Plants/', error.message);
        throw error;
      }
    },

 

    async deleteDiseasesAction(Id: string): Promise<void> {
      try {
        await del({
          url: `delete-disease/${Id}`,
        });
        this.listDiseases = this.listDiseases.filter((disease) => disease.id !== Id);
      } catch (error: any) {
        console.error('Error deleting disease', error.message);
        throw error;
      }
    },

  },
});
