<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <button @click="closeModal" class="close-button" aria-label="Fermer la modale">
        <X :size="24" />
      </button>

      <div class="modal-header">
        <h2 class="modal-title">
          {{ isEditing ? 'Modifier l\'info' : 'Créer une nouvelle info' }}
        </h2>
      </div>

      <form @submit.prevent="handleSubmit" class="info-form">
        <div class="form-group">
          <label for="titre" class="form-label">Titre *</label>
          <input
            id="titre"
            v-model="formData.titre"
            type="text"
            class="form-input"
            placeholder="Entrez le titre de l'info"
            required
            maxlength="255"
          />
          <div class="character-count">{{ formData.titre.length }}/255</div>
        </div>

        <div class="form-group">
          <label for="menu" class="form-label">Menu *</label>
          <select
            id="menu"
            v-model="formData.menu"
            class="form-select"
            required
          >
            <option value="">Sélectionnez un menu</option>
            <option v-for="menu in menus" :key="menu.id" :value="menu.id">
              {{ menu.nom }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label for="contenu" class="form-label">Contenu *</label>
          <textarea
            id="contenu"
            v-model="formData.contenu"
            class="form-textarea"
            placeholder="Entrez le contenu de l'info"
            required
            rows="8"
          ></textarea>
        </div>

        <div class="form-group">
          <label for="image" class="form-label">Image</label>
          <div class="image-upload-container">
            <input
              id="image"
              ref="imageInput"
              type="file"
              accept="image/*"
              class="form-file-input"
              @change="handleImageChange"
            />
            <label for="image" class="file-upload-label">
              <Upload :size="20" />
              {{ imageFile ? imageFile.name : 'Choisir une image' }}
            </label>
          </div>
          
          <!-- Prévisualisation de l'image -->
          <div v-if="imagePreview" class="image-preview">
            <img :src="imagePreview" alt="Aperçu" class="preview-image" />
            <button @click="removeImage" type="button" class="remove-image-btn">
              <X :size="16" />
            </button>
          </div>
        </div>

        <!-- Messages d'erreur -->
        <div v-if="errors.length > 0" class="error-messages">
          <ul>
            <li v-for="error in errors" :key="error">{{ error }}</li>
          </ul>
        </div>

        <div class="form-actions">
          <button type="button" @click="closeModal" class="btn-cancel">
            Annuler
          </button>
          <button type="submit" :disabled="loading" class="btn-submit">
            <div v-if="loading" class="loading-spinner"></div>
            {{ loading ? 'Enregistrement...' : (isEditing ? 'Modifier' : 'Créer') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { X, Upload } from 'lucide-vue-next';
import infoService from '@/services/singleton/infoService.js';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  info: {
    type: Object,
    default: null
  },
  menus: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'success']);

// État du formulaire
const formData = reactive({
  titre: '',
  contenu: '',
  menu: ''
});

const imageFile = ref(null);
const imagePreview = ref('');
const errors = ref([]);
const loading = ref(false);
const imageInput = ref(null);

// Computed pour savoir si on est en mode édition
const isEditing = computed(() => !!props.info);

// Fonction pour obtenir l'URL complète de l'image
const getImageUrl = (imagePath) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http')) return imagePath;
  // L'image est stockée côté backend dans public/uploads/infos/
  return `http://localhost:8000${imagePath}`;
};

// Réinitialiser le formulaire
const resetForm = () => {
  formData.titre = '';
  formData.contenu = '';
  formData.menu = '';
  imageFile.value = null;
  imagePreview.value = '';
  errors.value = [];
  if (imageInput.value) {
    imageInput.value.value = '';
  }
};

// Watcher pour remplir le formulaire quand on passe une info en props
watch(() => props.info, (newInfo) => {
  if (newInfo) {
    formData.titre = newInfo.titre || '';
    formData.contenu = newInfo.contenu || '';
    formData.menu = newInfo.menu?.id || '';
    
    // Afficher l'image existante si elle existe
    if (newInfo.image) {
      imagePreview.value = getImageUrl(newInfo.image);
    }
  } else {
    resetForm();
  }
}, { immediate: true });

// Gestion du changement d'image
const handleImageChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    imageFile.value = file;
    
    // Créer une prévisualisation
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

// Supprimer l'image
const removeImage = () => {
  imageFile.value = null;
  imagePreview.value = '';
  if (imageInput.value) {
    imageInput.value.value = '';
  }
};

// Valider le formulaire
const validateForm = () => {
  errors.value = [];
  
  if (!formData.titre.trim()) {
    errors.value.push('Le titre est requis');
  }
  
  if (!formData.contenu.trim()) {
    errors.value.push('Le contenu est requis');
  }
  
  if (!formData.menu) {
    errors.value.push('Le menu est requis');
  }
  
  if (formData.titre.length > 255) {
    errors.value.push('Le titre ne peut pas dépasser 255 caractères');
  }
  
  return errors.value.length === 0;
};

// Soumettre le formulaire
const handleSubmit = async () => {
  if (!validateForm()) return;
  
  loading.value = true;
  
  try {
    // Préparer les données pour l'API
    const dataToSubmit = {
      titre: formData.titre.trim(),
      contenu: formData.contenu.trim(),
      menu: formData.menu
    };
    
    if (isEditing.value) {
      // Modification
      await infoService.updateInfo(props.info.id, dataToSubmit, imageFile.value);
    } else {
      // Création
      await infoService.createInfo(dataToSubmit, imageFile.value);
    }
    
    emit('success');
    closeModal();
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error);
    
    // Gestion des erreurs spécifiques
    if (error.response?.data?.errors) {
      const serverErrors = error.response.data.errors;
      errors.value = Object.values(serverErrors).flat();
    } else if (error.response?.data?.message) {
      errors.value = [error.response.data.message];
    } else if (error.message) {
      errors.value = [error.message];
    } else {
      errors.value = ['Erreur lors de la sauvegarde. Veuillez réessayer.'];
    }
  } finally {
    loading.value = false;
  }
};

// Fermer la modale
const closeModal = () => {
  resetForm();
  emit('close');
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: flex-start;
  z-index: 2000;
  backdrop-filter: blur(5px);
  animation: fadeIn 0.3s ease-out forwards;
  overflow-y: auto;
  padding: 20px 0;
}

.modal-content {
  background: #fff;
  border-radius: 16px;
  position: relative;
  max-width: 700px;
  width: 90%;
  max-height: calc(100vh - 40px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
  animation: slideIn 0.3s ease-out forwards;
  margin: auto;
  overflow-y: auto;
}

.close-button {
  position: absolute;
  top: 15px;
  right: 15px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  color: #2a5d49;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.close-button:hover {
  background: #fff;
  transform: scale(1.1);
}

.modal-header {
  padding: 2rem 2rem 1rem 2rem;
  border-bottom: 1px solid rgba(42, 93, 73, 0.1);
}

.modal-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #2a5d49;
  margin: 0;
}

.info-form {
  padding: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  font-weight: 600;
  color: #2a5d49;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
  background: #fff;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #2a5d49;
  box-shadow: 0 0 0 3px rgba(42, 93, 73, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 120px;
  font-family: inherit;
}

.character-count {
  font-size: 0.8rem;
  color: #6c757d;
  text-align: right;
  margin-top: 0.25rem;
}

.image-upload-container {
  position: relative;
}

.form-file-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.file-upload-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border: 2px dashed #e9ecef;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #6c757d;
  font-weight: 500;
}

.file-upload-label:hover {
  border-color: #2a5d49;
  color: #2a5d49;
  background: rgba(42, 93, 73, 0.05);
}

.image-preview {
  position: relative;
  margin-top: 1rem;
  display: inline-block;
}

.preview-image {
  max-width: 200px;
  max-height: 200px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.remove-image-btn {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #ff6b6b;
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.remove-image-btn:hover {
  background: #ff5252;
  transform: scale(1.1);
}

.error-messages {
  background: #ffe6e6;
  border: 1px solid #ff6b6b;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.error-messages ul {
  margin: 0;
  padding-left: 1.5rem;
  color: #d63031;
}

.error-messages li {
  margin-bottom: 0.25rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 1px solid rgba(42, 93, 73, 0.1);
}

.btn-cancel,
.btn-submit {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  min-width: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-cancel {
  background: #f8f9fa;
  color: #6c757d;
  border: 1px solid #e9ecef;
}

.btn-cancel:hover {
  background: #e9ecef;
  color: #495057;
}

.btn-submit {
  background: #2a5d49;
  color: #fff;
}

.btn-submit:hover:not(:disabled) {
  background: #1a4a37;
  transform: translateY(-2px);
}

.btn-submit:disabled {
  background: #6c757d;
  cursor: not-allowed;
  transform: none;
}

.loading-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
  .modal-content {
    width: 95%;
  }
  
  .modal-header {
    padding: 1.5rem 1.5rem 1rem 1.5rem;
  }
  
  .modal-title {
    font-size: 1.5rem;
  }
  
  .info-form {
    padding: 1.5rem;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn-cancel,
  .btn-submit {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .modal-overlay {
    padding: 10px 0;
  }
  
  .modal-content {
    width: 98%;
    border-radius: 12px;
  }
  
  .modal-header {
    padding: 1rem 1rem 0.75rem 1rem;
  }
  
  .modal-title {
    font-size: 1.3rem;
  }
  
  .info-form {
    padding: 1rem;
  }
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideIn {
  from { transform: translateY(-50px) scale(0.9); opacity: 0; }
  to { transform: translateY(0) scale(1); opacity: 1; }
}
</style>
