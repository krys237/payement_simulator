# TP 3 : Récepteur de webhook de paiement Mobile Money

Objectif

Recevoir la notification de l'opérateur Mobile Money indiquant le résultat d'un paiement, vérifier qu'elle est authentique, puis mettre à jour le statut de la commande dans la base de données de façon sûre.

Les acteurs

- **Notre application** : une API FastAPI qui reçoit les notifications (`main.py`).
- **L'opérateur Mobile Money** : simulé par `client_test.py`. Il envoie le résultat du paiement.
- **La base de données** : SQLite (`shop.db`), qui contient les commandes.

Les fichiers

| Fichier            | Rôle                                                                   |
|                    |
| `requirements.txt` | Liste des bibliothèques à installer (FastAPI, Uvicorn, HTTPX).         |
| `init_db.py`       | Crée la table `orders` et deux commandes de test (CMD-001 et CMD-002). |
| `main.py`          | Contient le webhook et la vérification de la signature.                |
| `client_test.py`   | Simule l'opérateur et envoie quatre cas de test.                       |

Déroulement d'un callback

1. L'opérateur envoie un `POST` sur `/api/v1/payment/callback` avec le corps JSON et l'en-tête `X-Signature`.
2. L'application lit le **corps brut**, c'est-à-dire les octets exacts reçus.
3. Elle calcule une empreinte **HMAC-SHA256** de ce corps avec le secret partagé, puis la compare à `X-Signature`.
4. Si la signature ne correspond pas, la requête est rejetée avec un code **401**. Le contenu n'est jamais utilisé.
5. Si la signature est valide, le JSON est validé. S'il est mal formé, la réponse est **422**.
6. L'application exécute une seule requête SQL conditionnelle : `UPDATE ... WHERE id = ? AND status = 'PENDING' AND amount = ?`.
7. Selon le résultat, la réponse est :
   - **200** avec le statut `PAID` ou `FAILED` si la commande est mise à jour ;
   - **200** avec « Callback déjà traité » si la commande était déjà traitée (idempotence) ;
   - **404** si la commande n'existe pas ;
   - **409** si le montant ne correspond pas.

## Pourquoi le système est sûr

- **Authenticité** : seul celui qui connaît le secret peut produire une signature valide.
- **Intégrité** : toute modification du corps, même d'un seul caractère, invalide la signature.
- **Comparaison en temps constant** (`hmac.compare_digest`) : empêche les attaques par analyse du temps de réponse.
- **Atomicité** : la clause `WHERE status = 'PENDING'` garantit qu'une commande n'est payée qu'une seule fois, même si deux callbacks arrivent en même temps.
- **Idempotence** : un callback rejoué ne modifie rien et reçoit une réponse claire, ce qui évite les relances infinies de l'opérateur.

Résultats attendus lors de la démonstration

| Cas                 | Résultat                              |
|---|---              |
| Callback valide     | 200, commande CMD-001 passée à `PAID` |
| Rejeu               | 200, « Callback déjà traité »         |
| Signature falsifiée | 401, « Signature invalide »           |
| Montant falsifié    | 409, « Montant incohérent »           |

## Limites et améliorations possibles

Ce projet est une simulation pédagogique. En production, il faudrait :

- utiliser **HTTPS** pour chiffrer les échanges ;
- stocker le secret dans un coffre-fort ou une variable d'environnement, jamais dans le code ;
- utiliser une base **PostgreSQL** ou **MySQL** pour la charge et la concurrence réelle ;
- ajouter un **horodatage** dans la signature pour empêcher le rejeu d'anciens callbacks ;
- journaliser chaque callback reçu pour l'audit ;
- adapter le format exact des en-têtes et du corps à la documentation de l'opérateur réel.

## Lancement rapide

```bash
pip install -r requirements.txt
python init_db.py                  # Pour initialiser la base de donnee
python -m uvicorn main:app --reload --port 8001           # dans un terminal
python client_test.py              # dans un second terminal
```

Documentation interactive de l'API : `http://127.0.0.1:8001/docs`