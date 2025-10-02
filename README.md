<p align="center">
  <img src="https://fantasy.premierleague.com/img/fantasy-premier-league-logo-2023.png" width="400" alt="Fantasy Premier League Logo">
</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12.x-red" alt="Laravel Version"></a>
  <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/TailwindCSS-4.x-blue" alt="TailwindCSS"></a>
  <a href="https://render.com"><img src="https://img.shields.io/badge/Deploy-Render-brightgreen" alt="Deploy on Render"></a>
  <a href="https://github.com/Nafiz001/Fantasy-Premier-League/LICENSE"><img src="https://img.shields.io/badge/License-MIT-green" alt="License"></a>
</p>

# Fantasy Premier League Clone

A fully functional Fantasy Premier League clone built with Laravel 12 and TailwindCSS. Features live FPL data integration, squad management, points calculation (2025/26 rules), and classic league system with **100% FREE deployment on Render.com**.

## ✨ Features

### Core Features
- 🔐 **User Authentication**: Register, login, and manage your account
- ⚽ **Squad Selection**: Build your team with 15 players and £100M budget
- 🎯 **Auto-Pick**: AI-powered team selection based on player stats
- 📊 **Live FPL Data**: Real-time player stats, fixtures, and gameweek data
- 💯 **Points Calculation**: Accurate 2025/26 FPL scoring system
- 🏆 **League System**: Create and join classic leagues with leaderboards
- 📈 **Gameweek Navigation**: View points breakdown across all gameweeks
- 🎨 **Responsive Design**: Beautiful UI that works on all devices
- 🆓 **FREE Deployment**: Deploy on Render.com with PostgreSQL - NO credit card!

### League Features
- Create public or private leagues
- Unique 6-character league codes
- Real-time leaderboard with proper point aggregation
- Admin settings for league management
- League member statistics

## 🚀 Deploy for FREE on Render.com

**Deploy your Fantasy Premier League app in 5 minutes - 100% FREE!**

✅ **No credit card required**  
✅ **Free PostgreSQL database included**  
✅ **Free SSL certificate**  
✅ **Automatic GitHub deployments**  
✅ **750 hours/month free** (24/7 for a month!)  

### Quick Steps:
1. Push code to GitHub
2. Sign up on Render.com (free)
3. Create Web Service from your GitHub repo
4. Add free PostgreSQL database
5. Set environment variables
6. Deploy! ✅

**📚 Full Guide**: See [RENDER_DEPLOYMENT.md](RENDER_DEPLOYMENT.md) for detailed instructions.

---

## 💻 Local Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and NPM (v18+)
- MySQL or PostgreSQL
- Git

### Installation

- PHP 8.1 or higher
- Composer
- Node.js and NPM
- XAMPP/WAMP or other local server environment

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Nafiz001/Fantasy-Premier-League.git
   cd fantasy-premier-league
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Install NPM dependencies**:
   ```bash
   npm install
   ```

4. **Set up environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database** in `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fpl
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run migrations**:
   ```bash
   php artisan migrate
   ```

7. **Import FPL data** (optional):
   ```bash
   php artisan fpl:import-all
   ```

8. **Build frontend assets**:
   ```bash
   npm run dev
   # or for production:
   npm run build
   ```

9. **Start the development server**:
   ```bash
   php artisan serve
   ```
   
   Or use the provided batch file on Windows:
   ```bash
   start.bat
   ```

10. **Visit** [http://localhost:8000](http://localhost:8000) in your browser! 🎉

---

## 📖 Documentation

- **[RENDER_DEPLOYMENT.md](RENDER_DEPLOYMENT.md)** - Complete FREE deployment guide for Render.com
- **[DOCKER_GUIDE.md](DOCKER_GUIDE.md)** - Docker setup for local development and deployment

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x (PHP 8.2)
- **Frontend**: Vite + Tailwind CSS 4.x
- **Database**: MySQL (local) / PostgreSQL (production on Render)
- **APIs**: Official FPL API integration
- **Deployment**: Render.com (100% FREE tier!)

---

## 🎮 Usage

### First Time Setup
1. Register a new account
2. Select your squad (15 players, £100M budget)
3. Set your captain and vice-captain
4. Choose your formation
5. Join or create leagues
6. Track your points each gameweek!

### League Management
- Create your own league (public/private)
- Share league code with friends
- View leaderboard with real-time points
- Admin controls for league settings

### FPL Data Import
Visit `/fpl/data/dashboard` to:
- Import all FPL data
- Update specific gameweeks
- View import status
- Run manual imports

---

## 📁 Project Structure

```
fantasy-premier-league/
├── app/
│   ├── Http/Controllers/    # Controllers (Squad, Points, League, etc.)
│   ├── Models/               # Eloquent models
│   └── Services/             # Business logic (FPLPointsService, etc.)
├── database/
│   ├── migrations/           # Database schema
│   └── seeders/              # Data seeders
├── resources/
│   ├── views/                # Blade templates
│   └── css/                  # Styles
├── public/                   # Public assets
├── routes/
│   └── web.php              # Route definitions
├── config/                   # Configuration files
├── railway.json             # Railway deployment config
├── nixpacks.toml           # Nixpacks build config
└── RAILWAY_*.md            # Deployment guides
```

---

## 🚢 Deployment

### Render.com (Recommended - 100% FREE)
✅ Completely FREE (no credit card required)  
✅ Free PostgreSQL database included  
✅ Automatic GitHub deployments  
✅ Free SSL certificate  
✅ 750 hours/month (24/7 coverage)  

**See**: [RENDER_DEPLOYMENT.md](RENDER_DEPLOYMENT.md)

### Other Options
- **Heroku**: Easy deployment (paid)
- **Laravel Forge**: Professional deployment (paid)
- **DigitalOcean**: VPS hosting (paid)

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🙏 Acknowledgments

- Official [Fantasy Premier League](https://fantasy.premierleague.com) for inspiration
- [Laravel](https://laravel.com) framework
- [Tailwind CSS](https://tailwindcss.com) for styling
- FPL API for live data

---

## 📧 Contact

**Nafiz001** - [GitHub Profile](https://github.com/Nafiz001)

**Project Link**: [https://github.com/Nafiz001/Fantasy-Premier-League](https://github.com/Nafiz001/Fantasy-Premier-League)

---

<p align="center">Made with ❤️ for Fantasy Premier League fans</p>
<p align="center">⚽ Good luck with your team! 🏆</p>
