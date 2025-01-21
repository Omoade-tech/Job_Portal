<template>
    <div class="container my-4">
      <h1 class="text-center mb-4">Employers</h1>
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped">
          <thead class="table-dark">
            <tr>
              <th scope="col">S/N</th>
              <th scope="col">Name</th>
              <th scope="col">Email</th>
              <th scope="col">Phone Number</th>
              <th scope="col">Age</th>
              <th scope="col">Sex</th>
              <th scope="col">Address</th>
              <th scope="col">City</th>
              <th scope="col">State</th>
              <th scope="col">Country</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(employer, index) in employers" :key="employer.id">
              <td>{{ index + 1 }}</td>
              <td>{{ employer.name }}</td>
              <td>{{ employer.email }}</td>
              <td>{{ employer.phoneNumber }}</td>
              <td>{{ employer.age }}</td>
              <td>{{ employer.sex }}</td>
              <td>{{ employer.address }}</td>
              <td>{{ employer.city }}</td>
              <td>{{ employer.state }}</td>
              <td>{{ employer.country }}</td>
              <td>{{ employer.status }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="employers.length === 0" class="alert alert-warning text-center" role="alert">
        No employers found.
      </div>
    </div>
  </template>
  
  <script>
  import api from '@/services/api.js';
  
  export default {
    data() {
      return {
        employers: [], 
      };
    },
    created() {
      this.fetchEmployers();
    },
    methods: {
      async fetchEmployers() {
        try {
          const response = await api.getEmployers();
          this.employers = response.data.data; 
        } catch (error) {
          console.error('Error fetching employers:', error);
        }
      },
    },
  };
  </script>
  
  <style scoped>
  
  </style>
  