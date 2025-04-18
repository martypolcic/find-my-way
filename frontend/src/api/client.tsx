import axios, { AxiosError } from 'axios';
import { getCookie } from '../utils/cookies';

const apiClient = axios.create({
  baseURL: 'http://localhost:8001',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

apiClient.interceptors.request.use(config => {
  if (config.method?.toLowerCase() !== 'get') {
    const token = getCookie('XSRF-TOKEN');
    if (token) {
      config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
    }
  }
  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const axiosError = error as AxiosError<{
      errors?: Record<string, string[]>;
      message?: string;
    }>;

    if (axiosError.response?.status === 419) {
      console.error('⚠️ CSRF token mismatch or session expired.');
      window.location.reload();
    }

    if (axiosError.response?.status === 422 && axiosError.response.data?.errors) {
      return Promise.reject({
        ...axiosError,
        formattedErrors: Object.entries(axiosError.response.data.errors)
          .flatMap(([field, errors]) => errors.map(e => `${field}: ${e}`))
      });
    }

    return Promise.reject(axiosError);
  }
);

export const getCSRF = () => apiClient.get('/sanctum/csrf-cookie');
export default apiClient;
