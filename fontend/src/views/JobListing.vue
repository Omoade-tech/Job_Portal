<template>
  <div class="life">
    <div id="job-container" class="container mt-4">
      <!-- Search Section -->
      <div class="search-section mb-4">
        <div class="row">
          <div class="col-md-8 mx-auto">
            <div class="input-group">
              <input
                type="text"
                class="form-control"
                v-model="searchQuery"
                :placeholder="`Search by ${searchFilter}...`"
                @keyup.enter="handleSearch"
              />
              <select class="form-select" v-model="searchFilter" style="max-width: 150px;">
                <option value="companyName">Company</option>
                <option value="post">Position</option>
                <option value="location">Location</option>
              </select>
              <button class="btn btn-primary" @click="handleSearch">
                Search
              </button>
              <button class="btn btn-secondary" @click="clearSearch" v-if="isSearchActive">
                Clear
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center text-info my-4">Loading jobs...</div>

      <!-- No Results Message -->
      <div v-else-if="paginatedJobs.length === 0" class="text-center my-4">
        No jobs found matching your search criteria.
      </div>

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
                  :src="job.companyLogo || defaultLogo"
                  :alt="`${job.companyName} logo`"
                  class="job-logo me-3"
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
                <p><strong>Description:</strong> {{ truncateText(job.description, 100) }}</p>
              </div>
              <div class="card-btn m-3 text-end">
                <button class="btn btn-success" type="button">
                  <RouterLink to="/applyJob" class="nav-link">Apply</RouterLink>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-container text-center mt-4">
          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button class="page-link" @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
                  Previous
                </button>
              </li>
              <li v-for="page in totalPages" 
                  :key="page" 
                  class="page-item"
                  :class="{ active: currentPage === page }">
                <button class="page-link" @click="changePage(page)">
                  {{ page }}
                </button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button class="page-link" @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">
                  Next
                </button>
              </li>
            </ul>
          </nav>
          <div class="text-muted">
            Showing {{ paginationInfo.from }} to {{ paginationInfo.to }} of {{ filteredJobs.length }} entries
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
      jobs: [],
      loading: false,
      error: null,
      searchQuery: "",
      searchFilter: "companyName",
      isSearchActive: false,
      currentPage: 1,
      itemsPerPage: 6,
      defaultLogo: "https://via.placeholder.com/160" 
    };
  },

  computed: {
    filteredJobs() {
      if (!this.searchQuery) return this.jobs;

      const query = this.searchQuery.toLowerCase();
      return this.jobs.filter(job => {
        const value = String(job[this.searchFilter]).toLowerCase();
        return value.includes(query);
      });
    },

    paginatedJobs() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredJobs.slice(start, end);
    },

    totalPages() {
      return Math.ceil(this.filteredJobs.length / this.itemsPerPage);
    },

    paginationInfo() {
      const from = (this.currentPage - 1) * this.itemsPerPage + 1;
      const to = Math.min(this.currentPage * this.itemsPerPage, this.filteredJobs.length);
      return { from, to };
    }
  },

  methods: {
    async fetchJobs() {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.getJobPortals();
        this.jobs = response.data.data;
        this.currentPage = 1; // Reset to first page when fetching new data
      } catch (error) {
        this.error = "Failed to load jobs. Please try again.";
        console.error("Error fetching jobs:", error);
      } finally {
        this.loading = false;
      }
    },

    async handleSearch() {
      if (!this.searchQuery.trim()) {
        return;
      }

      this.loading = true;
      this.error = null;
      this.isSearchActive = true;
      this.currentPage = 1; // Reset to first page when searching

      try {
        const response = await api.searchJobPortals({
          [this.searchFilter]: this.searchQuery.trim()
        });
        this.jobs = response.data.data;
      } catch (error) {
        this.error = "Failed to perform search. Please try again.";
        console.error("Search error:", error);
      } finally {
        this.loading = false;
      }
    },

    clearSearch() {
      this.searchQuery = "";
      this.isSearchActive = false;
      this.currentPage = 1; // Reset to first page when clearing search
      this.fetchJobs();
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
    },

    truncateText(text, length) {
      if (text.length <= length) return text;
      return text.substring(0, length) + "...";
    },

    handleImageError(event) {
      event.target.src = this.defaultLogo; // Fallback to default logo URL
    }
  },

  mounted() {
    this.fetchJobs();
  },

  watch: {
    searchQuery(newVal) {
      // Reset to first page when search query changes
      if (!newVal && !this.isSearchActive) {
        this.currentPage = 1;
      }
    }
  }
};
</script>

<style scoped>
.life {
  background-position: center;
  background-size: cover;
  background-repeat: no-repeat;
}

.search-section {
  background-color: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
}

.job-logo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.card {
  transition: transform 0.2s;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card:hover {
  transform: translateY(-5px);
}

.card-body {
  background: #f8f9fa;
}

.pagination-container {
  margin-top: 2rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.page-link {
  color: #0d6efd;
  cursor: pointer;
}

.page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.nav-link {
  color: white;
  text-decoration: none;
}

@media (max-width: 768px) {
  .search-section .input-group {
    flex-wrap: nowrap;
  }
  
  .col-md-4 {
    margin-bottom: 1rem;
  }

  .pagination {
    flex-wrap: wrap;
    justify-content: center;
  }
}
</style>
