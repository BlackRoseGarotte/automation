pipeline {
    agent {
        label 'php'
    }

    options {
        skipDefaultCheckout()
    }

    stages {
        stage('Clone repository') {
            steps {
                checkout scm
            }
        }

        stage('Install dependencies') {
            steps {
                dir('lab05') {
                    sh 'php -v'
                    sh 'composer install --no-interaction --prefer-dist'
                }
            }
        }

        stage('Run unit tests') {
            steps {
                dir('lab05') {
                    sh 'mkdir -p reports'
                    sh 'vendor/bin/phpunit --log-junit reports/results.xml'
                }
            }

            post {
                always {
                    junit 'lab05/reports/results.xml'
                }
            }
        }
    }

    post {
        success {
            echo 'All PHP tests passed successfully!'
        }

        failure {
            echo 'Build or tests failed.'
        }

        always {
            echo 'Pipeline completed.'
        }
    }
}
