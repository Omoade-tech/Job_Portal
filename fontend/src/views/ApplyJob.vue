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
              <label class="form-label">Full Name</label>
              <input v-model="formData.name" type="text" class="form-control" required />
            </div>
  
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input v-model="formData.email" type="email" class="form-control" required />
            </div>
  
            <div class="mb-3">
              <label class="form-label">Phone Number</label>
              <input v-model="formData.phoneNumber" type="text" class="form-control" required />
            </div>
  
            <div class="mb-3">
              <label class="form-label">Address</label>
              <textarea v-model="formData.address" class="form-control" rows="3" required></textarea>
            </div>
  
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
  
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label">References</label>
                <button type="button" class="btn btn-outline-primary btn-sm" @click="addReference">
                  Add Reference
                </button>
              </div>
  
              <div
                v-for="(reference, index) in formData.references"
                :key="index"
                class="card mb-3"
              >
                <div class="card-body">
                  <div class="mb-2">
                    <label class="form-label">Name</label>
                    <input v-model="reference.name" type="text" class="form-control" required />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Phone Number</label>
                    <input
                      v-model="reference.phoneNumber"
                      type="text"
                      class="form-control"
                      required
                    />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input v-model="reference.email" type="email" class="form-control" required />
                  </div>
                </div>
              </div>
            </div>
  
            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
              {{ loading ? 'Submitting...' : 'Submit Application' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import api from "@/services/api.js";
  
  export default {
    name: "JobApplicationForm",
    data() {
      return {
        formData: {
          name: "",
          email: "",
          phoneNumber: "",
          address: "",
          coverLetter: "",
          resume: null,
          references: [
            { name: "", phoneNumber: "", email: "" },
          ],
        },
        error: "",
        success: "",
        loading: false,
      };
    },
  
    methods: {
      handleFileChange(e) {
        this.formData.resume = e.target.files[0];
      },
  
      addReference() {
        this.formData.references.push({
          name: "",
          phoneNumber: "",
          email: "",
        });
      },
  
      async handleSubmit() {
        this.loading = true;
        this.error = "";
        this.success = "";
  
        try {
          const formData = new FormData();
          Object.keys(this.formData).forEach((key) => {
            if (key === "references") {
              formData.append(key, JSON.stringify(this.formData[key]));
            } else if (key === "resume" && this.formData[key]) {
              formData.append(key, this.formData[key]);
            } else {
              formData.append(key, this.formData[key]);
            }
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
          name: "",
          email: "",
          phoneNumber: "",
          address: "",
          coverLetter: "",
          resume: null,
          references: [{ name: "", phoneNumber: "", email: "" }],
        };
  
        // Reset file input
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput) fileInput.value = "";
      },
    },
  };
  </script>
  