<template>
    <div class="container mt-4">
      <!-- Loading State -->
      <div v-if="loading" class="text-center text-info my-4">Loading job details...</div>
  
      <!-- Job Details -->
      <div v-else-if="job">
        <div class="card">
          <!-- Job Header -->
          <div class="card-header d-flex align-items-center">
            <img
              :src="job.companyLogo"
              :alt="`${job.companyName} logo`"
              class="job-logo me-3"
            />
            <div>
              <h5 class="card-title mb-1">{{ job.companyName }}</h5>
              <h6 class="card-subtitle text-muted">{{ job.post }}</h6>
            </div>
          </div>
  
          <!-- Job Body -->
          <div class="card-body">
            <p><strong>Location:</strong> {{ job.location }}</p>
            <p><strong>Contract:</strong> {{ job.contract }}</p>
            <p><strong>Salary:</strong> {{ job.salary }}</p>
            <p><strong>Description:</strong> {{ job.description }}</p>
            <p><strong>Responsibilities:</strong> {{ job.responsibility }}</p>
          </div>
  
          <!-- Action Buttons -->
          <div class="card-footer d-flex justify-content-end">
            <button
              class="btn btn-danger"
              @click="deleteJob(job.id)"
              :disabled="deleting"
            >
              <i class="bi bi-trash"></i> Delete
            </button>
            <button
              class="btn  btn-success ms-3 "
            >
            <router-link to="/jobdetails" class="link">Back</router-link>
            </button>
          </div>
        </div>
      </div>
  
      <!-- Error State -->
      <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    </div>
  </template>
  
  <script>
  import api from "@/services/api.js";
  import { useRoute, useRouter, onBeforeRouteUpdate } from "vue-router";
  
  export default {
    data() {
      return {
        job: null,
        loading: false,
        deleting: false,
        error: null,
      };
    },
    methods: {
      async fetchJobDetails(id) {
        this.loading = true;
        this.error = null;
        try {
          const response = await api.getJobPortalById(id); 
          this.job = response.data.data; 
        } catch (error) {
          this.error = "Failed to load job details. Please try again.";
        } finally {
          this.loading = false;
        }
      },
      async deleteJob(id) {
        if (confirm("Are you sure you want to delete this job?")) {
          this.deleting = true;
          this.error = null;
          try {
            await api.deleteJobPortal(id); 
            alert("Job deleted successfully!");
            const router = useRouter();
            router.push("/joblisting");
          } catch (error) {
            this.error = "Failed to delete the job. Please try again.";
          } finally {
            this.deleting = false;
          }
        }
      },
    },
    mounted() {
      const route = useRoute();
      const jobId = route.params.id; 
      if (jobId) {
        this.fetchJobDetails(jobId);
      } else {
        this.error = "Job ID is missing.";
      }
    },
    setup() {
      const route = useRoute();
  
      // React to route parameter changes
      onBeforeRouteUpdate((to, from, next) => {
        const jobId = to.params.id;
        if (jobId) {
          this.fetchJobDetails(jobId); 
        }
        next();
      });
    },
  };
  </script>
  
  <style scoped>
  .job-logo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
  }
  .link {
    text-decoration: none;
    color: inherit;
    margin-left: 10px;
  }
  </style>
  