import axios from 'axios';

/**
 * @class ApiService
 * @description Classe de base pour interagir avec les APIs REST.
 * Gère les requêtes GET, POST, PUT, DELETE et les erreurs génériques.
 */
class ApiService {

  constructor(baseURL = '/api', headers = {}) {
    this.api = axios.create({
      baseURL,
      headers,
      timeout: 5000,
    });


    this.api.interceptors.request.use(
      config => {
        const authToken = localStorage.getItem('authToken');
        if (authToken) {
          config.headers.Authorization = `Bearer ${authToken}`;
        }
        return config;
      },
      error => {
        return Promise.reject(error);
      }
    );

    this.api.interceptors.response.use(
      response => response,
      error => {

        console.error("Erreur API:", error.response?.data || error.message);
        return Promise.reject(error);
      }
    );
  }

  /**
   * Effectue une requête GET.
   * @param {string} url - Le chemin de l'URL relatif à la baseURL (ex: '/users').
   * @param {object} params - Les paramètres de la requête (query parameters) à ajouter à l'URL (ex: { page: 1, limit: 10 }).
   * @returns {Promise<any>} - La réponse de l'API (généralement response.data).
   */
  async get(url, params = {}) {
    try {
      const response = await this.api.get(url, { params });
      return response.data;
    } catch (error) {
      // L'erreur est déjà passée par l'intercepteur, nous la rejetons simplement à nouveau.
      throw error;
    }
  }

  /**
   * Effectue une requête POST.
   * @param {string} url - Le chemin de l'URL relatif à la baseURL (ex: '/users').
   * @param {object} data - Les données à envoyer dans le corps de la requête (payload).
   * @returns {Promise<any>} - La réponse de l'API.
   */
  async post(url, data = {}) {
    try {
      const response = await this.api.post(url, data);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  /**
   * Effectue une requête PUT.
   * @param {string} url - Le chemin de l'URL relatif à la baseURL (ex: '/users/123').
   * @param {object} data - Les données à envoyer dans le corps de la requête pour la mise à jour.
   * @returns {Promise<any>} - La réponse de l'API.
   */
  async put(url, data = {}) {
    try {
      const response = await this.api.put(url, data);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  /**
   * Effectue une requête DELETE.
   * @param {string} url - Le chemin de l'URL relatif à la baseURL (ex: '/users/123').
   * @param {object} data - Les données à envoyer dans le corps de la requête (moins courant pour DELETE, mais possible).
   * Souvent utilisé pour envoyer des IDs ou d'autres identifiants si nécessaire.
   * @returns {Promise<any>} - La réponse de l'API.
   */
  async delete(url, data = {}) {
    try {
      // Pour les requêtes DELETE avec un corps, utilisez l'option 'data' d'axios.
      const response = await this.api.delete(url, { data });
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  /**
   * Effectue une requête POST avec FormData (pour l'upload de fichiers).
   * @param {string} url - Le chemin de l'URL relatif à la baseURL (ex: '/upload').
   * @param {FormData} formData - Les données FormData à envoyer.
   * @returns {Promise<any>} - La réponse de l'API.
   */
  async postFormData(url, formData) {
    try {
      const response = await this.api.post(url, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  /**
   * Effectue une requête PUT avec FormData (pour l'upload de fichiers).
   * @param {string} url - Le chemin de l'URL relatif à la baseURL (ex: '/upload/123').
   * @param {FormData} formData - Les données FormData à envoyer.
   * @returns {Promise<any>} - La réponse de l'API.
   */
  async putFormData(url, formData) {
    try {
      const response = await this.api.put(url, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  /**
   * Définit un en-tête HTTP commun pour toutes les requêtes futures de cette instance.
   * Ceci est utile pour des choses comme les tokens d'autorisation.
   * @param {string} name - Nom de l'en-tête (ex: 'Authorization').
   * @param {string} value - Valeur de l'en-tête (ex: 'Bearer YOUR_TOKEN').
   */
  setHeader(name, value) {
    this.api.defaults.headers.common[name] = value;
  }

  /**
   * Supprime un en-tête HTTP commun.
   * @param {string} name - Nom de l'en-tête à supprimer.
   */
  removeHeader(name) {
    delete this.api.defaults.headers.common[name];
  }
}

export default ApiService;