import axios from 'axios';

const state = {
  user: null,
  token: null,
  usersByRole: [],
  authError: null,
};

const getters = {
  isAuthenticated: (state) => !!state.token,
  currentUser: (state) => state.user,
  usersByRole: (state) => state.usersByRole,
  authError: (state) => state.authError,
};

const actions = {
  async register({ commit }, payload) {
    try {
      const response = await axios.post('register', payload);
      commit('setUser', response.data.data);
      commit('setToken', response.data.token || null);
      return response;
    } catch (error) {
      commit('setAuthError', error.response?.data || error.message);
      throw error;
    }
  },

  async login({ commit }, payload) {
    try {
      const response = await axios.post('/login', payload);
      commit('setUser', response.data.data);
      commit('setToken', response.data.token);
      return response;
    } catch (error) {
      commit('setAuthError', error.response?.data || error.message);
      throw error;
    }
  },

  async logout({ commit }) {
    try {
      await axios.post('/api/logout');
      commit('clearAuth');
    } catch (error) {
      console.error('Logout error:', error);
      throw error;
    }
  },

  async getUsersByRole({ commit }, role) {
    try {
      const response = await axios.get('/users-by-role', { params: { role } });
      commit('setUsersByRole', response.data.data);
      return response;
    } catch (error) {
      console.error('Error fetching users by role:', error);
      throw error;
    }
  },
};

const mutations = {
  setUser(state, user) {
    state.user = user;
  },
  setToken(state, token) {
    state.token = token;
  },
  setAuthError(state, error) {
    state.authError = error;
  },
  setUsersByRole(state, users) {
    state.usersByRole = users;
  },
  clearAuth(state) {
    state.user = null;
    state.token = null;
    state.authError = null;
  },
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
};
