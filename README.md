<p align="center">
  <img src="https://fantasy.premierleague.com/img/fantasy-premier-league-logo-2023.png" width="400" alt="Fantasy Premier League Logo">
</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-10.x-red" alt="Laravel Version"></a>
  <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/TailwindCSS-3.x-blue" alt="TailwindCSS"></a>
  <a href="https://github.com/Nafiz001/Fantasy-Premier-League/LICENSE"><img src="https://img.shields.io/badge/License-MIT-green" alt="License"></a>
</p>

# Fantasy Premier League Website

A responsive Fantasy Premier League website built with Laravel and TailwindCSS. This project replicates the official FPL website with a modern UI and interactive features.

## Features

- **Responsive Design**: Works seamlessly on mobile, tablet, and desktop
- **Interactive UI Components**: Dynamic navigation, animated elements, and hover effects
- **Premier League Styling**: Official Premier League color scheme and design patterns
- **Modular Layout**: Easy to extend and customize

## Preview

Here's how the Fantasy Premier League homepage looks:

![FPL Homepage](https://fantasy.premierleague.com/img/players-cutout-hero.png)

## Getting Started

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and NPM
- XAMPP/WAMP or other local server environment

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Nafiz001/Fantasy-Premier-League.git
   cd fantasy-premier-league
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install NPM dependencies:
   ```bash
   npm install
   ```

4. Create a copy of the `.env` file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Build frontend assets:
   ```bash
   npm run dev
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```
   
   Or use the provided batch file:
   ```bash
   start.bat
   ```

Visit [http://localhost:8000](http://localhost:8000) in your browser to see the website.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
