
pipeline {
    agent {
        label 'ansible'
    }

    stages {
        stage('Clone repository') {
            steps {
                git branch: 'main',
                    url: 'https://github.com/BlackRoseGarotte/automation.git'
            }
        }

        stage('Configure test server') {
            steps {
                dir('lab05/ansible') {
                    sh '''
                        ansible-playbook -i hosts.ini setup_test_server.yml
                    '''
                }
            }
        }
    }

    post {
        success {
            echo 'Test server configured successfully!'
        }
        failure {
            echo 'Test server configuration failed!'
        }
    }
}
