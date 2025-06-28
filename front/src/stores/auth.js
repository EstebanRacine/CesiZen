// src/stores/auth.js
import { defineStore } from 'pinia';
import { jwtDecode } from 'jwt-decode'; 

/**
 * Définit le store pour la gestion de l'authentification.
 * Centralise l'état de l'utilisateur (token, rôles).
 */
export const useAuthStore = defineStore('auth', {

  state: () => ({
    // Le token d'authentification, récupéré depuis le localStorage si existant.
    token: localStorage.getItem('authToken') || null,
    user: null,
    roles: [],
    showLoginModal: false,
    loginModalMessage: ''
  }),

  /**
   * Getters pour accéder aux données calculées de l'état.
   */
  getters: {
    /**
     * Vérifie si l'utilisateur est authentifié (s'il y a un token).
     * @param {object} state L'état actuel du store.
     * @returns {boolean} Vrai si l'utilisateur est authentifié, faux sinon.
     */
    isAuthenticated: (state) => !!state.token,

    /**
     * Vérifie si l'utilisateur a le rôle 'admin'.
     * @param {object} state L'état actuel du store.
     * @returns {boolean} Vrai si l'utilisateur est admin, faux sinon.
     */
    isAdmin: (state) => state.roles.includes('admin'),

    /**
     * Vérifie si l'utilisateur possède au moins un des rôles requis.
     * @param {object} state L'état actuel du store.
     * @returns {function(string[]): boolean} Une fonction qui prend un tableau de rôles requis et retourne un booléen.
     */
    hasRole: (state) => (requiredRoles) => {
      // Si l'utilisateur n'est pas authentifié ou si aucun rôle n'est requis, retourne faux.
      if (!state.isAuthenticated || !requiredRoles || requiredRoles.length === 0) {
        return false;
      }
      // Vérifie si l'utilisateur possède au moins un des rôles requis.
      return requiredRoles.some(role => state.roles.includes(role));
    }
  },

  /**
   * Actions pour modifier l'état et exécuter de la logique asynchrone.
   */
  actions: {
    /**
     * Définit le token d'authentification et le stocke dans localStorage.
     * Décode également le token pour extraire les informations de l'utilisateur et les rôles.
     * @param {string} token Le token JWT à définir.
     */
    setToken(token) {
      this.token = token;
      localStorage.setItem('authToken', token);
      this.decodeTokenAndSetUser(token);
    },

    /**
     * Efface le token d'authentification et les informations de l'utilisateur.
     * Supprime le token de localStorage, effectuant une déconnexion.
     */
    clearToken() {
      this.token = null;
      this.user = null;
      this.roles = [];
      localStorage.removeItem('authToken');
    },

    /**
     * Décode un token JWT et met à jour l'état de l'utilisateur et de ses rôles.
     * Vérifie également l'expiration du token.
     * @param {string} token Le token JWT à décoder.
     */
    decodeTokenAndSetUser(token) {
      if (token) {
        try {
          // Utilisation de la fonction jwtDecode importée
          const decodedToken = jwtDecode(token);
          this.user = decodedToken;
          this.roles = decodedToken.roles || []; // Assurez-vous que votre JWT contient un champ 'roles'
          const tokenExpiration = decodedToken.exp * 1000; // Convertir en millisecondes

          // Vérifie si le token a expiré
          if (Date.now() > tokenExpiration) {
              console.warn("Token expiré. Déconnexion automatique.");
              this.clearToken(); // Déconnecte si le token est expiré
          }
        } catch (error) {
          // Gère les erreurs de décodage (token invalide ou malformé)
          console.error("Erreur lors du décodage du token :", error);
          this.clearToken(); // Déconnecte si le token est invalide
        }
      }
    },

    /**
     * Initialise l'état d'authentification au démarrage de l'application.
     * Tente de décoder un token existant si l'application est rafraîchie.
     */
    initializeAuth() {
        if (this.token) {
            this.decodeTokenAndSetUser(this.token);
        }
    },

    /**
     * Ouvre la modale de connexion avec un message optionnel.
     * @param {string} [message=''] Message à afficher dans la modale.
     */
    openLoginModal(message = '') {
      this.loginModalMessage = message;
      this.showLoginModal = true;
    },

    /**
     * Ferme la modale de connexion et efface le message.
     */
    closeLoginModal() {
      this.showLoginModal = false;
      this.loginModalMessage = '';
    }
  }
});
