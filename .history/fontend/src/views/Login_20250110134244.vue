<template>
  <div class="login-container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
      <!-- Header -->
      <h2 class="text-center mb-4">Login</h2>

      <!-- Alert Messages -->
      <div v-if="error" class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ error }}
        <button type="button" class="btn-close" @click="error = ''"></button>
      </div>

      <form @submit.prevent="handleLogin">
        <!-- Email Input -->
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-envelope"></i>
            </span>
            <input 
              type="email" 
              id="email" 
              v-model="email" 
              class="form-control" 
              :class="{ 'is-invalid': emailError }"
              placeholder="Enter your email" 
              required 
              @input="clearErrors"
            />
            <div class="invalid-feedback">{{ emailError }}</div>
          </div>
        </div>

        <!-- Password Input -->
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-lock"></i>
            </span>
            <input 
              :type="showPassword ? 'text' : 'password'" 
              id="password" 
              v-model="password" 
              class="form-control" 
              :class="{ 'is-invalid': passwordError }"
              placeholder="Enter your password" 
              required 
              @input="clearErrors"
            />
            <button 
              class="btn btn-outline-secondary" 
              type="button"
              @click="togglePassword"
            >
              <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
            <div class="invalid-feedback">{{ passwordError }}</div>
          </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="mb-3 form-check">
          <input 
            type="checkbox" 
            class="form-check-input" 
            id="rememberMe" 
            v-model="rememberMe"
          >
          <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>

        <!-- Submit Button -->
        <button 
          type="submit" 
          class="btn btn-primary w-100 mb-3" 
          :disabled="isLoading"
        >
          <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
          {{ isLoading ? 'Logging in...' : 'Login' }}
        </button>

        <!-- Register Link -->
        <div class="text-center">
          <span class="text-muted">Don't have an account? </span>
          <router-link to="/signup" class="text-primary text-decoration-none">
            Register here
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import api from '@/services/api.js';

export default {
  name: 'LoginView',
  data() {
    return {
      email: '',
      password: '',
      error: '',
      emailError: '',
      passwordError: '',
      isLoading: false,
      showPassword: false,
      rememberMe: false
    };
  },
  methods: {
    clearErrors() {
      this.error = '';
      this.emailError = '';
      this.passwordError = '';
    },
    togglePassword() {
      this.showPassword = !this.showPassword;
    },
    validateForm() {
      let isValid = true;
      this.clearErrors();

      if (!this.email) {
        this.emailError = 'Email is required';
        isValid = false;
      } else if (!this.isValidEmail(this.email)) {
        this.emailError = 'Please enter a valid email address';
        isValid = false;
      }

      if (!this.password) {
        this.passwordError = 'Password is required';
        isValid = false;
      }

      return isValid;
    },
    isValidEmail(email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    },
    async handleLogin() {
  if (!this.validateForm()) return;

  this.isLoading = true;
  this.clearErrors();

  try {
    // console.log('Sending API request...');
    // console.log('API endpoint URL:', 'http://localhost:8000/api/login');
    // console.log('API request headers:', {
    //   'Content-Type': 'application/json',
    //   'Accept': 'application/json'
    // });
    // console.log('API request body:', {
    //   email: this.email,
    //   password: this.password,
    //   remember: this.rememberMe
    // });

    const response = await api.login({
      email: this.email,
      password: this.password,
      remember: this.rememberMe
    });
    // console.log('API response:', response);

    if (response.data.success) {
      // Store user data and token
      localStorage.setItem('user', JSON.stringify(response.data.data));
      if (response.data.token) {
        localStorage.setItem('token', response.data.token);
      }

      const userRole = response.data.data.role;
const roleRoutes = {
  admin: 'AdminDashboard',
  employer: 'EmployerDashboard',
  job_seeker: 'JobSeekerDashboard'
};

const userId = response.data.data.id;

await this.$router.push({ 
  name: roleRoutes[userRole] || '/', 
  params: { id: userId } 
});
    } else {
      this.error = response.data.message || 'An unexpected error occurred. Please try again.';
    }
  } catch (error) {
    console.error('API error:', error);
    this.error = 'An unexpected error occurred. Please try again.';
  } finally {
    this.isLoading = false;
  }
},
    handleError(error) {
      if (error.response) {
        if (error.response.status === 204) {
          // Handle successful response with no content
          this.error = '';
          this.$router.push({ name: 'dashboard' });
        } else {
          // Handle other error cases
          switch (error.response.status) {
            case 422:
              const errors = error.response.data.errors;
              if (errors.email) this.emailError = errors.email[0];
              if (errors.password) this.passwordError = errors.password[0];
              break;
            case 401:
              this.error = 'Invalid email or password';
              break;
            default:
              this.error = error.response.data.message || 'An unexpected error occurred. Please try again.';
          }
        }
      } else if (error.request) {
        this.error = 'Network error. Please check your connection.';
      } else {
        this.error = 'An unexpected error occurred. Please try again.';
      }
    }
  }
};
</script>

<style scoped>
.login-container {
  background-color: #f8f9fa;
}

.card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.input-group-text {
  background-color: transparent;
  border-right: none;
}

.input-group .form-control {
  border-left: none;
}

.input-group .form-control:focus {
  border-left: none;
  box-shadow: none;
}

.btn-outline-secondary {
  border-left: none;
}

.btn-outline-secondary:focus {
  box-shadow: none;
}

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.invalid-feedback {
  display: block;
}

/* Animation for loading spinner */
.spinner-border {
  width: 1rem;
  height: 1rem;
  border-width: 0.15em;
}
</style>