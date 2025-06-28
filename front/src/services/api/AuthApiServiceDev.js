import { ref, computed } from 'vue';

// Version de développement du service d'authentification avec utilisateur admin simulé
class AuthApiServiceDev {

  constructor() {
    // Simuler un utilisateur admin connecté
    this.token = ref('dev-token-admin');
    this.user = ref({
      id: 1,
      email: 'admin@cesizen.com',
      nom: 'Admin',
      prenom: 'CesiZen',
      roles: ['ROLE_ADMIN', 'ROLE_USER'],
      dateCreation: '2024-01-01T00:00:00Z'
    });
  }

  /**
   * Vérifie si l'utilisateur est connecté
   */
  get isAuthenticated() {
    return computed(() => !!this.token.value && !!this.user.value);
  }

  /**
   * Vérifie si l'utilisateur est admin
   */
  get isAdmin() {
    return computed(() => {
      return this.user.value?.roles?.includes('ROLE_ADMIN') || false;
    });
  }

  /**
   * Récupère l'utilisateur actuel
   */
  get currentUser() {
    return computed(() => this.user.value);
  }

  /**
   * Simule une connexion
   */
  async login(email, password) {
    await this.delay();
    
    // Simulation de différents utilisateurs
    if (email === 'admin@cesizen.com') {
      this.user.value = {
        id: 1,
        email: 'admin@cesizen.com',
        nom: 'Admin',
        prenom: 'CesiZen',
        roles: ['ROLE_ADMIN', 'ROLE_USER']
      };
      this.token.value = 'dev-token-admin';
    } else if (email === 'user@cesizen.com') {
      this.user.value = {
        id: 2,
        email: 'user@cesizen.com',
        nom: 'Utilisateur',
        prenom: 'Test',
        roles: ['ROLE_USER']
      };
      this.token.value = 'dev-token-user';
    } else {
      throw new Error('Identifiants invalides');
    }

    return { data: { token: this.token.value, user: this.user.value } };
  }

  /**
   * Simule une déconnexion
   */
  async logout() {
    await this.delay();
    this.token.value = null;
    this.user.value = null;
    return { success: true };
  }

  /**
   * Simule l'inscription
   */
  async register(userData) {
    await this.delay();
    
    const newUser = {
      id: Date.now(),
      email: userData.email,
      nom: userData.nom,
      prenom: userData.prenom,
      roles: ['ROLE_USER']
    };

    this.user.value = newUser;
    this.token.value = 'dev-token-' + newUser.id;

    return { data: { token: this.token.value, user: newUser } };
  }

  /**
   * Vérifie si l'utilisateur a un rôle spécifique
   */
  hasRole(role) {
    return this.user.value?.roles?.includes(role) || false;
  }

  /**
   * Vérifie si l'utilisateur a l'un des rôles spécifiés
   */
  hasAnyRole(roles) {
    if (!Array.isArray(roles)) return false;
    return roles.some(role => this.hasRole(role));
  }

  /**
   * Met à jour le profil utilisateur
   */
  async updateProfile(profileData) {
    await this.delay();
    
    if (this.user.value) {
      this.user.value = { ...this.user.value, ...profileData };
    }

    return { data: this.user.value };
  }

  /**
   * Change le mot de passe
   */
  async changePassword(currentPassword, newPassword) {
    await this.delay();
    // Simulation
    return { success: true };
  }

  /**
   * Récupère le token actuel
   */
  getToken() {
    return this.token.value;
  }

  /**
   * Définit le token
   */
  setToken(token) {
    this.token.value = token;
  }

  /**
   * Simule un délai d'API
   */
  async delay(ms = 300) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  /**
   * Simule la vérification du token
   */
  async verifyToken() {
    await this.delay();
    
    if (!this.token.value) {
      throw new Error('Token manquant');
    }

    // Simuler la vérification
    return { data: { valid: true, user: this.user.value } };
  }

  /**
   * Initialise l'authentification au démarrage
   */
  async initialize() {
    try {
      // En mode dev, garder l'utilisateur admin connecté
      if (!this.user.value) {
        this.user.value = {
          id: 1,
          email: 'admin@cesizen.com',
          nom: 'Admin',
          prenom: 'CesiZen',
          roles: ['ROLE_ADMIN', 'ROLE_USER']
        };
        this.token.value = 'dev-token-admin';
      }
    } catch (error) {
      console.log('Aucun utilisateur connecté');
    }
  }
}

export default AuthApiServiceDev;
