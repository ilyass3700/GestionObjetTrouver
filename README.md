# Système de Gestion des Objets Trouvés - Aéroport

## Description
Système complet de gestion des objets trouvés dans un aéroport avec authentification multi-utilisateurs et interfaces différenciées selon les rôles.

## Technologies Utilisées
- **Frontend :** HTML5, CSS3, JavaScript (Vanilla)
- **Backend :** PHP 7.4+
- **Base de données :** MySQL 5.7+
- **Environnement :** XAMPP (Apache + MySQL + PHP)

## Installation sur XAMPP

### Prérequis
- XAMPP installé et fonctionnel
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur

### Étapes d'installation

1. **Démarrer XAMPP**
   - Lancer XAMPP Control Panel
   - Démarrer Apache et MySQL

2. **Copier les fichiers**
   - Copier tous les fichiers du projet dans `C:\xampp\htdocs\airport-lost-found\`

3. **Créer la base de données**
   - Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
   - Importer le fichier `database/schema.sql`
   - Ou exécuter les commandes SQL manuellement

4. **Configuration**
   - Vérifier les paramètres dans `includes/config.php`
   - Par défaut : host=localhost, user=root, password=vide

5. **Créer le dossier uploads**
   - Créer le dossier `uploads/` à la racine du projet
   - Donner les permissions d'écriture (755)

6. **Accéder à l'application**
   - Ouvrir : `http://localhost/airport-lost-found/`

## Comptes Utilisateurs

### Comptes par défaut
- **Responsable :** 
  - Username: `responsable`
  - Password: `responsable`
  
- **Directeur :**
  - Username: `directeur`
  - Password: `directeur`

### Inscription Passagers
Les passagers peuvent s'inscrire librement via la page d'inscription.

## Fonctionnalités par Rôle

### 👤 Passager
- ✅ Inscription et connexion
- ✅ Consultation des objets trouvés (lecture seule)
- ✅ Signalement d'objets perdus
- ✅ Suivi de ses signalements

### 👨‍💼 Responsable
- ✅ Gestion complète des objets trouvés (CRUD)
- ✅ Upload et gestion des photos
- ✅ Marquage des objets comme restitués
- ✅ Consultation des signalements de perte
- ✅ Statistiques de base

### 👔 Directeur
- ✅ Toutes les fonctionnalités du responsable
- ✅ Tableau de bord avec statistiques avancées
- ✅ Export des données (CSV)
- ✅ Vue d'ensemble de l'activité

## Structure du Projet

```
airport-lost-found/
├── assets/
│   ├── css/
│   │   ├── common.css      # Styles communs
│   │   ├── login.css       # Styles connexion
│   │   ├── passenger.css   # Styles passager
│   │   ├── manager.css     # Styles responsable
│   │   └── director.css    # Styles directeur
│   └── js/
│       └── common.js       # Scripts JavaScript
├── database/
│   └── schema.sql          # Structure de la BDD
├── includes/
│   ├── config.php          # Configuration BDD
│   └── functions.php       # Fonctions utilitaires
├── passenger/              # Interface passager
├── manager/                # Interface responsable
├── director/               # Interface directeur
├── uploads/                # Dossier des images
├── index.php               # Page d'accueil
├── login.php               # Connexion
├── register.php            # Inscription
└── logout.php              # Déconnexion
```

## Sécurité

### Mesures implémentées
- ✅ Requêtes préparées (protection injection SQL)
- ✅ Validation et nettoyage des données d'entrée
- ✅ Gestion des sessions PHP
- ✅ Contrôle d'accès par rôle
- ✅ Validation des uploads d'images

### Notes importantes
- Les mots de passe sont stockés en clair (selon spécifications)
- Validation des types de fichiers pour les uploads
- Limitation de la taille des fichiers (5MB max)

## Base de Données

### Tables principales

#### `users`
- `id` : Identifiant unique
- `username` : Nom d'utilisateur
- `password` : Mot de passe (en clair)
- `role` : Rôle (passager, responsable, directeur)
- `email` : Adresse email
- `created_at` : Date de création

#### `objects`
- `id` : Identifiant unique
- `description` : Description de l'objet
- `date_found` : Date de découverte
- `lieu` : Lieu de découverte
- `type` : Type d'objet
- `photo_path` : Chemin vers la photo
- `status` : Statut (trouve, restitue, archive)
- `created_by` : Utilisateur créateur
- `created_at` : Date de création

#### `lost_reports`
- `id` : Identifiant unique
- `description` : Description de l'objet perdu
- `date_lost` : Date de perte
- `lieu` : Lieu de perte
- `type` : Type d'objet
- `contact_info` : Informations de contact
- `passenger_id` : ID du passager
- `status` : Statut du signalement

## Dépannage

### Problèmes courants

1. **Erreur de connexion à la base de données**
   - Vérifier que MySQL est démarré dans XAMPP
   - Contrôler les paramètres dans `config.php`

2. **Images ne s'affichent pas**
   - Vérifier que le dossier `uploads/` existe
   - Contrôler les permissions du dossier

3. **Page blanche**
   - Activer l'affichage des erreurs PHP
   - Vérifier les logs d'erreur Apache

4. **Problème de sessions**
   - Vérifier que `session_start()` est appelé
   - Contrôler les paramètres de session PHP

## Extensions Possibles

- 🔄 Système de notifications par email
- 📊 Graphiques avancés pour les statistiques
- 🔍 Recherche avancée avec filtres multiples
- 📱 Interface responsive mobile
- 🔐 Chiffrement des mots de passe
- 📄 Export PDF des rapports
- 🏷️ Système de tags pour les objets

## Support

Pour toute question ou problème :
1. Vérifier les logs d'erreur Apache/PHP
2. Contrôler la configuration de la base de données
3. S'assurer que tous les fichiers sont présents
4. Vérifier les permissions des dossiers

## Licence

Projet éducatif - Libre d'utilisation et de modification.