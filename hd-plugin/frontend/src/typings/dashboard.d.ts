declare namespace Dashboard {

    type Diseases = {
        id?: string
        plantId?: string
        name: string
        image: string | File 
        keyLabel: number | null
        description: string
        healthCondition: string
        languageCodes: string[]
        translationsName: string[]
        translationsDescription: string[]
        translationsHealthCondition: string[]
        status: boolean
        productIds:[]
        products:[]
        createdAt: string
        updatedAt: string
    }

    type Plant = {
        id?: string
        name: string
        languageCodes: string[]
        translations: string[]
        image: string | File 
        status: boolean
        modelAIGithub?: string
        createdAt: string
        updatedAt: string
    }

    type ModelAI = {
        id?: string
        plantId?: string
        name: string
        description?: string
        githubUrl: string
        modelJsonFile?: string
        modelWeightsFile?: string
        modelMetaFile?: string
        modelClassFile?: string
        version?: string
        status: boolean
        createdAt: string
        updatedAt: string
    }

    type DashboardState = {
        listPlants: Plant[]
        currentPlant: Plant

        showModelAdd: boolean
        showModelAddDiseases: boolean
        showModelUpdatePlant: boolean
        showModelUpdateDiseases: boolean
        showModelAddModelAI: boolean
        listDiseases: Diseases[]
        listModelAI: ModelAI[]
        statistics: Statistics | null

        listHistoryUser: HistoryUser[]
    }


    type UserDetectionState = {
        selectedPlant: Plant

    }

    type Statistics = {
        plants: number
        diseases: number
        modelAi: number
        history: number

    }

    type HistoryUser = {
        id?: string
        image?: File
        plantId?: string
        predictionResultValue?: string
        correctDiseaseId?: string
        modelId?: string
        createdAt?: string

    }

    interface Product {
        id: number;
        name: string;
        permalink: string;
        image: string;
        priceHtml: string;
        onSale: boolean;
        stockStatus: string;
        categories: number[];
    }


}