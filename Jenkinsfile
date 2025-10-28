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
                    args '-v $HOME/.composer:/root/.composer'
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
        
        stage('PHPStan Analysis') {
            agent {
                docker {
                    image 'php:8.2-cli'
                }
            }
            steps {
                dir('backend') {
                    sh 'vendor/bin/phpstan analyse --error-format=table --no-progress'
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