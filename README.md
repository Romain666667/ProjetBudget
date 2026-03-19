# 💰 Budget App

Application web de gestion de budget personnel, permettant de suivre
ses dépenses et revenus par catégorie et par personne.

---

## 📋 Fonctionnalités

- Authentification par session avec login/mot de passe hashé (bcrypt)
- Ajout et suppression de dépenses par catégorie et par personne
- Ajout et suppression de revenus
- Définition de budgets mensuels par catégorie avec barre de progression
- Bilan mensuel : revenus − dépenses = solde restant
- Totaux par mois, par catégorie et par personne

---

## 🛠️ Stack technique

| Couche          | Technologie      |
|-----------------|------------------|
| Backend         | PHP 8.x (natif)  |
| Base de données | MySQL + PDO      |
| Frontend        | HTML / CSS natif |
| Serveur         | Apache           |

---

## 🗄️ Base de données

**Nom :** `budgett`

| Table          | Description                                         |
|----------------|-----------------------------------------------------|
| `utilisateurs` | Comptes utilisateurs avec mot de passe hashé        |
| `categories`   | Catégories de dépenses (nom, couleur, icône)        |
| `depenses`     | Dépenses liées à une catégorie via FK               |
| `revenus`      | Entrées d'argent par personne                       |
| `budgets`      | Plafonds mensuels par catégorie et par personne     |

Relations :
- `depenses.categorie_id` → `categories.id`
- `budgets.categorie_id`  → `categories.id`

---

## ⚙️ Installation

### Prérequis
- PHP 8.x
- MySQL
- Apache
