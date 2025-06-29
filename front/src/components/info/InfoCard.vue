<template>
  <div class="info-card" @click="$emit('click', info)">
    <div class="info-image-container">
      <img 
        v-if="info.image" 
        :src="getImageUrl(info.image)" 
        :alt="info.titre"
        class="info-image"
        @error="handleImageError"
      />
      <div v-else class="info-image-placeholder">
        <FileText :size="32" />
      </div>
    </div>
    
    <div class="info-content">
      <h3 class="info-title">{{ info.titre }}</h3>
      <p class="info-preview">{{ truncatedContent }}</p>
      <div class="info-meta">
        <span class="info-date">{{ formattedDate }}</span>
        <span v-if="info.createur" class="info-author">{{ info.createur.username }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { FileText } from 'lucide-vue-next';

const props = defineProps({
  info: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['click']);

// Computed pour tronquer le contenu
const truncatedContent = computed(() => {
  if (!props.info.contenu) return '';
  const maxLength = 120;
  if (props.info.contenu.length <= maxLength) return props.info.contenu;
  return props.info.contenu.substring(0, maxLength) + '...';
});

// Computed pour formater la date
const formattedDate = computed(() => {
  if (!props.info.dateCreation) return '';
  const date = new Date(props.info.dateCreation);
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
});

// Fonction pour obtenir l'URL complète de l'image
const getImageUrl = (imagePath) => {
  if (!imagePath) return '';
  // Si l'image commence par http, c'est une URL complète
  if (imagePath.startsWith('http')) return imagePath;
  // Sinon, construire l'URL avec le backend
  return `http://localhost:8000${imagePath}`;
};

// Gestion des erreurs d'image
const handleImageError = (event) => {
  event.target.style.display = 'none';
  event.target.nextElementSibling?.classList.remove('hidden');
};
</script>

<style scoped>
.info-card {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(42, 93, 73, 0.1);
  transition: all 0.3s ease;
  cursor: pointer;
  border: 1px solid rgba(42, 93, 73, 0.1);
}

.info-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(42, 93, 73, 0.15);
  border-color: #fade6d;
}

.info-image-container {
  position: relative;
  height: 200px;
  overflow: hidden;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.info-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.info-card:hover .info-image {
  transform: scale(1.05);
}

.info-image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #2a5d49 0%, #89c997 100%);
  color: #fff;
}

.info-content {
  padding: 1.5rem;
}

.info-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2a5d49;
  margin: 0 0 0.75rem 0;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.info-preview {
  font-size: 0.95rem;
  color: #6c757d;
  line-height: 1.5;
  margin: 0 0 1rem 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.info-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
  color: #898989;
}

.info-date {
  font-weight: 500;
}

.info-author {
  color: #fade6d;
  font-weight: 600;
  background: rgba(250, 222, 109, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
}

/* Responsive */
@media (max-width: 768px) {
  .info-image-container {
    height: 160px;
  }
  
  .info-content {
    padding: 1rem;
  }
  
  .info-title {
    font-size: 1.1rem;
  }
  
  .info-preview {
    font-size: 0.9rem;
    -webkit-line-clamp: 2;
    line-clamp: 2;
  }
  
  .info-meta {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
}

@media (max-width: 480px) {
  .info-image-container {
    height: 140px;
  }
  
  .info-content {
    padding: 0.75rem;
  }
  
  .info-title {
    font-size: 1rem;
  }
}
</style>
