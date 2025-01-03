
declare namespace Prediction {
    type StatePrediction = {
        currentPlants: Dashboard.Plant | null
        currentStep: number | null
        correctDisease: Dashboard.Diseases | null
        correctPredictedProbability: number | null
        imgSrc: string
        products :Dashboard.Product[] 

    }

}