import { login, logout, getInfo, getPermission, refreshToken } from '@/api/user'
import { getToken, setToken, removeToken, getExpiresIn } from '@/utils/auth'
import { resetRouter } from '@/router'

const state = {
  token: getToken(),
  expiresIn: getExpiresIn(),
  name: '',
  avatar: '',
  permission: []
}

const mutations = {
  SET_TOKEN: (state, token) => {
    state.token = token
  },
  SET_EXPIRESIN: (state, expiresIn) => {
    state.expiresIn = expiresIn
  },
  SET_NAME: (state, name) => {
    state.name = name
  },
  SET_AVATAR: (state, avatar) => {
    state.avatar = avatar
  },
  SET_PERMISSION: (state, permission) => {
    state.permission = permission
  }
}

const actions = {
  // user login
  login({ commit }, userInfo) {
    const { username, password } = userInfo
    return new Promise((resolve, reject) => {
      login({ username: username.trim(), password: password }).then(response => {
        const { data } = response
        commit('SET_TOKEN', data.data.token)
        setToken(data.token)
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  },

  // get user info
  getInfo({ commit, state }) {
    return new Promise((resolve, reject) => {
      getInfo(state.token).then(response => {
        const { data } = response

        if (!data) {
          reject('Verification failed, please Login again.')
        }

        const { name, avatar } = data.data

        commit('SET_NAME', name)
        commit('SET_AVATAR', avatar)
        resolve(data)
      }).catch(error => {
        reject(error)
      })
    })
  },

  // user logout
  logout({ commit, state }) {
    return new Promise((resolve, reject) => {
      logout(state.token).then(() => {
        commit('SET_TOKEN', '')
        commit('SET_EXPIRESIN', '')
        commit('SET_PERMISSION', '')
        removeToken()
        resetRouter()
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  },

  // remove token
  resetToken({ commit }) {
    return new Promise(resolve => {
      commit('SET_TOKEN', '')
      commit('SET_EXPIRESIN', '')
      commit('SET_PERMISSION', '')
      removeToken()
      resolve()
    })
  },

  // permission
  getPermission({ commit }) {
    return new Promise((resolve, reject) => {
      getPermission(state.token).then(({ data }) => {
        // console.log('getPermission', data)
        commit('SET_PERMISSION', data.data)
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  },

  // refresh token
  refreshToken({ commit }) {
    return new Promise((resolve, reject) => {
      refreshToken(state.token).then(({ data }) => {
        commit('SET_TOKEN', data.data.token)
        commit('SET_EXPIRESIN', data.data.expiresIn)
        setToken(data.data.token, data.data.expiresIn)
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}

