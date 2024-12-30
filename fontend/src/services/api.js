import axios from 'axios';

// Create axios instance with default config
const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: true,
});

// Add request interceptor to handle errors and add auth token if exists
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    console.error('Request error:', error);
    return Promise.reject(error);
  }
);

// Add response interceptor for handling errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      const status = error.response.status;
      const message = error.response.data.message || 'An error occurred.';
      switch (status) {
        case 401:
          localStorage.removeItem('token');
          localStorage.removeItem('user');
          console.error('Unauthorized access. Redirecting to login...');
          break;
        case 403:
          console.error('Access forbidden:', message);
          break;
        case 422:
          console.error('Validation error:', error.response.data.errors);
          break;
        case 429:
          console.error('Too many requests. Please try again later.');
          break;
        case 500:
          console.error('Server error. Please try again later.');
          break;
        default:
          console.error('API Error:', message);
      }
    } else if (error.request) {
      console.error('No response received:', error.request);
    } else {
      console.error('Error setting up request:', error.message);
    }
    return Promise.reject(error);
  }
);

export default {
  register(data) {
    return apiClient.post('/register', data);
  },

  login(data) {
    return apiClient.post('/login', data).then((response) => {
      if (response.data.token) {
        localStorage.setItem('token', response.data.token);
      }
      if (response.data.data) {
        localStorage.setItem('user', JSON.stringify(response.data.data));
      }
      return response;
    });
  },

  logout() {
    return apiClient.post('/logout').finally(() => {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
    });
  },

  // Job Portal Endpoints
  getJobPortals(params = {}) {
    return apiClient.get('/job_portals', { params });
  },

  getJobPortalById(id) {
    return apiClient.get(`/job_portals/${id}`);
  },

  createJobPortal(data) {
    return this.sendFormData('/job_portals', data, 'POST');
  },

  updateJobPortal(id, data) {
    return this.sendFormData(`/job_portals/${id}?_method=PUT`, data, 'POST');
  },

  deleteJobPortal(id) {
    return apiClient.delete(`/job_portals/${id}`);
  },

  getAllJobApplies() {
    return apiClient.get('/job_applies');
  },

  isLoggedIn() {
    return !!localStorage.getItem('token');
  },

  getCurrentUser() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  },

  sendFormData(url, data, method = 'POST') {
    const formData = data instanceof FormData ? data : new FormData();
    if (!(data instanceof FormData)) {
      for (const key in data) {
        if (data[key] !== null) {
          formData.append(key, data[key]);
        }
      }
    }
    return apiClient.request({
      url,
      method,
      data: formData,
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  }
};
