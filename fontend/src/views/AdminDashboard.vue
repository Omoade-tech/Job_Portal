<template>
    <div>
      <h1 class="text-center mt-4 admin">
        ADMIN <span>DASHBOARD</span>
      </h1>
      <div class="row ms-2 mt-3 g-3 ">
        <div class="col-12 box col-md-6 col-lg-3">
          <div class="card text-center card1" style="height: 12rem;">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <i class="fa-solid fa-user fa-2x mb-2"></i>
              <h4>
                <router-link to="registerseeker" class="link">Registered JobSeeker</router-link>
              </h4>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card box text-center card2" style="height: 12rem;">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <i class="fa-solid fa-building fa-2x mb-2"></i>
              <h4>
                <router-link to="registeremployer" class="link">Registered Employer</router-link>
              </h4>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card box text-center card3" style="height: 12rem;">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <i class="fa-solid fa-file fa-2x mb-2"></i>
              <h4>
                <router-link to="application" class="link">Application</router-link>
              </h4>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card box text-center card4" style="height: 12rem;">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <i class="fa-solid fa-briefcase fa-2x mb-2"></i>
              <h4>
                <router-link to="/jobdetails" class="link">JobDetails</router-link>
              </h4>
            </div>
          </div>
        </div>
      </div>
  
      <div class="line"></div>
    </div>
  </template>
  
  <script>

  
  </script>
  
  <style scoped>
    .card1 {
      background-color: crimson;
      color: #fff;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-right: 10px;
    }
    .card2 {
      background-color: #ddd;
      color: blue;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-right: 10px;
    }
    .card3 {
      background-color: blueviolet;
      color: black;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-right: 10px;
    }
    .card4 {
      background-color: purple;
      color: #fff;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-right: 10px;
    }
    .card1:hover, .card2:hover, .card3:hover, .card4:hover {
      transform: translateY(-15px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }
    .admin {
      font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
      font-weight: 900;
      justify-content: box-shadow;
      color: crimson;
    }
    span {
      color: blueviolet;
    }
    .line {
      border: 2px solid black;
      margin-top: 100px;
    }
    .link {
      text-decoration: none;
      color: inherit;
    }

    .fa-solid {
      color: #fff;
    }
    .box{
        margin-top: 50px;
    }
  </style>