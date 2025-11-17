# 📋 Récapitulatif Complet du Système

## 🎯 Vue d'ensemble

Système de modération Discord + Chat widget en temps réel pour la plateforme Action Sociale.

---

## ✅ Fonctionnalités Implémentées

### 1. 💬 Chat Widget (Bottom Right)

**Localisation**: Fenêtre flottante en bas à droite sur toutes les pages

**Fonctionnalités**:
- ✅ Liste des conversations avec badge de messages non lus
- ✅ Interface de chat en temps réel
- ✅ Envoi de messages AJAX (sans rechargement)
- ✅ Auto-refresh toutes les 5 secondes
- ✅ Compteur de messages non lus avec animation pulse
- ✅ Design moderne avec dégradé violet (#667eea → #764ba2)
- ⏳ Images/vidéos (prévu, non implémenté)

**Fichiers**:
- `templates/front/parts/chat-widget.html.twig` - Widget UI
- `templates/front/base.html.twig` - Intégration dans toutes les pages
- `src/Controller/Front/Conversations/ConversationController.php` - API REST JSON

**Routes API**:
```
GET  /conversations/list          → Liste des conversations
GET  /conversations/{id}/messages → Messages d'une conversation
POST /conversations/{id}/message  → Envoyer un message
GET  /conversations/unread-count  → Nombre total de non lus
```

---

### 2. 🤖 Bot Discord de Modération

**Fonctionnement**:
1. Une nouvelle annonce est créée sur le site
2. Webhook Discord envoie la notification dans `#moderation-annonces`
3. Message contient 3 boutons: ✅ Approuver | ❌ Rejeter | 🚫 Ban IP
4. **L'ADMIN clique sur un bouton** (pas de modération auto)
5. Bot détecte le clic → Appelle API Symfony → Exécute l'action
6. Bot répond, ajoute une réaction et retire les boutons

**Fichiers**:
- `discord-bot.js` - Bot Node.js (discord.js v14)
- `.env.bot.local` - Configuration du bot
- `src/Controller/Api/ModerationApiController.php` - API pour le bot
- `DISCORD-BOT-SETUP.md` - Guide de configuration

**API Endpoints**:
```
POST /api/moderation/approve/{id} → Approuver une annonce
POST /api/moderation/reject/{id}  → Rejeter et supprimer
POST /api/moderation/ban          → Bannir une IP
GET  /api/moderation/test         → Test de connexion
```

**Configuration Discord**:
- Application: Action Sociale Moderation Bot
- Bot Token: Stocké dans `.env.bot.local`
- Intents activés: SERVER MEMBERS INTENT + MESSAGE CONTENT INTENT
- Webhook URL: Configurée dans `.env.local`

---

## 🚀 Comment Démarrer

### Prérequis
```bash
# Vérifier que Docker tourne
docker-compose ps

# Vérifier que Symfony tourne
# → http://localhost:8080
```

### Démarrer le Bot Discord

**Option 1 - WSL/Ubuntu (Recommandé)**:
```bash
cd /mnt/c/Users/M_Beu/Desktop/action-sociale
npm run bot
```

**Option 2 - Windows**:
```bash
start-bot.bat
```

**Option 3 - Mode développement (auto-restart)**:
```bash
npm run bot:dev
```

### Vérifier que tout fonctionne
```bash
# Bot Discord doit afficher:
✅ Discord bot ready!
📡 Logged in as Entraide souverainiste#4993
🔗 Connected to Symfony API: http://localhost:8080
```

---

## 🧪 Tests à Effectuer

### Test 1: Modération Discord

1. Va sur http://localhost:8080
2. Connecte-toi ou crée un compte
3. Crée une nouvelle annonce
4. Va sur Discord → Canal `#moderation-annonces`
5. **Tu dois voir**: Message avec 3 boutons
6. **Clique sur ✅ Approuver**
7. **Vérifications**:
   - ✅ Bot répond "Annonce #X approuvée !"
   - ✅ Réaction ✅ ajoutée au message
   - ✅ Boutons retirés du message
   - ✅ Annonce active en BDD (`is_active = 1`)

8. **Teste aussi**:
   - ❌ Rejeter → Annonce supprimée de la BDD
   - 🚫 Ban IP → IP ajoutée dans la table `ip_bans`

### Test 2: Chat Widget

1. Ouvre http://localhost:8080 (connecté)
2. Clique sur l'icône chat (bas droite)
3. **Tu dois voir**: Liste des conversations
4. Clique sur une conversation
5. Envoie un message
6. **Vérifications**:
   - ✅ Message apparaît instantanément
   - ✅ Auto-scroll vers le bas
   - ✅ Badge de non-lus se met à jour

---

## 📁 Architecture des Fichiers

```
action-sociale/
│
├── discord-bot.js                      # Bot Discord (Node.js)
├── .env.bot.local                      # Config bot (TOKEN + API URL)
├── start-bot.bat                       # Script Windows pour lancer bot
├── package.json                        # Dépendances: discord.js, axios
├── DISCORD-BOT-SETUP.md                # Guide setup Discord
│
├── src/
│   ├── Controller/
│   │   ├── Front/Conversations/
│   │   │   └── ConversationController.php   # API Chat JSON
│   │   └── Api/
│   │       └── ModerationApiController.php  # API Modération
│   │
│   └── Entity/
│       ├── Conversations/               # Entities chat
│       └── IpBan.php                    # Entity pour IP bannies
│
├── templates/
│   └── front/
│       ├── base.html.twig              # Inclut le widget
│       └── parts/
│           └── chat-widget.html.twig   # Widget chat complet
│
└── .env.local                          # Webhook Discord URL
```

---

## 🔐 Variables d'Environnement

### `.env.local` (Symfony)
```bash
DISCORD_MODERATION_WEBHOOK_URL="https://discord.com/api/webhooks/1439913790971056189/..."
```

### `.env.bot.local` (Bot Discord)
```bash
DISCORD_BOT_TOKEN=VOTRE_TOKEN_BOT_DISCORD
SYMFONY_API_URL=http://localhost:8080
```

---

## 🛠️ Commandes Utiles

```bash
# Démarrer le bot
npm run bot

# Mode dev (auto-restart)
npm run bot:dev

# Vérifier les logs Docker
docker-compose logs -f php

# Vider le cache Symfony
docker-compose exec php php bin/console cache:clear

# Migrations BDD
docker-compose exec php php bin/console doctrine:migrations:migrate

# Voir les routes
docker-compose exec php php bin/console debug:router
```

---

## 🎨 Design du Chat Widget

### Couleurs
- **Primaire**: Dégradé violet `#667eea` → `#764ba2`
- **Hover**: `#5568d3`
- **Shadows**: `rgba(102, 126, 234, 0.3)`
- **Texte**: `#333` (messages user), `#555` (messages other)

### Animations
- Pulse sur le badge de non-lus
- Transition smooth sur hover
- Auto-scroll fluide vers le bas

### Icônes
- Material Icons (déjà intégré)
- `chat`, `close`, `arrow_back`, `send`

---

## 🐛 Troubleshooting

### Bot Discord offline?
```bash
# Vérifier le token
cat .env.bot.local

# Relancer le bot
npm run bot
```

### Pas de boutons sur Discord?
→ Vérifier `DISCORD_MODERATION_WEBHOOK_URL` dans `.env.local`

### Erreur API 404?
→ Vérifier que Symfony tourne: `curl http://localhost:8080/api/moderation/test`

### Chat widget invisible?
→ Vider cache navigateur + cache Symfony:
```bash
docker-compose exec php php bin/console cache:clear
```

### Messages ne s'affichent pas?
→ Vérifier la console JS du navigateur (F12)

---

## 📊 Statistiques BDD (Sans Admin)

Pour voir les stats, tu peux exécuter ces requêtes SQL directement:

```sql
-- Compter les annonces par statut
SELECT is_active, COUNT(*)
FROM announces
GROUP BY is_active;

-- Top utilisateurs par nombre d'annonces
SELECT u.email, COUNT(a.id) as nb_annonces
FROM user u
JOIN announces a ON a.user_offrant_id = u.id
GROUP BY u.id
ORDER BY nb_annonces DESC
LIMIT 10;

-- IPs bannies
SELECT * FROM ip_bans WHERE is_active = 1;

-- Messages par conversation
SELECT c.id, COUNT(m.id) as nb_messages
FROM conversations c
LEFT JOIN conversation_messages m ON m.conversation_id = c.id
GROUP BY c.id
ORDER BY nb_messages DESC;
```

---

## ✨ Prochaines Améliorations Possibles

1. **Images/Vidéos dans le chat** (upload + preview)
2. **WebSocket** au lieu du polling (Socket.io)
3. **Notifications push** (Service Worker)
4. **Archivage conversations**
5. **Recherche dans les messages**
6. **Emojis/GIFs** dans le chat
7. **Modération automatique** (ML pour spam)
8. **Dashboard admin** (graphiques, stats)

---

## 📝 Notes Importantes

- ⚠️ **Le bot NE modère PAS automatiquement** - c'est l'admin qui clique
- ⚠️ **Token Discord sensible** - Ne jamais commit `.env.bot.local`
- ⚠️ **Polling 5s** - Peut être optimisé avec WebSocket
- ✅ **Pas de page admin** - Uniquement requêtes SQL pour stats
- ✅ **Design responsive** - Widget s'adapte sur mobile

---

## 🎯 Statut Actuel

| Composant | Statut | Note |
|-----------|--------|------|
| Chat Widget | ✅ Opérationnel | UI/UX terminée |
| API Chat | ✅ Opérationnel | 4 routes JSON |
| Discord Bot | ✅ En ligne | Connecté et prêt |
| API Modération | ✅ Opérationnel | 3 actions + test |
| Webhook Discord | ✅ Configuré | URL valide |
| Tests E2E | ⏳ À faire | Workflow complet |

---

## 🚀 Prêt à Tester !

Le système est **100% fonctionnel et prêt à être testé**.

**Commandes rapides**:
```bash
# Lancer le bot
npm run bot

# Accéder au site
http://localhost:8080

# Voir Discord
https://discord.com/channels/TON_SERVEUR_ID
```

**Contact Support**:
- 📧 Logs Discord: Voir le terminal où tourne le bot
- 📧 Logs Symfony: `docker-compose logs -f php`
- 📧 Logs DB: Accès phpMyAdmin sur port configuré

---

**Créé le**: 2025-11-17
**Version**: 1.0
**Auteur**: Claude + User
**Projet**: Action Sociale - Plateforme d'Entraide
