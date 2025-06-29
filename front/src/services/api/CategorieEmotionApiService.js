import ApiService from './ApiService.js'

class CategorieEmotionApiService extends ApiService {

  /**
   * Récupère toutes les catégories d'émotions
   * @returns {Promise<Array>} - Liste de toutes les catégories d'émotions
   */
  async getAllCategories() {
    try {
      const response = await this.get('/categorie-emotion');
      return response;
    } catch (error) {
      console.error('Erreur lors de la récupération des catégories d\'émotions:', error);
      throw error;
    }
  }

  /**
   * Récupère une catégorie d'émotion par son ID
   * @param {number} id - ID de la catégorie à récupérer
   * @returns {Promise<Object>} - Détails de la catégorie d'émotion
   */
  async getCategoryById(id) {
    try {
      const response = await this.get(`/categorie-emotion/${id}`);
      return response;
    } catch (error) {
      console.error(`Erreur lors de la récupération de la catégorie ${id}:`, error);
      throw error;
    }
  }
}

export default CategorieEmotionApiService;
