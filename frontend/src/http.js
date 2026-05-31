import axios from 'axios'

const trimTrailingSlash = (value) => value.replace(/\/+$/, '')

export const API_BASE_URL = trimTrailingSlash(import.meta.env.VITE_API_BASE_URL ?? '')
export const BACKEND_ORIGIN = trimTrailingSlash(
  import.meta.env.VITE_BACKEND_ORIGIN ?? API_BASE_URL.replace(/\/api$/, '')
)

const localhostPattern = /^https?:\/\/(?:localhost|127\.0\.0\.1)(?::\d+)?/i

export const apiUrl = (path = '') => {
  const normalizedPath = path.startsWith('/') ? path : `/${path}`

  if (!API_BASE_URL) {
    return normalizedPath
  }

  return `${API_BASE_URL}${normalizedPath}`
}

export const backendUrl = (path = '') => {
  const normalizedPath = path.startsWith('/') ? path : `/${path}`

  if (!BACKEND_ORIGIN) {
    return normalizedPath
  }

  return `${BACKEND_ORIGIN}${normalizedPath}`
}

const rewriteLocalhostUrl = (url) => {
  if (typeof url !== 'string' || !localhostPattern.test(url)) {
    return url
  }

  const suffix = url.replace(localhostPattern, '')

  if (suffix.startsWith('/api/')) {
    return apiUrl(suffix.slice('/api'.length))
  }

  return backendUrl(suffix)
}

axios.defaults.baseURL = API_BASE_URL || undefined

axios.interceptors.request.use((config) => {
  if (config.url) {
    config.url = rewriteLocalhostUrl(config.url)
  }

  return config
})

export default axios