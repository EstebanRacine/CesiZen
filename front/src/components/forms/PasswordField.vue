<template>
  <div class="input-group">
    <label :for="id">{{ label }}</label>
    <div class="password-field">
      <input
        :id="id"
        :type="showPassword ? 'text' : 'password'"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :required="required"
        class="password-input"
        :class="{ 'error': hasError }"
        :placeholder="placeholder"
      />
      <button 
        type="button" 
        class="toggle-password-btn"
        @click="$emit('toggle-visibility')"
      >
        {{ showPassword ? '🔒' : '👁️' }}
      </button>
    </div>
    <span v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </span>
  </div>
</template>

<script setup>
defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  modelValue: {
    type: String,
    default: ''
  },
  showPassword: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  placeholder: {
    type: String,
    default: ''
  },
  errorMessage: {
    type: String,
    default: ''
  },
  hasError: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue', 'toggle-visibility'])
</script>

<style scoped>
.input-group {
  margin-bottom: 1.5rem;
}

.input-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.password-field {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input {
  width: 100%;
  padding: 0.75rem;
  padding-right: 3rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.toggle-password-btn {
  position: absolute;
  right: 0.75rem;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0;
  z-index: 1;
}

.password-input:focus {
  outline: none;
  border-color: #2a5d49;
  box-shadow: 0 0 0 2px rgba(42, 93, 73, 0.1);
}

.password-input.error {
  border-color: #e74c3c;
}

.error-message {
  color: #e74c3c;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: block;
}
</style>
