# 🤖 Configuration Discord Bot en Production

## 📋 Vue d'ensemble

Ce guide explique comment configurer et maintenir le bot Discord de modération sur le serveur o2switch en production.

---

## 🎯 Prérequis

### Sur Discord

1. Bot créé et configuré (voir `DISCORD-BOT-SETUP.md`)
2. Bot invité sur votre serveur Discord
3. Token du bot récupéré
4. Webhook de modération configuré

### Sur o2switch

1. Accès SSH activé
2. Node.js installé (vérifier : `node -v`)
3. npm installé (vérifier : `npm -v`)
4. PM2 ou alternative pour gérer le processus

---

## 🚀 Installation en Production

### Étape 1 : Configuration de l'environnement

```bash
# Via SSH sur o2switch
ssh VOTRE_USER@VOTRE_DOMAINE.o2switch.net

cd ~/entraidesouverainiste.fr
```

### Étape 2 : Créer le fichier `.env.bot.local`

```bash
# Copier le template
cp .env.bot.prod .env.bot.local

# Éditer avec nano ou vim
nano .env.bot.local
```

**Contenu** :

```bash
# Discord Bot Configuration - PRODUCTION
DISCORD_BOT_TOKEN=VOTRE_TOKEN_BOT_DISCORD_REEL
SYMFONY_API_URL=https://entraidesouverainiste.fr
```

**Sécuriser le fichier** :

```bash
chmod 600 .env.bot.local
```

### Étape 3 : Installer les dépendances

```bash
# Installer les packages Node.js
npm install discord.js axios --legacy-peer-deps
```

### Étape 4 : Tester le bot manuellement

```bash
# Charger les variables d'environnement et lancer
export $(cat .env.bot.local | xargs) && node discord-bot.js
```

**Résultat attendu** :

```
🚀 Starting Discord moderation bot...
✅ Discord bot ready!
📡 Logged in as Entraide souverainiste#4993
🔗 Connected to Symfony API: https://entraidesouverainiste.fr
```

**Arrêter avec** : `Ctrl+C`

---

## 🔧 Méthodes de Déploiement

### Option 1 : PM2 (Recommandé)

PM2 est un gestionnaire de processus pour Node.js qui redémarre automatiquement le bot en cas de crash.

#### Installation de PM2

```bash
# Installer PM2 globalement
npm install -g pm2

# Vérifier l'installation
pm2 --version
```

#### Créer un fichier de configuration PM2

Créez `ecosystem.config.js` :

```javascript
module.exports = {
  apps: [{
    name: 'entraide-bot',
    script: './discord-bot.js',
    cwd: '/home/VOTRE_USER/entraidesouverainiste.fr',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '200M',
    env_file: '.env.bot.local',
    error_file: './logs/bot-error.log',
    out_file: './logs/bot-output.log',
    log_file: './logs/bot-combined.log',
    time: true,
    env: {
      NODE_ENV: 'production'
    }
  }]
};
```

#### Démarrer le bot avec PM2

```bash
# Créer le dossier logs
mkdir -p logs

# Démarrer le bot
pm2 start ecosystem.config.js

# Vérifier le statut
pm2 status

# Voir les logs en temps réel
pm2 logs entraide-bot

# Sauvegarder la config PM2
pm2 save

# Auto-start au redémarrage serveur
pm2 startup
# Copier/coller la commande affichée et l'exécuter
```

#### Commandes PM2 utiles

```bash
# Statut du bot
pm2 status

# Logs en temps réel
pm2 logs entraide-bot

# Redémarrer
pm2 restart entraide-bot

# Arrêter
pm2 stop entraide-bot

# Supprimer
pm2 delete entraide-bot

# Informations détaillées
pm2 info entraide-bot

# Monitoring CPU/RAM
pm2 monit
```

---

### Option 2 : nohup (Alternative si PM2 indisponible)

```bash
# Charger les variables d'environnement et lancer en arrière-plan
nohup node discord-bot.js > logs/bot.log 2>&1 &

# Récupérer le PID
echo $! > bot.pid

# Voir les logs
tail -f logs/bot.log

# Arrêter le bot
kill $(cat bot.pid)
```

**Script de démarrage** (`start-bot.sh`) :

```bash
#!/bin/bash

BOT_DIR="/home/VOTRE_USER/entraidesouverainiste.fr"
PID_FILE="$BOT_DIR/bot.pid"
LOG_FILE="$BOT_DIR/logs/bot.log"

cd $BOT_DIR

# Charger les variables d'environnement
export $(cat .env.bot.local | xargs)

# Démarrer le bot
nohup node discord-bot.js > $LOG_FILE 2>&1 &

# Sauvegarder le PID
echo $! > $PID_FILE

echo "Bot démarré avec PID: $(cat $PID_FILE)"
```

**Script d'arrêt** (`stop-bot.sh`) :

```bash
#!/bin/bash

PID_FILE="/home/VOTRE_USER/entraidesouverainiste.fr/bot.pid"

if [ -f "$PID_FILE" ]; then
    kill $(cat $PID_FILE)
    rm $PID_FILE
    echo "Bot arrêté"
else
    echo "Fichier PID introuvable"
fi
```

**Rendre exécutables** :

```bash
chmod +x start-bot.sh stop-bot.sh
```

---

### Option 3 : Cron (Vérification périodique)

Si PM2 n'est pas disponible, utilisez cron pour vérifier que le bot tourne :

**Script de vérification** (`check-bot.sh`) :

```bash
#!/bin/bash

BOT_DIR="/home/VOTRE_USER/entraidesouverainiste.fr"
PID_FILE="$BOT_DIR/bot.pid"

# Vérifier si le bot tourne
if [ -f "$PID_FILE" ]; then
    PID=$(cat $PID_FILE)
    if ! ps -p $PID > /dev/null; then
        echo "Bot down, restarting..."
        cd $BOT_DIR
        ./start-bot.sh
    fi
else
    echo "PID file missing, starting bot..."
    cd $BOT_DIR
    ./start-bot.sh
fi
```

**Ajouter au crontab** :

```bash
crontab -e

# Vérifier toutes les 5 minutes
*/5 * * * * /home/VOTRE_USER/entraidesouverainiste.fr/check-bot.sh >> /home/VOTRE_USER/entraidesouverainiste.fr/logs/cron.log 2>&1
```

---

## 🔒 Sécurité

### Permissions

```bash
# Fichier de config
chmod 600 .env.bot.local

# Scripts
chmod 700 start-bot.sh stop-bot.sh check-bot.sh

# Logs
chmod 755 logs/
```

### Variables d'environnement

**JAMAIS** :
- Commit `.env.bot.local` dans Git
- Partager le token Discord publiquement
- Logger le token dans les fichiers de log

**Toujours** :
- Utiliser `.env.bot.local` (ignoré par Git)
- Garder le token secret
- Limiter les permissions du fichier `.env.bot.local`

---

## 📊 Monitoring et Logs

### Voir les logs en temps réel

**Avec PM2** :

```bash
pm2 logs entraide-bot --lines 100
```

**Avec nohup** :

```bash
tail -f logs/bot.log
```

### Logs Discord

Le bot log automatiquement :

```javascript
console.log('✅ Discord bot ready!');
console.log(`📡 Logged in as ${client.user.tag}`);
console.error('❌ Error:', error);
```

### Logs Symfony API

Vérifier que le bot peut communiquer avec l'API :

```bash
tail -f var/log/prod.log | grep -i moderation
```

---

## 🆘 Troubleshooting

### Bot offline

**Vérifier** :

```bash
# Avec PM2
pm2 status

# Avec nohup
ps aux | grep discord-bot.js
```

**Redémarrer** :

```bash
# PM2
pm2 restart entraide-bot

# nohup
./stop-bot.sh && ./start-bot.sh
```

### Erreur "Invalid token"

**Cause** : Token Discord invalide ou mal copié

**Solution** :

1. Aller sur https://discord.com/developers/applications
2. Sélectionner votre bot
3. Onglet "Bot" → "Reset Token"
4. Copier le nouveau token
5. Mettre à jour `.env.bot.local`
6. Redémarrer le bot

### Erreur "API not reachable"

**Cause** : Bot ne peut pas joindre l'API Symfony

**Vérifier** :

```bash
# Tester l'API
curl https://entraidesouverainiste.fr/api/moderation/test

# Vérifier SYMFONY_API_URL
cat .env.bot.local | grep SYMFONY_API_URL
```

**Solution** :

- Vérifier que l'URL est correcte (HTTPS, pas de trailing slash)
- Vérifier que l'API est accessible
- Vérifier les logs Symfony

### Bot ne répond pas aux boutons

**Causes possibles** :

1. Intents non activés sur Discord
2. Bot pas invité avec les bonnes permissions
3. Erreur dans le code de gestion des interactions

**Solution** :

1. Discord Developers → "Bot" → Vérifier "MESSAGE CONTENT INTENT"
2. Ré-inviter le bot avec le bon lien OAuth2
3. Vérifier les logs : `pm2 logs entraide-bot`

---

## 🔄 Mises à jour

### Mettre à jour le code du bot

```bash
# Via SSH
cd ~/entraidesouverainiste.fr

# Pull dernières modifications
git pull

# Redémarrer le bot
pm2 restart entraide-bot
```

### Mettre à jour discord.js

```bash
npm update discord.js --legacy-peer-deps
pm2 restart entraide-bot
```

---

## 📈 Performance

### Utilisation mémoire

```bash
# Avec PM2
pm2 info entraide-bot

# Avec top
top -p $(cat bot.pid)
```

### Limiter la mémoire (PM2)

Dans `ecosystem.config.js` :

```javascript
max_memory_restart: '200M'  // Redémarre si > 200 MB
```

---

## 🔧 Configuration avancée

### Webhook Discord production vs dev

Vous pouvez utiliser deux webhooks différents :

**.env.local** (Symfony) :

```bash
# DEV
DISCORD_MODERATION_WEBHOOK_URL="https://discord.com/api/webhooks/DEV_ID/DEV_TOKEN"

# PROD
DISCORD_MODERATION_WEBHOOK_URL="https://discord.com/api/webhooks/PROD_ID/PROD_TOKEN"
```

### Bot multi-serveurs

Si votre bot doit modérer plusieurs serveurs Discord :

```javascript
// Dans discord-bot.js
const GUILD_IDS = process.env.DISCORD_GUILD_IDS.split(',');

client.on('interactionCreate', async (interaction) => {
    if (!GUILD_IDS.includes(interaction.guildId)) {
        return; // Ignorer les interactions d'autres serveurs
    }
    // ... reste du code
});
```

**.env.bot.local** :

```bash
DISCORD_GUILD_IDS=123456789,987654321
```

---

## ✅ Checklist de déploiement

- [ ] Node.js installé sur o2switch
- [ ] npm installé
- [ ] `.env.bot.local` créé et sécurisé
- [ ] Token Discord configuré
- [ ] URL API Symfony correcte (HTTPS)
- [ ] Dépendances installées (`npm install`)
- [ ] Test manuel réussi
- [ ] PM2 installé (ou alternative configurée)
- [ ] Bot démarré avec PM2
- [ ] Auto-restart configuré
- [ ] Logs vérifiés
- [ ] Test complet : Créer annonce → Discord → Cliquer bouton
- [ ] Monitoring configuré

---

## 📝 Logs et Debugging

### Activer les logs détaillés

Modifiez `discord-bot.js` :

```javascript
// En haut du fichier
const DEBUG = process.env.DEBUG === 'true';

if (DEBUG) {
    console.log('DEBUG MODE ENABLED');
}

// Dans le code
if (DEBUG) console.log('Button clicked:', interaction.customId);
```

**.env.bot.local** :

```bash
DEBUG=true
```

### Rotation des logs (PM2)

```bash
pm2 install pm2-logrotate

# Configurer la rotation
pm2 set pm2-logrotate:max_size 10M
pm2 set pm2-logrotate:retain 7
pm2 set pm2-logrotate:compress true
```

---

## 🎯 Commandes rapides

```bash
# Démarrer
pm2 start ecosystem.config.js

# Redémarrer
pm2 restart entraide-bot

# Arrêter
pm2 stop entraide-bot

# Logs
pm2 logs entraide-bot --lines 50

# Monitoring
pm2 monit

# Infos
pm2 info entraide-bot

# Liste des processus
pm2 list
```

---

**Date de création** : 2025-11-17
**Bot** : Entraide souverainiste#4993
**URL API** : https://entraidesouverainiste.fr
**Gestionnaire** : PM2 (recommandé)

🎉 **Bon déploiement du bot !**
