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
              <div
                class="signature-pad"
                @click="ouvrirPadSignature('superieur')"
              >
                <div v-if="formData.signatureSuperieur" class="signature-image">
                  <img :src="formData.signatureSuperieur" alt="Signature" />
                </div>
                <div v-else class="signature-placeholder">
                  <i class="fas fa-upload"></i>
                  <span>Upload Signature</span>
                </div>
              </div>
            </td>
            <td>
              <div
                class="signature-pad"
                @click="ouvrirPadSignature('directeur')"
              >
                <div v-if="formData.signatureDirecteur" class="signature-image">
                  <img :src="formData.signatureDirecteur" alt="Signature" />
                </div>
                <div v-else class="signature-placeholder">
                  <i class="fas fa-upload"></i>
                  <span>Upload Signature</span>
                </div>
              </div>
            </td>
            <td>
              <div
                class="signature-pad"
                @click="ouvrirPadSignature('correspondantRH')"
              >
                <div
                  v-if="formData.signatureCorrespondantRH"
                  class="signature-image"
                >
                  <img
                    :src="formData.signatureCorrespondantRH"
                    alt="Signature"
                  />
                </div>
                <div v-else class="signature-placeholder">
                  <i class="fas fa-upload"></i>
                  <span>Upload Signature</span>
                </div>
              </div>
            </td>
            <td>
              <div
                class="signature-pad"
                @click="ouvrirPadSignature('validationDeptAdmin')"
              >
                <div
                  v-if="formData.signatureValidationDeptAdmin"
                  class="signature-image"
                >
                  <img
                    :src="formData.signatureValidationDeptAdmin"
                    alt="Signature"
                  />
                </div>
                <div v-else class="signature-placeholder">
                  <i class="fas fa-upload"></i>
                  <span>Upload Signature</span>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="drh-decision-absence">
        Décision du Directeur des Ressources Humaines
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
        <sup>1</sup> Dans les Unités opérationnelles et Délégations Régionales
      </div>
      <div class="actions-print">
        <button
          type="button"
          class="btn-envoyer"
          @click="envoyerDemande"
          :disabled="demandeEnvoyee"
        >
          Soumettre
        </button>
      </div>
      <div v-if="confirmation" class="confirmation-message">
        Demande envoyée avec succès !
      </div>
    </form>
    <SignaturePad
      v-if="showSignaturePad"
      @close="showSignaturePad = false"
      @signature-saved="sauvegarderSignature"
    />
  </div>
</template>

<script>
import SignaturePad from "../ui/SignaturePad.vue";

export default {
  name: "DemandeAbsence",
  components: {
    SignaturePad,
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
      this.demandeEnvoyee = true;
      this.confirmation = true;
      setTimeout(() => {
        this.confirmation = false;
      }, 3000);
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
</style>
