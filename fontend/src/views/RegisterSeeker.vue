<template>
    <div class="container my-4">
      <h1 class="text-center mb-4">Job Seekers</h1>
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped">
          <thead class="table-dark">
            <tr>
              <th scope="col">S/N</th>
              <th scope="col">Name</th>
              <th scope="col">Email</th>
              <th scope="col">Phone Number</th>
              <th scope="col">Age</th>
              <th scope="col">City</th>
              <th scope="col">State</th>
              <th scope="col">Country</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(jobseeker, index) in jobSeekers" :key="jobseeker.id">
              <td>{{ index + 1 }}</td>
              <td>{{ jobseeker.name }}</td>
              <td>{{ jobseeker.email }}</td>
              <td>{{ jobseeker.phoneNumber }}</td>
              <td>{{ jobseeker.age }}</td>
              <td>{{ jobseeker.city }}</td>
              <td>{{ jobseeker.state }}</td>
              <td>{{ jobseeker.country }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="jobSeekers.length === 0" class="alert alert-warning text-center" role="alert">
        No job seekers found.
      </div>
    </div>
  </template>
  
  <script>
  import api from '@/services/api.js'; 
  
  export default {
    data() {
      return {
        jobSeekers: [],
      };
    },
    created() {
      this.fetchJobSeekers();
    },
    methods: {
      async fetchJobSeekers() {
        try {
          const response = await api.getJobSeekers();
          this.jobSeekers = response.data.data; 
        } catch (error) {
          console.error('Error fetching job seekers:', error);
        }
      },
    },
  };
  </script>
  
  <style scoped>

  </style>
  