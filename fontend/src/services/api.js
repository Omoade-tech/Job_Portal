import axios from 'axios';

const apiClient = axios.create({
    baseURL: 'http://localhost:8000/api', // Base URL for the API
    headers: {
        'Content-Type': 'application/json',
    },
});

export default {
    getJobPortals(params = {}) {
        return apiClient.get('/job_portals', { params });
    },
    getJobPortalById(id) {
        return apiClient.get(`/job_portals/${id}`);
    },
    createJobPortal(data) {
        // Create a new job portal entry with the required fields
        // For file upload (e.g., companyLogo), use FormData
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
        // Use FormData for file upload
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
};


//http://localhost:8000/api/job_portals