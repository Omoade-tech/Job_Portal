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
                type="text"
                id="salary"
                v-model="salary"
                class="form-control"
                placeholder="Enter salary (e.g. $50,000)"
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

            <!-- Error Message -->
            <div v-if="errorMessage" class="alert alert-danger mb-3">
              {{ errorMessage }}
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
  import { useAuthStore } from '@/stores/Auth'
  import api from '@/services/api'

  export default {
    data() {
      return {
        companyLogo: null,
        companyName: '',
        post: '',
        salary: '',
        location: '',
        contract: '',
        description: '',
        responsibility: '',
        loading: false,
        errorMessage: '',
        authStore: null,
        employerId: null
      }
    },
    created() {
      // Initialize the auth store
      this.authStore = useAuthStore()

      // Log authentication details
      console.log('AddJob Created Hook - User Details:', {
        user: this.authStore.user,
        isAuthenticated: this.authStore.isAuthenticated,
        userRole: this.authStore.user?.role
      })

      // Check if user is an employer and set employer ID
      if (!this.authStore.isAuthenticated || this.authStore.user?.role !== 'employer') {
        alert('Only employers can post jobs.')
        this.$router.push('/dashboard')
      } else {
        // Extract employer ID from the authenticated user
        this.employerId = this.authStore.user?.employer_id || this.authStore.user?.id
      }
    },
    methods: {
      onFileChange(event) {
        this.companyLogo = event.target.files[0]
      },
      
      async handlePostJob() {
        this.loading = true
        this.errorMessage = ''

        try {
          const formData = new FormData()
          formData.append('companyName', this.companyName)
          formData.append('post', this.post)
          formData.append('salary', this.salary)
          formData.append('location', this.location)
          formData.append('contract', this.contract)
          formData.append('description', this.description)
          formData.append('responsibility', this.responsibility)

          // Append employer ID
          if (this.employerId) {
            formData.append('employer_id', this.employerId)
          }

          // Append logo if selected
          if (this.companyLogo) {
            formData.append('companyLogo', this.companyLogo)
          }

          // Use sendFormData method
          const response = await api.sendFormData('/job_portals', formData)

          if (response.data) {
            alert('Job Posted Successfully!')
            this.resetForm()
            this.$router.push('/joblisting')
          }
        } catch (error) {
          console.error('Job Posting Error:', error)
          
          if (error.response) {
            console.error('Error Response:', error.response.data)
            
            if (error.response.data.errors) {
              const errors = Object.values(error.response.data.errors).flat()
              this.errorMessage = errors.join(', ')
            } else if (error.response.data.message) {
              this.errorMessage = error.response.data.message
            }
          } else {
            this.errorMessage = "Failed to post job. Please try again."
          }

          alert(this.errorMessage)
        } finally {
          this.loading = false
        }
      },
      
      resetForm() {
        this.companyLogo = null
        this.companyName = ''
        this.post = ''
        this.salary = ''
        this.location = ''
        this.contract = ''
        this.description = ''
        this.responsibility = ''
      }
    }
  }
  </script>
  
  <style lang="scss" scoped>
  .spinner-border {
    width: 1rem;
    height: 1rem;
  }
  </style>