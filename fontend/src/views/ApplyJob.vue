<template>
  <div class="container mt-4">
    <div v-if="!isAuthenticated" class="card">
      <div class="card-body text-center">
        <h4 class="mb-4">Login Required</h4>
        <p class="mb-4">You need to login to apply for this job.</p>
        <button 
          @click="redirectToLogin" 
          class="btn btn-primary me-2"
        >
          Login
        </button>
        <button 
          @click="redirectToRegister" 
          class="btn btn-outline-primary"
        >
          Register
        </button>
      </div>
    </div>

    <div v-else class="card">
      <div class="card-header">
        <h3 class="card-title">Job Application Form</h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="handleSubmit">
          <div v-if="error" class="alert alert-danger">{{ error }}</div>
          <div v-if="success" class="alert alert-success">{{ success }}</div>

          <div class="mb-3">
            <label class="form-label">Cover Letter</label>
            <textarea 
              v-model="formData.coverLetter" 
              class="form-control" 
              rows="5" 
              required
              :disabled="loading"
            ></textarea>
            <small class="text-muted">Minimum 20 characters required</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Resume (PDF only, max 2MB)</label>
            <input
              type="file"
              class="form-control"
              @change="handleFileChange"
              accept=".pdf"
              required
              :disabled="loading"
            />
          </div>

          <button type="submit" class="btn btn-primary w-100" :disabled="loading || !formData.jobseeker_id">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Submitting...' : 'Submit Application' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from "@/stores/Auth";
import { useRouter } from 'vue-router';
import api from "@/services/api.js";

export default {
  name: "ApplyJob",
  props: {
    id: {
      type: [String, Number],
      required: true
    }
  },
  
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    return {
      authStore,
      router
    }
  },

  data() {
    return {
      formData: {
        coverLetter: "",
        resume: null,
        job_portals_id: this.id,
        jobseeker_id: null
      },
      error: null,
      success: null,
      loading: false,
    };
  },

  computed: {
    isAuthenticated() {
      return this.authStore.isAuthenticated;
    },
    jobseekerId() {
      const user = this.authStore.user;
      console.log('Current user:', user); // Debug log
      
      // Check if user is a job seeker
      if (user && user.role === 'job_seeker') {
        return user.model_id || user.id;
      }
      return null;
    }
  },

  created() {
    // Set the jobseeker_id when component is created
    this.formData.jobseeker_id = this.jobseekerId;
    
    // Also check localStorage/sessionStorage
    const storedUser = JSON.parse(localStorage.getItem('user') || sessionStorage.getItem('user') || '{}');
    console.log('Stored user:', storedUser); // Debug log
    
    if (storedUser && storedUser.role === 'job_seeker') {
      this.formData.jobseeker_id = storedUser.model_id || storedUser.id;
    }

    // Log the current state
    console.log('Initial jobseeker_id:', this.formData.jobseeker_id);
  },

  watch: {
    'authStore.user': {
      handler(newUser) {
        console.log('User changed:', newUser); // Debug log
        if (newUser && newUser.role === 'job_seeker') {
          this.formData.jobseeker_id = newUser.model_id || newUser.id;
          console.log('Updated jobseeker_id:', this.formData.jobseeker_id);
        }
      },
      immediate: true
    }
  },

  methods: {
    async handleSubmit() {
      if (!this.authStore.isAuthenticated) {
        this.error = "Please login to submit your application";
        return;
      }

      if (!this.formData.jobseeker_id) {
        this.error = "You must be logged in as a job seeker to apply";
        return;
      }

      this.loading = true;
      this.error = null;
      this.success = null;

      try {
        const submitData = new FormData();
        
        // Add form data with correct field names
        submitData.append('coverLetter', this.formData.coverLetter);
        submitData.append('resume', this.formData.resume);
        submitData.append('job_portals_id', this.formData.job_portals_id);
        submitData.append('jobseeker_id', this.formData.jobseeker_id);

        // Debug log the data being sent
        console.log('Submitting application with data:', {
          coverLetter: this.formData.coverLetter ? 'present' : 'missing',
          resume: this.formData.resume ? 'present' : 'missing',
          job_portals_id: this.formData.job_portals_id,
          jobseeker_id: this.formData.jobseeker_id,
          userRole: this.authStore.user?.role
        });

        const response = await api.createJobApplication(submitData);
        this.success = "Application submitted successfully!";
        this.resetForm();
        
        setTimeout(() => {
          this.router.push({ name: 'joblisting' });
        }, 2000);

      } catch (err) {
        console.error('Application submission error:', err.response?.data || err);
        if (err.response?.status === 401) {
          this.error = "Your session has expired. Please login again.";
          await this.authStore.logout();
          this.redirectToLogin();
        } else if (err.response?.data?.errors) {
          // Handle validation errors
          const errors = err.response.data.errors;
          this.error = Object.values(errors).flat().join('\n');
        } else {
          this.error = err.response?.data?.message || 
                      "Failed to submit application. Please try again.";
        }
      } finally {
        this.loading = false;
      }
    },

    handleFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) { // 2MB in bytes
          this.error = "File size must be less than 2MB";
          e.target.value = ''; // Clear the file input
          return;
        }
        if (file.type !== 'application/pdf') {
          this.error = "Only PDF files are allowed";
          e.target.value = ''; // Clear the file input
          return;
        }
        this.formData.resume = file;
        this.error = null;
      }
    },

    redirectToLogin() {
      this.router.push({
        name: 'login',
        query: { redirect: this.$route.fullPath }
      });
    },

    redirectToRegister() {
      this.router.push({
        name: 'register',
        query: { redirect: this.$route.fullPath }
      });
    },

    resetForm() {
      this.formData = {
        coverLetter: "",
        resume: null,
        job_portals_id: this.id,
        jobseeker_id: this.jobseekerId // Use computed property
      };
      // Reset file input
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) {
        fileInput.value = "";
      }
    }
  }
};
</script>