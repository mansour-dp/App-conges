<template>
  <div class="fiche-report" id="fiche-report">
    <div class="logo-centre">
      <img
        src="@/assets/images/logo-senelec.png"
        alt="Senelec"
        class="logo-fiche"
      />
    </div>
    <div class="entete-fiche">
      <div class="entete-texte">
        <div class="titre-processus">Processus Ressources Humaines</div>
        <div class="sous-titre">Demande de Report de Congés</div>
      </div>
      <div class="entete-infos">
        <div>Date de création : 26/03/18</div>
        <div>Réf : PS1-FOR-018-a</div>
        <div>Page 1 / 1</div>
      </div> 
    </div>
    <div class="bandeau-titre">
      <span>FICHE DE DEMANDE DE REPORT DE CONGÉS</span>
    </div>
    <form class="form-fiche" @submit.prevent>
      <div class="ligne-champs">
        <div class="champ">
          <label>Prénoms :</label>
          <input type="text" v-model="formData.prenom" required />
        </div>
        <div class="champ">
          <label>Nom :</label>
          <input type="text" v-model="formData.nom" required />
        </div>
      </div>
      <div class="ligne-champs">
        <div class="champ">
          <label>Matricule :</label>
          <input type="text" v-model="formData.matricule" required />
        </div>
        <div class="champ">
          <label>Unité d'Appartenance :</label>
          <input type="text" v-model="formData.unite" required />
        </div>
      </div>
      <div class="ligne-champs">
        <div class="champ">
          <label>Poste :</label>
          <input type="text" v-model="formData.poste" />
        </div>
      </div>
      <div class="ligne-champs">
        <div class="champ">
          <label>Adresse :</label>
          <input type="text" v-model="formData.adresse" />
        </div>
      </div>
      <div class="ligne-champs">
        <div class="champ">
          <label>Téléphone :</label>
          <input type="text" v-model="formData.telephone" />
        </div>
      </div>

      <div class="section-report">
        <div class="section-titre">Détails du Report :</div>
        <div class="ligne-champs">
          <div class="champ">
            <label>Date congé DRH :</label>
            <input type="date" v-model="formData.dateCongeDRH" required />
          </div>
          <div class="champ">
            <label>Date départ prévue :</label>
            <input
              type="date"
              v-model="formData.dateDepartPrevue"
              :min="minDateReport"
              required
            />
          </div>
        </div>
        <div class="ligne-champs">
          <div class="champ">
            <label>Joindre bulletin (PDF) :</label>
            <input
              type="file"
              @change="handleFileUpload"
              accept=".pdf"
              required
            />
          </div>
          <div v-if="formData.bulletin" class="champ">
            <span class="file-info">📄 {{ formData.bulletin.name || 'Fichier sélectionné' }}</span>
          </div>
        </div>
      </div>

      <div class="section-report">
        <div class="section-titre">Motif du Report :</div>
        <div class="ligne-champs">
          <div class="champ" style="width: 100%">
            <textarea
              v-model="formData.motif"
              rows="4"
              placeholder="Expliquez les raisons de votre demande de report..."
              required
              style="width: 100%"
            ></textarea>
          </div>
        </div>
      </div>

      <div class="section-signature">
        <div class="ligne-signature">
          <div class="date-container">
            <span>Dakar le</span>
            <input
              type="date"
              v-model="formData.dateDemande"
              style="width: 150px"
            />
          </div>
          <div class="signature-area">
            <span class="signature-label">Signature de l'intéressé(e)</span>
            <div class="signature-pad" @click="ouvrirPadSignature('employe')">
              <div v-if="formData.signatureEmploye" class="signature-image">
                <img :src="formData.signatureEmploye" alt="Signature" />
              </div>
              <div v-else class="signature-placeholder">
                <i class="fas fa-upload"></i>
                <span>Upload Signature</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Section des signatures d'approbation (remplies par les approbateurs lors de la validation) -->
      <table class="approbation-table">
        <tbody>
          <tr>
            <td class="approbation-cell-avis">
              <div class="approbation-title-v2">
                Avis du Supérieur Hiérarchique
              </div>
            </td>
            <td class="approbation-cell-avis">
              <div class="approbation-title-v2">Avis du Directeur d'Unité</div>
            </td>
          </tr>
          <tr>
            <td class="approbation-cell-avis">
              <div class="signature-pad disabled">
                <div class="signature-placeholder">
                  <i class="fas fa-lock"></i>
                  <span>Signature lors de l'approbation</span>
                </div>
              </div>
            </td>
            <td class="approbation-cell-avis">
              <div class="signature-pad disabled">
                <div class="signature-placeholder">
                  <i class="fas fa-lock"></i>
                  <span>Signature lors de l'approbation</span>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="rh-validation-container">
        <div class="rh-validation-row">
          <div class="rh-validation-col">
            <div class="rh-label-v2">Visa du Correspondant RH<sup>1</sup></div>
            <div class="signature-pad disabled">
              <div class="signature-placeholder">
                <i class="fas fa-lock"></i>
                <span>Signature lors de l'approbation</span>
              </div>
            </div>
          </div>
          <div class="rh-validation-col">
            <div class="rh-label-v2">Validation</div>
            <div class="rh-sublabel-v2">
              Département Administration du Personnel
            </div>
          </div>
        </div>
      </div>

      <div class="drh-decision-absence">
        Approbation du Directeur des Ressources Humaines
        <div class="signature-pad disabled">
          <div class="signature-placeholder">
            <i class="fas fa-lock"></i>
            <span>Signature lors de l'approbation</span>
          </div>
        </div>
        <div class="signature-line"></div>
      </div>

      <div class="note-bas">
        <sup>1</sup> Dans les Unités opérationnelles et Délégations Régionales
      </div>

      <div class="actions-print">
        <button
          type="button"
          class="btn-envoyer"
          @click="envoyerDemande"
          :disabled="!isSubmittable || demandeEnvoyee || isSubmitting"
        >
          {{ isSubmitting ? 'Envoi en cours...' : 'Soumettre' }}
        </button>
      </div>
      <div v-if="errors.length > 0" class="error-messages">
        <div v-for="error in errors" :key="error" class="error-message">
          {{ error }}
        </div>
      </div>
    </form>
    <SignaturePad
      v-if="showSignaturePad"
      @close="showSignaturePad = false"
      @signature-saved="sauvegarderSignature"
    />
    
    <!-- WorkflowModal -->
    <WorkflowModal
      v-model="showWorkflowModal"
      @submit="handleWorkflowSubmit"
      :demande="currentDemande"
    />
  </div>
</template>

<script>
import SignaturePad from "../ui/SignaturePad.vue";
import { useReportsStore } from '@/stores/reports'
import WorkflowModal from '@/components/workflow/WorkflowModal.vue'
import { useToast } from 'primevue/usetoast'
import { useUserStore } from '@/stores/users'
import { demandesReportApi } from '@/services/api'

export default {
  name: "DemandeReport",
  components: {
    SignaturePad,
    WorkflowModal,
  },
  setup() {
    const reportsStore = useReportsStore()
    const toast = useToast()
    const userStore = useUserStore()
    return { reportsStore, toast, userStore }
  },
  data() {
    return {
      showSignaturePad: false,
      currentSignatureType: null,
      currentYear: new Date().getFullYear(),
      formData: {
        prenom: "",
        nom: "",
        matricule: "",
        unite: "",
        poste: "",
        adresse: "",
        telephone: "",
        dateCongeDRH: "",
        dateDepartPrevue: "",
        bulletin: null,
        motif: "",
        dateDemande: new Date().toISOString().split("T")[0],
        dateCreation: new Date().toISOString().split("T")[0],
        signatureEmploye: null,
        signatureSuperieur: null,
        signatureDirecteur: null,
        signatureCorrespondantRH: null,
        signatureValidationDeptAdmin: null,
        signatureDirecteurRH: null,
        status: "en_attente",
        etapeActuelle: "Approbation Supérieur Hiérarchique",
      },
      demandeEnvoyee: false,
      confirmation: false,
      errors: [],
      showWorkflowModal: false,
      currentDemande: null,
      isSubmitting: false, // Protection contre les soumissions multiples
    };
  },
  computed: {
    minDateReport() {
      const today = new Date();
      return today.toISOString().split("T")[0];
    },
    peutImprimer() {
      return (
        this.formData.signatureEmploye &&
        this.formData.signatureSuperieur &&
        this.formData.signatureDirecteur &&
        this.formData.signatureCorrespondantRH &&
        this.formData.signatureValidationDeptAdmin &&
        this.formData.signatureDirecteurRH
      );
    },
    isSubmittable() {
      return this.formData.prenom && this.formData.nom && this.formData.matricule &&
        this.formData.dateCongeDRH &&
        this.formData.dateDepartPrevue &&
        this.formData.motif &&
        this.formData.bulletin &&
        this.formData.signatureEmploye
    },
    // Propriété pour compatibilité (si référencée quelque part)
    isDraftable() {
      return this.formData.prenom && this.formData.nom && this.formData.matricule
    },
    // Pour débugger - vous pouvez voir dans les dev tools
    formValidationStatus() {
      return {
        prenom: !!this.formData.prenom,
        nom: !!this.formData.nom,
        matricule: !!this.formData.matricule,
        dateCongeDRH: !!this.formData.dateCongeDRH,
        dateDepartPrevue: !!this.formData.dateDepartPrevue,
        motif: !!this.formData.motif,
        bulletin: !!this.formData.bulletin,
        signatureEmploye: !!this.formData.signatureEmploye
      }
    }
  },
  watch: {
    showWorkflowModal(newVal) {
      // Réinitialiser isSubmitting si le modal se ferme (annulation)
      if (!newVal && this.isSubmitting) {
        this.isSubmitting = false
      }
    }
  },
  mounted() {
    const user = localStorage.getItem("user");
    if (user) {
      try {
        const userData = JSON.parse(user);
        this.formData.nom = userData.nom || "";
        this.formData.prenom = userData.prenom || "";
      } catch (e) {}
    }
  },
  methods: {
    formatDate(dateString) {
      if (!dateString) return "";
      const options = { year: "numeric", month: "2-digit", day: "2-digit" };
      return new Date(dateString).toLocaleDateString("fr-FR", options);
    },
    imprimerFiche() {
      window.print();
    },
    envoyerDemande() {
      this.soumettreDemandeAvecWorkflow();
    },
    async soumettreDemandeAvecWorkflow() {
      // Protection contre les soumissions multiples
      if (this.isSubmitting) {
        console.log('⚠️ Soumission déjà en cours, ignoré...')
        return
      }

      console.log('🚀 Début de la soumission...')
      this.isSubmitting = true
      
      try {
        this.errors = []
        
        console.log('📝 Validation du formulaire...')
        // Validation des champs obligatoires
        if (!this.validateForm()) {
          console.log('❌ Validation échouée')
          this.isSubmitting = false
          return;
        }
        console.log('✅ Validation réussie')

        console.log('📋 Préparation des données...')
        // Créer d'abord la demande
        const demandeData = this.prepareDemandeData()
        demandeData.statut = 'brouillon' // Statut initial avant workflow
        
        console.log('📤 Envoi vers l\'API...', demandeData)
        const response = await demandesReportApi.create(demandeData)
        console.log('📥 Réponse reçue:', response)
        
        if (response.data.success) {
          this.currentDemande = response.data.data
          console.log('🎯 Ouverture du modal workflow...')
          // Ouvrir le modal de workflow
          this.showWorkflowModal = true
          console.log('✅ Modal ouvert:', this.showWorkflowModal)
        } else {
          throw new Error(response.data.message || 'Erreur lors de la création de la demande')
        }
        
      } catch (error) {
        console.error('💥 Erreur lors de la création de la demande:', error)
        console.error('💥 Détails de l\'erreur:', error.response?.data)
        this.errors = [error.response?.data?.message || 'Erreur lors de la création de la demande']
        this.isSubmitting = false
      }
    },
    validateForm() {
      console.log('🔍 Validation des champs...')
      console.log('formValidationStatus:', this.formValidationStatus)
      
      // Validation des champs obligatoires
      if (!this.formData.prenom || !this.formData.nom || !this.formData.matricule) {
        console.log('❌ Informations personnelles manquantes')
        this.errors = ['Veuillez remplir les informations personnelles (prénom, nom, matricule)'];
        return false;
      }

      if (!this.formData.dateCongeDRH || !this.formData.dateDepartPrevue) {
        console.log('❌ Dates manquantes')
        this.errors = ['Veuillez remplir toutes les dates requises'];
        return false;
      }

      if (!this.formData.motif) {
        console.log('❌ Motif manquant')
        this.errors = ['Veuillez saisir le motif du report'];
        return false;
      }

      if (!this.formData.bulletin) {
        console.log('❌ Bulletin manquant')
        this.errors = ['Veuillez joindre le bulletin PDF'];
        return false;
      }

      if (!this.formData.signatureEmploye) {
        console.log('❌ Signature manquante')
        this.errors = ['Votre signature est obligatoire pour soumettre la demande'];
        return false;
      }

      console.log('✅ Tous les champs requis sont remplis')
      return true;
    },
    async handleWorkflowSubmit(workflowData) {
      try {
        console.log('🚀 Début handleWorkflowSubmit avec:', workflowData)
        console.log('📋 currentDemande:', this.currentDemande)
        
        if (!this.currentDemande?.id) {
          throw new Error('Aucune demande créée pour le workflow');
        }

        console.log('Données envoyées au workflow:', {
          demande_id: this.currentDemande.id,
          superieur_email: workflowData.emailSuperieur
        });

        // Envoyer la demande avec l'email du supérieur
        const response = await demandesReportApi.soumettreAvecWorkflow({
          demande_id: this.currentDemande.id,
          superieur_email: workflowData.emailSuperieur
          // La signature de l'employé est déjà stockée dans la demande
        });

        if (response.data.success) {
          this.demandeEnvoyee = true;
          this.showWorkflowModal = false;

          // Afficher le toast de succès
          this.toast.add({
            severity: 'success',
            summary: 'Demande envoyée',
            detail: 'Votre demande de report a été envoyée à votre supérieur avec succès',
            life: 5000
          });

          setTimeout(() => {
            // Rediriger vers l'état des demandes selon le rôle de l'utilisateur
            this.redirectToCorrectEtatDemandes();
          }, 3000);
          
        } else {
          throw new Error(response.data.message || 'Erreur lors de l\'envoi');
        }

      } catch (error) {
        console.error('💥 Erreur lors de la soumission du workflow:', error)
        console.error('💥 Détails erreur:', error.response?.data)
        this.errors = [error.response?.data?.message || error.message || 'Erreur lors de la soumission']
        
        // Toast d'erreur
        this.toast.add({
          severity: 'error',
          summary: 'Erreur d\'envoi',
          detail: 'Erreur lors de l\'envoi au supérieur: ' + (error.response?.data?.message || error.message),
          life: 5000
        })
      } finally {
        this.isSubmitting = false
      }
    },
    prepareDemandeData() {
      // Préparer les pièces jointes si un bulletin est sélectionné
      let piecesJointes = [];
      if (this.formData.bulletin) {
        piecesJointes.push({
          nom: this.formData.bulletin.name,
          type: this.formData.bulletin.type,
          taille: this.formData.bulletin.size
        });
      }
      
      const baseData = {
        type_demande: 'report_conges',
        date_conge_drh: this.formData.dateCongeDRH || new Date().toISOString().split('T')[0],
        date_depart_prevue: this.formData.dateDepartPrevue,
        nouvelle_date_debut: this.formData.dateDepartPrevue,
        nouvelle_date_fin: this.formData.dateDepartPrevue,
        duree_jours: 1,
        motif: this.formData.motif,
        commentaire: this.formData.motif,
        signature_interresse: this.formData.signatureEmploye || 'signature_placeholder',
        pieces_jointes: piecesJointes,
        form_data: {
          prenom: this.formData.prenom,
          nom: this.formData.nom,
          matricule: this.formData.matricule,
          unite: this.formData.unite,
          poste: this.formData.poste,
          adresse: this.formData.adresse,
          telephone: this.formData.telephone,
          dateCongeDRH: this.formData.dateCongeDRH,
          dateDepartPrevue: this.formData.dateDepartPrevue,
          motif: this.formData.motif,
          bulletin_info: this.formData.bulletin ? {
            nom: this.formData.bulletin.name,
            taille: this.formData.bulletin.size
          } : null
        }
      };

      // Ajouter les signatures si elles existent
      if (this.formData.signatureEmploye) {
        baseData.signatures = {
          employe: {
            data: this.formData.signatureEmploye,
            name: `${this.formData.prenom} ${this.formData.nom}`,
            timestamp: new Date().toISOString(),
            role: 'Demandeur'
          }
        };
      }

      return baseData;
    },
    ouvrirPadSignature(type) {
      this.currentSignatureType = type;
      this.showSignaturePad = true;
    },
    sauvegarderSignature(signatureData) {
      const type = this.currentSignatureType;
      this.formData[
        `signature${type.charAt(0).toUpperCase() + type.slice(1)}`
      ] = signatureData;
    },
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        // Vérifier que c'est bien un PDF
        if (file.type !== 'application/pdf') {
          this.toast.add({
            severity: 'warn',
            summary: 'Format invalide',
            detail: 'Veuillez sélectionner un fichier PDF uniquement.',
            life: 4000
          })
          event.target.value = '';
          return;
        }
        
        // Vérifier la taille (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
          this.toast.add({
            severity: 'warn',
            summary: 'Fichier trop volumineux',
            detail: 'Le fichier ne doit pas dépasser 5MB.',
            life: 4000
          })
          event.target.value = '';
          return;
        }
        
        // Stocker le fichier avec ses informations
        this.formData.bulletin = {
          file: file,
          name: file.name,
          size: file.size,
          type: file.type
        };
        
        this.toast.add({
          severity: 'success',
          summary: 'Fichier ajouté',
          detail: `Fichier "${file.name}" ajouté avec succès`,
          life: 3000
        })
        
        console.log('Fichier PDF sélectionné:', file.name);
      }
    },
    resetForm() {
      // Réinitialiser le formulaire
      this.formData = {
        prenom: "",
        nom: "",
        matricule: "",
        unite: "",
        poste: "",
        adresse: "",
        telephone: "",
        dateCongeDRH: "",
        dateDepartPrevue: "",
        bulletin: null,
        motif: "",
        dateDemande: new Date().toISOString().split("T")[0],
        dateCreation: new Date().toISOString().split("T")[0],
        signatureEmploye: null,
        signatureSuperieur: null,
        signatureDirecteur: null,
        signatureCorrespondantRH: null,
        signatureValidationDeptAdmin: null,
        signatureDirecteurRH: null,
        status: "en_attente",
        etapeActuelle: "Approbation Supérieur Hiérarchique",
      }
      this.demandeEnvoyee = false
      this.errors = []
      this.currentDemande = null
      
      // Recharger les données utilisateur
      const user = localStorage.getItem("user");
      if (user) {
        try {
          const userData = JSON.parse(user);
          this.formData.nom = userData.nom || "";
          this.formData.prenom = userData.prenom || "";
        } catch (e) {}
      }
    },
    
    // Méthode pour rediriger vers le bon dashboard basé sur le rôle de l'utilisateur
    redirectToCorrectEtatDemandes() {
      const user = this.userStore.currentUser;
      
      if (!user || !user.roles || user.roles.length === 0) {
        // Par défaut, rediriger vers le dashboard employé
        this.$router.push({ name: 'etatDemandes' });
        return;
      }

      // Vérifier les rôles dans l'ordre de priorité
      const roles = user.roles.map(role => role.nom || role.name || role);
      
      if (roles.includes('Directeur RH') || roles.includes('directeur_rh')) {
        this.$router.push({ name: 'directeurRHEtatDemandes' });
      } else if (roles.includes('Directeur Unité') || roles.includes('directeur_unite')) {
        this.$router.push({ name: 'directeurUniteEtatDemandes' });
      } else if (roles.includes('Supérieur') || roles.includes('superieur') || roles.includes('Superviseur') || roles.includes('superviseur')) {
        this.$router.push({ name: 'superieurEtatDemandes' });
      } else {
        // Rôle employé ou autre
        this.$router.push({ name: 'etatDemandes' });
      }
    },
  },
  props: {
    sidebarOpen: {
      type: Boolean,
      default: true,
    },
  },
};
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.fiche-report {
  background: white;
  max-width: 900px;
  margin: 30px auto;
  padding: 32px 40px;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  font-family: "Segoe UI", Arial, sans-serif;
  color: #222;
}

.logo-centre {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 10px;
}

.logo-fiche {
  width: 90px;
  height: auto;
}

.entete-fiche {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  border-bottom: 2px solid #eee;
  padding-bottom: 10px;
}

.entete-texte {
  flex: 1;
  text-align: center;
}

.titre-processus {
  font-size: 16px;
  font-weight: 600;
}

.sous-titre {
  font-size: 15px;
  color: #555;
}

.entete-infos {
  text-align: right;
  font-size: 13px;
  color: #666;
}

.bandeau-titre {
  background: #f3f3f3;
  text-align: center;
  font-size: 22px;
  font-weight: bold;
  margin: 24px 0 18px 0;
  letter-spacing: 1px;
  padding: 10px 0;
  border-radius: 6px;
}

.form-fiche {
  margin-top: 10px;
}

.ligne-champs {
  display: flex;
  gap: 30px;
  margin-bottom: 12px;
}

.champ {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 8px;
}

.champ label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #424242;
  font-size: 14px;
}

.section-report {
  margin: 18px 0 10px 0;
}

.section-titre {
  font-weight: 600;
  font-size: 15px;
  margin-bottom: 7px;
}

.section-signature {
  margin: 18px 0 10px 0;
}

.ligne-signature {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 15px;
  margin-top: 10px;
}

.signature-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  min-width: 200px;
  position: relative;
  padding-bottom: 20px;
}

.signature-pad {
  width: 250px;
  height: 120px;
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

.signature-pad:hover {
  border-color: #008a9b;
  background: #f0f9fa;
}

.signature-pad.disabled {
  cursor: not-allowed;
  background: #f5f5f5;
  border-color: #ddd;
}

.signature-pad.disabled:hover {
  border-color: #ddd;
  background: #f5f5f5;
}

.signature-pad.disabled .signature-placeholder {
  color: #999;
}

.signature-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #666;
  position: relative;
  width: 100%;
}

.signature-placeholder i {
  font-size: 24px;
  margin-bottom: 8px;
}

.signature-placeholder span {
  font-size: 14px;
  margin-bottom: 8px;
}

.signature-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
}

.signature-image img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.signature-label {
  font-size: 14px;
  font-weight: 500;
  text-align: center;
}

.note-bas {
  font-size: 0.9em;
  color: #666;
  margin-top: 20px;
}

.actions-print {
  text-align: right;
  margin-top: 18px;
}

.actions-print button {
  background: #008a9b;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 28px;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  margin-left: 10px;
}

.actions-print button:disabled {
  background: #bdbdbd;
  cursor: not-allowed;
}

.btn-envoyer {
  background: #00b894;
}

.confirmation-message {
  margin-top: 18px;
  color: #00b894;
  font-weight: 600;
  text-align: right;
}

@media print {
  .actions-print,
  .actions-print button {
    display: none !important;
  }
  .fiche-report {
    box-shadow: none !important;
    border-radius: 0 !important;
    margin: 0 !important;
    padding: 0 0 0 0 !important;
    width: 100% !important;
    color: #000 !important;
  }
}

.date-creation-container {
  display: flex;
  align-items: center;
  gap: 5px;
}

.date-input-hidden-print {
  display: inline-block;
}

.date-display-only-print {
  display: none;
}

@media print {
  .date-input-hidden-print {
    display: none;
  }
  .date-display-only-print {
    display: inline-block;
  }
}

/* Styles spécifiques pour les approbations */
.approbation-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.approbation-table td {
  border: 1px solid #bbb;
  padding: 15px;
  vertical-align: top;
}

.approbation-table td:first-child {
  border-left: none;
}

.approbation-table td:last-child {
  border-right: none;
}

.approbation-cell-avis {
  text-align: center;
  width: 50%; /* Pour diviser le tableau en deux colonnes égales */
}

.approbation-cell-avis:first-child {
  border-right: 1px solid #bbb;
}

/* Nouveaux styles pour les sections sans tableau */
.rh-validation-container {
  display: flex;
  flex-direction: column;
  margin-top: 20px;
}

.rh-validation-row {
  display: flex;
}

.rh-validation-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 15px;
  gap: 10px;
}

.approbation-cell-visa {
  text-align: center;
  width: 50%;
}

.approbation-cell-validation {
  text-align: center;
  width: 50%;
}

.signature-box-empty {
  width: 200px; /* Taille ajustée pour l'espace de signature */
  height: 80px; /* Hauteur ajustée */
  border: 1px solid #ccc;
  margin: 10px auto 0 auto; /* Centrer la boîte */
  background: #f8f9fa;
}

.signature-line-bottom {
  width: 200px; /* Taille ajustée */
  border-bottom: 1px solid #333;
  margin: 10px auto 0 auto; /* Centrer la ligne */
  padding-bottom: 5px;
}

.drh-decision-absence {
  margin-top: 30px;
  text-align: left;
  font-weight: 500;
}

.signature-pad {
  width: 200px;
  height: 100px;
  border: 1px solid #ccc;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  background-color: #f8f8f8;
  transition: all 0.3s ease;
  margin: 10px 0;
}

.signature-pad:hover {
  border-color: #1976d2;
  background-color: #f0f7ff;
}

.signature-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.signature-image img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.signature-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #666;
}

.signature-placeholder i {
  font-size: 24px;
  margin-bottom: 8px;
}

.signature-placeholder span {
  font-size: 14px;
}

.signature-line {
  width: 200px;
  height: 1px;
  background-color: #000;
  margin-top: 5px;
}

.note-absence {
  font-size: 0.9em;
  color: #666;
  margin-top: 10px;
}

input[type="text"],
input[type="date"],
input[type="number"],
select,
textarea {
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 6px 10px;
  font-size: 14px;
  transition: all 0.3s ease;
  background-color: #ffffff;
  color: #374151;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

input[type="text"]:hover,
input[type="date"]:hover,
input[type="number"]:hover,
select:hover,
textarea:hover {
  border-color: #9ca3af;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

input[type="text"]:focus,
input[type="date"]:focus,
input[type="number"]:focus,
select:focus,
textarea:focus {
  border-color: #008a9b;
  outline: none;
  box-shadow: 0 0 0 3px rgba(0, 138, 155, 0.1), 0 2px 4px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

select {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 4px center;
  background-size: 12px;
  padding-right: 20px;
}

/* Style pour l'impression */
@media print {
  input[type="text"],
  input[type="date"],
  input[type="number"],
  select,
  textarea {
    border: 1px solid #000000;
    box-shadow: none;
    transform: none;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}

.error-messages {
  margin-top: 15px;
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
  padding: 8px;
  border-radius: 5px;
  margin-bottom: 5px;
}
</style>
