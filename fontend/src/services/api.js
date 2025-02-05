import axios from 'axios';

export const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true
});

// Request interceptor
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    console.log('API Request:', {
      url: config.url,
      method: config.method,
      hasToken: !!token
    });
    return config;
  },
  (error) => {
    console.error('Request interceptor error:', error);
    return Promise.reject(error);
  }
);

// Response interceptor
apiClient.interceptors.response.use(
  (response) => {
    console.log('API Response:', {
      url: response.config.url,
      status: response.status,
      hasData: !!response.data
    });
    return response;
  },
  (error) => {
    if (error.response) {
      console.error('API Error Response:', {
        url: error.config?.url,
        status: error.response.status,
        data: error.response.data,
        headers: error.response.headers
      });
      handleErrorResponse(error.response);
    } else if (error.request) {
      console.error('API No Response:', {
        url: error.config?.url,
        request: error.request,
        message: error.message
      });
      if (error.code === 'ERR_CONNECTION_REFUSED') {
        console.error('Backend server is not running or not accessible');
      }
    } else {
      console.error('API Request Setup Error:', error.message);
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
    console.log('Sending login request');
    try {
      const response = await apiClient.post('/login', credentials);
      console.log('Full login response:', response.data);
      
      // Enhanced employer ID extraction
      const userData = response.data.data || {};
      const employerId = 
        userData.employer_id || 
        userData.model_id || 
        (userData.role === 'employer' ? userData.id : null);

      console.log('Login Response Details:', {
        hasToken: !!response.data.token,
        userData: userData,
        employerId: employerId,
        role: userData.role
      });
      
      // Restructure the response to match our expected format
      if (response.data.success && response.data.token) {
        // Augment user data with employer ID
        userData.employer_id = employerId;

        // Set the token in API client headers
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
        
        // Return restructured response
        return {
          data: {
            token: response.data.token,
            user: userData
          }
        };
      }
      
      return response;
    } catch (error) {
      console.error('Login request failed:', {
        status: error.response?.status,
        data: error.response?.data,
        message: error.message
      });
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