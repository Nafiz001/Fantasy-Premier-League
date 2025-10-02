@echo off
echo ========================================
echo Railway Deployment - Git Push Helper
echo ========================================
echo.

echo Step 1: Adding all files to git...
git add .

echo.
echo Step 2: Creating commit...
set /p commit_message="Enter commit message (or press Enter for default): "

if "%commit_message%"=="" (
    git commit -m "Added Railway deployment configuration"
) else (
    git commit -m "%commit_message%"
)

echo.
echo Step 3: Pushing to GitHub...
git push origin main

echo.
echo ========================================
echo Done! Your code is now on GitHub.
echo Next: Go to https://railway.app to deploy
echo ========================================
echo.

pause
