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
import { useAuthStore } from '@/stores/Auth';

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
        console.log('Attempting login with:', { email: this.email, rememberMe: this.rememberMe });
        const response = await api.login({
          email: this.email,
          password: this.password,
          rememberMe: this.rememberMe
        });

        console.log('Login response:', {
          hasToken: !!response?.data?.token,
          hasUser: !!response?.data?.user,
          userData: response?.data?.user
        });

        if (response.data && response.data.token) {
          // Update auth store
          const authStore = useAuthStore();
          await authStore.login({
            email: this.email,
            password: this.password,
            rememberMe: this.rememberMe
          });

          // Get return URL or determine dashboard based on role
          const returnUrl = localStorage.getItem('intendedRoute');
          const userData = response.data.user;
          
          // Safely get user role and determine route
          let targetRoute = '/';
          if (userData && userData.role) {
            const roleRoutes = {
              admin: '/AdminDashboard',
              employer: '/EmployerDashboard',
              job_seeker: `/JobSeekerDashboard/${userData.id}`
            };
            targetRoute = roleRoutes[userData.role] || '/';
            console.log('Determined target route:', targetRoute);
          }

          // Navigate to appropriate route
          if (returnUrl) {
            localStorage.removeItem('intendedRoute');
            console.log('Navigating to return URL:', returnUrl);
            await this.$router.push(returnUrl);
          } else {
            console.log('Navigating to target route:', targetRoute);
            await this.$router.push(targetRoute);
          }
        } else {
          this.error = 'Invalid response from server. Please try again.';
        }
      } catch (error) {
        console.error('Login error details:', {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        console.error('Login error:', error);
        if (error.response) {
          // Handle specific error responses
          switch (error.response.status) {
            case 401:
              this.error = 'Invalid email or password';
              break;
            case 422:
              this.error = Object.values(error.response.data.errors)[0][0];
              break;
            case 429:
              this.error = 'Too many login attempts. Please try again later.';
              break;
            default:
              this.error = error.response.data.message || 'An error occurred during login';
          }
        } else if (error.code === 'ERR_NETWORK') {
          this.error = 'Unable to connect to the server. Please check your connection and try again.';
        } else {
          this.error = 'An unexpected error occurred. Please try again.';
        }
      } finally {
        this.isLoading = false;
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