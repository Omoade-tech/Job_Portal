<template>
    <div class="container mt-5">
      <h3 class="text-center mb-4">Add Job</h3>
  
      <div class="row justify-content-center">
        <div class="col-md-8">
          <!-- Job Posting Form -->
          <form @submit.prevent="handlePostJob">
            <!-- Company Logo -->
            <div class="mb-3">
              <label for="companyLogo" class="form-label">Company Logo</label>
              <input
                type="file"
                id="companyLogo"
                class="form-control"
                accept="image/*"
                @change="onFileChange"
                required
              />
            </div>
  
            <!-- Company Name -->
            <div class="mb-3">
              <label for="companyName" class="form-label">Company Name</label>
              <input
                type="text"
                id="companyName"
                v-model="companyName"
                class="form-control"
                placeholder="Enter company name"
                required
              />
            </div>
  
            <!-- Job Post -->
            <div class="mb-3">
              <label for="post" class="form-label">Job Post</label>
              <input
                type="text"
                id="post"
                v-model="post"
                class="form-control"
                placeholder="Enter job post"
                required
              />
            </div>
  
            <!-- Salary -->
            <div class="mb-3">
              <label for="salary" class="form-label">Salary</label>
              <input
                type="number"
                id="salary"
                v-model="salary"
                class="form-control"
                placeholder="Enter salary"
                required
              />
            </div>
  
            <!-- Location -->
            <div class="mb-3">
              <label for="location" class="form-label">Location</label>
              <input
                type="text"
                id="location"
                v-model="location"
                class="form-control"
                placeholder="Enter job location"
                required
              />
            </div>
  
            <!-- Contract Type -->
            <div class="mb-3">
              <label for="contract" class="form-label">Contract Type</label>
              <select
                id="contract"
                v-model="contract"
                class="form-select"
                required
              >
                <option value="" disabled>Select contract type</option>
                <option value="fulltime">Full-Time</option>
                <option value="remote">Remote</option>
                <option value="parttime">Part-Time</option>
              </select>
            </div>
  
            <!-- Description -->
            <div class="mb-3">
              <label for="description" class="form-label">Job Description</label>
              <textarea
                id="description"
                v-model="description"
                class="form-control"
                rows="4"
                placeholder="Enter job description"
                required
              ></textarea>
            </div>
  
            <!-- Responsibility -->
            <div class="mb-3">
              <label for="responsibility" class="form-label">Responsibilities</label>
              <textarea
                id="responsibility"
                v-model="responsibility"
                class="form-control"
                rows="4"
                placeholder="Enter job responsibilities"
                required
              ></textarea>
            </div>
  
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              Post Job
            </button>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import jobApi from "@/services/api.js";
  
  export default {
    data() {
      return {
        companyLogo: null,
        companyName: "",
        post: "",
        salary: null,
        location: "",
        contract: "",
        description: "",
        responsibility: "",
        loading: false,
      };
    },
    methods: {
      onFileChange(event) {
        this.companyLogo = event.target.files[0];
      },
      async handlePostJob() {
        this.loading = true;
  
        try {
          // Prepare form data
          const formData = new FormData();
          formData.append("companyLogo", this.companyLogo);
          formData.append("companyName", this.companyName);
          formData.append("post", this.post);
          formData.append("salary", this.salary);
          formData.append("location", this.location);
          formData.append("contract", this.contract);
          formData.append("description", this.description);
          formData.append("responsibility", this.responsibility);
  
          // Post the job
          await jobApi.createJobPortal(formData);
          alert("Job posted successfully!");
          this.resetForm();
        } catch (error) {
          console.error("Failed to post job:", error);
          alert(
            error.response?.data?.errors
              ? JSON.stringify(error.response.data.errors)
              : "Failed to post job. Please try again."
          );
        } finally {
          this.loading = false;
        }
      },
      resetForm() {
        this.companyLogo = null;
        this.companyName = "";
        this.post = "";
        this.salary = null;
        this.location = "";
        this.contract = "";
        this.description = "";
        this.responsibility = "";
      },
    },
  };
  </script>
  
  <style lang="scss" scoped></style>
  