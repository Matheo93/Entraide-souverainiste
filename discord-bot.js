/**
 * Discord Moderation Bot for Action Sociale
 * Handles approve/reject/ban buttons on announcement webhooks
 */

const { Client, GatewayIntentBits, InteractionType } = require('discord.js');
const axios = require('axios');

// Configuration
const DISCORD_BOT_TOKEN = process.env.DISCORD_BOT_TOKEN || '';
const SYMFONY_API_URL = process.env.SYMFONY_API_URL || 'http://localhost:8080';

if (!DISCORD_BOT_TOKEN) {
    console.error('❌ DISCORD_BOT_TOKEN not set in environment variables');
    process.exit(1);
}

// Create Discord client
const client = new Client({
    intents: [
        GatewayIntentBits.Guilds,
        GatewayIntentBits.GuildMessages
    ]
});

// Bot ready
client.once('ready', () => {
    console.log('✅ Discord bot ready!');
    console.log(`📡 Logged in as ${client.user.tag}`);
    console.log(`🔗 Connected to Symfony API: ${SYMFONY_API_URL}`);
});

// Handle button interactions
client.on('interactionCreate', async (interaction) => {
    if (!interaction.isButton()) return;

    const customId = interaction.customId;
    console.log(`🔘 Button clicked: ${customId}`);

    // Parse custom_id: approve_123, reject_456, ban_789_192.168.1.1
    const parts = customId.split('_');
    const action = parts[0]; // approve, reject, ban
    const announceId = parts[1];
    const ipAddress = parts[2] || null;

    // Defer reply (acknowledge the interaction)
    await interaction.deferReply({ ephemeral: true });

    try {
        let response;
        let message;

        switch (action) {
            case 'approve':
                response = await axios.post(`${SYMFONY_API_URL}/api/moderation/approve/${announceId}`);
                message = `✅ Annonce #${announceId} approuvée !`;

                // Update original message
                await interaction.message.edit({
                    embeds: interaction.message.embeds,
                    components: [] // Remove buttons
                });

                // Add reaction
                await interaction.message.react('✅');
                break;

            case 'reject':
                response = await axios.post(`${SYMFONY_API_URL}/api/moderation/reject/${announceId}`);
                message = `❌ Annonce #${announceId} rejetée !`;

                await interaction.message.edit({
                    embeds: interaction.message.embeds,
                    components: []
                });

                await interaction.message.react('❌');
                break;

            case 'ban':
                if (!ipAddress) {
                    throw new Error('IP address missing for ban action');
                }

                response = await axios.post(`${SYMFONY_API_URL}/api/moderation/ban`, {
                    announceId: announceId,
                    ipAddress: ipAddress,
                    reason: 'Discord moderation ban'
                });
                message = `🚫 IP ${ipAddress} bannie ! (Annonce #${announceId})`;

                await interaction.message.edit({
                    embeds: interaction.message.embeds,
                    components: []
                });

                await interaction.message.react('🚫');
                break;

            default:
                throw new Error(`Unknown action: ${action}`);
        }

        // Reply to admin
        await interaction.editReply({
            content: message,
            ephemeral: true
        });

        console.log(`✅ ${message}`);

    } catch (error) {
        console.error('❌ Error:', error.message);

        await interaction.editReply({
            content: `❌ Erreur: ${error.response?.data?.error || error.message}`,
            ephemeral: true
        });
    }
});

// Error handling
client.on('error', (error) => {
    console.error('❌ Discord client error:', error);
});

process.on('unhandledRejection', (error) => {
    console.error('❌ Unhandled promise rejection:', error);
});

// Login
client.login(DISCORD_BOT_TOKEN).catch((error) => {
    console.error('❌ Failed to login:', error);
    process.exit(1);
});

console.log('🚀 Starting Discord moderation bot...');
