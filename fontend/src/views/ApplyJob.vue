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
          </div>

          <div class="mb-3">
            <label class="form-label">Resume (PDF only)</label>
            <input
              type="file"
              class="form-control"
              @change="handleFileChange"
              accept=".pdf"
              required
              :disabled="loading"
            />
          </div>

          <button type="submit" class="btn btn-primary w-100" :disabled="loading">
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
// import api from "@/services/api.js";

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

    // Check authentication
    if (!authStore.isAuthenticated) {
      localStorage.setItem('intendedRoute', `/applyJob/${props.id}`);
      router.push('/login');
    }

    return {
      user: authStore.user,
      authStore,
      router
    }
  },

  data() {
    return {
      formData: {
        jobId: this.id,
        coverLetter: "",
        resume: null,
      },
      error: null,
      success: null,
      loading: false,
    };
  },

  computed: {
    isAuthenticated() {
      return this.authStore.isAuthenticated
    }
  },

  methods: {
    async submitApplication() {
      if (!this.authStore.isAuthenticated) {
        localStorage.setItem('intendedRoute', `/applyJob/${this.id}`);
        this.router.push('/login');
        return;
      }
      
      // ... rest of your submit logic
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

    handleFileChange(e) {
      this.formData.resume = e.target.files[0];
    },

    resetForm() {
      this.formData = {
        jobId: this.id,
        coverLetter: "",
        resume: null,
      };
      // Reset file input
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) {
        fileInput.value = "";
      }
    },

    async handleSubmit() {
      if (!this.authStore.isAuthenticated) {
        this.error = "Please login to submit your application";
        return;
      }

      this.loading = true;
      this.error = null;
      this.success = null;

      try {
        const submitData = new FormData();
        
        // Add form data
        Object.keys(this.formData).forEach((key) => {
          submitData.append(key, this.formData[key]);
        });
        
        // Add job ID if available from route params
        if (this.$route.params.jobId) {
          submitData.append('job_id', this.$route.params.jobId);
        }

        const response = await api.createJobApplication(submitData);
        this.success = "Application submitted successfully!";
        this.resetForm();
        
        // Redirect to applications list after success
        setTimeout(() => {
          this.router.push({ name: 'joblisting' });
        }, 2000);

      } catch (err) {
        if (err.response?.status === 401) {
          this.error = "Your session has expired. Please login again.";
          await this.authStore.logout();
          this.redirectToLogin();
        } else {
          this.error = err.response?.data?.message || 
                      "Failed to submit application. Please try again.";
        }
      } finally {
        this.loading = false;
      }
    },
  },

  // Add navigation guard at component level
  beforeRouteEnter(to, from, next) {
    const authStore = useAuthStore();
    if (!authStore.isAuthenticated) {
      next({
        name: 'login',
        query: { redirect: to.fullPath }
      });
    } else {
      next();
    }
  },

  // Watch for auth state changes
  watch: {
    isAuthenticated(newValue) {
      if (!newValue) {
        this.redirectToLogin();
      }
    }
  }
};
</script>