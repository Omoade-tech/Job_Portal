import axios from 'axios';

const apiClient = axios.create({
    baseURL: 'http://localhost:8000/api', // Base URL for the API
    headers: {
        'Content-Type': 'application/json',
    },
});

export default {
    // Authentication Endpoints
    register(data) {
        // Register a new user
        return apiClient.post('/register', data);
    },
    login(data) {
        // Log in the user
        return apiClient.post('/login', data);
    },
    logout() {
        // Log out the user
        return apiClient.post('/logout');
    },

    // Job Portal Endpoints
    getJobPortals(params = {}) {
        return apiClient.get('/job_portals', { params });
    },
    getJobPortalById(id) {
        return apiClient.get(`/job_portals/${id}`);
    },
    createJobPortal(data) {
        const formData = new FormData();
        for (const key in data) {
            formData.append(key, data[key]);
        }
        return apiClient.post('/job_portals', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },
    updateJobPortal(id, data) {
        // Update an existing job portal entry by ID
        const formData = new FormData();
        for (const key in data) {
            formData.append(key, data[key]);
        }
        return apiClient.post(`/job_portals/${id}?_method=PUT`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },
    deleteJobPortal(id) {
        return apiClient.delete(`/job_portals/${id}`);
    },

    // Job Apply Endpoints
    getAllJobApplies() {
        // Fetch all job applies
        return apiClient.get('/job_applies');
    },
};
