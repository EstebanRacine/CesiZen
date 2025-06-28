import ApiService from './ApiService.js';
import { jwtDecode } from 'jwt-decode';
import { ref, computed } from 'vue';

class AuthApiService extends ApiService {

  constructor() {
    super();
    
    // État réactif pour l'authentification
    this.token = ref(localStorage.getItem('authToken') || null);
    this.user = ref(null);
    this.roles = ref([]);
    this.showLoginModal = ref(false);
    this.loginModalMessage = ref('');
    
    // Initialiser l'authentification si un token existe
    this.initializeAuth();
  }

  // Getters réactifs
  get isAuthenticated() {
    return computed(() => !!this.token.value);
  }

  get isAdmin() {
    return computed(() => this.roles.value.includes('ROLE_ADMIN'));
  }

  // Méthode pour vérifier les rôles
  hasRole(requiredRoles) {
    if (!this.isAuthenticated.value || !requiredRoles || requiredRoles.length === 0) {
      return false;
    }
    return requiredRoles.some(role => this.roles.value.includes(role));
  }

  /**
   * Authentifie un utilisateur avec son login/email et mot de passe.
   * @param {string} login - Le login ou email de l'utilisateur.
   * @param {string} password - Le mot de passe de l'utilisateur.
   * @returns {Promise<any>} - La réponse de l'API contenant le token d'authentification.
   */
  async login(login, password) {
    try {
      const loginData = {
        username: login,
        password: password
      };

      const response = await this.post('/login', loginData);
      
      if (response.token) {
        // Utiliser la méthode setToken pour gérer le token
        this.setToken(response.token);
        
        // Mettre à jour l'en-tête Authorization pour les prochaines requêtes
        this.setHeader('Authorization', `Bearer ${response.token}`);
      }

      return response;
    } catch (error) {
      console.error('Erreur lors de la connexion:', error);
      throw error;
    }
  }

  /**
   * Enregistre un nouvel utilisateur avec son login et mot de passe.
   * @param {string} login - Le nom d'utilisateur pour le nouvel utilisateur.
   * @param {string} password - Le mot de passe pour le nouvel utilisateur.
   * @returns {Promise<any>} - La réponse de l'API.
   */
  async register(login, password) {
    try {
      const registerData = {
        username: login,
        password: password
      };

      const response = await this.post('/register', registerData);
      
      // Si l'inscription réussit et qu'un token est fourni, connecter automatiquement l'utilisateur
      if (response.token) {
        this.setToken(response.token);
        this.setHeader('Authorization', `Bearer ${response.token}`);
      }

      return response;
    } catch (error) {
      console.error('Erreur lors de l\'inscription:', error);
      throw error;
    }
  }

  /**
   * Déconnecte l'utilisateur en supprimant le token d'authentification.
   */
  async logout() {
    try {      
      
      // Utiliser clearToken pour nettoyer l'authentification
      this.clearToken();
      
      // Supprimer l'en-tête Authorization
      this.removeHeader('Authorization');

      return { success: true };
    } catch (error) {
      // Même en cas d'erreur serveur, nettoyer le token local
      this.clearToken();
      this.removeHeader('Authorization');
      
      console.error('Erreur lors de la déconnexion:', error);
      throw error;
    }
  }

  /**
   * Vérifie si l'utilisateur est connecté en vérifiant la présence du token.
   * @returns {boolean} - True si l'utilisateur est connecté, false sinon.
   */
  isAuthenticatedSync() {
    return this.isAuthenticated.value;
  }

  /**
   * Récupère le token d'authentification stocké.
   * @returns {string|null} - Le token d'authentification ou null s'il n'existe pas.
   */
  getAuthToken() {
    return this.token.value;
  }
  /**
   * Vérifie la validité du token auprès du serveur.
   * @returns {Promise<any>} - La réponse de l'API avec les informations de l'utilisateur.
   */
  async verifyToken() {
    try {
      const token = this.getAuthToken();
      
      if (!token) {
        throw new Error('Aucun token d\'authentification trouvé');
      }

      // Vérifie d'abord la validité locale du token (expiration)
      try {
        const decodedToken = jwtDecode(token);
        const tokenExpiration = decodedToken.exp * 1000; // Convertir en millisecondes
        
        if (Date.now() > tokenExpiration) {
          console.warn("Token expiré localement. Déconnexion automatique.");
          this.clearToken();
          throw new Error('Token expiré');
        }
      } catch (decodeError) {
        console.error("Erreur lors du décodage du token :", decodeError);
        this.clearToken();
        throw new Error('Token invalide');
      }

      // Si le token est valide localement, vérifier auprès du serveur
      const response = await this.get('/verify-token');
      return response;
    } catch (error) {
      // Si le token n'est pas valide, nettoyer l'authentification
      this.clearToken();
      this.removeHeader('Authorization');
      throw error;
    }
  }

  /**
   * Récupère les informations du profil de l'utilisateur connecté.
   * @returns {Promise<any>} - La réponse de l'API avec les informations de l'utilisateur.
   */
  async getUserProfile() {
    try {
      const response = await this.get('/profile');
      return response;
    } catch (error) {
      console.error('Erreur lors de la récupération du profil:', error);
      throw error;
    }
  }

  /**
   * Vérifie si l'utilisateur a le rôle admin.
   * @returns {boolean} - True si l'utilisateur est admin, false sinon.
   */
  isAdminSync() {
    return this.isAdmin.value;
  }

  /**
   * Vérifie si l'utilisateur possède au moins un des rôles requis.
   * @param {string[]} requiredRoles - Tableau des rôles requis.
   * @returns {boolean} - True si l'utilisateur a au moins un des rôles, false sinon.
   */
  hasRoleSync(requiredRoles) {
    return this.hasRole(requiredRoles);
  }

  /**
   * Récupère les rôles de l'utilisateur connecté.
   * @returns {string[]} - Tableau des rôles de l'utilisateur.
   */
  getUserRoles() {
    return this.roles.value;
  }

  /**
   * Récupère les informations de l'utilisateur connecté.
   * @returns {object|null} - Les informations de l'utilisateur ou null.
   */
  getCurrentUser() {
    return this.user.value;
  }

  /**
   * Définit le token d'authentification et le stocke dans localStorage.
   * Décode également le token pour extraire les informations de l'utilisateur et les rôles.
   * @param {string} token Le token JWT à définir.
   */
  setToken(token) {
    this.token.value = token;
    localStorage.setItem('authToken', token);
    this.decodeTokenAndSetUser(token);
  }

  /**
   * Efface le token d'authentification et les informations de l'utilisateur.
   * Supprime le token de localStorage, effectuant une déconnexion.
   */
  clearToken() {
    this.token.value = null;
    this.user.value = null;
    this.roles.value = [];
    localStorage.removeItem('authToken');
  }

  /**
   * Décode un token JWT et met à jour l'état de l'utilisateur et de ses rôles.
   * Vérifie également l'expiration du token.
   * @param {string} token Le token JWT à décoder.
   */
  decodeTokenAndSetUser(token) {
    if (token) {
      try {
        const decodedToken = jwtDecode(token);
        this.user.value = decodedToken;
        this.roles.value = decodedToken.roles || [];
        const tokenExpiration = decodedToken.exp * 1000; // Convertir en millisecondes

        // Vérifie si le token a expiré
        if (Date.now() > tokenExpiration) {
          console.warn("Token expiré. Déconnexion automatique.");
          this.clearToken();
        }
      } catch (error) {
        console.error("Erreur lors du décodage du token :", error);
        this.clearToken();
      }
    }
  }

  /**
   * Ouvre la modale de connexion avec un message optionnel.
   * @param {string} [message=''] Message à afficher dans la modale.
   */
  openLoginModal(message = '') {
    this.loginModalMessage.value = message;
    this.showLoginModal.value = true;
  }

  /**
   * Ferme la modale de connexion et efface le message.
   */
  closeLoginModal() {
    this.showLoginModal.value = false;
    this.loginModalMessage.value = '';
  }

  /**
   * Initialise l'état d'authentification au démarrage de l'application.
   * Tente de décoder un token existant si l'application est rafraîchie.
   */
  initializeAuth() {
    if (this.token.value) {
      this.decodeTokenAndSetUser(this.token.value);
    }
  }
}

export default AuthApiService;