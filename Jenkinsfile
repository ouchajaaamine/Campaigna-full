pipeline {
    agent any

    
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        
        stage('Backend Dependencies') {
            agent {
                docker {
                    image 'php:8.2-cli'
                }
            }
            steps {
                dir('backend') {
                    sh '''
                        apt-get update -qq
                        apt-get install -y -qq git unzip
                        curl -sS https://getcomposer.org/installer | php
                        php composer.phar install --no-interaction --prefer-dist --no-progress
                    '''
                }
            }
        }
        
        stage('Trivy: Scan Composer Dependencies') {
            agent {
                docker {
                    image 'aquasec/trivy:latest'
                    args '--entrypoint=""'
                }
            }
            steps {
                dir('backend') {
                    sh '''
                        trivy fs \
                            --scanners vuln \
                            --severity CRITICAL,HIGH,MEDIUM \
                            --format table \
                            --output trivy-composer-report.json \
                            --exit-code 0 \
                            .
                    '''
                }
            }
            post {
                always {
                    archiveArtifacts artifacts: 'backend/trivy-composer-report.json', allowEmptyArchive: true
                }
            }
        }
        
        stage('PHPStan Analysis') {
            agent {
                docker {
                    image 'php:8.2-cli'
                }
            }
            steps {
                dir('backend') {
                    // Increase PHPStan memory limit to avoid worker crashes on 128M default
                    sh 'vendor/bin/phpstan analyse --error-format=table --no-progress --memory-limit=512M'
                }
            }
        }
        
        stage('PHPUnit Tests') {
            agent {
                docker {
                    image 'php:8.2-cli'
                }
            }
            steps {
                dir('backend') {
                    sh 'vendor/bin/phpunit --log-junit test-results.xml'
                }
            }
            post {
                always {
                    junit 'backend/test-results.xml'
                }
            }
        }
        
        stage('Frontend Build') {
            agent {
                docker {
                    image 'node:18-alpine'
                }
            }
            steps {
                dir('frontend') {
                    sh '''
                        corepack enable
                        corepack prepare pnpm@latest --activate
                        pnpm install
                        pnpm run build
                    '''
                }
            }
        }
    }
    
    post {
        success {
            echo 'Build completed successfully'
        }
        failure {
            echo 'Build failed'
        }
        always {
            cleanWs()
        }
    }
}