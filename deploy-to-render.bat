@echo off
echo ========================================
echo Render.com Deployment - Git Push Helper
echo ========================================
echo.

echo Step 1: Adding all files to git...
git add .

echo.
echo Step 2: Creating commit...
set /p commit_message="Enter commit message (or press Enter for default): "

if "%commit_message%"=="" (
    git commit -m "Ready for Render deployment"
) else (
    git commit -m "%commit_message%"
)

echo.
echo Step 3: Pushing to GitHub...
git push origin main

echo.
echo ========================================
echo Done! Your code is now on GitHub.
echo.
echo Next Steps:
echo 1. Go to https://render.com
echo 2. Sign up for FREE (no credit card!)
echo 3. Create Web Service from your GitHub repo
echo 4. Select "Docker" as Language
echo 5. Add FREE PostgreSQL database
echo 6. Set environment variables (see RENDER_DEPLOYMENT.md)
echo 7. Deploy!
echo.
echo See RENDER_DEPLOYMENT.md for full guide
echo ========================================
echo.

pause
