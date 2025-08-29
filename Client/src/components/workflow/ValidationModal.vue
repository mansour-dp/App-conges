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
                class="user-search-field"
              >
                <template #item="{ props, item }">
                  <v-list-item
                    v-bind="props"
                    class="user-search-item"
                    lines="two"
                  >
                    <template #prepend>
                      <v-avatar 
                        size="44" 
                        :color="getUserAvatarColor(item.raw.name)"
                        class="user-avatar"
                      >
                        <span class="avatar-text">{{ getUserInitials(item.raw.name) }}</span>
                      </v-avatar>
                    </template>
                    
                    <v-list-item-title class="user-name-title">
                      {{ item.raw.name }}
                    </v-list-item-title>
                    
                    <v-list-item-subtitle>
                      <div class="user-search-details">
                        <div class="user-email-line">
                          <v-icon size="14" color="grey-darken-1" class="mr-1">mdi-email</v-icon>
                          <span class="user-email-text">{{ item.raw.email }}</span>
                        </div>
                        
                        <div class="user-info-chips">
                          <v-chip 
                            v-if="item.raw.role" 
                            size="x-small" 
                            :color="getRoleChipColor(item.raw.role)"
                            variant="flat"
                            class="role-chip"
                          >
                            <v-icon size="12" class="mr-1">mdi-account-tie</v-icon>
                            {{ item.raw.role }}
                          </v-chip>
                          
                          <v-chip 
                            v-if="item.raw.department" 
                            size="x-small" 
                            color="blue-grey-lighten-2"
                            variant="flat"
                            class="dept-chip"
                          >
                            <v-icon size="12" class="mr-1">mdi-office-building</v-icon>
                            {{ item.raw.department }}
                          </v-chip>
                        </div>
                      </div>
                    </v-list-item-subtitle>
                    
                    <template #append>
                      <v-icon 
                        color="success" 
                        size="20"
                        class="select-indicator"
                      >
                        mdi-chevron-right
                      </v-icon>
                    </template>
                  </v-list-item>
                </template>

                <template #selection="{ item }">
                  <div class="selected-user-inline">
                    <v-avatar 
                      size="24" 
                      :color="getUserAvatarColor(item.raw.name)"
                      class="mr-2"
                    >
                      <span class="avatar-text-small">{{ getUserInitials(item.raw.name) }}</span>
                    </v-avatar>
                    <span class="selected-text">{{ item.raw.name }}</span>
                  </div>
                </template>
              </v-autocomplete>

              <!-- Affichage amélioré de l'utilisateur sélectionné -->
              <div v-if="selectedUser" class="selected-user-preview">
                <v-card 
                  variant="outlined" 
                  class="user-confirmation-card"
                  elevation="0"
                >
                  <v-card-text class="pa-4">
                    <div class="confirmation-header">
                      <v-icon color="success" size="20" class="mr-2">mdi-check-circle</v-icon>
                      <span class="confirmation-title">Utilisateur sélectionné</span>
                    </div>
                    
                    <div class="user-confirmation-content">
                      <div class="user-avatar-section">
                        <v-avatar 
                          size="56" 
                          :color="getUserAvatarColor(selectedUser.name)"
                          class="user-main-avatar"
                        >
                          <span class="avatar-text-large">{{ getUserInitials(selectedUser.name) }}</span>
                        </v-avatar>
                        <div class="user-status-indicator">
                          <v-icon color="success" size="16">mdi-check-circle</v-icon>
                        </div>
                      </div>
                      
                      <div class="user-info-section">
                        <div class="user-name-main">{{ selectedUser.name }}</div>
                        <div class="user-email-main">
                          <v-icon size="16" color="grey-darken-1" class="mr-1">mdi-email</v-icon>
                          {{ selectedUser.email }}
                        </div>
                        
                        <div class="user-details-chips">
                          <v-chip 
                            v-if="selectedUser.role" 
                            size="small" 
                            :color="getRoleChipColor(selectedUser.role)"
                            variant="flat"
                            class="detail-role-chip"
                          >
                            <v-icon size="14" class="mr-1">mdi-account-tie</v-icon>
                            {{ selectedUser.role }}
                          </v-chip>
                          
                          <v-chip 
                            v-if="selectedUser.department" 
                            size="small" 
                            color="blue-grey-lighten-2"
                            variant="flat"
                            class="detail-dept-chip"
                          >
                            <v-icon size="14" class="mr-1">mdi-office-building</v-icon>
                            {{ selectedUser.department }}
                          </v-chip>
                        </div>
                      </div>
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
    },
    validationContext: {
      type: String,
      default: null
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
      
      console.log('Debug canValidate:', {
        hasDecision,
        decision: this.decision,
        signature: this.signature,
        selectedUser: this.selectedUser,
        isLastValidator: this.isLastValidator,
        commentaire: this.commentaire
      })
      
      if (this.decision === 'approve') {
        // Pour approbation : signature obligatoire + prochain validateur (si pas dernier)
        const hasSignature = !!this.signature
        const hasNextValidator = this.isLastValidator || !!this.selectedUser
        console.log('Approve validation:', { hasSignature, hasNextValidator })
        return hasDecision && hasSignature && hasNextValidator
      } else if (this.decision === 'reject') {
        // Pour rejet : commentaire obligatoire uniquement
        const hasComment = !!this.commentaire && this.commentaire.length >= 10
        console.log('Reject validation:', { hasComment })
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

    // Détermine le rôle suivant dans la hiérarchie selon le contexte de validation
    nextRequiredRole() {
      // Si on a un contexte de validation spécifique, utilisons-le
      if (this.validationContext) {
        const roleHierarchy = {
          'superieur': 'Directeur Unité',
          'directeur_unite': 'Responsable RH', 
          'responsable_rh': 'Directeur RH'
          // directeur_rh n'a pas de suivant
        }
        
        return roleHierarchy[this.validationContext] || null
      }
      
      // Fallback sur l'ancienne logique si pas de contexte
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
      if (!this.nextRequiredRole) {
        return "Vous pouvez sélectionner n'importe quel utilisateur"
      }

      // Messages contextuels selon le type de validation
      if (this.validationContext === 'superieur') {
        return `En tant que supérieur hiérarchique, vous devez sélectionner un utilisateur avec le rôle "${this.nextRequiredRole}"`
      } else if (this.validationContext === 'directeur_unite') {
        return `En tant que Directeur d'Unité, vous devez sélectionner un utilisateur avec le rôle "${this.nextRequiredRole}"`
      } else if (this.validationContext === 'responsable_rh') {
        return `En tant que Responsable RH, vous devez sélectionner un utilisateur avec le rôle "${this.nextRequiredRole}"`
      } else if (this.validationContext === 'directeur_rh') {
        return `En tant que Directeur RH, vous devez sélectionner un utilisateur avec le rôle "${this.nextRequiredRole}"`
      }
      
      // Message par défaut
      return `Vous devez sélectionner un utilisateur avec le rôle "${this.nextRequiredRole}"`
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
        
        // ENLEVER LA RESTRICTION DE RÔLE - permettre la recherche de n'importe quel compte
        // Maintenant on peut rechercher tous les utilisateurs actifs sans restriction de rôle
        const filteredUsers = uniqueUsers
        
        // Formater pour l'autocomplete
        this.userSuggestions = filteredUsers.map(user => {
          const cleanName = this.formatUserName(user)
          return {
            label: cleanName, // Rétablir le label pour que la recherche fonctionne
            value: user.id,
            name: cleanName,
            email: user.email,
            role: user.roles && user.roles[0] ? user.roles[0].nom || user.roles[0].name : null,
            department: user.department ? user.department.name : null,
            id: user.id
          }
        })
        
      } catch (error) {
        console.error('Erreur recherche utilisateurs:', error)
        this.userSuggestions = []
      } finally {
        this.searchLoading = false
      }
    },

    onUserSelect(user) {
      console.log('User selected:', user)
      if (user) {
        this.selectedUser = user
        this.searchText = user.name || user.label
        this.emailProchainValidateur = user.email
        console.log('Selected user set:', this.selectedUser)
      } else {
        this.selectedUser = null
        this.searchText = ''
        this.emailProchainValidateur = ''
        console.log('Selected user cleared')
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
    },

    // Méthodes pour l'amélioration visuelle des utilisateurs
    getUserInitials(name) {
      if (!name) return 'U'
      const parts = name.split(' ')
      if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase()
      }
      return name[0].toUpperCase()
    },

    getUserAvatarColor(name) {
      if (!name) return 'primary'
      const colors = [
        'primary', 'secondary', 'success', 'info', 'warning',
        'purple', 'pink', 'indigo', 'deep-purple', 'blue',
        'cyan', 'teal', 'green', 'light-green', 'amber'
      ]
      const hash = name.split('').reduce((a, b) => a + b.charCodeAt(0), 0)
      return colors[hash % colors.length]
    },

    getRoleChipColor(role) {
      const roleColors = {
        'Directeur RH': 'red-darken-1',
        'Responsable RH': 'orange-darken-1', 
        'Directeur Unité': 'blue-darken-1',
        'Superieur': 'green-darken-1',
        'Employé': 'grey-darken-1'
      }
      return roleColors[role] || 'primary'
    },

    // Fonction pour nettoyer et formater correctement le nom complet
    formatUserName(user) {
      if (!user) return ''
      
      const firstName = user.first_name || ''
      const lastName = user.name || user.last_name || ''
      
      // Éviter la duplication si first_name est déjà inclus dans name
      if (firstName && lastName) {
        // Si lastName commence par firstName, utiliser seulement lastName
        if (lastName.toLowerCase().startsWith(firstName.toLowerCase())) {
          return lastName.trim()
        }
        // Sinon, combiner les deux
        return `${firstName} ${lastName}`.trim()
      }
      
      return (firstName || lastName).trim()
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

/* Styles améliorés pour la recherche d'utilisateurs Vuetify */
.user-search-field {
  margin-bottom: 16px;
}

/* Masquer le texte par défaut de vuetify pour éviter la duplication */
.user-search-field :deep(.v-list-item-title) {
  display: none !important;
}

.user-search-item {
  padding: 12px 16px !important;
  margin-bottom: 4px;
  border-radius: 12px;
  transition: all 0.2s ease;
}

.user-search-item:hover {
  background-color: #f5f5f5 !important;
  transform: translateY(-1px);
}

.user-avatar {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  font-weight: 600;
}

.avatar-text {
  font-size: 16px;
  font-weight: 700;
  color: white;
}

.avatar-text-small {
  font-size: 11px;
  font-weight: 700;
  color: white;
}

.user-name-title {
  font-weight: 600 !important;
  color: #1a1a1a !important;
  font-size: 1rem !important;
}

.user-search-details {
  padding-top: 4px;
}

.user-email-line {
  display: flex;
  align-items: center;
  margin-bottom: 6px;
}

.user-email-text {
  color: #666;
  font-size: 0.9rem;
}

.user-info-chips {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.role-chip, .dept-chip {
  font-size: 0.75rem !important;
  height: 20px !important;
  font-weight: 500 !important;
}

.select-indicator {
  opacity: 0.5;
  transition: all 0.2s ease;
}

.user-search-item:hover .select-indicator {
  opacity: 1;
  color: #1976d2 !important;
}

/* Styles pour l'utilisateur sélectionné inline */
.selected-user-inline {
  display: flex;
  align-items: center;
  padding: 4px 0;
}

.selected-text {
  font-weight: 600;
  color: #1a1a1a;
}

/* Styles pour la preview de confirmation */
.selected-user-preview {
  margin-top: 16px;
  animation: fadeInUp 0.3s ease;
}

.user-confirmation-card {
  border: 2px solid #4caf50 !important;
  background: #f8fff8 !important;
  border-radius: 16px !important;
}

.confirmation-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e8f5e8;
}

.confirmation-title {
  font-weight: 600;
  color: #2e7d32;
  font-size: 0.95rem;
}

.user-confirmation-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-avatar-section {
  position: relative;
}

.user-main-avatar {
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
}

.user-status-indicator {
  position: absolute;
  bottom: -2px;
  right: -2px;
  background: white;
  border-radius: 50%;
  padding: 2px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.user-info-section {
  flex: 1;
}

.user-name-main {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 6px;
}

.user-email-main {
  display: flex;
  align-items: center;
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 12px;
}

.user-details-chips {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.detail-role-chip, .detail-dept-chip {
  font-weight: 500 !important;
  font-size: 0.8rem !important;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
