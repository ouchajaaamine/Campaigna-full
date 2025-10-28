pipeline {
    agent any
    
    stages {
        stage('Checkout') {
            steps {
                echo '📦 Checking out code...'
                checkout scm
            }
        }
        
        stage('Build Frontend') {
            agent {
                docker {
                    image 'node:18-alpine'
                }
            }
            steps {
                echo '🔨 Building frontend...'
                dir('frontend') {
                    sh '''
                        pnpm install
                        pnpm run build
                    '''
                }
            }
        }
    }
    
    post {
        success {
            echo '✅ Build completed successfully!'
        }
        failure {
            echo '❌ Build failed. Check the logs above.'
        }
    }
}