import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/Auth'
import Home from '../views/Home.vue'
import JobListing from '@/views/JobListing.vue'
import ApplyJob from '@/views/ApplyJob.vue'
import EmployerDashboard from '@/views/EmployerDashboard.vue'
import Login from '@/views/Login.vue'
import Signup from '@/views/Signup.vue'
import Logout from '@/views/Logout.vue'
import AdminDashboard from '@/views/AdminDashboard.vue'
import AddJob from '@/views/AddJob.vue'
import JobDetails from '@/views/JobDetails.vue'
import ViewJob from '@/views/ViewJob.vue'
import EditJob from '@/views/EditJob.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home,
    },
    {
      path: '/joblisting',
      name: 'joblisting',
      component: JobListing,
    },
    {
      path: '/applyjob/:id',
      name: 'applyjob',
      component: ApplyJob,
      props: true,
      meta: { 
        requiresAuth: true,
        role: 'job_seeker'
      }
    },
    {
      path: '/addjob',
      name: 'addjob',
      component: AddJob,
      meta: { 
        requiresAuth: true,
        role: 'employer'
      }
    },
    {
      path: '/jobdetails',
      name: 'jobdetails',
      component: JobDetails,
    },
    {
      path: '/viewjob/:id',
      name: 'viewjob',
      component: ViewJob,
    },
    {
      path: '/editjob/:id',
      name: 'editjob',
      component: EditJob,
    },

    {
      path: '/JobSeekerDashboard/:id',
      name: 'JobSeekerDashboard',
      component: () => import('@/views/JobSeekerDashboard.vue')
    },
    {
      path: '/AdminDashboard',
      name: 'AdminDashboard',
      component: AdminDashboard,
    },
     {
      path: '/EmployerDashboard',
      name: 'EmployerDashboard',
      component: EmployerDashboard,
    },
    
    {
      path: '/login',
      name: 'login',
      component: Login,
    },
    {
      path: '/signup',
      name: 'signup',
      component: Signup,
    },
    {
      path: '/logout',
      name: 'logout',
      component: Logout,
    },
    {
      path: '/application',
      name: 'application',
      component: () => import('@/views/Applcation.vue')
    },
    {
      path: '/registerseeker',
      name: 'registerseeker',
      component: () => import('@/views/RegisterSeeker.vue')
    },
  {
    path: '/registeremployer',
    name: 'registeremployer',
    component: () => import('@/views/RegisterEmployer.vue')
  }
  ],
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  await authStore.checkAuth() // Check auth state before proceeding
  
  const publicPages = ['/login', '/signup', '/', '/joblisting', '/registerseeker', '/registeremployer']
  const authRequired = !publicPages.includes(to.path)

  if (authRequired && !authStore.isAuthenticated) {
    // Store the intended destination
    localStorage.setItem('intendedRoute', to.fullPath)
    return next('/login')
  }

  if (to.path === '/login' && authStore.isAuthenticated) {
    const intendedRoute = localStorage.getItem('intendedRoute')
    if (intendedRoute) {
      localStorage.removeItem('intendedRoute')
      return next(intendedRoute)
    }
    return next('/')
  }

  // Check if the route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    // Store the intended route for redirect after login
    if (!authStore.isAuthenticated) {
      localStorage.setItem('intendedRoute', to.fullPath);
      next({ name: 'Login' });
      return;
    }

    // Check role requirements if specified
    if (to.meta.role && authStore.user?.role !== to.meta.role) {
      console.log('Unauthorized access: incorrect role');
      next({ name: 'Home' });
      return;
    }
  }

  next()
})

export default router