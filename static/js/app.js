/* Fonctions partagées par toutes les pages de l'interface. */

const API = "/api/v1";

const STATUTS = {
  PENDING: { libelle: "En attente", classe: "badge-attente" },
  PAID: { libelle: "Payée", classe: "badge-payee" },
  FAILED: { libelle: "Échouée", classe: "badge-echec" },
};

/** Appel à l'API. Lève une erreur lisible si la réponse n'est pas un succès. */
async function api(chemin, options = {}) {
  const reponse = await fetch(API + chemin, options);
  const corps = await reponse.json().catch(() => ({}));
  if (!reponse.ok) {
    const message = typeof corps.detail === "string" ? corps.detail : `Erreur ${reponse.status}`;
    throw new Error(message);
  }
  return corps;
}

/** 15000 -> "15 000 FCFA" */
function formatMontant(montant) {
  return new Intl.NumberFormat("fr-FR").format(montant) + " FCFA";
}

/** SQLite renvoie une date UTC sans fuseau ("2026-10-21 10:30:00"). */
function formatDate(texte) {
  if (!texte) return "—";
  const date = new Date(texte.replace(" ", "T") + "Z");
  return date.toLocaleString("fr-FR", { dateStyle: "short", timeStyle: "short" });
}

function badge(statut) {
  const info = STATUTS[statut] || { libelle: statut, classe: "badge-neutre" };
  return `<span class="badge ${info.classe}">${info.libelle}</span>`;
}

/** Protège l'affichage contre l'injection HTML. */
function echapper(texte) {
  const div = document.createElement("div");
  div.textContent = texte ?? "";
  return div.innerHTML;
}

/** Met en évidence le lien de la page courante dans la navigation. */
function marquerLienActif() {
  const chemin = location.pathname;
  document.querySelectorAll(".nav-liens a").forEach((lien) => {
    const cible = lien.getAttribute("href");
    const actif = cible === "/" ? chemin === "/" : chemin.startsWith(cible);
    lien.classList.toggle("actif", actif);
  });
}

document.addEventListener("DOMContentLoaded", marquerLienActif);
