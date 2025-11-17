# Configuration du Bot Discord - Modération

## Étape 1 : Créer l'application Discord

1. Va sur https://discord.com/developers/applications
2. Clique sur "New Application"
3. Nom : `Action Sociale Moderation Bot`
4. Onglet "Bot" → "Add Bot" → Confirme
5. **Copie le TOKEN** (bouton "Reset Token" si besoin)
6. Active ces options sous "Privileged Gateway Intents" :
   - ✅ SERVER MEMBERS INTENT
   - ✅ MESSAGE CONTENT INTENT

## Étape 2 : Inviter le bot sur ton serveur

1. Onglet "OAuth2" → "URL Generator"
2. Scopes : Coche `bot`
3. Bot Permissions : Coche ces permissions :
   - ✅ Read Messages/View Channels
   - ✅ Send Messages
   - ✅ Add Reactions
   - ✅ Read Message History
4. Copie l'URL générée en bas
5. Colle l'URL dans ton navigateur → Sélectionne ton serveur → Autorise

## Étape 3 : Configurer le bot localement

1. Copie `.env.bot` vers `.env.bot.local` :
   ```bash
   cp .env.bot .env.bot.local
   ```

2. Édite `.env.bot.local` et remplace :
   ```
   DISCORD_BOT_TOKEN=TON_TOKEN_ICI
   SYMFONY_API_URL=http://localhost:8080
   ```

3. Installe les dépendances Node.js :
   ```bash
   npm install
   ```

## Étape 4 : Démarrer le bot

```bash
npm run bot
```

Ou en mode dev (redémarre automatiquement) :
```bash
npm run bot:dev
```

## Test

1. Crée une annonce sur http://localhost:8080
2. Vérifie Discord → message avec 3 boutons
3. Clique sur un bouton → le bot répond et exécute l'action !

## Troubleshooting

- **Bot offline ?** → Vérifie le TOKEN dans `.env.bot.local`
- **Pas de boutons ?** → Vérifie que le webhook Discord est bien configuré dans `.env.local`
- **Erreur API ?** → Vérifie que Symfony tourne sur `http://localhost:8080`
