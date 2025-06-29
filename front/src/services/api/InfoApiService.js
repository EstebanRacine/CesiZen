import ApiService from './ApiService.js';

class InfoApiService extends ApiService {

  /**
   * Récupère toutes les infos actives
   * @returns {Promise<Array>} - Liste des infos actives triées par date de création décroissante
   */
  async getAllInfos() {
    try {
      const response = await this.get('/info');
      return response;
    } catch (error) {
      console.error('Erreur lors de la récupération des infos:', error);
      throw error;
    }
  }

  /**
   * Récupère une info par son ID
   * @param {number} id - ID de l'info à récupérer
   * @returns {Promise<Object>} - Détails de l'info
   */
  async getInfoById(id) {
    try {
      const response = await this.get(`/info/${id}`);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la récupération de l'info ${id}:`, error);
      throw error;
    }
  }

  /**
   * Récupère les infos par menu
   * @param {number} menuId - ID du menu pour lequel récupérer les infos
   * @returns {Promise<Array>} - Liste des infos actives pour le menu
   */
  async getInfosByMenu(menuId) {
    try {
      const response = await this.get(`/info/menu/${menuId}`);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la récupération des infos pour le menu ${menuId}:`, error);
      throw error;
    }
  }

  /**
   * Crée une nouvelle info
   * @param {Object} infoData - Données de l'info à créer
   * @param {string} infoData.titre - Titre de l'info
   * @param {string} infoData.contenu - Contenu de l'info
   * @param {number} infoData.menu - ID du menu associé
   * @param {File} [imageFile] - Fichier image à associer (optionnel)
   * @returns {Promise<Object>} - Info créée
   */
  async createInfo(infoData, imageFile = null) {
    try {
      // Si une image est fournie, utiliser FormData pour l'upload
      if (imageFile) {
        const formData = new FormData();
        formData.append('titre', infoData.titre);
        formData.append('contenu', infoData.contenu);
        formData.append('menu', infoData.menu);
        formData.append('image', imageFile);

        const response = await this.postFormData('/info', formData);
        return response;
      } else {
        // Sinon, envoyer les données JSON
        const response = await this.post('/info', infoData);
        return response;
      }
    } catch (error) {
      console.error('Erreur lors de la création de l\'info:', error);
      throw error;
    }
  }

  /**
   * Met à jour une info existante
   * @param {number} id - ID de l'info à mettre à jour
   * @param {Object} infoData - Nouvelles données de l'info
   * @param {string} infoData.titre - Nouveau titre de l'info
   * @param {string} infoData.contenu - Nouveau contenu de l'info
   * @param {File} [imageFile] - Nouveau fichier image (optionnel)
   * @returns {Promise<Object>} - Info mise à jour
   */
  async updateInfo(id, infoData, imageFile = null) {
    try {
      // Si une image est fournie, utiliser FormData
      if (imageFile) {
        
        const formData = new FormData();
        formData.append('titre', infoData.titre);
        formData.append('contenu', infoData.contenu);
        formData.append('image', imageFile);
        formData.append('menu', infoData.menu);

        const response = await this.postFormData(`/info/${id}`, formData);
        return response;
      } else {
        // Sinon, envoyer les données JSON
        const response = await this.post(`/info/${id}`, infoData);
        return response;
      }
    } catch (error) {
      console.error(`Erreur lors de la mise à jour de l'info ${id}:`, error);
      throw error;
    }
  }

  /**
   * Supprime une info
   * @param {number} id - ID de l'info à supprimer
   * @returns {Promise<void>}
   */
  async deleteInfo(id) {
    try {
      await this.delete(`/info/${id}`);
    } catch (error) {
      console.error(`Erreur lors de la suppression de l'info ${id}:`, error);
      throw error;
    }
  }

  /**
   * Recherche des infos par titre ou contenu
   * @param {string} query - Terme de recherche
   * @returns {Promise<Array>} - Liste des infos correspondant à la recherche
   */
  async searchInfos(query) {
    try {
      const allInfos = await this.getAllInfos();
      // Filtrage côté client (vous pouvez implémenter une recherche côté serveur si nécessaire)
      const filteredInfos = allInfos.filter(info => 
        info.titre.toLowerCase().includes(query.toLowerCase()) ||
        info.contenu.toLowerCase().includes(query.toLowerCase())
      );
      return filteredInfos;
    } catch (error) {
      console.error('Erreur lors de la recherche d\'infos:', error);
      throw error;
    }
  }

  /**
   * Valide les données d'une info avant envoi
   * @param {Object} infoData - Données à valider
   * @returns {Object} - Objet avec isValid (boolean) et errors (array)
   */
  validateInfoData(infoData) {
    const errors = [];

    if (!infoData.titre || infoData.titre.trim().length === 0) {
      errors.push('Le titre est requis');
    }

    if (!infoData.contenu || infoData.contenu.trim().length === 0) {
      errors.push('Le contenu est requis');
    }

    if (!infoData.menu) {
      errors.push('Le menu est requis');
    }

    if (infoData.titre && infoData.titre.length > 255) {
      errors.push('Le titre ne peut pas dépasser 255 caractères');
    }

    return {
      isValid: errors.length === 0,
      errors: errors
    };
  }

  /**
   * Formate une info pour l'affichage
   * @param {Object} info - Info à formater
   * @returns {Object} - Info formatée
   */
  formatInfoForDisplay(info) {
    return {
      ...info,
      dateCreationFormatted: this.formatDate(info.dateCreation),
      dateModificationFormatted: info.dateModification ? this.formatDate(info.dateModification) : null,
      contenuPreview: this.truncateText(info.contenu, 150)
    };
  }

  /**
   * Formate une date pour l'affichage
   * @param {string} dateString - Date au format ISO
   * @returns {string} - Date formatée
   */
  formatDate(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  /**
   * Tronque un texte à une longueur donnée
   * @param {string} text - Texte à tronquer
   * @param {number} maxLength - Longueur maximale
   * @returns {string} - Texte tronqué
   */
  truncateText(text, maxLength = 100) {
    if (!text || text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
  }

  /**
   * Récupère tous les menus disponibles
   * @returns {Promise<Array>} - Liste des menus
   */
  async getAllMenus() {
    try {
      const response = await this.get('/menu');
      return response;
    } catch (error) {
      console.error('Erreur lors de la récupération des menus:', error);
      throw error;
    }
  }
}

export default InfoApiService;