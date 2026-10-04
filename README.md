# Task Flow API

RESTful API for a Task Management system (called Task Flow) built with PHP (using Slim Framework) and PostgreSQL, following Hexagonal Architecture and DDD principles.

This project serves as a robust backend foundation, demonstrating advanced architectural patterns, strict typing and comprehensive test coverage.

It provides complete management for Users, Boards and Tasks, including role-based permissions for board members.

## 🔑 Key Features

* **Hexagonal Architecture (Ports and Adapters):** Strict separation of concerns between Domain, Application (Use Cases), and Infrastructure layers.

* **Domain-Driven Design (DDD):** Rich domain entities, value objects, and repository interfaces.

* **100% Code Coverage:** Fully tested Use Cases and domain logic using PHPUnit with rigorous edge-case and exception handling.

* **JWT Authentication:** Secure stateless authentication for all private endpoints.

* **Interactive API Documentation:** Automatically generated OpenAPI/Swagger UI documentation via PHP attributes (`#[OA\...]`).

* **Custom Migration System:** Native PHP migration system to manage PostgreSQL database schemas without relying on heavy ORMs.

* **Containerized Environment:** Fully orchestrated local development environment using Docker and Docker Compose.

## 🛠️ Tech Stack

* **Language:** PHP 8.5
* **Framework:** Slim Framework 4
* **Database:** PostgreSQL 18
* **Testing:** PHPUnit 13.4
* **Documentation:** Swagger UI / `zircote/swagger-php`
* **Authentication:** Firebase JWT
* **Infrastructure:** Docker & Docker Compose

## 📂 Project Structure

```text
├── bin/                        # Native PHP migration CLI scripts
├── config/                     # Application and DI container configuration
├── database/migrations/        # Database schema migration files
├── docker/                     # Dockerfiles and container configurations
├── public/                     # Web server root (index.php) and Swagger UI
├── src/                        # Core application code
│   ├── Application/            # Use Cases (orchestration)
│   ├── Domain/                 # Entities, Enums, and Repository Interfaces
│   └── Infrastructure/         # Controllers, Persistence (PDO), Middlewares
├── tests/                      # PHPUnit test suites (Unit/Integration)
├── .env.example                # Environment variables template
├── composer.json               # PHP dependencies
└── docker-compose.yml          # Docker orchestration file
```

## ⚙️ Prerequisites

- Docker and Docker Compose
- Git

## 💻 Getting Started

**1. Clone the repository**
```bash
git clone git@github.com:j-rodrigueza-2018/task-flow-api.git
cd task-flow-api
```

**2. Setup environment variables**
```bash
cp .env.example .env
```
*Update the `.env` file with your desired database credentials and a secure `JWT_SECRET`.*

**3. Build and spin up the containers**
```bash
docker-compose up -d --build
```

**4. Install dependencies**
```bash
docker-compose exec php composer install
```

**5. Run database migrations**
```bash
docker-compose exec php php bin/migrate.php
```

## 🧪 Testing

The project maintains 100% code coverage. To run the test suite inside the container:

```bash
docker-compose exec php ./vendor/bin/phpunit tests/
```

## 📖 API Documentation

The API endpoints, request schemas, and responses are fully documented using OpenAPI 3.0. 

Once the Docker containers are running, you can access the interactive Swagger UI at:
- **http://localhost:8080/docs** *(If deployed to a remote server, replace `localhost:8080` with your server's domain or IP).*

Alternatively, the raw OpenAPI specification is available at `public/openapi.yaml`.

### Core Modules:
- **Auth/Users:** `POST /api/users`, `POST /api/login`
- **Boards:** `CRUD` operations, member assignments, and role checks (`/api/private/boards`).
- **Tasks:** `CRUD` operations, task status updates, and user assignments (`/api/private/tasks`).

## 📄 License

This project is licensed under the [MIT License](LICENSE).

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## 👨‍💻 Author

**Juan Rodríguez**
- GitHub: [@j-rodrigueza-2018](https://github.com/j-rodrigueza-2018)
