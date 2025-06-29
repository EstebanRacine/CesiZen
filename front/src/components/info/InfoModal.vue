<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <button @click="closeModal" class="close-button" aria-label="Fermer la modale">
        <X :size="24" />
      </button>

      <div class="modal-header">
        <h2 class="modal-title">{{ info.titre }}</h2>
        <div class="info-meta">
          <span class="info-date">{{ formattedDate }}</span>
          <span v-if="info.createur" class="info-author">
            <User :size="16" />
            {{ info.createur.username }}
          </span>
        </div>
      </div>

      <div class="modal-body">
        <div v-if="info.image" class="info-image-container">
          <img 
            :src="getImageUrl(info.image)" 
            :alt="info.titre"
            class="info-image"
            @error="handleImageError"
          />
        </div>
        
        <div class="info-content">
          <div class="info-text" v-html="formattedContent"></div>
        </div>
        
        <div v-if="info.dateModification" class="modification-info">
          <Clock :size="16" />
          <span>Modifié le {{ formattedModificationDate }}</span>
          <span v-if="info.modificateur">par {{ info.modificateur.username }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { X, User, Clock } from 'lucide-vue-next';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  info: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['close']);

// Computed pour formater la date de création
const formattedDate = computed(() => {
  if (!props.info.dateCreation) return '';
  const date = new Date(props.info.dateCreation);
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

// Computed pour formater la date de modification
const formattedModificationDate = computed(() => {
  if (!props.info.dateModification) return '';
  const date = new Date(props.info.dateModification);
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

// Computed pour formater le contenu (remplacer les retours à la ligne par des <br>)
const formattedContent = computed(() => {
  if (!props.info.contenu) return '';
  return props.info.contenu.replace(/\n/g, '<br>');
});

// Fonction pour obtenir l'URL complète de l'image
const getImageUrl = (imagePath) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http')) return imagePath;
  return `http://localhost:8000${imagePath}`;
};

// Gestion des erreurs d'image
const handleImageError = (event) => {
  event.target.style.display = 'none';
};

// Fonction pour fermer la modale
const closeModal = () => {
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
  
  /* Cacher la scrollbar */
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.modal-overlay::-webkit-scrollbar {
  display: none;
}

.modal-content {
  background: #fff;
  border-radius: 16px;
  position: relative;
  max-width: 800px;
  width: 90%;
  max-height: calc(100vh - 40px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
  animation: slideIn 0.3s ease-out forwards;
  margin: auto;
  overflow-y: auto;
  
  /* Cacher la scrollbar interne */
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.modal-content::-webkit-scrollbar {
  display: none;
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
  margin: 0 0 1rem 0;
  line-height: 1.3;
}

.info-meta {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.info-date {
  font-size: 0.9rem;
  color: #6c757d;
  font-weight: 500;
}

.info-author {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #fade6d;
  font-weight: 600;
  background: rgba(250, 222, 109, 0.1);
  padding: 0.25rem 0.75rem;
  border-radius: 8px;
}

.modal-body {
  padding: 1rem 2rem 2rem 2rem;
}

.info-image-container {
  margin-bottom: 1.5rem;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.info-image {
  width: 100%;
  height: auto;
  max-height: 400px;
  object-fit: cover;
}

.info-content {
  margin-bottom: 1.5rem;
}

.info-text {
  font-size: 1rem;
  line-height: 1.7;
  color: #333;
  text-align: justify;
}

.modification-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #6c757d;
  padding: 1rem;
  background: rgba(42, 93, 73, 0.05);
  border-radius: 8px;
  border-left: 4px solid #fade6d;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-content {
    width: 95%;
    max-width: none;
  }
  
  .modal-header {
    padding: 1.5rem 1.5rem 1rem 1.5rem;
  }
  
  .modal-title {
    font-size: 1.5rem;
  }
  
  .modal-body {
    padding: 1rem 1.5rem 1.5rem 1.5rem;
  }
  
  .info-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .close-button {
    top: 10px;
    right: 10px;
    padding: 0.4rem;
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
  
  .modal-body {
    padding: 0.75rem 1rem 1rem 1rem;
  }
  
  .info-text {
    font-size: 0.95rem;
  }
  
  .modification-info {
    padding: 0.75rem;
    font-size: 0.8rem;
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
