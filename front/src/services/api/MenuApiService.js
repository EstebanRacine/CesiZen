import ApiService from './ApiService.js'

class MenuApiService extends ApiService {
  
  async getAllActiveMenus() {
    const response = await this.api.get('/menu')
    return response.data
  }

  async getMenuById(id) {
    const response = await this.api.get(`/menu/${id}`)
    return response.data
  }

  async getInfosByMenu(menuId) {
    const response = await this.api.get(`/info/menu/${menuId}`)
    return response.data
  }
}

export default MenuApiService
