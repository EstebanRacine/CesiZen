import UserApiService from '../api/UserApiService.js'

class UserService {
  constructor() {
    this.apiService = new UserApiService()
  }

  // Méthodes CRUD essentielles
  async getAllUsers() {
    return this.apiService.getAllUsers()
  }

  async createUser(userData) {
    return this.apiService.createUser(userData)
  }

  async updateUser(id, userData) {
    return this.apiService.updateUser(id, userData)
  }

  async deleteUser(id) {
    return this.apiService.deleteUser(id)
  }

  // Méthodes admin
  async adminResetPassword(id, newPassword) {
    return this.apiService.adminResetPassword(id, newPassword)
  }

  async toggleUserStatus(id) {
    return this.apiService.toggleUserStatus(id)
  }

  async toggleAdminRole(id) {
    return this.apiService.toggleAdminRole(id)
  }

  // Méthodes utilitaires
  validateUsername(username) {
    return this.apiService.validateUsername(username)
  }

  validatePassword(password) {
    return this.apiService.validatePassword(password)
  }

  formatUserForDisplay(user) {
    return this.apiService.formatUserForDisplay(user)
  }

  generateTemporaryPassword() {
    return this.apiService.generateTemporaryPassword()
  }
}

// Instance singleton
const userService = new UserService()

export default userService
