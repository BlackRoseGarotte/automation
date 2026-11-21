pipeline {
    agent {
        label 'ansible'
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

        stage('Deploy PHP project') {
            steps {
                dir('lab05/ansible') {
                    sh '''
                        ansible-playbook -i hosts.ini deploy_php.yml
                    '''
                }
            }
        }
    }

    post {
        success {
            echo 'PHP application deployed successfully!'
        }

        failure {
            echo 'Deployment failed.'
        }
    }
}
