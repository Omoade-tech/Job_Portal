import axios from 'axios';

export const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true // Required for cookies to be sent
});

// Get CSRF token before making requests
async function getCsrfToken() {
  try {
    await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
      withCredentials: true
    });
  } catch (error) {
    console.error('Failed to get CSRF token:', error);
    throw error; // Propagate the error
  }
}

// Request interceptor
apiClient.interceptors.request.use(
  async (config) => {
    try {
      // Always get CSRF token for authenticated routes
      await getCsrfToken();
      
      const token = localStorage.getItem('token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
        console.log('Token verification:', {
          exists: true,
          length: token.length,
          preview: `${token.substring(0, 10)}...`,
          url: config.url
        });
      }
      return config;
    } catch (error) {
      console.error('Request interceptor error:', error);
      return Promise.reject(error);
    }
  },
  (error) => {
    console.error('Request error:', error);
    return Promise.reject(error);
  }
);

// Response interceptor
apiClient.interceptors.response.use(
  (response) => {
    console.log('Response success:', {
      url: response.config.url,
      status: response.status,
      hasData: !!response.data
    });
    return response;
  },
  (error) => {
    if (error.response) {
      console.error('API Error:', {
        url: error.config?.url,
        status: error.response.status,
        message: error.response.data?.message,
        data: error.response.data
      });
    }
    return Promise.reject(error);
  }
);

function handleErrorResponse(response) {
  const status = response.status;
  const message = response.data?.message || 'An error occurred.';

  switch (status) {
    case 401:
      // Clear auth state on unauthorized
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      break;
    case 403:
      console.error('Forbidden access:', message);
      break;
    case 404:
      console.error('Resource not found:', message);
      break;
    case 422:
      console.error('Validation error:', message);
      break;
    default:
      console.error(`Error ${status}:`, message);
  }
}

function ensureToken() {
  const token = localStorage.getItem('token') || sessionStorage.getItem('token')
  if (!token) {
    console.error('No authentication token found')
    throw new Error('Authentication token is missing')
  }
  apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

export default {
  // Auth endpoints
  async login(credentials) {
    try {
      const response = await apiClient.post('/login', credentials);
      console.log('Raw login response:', response.data);
      
      // Check for token in various possible locations
      const token = response.data.token || response.data.access_token || response.data.data?.token;
      const userData = response.data.user || response.data.data;
      
      if (!token) {
        throw new Error('No token received from server');
      }

      // Store the raw token without 'Bearer' prefix
      const cleanToken = token.replace('Bearer ', '');
      localStorage.setItem('token', cleanToken);
      
      // Store user data
      if (userData) {
        localStorage.setItem('user', JSON.stringify(userData));
      }
      
      // Set in axios defaults
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${cleanToken}`;
      
      return {
        data: {
          token: cleanToken,
          user: userData
        }
      };
    } catch (error) {
      console.error('Login failed:', error.response?.data || error.message);
      throw error;
    }
  },

  register(formData) {
    return apiClient.post('/register', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
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

  // Employer Dashboard Endpoints
  getEmployerApplications(employerId) {
    console.group('API: Employer Applications Fetch')
    console.log('Fetching employer applications', { 
      employerId,
      type: typeof employerId,
      stringValue: String(employerId)
    })
    
    if (!employerId) {
      console.error('No employer ID provided')
      console.groupEnd()
      return Promise.reject(new Error('Employer ID is required'))
    }

    try {
      // Ensure token is set before making the request
      ensureToken()

      console.log('API Request: Fetching employer applications', {
        employerId,
        hasToken: !!apiClient.defaults.headers.common['Authorization']
      })

      return apiClient.get(`/job_applies/employer/${employerId}`)
        .then(response => {
          console.log('Employer Applications Response:', {
            status: response.status,
            data: response.data,
            dataType: typeof response.data,
            dataLength: response.data?.data ? response.data.data.length : 'N/A'
          })

          // Ensure consistent response structure
          const processedResponse = {
            success: response.data.success !== undefined ? response.data.success : true,
            data: response.data.data || response.data,
            message: response.data.message || ''
          }

          console.log('Processed Response:', processedResponse)
          console.groupEnd()

          return processedResponse
        })
        .catch(error => {
          console.group('API: Employer Applications Error')
          console.error('Error fetching employer applications', {
            status: error.response?.status,
            data: error.response?.data,
            message: error.message,
            config: error.config
          })

          // Handle specific error scenarios
          if (error.response) {
            switch (error.response.status) {
              case 401:
                console.error('Unauthorized: Token might be invalid')
                // Clear token and redirect to login
                localStorage.removeItem('token')
                sessionStorage.removeItem('token')
                localStorage.removeItem('user')
                sessionStorage.removeItem('user')
                window.location.href = '/login'
                break
              case 403:
                console.error('Forbidden: Insufficient permissions')
                break
              case 404:
                console.error('No applications found for this employer')
                break
              default:
                console.error('Unknown error fetching applications')
            }
          }

          console.groupEnd()
          throw error
        })
    } catch (error) {
      console.error('Token setup error:', error)
      throw error
    }
  },

  updateApplicationStatus(applicationId, data) {
    return apiClient.put(`/applications/${applicationId}/status`, data);
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