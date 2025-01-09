<template>
  <section>
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="container">
        <div class="hero-content">
          <div class="image-content animate__animated animate__fadeInRight">
            <img src="/images/jobbg.png" alt="Job Portal Hero" />
          </div>
          <div class="text-content animate__animated animate__fadeInLeft">
            <h1 style="color:green">Welcome to JBL Portal</h1>
            <p style="color:#fff">
              The home for Developers searching for job opportunities around the world.
              There are thousands of jobs here.
            </p>
            <Search />
          </div>
        </div>
      </div>
    </section>

    <!-- Roles Section with Bootstrap Cards -->
    <section class="roles-section py-5 bg-light">
      <div class="container">
        <h2 class="text-center mb-4" style="color:green">Roles We're Hiring For</h2>
        <div class="row g-4">
          <!-- Fullstack Developer Card -->
          <div class="col-md-3">
            <div class="card h-100 shadow-sm">
              <div class="card-body text-center">
                <h5 class="card-title">Fullstack Developer</h5>
                <p class="card-text">
                  Work on both frontend and backend layers, creating dynamic web applications.
                </p>
              </div>
            </div>
          </div>

          <!-- Frontend Developer Card -->
          <div class="col-md-3">
            <div class="card h-100 shadow-sm">
              <div class="card-body text-center">
                <h5 class="card-title">Frontend Developer</h5>
                <p class="card-text">
                  Create intuitive user interfaces with modern web technologies like HTML, CSS, JS, and Vue.js.
                </p>
              </div>
            </div>
          </div>

          <!-- Software Developer Card -->
          <div class="col-md-3">
            <div class="card h-100 shadow-sm">
              <div class="card-body text-center">
                <h5 class="card-title">Software Engineer</h5>
                <p class="card-text">
                  Build scalable applications and design software solutions for enterprise-level systems.
                </p>
              </div>
            </div>
          </div>

          <!-- Backend Developer Card -->
          <div class="col-md-3">
            <div class="card h-100 shadow-sm">
              <div class="card-body text-center">
                <h5 class="card-title">Backend Developer</h5>
                <p class="card-text">
                  Focus on server-side development, databases, and RESTful APIs.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Logo Section -->
    <section class="logo-section py-5">
      <div class="container text-center">
        <h3 class="mb-4">Our Hiring Partners</h3>
        <div class="d-flex flex-wrap justify-content-center gap-3 image-content">
          <img src="/images/photosnap.svg" alt="Photosnap Logo" class="company-logo">
          <img src="/images/manage.svg" alt="Manage Logo" class="company-logo">
          <img src="/images/account.svg" alt="Account Logo" class="company-logo">
          <img src="/images/myhome.svg" alt="MyHome Logo" class="company-logo">
          <img src="/images/loop-studios.svg" alt="Loop Studios Logo" class="company-logo">
          <img src="/images/faceit.svg" alt="FaceIt Logo" class="company-logo">
          <img src="/images/shortly.svg" alt="Shortly Logo" class="company-logo">
          <img src="/images/insure.svg" alt="Insure Logo" class="company-logo">
          <img src="/images/eyecam-co.svg" alt="Eyecam Co Logo" class="company-logo">
          <img src="/images/the-air-filter-company.svg" alt="The Air Filter Company Logo" class="company-logo">
        </div>
      </div>
    </section>
  </section>
</template>

<script>
export default {
  name: "HeroSection",
};
</script>

<style scoped>
.hero-section {
  background-image: url("/images/background2024.avif");
  background-size: cover;
  background-color: #f8f9fa;
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.hero-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
}

.text-content {
  max-width: 500px;
}

.text-content h1 {
  font-size: 2.5rem;
  font-weight: bold;
  color: #343a40;
}

.text-content p {
  font-size: 1.2rem;
  color: #6c757d;
  line-height: 1.5;
}

.image-content img {
  max-width: 100%;
  border-radius: 8px;
}

.roles-section .card {
  transition: transform 0.2s ease-in-out;
}

.roles-section .card:hover {
  transform: scale(1.05);
}

.logo-section {
  background-color: #f8f9fa;
}

.company-logo {
  max-height: 60px;
  max-width: 100px;
  object-fit: contain;
}

@media screen and (min-width: 320px) and (max-width: 425px) {
  .image-content {
    display: none;
  }
}
</style>
