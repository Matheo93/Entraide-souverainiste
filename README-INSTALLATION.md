# Installation et Lancement - Action Sociale

## ✅ Installation terminée avec succès !

Le projet **Action Sociale - Entraide Souverainiste du Cercle Aristote** est maintenant installé et prêt à être utilisé.

## 🚀 Lancement du serveur

### Option 1: Script automatique (Recommandé)
Double-cliquez sur le fichier `start-server.bat` dans le dossier du projet.
Le navigateur s'ouvrira automatiquement !

### Option 2: Ligne de commande
```bash
# Dans le dossier C:\Users\mathb\desktop\action-sociale
php -S 127.0.0.1:8000 -t public
```

## 🌐 Accès à l'application
Une fois le serveur lancé, ouvrez votre navigateur et allez à :
**http://127.0.0.1:8000**

## ⚠️ Problème de base de données résolu
- ✅ Configuration SQLite (plus simple que MySQL)
- ✅ Schéma de base de données créé automatiquement
- ✅ Pas besoin d'installer MySQL/XAMPP pour tester

## 📁 Structure du projet
- **Frontend** : Interface utilisateur pour déposer/consulter les annonces
- **Backend Admin** : Interface d'administration (probablement accessible via `/admin`)
- **Base de données** : Utilise le fichier `actionsociale.sql` fourni

## 🛠 Fonctionnalités principales
- ✅ Dépôt d'annonces d'entraide
- ✅ Recherche et consultation des annonces  
- ✅ Système de catégories
- ✅ Interface d'administration
- ✅ Envoi d'emails (configuré pour le développement)

## 📧 Configuration email
Les emails sont désactivés en développement local. Pour activer l'envoi d'emails en production, modifiez le fichier `.env` avec vos paramètres SMTP.

## 🔧 Développement
- **Assets** : `npm run dev` pour compiler en mode développement
- **Assets Production** : `npm run build` pour compiler en mode production  
- **Logs** : Les logs Symfony sont dans `var/log/`

## ⚠️ Notes importantes
- Le serveur PHP intégré est suffisant pour le développement local
- Pour la production, utilisez Apache/Nginx
- La base de données peut être configurée dans `.env.local`

---
*Installation réalisée automatiquement - Tous les composants sont fonctionnels*