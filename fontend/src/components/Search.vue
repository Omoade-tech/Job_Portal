<template>
  <div class="container mt-4">
    <div class="input-group mb-3">
      <select
        class="form-select w-25"
        v-model="selectedFilter"
      >
        <option value="query">General Search</option>
        <option value="companyName">Company Name</option>
        <option value="post">Post</option>
        <option value="location">Location</option>
        <option value="contract">Contract Type</option>
      </select>

      <input
        type="text"
        class="form-control"
        placeholder="Enter your search query"
        v-model="searchQuery"
        @keyup.enter="performSearch"
      />

      <button class="btn btn-primary" type="button" @click="performSearch">
        Search
      </button>
    </div>

    <!-- Results section -->
    <div v-if="searchResults.length" class="mt-4">
      <h4>Search Results</h4>
      <div class="list-group">
        <div v-for="job in searchResults" :key="job.id" class="list-group-item">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="mb-1">{{ job.companyName }}</h5>
              <p class="mb-1">{{ job.post }}</p>
              <small>{{ job.location }} • {{ job.contract }}</small>
            </div>
            <div class="text-end">
              <div class="badge bg-primary">{{ job.salary }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div v-else-if="searchPerformed" class="mt-4 text-center">
      <p>No jobs found matching your search criteria.</p>
    </div>

    <!-- Loading state -->
    <div v-if="isLoading" class="text-center mt-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/services/api.js";

export default {
  name: "Search",
  data() {
    return {
      selectedFilter: "query",
      searchQuery: "",
      searchResults: [],
      searchPerformed: false,
      isLoading: false,
      error: null
    };
  },
  methods: {
    async performSearch() {
      if (!this.searchQuery.trim()) {
        alert("Please enter a search query.");
        return;
      }

      this.isLoading = true;
      this.error = null;

      try {
        const response = await api.searchJobPortals({
          filterType: this.selectedFilter,
          query: this.searchQuery.trim()
        });

        this.searchResults = response.data.data;
        this.searchPerformed = true;
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while searching";
        console.error("Search error:", error);
        alert(this.error);
      } finally {
        this.isLoading = false;
      }
    },

    clearSearch() {
      this.searchQuery = "";
      this.searchResults = [];
      this.searchPerformed = false;
      this.error = null;
    }
  }
};
</script>

<style scoped>
.input-group {
  max-width: 800px;
  margin: 0 auto;
}

.list-group {
  max-width: 800px;
  margin: 0 auto;
}

.list-group-item {
  transition: background-color 0.2s;
}

.list-group-item:hover {
  background-color: #f8f9fa;
}

.badge {
  font-size: 0.9rem;
  padding: 0.5em 1em;
}
</style>