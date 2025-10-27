# 🚀 Jenkins Local Setup

Complete Jenkins setup with Docker, Blue Ocean, and all CI/CD plugins.

## 📋 Prerequisites

- Docker Desktop installed and running
- At least 4GB RAM allocated to Docker

## 🏃 Quick Start

### Option 1: Use Pre-built Image (Faster)

```powershell
cd jenkins
docker-compose up -d
```

### Option 2: Build Custom Image (Recommended)

```powershell
cd jenkins
docker build -t jenkins-custom .
```

Then update `docker-compose.yml` to use `jenkins-custom` instead of `jenkins/jenkins:lts-jdk17`, and run:

```powershell
docker-compose up -d
```

## 🔑 Access Jenkins

1. **URL**: http://localhost:8080
2. **Username**: `admin`
3. **Password**: `admin`

> ⚠️ Change the password after first login!

## 🎨 Access Blue Ocean

1. Go to http://localhost:8080/blue
2. Click **"New Pipeline"**
3. Select **GitHub**
4. Paste your Personal Access Token
5. Select repository: **Campaigna-full**

## 📦 Installed Plugins

- ✅ Blue Ocean (modern UI)
- ✅ Git & GitHub Integration
- ✅ Docker Pipeline
- ✅ JUnit (test reports)
- ✅ Pipeline Stage View
- ✅ GitHub Branch Source (Multibranch)
- ✅ Performance Plugin
- ✅ Timestamper, AnsiColor, etc.

## 🛠️ Management Commands

```powershell
# Start Jenkins
docker-compose up -d

# Stop Jenkins
docker-compose down

# View logs
docker-compose logs -f jenkins

# Restart Jenkins
docker-compose restart jenkins

# Remove everything (including data)
docker-compose down -v
```

## 🔧 Jenkins Configuration

- **Executors**: 2 (configured via JCasC)
- **Docker-in-Docker**: Enabled (can run docker commands in pipelines)
- **Persistent storage**: `jenkins_home` volume
- **Configuration as Code**: `casc.yaml` (JCasC)

## 📝 Next Steps

1. Start Jenkins: `docker-compose up -d`
2. Access UI: http://localhost:8080
3. Login with `admin/admin`
4. Create GitHub credentials (PAT)
5. Create Multibranch Pipeline for Campaigna-full
6. Build and enjoy! 🎉

## 🐛 Troubleshooting

### Jenkins won't start?
```powershell
docker logs jenkins-local
```

### Permission issues?
Make sure Docker Desktop is running and you have permissions to `/var/run/docker.sock`

### Plugins not installed?
Rebuild the image:
```powershell
docker-compose down
docker build -t jenkins-custom .
# Update docker-compose.yml image to jenkins-custom
docker-compose up -d
```
