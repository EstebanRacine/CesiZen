import ApiService from './ApiService.js'

class UserApiService extends ApiService {
  constructor() {
    super()
  }

  /**
   * Récupérer tous les utilisateurs (admin uniquement)
   */
  async getAllUsers() {
    try {
      const response = await this.get('/user/admin/list')
      return response
    } catch (error) {
      console.error('Erreur lors de la récupération des utilisateurs:', error)
      throw error
    }
  }

  /**
   * Créer un nouvel utilisateur (admin uniquement)
   */
  async createUser(userData) {
    try {
      const response = await this.post('/user/admin/create', userData)
      return response
    } catch (error) {
      console.error('Erreur lors de la création de l\'utilisateur:', error)
      throw error
    }
  }

  /**
   * Mettre à jour un utilisateur (admin uniquement)
   */
  async updateUser(id, userData) {
    try {
      const response = await this.post(`/user/admin/update/${id}`, userData)
      return response
    } catch (error) {
      console.error(`Erreur lors de la mise à jour de l'utilisateur ${id}:`, error)
      throw error
    }
  }

  /**
   * Supprimer un utilisateur (admin uniquement)
   */
  async deleteUser(id) {
    try {
      const response = await this.delete(`/user/admin/${id}`)
      return response
    } catch (error) {
      console.error(`Erreur lors de la suppression de l'utilisateur ${id}:`, error)
      throw error
    }
  }

  /**
   * Réinitialiser le mot de passe d'un utilisateur (admin uniquement)
   */
  async adminResetPassword(id, newPassword) {
    try {
      const response = await this.post('/user/admin/reset-password', {
        id,
        new_password: newPassword
      })
      return response
    } catch (error) {
      console.error('Erreur lors de la réinitialisation du mot de passe:', error)
      throw error
    }
  }

  /**
   * Activer/désactiver un utilisateur (admin uniquement)
   */
  async toggleUserStatus(id) {
    try {
      const response = await this.post(`/user/admin/toggle-status/${id}`)
      return response
    } catch (error) {
      console.error('Erreur lors du changement de statut de l\'utilisateur:', error)
      throw error
    }
  }

  /**
   * Basculer le statut administrateur d'un utilisateur
   */
  async toggleAdminRole(id) {
    try {
      const response = await this.post(`/user/admin/update-roles/${id}`)
      return response
    } catch (error) {
      console.error('Erreur lors du changement de rôle administrateur:', error)
      throw error
    }
  }

  /**
   * Valider un nom d'utilisateur côté client
   */
  validateUsername(username) {
    const errors = []
    
    if (!username || username.trim().length === 0) {
      errors.push('Le nom d\'utilisateur est requis')
    } else {
      if (username.length < 3) {
        errors.push('Le nom d\'utilisateur doit contenir au moins 3 caractères')
      }
      if (username.length > 50) {
        errors.push('Le nom d\'utilisateur ne peut pas dépasser 50 caractères')
      }
      if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        errors.push('Le nom d\'utilisateur ne peut contenir que des lettres, des chiffres et des tirets bas')
      }
    }
    
    return errors
  }

  /**
   * Valider un mot de passe côté client selon les règles du UserController
   */
  validatePassword(password) {
    const errors = []
    
    if (!password || password.trim().length === 0) {
      errors.push('Le mot de passe est requis')
    } else {
      if (!/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/.test(password)) {
        errors.push('Le mot de passe doit contenir au moins 6 caractères, une lettre, un chiffre et un caractère spécial')
      }
    }
    
    return errors
  }

  /**
   * Formater un utilisateur pour l'affichage
   */
  formatUserForDisplay(user) {
    const isAdmin = user.roles?.includes('ROLE_ADMIN') || false
    
    return {
      id: user.id,
      username: user.username,
      isAdmin: isAdmin,
      roleLabel: isAdmin ? 'Administrateur' : 'Utilisateur',
      roles: user.roles || [],
      isActive: user.actif !== undefined ? user.actif : true,
      statusLabel: (user.actif !== undefined ? user.actif : true) ? 'Actif' : 'Inactif',
      dateCreation: user.dateCreation || user.date_creation,
      dateSupression: user.dateSupression || user.date_suppression
    }
  }

  /**
   * Générer un mot de passe temporaire selon les règles du UserController
   */
  generateTemporaryPassword() {
    const uppercaseChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
    const lowercaseChars = 'abcdefghijklmnopqrstuvwxyz'
    const numberChars = '0123456789'
    
    let password = '!'
    
    // S'assurer qu'on a au moins une majuscule et un chiffre (règles du UserController)
    password += uppercaseChars[Math.floor(Math.random() * uppercaseChars.length)]
    password += numberChars[Math.floor(Math.random() * numberChars.length)]
    
    // Compléter avec des caractères aléatoires pour atteindre 10 caractères
    const allChars = uppercaseChars + lowercaseChars + numberChars
    for (let i = password.length; i < 10; i++) {
      password += allChars[Math.floor(Math.random() * allChars.length)]
    }
    
    // Mélanger les caractères
    return password.split('').sort(() => Math.random() - 0.5).join('')
  }
}

export default UserApiService
