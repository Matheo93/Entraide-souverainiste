@echo off
echo Starting Discord Moderation Bot...
echo.

REM Load environment variables from .env.bot.local
for /f "delims== tokens=1,2" %%G in (.env.bot.local) do (
    set %%G=%%H
)

echo Bot Token: %DISCORD_BOT_TOKEN:~0,20%...
echo Symfony API: %SYMFONY_API_URL%
echo.
echo Press Ctrl+C to stop the bot
echo.

node discord-bot.js
