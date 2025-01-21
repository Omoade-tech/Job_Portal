// src/stores/Auth.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    usersByRole: [],
    authError: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    currentUser: (state) => state.user,
    usersByRole: (state) => state.usersByRole,
    authError: (state) => state.authError,
  },
  actions: {
    async register(payload) {
      try {
        const response = await axios.post('register', payload);
        this.setUser(response.data.data);
        this.setToken(response.data.token || null);
        return response;
      } catch (error) {
        this.setAuthError(error.response?.data || error.message);
        throw error;
      }
    },

    async login(payload) {
      try {
        const response = await axios.post('/login', payload);
        this.setUser(response.data.data);
        this.setToken(response.data.token);
        return response;
      } catch (error) {
        this.setAuthError(error.response?.data || error.message);
        throw error;
      }
    },

    async logout() {
      try {
        await axios.post('/api/logout');
        this.clearAuth();
      } catch (error) {
        console.error('Logout error:', error);
        throw error;
      }
    },

    async getUsersByRole(role) {
      try {
        const response = await axios.get('/users-by-role', { params: { role } });
        this.setUsersByRole(response.data.data);
        return response;
      } catch (error) {
        console.error('Error fetching users by role:', error);
        throw error;
      }
    },
  },
  persist: true,
})