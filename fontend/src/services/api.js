import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: true,
});

// Request interceptor
apiClient.interceptors.request.use(
  (config) => {
    return config;
  },
  (error) => {
    console.error('Request error:', error);
    return Promise.reject(error);
  }
);

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      handleErrorResponse(error.response);
    } else if (error.request) {
      console.error('No response received:', error.request);
    } else {
      console.error('Error setting up request:', error.message);
    }
    return Promise.reject(error);
  }
);

function handleErrorResponse(response) {
  const status = response.status;
  const message = response.data.message || 'An error occurred.';

  switch (status) {
    case 401:
      console.error('Unauthorized:', message);
      break;
    case 403:
      console.error('Access forbidden:', message);
      break;
    case 422:
      console.error('Validation error:', response.data.errors);
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
}

export default {
  // Auth endpoints
  register(formData) {
    return apiClient.post('/register', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },

  login(data) {
    if (!data.email || !data.password) {
      throw new Error('Email and password are required');
    }
    const payload = {
      email: data.email,
      password: data.password,
      remember: data.rememberMe
    };
    return apiClient.post('/login', payload);
  },

  logout() {
    return apiClient.post('/logout');
  },

  // User Role endpoints
  getJobSeekers() {
    return apiClient.get('/users', {
      params: {
        role: 'job_seeker'
      }
    });
  },

  getEmployers() {
    return apiClient.get('/users', {
      params: {
        role: 'employer'
      }
    });
  },

  getUsersByRole(role) {
    if (!['admin', 'employer', 'job_seeker'].includes(role)) {
      throw new Error('Invalid role specified');
    }
    return apiClient.get('/users', {
      params: { role }
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

  // Job Applications Endpoints
  getAllJobApplications() {
    return apiClient.get('/job_applies');
  },

  getJobApplicationById(id) {
    return apiClient.get(`/job_applies/${id}`);
  },

  createJobApplication(data) {
    return this.sendFormData('/job_applies', data);
  },

  updateJobApplication(id, data) {
    return this.sendFormData(`/job_applies/${id}?_method=PUT`, data);
  },

  // Search Functionality
  searchJobPortals(searchParams) {
    const params = {};
    if (searchParams.filterType === 'query') {
      params.query = searchParams.query;
    } else {
      params[searchParams.filterType] = searchParams.query;
    }
    return apiClient.get('/job_portals/search', { params });
  },

  // Utility function for handling form data
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
  },
};