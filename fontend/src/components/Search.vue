<template>
  <div class="container mt-4">
    <div class="input mb-3 d-flex align-items-center">
      <input
        type="text"
        class="form-control w-50 me-2"
        placeholder="Search by job name or company"
        v-model="jobStore.searchQuery"
      />

         <button class="btn btn-primary" type="button" @click="performSearch">
        Search
      </button>
    </div>
  
  </div>
</template>

<script>
import { useJobStore } from "@/stores/jobs";

export default {
  name: "Search",
  data() {
    return {
      searchQuery: "",
    };
  },
  computed: {
    jobStore() {
      return useJobStore();
    },
  },
  // methods: {
  //   updateSearchQuery() {
  //     this.jobStore.updateSearchQuery(this.searchQuery);
  //   },
  // },
  created() {
    // this.jobStore = useJobStore();
    this.jobStore.fetchJobs();
  },
};
</script>

<style scoped>
.input {
  justify-content: center;
}

.form-control {
  max-width: 50%;
}
</style>
