# 🍵 Indo Cafe | Rasa Nusantara, Gaya Masa Kini

Website kafe modern dengan tema Nusantara yang menggabungkan sistem pemesanan online, manajemen menu, dan dashboard admin.

## 📋 Tech Stack

### Backend
- **Framework:** Laravel 12.0
- **Language:** PHP 8.2+
- **Database:** MySQL 8.0+
- **Authentication:** Laravel Sanctum
- **Server:** Laravel Development Server / Apache (Laragon)

### Frontend
- **Framework:** React 18.3
- **Language:** TypeScript
- **Build Tool:** Vite 6.0
- **Styling:** Tailwind CSS 3.4
- **State Management:** Redux Toolkit
- **Form Handling:** React Hook Form + Zod
- **HTTP Client:** Axios

## 🚀 Prerequisites

Pastikan sudah terinstall:
- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **npm** >= 9.x
- **MySQL** >= 8.0
- **Laragon** (recommended for Windows) atau XAMPP/MAMP

## 📦 Installation

### 1. Clone Repository

```bash
git clone https://github.com/Rzky14/indo_cafe.git
cd indo_cafe
```

### 2. Backend Setup (Laravel)

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database di .env
# DB_DATABASE=indo_cafe
# DB_USERNAME=root
# DB_PASSWORD=

# Create database (via MySQL client atau phpMyAdmin)
# CREATE DATABASE indo_cafe;

# Run migrations
php artisan migrate

# (Optional) Seed database
php artisan db:seed

# Start Laravel development server
php artisan serve
# Server akan berjalan di http://localhost:8000
```

### 3. Frontend Setup (React + Vite)

```bash
cd ../frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Start development server
npm run dev
# Frontend akan berjalan di http://localhost:5173
```

## 🗂️ Project Structure

```
indo_cafe/
├── backend/                 # Laravel 12 Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/     # API Controllers (per feature)
│   │   │   ├── Requests/    # Form Request Validation
│   │   │   ├── Resources/   # API Response Transformers
│   │   │   └── Middleware/
│   │   ├── Models/          # Eloquent Models
│   │   ├── Services/        # Business Logic Layer
│   │   ├── Repositories/    # Data Access Layer (Optional)
│   │   └── Traits/          # Reusable Traits
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   └── routes/
│       └── api.php          # API Routes
│
├── frontend/                # React + TypeScript Frontend
│   ├── src/
│   │   ├── features/        # Feature-based modules
│   │   │   ├── auth/
│   │   │   ├── menu/
│   │   │   ├── cart/
│   │   │   └── orders/
│   │   ├── components/      # Shared components
│   │   │   ├── ui/          # UI primitives
│   │   │   └── layout/      # Layout components
│   │   ├── services/        # API services
│   │   ├── store/           # Redux store
│   │   ├── hooks/           # Custom hooks
│   │   └── types/           # TypeScript types
│   └── public/
│
└── docs/                    # Project documentation
    ├── prd.md              # Product Requirements
    └── plan.md             # Development Plan
```

## 🔧 Configuration

### Backend Configuration (.env)

```env
APP_NAME="Indo Cafe"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=indo_cafe
DB_USERNAME=root
DB_PASSWORD=
```

### Frontend Configuration (.env)

```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=Indo Cafe
```

## 🎨 Development Guidelines

### SOLID Principles

Project ini mengikuti **SOLID principles** untuk maintainability:

- **S**ingle Responsibility: Setiap class/function satu tanggung jawab
- **O**pen/Closed: Open for extension, closed for modification
- **L**iskov Substitution: Subclass dapat menggantikan parent class
- **I**nterface Segregation: Interface yang spesifik dan fokus
- **D**ependency Inversion: Depend on abstractions, bukan konkrit

### Coding Standards

#### PHP/Laravel
- Type hints untuk semua parameters dan return types
- Declare `strict_types=1` di setiap file
- Laravel PSR-12 naming conventions
- Gunakan Form Request untuk validation
- Gunakan Resource classes untuk API responses

#### TypeScript/React
- Strict TypeScript mode enabled
- No `any` type allowed
- Functional components dengan hooks
- Props typing dengan interfaces
- Consistent file naming (kebab-case)

### Git Workflow

```bash
# Create feature branch
git checkout -b feature/nama-fitur

# Commit dengan format
git commit -m "[IC-XXX] type: description"

# Types: feat, fix, refactor, style, docs, test, chore

# Push ke remote
git push origin feature/nama-fitur
```

## 📝 Available Scripts

### Backend

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Create new migration
php artisan make:migration create_table_name

# Create controller
php artisan make:controller Api/Feature/FeatureController

# Create model
php artisan make:model ModelName -m

# Create service
php artisan make:service Feature/FeatureService

# Run tests
php artisan test
```

### Frontend

```bash
# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Run linter
npm run lint

# Type check
tsc --noEmit
```

## 🧪 Testing

### Backend Tests

```bash
cd backend
php artisan test
```

### Frontend Tests

```bash
cd frontend
npm run test
```

## 📚 Documentation

- **PRD:** `docs/prd.md` - Product Requirements Document
- **Plan:** `docs/plan.md` - Development Plan & Task List
- **API Docs:** Coming soon (Postman/Swagger)

## 🤝 Contributing

1. Read `docs/plan.md` untuk task yang available
2. Pilih task dan assign ke diri sendiri
3. Create branch sesuai format: `feature/task-name`
4. Follow coding standards dan SOLID principles
5. Write tests untuk new features
6. Submit Pull Request dengan:
   - Task ID di title `[IC-XXX] Feature: Description`
   - Description lengkap
   - Screenshots untuk UI changes

## 📞 Support

Jika ada pertanyaan atau issue:
- Check documentation di `docs/`
- Contact: [Your Email/Contact]

## 📄 License

This project is licensed under the MIT License.

---

**Built with ❤️ for Indo Cafe**  
*Rasa Nusantara, Gaya Masa Kini*
