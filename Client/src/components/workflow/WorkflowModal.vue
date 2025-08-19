<template>
  <v-dialog v-model="dialog" max-width="600px" persistent>
    <v-card>
      <v-card-title class="modal-title">
        <span class="headline">
          <v-icon icon="mdi-send" class="mr-2"></v-icon>
          Envoyer à votre Supérieur
        </span>
        <v-spacer></v-spacer>
        <v-btn
          icon="mdi-close"
          variant="text"
          size="small"
          @click="annuler"
        ></v-btn>
      </v-card-title>
      
      <v-card-text class="modal-content">
        <div class="workflow-info">
          <v-icon icon="mdi-check-circle" color="success" class="success-icon"></v-icon>
          <div>
            <p class="success-title">Demande créée avec succès !</p>
            <p class="success-subtitle">Veuillez sélectionner votre supérieur hiérarchique pour continuer le processus de validation.</p>
          </div>
        </div>
        
        <v-form ref="formRef" v-model="valid" @submit.prevent="envoyerDemande">
          <div class="form-fields">
            <v-autocomplete
              v-model="selectedUser"
              v-model:search="searchText"
              :items="userSuggestions"
              :loading="searchLoading"
              item-title="label"
              item-value="value"
              label="Rechercher un supérieur"
              placeholder="Tapez le nom ou l'email..."
              variant="outlined"
              density="comfortable"
              :rules="supervisorRules"
              :no-data-text="noDataText"
              prepend-inner-icon="mdi-account-search"
              hide-details="auto"
              return-object
              clearable
              @update:search="onSearchInput"
              @update:model-value="onUserSelect"
            >
              <template #item="{ props, item }">
                <v-list-item v-bind="props">
                  <template #prepend>
                    <v-avatar size="40" color="primary">
                      <v-icon color="white">mdi-account</v-icon>
                    </v-avatar>
                  </template>
                  
                  <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                  <v-list-item-subtitle>
                    <div class="user-details">
                      <span class="user-email">{{ item.raw.email }}</span>
                      <v-chip 
                        v-if="item.raw.role" 
                        size="x-small" 
                        color="primary" 
                        variant="outlined"
                        class="ml-2"
                      >
                        {{ item.raw.role }}
                      </v-chip>
                    </div>
                    <div v-if="item.raw.department" class="user-department">
                      {{ item.raw.department }}
                    </div>
                  </v-list-item-subtitle>
                </v-list-item>
              </template>

              <template #selection="{ item }">
                <div class="selected-user">
                  <v-avatar size="24" color="primary" class="mr-2">
                    <v-icon color="white" size="14">mdi-account</v-icon>
                  </v-avatar>
                  <span>{{ item.raw.name }} ({{ item.raw.email }})</span>
                </div>
              </template>
            </v-autocomplete>

            <!-- Affichage de l'utilisateur sélectionné -->
            <div v-if="selectedUser" class="selected-user-card">
              <v-card variant="outlined" class="user-preview">
                <v-card-text class="pa-4">
                  <div class="d-flex align-center">
                    <v-avatar size="48" color="primary">
                      <v-icon color="white">mdi-account</v-icon>
                    </v-avatar>
                    <div class="ml-4 flex-grow-1">
                      <div class="user-name">{{ selectedUser.name }}</div>
                      <div class="user-email-display">{{ selectedUser.email }}</div>
                      <div class="user-meta">
                        <v-chip 
                          v-if="selectedUser.role" 
                          size="small" 
                          color="primary" 
                          variant="outlined"
                          class="mr-2"
                        >
                          {{ selectedUser.role }}
                        </v-chip>
                        <span v-if="selectedUser.department" class="department-text">
                          {{ selectedUser.department }}
                        </span>
                      </div>
                    </div>
                    <v-chip color="success" variant="flat" size="small">
                      <v-icon left size="small">mdi-check</v-icon>
                      Confirmé
                    </v-chip>
                  </div>
                </v-card-text>
              </v-card>
            </div>
          </div>
        </v-form>
      </v-card-text>

      <v-card-actions class="modal-actions">
        <v-btn
          variant="outlined"
          @click="annuler"
          :disabled="loading"
        >
          Annuler
        </v-btn>
        <v-btn
          color="primary"
          :loading="loading"
          :disabled="!selectedUser"
          @click="envoyerDemande"
        >
          <v-icon left>mdi-send</v-icon>
          Envoyer la demande
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { usersApi } from '@/services/api'

export default {
  name: 'WorkflowModal',
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    demande: {
      type: Object,
      default: null
    }
  },
  
  emits: ['update:modelValue', 'submit'],
  
  data() {
    return {
      selectedUser: null,
      searchText: '',
      userSuggestions: [],
      searchLoading: false,
      loading: false,
      valid: false,
      searchTimeout: null,
      
      supervisorRules: [
        v => !!v || 'Veuillez sélectionner un supérieur'
      ]
    }
  },
  
  computed: {
    dialog: {
      get() {
        return this.modelValue
      },
      set(value) {
        this.$emit('update:modelValue', value)
      }
    },
    
    noDataText() {
      if (this.searchLoading) {
        return 'Recherche en cours...'
      }
      if (!this.searchText || this.searchText.length < 2) {
        return 'Tapez au moins 2 caractères pour rechercher'
      }
      return 'Aucun utilisateur trouvé'
    }
  },
  
  watch: {
    modelValue(newVal) {
      if (!newVal) {
        this.resetForm()
      }
    }
  },
  
  methods: {
    onSearchInput(searchText) {
      // Debounce la recherche
      clearTimeout(this.searchTimeout)
      
      if (!searchText || searchText.length < 2) {
        this.userSuggestions = []
        return
      }
      
      this.searchTimeout = setTimeout(() => {
        this.searchUsers(searchText)
      }, 300)
    },
    
    async searchUsers(query) {
      if (!query || query.length < 2) return
      
      this.searchLoading = true
      
      try {
        // Recherche par email et par nom
        const [emailResponse, nameResponse] = await Promise.allSettled([
          usersApi.searchByEmail(query),
          usersApi.searchByName ? usersApi.searchByName(query) : Promise.resolve({ data: { data: [] } })
        ])
        
        const emailUsers = emailResponse.status === 'fulfilled' && emailResponse.value.data.success 
          ? [emailResponse.value.data.data].filter(Boolean) 
          : []
          
        const nameUsers = nameResponse.status === 'fulfilled' && nameResponse.value.data.success 
          ? nameResponse.value.data.data || []
          : []
        
        // Combiner et dédupliquer les résultats
        const allUsers = [...emailUsers, ...nameUsers]
        const uniqueUsers = allUsers.filter((user, index, self) => 
          user && index === self.findIndex(u => u.id === user.id)
        )
        
        // Formater pour l'autocomplete
        this.userSuggestions = uniqueUsers.map(user => ({
          label: `${user.first_name || ''} ${user.name || user.last_name || ''} - ${user.email}`,
          value: user.id,
          name: `${user.first_name || ''} ${user.name || user.last_name || ''}`.trim(),
          email: user.email,
          role: user.roles && user.roles[0] ? user.roles[0].nom || user.roles[0].name : null,
          department: user.department ? user.department.name : null,
          id: user.id
        }))
        
      } catch (error) {
        console.error('Erreur recherche utilisateurs:', error)
        this.userSuggestions = []
      } finally {
        this.searchLoading = false
      }
    },
    
    onUserSelect(user) {
      if (user) {
        this.selectedUser = user
        this.searchText = `${user.name} (${user.email})`
      } else {
        this.selectedUser = null
        this.searchText = ''
      }
    },
    
    async envoyerDemande() {
      if (!this.selectedUser) {
        this.$toast?.add({
          severity: 'warn',
          summary: 'Attention',
          detail: 'Veuillez sélectionner un supérieur',
          life: 3000
        })
        return
      }
      
      this.loading = true
      
      try {
        // Émettre l'événement avec les données
        this.$emit('submit', {
          demandeId: this.demande?.id,
          emailSuperieur: this.selectedUser.email,
          userSuggestion: this.selectedUser
        })
        
        // Fermer le modal
        this.dialog = false
        
      } catch (error) {
        console.error('Erreur envoi demande:', error)
        this.$toast?.add({
          severity: 'error',
          summary: 'Erreur',
          detail: 'Erreur lors de l\'envoi de la demande',
          life: 5000
        })
      } finally {
        this.loading = false
      }
    },
    
    annuler() {
      this.dialog = false
    },
    
    resetForm() {
      this.selectedUser = null
      this.searchText = ''
      this.userSuggestions = []
      this.loading = false
      this.searchLoading = false
      this.valid = false
      clearTimeout(this.searchTimeout)
    }
  }
}
</script>

<style scoped>
/* Styles similaires à UserModal.vue */
.modal-title {
  padding: 20px 24px 16px 24px;
  background: #f8f9fa;
  border-bottom: 1px solid #e0e0e0;
}

.modal-title .headline {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1a1a1a;
  display: flex;
  align-items: center;
}

.modal-content {
  padding: 24px;
}

.workflow-info {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 20px;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 12px;
  margin-bottom: 24px;
}

.success-icon {
  margin-top: 2px;
}

.success-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #065f46;
  margin-bottom: 4px;
}

.success-subtitle {
  font-size: 0.95rem;
  color: #047857;
  margin: 0;
  line-height: 1.5;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.selected-user {
  display: flex;
  align-items: center;
  font-size: 0.9rem;
}

.selected-user-card {
  margin-top: 16px;
}

.user-preview {
  background: #f8fdf8;
  border-color: #10b981;
}

.user-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 4px;
}

.user-email-display {
  font-size: 0.9rem;
  color: #6b7280;
  margin-bottom: 8px;
}

.user-meta {
  display: flex;
  align-items: center;
  gap: 8px;
}

.department-text {
  font-size: 0.85rem;
  color: #6b7280;
}

.user-details {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
}

.user-email {
  font-size: 0.85rem;
  color: #6b7280;
}

.user-department {
  font-size: 0.8rem;
  color: #9ca3af;
  margin-top: 2px;
}

.modal-actions {
  padding: 16px 24px 20px 24px;
  border-top: 1px solid #e0e0e0;
  gap: 12px;
}

.modal-actions .v-btn {
  text-transform: none;
  font-weight: 500;
}

/* Style des champs de formulaire */
.v-autocomplete {
  margin-bottom: 0;
}

:deep(.v-field) {
  border-radius: 8px;
}

:deep(.v-field--focused .v-field__outline) {
  border-color: #008a9b;
  border-width: 2px;
}

/* Style pour l'autocomplete */
:deep(.v-autocomplete .v-field__input) {
  padding-top: 8px;
  padding-bottom: 8px;
}

:deep(.v-list-item) {
  padding: 12px 16px;
  border-bottom: 1px solid #f3f4f6;
}

:deep(.v-list-item:last-child) {
  border-bottom: none;
}

:deep(.v-list-item--active) {
  background-color: #f0f9ff;
}

:deep(.v-list-item-title) {
  font-weight: 500;
  color: #1f2937;
}

:deep(.v-list-item-subtitle) {
  color: #6b7280;
  font-size: 0.85rem;
}

/* Animation pour la carte de prévisualisation */
.selected-user-card {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 600px) {
  .modal-title {
    padding: 16px 20px 12px 20px;
  }
  
  .modal-content {
    padding: 20px;
  }
  
  .workflow-info {
    padding: 16px;
    gap: 12px;
  }
  
  .success-title {
    font-size: 1rem;
  }
  
  .success-subtitle {
    font-size: 0.9rem;
  }
}
</style>
