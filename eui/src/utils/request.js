import axios from 'axios'
import { Message } from 'element-ui'
import store from '@/store'
import router from '@/router'
import { removeToken } from '@/utils/auth'
import QS from 'qs'
// create an axios instance
const service = axios.create({
  baseURL: process.env.VUE_APP_BASE_API, // url = base url + request url
  // withCredentials: true, // send cookies when cross-domain requests
  timeout: 5000 // request timeout
})

// request interceptor
service.interceptors.request.use(
  config => {
    if (store.getters.token) {
      config.headers['Authorization'] = 'Bearer ' + store.getters.token
    }

    if (config.method === 'get') {
      try {
        const p = QS.stringify(config.data.params)
        config.url = config.url + (config.url.indexOf('?') === -1 ? '?' : '&') + p
      } catch (error) {
        // not do...
      }
    }
    return config
  },
  error => {
    // do something with request error
    console.log('first:', error) // for debug
    return Promise.reject(error)
  }
)

// response interceptor
service.interceptors.response.use(
  /**
   * If you want to get http information such as headers or status
   * Please return  response => response
  */

  /**
   * Determine the request status by custom code
   * Here is just an example
   * You can also judge the status by HTTP Status Code
   */
  response => {
    return response
  },
  error => {
    // console.log('second:', error.response.data.meta.message) // for debug
    Message({
      message: error.response.data.meta.message || error.message,
      type: 'error',
      duration: 5 * 1000
    })

    // console.log(error.response)
    if (error.response.status === 401) {
      store.commit('user/logout')
      removeToken()
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

export default service
