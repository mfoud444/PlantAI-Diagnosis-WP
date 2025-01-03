import axios, { type AxiosResponse } from 'axios'
import applyCaseMiddleware from 'axios-case-converter';
// export const host = "http://localhost/wordpress65/"
export const host = "https://worldofplants.ai/"
export const baseURL = host + "wp-json/wphd/v2/"
export const baseImageUrl = host +  "wp-content/plugins/wp-health-diagnosis-plugin/hd-plugin/frontend/dist/assets/"
const service = applyCaseMiddleware(axios.create({
  baseURL:baseURL
  // baseURL:import.meta.env.VITE_GLOB_API_URL,
}))

service.interceptors.request.use(
  (config) => {
    const token = false
    if (token)
      config.headers.Authorization = `Bearer ${token}`
    return config
  },
  (error) => {
    return Promise.reject(error.response)
  },
)

service.interceptors.response.use(
  (response: AxiosResponse): AxiosResponse => {
    if (response.status === 200)
      return response

    throw new Error(response.status.toString())
  },
  (error) => {
    //w
    return Promise.reject(error)
  },
)

export default service
