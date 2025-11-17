@echo off
echo Lancement du serveur Action Sociale...
echo.
echo 1. Compilation des assets...
call npm run dev
echo.
echo 2. Lancement du serveur PHP sur http://127.0.0.1:8000
echo.
echo CTRL+C pour arreter le serveur
echo.
echo Ouverture automatique du navigateur dans 3 secondes...
timeout /t 3 /nobreak >nul
start http://127.0.0.1:8000
php -S 127.0.0.1:8000 -t public
pause