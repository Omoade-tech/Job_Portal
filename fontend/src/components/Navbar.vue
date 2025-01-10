<template>
    <div class="nav">
     <nav class="navbar navbar-expand-lg navbar-light bg-light  w-100">
       <div class="container-fluid p-0">
         <!-- Logo -->
         <router-link to="/" class="navbar-brand fw-bold px-3" href="#">JBL</router-link>
   
         <!-- Toggle button for mobile view -->
         <button
           class="navbar-toggler"
           type="button"
           data-bs-toggle="collapse"
           data-bs-target="#navbarNav"
           aria-controls="navbarNav"
           aria-expanded="false"
           aria-label="Toggle navigation"
         >
           <span class="navbar-toggler-icon"></span>
         </button>
   
         <!-- Navbar Links -->
         <div class="collapse navbar-collapse" id="navbarNav">
           <!-- Centered Links -->
           <ul class="navbar-nav mx-auto">
             <li class="nav-item">
               <RouterLink to="/joblisting" class="nav-link">Job Listing</RouterLink>
             </li>


             <li class="nav-item">
              <RouterLink to="/addjob" class="nav-link">Add job</RouterLink>
             </li>
             <li class="nav-item">
              <RouterLink to="/jobdetails" class="nav-link">Job Details</RouterLink>
             </li>
           </ul>
   
           <!-- Right-aligned Links -->
           <ul class="navbar-nav">
             <li class="nav-item">
              <RouterLink to="/login" class="nav-link">Login</RouterLink>
             </li>
             <li class="nav-item">
              <RouterLink to="/signup" class="nav-link">signup</RouterLink>
             </li>
             <li class="nav-item">
              <RouterLink to="/login" class="nav-link">Logout</RouterLink>
             </li>
           </ul>
         </div>
       </div>
     </nav>
   </div>
   
   
   </template>
   
   <script>
   
   </script>
   
   <style scoped>
   .nav{
    height: 60px; 
   }
   
   </style>