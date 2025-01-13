import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App.tsx'
import axios from 'axios';

axios.defaults.withXSRFToken = true;
axios.defaults.withCredentials = true;
axios.defaults.baseURL = 'http://localhost:81';
axios.defaults.headers['Content-Type'] = 'application/json';
axios.defaults.headers['Accept'] = 'application/json';

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
