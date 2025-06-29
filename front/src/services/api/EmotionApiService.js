import ApiService from './ApiService.js'

class EmotionApiService extends ApiService {

  /**
   * Récupère toutes les émotions (actives et inactives)
   * @returns {Promise<Array>} - Liste de toutes les émotions
   */
  async getAllEmotions() {
    try {
      const response = await this.get('/emotion/all');
      return response;
    } catch (error) {
      console.error('Erreur lors de la récupération de toutes les émotions:', error);
      throw error;
    }
  }

  /**
   * Récupère toutes les émotions actives
   * @returns {Promise<Array>} - Liste des émotions actives
   */
  async getActiveEmotions() {
    try {
      const response = await this.get('/emotion');
      return response;
    } catch (error) {
      console.error('Erreur lors de la récupération des émotions actives:', error);
      throw error;
    }
  }

  /**
   * Récupère les émotions par catégorie
   * @param {number} categoryId - ID de la catégorie d'émotions
   * @returns {Promise<Array>} - Liste des émotions pour la catégorie
   */
  async getEmotionsByCategory(categoryId) {
    try {
      const response = await this.get(`/emotion/categorie/${categoryId}`);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la récupération des émotions pour la catégorie ${categoryId}:`, error);
      throw error;
    }
  }

  /**
   * Récupère une émotion par son ID
   * @param {number} id - ID de l'émotion à récupérer
   * @returns {Promise<Object>} - Détails de l'émotion
   */
  async getEmotionById(id) {
    try {
      const response = await this.get(`/emotion/${id}`);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la récupération de l'émotion ${id}:`, error);
      throw error;
    }
  }

  /**
   * Crée une nouvelle émotion
   * @param {Object} emotionData - Données de l'émotion à créer
   * @param {string} emotionData.nom - Nom de l'émotion
   * @param {number} emotionData.categorie - ID de la catégorie d'émotion
   * @param {File} imageFile - Fichier image à associer (requis)
   * @returns {Promise<Object>} - Émotion créée
   */
  async createEmotion(emotionData, imageFile) {
    try {
      if (!imageFile) {
        throw new Error('Une image est requise pour créer une émotion');
      }

      const formData = new FormData();
      formData.append('nom', emotionData.nom);
      formData.append('categorie', emotionData.categorie);
      formData.append('icone', imageFile);

      const response = await this.postFormData('/emotion', formData);
      return response;
    } catch (error) {
      console.error('Erreur lors de la création de l\'émotion:', error);
      throw error;
    }
  }

  /**
   * Met à jour une émotion existante
   * @param {number} id - ID de l'émotion à mettre à jour
   * @param {Object} emotionData - Nouvelles données de l'émotion
   * @param {string} [emotionData.nom] - Nouveau nom de l'émotion
   * @param {number} [emotionData.categorie] - Nouvel ID de catégorie
   * @param {boolean} [emotionData.actif] - Nouveau statut actif
   * @param {File} [imageFile] - Nouveau fichier image (optionnel)
   * @returns {Promise<Object>} - Émotion mise à jour
   */
  async updateEmotion(id, emotionData, imageFile = null) {
    try {
      const formData = new FormData();
      
      // Ajouter tous les champs de données
      if (emotionData.nom !== undefined) {
        formData.append('nom', emotionData.nom);
      }
      if (emotionData.categorie !== undefined) {
        formData.append('categorie', emotionData.categorie);
      }
      if (emotionData.actif !== undefined) {
        formData.append('actif', emotionData.actif);
      }
      
      // Ajouter l'image seulement si elle est fournie
      if (imageFile) {
        formData.append('icone', imageFile);
      }

      const response = await this.postFormData(`/emotion/${id}`, formData);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la mise à jour de l'émotion ${id}:`, error);
      throw error;
    }
  }

  /**
   * Supprime une émotion (la marque comme inactive)
   * @param {number} id - ID de l'émotion à supprimer
   * @returns {Promise<void>} - Confirmation de suppression
   */
  async deleteEmotion(id) {
    try {
      const response = await this.delete(`/emotion/${id}`);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la suppression de l'émotion ${id}:`, error);
      throw error;
    }
  }
}

export default EmotionApiService;
