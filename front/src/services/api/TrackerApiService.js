import ApiService from './ApiService.js'

class TrackerApiService extends ApiService {

  /**
   * Récupère tous les trackers de l'utilisateur connecté
   * @returns {Promise<Array>} - Liste des trackers de l'utilisateur
   */
  async getMyTrackers() {
    try {
      const response = await this.get('/tracker/me');
      console.log('Trackers récupérés:', response);
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
   * Récupère les trackers de l'utilisateur pour un mois et une année donnés
   * @param {number} year - Année (ex: 2025)
   * @param {number} month - Mois (1-12)
   * @returns {Promise<Array>} - Liste des trackers pour ce mois
   */
  async getTrackersByMonth(year, month) {
    try {
      // Formatter le mois avec un zéro devant si nécessaire
      const formattedMonth = String(month).padStart(2, '0');
      const response = await this.get(`/tracker/me/month/${year}/${formattedMonth}`);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la récupération des trackers pour ${year}/${month}:`, error);
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
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  }

  /**
   * Formate la datetime pour l'API en timezone locale (UTC+2)
   * @param {Date} date - Date à formater
   * @returns {string} - DateTime formatée en timezone locale
   */
  formatDateTimeForAPI(date) {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')
    const seconds = String(date.getSeconds()).padStart(2, '0')
    
    // Retourner au format YYYY-MM-DDTHH:MM:SS en timezone locale
    return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`
  }
}

export default TrackerApiService;
