<template>
    <div class="container mt-4">
      <div v-if="loading" class="text-center text-info my-4">Loading job details...</div>
  
      <div v-else-if="job" class="edit-form">
        <h2 class="mb-4">Edit Job</h2>
        <form @submit.prevent="updateJob" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="companyLogo" class="form-label">Company Logo</label>
            <div class="d-flex align-items-center gap-3">
              <img 
                v-if="logoPreview" 
                :src="getLogoUrl(logoPreview)" 
                alt="Company Logo" 
                class="company-logo mb-2"
              />
              <input
                type="file"
                id="companyLogo"
                class="form-control"
                @change="handleLogoUpload"
                accept="image/jpeg,image/png,image/jpg,image/gif"
              />
            </div>
            <small class="text-muted">Max file size: 2MB. Allowed formats: JPEG, PNG, JPG, GIF</small>
          </div>
  
          <div class="mb-3">
            <label for="companyName" class="form-label">Company Name</label>
            <input 
              type="text" 
              id="companyName" 
              class="form-control" 
              v-model="form.companyName" 
              maxlength="255"
              required 
            />
          </div>
  
          <div class="mb-3">
            <label for="post" class="form-label">Job Post</label>
            <input 
              type="text" 
              id="post" 
              class="form-control" 
              v-model="form.post" 
              maxlength="100"
              required 
            />
          </div>
  
          <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input 
              type="text" 
              id="location" 
              class="form-control" 
              v-model="form.location" 
              maxlength="255"
              required 
            />
          </div>
  
          <div class="mb-3">
            <label for="contract" class="form-label">Contract</label>
            <select id="contract" class="form-control" v-model="form.contract" required>
              <option value="fulltime">Full-Time</option>
              <option value="remote">Remote</option>
              <option value="parttime">Part-Time</option>
            </select>
          </div>
  
          <div class="mb-3">
            <label for="salary" class="form-label">Salary</label>
            <input 
              type="text" 
              id="salary" 
              class="form-control" 
              v-model.number="form.salary" 
              min="0"
              required 
            />
          </div>
  
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea 
              id="description" 
              class="form-control" 
              v-model="form.description" 
              rows="4" 
              minlength="10"
              maxlength="1000"
              required
            ></textarea>
          </div>
  
          <div class="mb-3">
            <label for="responsibility" class="form-label">Responsibilities</label>
            <textarea 
              id="responsibility" 
              class="form-control" 
              v-model="form.responsibility" 
              rows="4" 
              minlength="10"
              maxlength="1000"
              required
            ></textarea>
          </div>
  
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary me-2" :disabled="updating">
              <span v-if="updating">Saving...</span>
              <span v-else><i class="bi bi-save"></i> Save Changes</span>
            </button>
            <button type="button" class="btn btn-secondary" @click="cancelEdit">
              <i class="bi bi-x"></i> Cancel
            </button>
          </div>
        </form>
      </div>
  
      <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    </div>
  </template>
  
  <script>
  import api from "@/services/api.js";
  import { useRoute, useRouter } from "vue-router";
  
  export default {
    data() {
      return {
        job: null,
        form: {
          companyName: "",
          post: "",
          location: "",
          contract: "",
          salary: "",
          description: "",
          responsibility: "",
          companyLogo: null
        },
        logoPreview: null,
        loading: false,
        updating: false,
        error: null,
        router: null,
        route: null
      };
    },
  
    created() {
      this.router = useRouter();
      this.route = useRoute();
    },
  
    mounted() {
      const jobId = this.route.params.id;
      if (jobId) {
        this.fetchJobDetails(jobId);
      } else {
        this.error = "Job ID is missing.";
      }
    },
  
    methods: {
      getLogoUrl(path) {
        if (!path) return '';
        if (path.startsWith('http')) return path;
        return `${import.meta.env.VITE_APP_URL}/storage/${path}`;
      },
  
      handleLogoUpload(event) {
        const file = event.target.files[0];
        if (file) {
          if (file.size > 2 * 1024 * 1024) {
            alert('File size should not exceed 2MB');
            event.target.value = '';
            return;
          }
          this.form.companyLogo = file;
          this.logoPreview = URL.createObjectURL(file);
        }
      },
  
      async fetchJobDetails(id) {
        this.loading = true;
        this.error = null;
        
        try {
          const response = await api.getJobPortalById(id);
          this.job = response.data.data;
          this.form = { 
            ...this.job,
            companyLogo: null // Reset file input
          };
          if (this.job.companyLogo) {
            this.logoPreview = this.job.companyLogo;
          }
        } catch (error) {
          this.error = error.response?.data?.message || "Failed to load job details.";
          console.error('Error fetching job:', error);
        } finally {
          this.loading = false;
        }
      },
  
      async updateJob() {
        this.updating = true;
        this.error = null;
  
        try {
          const formData = new FormData();
          Object.keys(this.form).forEach(key => {
            if (this.form[key] !== null) {
              formData.append(key, this.form[key]);
            }
          });
  
          await api.updateJobPortal(this.job.id, formData);
          this.router.push('/jobdetails');
        } catch (error) {
          this.error = error.response?.data?.message || "Failed to update job.";
          if (error.response?.data?.errors) {
            this.error = Object.values(error.response.data.errors).flat().join('\n');
          }
          console.error('Error updating job:', error);
        } finally {
          this.updating = false;
        }
      },
  
      cancelEdit() {
        this.router.push('/jobdetails');
      }
    }
  };
  </script>
  
  <style scoped>
  .container {
    max-width: 600px;
  }
  .edit-form {
    background: #fff;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  .company-logo {
    width: 100px;
    height: 100px;
    object-fit: contain;
    border: 1px solid #ddd;
    border-radius: 4px;
  }
  </style>