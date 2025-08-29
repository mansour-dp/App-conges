<template>
  <v-dialog v-model="dialog" max-width="700px" persistent>
    <v-card>
      <v-card-title class="modal-title">
        <span class="headline">
          <v-icon icon="mdi-clipboard-check" class="mr-2"></v-icon>
          Validation de la demande
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
        <!-- Informations de la demande -->
        <div class="demande-info">
          <v-icon icon="mdi-file-document-outline" color="primary" class="info-icon"></v-icon>
          <div>
            <p class="info-title">{{ demande?.user?.first_name }} {{ demande?.user?.name }}</p>
            <p class="info-subtitle">Demande de {{ demande?.type_demande?.toLowerCase() }} - {{ demande?.duree_jours }} jour(s)</p>
          </div>
        </div>

        <v-card variant="outlined" class="demande-details mb-6">
          <v-card-text class="pa-4">
            <v-row>
              <v-col cols="6">
                <div class="detail-label">Type de demande</div>
                <div class="detail-value">{{ demande?.type_demande }}</div>
              </v-col>
              <v-col cols="6">
                <div class="detail-label">Durée</div>
                <div class="detail-value">{{ demande?.duree_jours }} jour(s)</div>
              </v-col>
              <v-col cols="6">
                <div class="detail-label">Date de début</div>
                <div class="detail-value">{{ formatDate(demande?.date_debut) }}</div>
              </v-col>
              <v-col cols="6">
                <div class="detail-label">Date de fin</div>
                <div class="detail-value">{{ formatDate(demande?.date_fin) }}</div>
              </v-col>
              <v-col cols="12" v-if="demande?.motif">
                <div class="detail-label">Motif</div>
                <div class="detail-value">{{ demande?.motif }}</div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <v-form ref="formRef" v-model="valid" @submit.prevent="valider">
          <div class="form-fields">
            <!-- Zone de décision -->
            <div class="decision-section">
              <h3 class="section-title">
                <v-icon icon="mdi-gavel" class="mr-2"></v-icon>
                Votre Décision
              </h3>
              <v-radio-group v-model="decision" :rules="decisionRules" class="decision-group">
                <v-radio 
                  value="approve"
                  color="success"
                  class="decision-radio"
                >
                  <template #label>
                    <div class="decision-option approve">
                      <v-icon color="success" size="20" class="mr-2">mdi-check-circle</v-icon>
                      <span>Approuver la demande</span>
                    </div>
                  </template>
                </v-radio>
                <v-radio 
                  value="reject"
                  color="error"
                  class="decision-radio"
                >
                  <template #label>
                    <div class="decision-option reject">
                      <v-icon color="error" size="20" class="mr-2">mdi-close-circle</v-icon>
                      <span>Rejeter la demande</span>
                    </div>
                  </template>
                </v-radio>
              </v-radio-group>

              <v-textarea
                v-model="commentaire"
                :label="decision === 'reject' ? 'Motif du rejet (obligatoire)' : 'Commentaire (optionnel)'"
                variant="outlined"
                rows="3"
                :placeholder="decision === 'reject' ? 'Veuillez expliquer les raisons du rejet...' : 'Ajoutez un commentaire sur votre décision...'"
                density="comfortable"
                class="mt-4"
                :rules="decision === 'reject' ? commentaireRejetRules : []"
              />

              <!-- Message informatif pour le rejet -->
              <div v-if="decision === 'reject'" class="reject-info mt-4">
                <v-alert
                  type="info"
                  variant="tonal"
                  density="compact"
                  class="rejection-alert"
                >
                  <template #prepend>
                    <v-icon>mdi-information</v-icon>
                  </template>
                  <div class="alert-content">
                    <strong>Information :</strong> Aucune signature électronique n'est requise pour un rejet.
                    <br>Veuillez simplement expliquer les raisons de votre décision.
                  </div>
                </v-alert>
              </div>
            </div>

            <!-- Signature électronique (seulement si approbation) -->
            <div v-if="decision === 'approve'" class="signature-section">
              <h3 class="section-title">
                <v-icon icon="mdi-draw-pen" class="mr-2"></v-icon>
                Signature Électronique
                <v-chip color="error" size="small" class="ml-2">Obligatoire</v-chip>
              </h3>
              <div class="signature-zone" @click="openSignaturePad">
                <div v-if="signature" class="signature-preview">
                  <img :src="signature" alt="Signature" class="signature-image" />
                  <v-btn 
                    size="small" 
                    color="primary" 
                    variant="outlined"
                    @click.stop="openSignaturePad"
                    class="mt-3"
                  >
                    Modifier la signature
                  </v-btn>
                </div>
                <div v-else class="signature-placeholder">
                  <v-icon size="48" color="grey-lighten-2">mdi-draw-pen</v-icon>
                  <div class="placeholder-title">Cliquez pour signer</div>
                  <div class="placeholder-subtitle">Signature électronique obligatoire pour l'approbation</div>
                </div>
              </div>
            </div>

            <!-- Prochain validateur (si approbation) -->
            <div v-if="decision === 'approve' && !isLastValidator" class="next-validator-section">
              <h3 class="section-title">
                <v-icon icon="mdi-account-arrow-right" class="mr-2"></v-icon>
                Prochain Validateur
              </h3>
              
              <!-- Message d'aide pour le rôle requis -->
              <v-alert 
                v-if="nextRequiredRole" 
                type="info" 
                variant="tonal" 
                density="compact" 
                class="mb-4"
              >
                <template #prepend>
                  <v-icon>mdi-information</v-icon>
                </template>
                <div class="alert-content">
                  <strong>Contrainte hiérarchique :</strong> {{ roleHelpMessage }}
                </div>
              </v-alert>
              
              <v-autocomplete
                v-model="selectedUser"
                v-model:search="searchText"
                :items="userSuggestions"
                :loading="searchLoading"
                item-title="label"
                item-value="value"
                label="Rechercher le prochain validateur"
                placeholder="Tapez le nom ou l'email..."
                variant="outlined"
                density="comfortable"
                :rules="nextValidatorRules"
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
          :color="decision === 'approve' ? 'success' : 'error'"
          :loading="loading"
          :disabled="!canValidate"
          @click="valider"
        >
          <v-icon left>{{ decision === 'approve' ? 'mdi-check' : 'mdi-close' }}</v-icon>
          {{ decision === 'approve' ? 'Approuver' : 'Rejeter' }}
        </v-btn>
      </v-card-actions>
    </v-card>

    <!-- Signature Pad Modal -->
    <SignaturePad
      v-if="showSignaturePad"
      @close="showSignaturePad = false"
      @signature-saved="saveSignature"
    />
  </v-dialog>
</template>

<script>
import { usersApi } from '@/services/api'
import SignaturePad from '@/components/ui/SignaturePad.vue'
import { useUserStore } from '@/stores/users'

export default {
  name: 'ValidationModal',
  
  components: {
    SignaturePad
  },
  
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    demande: {
      type: Object,
      default: null
    },
    isLastValidator: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['update:modelValue', 'submit'],
  
  setup() {
    const userStore = useUserStore()
    return { userStore }
  },
  
  data() {
    return {
      decision: '',
      commentaire: '',
      signature: null,
      selectedUser: null,
      searchText: '',
      userSuggestions: [],
      searchLoading: false,
      emailProchainValidateur: '',
      nextUserSuggestion: null,
      emailError: '',
      loading: false,
      valid: false,
      showSignaturePad: false,
      searchTimeout: null,
      
      validationHistory: [], // Sera rempli avec les validations précédentes
      
      decisionRules: [
        v => !!v || 'Veuillez choisir une décision'
      ],
      
      commentaireRejetRules: [
        v => !!v || 'Le motif du rejet est obligatoire',
        v => (v && v.length >= 10) || 'Le motif doit contenir au moins 10 caractères'
      ],
      
      nextValidatorRules: [
        v => !!v || 'Veuillez sélectionner le prochain validateur'
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
    
    canValidate() {
      const hasDecision = !!this.decision
      
      if (this.decision === 'approve') {
        // Pour approbation : signature obligatoire + prochain validateur (si pas dernier)
        const hasSignature = !!this.signature
        const hasNextValidator = this.isLastValidator || !!this.selectedUser
        return hasDecision && hasSignature && hasNextValidator
      } else if (this.decision === 'reject') {
        // Pour rejet : commentaire obligatoire uniquement
        const hasComment = !!this.commentaire && this.commentaire.length >= 10
        return hasDecision && hasComment
      }
      
      return false
    },
    
    noDataText() {
      if (this.searchLoading) {
        return 'Recherche en cours...'
      }
      if (!this.searchText || this.searchText.length < 2) {
        return 'Tapez au moins 2 caractères pour rechercher'
      }
      return 'Aucun utilisateur trouvé'
    },

    // Détermine le rôle suivant dans la hiérarchie selon le rôle actuel
    nextRequiredRole() {
      // Essayer plusieurs façons d'accéder au rôle de l'utilisateur
      const user = this.userStore.user
      let currentUserRole = null
      
      if (user?.role?.name) {
        currentUserRole = user.role.name
      } else if (user?.role?.nom) {
        currentUserRole = user.role.nom
      } else if (user?.roles && user.roles.length > 0) {
        currentUserRole = user.roles[0].nom || user.roles[0].name
      }
      
      const roleHierarchy = {
        'Superieur': 'Directeur Unité',
        'Directeur Unité': 'Responsable RH', 
        'Responsable RH': 'Directeur RH'
      }
      
      const nextRole = roleHierarchy[currentUserRole]
      
      return nextRole || null
    },

    // Message d'aide pour expliquer le rôle requis
    roleHelpMessage() {
      if (this.nextRequiredRole) {
        return `Vous devez sélectionner un utilisateur avec le rôle "${this.nextRequiredRole}"`
      }
      return "Vous pouvez sélectionner n'importe quel utilisateur"
    }
  },
  
  watch: {
    modelValue(newVal) {
      if (newVal && this.demande) {
        this.loadValidationHistory()
      } else if (!newVal) {
        this.resetForm()
      }
    },
    
    decision(newDecision, oldDecision) {
      // Réinitialiser les champs spécifiques quand on change de décision
      if (oldDecision && newDecision !== oldDecision) {
        if (newDecision === 'reject') {
          // Si on passe à rejet, effacer la signature et le prochain validateur
          this.signature = null
          this.selectedUser = null
          this.searchText = ''
          this.emailProchainValidateur = ''
          this.userSuggestions = []
        } else if (newDecision === 'approve') {
          // Si on passe à approbation, garder le commentaire mais le rendre optionnel
          // Les autres champs restent vides pour être remplis
        }
      }
    }
  },
  
  methods: {
    formatDate(dateString) {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('fr-FR')
    },
    
    formatDateTime(dateString) {
      if (!dateString) return ''
      return new Date(dateString).toLocaleString('fr-FR')
    },
    
    async loadValidationHistory() {
      // Charger l'historique des validations de cette demande
      if (this.demande?.signatures) {
        this.validationHistory = Object.entries(this.demande.signatures).map(([role, data]) => ({
          validator_name: data.name,
          role: role,
          status: 'approved',
          date: data.timestamp,
          comment: data.comment
        }))
      }
    },

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
        // RETOUR A LA VERSION SIMPLE QUI FONCTIONNAIT
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
        
        // FILTRAGE HIÉRARCHIQUE SIMPLE : après avoir récupéré tous les users
        let filteredUsers = uniqueUsers
        if (this.nextRequiredRole) {
          filteredUsers = uniqueUsers.filter(user => {
            const userRole = user.roles && user.roles[0] ? user.roles[0].nom || user.roles[0].name : null
            return userRole === this.nextRequiredRole
          })
        }
        
        // Formater pour l'autocomplete
        this.userSuggestions = filteredUsers.map(user => ({
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
        this.emailProchainValidateur = user.email
      } else {
        this.selectedUser = null
        this.searchText = ''
        this.emailProchainValidateur = ''
      }
    },
    
    async searchNextUser(email) {
      try {
        const response = await usersApi.searchByEmail(email)
        
        if (response.data.success && response.data.data) {
          this.nextUserSuggestion = response.data.data
          this.emailError = ''
        } else {
          this.nextUserSuggestion = null
          this.emailError = 'Utilisateur non trouvé'
        }
      } catch (error) {
        this.nextUserSuggestion = null
        this.emailError = 'Erreur lors de la recherche'
      }
    },
    
    clearEmailError() {
      this.emailError = ''
    },
    
    openSignaturePad() {
      this.showSignaturePad = true
    },
    
    saveSignature(signatureData) {
      this.signature = signatureData
      this.showSignaturePad = false
    },
    
    async valider() {
      if (!this.canValidate) return
      
      this.loading = true
      
      try {
        const validationData = {
          demande_id: this.demande.id,
          decision: this.decision === 'approve' ? 'approuve' : 'rejete', // Conversion pour l'API
          commentaire: this.commentaire,
          signature: this.signature,
          selectedUser: this.selectedUser  // Passer l'objet utilisateur complet
        }
        
        this.$emit('submit', validationData)
        this.dialog = false
        
      } catch (error) {
        this.$toast?.add({
          severity: 'error',
          summary: 'Erreur',
          detail: 'Erreur lors de la validation',
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
      this.decision = ''
      this.commentaire = ''
      this.signature = null
      this.selectedUser = null
      this.searchText = ''
      this.userSuggestions = []
      this.emailProchainValidateur = ''
      this.nextUserSuggestion = null
      this.emailError = ''
      this.loading = false
      this.valid = false
      this.searchLoading = false
      this.validationHistory = []
      clearTimeout(this.searchTimeout)
    }
  }
}
</script>

<style scoped>
/* Styles identiques à WorkflowModal.vue */
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

.demande-info {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 20px;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 12px;
  margin-bottom: 24px;
}

.info-icon {
  margin-top: 2px;
}

.info-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #065f46;
  margin-bottom: 4px;
}

.info-subtitle {
  font-size: 0.95rem;
  color: #047857;
  margin: 0;
  line-height: 1.5;
}

.demande-details {
  background: #f8fdf8;
  border-color: #10b981;
}

.detail-label {
  font-size: 0.85rem;
  color: #6b7280;
  margin-bottom: 4px;
  font-weight: 500;
}

.detail-value {
  font-size: 0.95rem;
  color: #1f2937;
  font-weight: 600;
  margin-bottom: 12px;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
}

.decision-section {
  padding: 20px;
  background: #fafafa;
  border-radius: 12px;
  border: 1px solid #e0e0e0;
}

.decision-group {
  margin-bottom: 0;
}

.decision-radio {
  margin-bottom: 12px;
}

.decision-option {
  display: flex;
  align-items: center;
  font-size: 1rem;
  font-weight: 500;
  padding: 8px 0;
}

.decision-option.approve span {
  color: #059669;
}

.decision-option.reject span {
  color: #dc2626;
}

.reject-info {
  margin-top: 16px;
}

.rejection-alert {
  border-radius: 8px;
}

.alert-content {
  font-size: 0.9rem;
  line-height: 1.5;
}

.signature-section {
  padding: 20px;
  background: #fafafa;
  border-radius: 12px;
  border: 1px solid #e0e0e0;
}

.signature-zone {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 32px;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.3s, background-color 0.3s;
  min-height: 140px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
}

.signature-zone:hover {
  border-color: #008a9b;
  background-color: #f9fafb;
}

.signature-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.placeholder-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #6b7280;
  margin-top: 12px;
  margin-bottom: 4px;
}

.placeholder-subtitle {
  font-size: 0.9rem;
  color: #9ca3af;
}

.signature-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.signature-image {
  max-width: 250px;
  max-height: 100px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
}

.next-validator-section {
  padding: 20px;
  background: #fafafa;
  border-radius: 12px;
  border: 1px solid #e0e0e0;
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
.v-autocomplete, .v-textarea {
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

/* Style pour les radio buttons */
:deep(.v-radio .v-selection-control__wrapper) {
  margin: 0;
}

:deep(.v-radio .v-label) {
  margin-left: 8px;
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
@media (max-width: 700px) {
  .modal-title {
    padding: 16px 20px 12px 20px;
  }
  
  .modal-content {
    padding: 20px;
  }
  
  .demande-info {
    padding: 16px;
    gap: 12px;
  }
  
  .info-title {
    font-size: 1rem;
  }
  
  .info-subtitle {
    font-size: 0.9rem;
  }
  
  .form-fields {
    gap: 24px;
  }
  
  .decision-section,
  .signature-section,
  .next-validator-section {
    padding: 16px;
  }
  
  .signature-zone {
    padding: 24px;
    min-height: 120px;
  }
}
</style>
