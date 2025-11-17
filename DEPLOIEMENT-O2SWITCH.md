# 🚀 Guide de Déploiement sur o2switch - entraidesouverainiste.fr

## 📋 Vue d'ensemble

Ce guide vous accompagne étape par étape pour déployer votre application Symfony 5.2 sur o2switch avec le domaine **entraidesouverainiste.fr**.

---

## ✅ Prérequis o2switch

### Vérifications à faire sur votre compte o2switch

1. **Version PHP** : PHP 7.4 minimum (idéalement 8.0+)
   - Via cPanel → "Sélectionner une version PHP"
   - Activer les extensions : `pdo_mysql`, `intl`, `mbstring`, `xml`, `ctype`, `iconv`, `json`, `tokenizer`

2. **Accès SSH** : Activé
   - Via cPanel → "Accès SSH"
   - Générer une clé SSH si nécessaire

3. **Base de données MySQL**
   - Via cPanel → "Bases de données MySQL"
   - Créer une nouvelle base de données
   - Créer un utilisateur avec tous les privilèges

4. **Node.js/npm** : Pour compiler les assets
   - o2switch fournit Node.js via SSH
   - Vérifier avec : `node -v` et `npm -v`

---

## 🗂️ Étape 1 : Préparer les fichiers localement

### 1.1 Créer le fichier `.env.prod`

Créez un fichier `.env.prod` avec vos paramètres de production :

```bash
###> symfony/framework-bundle ###
APP_ENV=prod
APP_SECRET=GENERER_UNE_NOUVELLE_CLE_SECRETE_ICI
APP_DEBUG=0
###< symfony/framework-bundle ###

###> symfony/mailer ###
APP_EMAIL_ADMIN=contact@entraidesouverainiste.fr
APP_EMAILSMTP_ADMIN=contact@entraidesouverainiste.fr
APP_EMAIL_TEMP=noreply@entraidesouverainiste.fr
APP_PSW_TEMP=VOTRE_MOT_DE_PASSE_EMAIL

APP_EMAIL_NAME="Entraide Souverainiste"
APP_EMAIL_HOSTNAME=ssl0.ovh.net
MAILER_DSN="smtp://$APP_EMAIL_TEMP:$APP_PSW_TEMP@$APP_EMAIL_HOSTNAME"
###< symfony/mailer ###

###> doctrine/doctrine-bundle ###
DATABASE_USER=votre_user_mysql_o2switch
DATABASE_PASSWORD=votre_password_mysql_o2switch
DATABASE_HOST=localhost
DATABASE_DBNAME=votre_nom_bdd_o2switch
DATABASE_DBVERSION=mariadb-10.5
DATABASE_URL="mysql://$DATABASE_USER:$DATABASE_PASSWORD@$DATABASE_HOST:3306/$DATABASE_DBNAME?serverVersion=$DATABASE_DBVERSION"
###< doctrine/doctrine-bundle ###

###> Discord Configuration ###
DISCORD_MODERATION_WEBHOOK_URL="https://discord.com/api/webhooks/VOTRE_WEBHOOK_ID/VOTRE_WEBHOOK_TOKEN"
###< Discord Configuration ###

ADMIN_CONTACT_EMAIL="admin@entraidesouverainiste.fr"
WEBMASTER_CONTACT_EMAIL="webmaster@entraidesouverainiste.fr"
DEV_CONTACT_EMAIL="dev@entraidesouverainiste.fr"
```

**⚠️ Important** :
- Générer une nouvelle `APP_SECRET` : `php bin/console secrets:generate-keys` ou utiliser un générateur en ligne
- Ne JAMAIS commit ce fichier dans Git

### 1.2 Créer `.htaccess` pour Apache

o2switch utilise Apache, il faut créer `/public/.htaccess` :

```apache
# /public/.htaccess

DirectoryIndex index.php

<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On

    # Détermine le chemin de base de l'application
    RewriteCond %{REQUEST_URI}::$0 ^(/.+)/(.*)::\2$
    RewriteRule .* - [E=BASE:%1]

    # Redirection vers index.php si le fichier n'existe pas
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ %{ENV:BASE}index.php [L]
</IfModule>

<IfModule !mod_rewrite.c>
    <IfModule mod_alias.c>
        RedirectMatch 307 ^/$ /index.php/
    </IfModule>
</IfModule>

# Désactiver l'affichage des erreurs en production
php_flag display_errors off
php_flag display_startup_errors off

# Limites de mémoire et temps d'exécution
php_value memory_limit 256M
php_value max_execution_time 300
php_value upload_max_filesize 20M
php_value post_max_size 25M

# Timezone
php_value date.timezone Europe/Paris
```

### 1.3 Créer `.htaccess` racine (sécurité)

Créez `/.htaccess` à la racine du projet :

```apache
# /.htaccess - Sécurité racine

# Interdire l'accès direct aux fichiers sensibles
<FilesMatch "\.(yml|yaml|env|env.local|env.prod|lock|json)$">
    Require all denied
</FilesMatch>

# Protéger les répertoires
RedirectMatch 404 /\.git
RedirectMatch 404 /\.env
RedirectMatch 404 /config
RedirectMatch 404 /src
RedirectMatch 404 /var
RedirectMatch 404 /vendor

# Rediriger tout vers /public si accès racine
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L]
```

### 1.4 Compiler les assets en production

```bash
# Sur votre machine locale
npm install
npm run build

# Cela génère les fichiers optimisés dans /public/build/
```

---

## 🌐 Étape 2 : Configuration DNS du domaine

### 2.1 Chez votre registrar (ex: Gandi, OVH, etc.)

Une fois que vous avez acheté **entraidesouverainiste.fr**, configurez les DNS :

**Type A** :
```
@ (ou vide)    →  IP_O2SWITCH
www            →  IP_O2SWITCH
```

**Type CNAME (optionnel)** :
```
www  →  entraidesouverainiste.fr
```

**MX Records** (pour les emails si hébergés chez o2switch) :
```
@  →  mx1.mail.ovh.net  (Priorité: 10)
@  →  mx2.mail.ovh.net  (Priorité: 20)
```

### 2.2 Dans cPanel o2switch

1. Aller dans **"Domaines"** ou **"Domaines supplémentaires"**
2. Ajouter le domaine : `entraidesouverainiste.fr`
3. Document Root : `/home/VOTRE_USER/entraidesouverainiste.fr/public`
4. Activer SSL/TLS (Let's Encrypt gratuit)

---

## 📤 Étape 3 : Upload des fichiers sur o2switch

### Option A : Via SSH + Git (Recommandé)

```bash
# 1. Se connecter en SSH
ssh VOTRE_USER@VOTRE_DOMAINE.o2switch.net

# 2. Cloner le repository
cd ~/
git clone https://github.com/Matheo93/Entraide-souverainiste.git entraidesouverainiste.fr
cd entraidesouverainiste.fr

# 3. Copier le fichier .env.prod vers .env.local
cp .env.prod .env.local

# 4. Installer les dépendances Composer
composer install --no-dev --optimize-autoloader

# 5. Installer les dépendances Node.js
npm install

# 6. Compiler les assets en production
npm run build

# 7. Permissions
chmod -R 755 var/cache var/log
```

### Option B : Via FileZilla/FTP

1. Connectez-vous via FTP (identifiants cPanel)
2. Uploadez TOUS les fichiers dans `/home/VOTRE_USER/entraidesouverainiste.fr/`
3. **NE PAS UPLOADER** :
   - `/node_modules/` (trop lourd, à regénérer sur le serveur)
   - `/var/cache/` (sera regénéré)
   - `.env.local` (créer manuellement)
   - `/docker-compose.yml` (inutile en prod)
   - `/Dockerfile` (inutile en prod)

---

## 🗄️ Étape 4 : Configuration de la base de données

### 4.1 Créer la base via cPanel

1. cPanel → **"Bases de données MySQL"**
2. Créer une base : `votreuser_entraide`
3. Créer un utilisateur : `votreuser_admin`
4. Mot de passe fort
5. Ajouter l'utilisateur à la base avec **TOUS LES PRIVILÈGES**

### 4.2 Importer la structure

**Option 1 : Via SSH**
```bash
# Exporter depuis local
mysqldump -u actionsociale -p actionsociale > database_export.sql

# Uploader sur le serveur, puis :
mysql -u votreuser_admin -p votreuser_entraide < database_export.sql
```

**Option 2 : Via phpMyAdmin**
1. cPanel → phpMyAdmin
2. Sélectionner votre base `votreuser_entraide`
3. Onglet "Importer"
4. Uploader votre fichier `.sql`

### 4.3 Exécuter les migrations

```bash
# Via SSH
php bin/console doctrine:migrations:migrate --no-interaction
```

---

## 🔧 Étape 5 : Configuration Symfony en production

### 5.1 Vider et optimiser le cache

```bash
# Via SSH
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod
```

### 5.2 Vérifier les permissions

```bash
chmod -R 755 var/
chmod -R 755 public/build/
chmod -R 755 public/uploads/
chown -R VOTRE_USER:VOTRE_USER var/
```

### 5.3 Optimiser l'autoloader

```bash
composer dump-autoload --optimize --no-dev --classmap-authoritative
```

---

## 🤖 Étape 6 : Configuration du Bot Discord en production

### 6.1 Créer `.env.bot.prod`

```bash
# .env.bot.prod
DISCORD_BOT_TOKEN=VOTRE_TOKEN_BOT_DISCORD
SYMFONY_API_URL=https://entraidesouverainiste.fr
```

### 6.2 Installer PM2 pour gérer le bot

```bash
# Via SSH
npm install -g pm2

# Démarrer le bot avec PM2
pm2 start discord-bot.js --name "entraide-bot" --env production

# Sauvegarder la config PM2
pm2 save

# Auto-start au redémarrage serveur
pm2 startup
```

### 6.3 Vérifier que le bot tourne

```bash
pm2 status
pm2 logs entraide-bot
```

**⚠️ Important** : Sur o2switch, si PM2 n'est pas disponible globalement, vous devrez :
- Utiliser `nohup` : `nohup node discord-bot.js > bot.log 2>&1 &`
- Ou créer un cronjob pour vérifier/relancer le bot

---

## 🔒 Étape 7 : Sécurité et SSL

### 7.1 Activer HTTPS (Let's Encrypt)

1. cPanel → **"SSL/TLS Status"**
2. Sélectionner `entraidesouverainiste.fr` et `www.entraidesouverainiste.fr`
3. Cliquer sur **"Run AutoSSL"**
4. Attendre ~2 minutes

### 7.2 Forcer HTTPS via `.htaccess`

Ajouter dans `/public/.htaccess` (en haut) :

```apache
# Forcer HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 7.3 Sécuriser `.env.local`

```bash
chmod 600 .env.local
```

---

## 📧 Étape 8 : Configuration des emails

### 8.1 Créer les comptes emails via cPanel

1. cPanel → **"Comptes de messagerie"**
2. Créer :
   - `contact@entraidesouverainiste.fr`
   - `noreply@entraidesouverainiste.fr`
   - `admin@entraidesouverainiste.fr`

### 8.2 Tester l'envoi d'emails

```bash
php bin/console mailer:test contact@entraidesouverainiste.fr
```

---

## ✅ Étape 9 : Tests post-déploiement

### 9.1 Checklist

- [ ] Site accessible : `https://entraidesouverainiste.fr`
- [ ] HTTPS actif (cadenas vert)
- [ ] Page d'accueil s'affiche correctement
- [ ] CSS/JS chargés (vérifier console F12)
- [ ] Connexion/Inscription fonctionne
- [ ] Création d'annonce fonctionne
- [ ] Chat widget s'affiche
- [ ] Emails envoyés correctement
- [ ] Discord webhook fonctionne
- [ ] Bot Discord répond aux boutons

### 9.2 Commandes de diagnostic

```bash
# Vérifier la config Symfony
php bin/console about

# Vérifier les routes
php bin/console debug:router

# Vérifier la BDD
php bin/console doctrine:schema:validate

# Logs d'erreurs
tail -f var/log/prod.log
```

---

## 🔄 Étape 10 : Mises à jour futures

### 10.1 Script de déploiement automatique

Créez `/deploy.sh` :

```bash
#!/bin/bash

echo "🚀 Déploiement Entraide Souverainiste"

# 1. Pull dernières modifications
git pull origin master

# 2. Installer dépendances
composer install --no-dev --optimize-autoloader

# 3. Migrations BDD
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Rebuild assets
npm install
npm run build

# 5. Vider cache
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod

# 6. Redémarrer bot Discord
pm2 restart entraide-bot

echo "✅ Déploiement terminé !"
```

Rendre exécutable :
```bash
chmod +x deploy.sh
```

Utiliser :
```bash
./deploy.sh
```

---

## 📊 Monitoring et Logs

### Logs Symfony

```bash
# Erreurs production
tail -f var/log/prod.log

# Voir les 100 dernières lignes
tail -100 var/log/prod.log
```

### Logs Apache

```bash
# Via cPanel → "Erreurs"
# Ou via SSH :
tail -f ~/logs/DOMAINE/error.log
```

### Logs Discord Bot

```bash
pm2 logs entraide-bot
```

---

## 🆘 Troubleshooting

### Erreur 500 - Internal Server Error

**Causes possibles** :
1. `.htaccess` mal configuré
2. Permissions incorrectes sur `/var`
3. `APP_ENV=prod` manquant dans `.env.local`
4. Cache corrompu

**Solutions** :
```bash
# Vérifier les logs
tail -50 var/log/prod.log

# Refaire le cache
rm -rf var/cache/*
php bin/console cache:clear --env=prod

# Vérifier les permissions
chmod -R 755 var/
```

### CSS/JS ne se charge pas

**Causes** :
1. Assets non compilés
2. Mauvais chemin dans `webpack.config.js`
3. `.htaccess` bloque `/build/`

**Solutions** :
```bash
# Recompiler
npm run build

# Vérifier les permissions
chmod -R 755 public/build/
```

### BDD "Connection refused"

**Causes** :
1. Mauvais identifiants dans `.env.local`
2. Base non créée
3. Utilisateur n'a pas les droits

**Solutions** :
```bash
# Tester la connexion MySQL
mysql -u votreuser_admin -p votreuser_entraide

# Vérifier DATABASE_URL dans .env.local
```

### Bot Discord offline

**Causes** :
1. Token invalide
2. PM2 pas démarré
3. Mauvaise URL API

**Solutions** :
```bash
# Vérifier PM2
pm2 status

# Voir les logs
pm2 logs entraide-bot

# Redémarrer
pm2 restart entraide-bot
```

---

## 📞 Support o2switch

- **Assistance** : https://www.o2switch.fr/assistance
- **Tutoriels** : https://faq.o2switch.fr/
- **Contact** : contact@o2switch.fr
- **Téléphone** : +33 4 44 44 60 40

---

## 🎯 Checklist Finale

Avant de mettre en ligne :

- [ ] Domaine acheté : `entraidesouverainiste.fr`
- [ ] DNS configurés (A + CNAME)
- [ ] Fichiers uploadés sur o2switch
- [ ] `.env.local` créé avec bonnes valeurs PROD
- [ ] Base de données importée
- [ ] Migrations exécutées
- [ ] Assets compilés (`npm run build`)
- [ ] Cache vidé et warmed up
- [ ] HTTPS activé (Let's Encrypt)
- [ ] Emails configurés
- [ ] Discord webhook configuré
- [ ] Bot Discord démarré avec PM2
- [ ] Tests réalisés (connexion, annonce, chat, Discord)
- [ ] Logs vérifiés (pas d'erreurs)
- [ ] Backup BDD créé

---

**Date de création** : 2025-11-17
**Version Symfony** : 5.2
**Hébergeur** : o2switch
**Domaine** : entraidesouverainiste.fr
**Repository** : https://github.com/Matheo93/Entraide-souverainiste

🎉 **Bon déploiement !**
