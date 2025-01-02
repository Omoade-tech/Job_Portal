<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="text-center">Register</h3>
          </div>
          <div class="card-body">
            <form @submit.prevent="registerUser">
              <!-- Role -->
              <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select id="role" v-model="form.role" class="form-control" required>
                  <option disabled value="">Select Role</option>
                  <option value="admin">Admin</option>
                  <option value="employer">Employer</option>
                  <option value="job_seeker">Jobseeker</option>
                </select>
              </div>

              <!-- Name -->
              <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" v-model="form.name" class="form-control" required />
              </div>

              <!-- Email -->
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" v-model="form.email" class="form-control" required />
              </div>

              <!-- Password -->
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" v-model="form.password" class="form-control" required />
              </div>

              <!-- Confirm Password -->
              <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <input type="password" id="confirmPassword" v-model="form.confirmPassword" class="form-control" required />
              </div>

              <!-- Phone Number -->
              <div class="mb-3">
                <label for="phoneNumber" class="form-label">Phone Number</label>
                <input type="tel" id="phoneNumber" v-model="form.phoneNumber" class="form-control" required />
              </div>

              <!-- Age -->
              <div class="mb-3">
                <label for="age" class="form-label">Age</label>
                <input type="number" id="age" v-model="form.age" class="form-control" required />
              </div>

              <!-- Sex -->
              <div class="mb-3">
                <label for="sex" class="form-label">Sex</label>
                <select id="sex" v-model="form.sex" class="form-control" required>
                  <option disabled value="">Select Sex</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
              </div>

              <!-- Status -->
              <div class="mb-3">
                <label for="status" class="form-label">Marital Status</label>
                <select id="status" v-model="form.status" class="form-control" required>
                  <option disabled value="">Select Status</option>
                  <option value="single">Single</option>
                  <option value="married">Married</option>
                </select>
              </div>

              <!-- Address -->
              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea id="address" v-model="form.address" class="form-control" rows="2" required></textarea>
              </div>

              <!-- City -->
              <div class="mb-3">
                <label for="city" class="form-label">City</label>
                <input type="text" id="city" v-model="form.city" class="form-control" required />
              </div>

              <!-- State -->
              <div class="mb-3">
                <label for="state" class="form-label">State</label>
                <input type="text" id="state" v-model="form.state" class="form-control" required />
              </div>

              <!-- Country -->
              <div class="mb-3">
                <label for="country" class="form-label">Country</label>
                <input type="text" id="country" v-model="form.country" class="form-control" required />
              </div>

              <!-- Submit Button -->
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Register</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/services/api.js";

export default {
  data() {
    return {
      form: {
        role: "",
        name: "",
        email: "", 
        password: "",
        confirmPassword: "",
        phoneNumber: "",
        age: null,
        sex: "",
        status: "",
        address: "",
        city: "",
        state: "",
        country: "",
      },
    };
  },
  methods: {
    async registerUser() {
      // Check if passwords match
      if (this.form.password !== this.form.confirmPassword) {
        alert("Passwords do not match.");
        return;
      }

      try {
        const response = await api.register(this.form);
        alert("Registration successful!");
        // console.log(response.data);
        this.$router.push("/login");
      } catch (error) {
        console.error("Registration failed:", error.response.data);
        const errorMessage = error.response.data.message || "Registration failed. Please try again.";
        alert(errorMessage);
      }
    },
  },
};
</script>
