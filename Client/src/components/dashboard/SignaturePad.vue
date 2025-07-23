<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Signature</h2>
        <button class="close-button" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="signature-container">
        <div class="upload-section">
          <div
            class="upload-area"
            @dragover.prevent
            @drop.prevent="handleDrop"
            @click="triggerFileInput"
            :class="{ 'has-image': previewUrl }"
          >
            <input
              type="file"
              ref="fileInput"
              accept="image/*"
              @change="handleFileSelect"
              style="display: none"
            />
            <div v-if="!previewUrl" class="upload-placeholder">
              <i class="fas fa-upload"></i>
              <p>Cliquez ou glissez-déposez une photo de signature</p>
              <small>Formats acceptés : JPG, PNG (max 2MB)</small>
            </div>
            <img
              v-else
              :src="previewUrl"
              alt="Aperçu de la signature"
              class="preview-image"
            />
          </div>
        </div>
        <div class="signature-actions">
          <button @click="effacer" class="btn-secondary" v-if="previewUrl">
            <i class="fas fa-trash"></i> Supprimer
          </button>
          <button
            @click="sauvegarder"
            class="btn-primary"
            :disabled="!previewUrl"
          >
            <i class="fas fa-save"></i> Sauvegarder
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SignaturePad",
  data() {
    return {
      previewUrl: null,
      selectedFile: null,
    };
  },
  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    handleFileSelect(event) {
      const file = event.target.files[0];
      if (file) {
        this.processFile(file);
      }
    },
    handleDrop(event) {
      const file = event.dataTransfer.files[0];
      if (file) {
        this.processFile(file);
      }
    },
    processFile(file) {
      if (!file.type.startsWith("image/")) {
        alert("Veuillez sélectionner une image valide");
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        alert("L'image ne doit pas dépasser 2MB");
        return;
      }
      this.selectedFile = file;
      this.previewUrl = URL.createObjectURL(file);
    },
    effacer() {
      this.previewUrl = null;
      this.selectedFile = null;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
    },
    sauvegarder() {
      if (!this.selectedFile) {
        alert("Veuillez sélectionner une signature");
        return;
      }
      const reader = new FileReader();
      reader.onload = (e) => {
        this.$emit("signature-saved", e.target.result);
        this.$emit("close");
      };
      reader.readAsDataURL(this.selectedFile);
    },
  },
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 600px;
  padding: 2rem;
  position: relative;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modal-header h2 {
  color: #2c3e50;
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.close-button {
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 0.5rem;
  font-size: 1.25rem;
  transition: color 0.3s ease;
}

.close-button:hover {
  color: #1a1a1a;
}

.signature-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.upload-section {
  width: 100%;
}

.upload-area {
  width: 100%;
  height: 200px;
  border: 2px dashed #ccc;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f8f9fa;
  overflow: hidden;
}

.upload-area:hover {
  border-color: #008a9b;
  background: #f0f9fa;
}

.upload-area.has-image {
  border-style: solid;
  border-color: #008a9b;
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: #666;
  text-align: center;
  padding: 1rem;
}

.upload-placeholder i {
  font-size: 2rem;
  color: #008a9b;
}

.upload-placeholder small {
  color: #888;
}

.preview-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.signature-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-secondary,
.btn-primary {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
}

.btn-secondary {
  background: #f1f5f9;
  color: #64748b;
  border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #e2e8f0;
}

.btn-primary {
  background: #008a9b;
  color: white;
  border: none;
}

.btn-primary:hover {
  background: #006d7a;
}

.btn-primary:disabled {
  background: #ccc;
  cursor: not-allowed;
}
</style>
