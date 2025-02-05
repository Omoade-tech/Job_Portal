<template>
  <div class="employer-dashboard">
    <!-- Error Alert -->
    <div v-if="error" class="alert alert-danger" role="alert">
      {{ error }}
      <button type="button" class="btn-close" @click="error = null"></button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center my-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div v-if="!loading" class="dashboard-content">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Job Applications</h2>
        <div class="d-flex gap-2">
          <select v-model="statusFilter" class="form-select form-select-sm" style="width: auto;">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="accepted">Accepted</option>
            <option value="rejected">Rejected</option>
          </select>
          <button @click="handleGetApplications" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-clockwise"></i> Refresh
          </button>
        </div>
      </div>

      <!-- Applications Table -->
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Job Title</th>
              <th>Applied Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="applicant in filteredApplicants" :key="applicant.id">
              <td>
                <div class="fw-bold">{{ applicant.job.title }}</div>
                <small class="text-muted">{{ applicant.job.company }}</small>
              </td>
              <td>
                <span :class="getStatusClass(applicant.status)">
                  {{ formatStatus(applicant.status) }}
                </span>
              </td>
              <td>
                <small>{{ formatDate(applicant.created_at) }}</small>
              </td>
              <td>
                <div class="btn-group">
                  <button v-if="applicant.status === 'pending'"
                          @click="updateStatus(applicant.id, 'accepted')" 
                          class="btn btn-sm btn-success">
                    <i class="bi bi-check-lg"></i> Accept
                  </button>
                  <button v-if="applicant.status === 'pending'"
                          @click="updateStatus(applicant.id, 'rejected')" 
                          class="btn btn-sm btn-danger">
                    <i class="bi bi-x-lg"></i> Reject
                  </button>
                  <button v-if="applicant.status !== 'pending'"
                          @click="updateStatus(applicant.id, 'pending')" 
                          class="btn btn-sm btn-warning">
                    Reset
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!loading && filteredApplicants.length === 0">
              <td colspan="4" class="text-center py-4">
                <div class="alert alert-info mb-0">
                  <i class="bi bi-info-circle me-2"></i>
                  {{ statusFilter ? `No ${statusFilter} applications found.` : 'No applications found yet.' }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api'
import { useAuthStore } from '@/stores/Auth'
import { mapState } from 'pinia'

export default {
  name: 'EmployerDashboard',
  data() {
    return {
      applicants: [],
      loading: false,
      error: null,
      statusFilter: '',
    }
  },
  computed: {
    ...mapState(useAuthStore, ['user', 'isAuthenticated']),
    filteredApplicants() {
      if (!this.statusFilter) return this.applicants
      return this.applicants.filter(app => app.status === this.statusFilter)
    }
  },
  methods: {
    async mounted() {
      const authStore = useAuthStore()
      
      try {
        // Perform comprehensive authentication check
        const authenticatedUser = await authStore.checkAuth()
        
        // Log the entire user object to understand its structure
        console.log('Authenticated User Object:', authenticatedUser)
        
        // More robust employer ID extraction
        const employerId = 
          authenticatedUser?.employer_id || 
          authenticatedUser?.model_id || 
          (authenticatedUser?.role === 'employer' ? authenticatedUser.id : null)

        if (!employerId) {
          console.error('No valid employer ID found')
          this.error = 'Unable to identify employer. Please log in again.'
          this.$router.push('/login')
          return
        }

        // Pass the confirmed employer ID
        await this.getEmployerApplication(employerId)
      } catch (error) {
        console.error('Mounting error:', error)
        this.error = 'Failed to initialize dashboard. Please log in again.'
        this.$router.push('/login')
      }
    },
    async getEmployerApplication(employerId) {
      try {
        console.group('Employer Application Fetch')
        console.log('Attempting to fetch applications with Employer ID:', {
          employerId,
          type: typeof employerId,
          stringValue: String(employerId)
        })

        // Ensure loading state is set
        this.loading = true
        this.error = null

        // Log the full authenticated user object
        console.log('Current Authenticated User:', this.user)

        // Detailed API call logging
        console.log('Calling API with Employer ID:', employerId)
        const response = await api.getEmployerApplications(employerId)
        
        console.log('Full API Response:', {
          success: response.success,
          data: response.data,
          dataType: typeof response.data,
          dataLength: response.data ? response.data.length : 'N/A'
        })

        // Ensure data is processed correctly
        if (response && response.data) {
          this.applicants = response.data || []
          
          console.log('Processed Applications:', {
            count: this.applicants.length,
            applications: this.applicants
          })

          // Set user-friendly message if no applications
          if (this.applicants.length === 0) {
            this.error = 'No job applications found for this employer.'
            console.warn('No applications found for employer')
          }
        } else {
          // Handle unexpected response structure
          console.error('Invalid response structure:', response)
          throw new Error('Invalid response format from server')
        }

        console.groupEnd()
      } catch (error) {
        console.group('Employer Application Fetch Error')
        console.error('Application Fetch Error:', {
          message: error.message,
          response: error.response,
          fullError: error
        })

        // Set specific error messages based on error type
        if (error.response) {
          switch (error.response.status) {
            case 401:
              this.error = 'Session expired. Please log in again.'
              this.$router.push('/login')
              break
            case 403:
              this.error = 'You are not authorized to view these applications.'
              break
            case 404:
              this.error = 'No applications found for this employer.'
              break
            default:
              this.error = error.response.data.message || 
                           'Failed to fetch applications. Please try again later.'
          }
        } else {
          this.error = error.message || 
                       'Network error or server unavailable. Please check your connection.'
        }
        console.groupEnd()
      } finally {
        // Ensure loading state is always turned off
        this.loading = false
      }
    },
    async handleGetApplications() {
      const authStore = useAuthStore()
      const authenticatedUser = authStore.user

      const employerId = 
        authenticatedUser?.employer_id || 
        authenticatedUser?.model_id || 
        (authenticatedUser?.role === 'employer' ? authenticatedUser.id : null)

      if (employerId) {
        await this.getEmployerApplication(employerId)
      } else {
        console.error('No employer ID found')
        this.error = 'Unable to retrieve employer applications. Please log in again.'
      }
    },
    async updateStatus(applicationId, status) {
      try {
        this.error = null
        await api.updateApplicationStatus(applicationId, { status })
        await this.getEmployerApplication()
      } catch (error) {
        this.error = 'Failed to update application status: ' + 
          (error.message || 'Unknown error')
        console.error('Error updating application status:', error)
      }
    },

    getStatusClass(status) {
      const classes = {
        pending: 'badge bg-warning text-dark',
        accepted: 'badge bg-success',
        rejected: 'badge bg-danger'
      }
      return classes[status] || 'badge bg-secondary'
    },
    formatStatus(status) {
      return status ? status.charAt(0).toUpperCase() + status.slice(1) : status
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
  },
}
</script>

<style lang="scss" scoped>
.employer-dashboard {
  padding: 20px;

  .dashboard-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
  }

  .table {
    th {
      font-weight: 600;
      color: #495057;
    }

    td {
      vertical-align: middle;
    }
  }

  .badge {
    padding: 8px 12px;
    font-size: 0.85rem;
  }

  .btn-group {
    .btn {
      padding: 0.25rem 0.5rem;
      font-size: 0.875rem;
    }
  }

  .debug-info {
    background: #f8f9fa;
    padding: 8px;
    border-radius: 4px;
  }

  .alert {
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal {
    background-color: rgba(0, 0, 0, 0.5);

    &.show {
      display: block;
    }
  }

  .modal-dialog {
    margin: 1.75rem auto;
  }

  .modal-content {
    background-color: white;
    border-radius: 0.3rem;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
  }

  .modal-body {
    padding: 1rem;
  }

  .btn-close {
    background: transparent;
    border: 0;
    font-size: 1.5rem;
    padding: 0.5rem;
    cursor: pointer;
    opacity: 0.5;

    &:hover {
      opacity: 1;
    }
  }
}
</style>