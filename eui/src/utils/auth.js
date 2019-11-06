import Cookies from 'js-cookie'

const TokenKey = '_token'

export function getToken() {
  return Cookies.get(TokenKey)
}

export function getExpiresIn() {
  return Cookies.get(TokenKey + '_expiresIn')
}

export function setToken(token, expiresIn) {
  Cookies.set(TokenKey, token)
  Cookies.set(TokenKey + '_expiresIn', expiresIn)
}

export function removeToken() {
  Cookies.remove(TokenKey)
  Cookies.remove(TokenKey + '_expiresIn')
}
