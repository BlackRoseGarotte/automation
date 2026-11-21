# Jenkins CI/CD Automation with Ansible

## 1. Project description

The project extends Individual Work 04 by introducing automated server configuration and PHP application deployment using Ansible.

The PHP application uses SQLite for data storage and includes `Database` and `Page` classes. The existing automated tests were adapted to PHPUnit.

The infrastructure consists of four Docker containers: Jenkins Controller, SSH Agent, Ansible Agent, and Test Server.

## 2. Steps for setting up Jenkins Controller

A `compose.yaml` file was created with the `jenkins-controller` service based on the `jenkins/jenkins:lts` image.

The controller exposes ports `8080` and `50000`, uses persistent volume storage, and connects to the `jenkins-network` network.

The initial administrator password was retrieved, the necessary Jenkins plugins were installed, and an administrator account was created.

## 3. Steps for setting up SSH Agent

The SSH Agent configuration from Individual Work 04 was reused and extended with Composer and additional PHP extensions.

The existing SSH key pair was used for authentication. The private key was registered in Jenkins Credentials, and a permanent SSH agent was configured through the Jenkins web interface.

## 4. Steps for creating and configuring Ansible Agent

A `Dockerfile.ansible_agent` file based on Ubuntu 24.04 was created. Ansible, Python, Git, OpenSSH, sudo, and OpenJDK 21 were installed.

Two separate SSH key pairs were generated for connections between:

- Jenkins Controller and Ansible Agent.
- Ansible Agent and Test Server.

A permanent agent named `ansible-agent` with the label `ansible` was created in Jenkins.

![alt](images/jenkins_agents.png)

## 5. Steps for creating the Test Server

A `Dockerfile.test_server` file based on Ubuntu 24.04 was created with OpenSSH Server, Python, and sudo.

A dedicated `ansible` user was created with passwordless sudo privileges and SSH key authentication.

All four containers were started using:

```bash
docker compose up -d --build
```

![alt](images/docker_containers.png)

The Test Server exposes port `80`, mapped to port `8081` on the host machine.

## 6. Ansible playbook and its tasks

An `ansible` directory was created containing the `hosts.ini` inventory file and two playbooks.

The `setup_test_server.yml` playbook performs the following tasks:

1. Updates the APT package cache.
2. Installs Apache2, PHP, and the required extensions.
3. Creates the application directory.
4. Configures the Apache VirtualHost.
5. Starts and restarts Apache.

The playbook was tested manually using:

```bash
docker exec -u jenkins ansible-agent ansible-playbook -i /home/jenkins/ansible/hosts.ini /home/jenkins/ansible/setup_test_server.yml
```

![alt](images/ansible_playbook.png)

The `deploy_php.yml` playbook was also created to copy application files, initialize the SQLite database, configure file permissions, and restart Apache.

## 7. Steps for creating and configuring Jenkins pipelines

Three Pipeline files were created in the `lab05/pipelines` directory.

All pipelines use the following Git repository configuration:

- **Repository:** `https://github.com/BlackRoseGarotte/automation.git`
- **Branch:** `main`

### PHP Build and Test Pipeline

- **Script Path:** `lab05/pipelines/php_build_and_test_pipeline.groovy`

The Pipeline clones the repository, installs dependencies using Composer, executes PHPUnit tests, and publishes test results using JUnit.

![alt](images/php_build_and_test.png)

![alt](images/test_results.png)

### Ansible Setup Pipeline

- **Script Path:** `lab05/pipelines/ansible_setup_pipeline.groovy`
- **Agent label:** `ansible`

The Pipeline clones the repository and executes the `setup_test_server.yml` playbook to configure the Test Server.

![alt](images/ansible_setup.png)

### PHP Deployment Pipeline

- **Script Path:** `lab05/pipelines/php_deploy_pipeline.groovy`
- **Agent label:** `ansible`

The Pipeline clones the repository and executes the `deploy_php.yml` playbook.

The deployment was completed successfully with no failed or unreachable hosts.

![alt](images/php_deploy.png)

The deployed application was tested in a web browser at `http://localhost:8081`. All three pages were displayed correctly using data stored in the SQLite database.

![alt](images/application.png)

## 8. Control questions

**1. What are the advantages of using Ansible for server configuration?**

Ansible automates repetitive configuration tasks, reduces human errors, and ensures consistent server configurations. It uses readable YAML playbooks, supports idempotency, and does not require a dedicated agent on the managed server.

**2. What other Ansible modules exist for configuration management?**

- `user` — manages user accounts.
- `group` — manages user groups.
- `template` — generates configuration files from templates.
- `lineinfile` — modifies individual lines in configuration files.
- `package` — installs and removes software packages.
- `cron` — manages scheduled tasks.
- `systemd_service` — manages systemd services.

**3. What problems did you encounter when creating the Ansible playbook and how did you solve them?**

The initial problem was an inaccessible inventory file inside the Ansible Agent container. It was resolved by correcting the Docker volume configuration and filling in the missing inventory parameters. Another problem was SSH host key verification failure. It was resolved by adding the Test Server to the `known_hosts` file. After resolving these issues, the server configuration and application deployment were completed successfully.