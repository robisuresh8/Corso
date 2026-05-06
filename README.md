## 📂 Project Structure

```
app/
 ├── Controllers/
 ├── Models/
 ├── Services/
 ├── Filters/
 ├── Config/
 └── Database/

public/
system/
tests/
composer.json
spark
```

Architecture overview:

- Controllers → Handle HTTP requests
- Services → Contain business logic
- Models → Interact with database
- Filters → Handle authentication & authorization

---

## ⚙️ Installation

### 1️⃣ Clone Repository

```bash
git clone https://github.com/kuhiteleena/Corso_e-learning.git
cd Corso_e-learning
```

### 2️⃣ Install Dependencies

```bash
composer install
```

### 3️⃣ Setup Environment File

Copy environment file:

```bash
cp env .env
```

Update `.env`:

```
app.baseURL = 'http://localhost/ci4/public'

database.default.hostname = localhost
database.default.database = corso
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

```

---

### 4️⃣ Run Migrations

```bash
php spark migrate
```

(Optional) Run seeders:

```bash
php spark db:seed SeederName
```

---

### 5️⃣ Start Development Server

```bash
php spark serve
```

Server will run at:

```
http://localhost:8080
```

---

## 🔐 Authentication

This API uses JWT authentication.

Login endpoint returns an access token.

Send token in request header:

```
Authorization: Bearer <your_token>
```

---

## 🧪 Running Tests

```bash
php spark test
```

---

## 👥 Roles

| Role    | Access Level |
|---------|--------------|
| Admin   | Full system access |
| Student | Limited access (courses, quizzes, profile) |

---

## 📌 Git Workflow

- `main` branch is protected
- Use feature branches for development
- Pull requests required before merging

---

## 🛡 Security Notes

- `.env` file is ignored in Git
- Do NOT commit real production credentials
- Store JWT secret securely