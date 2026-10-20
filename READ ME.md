# PaySimu — Application de Simulation de Paiement

> Application web de simulation de paiement Mobile Money développée en PHP natif,
> sans framework, avec une interface inspirée du design **Claude Code** (Anthropic).

---

## 📋 Table des matières

1. [Présentation](#-présentation)
2. [Fonctionnalités](#-fonctionnalités)
3. [Structure du projet](#-structure-du-projet)
4. [Prérequis](#-prérequis)
5. [Installation](#-installation)
6. [Architecture technique](#-architecture-technique)
7. [Base de données](#-base-de-données)
8. [Flux de l'application](#-flux-de-lapplication)
9. [API & Webhook](#-api--webhook)
10. [Contraintes techniques](#-contraintes-techniques)
11. [Design & Personnalisation](#-design--personnalisation)
12. [Guide de contribution](#-guide-de-contribution)
13. [Dépannage](#-dépannage)
14. [Licence](#-licence)

---

## 🎯 Présentation

**PaySimu** est une application de démonstration qui simule le cycle complet
d'un paiement Mobile Money :

1. Un client passe une commande.
2. La commande est créée avec un statut `PENDING`.
3. L'utilisateur dispose de **3 minutes** pour simuler le paiement.
4. Un webhook traite le paiement et met à jour la base de données.
5. Passé le délai, la commande est automatiquement marquée `FAILED`.

> ⚠️ **Aucune transaction réelle** n'est effectuée. Cette application est
> destinée à la démonstration et à l'apprentissage.

---

## ✨ Fonctionnalités

| Fonctionnalité | Description |
|---|---|
| **Tableau de bord** | Vue d'ensemble des commandes avec statistiques en temps réel |
| **Création de commande** | Formulaire client + commande avec génération automatique de numéro |
| **Simulateur de paiement** | Interface Mobile Money avec compte à rebours de 3 minutes |
| **Webhook de paiement** | Endpoint API avec transaction atomique |
| **Expiration automatique** | Commandes PENDING > 3 min → FAILED |
| **Statistiques** | Répartition des commandes, taux de succès, revenus |
| **Sidebar rétractable** | Navigation latérale avec chevron (desktop) et overlay (mobile) |
| **Responsive** | Adapté mobile, tablette et desktop |

---

## 📂 Structure du projet# PaySimu — Application de Simulation de Paiement

> Application web de simulation de paiement Mobile Money développée en PHP natif,
> sans framework, avec une interface inspirée du design **Claude Code** (Anthropic).

---

## 📋 Table des matières

1. [Présentation](#-présentation)
2. [Fonctionnalités](#-fonctionnalités)
3. [Structure du projet](#-structure-du-projet)
4. [Prérequis](#-prérequis)
5. [Installation](#-installation)
6. [Architecture technique](#-architecture-technique)
7. [Base de données](#-base-de-données)
8. [Flux de l'application](#-flux-de-lapplication)
9. [API & Webhook](#-api--webhook)
10. [Contraintes techniques](#-contraintes-techniques)
11. [Design & Personnalisation](#-design--personnalisation)
12. [Guide de contribution](#-guide-de-contribution)
13. [Dépannage](#-dépannage)
14. [Licence](#-licence)

---

## 🎯 Présentation

**PaySimu** est une application de démonstration qui simule le cycle complet
d'un paiement Mobile Money :

1. Un client passe une commande.
2. La commande est créée avec un statut `PENDING`.
3. L'utilisateur dispose de **3 minutes** pour simuler le paiement.
4. Un webhook traite le paiement et met à jour la base de données.
5. Passé le délai, la commande est automatiquement marquée `FAILED`.

> ⚠️ **Aucune transaction réelle** n'est effectuée. Cette application est
> destinée à la démonstration et à l'apprentissage.

---

## ✨ Fonctionnalités

| Fonctionnalité | Description |
|---|---|
| **Tableau de bord** | Vue d'ensemble des commandes avec statistiques en temps réel |
| **Création de commande** | Formulaire client + commande avec génération automatique de numéro |
| **Simulateur de paiement** | Interface Mobile Money avec compte à rebours de 3 minutes |
| **Webhook de paiement** | Endpoint API avec transaction atomique |
| **Expiration automatique** | Commandes PENDING > 3 min → FAILED |
| **Statistiques** | Répartition des commandes, taux de succès, revenus |
| **Sidebar rétractable** | Navigation latérale avec chevron (desktop) et overlay (mobile) |
| **Responsive** | Adapté mobile, tablette et desktop |

---

## 📂 Structure du projet# PaySimu — Application de Simulation de Paiement

> Application web de simulation de paiement Mobile Money développée en PHP natif,
> sans framework, avec une interface inspirée du design **Claude Code** (Anthropic).

---

## 📋 Table des matières

1. [Présentation](#-présentation)
2. [Fonctionnalités](#-fonctionnalités)
3. [Structure du projet](#-structure-du-projet)
4. [Prérequis](#-prérequis)
5. [Installation](#-installation)
6. [Architecture technique](#-architecture-technique)
7. [Base de données](#-base-de-données)
8. [Flux de l'application](#-flux-de-lapplication)
9. [API & Webhook](#-api--webhook)
10. [Contraintes techniques](#-contraintes-techniques)
11. [Design & Personnalisation](#-design--personnalisation)
12. [Guide de contribution](#-guide-de-contribution)
13. [Dépannage](#-dépannage)
14. [Licence](#-licence)

---

## 🎯 Présentation

**PaySimu** est une application de démonstration qui simule le cycle complet
d'un paiement Mobile Money :

1. Un client passe une commande.
2. La commande est créée avec un statut `PENDING`.
3. L'utilisateur dispose de **3 minutes** pour simuler le paiement.
4. Un webhook traite le paiement et met à jour la base de données.
5. Passé le délai, la commande est automatiquement marquée `FAILED`.

> ⚠️ **Aucune transaction réelle** n'est effectuée. Cette application est
> destinée à la démonstration et à l'apprentissage.

---

## ✨ Fonctionnalités

| Fonctionnalité | Description |
|---|---|
| **Tableau de bord** | Vue d'ensemble des commandes avec statistiques en temps réel |
| **Création de commande** | Formulaire client + commande avec génération automatique de numéro |
| **Simulateur de paiement** | Interface Mobile Money avec compte à rebours de 3 minutes |
| **Webhook de paiement** | Endpoint API avec transaction atomique |
| **Expiration automatique** | Commandes PENDING > 3 min → FAILED |
| **Statistiques** | Répartition des commandes, taux de succès, revenus |
| **Sidebar rétractable** | Navigation latérale avec chevron (desktop) et overlay (mobile) |
| **Responsive** | Adapté mobile, tablette et desktop |

---# PaySimu — Application de Simulation de Paiement

> Application web de simulation de paiement Mobile Money développée en PHP natif,
> sans framework, avec une interface inspirée du design **Claude Code** (Anthropic).

---

## 📋 Table des matières

1. [Présentation](#-présentation)
2. [Fonctionnalités](#-fonctionnalités)
3. [Structure du projet](#-structure-du-projet)
4. [Prérequis](#-prérequis)
5. [Installation](#-installation)
6. [Architecture technique](#-architecture-technique)
7. [Base de données](#-base-de-données)
8. [Flux de l'application](#-flux-de-lapplication)
9. [API & Webhook](#-api--webhook)
10. [Contraintes techniques](#-contraintes-techniques)
11. [Design & Personnalisation](#-design--personnalisation)
12. [Guide de contribution](#-guide-de-contribution)
13. [Dépannage](#-dépannage)
14. [Licence](#-licence)

---

## 🎯 Présentation

**PaySimu** est une application de démonstration qui simule le cycle complet
d'un paiement Mobile Money :

1. Un client passe une commande.
2. La commande est créée avec un statut `PENDING`.
3. L'utilisateur dispose de **3 minutes** pour simuler le paiement.
4. Un webhook traite le paiement et met à jour la base de données.
5. Passé le délai, la commande est automatiquement marquée `FAILED`.

> ⚠️ **Aucune transaction réelle** n'est effectuée. Cette application est
> destinée à la démonstration et à l'apprentissage.

---

## ✨ Fonctionnalités

| Fonctionnalité | Description |
|---|---|
| **Tableau de bord** | Vue d'ensemble des commandes avec statistiques en temps réel |
| **Création de commande** | Formulaire client + commande avec génération automatique de numéro |
| **Simulateur de paiement** | Interface Mobile Money avec compte à rebours de 3 minutes |
| **Webhook de paiement** | Endpoint API avec transaction atomique |
| **Expiration automatique** | Commandes PENDING > 3 min → FAILED |
| **Statistiques** | Répartition des commandes, taux de succès, revenus |
| **Sidebar rétractable** | Navigation latérale avec chevron (desktop) et overlay (mobile) |
| **Responsive** | Adapté mobile, tablette et desktop |

---



## 📂 Structure du projet