<template>
  <div class="fiche-conge" id="fiche-conge">
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
        <div class="sous-titre">Fiche de Demande de Congés</div>
      </div>
      <div class="entete-infos">
        <div>Date de création : 26/03/18</div>
        <div>Réf : PS1-FOR-018-a</div>
        <div>Page 1 / 1</div>
      </div>
    </div>
    <div class="bandeau-titre">
      <span>FICHE DE DEMANDE DE CONGE</span>
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
      <div class="section-conge">
        <div class="section-titre">
          Demande à bénéficier de ses droits au congé :
        </div>
        <div class="section-titre-bandeau">Nature des Congés</div>
        <ul class="liste-conges">
          <li>
            <input type="checkbox" v-model="formData.annuel" /> Congés Annuels
          </li>
          <li>
            <input type="checkbox" v-model="formData.fractionne" /> Congés
            Fractionnés
          </li>
          <li>
            <input type="checkbox" v-model="formData.legal" /> Autres congés
            légaux
          </li>
        </ul>
      </div>
      <div class="section-conge">
        <div class="section-titre-bandeau">Période des Congés</div>
        <div class="ligne-champs">
          <div class="champ">
            <label>Date de départ :</label>
            <input type="date" v-model="formData.dateDebut" required />
          </div>
          <div class="champ">
            <label>au :</label>
            <input type="date" v-model="formData.dateFin" required />
          </div>
          <div class="champ">
            <label>soit</label>
            <input
              type="number"
              :value="nbJours"
              readonly
              style="width: 70px; background: #f3f3f3"
            />
            jours
          </div>
        </div>
        <div class="ligne-champs">
          <div class="champ">
            <label>Date de reprise :</label>
            <input type="date" v-model="formData.dateReprise" required />
          </div>
        </div>
      </div>
      <div class="section-conge">
        <div class="section-titre-bandeau">Situation des jours de congés</div>
        <div class="ligne-champs">
          <div class="champ">
            <label>A la date de la demande :</label>
            <input
              type="number"
              min="0"
              v-model="formData.soldeAvant"
              style="width: 60px"
            />
            <span>jours</span>
          </div>
        </div>
        <div class="ligne-champs">
          <div class="champ">
            <label>Jour(s) imputable(s) :</label>
            <input
              type="number"
              min="0"
              v-model="formData.joursImputables"
              style="width: 60px"
            />
            <span>jours</span>
          </div>
        </div>
        <div class="ligne-champs">
          <div class="champ">
            <label>Solde après déduction :</label>
            <input
              type="number"
              :value="soldeApresDeduction"
              readonly
              style="width: 60px; background: #f3f3f3"
            />
            <span>jours</span>
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
              <div
                class="signature-pad"
                @click="ouvrirPadSignature('superieur')"
              >
                <div v-if="formData.signatureSuperieur" class="signature-image">
                  <img :src="formData.signatureSuperieur" alt="Signature" />
                </div>
                <div v-else class="signature-placeholder">
                  <i class="fas fa-upload"></i>
                  <span>Signature Supérieur</span>
                </div>
              </div>
            </td>
            <td class="approbation-cell-avis">
              <div
                class="signature-pad"
                @click="ouvrirPadSignature('directeur')"
              >
                <div v-if="formData.signatureDirecteur" class="signature-image">
                  <img :src="formData.signatureDirecteur" alt="Signature" />
                </div>
                <div v-else class="signature-placeholder">
                  <i class="fas fa-upload"></i>
                  <span>Signature Directeur</span>
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
            <div
              class="signature-pad"
              @click="ouvrirPadSignature('correspondantRH')"
            >
              <div
                v-if="formData.signatureCorrespondantRH"
                class="signature-image"
              >
                <img :src="formData.signatureCorrespondantRH" alt="Signature" />
              </div>
              <div v-else class="signature-placeholder">
                <i class="fas fa-upload"></i>
                <span>Upload Signature</span>
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
        <div class="signature-pad" @click="ouvrirPadSignature('directeurRH')">
          <div v-if="formData.signatureDirecteurRH" class="signature-image">
            <img :src="formData.signatureDirecteurRH" alt="Signature" />
          </div>
          <div v-else class="signature-placeholder">
            <i class="fas fa-upload"></i>
            <span>Upload Signature</span>
          </div>
        </div>
        <div class="signature-line"></div>
      </div>

      <div class="note-bas">
        1 Dans les Unités opérationnelles et Délégations Régionales
      </div>
      <div class="actions-print">
        <button
          type="button"
          class="btn-envoyer"
          @click="soumettreDemandeAvecWorkflow"
          :disabled="demandeEnvoyee || !formData.signatureEmploye"
        >
          Soumettre
        </button>
      </div>
      <div v-if="confirmation" class="confirmation-message">
        Demande envoyée avec succès !
      </div>
    </form>
    
    <!-- Modals -->
    <SignaturePad
      v-if="showSignaturePad"
      @close="showSignaturePad = false"
      @signature-saved="sauvegarderSignature"
    />
    
    <WorkflowModal
      v-model="showWorkflowModal"
      :demande="currentDemande"
      @submit="handleWorkflowSubmit"
    />
  </div>
</template>

<script>
import SignaturePad from "../ui/SignaturePad.vue";
import WorkflowModal from "../workflow/WorkflowModal.vue";
import { useCongesStore } from "@/stores/conges";
import { useNotificationsStore } from "@/stores/notifications";
import { demandesApi } from "@/services/api";

export default {
  name: "PlanificationConges",
  components: {
    SignaturePad,
    WorkflowModal,
  },
  setup() {
    const congesStore = useCongesStore();
    const notificationsStore = useNotificationsStore();
    return { congesStore, notificationsStore };
  },
  data() {
    return {
      showSignaturePad: false,
      currentSignatureType: null,
      showWorkflowModal: false,
      currentDemande: null,
      formData: {
        dateCreation: new Date().toISOString().split("T")[0],
        prenom: "",
        nom: "",
        matricule: "",
        unite: "",
        poste: "",
        adresse: "",
        telephone: "",
        annuel: false,
        fractionne: false,
        legal: false,
        dateDebut: "",
        dateFin: "",
        dateReprise: "",
        soldeAvant: 0,
        joursImputables: 0,
        dateDemande: new Date().toISOString().split("T")[0],
        signatureEmploye: null,
        signatureSuperieur: null,
        signatureDirecteur: null,
      },
      demandeEnvoyee: false,
      confirmation: false,
    };
  },
  computed: {
    nbJours() {
      if (!this.formData.dateDebut || !this.formData.dateFin) return 0;
      const debut = new Date(this.formData.dateDebut);
      const fin = new Date(this.formData.dateFin);
      const diffTime = Math.abs(fin - debut);
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    },
    soldeApresDeduction() {
      return this.formData.soldeAvant - this.formData.joursImputables;
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
    formatDate(date) {
      if (!date) return "";
      return new Date(date).toLocaleDateString("fr-FR");
    },
    imprimerFiche() {
      window.print();
    },
    async soumettreDemandeAvecWorkflow() {
      try {
        // Validation des champs obligatoires
        if (!this.validateForm()) {
          return;
        }

        // Créer la demande d'abord
        const demandeData = this.prepareDemandeData();
        console.log('Données à envoyer:', demandeData); // Debug
        
        const response = await demandesApi.create(demandeData);
        
        if (response.data.success) {
          this.currentDemande = response.data.data;
          this.showWorkflowModal = true; // Ouvrir le modal de workflow
        } else {
          throw new Error(response.data.message || 'Erreur lors de la création de la demande');
        }
        
      } catch (error) {
        console.error('Erreur lors de la soumission:', error);
        console.error('Détails de l\'erreur:', error.response?.data); // Plus de détails
        this.$toast?.add({
          severity: 'error',
          summary: 'Erreur',
          detail: 'Erreur lors de la création de la demande: ' + (error.response?.data?.message || error.message),
          life: 5000
        });
      }
    },

    validateForm() {
      if (!this.formData.prenom || !this.formData.nom || !this.formData.matricule) {
        this.$toast?.add({
          severity: 'warn',
          summary: 'Champs requis',
          detail: 'Veuillez remplir tous les champs obligatoires',
          life: 4000
        });
        return false;
      }

      if (!this.formData.dateDebut || !this.formData.dateFin) {
        this.$toast?.add({
          severity: 'warn',
          summary: 'Dates requises',
          detail: 'Veuillez saisir les dates de début et fin',
          life: 4000
        });
        return false;
      }

      if (!this.formData.signatureEmploye) {
        this.$toast?.add({
          severity: 'warn',
          summary: 'Signature requise',
          detail: 'Votre signature est obligatoire pour soumettre la demande',
          life: 4000
        });
        return false;
      }

      if (!this.formData.annuel && !this.formData.fractionne && !this.formData.legal) {
        this.$toast?.add({
          severity: 'warn',
          summary: 'Type de congé requis',
          detail: 'Veuillez sélectionner au moins un type de congé',
          life: 4000
        });
        return false;
      }

      return true;
    },

    prepareDemandeData() {
      // Déterminer le type de demande basé sur les cases cochées
      let typeDemande = 'conge_annuel'; // Par défaut
      if (this.formData.fractionne) typeDemande = 'conges_fractionnes'; // Congés fractionnés
      if (this.formData.legal) typeDemande = 'autres_conges_legaux'; // Autres congés légaux

      const baseData = {
        type_demande: typeDemande,
        date_debut: this.formData.dateDebut,
        date_fin: this.formData.dateFin,
        duree_jours: this.nbJours,
        motif: `Demande de ${this.getTypeDemandeLabel()} - ${this.formData.prenom} ${this.formData.nom}`,
        commentaire: this.formData.commentaire || '',
        statut: 'brouillon', // Statut initial avant workflow
        form_data: this.formData // Sauvegarder les données du formulaire
      };

      // Ajouter les signatures seulement si elles existent
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

    getTypeDemandeLabel() {
      if (this.formData.annuel) return 'congé annuel';
      if (this.formData.fractionne) return 'congé fractionné';
      if (this.formData.legal) return 'autres congés légaux';
      return 'congé';
    },

    async handleWorkflowSubmit(workflowData) {
      try {
        if (!this.currentDemande?.id) {
          throw new Error('Aucune demande créée pour le workflow');
        }

        console.log('Données envoyées au workflow:', {
          demande_id: this.currentDemande.id,
          superieur_email: workflowData.emailSuperieur
        });

        // Envoyer la demande avec l'email du supérieur
        const response = await demandesApi.soumettreAvecWorkflow({
          demande_id: this.currentDemande.id,
          superieur_email: workflowData.emailSuperieur
          // La signature de l'employé est déjà stockée dans la demande
        });

        if (response.data.success) {
          this.demandeEnvoyee = true;
          this.confirmation = true;
          
          this.$toast?.add({
            severity: 'success',
            summary: 'Demande envoyée',
            detail: 'Votre demande a été envoyée à votre supérieur avec succès',
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
        console.error('Erreur workflow:', error);
        this.$toast?.add({
          severity: 'error',
          summary: 'Erreur d\'envoi',
          detail: 'Erreur lors de l\'envoi au supérieur: ' + error.message,
          life: 5000
        });
      }
    },
    ouvrirPadSignature(type) {
      this.currentSignatureType = type;
      this.showSignaturePad = true;
    },
    fermerPadSignature() {
      this.showSignaturePad = false;
      this.currentSignatureType = null;
    },
    sauvegarderSignature(signature) {
      if (this.currentSignatureType === "employe") {
        this.formData.signatureEmploye = signature;
      } else if (this.currentSignatureType === "superieur") {
        this.formData.signatureSuperieur = signature;
      } else if (this.currentSignatureType === "directeur") {
        this.formData.signatureDirecteur = signature;
      }
      this.fermerPadSignature();
    },
    async soumettreDemande() {
      try {
        // Vérifier que tous les champs requis sont remplis
        if (
          !this.formData.prenom ||
          !this.formData.nom ||
          !this.formData.matricule ||
          !this.formData.unite ||
          !this.formData.dateDebut ||
          !this.formData.dateFin ||
          !this.formData.dateReprise ||
          !this.formData.signatureEmploye
        ) {
          this.$toast.add({
            severity: 'warn',
            summary: 'Champs requis',
            detail: 'Veuillez remplir tous les champs obligatoires',
            life: 4000
          });
          return;
        }

        // Vérifier qu'au moins un type de congé est sélectionné
        if (
          !this.formData.annuel &&
          !this.formData.fractionne &&
          !this.formData.legal
        ) {
          this.$toast.add({
            severity: 'warn',
            summary: 'Type de congé requis',
            detail: 'Veuillez sélectionner au moins un type de congé',
            life: 4000
          });
          return;
        }

        // Soumettre la demande au store
        const id = await this.congesStore.soumettreDemande(this.formData);

        // Afficher un message de succès
        this.$toast.add({
          severity: 'success',
          summary: 'Demande soumise',
          detail: `Demande soumise avec succès. Numéro : ${id}`,
          life: 3000
        });

        // Émettre un événement pour fermer le formulaire
        this.$emit("close");
      } catch (error) {
        this.$toast.add({
          severity: 'error',
          summary: 'Erreur de soumission',
          detail: 'Erreur lors de la soumission : ' + error.message,
          life: 5000
        });
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
    }
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

.fiche-conge {
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

.section-conge {
  margin: 18px 0 10px 0;
}

.section-titre {
  font-weight: 600;
  font-size: 15px;
  margin-bottom: 7px;
}

.section-titre-bandeau {
  font-weight: 600;
  font-size: 15px;
  margin-bottom: 7px;
  background: #f3f3f3;
  padding: 5px 10px;
  border-radius: 4px;
  display: inline-block;
  width: fit-content;
}

.liste-conges {
  list-style-type: disc;
  margin-left: 20px;
  margin-top: 10px;
}

.liste-conges li {
  font-size: 14px;
  margin-bottom: 5px;
  color: #374151;
  display: flex;
  align-items: center;
  gap: 8px;
}

.liste-conges input[type="checkbox"] {
  margin: 0;
}

.cases-conge label {
  margin-right: 30px;
  font-size: 14px;
}

.table-situation {
  width: 100%;
  border-collapse: collapse;
  margin-top: 7px;
  margin-bottom: 7px;
}

.table-situation td {
  border: 1px solid #ddd;
  padding: 7px 12px;
  font-size: 14px;
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

.signature-droite {
  margin-left: 40px;
  font-weight: 500;
}

.section-approbations-v2 {
  border: 1px solid #bbb;
  margin-top: 20px;
  display: flex;
  flex-direction: column;
}

.approbation-row-v2 {
  display: flex;
  border-bottom: 1px solid #bbb;
}

.approbation-col-v2 {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 15px;
  gap: 10px;
}

.approbation-col-v2:first-child {
  border-right: 1px solid #bbb;
}

.approbation-title-v2 {
  font-weight: 600;
  font-size: 14px;
  text-align: center;
  margin-bottom: 5px;
}

.rh-validation-drh-section-v2 {
  display: flex;
  flex-direction: column;
}

.rh-validation-row-v2 {
  display: flex;
}

.rh-validation-col-v2 {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 15px;
  gap: 10px;
}

.rh-label-v2 {
  font-weight: 600;
  font-size: 14px;
  text-align: center;
  margin-bottom: 5px;
}

.rh-sublabel-v2 {
  font-size: 12px;
  color: #555;
  text-align: center;
  margin-top: 5px;
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
  .fiche-conge {
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

/* Styles pour les nouvelles zones de signature */
.signature-box-empty {
  width: 250px;
  height: 120px;
  border: 1px solid #ccc;
  margin-top: 10px;
  background: #f8f9fa;
}

.signature-line-bottom {
  width: 250px;
  border-bottom: 1px solid #333;
  margin-top: 10px;
  padding-bottom: 5px;
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

.signature-area-drh {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  min-width: 200px;
  position: relative;
  padding-bottom: 20px;
  margin-top: 10px;
}

.drh-approbation-row-v2 {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 15px;
  gap: 10px;
  margin-top: 20px;
  /* Removed border here */
}

.actions-buttons {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
  padding: 1rem;
}

.btn-imprimer,
.btn-soumettre {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
}

.btn-imprimer {
  background-color: #f3f4f6;
  color: #374151;
}

.btn-imprimer:hover {
  background-color: #e5e7eb;
}

.btn-soumettre {
  background-color: #008a9b;
  color: white;
}

.btn-soumettre:hover {
  background-color: #006d7a;
}

@media print {
  .actions-buttons {
    display: none;
  }
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
</style>
