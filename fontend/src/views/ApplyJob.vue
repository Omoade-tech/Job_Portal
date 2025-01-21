<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Job Application Form</h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="handleSubmit">
          <div v-if="error" class="alert alert-danger">{{ error }}</div>
          <div v-if="success" class="alert alert-success">{{ success }}</div>

          <div class="mb-3">
            <label class="form-label">Cover Letter</label>
            <textarea v-model="formData.coverLetter" class="form-control" rows="5" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Resume (PDF only)</label>
            <input
              type="file"
              class="form-control"
              @change="handleFileChange"
              accept=".pdf"
              required
            />
          </div>

          <button type="submit" class="btn btn-primary w-100" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm"></span>
            {{ loading ? 'Submitting...' : 'Submit Application' }}
          </button>

        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from "@/stores/Auth.js";
import api from "@/services/api.js";

export default {
  name: "JobApplicationForm",
  data() {
    return {
      formData: {
        coverLetter: "",
        resume: null,
      },
      error: "",
      success: "",
      loading: false,
      isAuthenticated: false
    };
  },

  mounted() {
    if (!this.isLoggedIn()) {
      this.$router.push({ name: 'login' });
    }
  },

  methods: {
    handleFileChange(e) {
      this.formData.resume = e.target.files[0];
    },

    async handleSubmit() {
      this.loading = true;
      this.error = "";
      this.success = "";

      try {
        const formData = new FormData();
        Object.keys(this.formData).forEach((key) => {
          formData.append(key, this.formData[key]);
        });

        await api.createJobApplication(formData);
        this.success = "Application submitted successfully!";
        this.resetForm();
      } catch (err) {
        this.error =
          err.response?.data?.message ||
          "Failed to submit application. Please try again.";
      } finally {
        this.loading = false;
      }
    },

    resetForm() {
      this.formData = {
        coverLetter: "",
        resume: null,
      };

      // Reset file input
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) fileInput.value = "";
    },
    created() {
      const authStore = useAuthStore();
      this.isAuthenticated = authStore.isAuthenticated;
  
      if (!this.isAuthenticated) {
        // Redirect to login if not authenticated
        this.$router.push("/login");
      }
    },
    isLoggedIn() {
      const authStore = useAuthStore();
      return authStore.isAuthenticated;
    }

   
  },
};
</script>