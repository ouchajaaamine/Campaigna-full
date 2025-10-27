pipeline {
  agent any

  environment {
    BACKEND_DIR = 'backend'
    COMPOSE_FILE = "${env.WORKSPACE}/${BACKEND_DIR}/.ci/docker-compose.test.yml"
  }

  stages {
    stage('Checkout') {
      steps {
        checkout scm
      }
    }

    stage('Start test stack') {
      steps {
        dir("${BACKEND_DIR}") {
          sh './.ci/test-stack.sh || true'
        }
      }
    }

    stage('Frontend build') {
      steps {
        dir('frontend') {
          sh '''
            if command -v pnpm >/dev/null 2>&1; then
              pnpm install --frozen-lockfile
              pnpm build
            else
              npm ci
              npm run build
            fi
          '''
        }
      }
    }
  }

  post {
    always {
      dir("${BACKEND_DIR}") {
        sh 'docker compose -f .ci/docker-compose.test.yml down -v || true'
      }
      archiveArtifacts artifacts: "${BACKEND_DIR}/build/**, ${BACKEND_DIR}/junit.xml", allowEmptyArchive: true
      junit allowEmptyResults: true, testResults: "${BACKEND_DIR}/junit.xml"
    }
    success {
      echo 'Pipeline succeeded'
    }
    failure {
      echo 'Pipeline failed'
    }
  }
}
