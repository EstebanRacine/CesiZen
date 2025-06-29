<template>
  <div class="file-input-wrapper">
    <div class="image-upload-container">
      <input
        :id="inputId"
        ref="fileInput"
        type="file"
        accept="image/*"
        class="form-file-input"
        @change="handleFileChange"
        :required="required"
      />
      <label :for="inputId" class="file-upload-label">
        <Upload :size="20" />
        {{ selectedFile ? selectedFile.name : (placeholder || 'Choisir une image') }}
      </label>
    </div>
    
    <!-- Prévisualisation de l'image -->
    <div v-if="previewUrl" class="image-preview">
      <img :src="previewUrl" :alt="altText || 'Aperçu'" class="preview-image" />
      <button @click="removeFile" type="button" class="remove-image-btn">
        <X :size="16" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Upload, X } from 'lucide-vue-next'

const props = defineProps({
  modelValue: {
    type: File,
    default: null
  },
  placeholder: {
    type: String,
    default: 'Choisir une image'
  },
  required: {
    type: Boolean,
    default: false
  },
  altText: {
    type: String,
    default: 'Aperçu'
  },
  inputId: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'remove'])

const fileInput = ref(null)
const selectedFile = ref(null)
const previewUrl = ref('')

// Computed pour générer un ID unique si non fourni
const inputId = computed(() => props.inputId || `file-input-${Math.random().toString(36).substr(2, 9)}`)

// Watcher pour synchroniser avec la prop modelValue
watch(() => props.modelValue, (newFile) => {
  if (newFile !== selectedFile.value) {
    selectedFile.value = newFile
    if (newFile) {
      createPreview(newFile)
    } else {
      previewUrl.value = ''
    }
  }
}, { immediate: true })

// Fonction pour créer l'aperçu de l'image
const createPreview = (file) => {
  if (file && file instanceof File) {
    const reader = new FileReader()
    reader.onload = (e) => {
      previewUrl.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Gestion du changement de fichier
const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    createPreview(file)
    emit('update:modelValue', file)
    emit('change', file)
  }
}

// Supprimer le fichier
const removeFile = () => {
  selectedFile.value = null
  previewUrl.value = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  emit('update:modelValue', null)
  emit('remove')
}

// Exposer la fonction de reset pour usage externe
defineExpose({
  reset: removeFile
})
</script>

<style scoped>
.file-input-wrapper {
  width: 100%;
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
  width: 100%;
  box-sizing: border-box;
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
</style>
