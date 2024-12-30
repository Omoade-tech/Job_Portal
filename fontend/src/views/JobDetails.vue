<template>
    <search />
    <div class="life">
      <div id="job-container" class="container mt-4">
        <!-- Loading State -->
        <div v-if="loading" class="text-center text-info my-4">Loading jobs...</div>
  
        <!-- Job Cards -->
        <div v-else>
          <div class="row">
            <div
              v-for="job in jobs"
              :key="job.id"
              class="col-md-4 mb-4"
            >
              <div class="card h-100">
                <!-- Card Header -->
                <div class="card-header d-flex align-items-center">
                  <img
                    :src="job.companyLogo"
                    :alt="`${job.companyLogo} logo`"
                    class="job-logo me-3"
                  />
                  <div>
                    <h5 class="card-title mb-1">{{ job.companyName }}</h5>
                    <h6 class="card-subtitle text-muted">{{ job.post }}</h6>
                  </div>
                </div>
  
                <!-- Card Body -->
                <div class="card-body">
                  <p><strong>Location:</strong> {{ job.location }}</p>
                  <p><strong>Contract:</strong> {{ job.contract }}</p>
                  <p><strong>Salary:</strong> {{ job.salary }}</p>
                  <p><strong>Description:</strong> {{ job.description }}</p>
                </div>
  
                <!-- Card Buttons -->
                <div class="card-btn m-3 d-flex justify-content-between">
                  <RouterLink to="/applyJob" class="btn btn-success">
                    Apply
                  </RouterLink>
  
                  <div>
                
                    <RouterLink :to="`/viewjob/${job.id}`" class="btn btn-primary me-2">
                    <i class="bi bi-eye"></i> View
                    </RouterLink>
                    <RouterLink :to="`/editjob/${job.id}`" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </RouterLink>
                    
                   
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Error State -->
        <div v-if="error" class="alert alert-danger mt-3 text-center">{{ error }}</div>
      </div>
    </div>
  </template>
  
  <script>
  import api from "@/services/api.js";
  
  export default {
    data() {
      return {
        jobs: null,
        loading: false,
        error: false,
      };
    },
  
    methods: {
      async fetchJobs() {
        this.loading = true;
        this.error = null;
        try {
          const response = await api.getJobPortals(); // Fetch jobs from the API
          this.jobs = response.data.data; // Access the job data
        } catch (error) {
          this.error = "Failed to load jobs. Please try again.";
        } finally {
          this.loading = false;
        }
      },
    },
  
    mounted() {
      this.fetchJobs(); // Fetch jobs when the component is mounted
    },
  };
  </script>
  
  <style scoped>
  .life {
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
  }
  
  .loading-state {
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1rem;
  }
  
  .job-logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
  }
  
  .job-body {
    margin-top: 0.5rem;
  }
  
  .card-body {
    background: #ddd;
  }
  
  .card-btn .btn {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
  }
  
  .error {
    color: red;
    font-weight: bold;
  }
  </style>
  