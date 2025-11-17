# 🚀 Guide de Déploiement Complet - Entraide Souverainiste

## 📋 Vue d'ensemble

Ce document centralise toutes les informations nécessaires pour déployer **Entraide Souverainiste** sur o2switch avec le domaine **entraidesouverainiste.fr**.

---

## 📚 Documentation disponible

| Fichier | Description |
|---------|-------------|
| `DEPLOIEMENT-O2SWITCH.md` | Guide complet de déploiement sur o2switch |
| `DNS-CONFIGURATION.md` | Configuration DNS et domaine |
| `MIGRATION-DATABASE.md` | Migration de la base de données |
| `DISCORD-BOT-PRODUCTION.md` | Configuration du bot Discord en production |
| `INFOS-O2SWITCH.md` | ⚠️ **CONFIDENTIEL** - Identifiants o2switch |
| `AVANT-VS-APRES.md` | Comparaison ancien/nouveau système |
| `README-SYSTEME-COMPLET.md` | Documentation technique complète |

---

## ⚡ Démarrage Rapide

### Prérequis

- [ ] Compte o2switch actif
- [ ] Domaine `entraidesouverainiste.fr` acheté
- [ ] Accès SSH activé sur o2switch
- [ ] Code source sur GitHub : `https://github.com/Matheo93/Entraide-souverainiste`

### Étapes de déploiement (résumé)

```bash
# 1. Configurer le domaine DNS (chez votre registrar)
Nameserver 1: ns1.o2switch.net
Nameserver 2: ns2.o2switch.net

# 2. Se connecter en SSH
ssh roju9688@kitty.o2switch.net

# 3. Cloner le repository
git clone https://github.com/Matheo93/Entraide-souverainiste.git entraidesouverainiste.fr
cd entraidesouverainiste.fr

# 4. Configurer l'environnement
cp .env.prod .env.local
nano .env.local  # Remplir avec les vrais identifiants

# 5. Installer les dépendances
composer install --no-dev --optimize-autoloader
npm install --legacy-peer-deps
npm run build

# 6. Créer la base de données (via cPanel)
# Base: roju9688_entraide_souverainiste
# User: roju9688_entraide_admin

# 7. Importer les données
mysql -u roju9688_entraide_admin -p roju9688_entraide_souverainiste < backup.sql

# 8. Configurer le domaine (cPanel → Domaines)
# Document root: /home/roju9688/entraidesouverainiste.fr/public

# 9. Activer SSL/TLS (cPanel → SSL/TLS Status)

# 10. Déployer le bot Discord
cp .env.bot.prod .env.bot.local
nano .env.bot.local
npm install -g pm2
pm2 start ecosystem.config.js
pm2 save
pm2 startup

# 11. Vider le cache Symfony
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# 12. Tester le site
curl https://entraidesouverainiste.fr
```

---

## 🔑 Informations du compte o2switch

**⚠️ Voir le fichier `INFOS-O2SWITCH.md` pour les identifiants complets**

```
Serveur: kitty.o2switch.net
Utilisateur: roju9688
cPanel: https://kitty.o2switch.net:2083
```

---

## 🗄️ Base de données

### Nom suggéré

```
Base: roju9688_entraide_souverainiste
User: roju9688_entraide_admin
Password: [À générer dans cPanel]
Host: localhost
Port: 3306
```

### Création via cPanel

1. cPanel → "Bases de données MySQL"
2. Créer la base
3. Créer l'utilisateur
4. Ajouter l'utilisateur à la base avec TOUS LES PRIVILÈGES

---

## 📧 Emails à créer

Via cPanel → "Comptes de messagerie" :

```
contact@entraidesouverainiste.fr
noreply@entraidesouverainiste.fr
admin@entraidesouverainiste.fr
webmaster@entraidesouverainiste.fr
```

---

## 🌐 Configuration DNS

### Option 1 : Utiliser les nameservers o2switch (Recommandé)

Chez votre registrar :

```
ns1.o2switch.net (109.234.160.5)
ns2.o2switch.net (109.234.161.5)
```

### Option 2 : Gérer les DNS vous-même

```
Type A : @ → [IP de kitty.o2switch.net]
Type A : www → [IP de kitty.o2switch.net]
Type MX : @ → mail.entraidesouverainiste.fr (Priorité: 10)
```

---

## 🤖 Bot Discord

### Configuration

```bash
cd ~/entraidesouverainiste.fr
cp .env.bot.prod .env.bot.local
nano .env.bot.local
```

Remplir :

```
DISCORD_BOT_TOKEN=VOTRE_TOKEN_DISCORD
SYMFONY_API_URL=https://entraidesouverainiste.fr
```

### Démarrage avec PM2

```bash
npm install -g pm2
pm2 start ecosystem.config.js
pm2 save
pm2 startup
# Copier/coller la commande affichée
```

### Commandes utiles

```bash
pm2 status                # Statut
pm2 logs entraide-bot     # Logs
pm2 restart entraide-bot  # Redémarrer
pm2 stop entraide-bot     # Arrêter
```

---

## 🔄 Script de déploiement automatique

Pour les mises à jour futures :

```bash
cd ~/entraidesouverainiste.fr
chmod +x deploy.sh
./deploy.sh
```

Le script effectue automatiquement :

1. ✅ Pull Git
2. ✅ Installation dépendances Composer
3. ✅ Installation dépendances npm
4. ✅ Build des assets
5. ✅ Migrations BDD
6. ✅ Optimisation autoloader
7. ✅ Vidage cache Symfony
8. ✅ Configuration permissions
9. ✅ Redémarrage bot Discord

---

## ✅ Checklist de déploiement

### Préparation

- [ ] Compte o2switch actif
- [ ] Domaine acheté
- [ ] DNS configurés
- [ ] Accès SSH activé

### Configuration o2switch

- [ ] Base de données créée
- [ ] Utilisateur MySQL créé
- [ ] Comptes email créés
- [ ] Domaine ajouté dans cPanel
- [ ] SSL/TLS activé (Let's Encrypt)

### Déploiement code

- [ ] Repository cloné
- [ ] `.env.local` configuré
- [ ] Dépendances Composer installées
- [ ] Dépendances npm installées
- [ ] Assets compilés
- [ ] Base de données importée
- [ ] Migrations exécutées
- [ ] Cache vidé et préchauffé

### Bot Discord

- [ ] `.env.bot.local` configuré
- [ ] PM2 installé
- [ ] Bot démarré
- [ ] Auto-restart configuré
- [ ] Logs vérifiés

### Tests

- [ ] Site accessible en HTTPS
- [ ] Page d'accueil affichée
- [ ] CSS/JS chargés
- [ ] Connexion/inscription fonctionne
- [ ] Création d'annonce fonctionne
- [ ] Chat widget visible
- [ ] Emails envoyés
- [ ] Discord webhook fonctionne
- [ ] Bot Discord répond aux boutons

---

## 🆘 En cas de problème

### Site inaccessible

1. Vérifier DNS : `dig entraidesouverainiste.fr`
2. Vérifier document root dans cPanel
3. Vérifier logs Apache : `tail -f ~/logs/entraidesouverainiste.fr/error_log`

### Erreur 500

1. Vérifier logs Symfony : `tail -f var/log/prod.log`
2. Vérifier permissions : `chmod -R 755 var/`
3. Vider cache : `php bin/console cache:clear --env=prod`

### Base de données inaccessible

1. Vérifier identifiants dans `.env.local`
2. Tester connexion : `mysql -u roju9688_entraide_admin -p`
3. Vérifier DATABASE_URL dans `.env.local`

### Bot Discord offline

1. Vérifier PM2 : `pm2 status`
2. Voir logs : `pm2 logs entraide-bot`
3. Redémarrer : `pm2 restart entraide-bot`

---

## 📞 Support

### o2switch

```
Email: support@o2switch.fr
Téléphone: 04 44 44 60 40
Ticket: https://clients.o2switch.fr
Documentation: https://faq.o2switch.fr
```

### Ressources utiles

- Documentation Symfony : https://symfony.com/doc
- Discord.js Guide : https://discordjs.guide
- o2switch FAQ : https://faq.o2switch.fr
- GitHub Repository : https://github.com/Matheo93/Entraide-souverainiste

---

## 🎯 Prochaines étapes après déploiement

1. **Tester toutes les fonctionnalités**
2. **Configurer les sauvegardes automatiques**
3. **Mettre en place le monitoring**
4. **Optimiser les performances** (cache, CDN)
5. **Documenter les procédures de maintenance**

---

## 📊 Commandes de diagnostic

```bash
# Version PHP
php -v

# Version Composer
composer --version

# Version Node.js
node -v

# Version npm
npm -v

# Espace disque
du -sh ~/entraidesouverainiste.fr
quota -s

# Processus en cours
ps aux | grep php
ps aux | grep node

# Logs en temps réel
tail -f var/log/prod.log
pm2 logs entraide-bot
```

---

**Date de création** : 2025-11-17
**Version** : 1.0
**Projet** : Entraide Souverainiste
**Domaine** : entraidesouverainiste.fr
**Hébergeur** : o2switch (kitty.o2switch.net)
**Repository** : https://github.com/Matheo93/Entraide-souverainiste

🎉 **Prêt pour le déploiement !**
