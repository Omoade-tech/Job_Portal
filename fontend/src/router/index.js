import { createRouter, createWebHistory } from 'vue-router'
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
      path: '/applyjob',
      name: 'applyjob',
      component: ApplyJob,
    },
    {
      path: '/addjob',
      name: 'addjob',
      component: AddJob,
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
  ],
})

export default router
