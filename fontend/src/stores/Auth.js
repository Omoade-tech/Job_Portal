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

      const response = await api.login({
        email: credentials.email,
        password: credentials.password,
        rememberMe: credentials.rememberMe
      });

      if (response.data && response.data.token) {
        token.value = response.data.token;
        user.value = response.data.user;

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
      
      if (!storedToken || !storedUserStr) {
        user.value = null;
        token.value = null;
        return null;
      }

      try {
        const storedUser = JSON.parse(storedUserStr);
        user.value = storedUser;
        token.value = storedToken;
        
        // Set Authorization header
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
        
        console.log('Auth state updated:', {
          user: user.value,
          isAuthenticated: isAuthenticated.value
        });
        
        return user.value;
      } catch (e) {
        console.error('Error parsing stored user data:', e);
        user.value = null;
        token.value = null;
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('user');
        return null;
      }
    } catch (err) {
      console.error('Error checking auth:', err);
      user.value = null;
      token.value = null;
      return null;
    }
  }

  async function logout() {
    try {
      if (api.logout) {
        await api.logout();
      }
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      user.value = null;
      token.value = null;
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      delete apiClient.defaults.headers.common['Authorization'];
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