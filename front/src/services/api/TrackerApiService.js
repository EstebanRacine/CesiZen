import ApiService from './ApiService.js'

class TrackerApiService extends ApiService {

  /**
   * Récupère tous les trackers de l'utilisateur connecté
   * @returns {Promise<Array>} - Liste des trackers de l'utilisateur
   */
  async getMyTrackers() {
    try {
      const response = await this.get('/tracker/me');
      return response;
    } catch (error) {
      console.error('Erreur lors de la récupération des trackers:', error);
      throw error;
    }
  }

  /**
   * Récupère les trackers de l'utilisateur pour une date donnée
   * @param {string} date - Date au format YYYY-MM-DD
   * @returns {Promise<Array>} - Liste des trackers pour cette date
   */
  async getTrackersByDate(date) {
    try {
      const response = await this.post(`/tracker/me/date/${date}`);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la récupération des trackers pour la date ${date}:`, error);
      throw error;
    }
  }

  /**
   * Crée un nouveau tracker
   * @param {Object} trackerData - Données du tracker à créer
   * @param {number} trackerData.emotion - ID de l'émotion
   * @param {string} trackerData.datetime - Date et heure au format ISO 8601
   * @param {string} [trackerData.commentaire] - Commentaire optionnel
   * @returns {Promise<Object>} - Tracker créé
   */
  async createTracker(trackerData) {
    try {
      const response = await this.post('/tracker', trackerData);
      return response;
    } catch (error) {
      console.error('Erreur lors de la création du tracker:', error);
      throw error;
    }
  }

  /**
   * Met à jour un tracker existant
   * @param {number} id - ID du tracker à mettre à jour
   * @param {Object} trackerData - Nouvelles données du tracker
   * @returns {Promise<Object>} - Tracker mis à jour
   */
  async updateTracker(id, trackerData) {
    try {
      const response = await this.post(`/tracker/${id}`, trackerData);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la mise à jour du tracker ${id}:`, error);
      throw error;
    }
  }

  /**
   * Supprime un tracker
   * @param {number} id - ID du tracker à supprimer
   * @returns {Promise<void>}
   */
  async deleteTracker(id) {
    try {
      const response = await this.delete(`/tracker/${id}`);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la suppression du tracker ${id}:`, error);
      throw error;
    }
  }

  /**
   * Formate la date pour l'API (YYYY-MM-DD)
   * @param {Date} date - Date à formater
   * @returns {string} - Date formatée
   */
  formatDateForAPI(date) {
    return date.toISOString().split('T')[0];
  }

  /**
   * Formate la datetime pour l'API (ISO 8601)
   * @param {Date} date - Date à formater
   * @returns {string} - DateTime formatée
   */
  formatDateTimeForAPI(date) {
    return date.toISOString().slice(0, 19); // YYYY-MM-DDTHH:MM:SS
  }
}

export default TrackerApiService;
