<template>
  <v-dialog v-model="dialog" max-width="800px" persistent>
    <v-card>
      <v-card-title class="text-h5 primary white--text">
        <v-icon left color="white">mdi-clipboard-check</v-icon>
        Validation de la demande
      </v-card-title>
      
      <v-card-text class="pa-6">
        <!-- Informations de la demande -->
        <v-card variant="outlined" class="mb-4">
          <v-card-title class="text-h6 pb-2">
            <v-icon left>mdi-account</v-icon>
            {{ demande?.user?.first_name }} {{ demande?.user?.name }}
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="6">
                <div class="text-caption text-grey">Type de demande</div>
                <div class="font-weight-medium">{{ demande?.type_demande }}</div>
              </v-col>
              <v-col cols="6">
                <div class="text-caption text-grey">Durée</div>
                <div class="font-weight-medium">{{ demande?.duree_jours }} jour(s)</div>
              </v-col>
              <v-col cols="6">
                <div class="text-caption text-grey">Date de début</div>
                <div class="font-weight-medium">{{ formatDate(demande?.date_debut) }}</div>
              </v-col>
              <v-col cols="6">
                <div class="text-caption text-grey">Date de fin</div>
                <div class="font-weight-medium">{{ formatDate(demande?.date_fin) }}</div>
              </v-col>
              <v-col cols="12" v-if="demande?.motif">
                <div class="text-caption text-grey">Motif</div>
                <div class="font-weight-medium">{{ demande?.motif }}</div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Historique des validations -->
        <v-card v-if="validationHistory.length > 0" variant="outlined" class="mb-4">
          <v-card-title class="text-h6">
            <v-icon left>mdi-history</v-icon>
            Historique de validation
          </v-card-title>
          <v-card-text>
            <v-timeline density="compact">
              <v-timeline-item
                v-for="(validation, index) in validationHistory"
                :key="index"
                :dot-color="validation.status === 'approved' ? 'success' : 'error'"
                size="small"
              >
                <div class="d-flex justify-space-between">
                  <div>
                    <strong>{{ validation.validator_name }}</strong>
                    <div class="text-caption">{{ validation.role }}</div>
                  </div>
                  <div class="text-end">
                    <v-chip 
                      :color="validation.status === 'approved' ? 'success' : 'error'"
                      size="small"
                      variant="tonal"
                    >
                      {{ validation.status === 'approved' ? 'Approuvé' : 'Rejeté' }}
                    </v-chip>
                    <div class="text-caption">{{ formatDateTime(validation.date) }}</div>
                  </div>
                </div>
                <div v-if="validation.comment" class="text-body-2 mt-2 pa-2 bg-grey-lighten-4 rounded">
                  "{{ validation.comment }}"
                </div>
              </v-timeline-item>
            </v-timeline>
          </v-card-text>
        </v-card>

        <!-- Zone de décision -->
        <v-card variant="outlined" class="mb-4">
          <v-card-title class="text-h6">
            <v-icon left>mdi-gavel</v-icon>
            Votre Décision
          </v-card-title>
          <v-card-text>
            <v-radio-group v-model="decision" :rules="decisionRules">
              <v-radio 
                label="Approuver la demande" 
                value="approve"
                color="success"
              >
                <template #label>
                  <span class="text-success font-weight-medium">
                    <v-icon color="success" size="small" class="mr-1">mdi-check-circle</v-icon>
                    Approuver la demande
                  </span>
                </template>
              </v-radio>
              <v-radio 
                label="Rejeter la demande" 
                value="reject"
                color="error"
              >
                <template #label>
                  <span class="text-error font-weight-medium">
                    <v-icon color="error" size="small" class="mr-1">mdi-close-circle</v-icon>
                    Rejeter la demande
                  </span>
                </template>
              </v-radio>
            </v-radio-group>

            <v-textarea
              v-model="commentaire"
              label="Commentaire (optionnel)"
              variant="outlined"
              rows="3"
              placeholder="Ajoutez un commentaire sur votre décision..."
              class="mt-3"
            />
          </v-card-text>
        </v-card>

        <!-- Signature électronique obligatoire -->
        <v-card variant="outlined" class="mb-4">
          <v-card-title class="text-h6">
            <v-icon left>mdi-draw-pen</v-icon>
            Signature Électronique
            <v-chip color="error" size="small" class="ml-2">Obligatoire</v-chip>
          </v-card-title>
          <v-card-text>
            <div class="signature-zone" @click="openSignaturePad">
              <div v-if="signature" class="signature-preview">
                <img :src="signature" alt="Signature" class="signature-image" />
                <v-btn 
                  size="small" 
                  color="primary" 
                  variant="outlined"
                  @click.stop="openSignaturePad"
                  class="mt-2"
                >
                  Modifier la signature
                </v-btn>
              </div>
              <div v-else class="signature-placeholder">
                <v-icon size="48" color="grey-lighten-2">mdi-draw-pen</v-icon>
                <div class="text-h6 text-grey mt-2">Cliquez pour signer</div>
                <div class="text-caption text-grey">Signature électronique obligatoire</div>
              </div>
            </div>
          </v-card-text>
        </v-card>

        <!-- Email du prochain validateur (si approbation) -->
        <v-card 
          v-if="decision === 'approve' && !isLastValidator"
          variant="outlined" 
          class="mb-4"
        >
          <v-card-title class="text-h6">
            <v-icon left>mdi-account-arrow-right</v-icon>
            Prochain Validateur
          </v-card-title>
          <v-card-text>
            <v-text-field
              v-model="emailProchainValidateur"
              label="Email du prochain validateur"
              placeholder="exemple@senelec.sn"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="mdi-email"
              :rules="emailRules"
              :error-messages="emailError"
              @input="clearEmailError"
            />
            
            <!-- Suggestion d'utilisateur -->
            <v-card 
              v-if="nextUserSuggestion" 
              variant="outlined" 
              class="mt-2 pa-3"
              color="success"
            >
              <div class="d-flex align-center">
                <v-avatar size="32" color="primary">
                  <v-icon color="white">mdi-account</v-icon>
                </v-avatar>
                <div class="ml-3">
                  <div class="font-weight-medium">{{ nextUserSuggestion.name }}</div>
                  <div class="text-caption text-grey">{{ nextUserSuggestion.role }} - {{ nextUserSuggestion.department }}</div>
                </div>
                <v-spacer></v-spacer>
                <v-chip color="success" variant="outlined" size="small">
                  <v-icon left size="small">mdi-check</v-icon>
                  Trouvé
                </v-chip>
              </div>
            </v-card>
          </v-card-text>
        </v-card>
      </v-card-text>

      <v-card-actions class="pa-6 pt-0">
        <v-spacer></v-spacer>
        <v-btn 
          @click="annuler" 
          variant="outlined"
          :disabled="loading"
        >
          Annuler
        </v-btn>
        <v-btn 
          @click="valider" 
          :color="decision === 'approve' ? 'success' : 'error'"
          variant="elevated"
          :loading="loading"
          :disabled="!canValidate"
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
  
  data() {
    return {
      decision: '',
      commentaire: '',
      signature: null,
      emailProchainValidateur: '',
      nextUserSuggestion: null,
      emailError: '',
      loading: false,
      showSignaturePad: false,
      searchTimeout: null,
      
      validationHistory: [], // Sera rempli avec les validations précédentes
      
      decisionRules: [
        v => !!v || 'Veuillez choisir une décision'
      ],
      
      emailRules: [
        v => !!v || 'L\'email du prochain validateur est obligatoire',
        v => /.+@.+\..+/.test(v) || 'Email invalide'
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
      const hasSignature = !!this.signature
      const hasNextEmail = this.decision === 'reject' || this.isLastValidator || !!this.emailProchainValidateur
      
      return hasDecision && hasSignature && hasNextEmail
    }
  },
  
  watch: {
    emailProchainValidateur(newEmail) {
      clearTimeout(this.searchTimeout)
      
      if (newEmail && newEmail.includes('@')) {
        this.searchTimeout = setTimeout(() => {
          this.searchNextUser(newEmail)
        }, 500)
      } else {
        this.nextUserSuggestion = null
        this.emailError = ''
      }
    },
    
    modelValue(newVal) {
      if (newVal && this.demande) {
        this.loadValidationHistory()
      } else if (!newVal) {
        this.resetForm()
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
        console.error('Erreur recherche utilisateur:', error)
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
          demandeId: this.demande.id,
          decision: this.decision,
          commentaire: this.commentaire,
          signature: this.signature,
          emailProchainValidateur: this.decision === 'approve' && !this.isLastValidator ? this.emailProchainValidateur : null,
          nextUserSuggestion: this.nextUserSuggestion
        }
        
        this.$emit('submit', validationData)
        this.dialog = false
        
      } catch (error) {
        console.error('Erreur validation:', error)
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
      this.emailProchainValidateur = ''
      this.nextUserSuggestion = null
      this.emailError = ''
      this.loading = false
      this.validationHistory = []
      clearTimeout(this.searchTimeout)
    }
  }
}
</script>

<style scoped>
.v-card-title {
  background: #008a9b;
}

.signature-zone {
  border: 2px dashed #ccc;
  border-radius: 8px;
  padding: 24px;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.3s;
  min-height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.signature-zone:hover {
  border-color: #008a9b;
}

.signature-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.signature-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.signature-image {
  max-width: 200px;
  max-height: 80px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.v-timeline {
  margin-top: 0;
}
</style>
