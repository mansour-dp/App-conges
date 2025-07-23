<template>
  <div class="commentaire-validation">
    <div class="commentaire-header">
      <h3>{{ titre }}</h3>
      <p class="subtitle">{{ sousTitre }}</p>
    </div>

    <div class="commentaire-form">
      <div class="form-group">
        <label for="commentaire">Commentaire de validation</label>
        <textarea
          id="commentaire"
          v-model="commentaire"
          rows="4"
          :placeholder="placeholder"
          class="commentaire-textarea"
        ></textarea>
        <div class="char-count">{{ commentaire.length }}/500 caractères</div>
      </div>

      <div class="validation-options" v-if="showOptions">
        <div class="option-group">
          <label class="option-label">Raison du rejet (si applicable)</label>
          <div class="option-buttons">
            <button
              v-for="raison in raisonsRejet"
              :key="raison.value"
              @click="selectRaison(raison.value)"
              :class="[
                'option-btn',
                { active: raisonSelectionnee === raison.value },
              ]"
            >
              {{ raison.label }}
            </button>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button @click="$emit('cancel')" class="btn-cancel">Annuler</button>
        <button
          @click="valider"
          :disabled="!peutValider"
          :class="[
            'btn-confirm',
            action === 'approve' ? 'btn-approve' : 'btn-reject',
          ]"
        >
          <i
            :class="action === 'approve' ? 'fas fa-check' : 'fas fa-times'"
          ></i>
          {{ action === "approve" ? "Approuver" : "Rejeter" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CommentaireValidation",
  props: {
    action: {
      type: String,
      required: true,
      validator: (value) => ["approve", "reject"].includes(value),
    },
    demande: {
      type: Object,
      required: true,
    },
    showOptions: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      commentaire: "",
      raisonSelectionnee: "",
      raisonsRejet: [
        { value: "periode_charge", label: "Période de forte charge" },
        {
          value: "justification_insuffisante",
          label: "Justification insuffisante",
        },
        { value: "solde_insuffisant", label: "Solde de congés insuffisant" },
        { value: "conflit_planning", label: "Conflit avec le planning" },
        { value: "autre", label: "Autre raison" },
      ],
    };
  },
  computed: {
    titre() {
      return this.action === "approve"
        ? "Approuver la demande"
        : "Rejeter la demande";
    },
    sousTitre() {
      return this.action === "approve"
        ? "Confirmez l'approbation de cette demande de congé"
        : "Indiquez les raisons du rejet de cette demande";
    },
    placeholder() {
      return this.action === "approve"
        ? "Ajoutez un commentaire optionnel pour expliquer votre décision..."
        : "Expliquez les raisons du rejet de cette demande...";
    },
    peutValider() {
      if (this.action === "reject") {
        return this.commentaire.trim().length > 0;
      }
      return true; // Pour l'approbation, le commentaire est optionnel
    },
  },
  methods: {
    selectRaison(raison) {
      this.raisonSelectionnee = raison;
      if (raison !== "autre") {
        const raisonLabel = this.raisonsRejet.find(
          (r) => r.value === raison
        )?.label;
        if (raisonLabel && !this.commentaire.includes(raisonLabel)) {
          this.commentaire =
            raisonLabel + (this.commentaire ? "\n" + this.commentaire : "");
        }
      }
    },
    valider() {
      const data = {
        action: this.action,
        commentaire: this.commentaire.trim(),
        raison: this.raisonSelectionnee,
        demandeId: this.demande.id,
      };
      this.$emit("validate", data);
    },
  },
};
</script>

<style scoped>
.commentaire-validation {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.commentaire-header {
  margin-bottom: 25px;
}

.commentaire-header h3 {
  margin: 0 0 8px 0;
  color: #2c3e50;
  font-size: 20px;
}

.subtitle {
  margin: 0;
  color: #7f8c8d;
  font-size: 14px;
}

.commentaire-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 500;
  color: #2c3e50;
  font-size: 14px;
}

.commentaire-textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
  transition: border-color 0.2s ease;
}

.commentaire-textarea:focus {
  outline: none;
  border-color: #008a9b;
  box-shadow: 0 0 0 3px rgba(0, 138, 155, 0.1);
}

.char-count {
  text-align: right;
  font-size: 12px;
  color: #95a5a6;
}

.validation-options {
  border-top: 1px solid #eee;
  padding-top: 20px;
}

.option-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.option-label {
  font-weight: 500;
  color: #2c3e50;
  font-size: 14px;
}

.option-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.option-btn {
  padding: 6px 12px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s ease;
  color: #7f8c8d;
}

.option-btn:hover {
  border-color: #008a9b;
  color: #008a9b;
}

.option-btn.active {
  background: #008a9b;
  color: white;
  border-color: #008a9b;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.btn-cancel {
  padding: 10px 20px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.2s ease;
}

.btn-cancel:hover {
  background: #f8f9fa;
}

.btn-confirm {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
  transition: all 0.2s ease;
}

.btn-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-confirm.btn-approve {
  background: #27ae60;
}

.btn-confirm.btn-approve:hover:not(:disabled) {
  background: #229954;
}

.btn-confirm.btn-reject {
  background: #e74c3c;
}

.btn-confirm.btn-reject:hover:not(:disabled) {
  background: #c0392b;
}

@media (max-width: 768px) {
  .commentaire-validation {
    padding: 20px;
  }

  .option-buttons {
    flex-direction: column;
  }

  .form-actions {
    flex-direction: column;
  }

  .btn-cancel,
  .btn-confirm {
    width: 100%;
    justify-content: center;
  }
}
</style>
