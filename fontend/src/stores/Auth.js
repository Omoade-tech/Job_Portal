import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api, { apiClient } from '@/services/api.js';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('token'));
  const isAuthenticated = computed(() => !!user.value && !!token.value);
  const isLoading = ref(false);
  const error = ref(null);

  async function login(credentials) {
    try {
      isLoading.value = true;
      error.value = null;

      const response = await api.login(credentials);
      console.log('Auth store login response:', response.data);

      if (response.data) {
        token.value = response.data.token;
        user.value = response.data.user;

        // Store auth data based on remember me
        if (credentials.rememberMe) {
          localStorage.setItem('token', token.value);
          localStorage.setItem('user', JSON.stringify(user.value));
        } else {
          sessionStorage.setItem('token', token.value);
          sessionStorage.setItem('user', JSON.stringify(user.value));
        }

        // Set Authorization header
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
        return response.data;
      }
      throw new Error('Invalid response format');
    } catch (err) {
      console.error('Auth store login error:', {
        message: err.message,
        response: err.response?.data
      });
      error.value = err.response?.data?.message || 'Login failed';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function checkAuth() {
    try {
      const storedToken = localStorage.getItem('token') || sessionStorage.getItem('token');
      const storedUserStr = localStorage.getItem('user') || sessionStorage.getItem('user');
      
      console.log('Auth Check Details:', {
        hasToken: !!storedToken,
        hasUserStr: !!storedUserStr
      });

      // If no token or user, soft logout without aggressive redirection
      if (!storedToken || !storedUserStr) {
        console.warn('No authentication data found')
        this.user = null
        this.token = null
        return null
      }

      try {
        const storedUser = JSON.parse(storedUserStr);
        
        // More resilient employer ID extraction
        const employerId = 
          storedUser.employer_id || 
          storedUser.model_id || 
          (storedUser.role === 'employer' ? storedUser.id : null) ||
          (storedUser.employer && storedUser.employer.id) ||
          null;

        console.log('Detailed User Authentication Log:', {
          fullUserObject: storedUser,
          id: storedUser.id,
          email: storedUser.email,
          role: storedUser.role,
          employerId: employerId,
          isEmployer: storedUser.role === 'employer'
        });

        // Update user object with extracted employer ID
        const updatedUser = {
          ...storedUser,
          employer_id: employerId
        };

        this.user = updatedUser;
        this.token = storedToken;
        
        // Set Authorization header
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
        
        console.log('Final Auth State:', {
          user: this.user,
          isAuthenticated: this.isAuthenticated,
          isEmployerRole: this.user?.role === 'employer'
        });
        
        return updatedUser;
      } catch (e) {
        console.error('Error parsing stored user data:', e)
        this.user = null
        this.token = null
        return null
      }
    } catch (err) {
      console.error('Error checking auth:', err)
      this.user = null
      this.token = null
      return null
    }
  }

  async function logout() {
    try {
      // Attempt backend logout if possible
      if (api.logout) {
        await api.logout();
      }
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      // Soft logout without page reload
      this.user = null
      this.token = null
      
      // Clear authentication-related storage
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      sessionStorage.removeItem('token')
      sessionStorage.removeItem('user')
      
      // Remove Authorization header
      delete apiClient.defaults.headers.common['Authorization']
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isLoading,
    error,
    login,
    logout,
    checkAuth
  };
});