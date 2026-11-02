# PHP SQLite Pages — Jenkins CI laboratory

Small PHP website with SQLite CRUD and a custom unit-test runner. Suitable for the Jenkins Controller + SSH Agent assignment.

## Structure

- `site/modules/database.php`: `Database` with `Execute`, `Fetch`, `Create`, `Read`, `Update`, `Delete`, `Count`.
- `site/modules/page.php`: `Page` with `Render`.
- `site/config.php`, `site/index.php`, `site/templates/index.tpl`, `site/styles/style.css`: website.
- `sql/schema.sql`: sample database and 3 pages.
- `tests/testframework.php`, `tests/tests.php`: 14 self-contained tests.
- `Dockerfile`: optional standalone PHP website image.
- `Dockerfile.jenkins-agent`: PHP+SQLite image for Jenkins SSH Agent.
- `Jenkinsfile`: Jenkins pipeline on `php-agent`.

## Run the website using Docker

From the project directory:

```bash
docker build -t php-sqlite-pages .
docker run --rm -p 8081:80 php-sqlite-pages
```

Visit `http://localhost:8081/?page=1` (also pages 2 and 3). No external database server is needed.

## Run tests locally

Requires PHP CLI with `pdo_sqlite` extension:

```bash
php tests/tests.php
```

Expected: `RESULT: 14 / 14` and exit status 0. Tests create a new temporary SQLite database from `sql/schema.sql` and delete it after the run. A failed assertion causes exit status 1 so Jenkins correctly marks the build as FAILED.

## Jenkins setup

1. Create Jenkins Controller and SSH Agent using the assignment's `docker-compose.yml` (in a separate `jenkins-lab` directory).
2. In the agent's build definition use `Dockerfile.jenkins-agent` from this project (copy the file to the Jenkins Compose directory, or change its build context).
3. Register an agent with label `php-agent`.
4. Push this project to GitHub. Create a Pipeline job with **Pipeline script from SCM**, select **Git**, enter repository URL and use script path `Jenkinsfile`.
5. Run **Build Now**. The pipeline checks dependencies and executes `php tests/tests.php` on the SSH Agent.

For public GitHub repositories HTTPS cloning works without Git credentials. For private repositories, provide appropriate Jenkins credentials.

### Notes

- The Jenkins SSH key and the Jenkins Compose `.env` belong in the separate Jenkins infrastructure project, not in this PHP repository.
- This project does **not** require Composer/PHPUnit. The custom test framework meets the assignment's requirement for unit tests.
- For a production application, use proper migrations, authentication, templating, and persistent database storage rather than this training configuration.
