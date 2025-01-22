<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <router-link class="navbar-brand" to="/">Job Portal</router-link>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <router-link class="nav-link" to="/">Home</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/joblisting">Jobs</router-link>
          </li>
          <li class="nav-item" v-if="isAuthenticated && user?.role === 'employer'">
            <router-link class="nav-link" to="/addjob">Post Job</router-link>
          </li>
        </ul>
        <ul class="navbar-nav">
          <template v-if="!isAuthenticated">
            <li class="nav-item">
              <router-link class="nav-link" to="/login">Login</router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/register">Register</router-link>
            </li>
          </template>
          <template v-else>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                {{ user?.name || 'Account' }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li v-if="user?.role === 'job_seeker'">
                  <router-link class="dropdown-item" :to="'/JobSeekerDashboard/' + user.id">Dashboard</router-link>
                </li>
                <li v-if="user?.role === 'employer'">
                  <router-link class="dropdown-item" to="/EmployerDashboard">Dashboard</router-link>
                </li>
                <li v-if="user?.role === 'admin'">
                  <router-link class="dropdown-item" to="/AdminDashboard">Dashboard</router-link>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="#" @click.prevent="handleLogout">Logout</a>
                </li>
              </ul>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
import { useAuthStore } from '@/stores/Auth';
import { useRouter } from 'vue-router';
import { computed } from 'vue';

export default {
  name: 'Navbar',
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();

    const isAuthenticated = computed(() => authStore.isAuthenticated);
    const user = computed(() => authStore.user);

    const handleLogout = async () => {
      try {
        await authStore.logout();
        router.push('/');
      } catch (error) {
        console.error('Logout error:', error);
      }
    };

    return {
      isAuthenticated,
      user,
      handleLogout
    };
  }
};
</script>

<style scoped>
.navbar {
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.dropdown-menu {
  min-width: 200px;
}

.dropdown-item:active {
  background-color: #007bff;
}
</style>