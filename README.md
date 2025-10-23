# Campaigna

**AI-Powered Campaign Management Platform**

A full-stack application for managing advertising campaigns with real-time analytics and AI-powered insights.

[![Docker](https://img.shields.io/badge/Docker-Containerized-2496ED?style=flat-square&logo=docker)](https://www.docker.com/)
[![Next.js](https://img.shields.io/badge/Next.js-15-black?style=flat-square&logo=next.js)](https://nextjs.org/)
[![Symfony](https://img.shields.io/badge/Symfony-7.3-000000?style=flat-square&logo=symfony)](https://symfony.com/)

---

## Screenshots

### Dashboard
![Dashboard Overview](./screenshots/Screenshot%202025-10-18%20175653.png)

### Campaign List
![Campaign List](./screenshots/Screenshot%202025-10-18%20175713.png)

### AI Assistant
![AI Chatbot](./screenshots/Screenshot%202025-10-18%20180909.png)

### Campaign Details
![Campaign Detail](./screenshots/Screenshot%202025-10-18%20181143.png)

### Affiliates
![Affiliates List](./screenshots/Screenshot%202025-10-18%20181246.png)

---

## Features

- **Campaign Management** - Create, edit, and monitor advertising campaigns
- **Real-Time Analytics** - Track revenue, ROI, and conversion metrics
- **AI Assistant** - Get insights and optimization recommendations
- **Affiliate Management** - Manage partners and track commissions
- **Interactive Dashboards** - Visualize performance with charts and graphs

## Tech Stack

**Frontend:**
- Next.js 15 (App Router)
- React 19
- TypeScript
- Tailwind CSS 4
- shadcn/ui
- Recharts

**Backend:**
- Symfony 7.3
- API Platform 4.2
- Doctrine ORM 3.5
- PostgreSQL 16
- Redis 7
- PHP 8.2

**Infrastructure:**
- Docker & Docker Compose

## Getting Started

### Prerequisites
- Docker Desktop
- Git
- 8GB RAM minimum

### Installation

1. Clone the repository:
```bash
git clone https://github.com/ouchajaaamine/Campaigna-full.git
cd Campaigna-full
```

2. Set up environment variables:
```bash
# The .env file is already configured with default values
# Edit .env if you need to change the OpenRouter API token
```

Edit `.env` and add your OpenRouter API token (optional, for AI features):
```env
OPENROUTER_TOKEN=your_token_here
```

3. Start the application:
```bash
docker compose up --build
```

The startup process will initialize the database, run migrations, and load sample data.

## Access Points

| Service | URL |
|---------|-----|
| Frontend | http://localhost:3000 |
| API Documentation | http://localhost:8000/api |
| Dashboard | http://localhost:3000/dashboard |
| Database | localhost:5432 (user: `app`, password: `app`) |

## Development

### Basic Commands
```bash
# Stop services
docker compose down

# Rebuild and restart
docker compose up --build

# Start in background
docker compose up -d

# View logs
docker compose logs -f app
docker compose logs -f frontend

# Reset database (WARNING: deletes all data)
docker compose down -v
docker compose up --build
```

### Container Access
```bash
# Backend shell
docker compose exec app sh

# Frontend shell
docker compose exec frontend sh
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -m 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Open a Pull Request

## Project Structure

```
Campaigna-full/
├── backend/                 # Symfony API application
│   ├── src/                # PHP source code
│   ├── config/             # Symfony configuration
│   ├── migrations/         # Database migrations
│   ├── public/             # Web assets
│   └── composer.json       # PHP dependencies
├── frontend/               # Next.js application
│   ├── app/                # Next.js app router
│   ├── components/         # React components
│   ├── lib/                # Utilities and API client
│   └── package.json        # Node dependencies
├── screenshots/            # Application screenshots
├── docker-compose.yml      # Docker orchestration
└── .env                    # Environment variables
```

---

## Disclaimer

This project was developed by me as a learning experience. I used AI tools (including Claude and other AI assistants) throughout the development process to help me understand concepts, debug issues, and learn best practices in full-stack development. The AI tools served as educational resources and coding assistants while I built this application.