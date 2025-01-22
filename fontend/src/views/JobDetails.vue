<template>
  <div class="life">
    <div id="job-container" class="container mt-4">
      <!-- Loading State -->
      <div v-if="loading" class="text-center text-info my-4">Loading jobs...</div>

      <!-- Job Cards -->
      <div v-else>
        <div class="row">
          <div
            v-for="job in paginatedJobs"
            :key="job.id"
            class="col-md-4 mb-4"
          >
            <div class="card h-100">
              <!-- Card Header -->
              <div class="card-header d-flex align-items-center">
                <img
                  :src="job.company_logo || defaultLogo"
                  :alt="job.companyName + ' logo'"
                  class="company-logo me-3"
                  @error="handleImageError"
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
                <!-- <RouterLink to="/applyJob" class="btn btn-success">
                  Apply
                </RouterLink> -->

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

        <!-- Pagination -->
        <nav class="mt-4">
          <ul class="pagination justify-content-center">
            <li
              class="page-item"
              :class="{ disabled: currentPage === 1 }"
              @click="changePage(currentPage - 1)"
            >
              <a class="page-link" href="#">Previous</a>
            </li>
            <li
              class="page-item"
              v-for="page in totalPages"
              :key="page"
              :class="{ active: currentPage === page }"
              @click="changePage(page)"
            >
              <a class="page-link" href="#">{{ page }}</a>
            </li>
            <li
              class="page-item"
              :class="{ disabled: currentPage === totalPages }"
              @click="changePage(currentPage + 1)"
            >
              <a class="page-link" href="#">Next</a>
            </li>
          </ul>
        </nav>
      </div>

      <!-- Error State -->
      <div v-if="error" class="alert alert-danger mt-3 text-center">{{ error }}</div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api.js';
import defaultLogo from '@/assets/default-company-logo.svg';

export default {
  data() {
    return {
      jobs: null,
      loading: false,
      error: false,
      defaultLogo: defaultLogo,
      currentPage: 1, // Current active page
      itemsPerPage: 6, // Jobs per page
    };
  },

  computed: {
    // Compute paginated jobs for the current page
    paginatedJobs() {
      if (!this.jobs) return [];
      const startIndex = (this.currentPage - 1) * this.itemsPerPage;
      const endIndex = startIndex + this.itemsPerPage;
      return this.jobs.slice(startIndex, endIndex);
    },

    // Compute the total number of pages
    totalPages() {
      return this.jobs ? Math.ceil(this.jobs.length / this.itemsPerPage) : 0;
    },
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

    // Handle image loading errors
    handleImageError(event) {
      event.target.src = this.defaultLogo; // Replace with the default logo if image fails to load
    },

    // Change the current page
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
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

.company-logo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
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

/* Pagination Styles */
.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
  color: white;
}

.pagination .page-item .page-link {
  color: #007bff;
  cursor: pointer;
}

.pagination .page-item.disabled .page-link {
  color: #6c757d;
  pointer-events: none;
}
</style>
