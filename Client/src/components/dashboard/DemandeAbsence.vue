<template>
  <div class="fiche-absence" id="fiche-absence">
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
        <div class="sous-titre">Demande d'autorisation d'absence</div>
      </div>
      <div class="entete-infos">
        <div>Date de création : 26/03/18</div>
        <div>Réf : PS1-FOR-017-a</div>
        <div>Page 1 / 1</div>
      </div>
    </div>
    <div class="bandeau-titre">
      <span>DEMANDE D'AUTORISATION D'ABSENCE</span>
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

      <div class="section-absence">
        <div class="section-titre">Sollicite une autorisation d'absence :</div>
        <div class="ligne-champs">
          <div class="champ">
            <label>Le matin du :</label>
            <input type="date" v-model="formData.matin" />
          </div>
          <div class="champ">
            <label>L'après-midi du :</label>
            <input type="date" v-model="formData.apresMidi" />
          </div>
        </div>
        <div class="ligne-champs">
          <div class="champ">
            <label>La journée du :</label>
            <input type="date" v-model="formData.journee" />
          </div>
        </div>
        <div class="ligne-champs">
          <div class="champ">
            <label>Les journées du :</label>
            <input type="date" v-model="formData.periodeDebut" />
          </div>
          <div class="champ">
            <label>Jusqu'au :</label>
            <input type="date" v-model="formData.periodeFin" />
          </div>
          <div class="champ">
            <span>(inclus)</span>
          </div>
        </div>
      </div>

      <div class="section-absence">
        <div class="ligne-champs">
          <div class="champ">
            <label>Nombre de jours déductibles :</label>
            <input
              type="number"
              min="0"
              v-model="formData.nbJours"
              style="width: 60px"
            />
            <span>jours</span>
          </div>
        </div>
      </div>

      <div class="section-absence">
        <div class="section-titre">Motifs à préciser obligatoirement :</div>
        <div class="ligne-champs">
          <div class="champ" style="width: 100%">
            <textarea
              v-model="formData.motif"
              rows="4"
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

      <table class="approbation-table-absence">
        <thead>
          <tr>
            <th>Avis du Supérieur Hiérarchique</th>
            <th>Avis du Directeur d'Unité</th>
            <th>Avis du Correspondant RH<sup>1</sup></th>
            <th>
              Avis Département Administration du Personnel et Rémunération
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div class="signature-pad disabled">
                <div class="signature-placeholder">
                  <i class="fas fa-lock"></i>
                  <span>Signature lors de l'approbation</span>
                </div>
              </div>
            </td>
            <td>
              <div class="signature-pad disabled">
                <div class="signature-placeholder">
                  <i class="fas fa-lock"></i>
                  <span>Signature lors de l'approbation</span>
                </div>
              </div>
            </td>
            <td>
              <div class="signature-pad disabled">
                <div class="signature-placeholder">
                  <i class="fas fa-lock"></i>
                  <span>Signature lors de l'approbation</span>
                </div>
              </div>
            </td>
            <td>
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

      <div class="drh-decision-absence">
        Décision du Directeur des Ressources Humaines
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
          :disabled="demandeEnvoyee || !formData.signatureEmploye || isSubmitting"
        >
          {{ isSubmitting ? 'Envoi en cours...' : 'Soumettre' }}
        </button>
      </div>
      <div v-if="confirmation" class="confirmation-message">
        Demande envoyée avec succès !
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
import { useAbsencesStore } from '@/stores/absences'
import WorkflowModal from '@/components/workflow/WorkflowModal.vue'
import { useToast } from 'primevue/usetoast'
import { useUserStore } from '@/stores/users'
import { demandesAbsenceApi } from '@/services/api'

export default {
  name: "DemandeAbsence",
  components: {
    SignaturePad,
    WorkflowModal,
  },
  setup() {
    const absencesStore = useAbsencesStore()
    const toast = useToast()
    const userStore = useUserStore()
    return { absencesStore, toast, userStore }
  },
  data() {
    return {
      showSignaturePad: false,
      currentSignatureType: null,
      formData: {
        prenom: "",
        nom: "",
        matricule: "",
        unite: "",
        poste: "",
        adresse: "",
        telephone: "",
        matin: "",
        apresMidi: "",
        journee: "",
        periodeDebut: "",
        periodeFin: "",
        nbJours: "",
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
    nbJours() {
      if (!this.formData.periodeDebut || !this.formData.periodeFin) {
        return 0
      }
      const debut = new Date(this.formData.periodeDebut)
      const fin = new Date(this.formData.periodeFin)
      const diffTime = Math.abs(fin - debut)
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1
    },
    isDraftable() {
      return this.formData.prenom && this.formData.nom && this.formData.matricule
    },
    isSubmittable() {
      return this.isDraftable &&
        this.formData.motif &&
        (this.formData.matin || this.formData.apresMidi || this.formData.journee || 
         (this.formData.periodeDebut && this.formData.periodeFin))
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
    async enregistrerBrouillon() {
      try {
        this.errors = []
        const demandeData = this.prepareDemandeData()
        demandeData.status = 'brouillon'
        
        await this.absencesStore.createDemande(demandeData)
        
        this.confirmation = true
        setTimeout(() => {
          this.confirmation = false
        }, 3000)
      } catch (error) {
        console.error('Erreur lors de l\'enregistrement du brouillon:', error)
        this.errors = [error.response?.data?.message || 'Erreur lors de l\'enregistrement']
      }
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
        
        // Validation des champs obligatoires
        if (!this.validateForm()) {
          this.isSubmitting = false
          return;
        }

        // Créer d'abord la demande
        const demandeData = this.prepareDemandeData()
        demandeData.statut = 'brouillon' // Statut initial avant workflow
        
        console.log('Données à envoyer:', demandeData); // Debug
        
        const response = await demandesAbsenceApi.create(demandeData)
        
        if (response.data.success) {
          this.currentDemande = response.data.data
          // Ouvrir le modal de workflow
          this.showWorkflowModal = true
          console.log('✅ Modal ouvert:', this.showWorkflowModal)
        } else {
          throw new Error(response.data.message || 'Erreur lors de la création de la demande')
        }
        
      } catch (error) {
        console.error('Erreur lors de la création de la demande:', error)
        console.error('Détails de l\'erreur:', error.response?.data)
        this.errors = [error.response?.data?.message || 'Erreur lors de la création de la demande']
      } finally {
        this.isSubmitting = false
      }
    },
    async handleWorkflowSubmit(workflowData) {
      try {
        console.log('🚀 Début handleWorkflowSubmit absence avec:', workflowData)
        console.log('📋 currentDemande:', this.currentDemande)
        
        if (!this.currentDemande?.id) {
          throw new Error('Aucune demande créée pour le workflow');
        }

        console.log('Données envoyées au workflow:', {
          demande_id: this.currentDemande.id,
          superieur_email: workflowData.emailSuperieur
        });

        // Envoyer la demande avec l'email du supérieur
        const response = await demandesAbsenceApi.soumettreAvecWorkflow({
          demande_id: this.currentDemande.id,
          superieur_email: workflowData.emailSuperieur
          // La signature de l'employé est déjà stockée dans la demande
        });

        if (response.data.success) {
          this.demandeEnvoyee = true;
          this.confirmation = true;
          this.showWorkflowModal = false;

          // Afficher le toast de succès
          this.toast.add({
            severity: 'success',
            summary: 'Demande envoyée',
            detail: 'Votre demande d\'absence a été envoyée à votre supérieur avec succès',
            life: 5000
          });

          setTimeout(() => {
            this.confirmation = false;
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

    validateForm() {
      if (!this.formData.prenom || !this.formData.nom || !this.formData.matricule) {
        this.toast.add({
          severity: 'warn',
          summary: 'Informations manquantes',
          detail: 'Veuillez remplir les informations personnelles (prénom, nom, matricule)',
          life: 4000
        });
        return false;
      }

      if (!this.formData.motif) {
        this.toast.add({
          severity: 'warn',
          summary: 'Motif requis',
          detail: 'Veuillez saisir le motif de votre absence',
          life: 4000
        });
        return false;
      }

      if (!this.formData.signatureEmploye) {
        this.toast.add({
          severity: 'warn',
          summary: 'Signature requise',
          detail: 'Votre signature est obligatoire pour soumettre la demande',
          life: 4000
        });
        return false;
      }

      return true;
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
    prepareDemandeData() {
      // Déterminer le type d'absence et les dates
      let typeAbsence = 'personnelle' // Type par défaut
      
      return {
        type_absence: typeAbsence,
        date_matin: this.formData.matin || null,
        date_apres_midi: this.formData.apresMidi || null,
        date_journee: this.formData.journee || null,
        periode_debut: this.formData.periodeDebut || null,
        periode_fin: this.formData.periodeFin || null,
        nb_jours_deductibles: this.nbJours,
        motif: this.formData.motif,
        commentaire: this.formData.motif,
        signature_interresse: 'signature_placeholder',
        form_data: {
          prenom: this.formData.prenom,
          nom: this.formData.nom,
          matricule: this.formData.matricule,
          unite: this.formData.unite,
          poste: this.formData.poste,
          adresse: this.formData.adresse,
          telephone: this.formData.telephone,
        }
      }
    },
    ouvrirPadSignature(type) {
      // Seul l'employé peut signer
      if (type === 'employe') {
        this.currentSignatureType = type;
        this.showSignaturePad = true;
      }
    },
    sauvegarderSignature(signatureData) {
      const type = this.currentSignatureType;
      this.formData[
        `signature${type.charAt(0).toUpperCase() + type.slice(1)}`
      ] = signatureData;
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

.fiche-absence {
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
  min-width: 110px;
  font-size: 14px;
  font-weight: 500;
}

.section-absence {
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

.signature-line {
  position: absolute;
  bottom: 0;
  left: 0;
  transform: translateX(0%);
  width: 40%;
  height: 1px;
  background-color: #333;
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

.signature-label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: #333;
}

.signature-line {
  width: 200px;
  height: 1px;
  background-color: #000;
  margin-top: 5px;
}

.note-bas {
  font-size: 12px;
  color: #888;
  margin-top: 10px;
  text-align: left;
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
  .fiche-absence {
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

.approbation-table-absence {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.approbation-table-absence th,
.approbation-table-absence td {
  border: 1px solid #bbb;
  padding: 8px;
  vertical-align: top;
  text-align: center;
}

.approbation-table-absence th {
  background-color: #f3f3f3;
  font-weight: 600;
  font-size: 14px;
}

.drh-decision-absence {
  margin-top: 20px;
  font-weight: 600;
  font-size: 14px;
  text-align: center;
  padding-bottom: 20px;
  position: relative;
}

.drh-decision-absence .signature-line {
  position: absolute;
  bottom: 0;
  left: 0;
  transform: translateX(0%);
  width: 40%;
  height: 1px;
  background-color: #333;
}

.approbation-table-absence .signature-pad {
  width: 100%;
  height: 100px;
  border: 1px dashed #ccc;
  border-radius: 4px;
  margin: 5px auto;
}

/* Seule la signature de l'employé reste cliquable */
.section-signature .signature-pad {
  cursor: pointer;
  opacity: 1;
}

.signature-pad.disabled {
  cursor: not-allowed;
  background: #f5f5f5;
  border-color: #ddd;
  opacity: 1;
}

.signature-pad.disabled:hover {
  border-color: #ddd;
  background: #f5f5f5;
}

.signature-pad.disabled .signature-placeholder {
  color: #999;
}

.note-bas {
  font-size: 12px;
  color: #888;
  margin-top: 10px;
  text-align: left;
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
