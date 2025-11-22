# GitHub Copilot Instructions - Indo Cafe Project

## 🎯 **Core Rules**

### **1. Always Read Project Documentation**
- **WAJIB** membaca `docs/prd.md` sebelum memulai task apapun
- **WAJIB** membaca `docs/plan.md` untuk mengecek task mana yang sudah dan belum dikerjakan
- Pahami konteks bisnis dan requirement sebelum menulis code

### **2. Task Management Protocol**
- **HANYA** centang/mark task sebagai completed (`✅`) ketika **Author EXPLICITLY menyetujui dan mengkonfirmasi** bahwa task tersebut sudah selesai
- Jangan pernah mark task sebagai done tanpa approval dari Author
- Selalu referensikan Task ID (contoh: `IC-105`) dalam commit message dan pull request
- Update status task di todo list secara real-time

### **3. SOLID Principles - MANDATORY**
Semua code **HARUS** mengikuti prinsip SOLID:

#### **S - Single Responsibility Principle**
- Setiap class/function hanya memiliki satu tanggung jawab
- Contoh: `AuthService` hanya handle authentication logic, bukan juga handle email sending

```php
// ❌ BAD
class UserService {
  public function register() { 
    // register + send email + log in one method
  }
}

// ✅ GOOD
class UserService {
  public function register() { 
    // only registration logic
  }
}
class EmailService {
  public function sendWelcomeEmail() { 
    // only email sending
  }
}
```

#### **O - Open/Closed Principle**
- Open for extension, closed for modification
- Gunakan interfaces dan abstract classes

```php
// ✅ GOOD
interface PaymentGateway {
  public function processPayment(int $amount): PaymentResult;
}

class MidtransGateway implements PaymentGateway {
  public function processPayment(int $amount): PaymentResult { 
    // Midtrans specific implementation
  }
}

class XenditGateway implements PaymentGateway {
  public function processPayment(int $amount): PaymentResult { 
    // Xendit specific implementation
  }
}
```

#### **L - Liskov Substitution Principle**
- Subclass harus bisa menggantikan parent class tanpa breaking functionality
- Jangan override methods dengan behavior yang berbeda drastis

#### **I - Interface Segregation Principle**
- Jangan paksa class implement interface yang tidak digunakan
- Pecah interface besar menjadi interface-interface kecil yang spesifik

```php
// ❌ BAD
interface User {
  public function login();
  public function logout();
  public function generateReport(); // Admin only
  public function manageInventory(); // Admin only
}

// ✅ GOOD
interface Authenticatable {
  public function login();
  public function logout();
}

interface AdminCapabilities {
  public function generateReport();
  public function manageInventory();
}
```

#### **D - Dependency Inversion Principle**
- High-level modules tidak boleh depend on low-level modules
- Keduanya harus depend on abstractions

```php
// ✅ GOOD - Laravel Dependency Injection
class OrderService {
  public function __construct(
    private PaymentGateway $paymentGateway,
    private NotificationService $notificationService
  ) {}
}

// Atau dengan method injection
class OrderController extends Controller {
  public function store(StoreOrderRequest $request, OrderService $orderService) {
    return $orderService->createOrder($request->validated());
  }
}
```

### **4. Folder Structure Rules**

#### **Backend Structure (Laravel)**
```
backend/app/
├── Http/
│   ├── Controllers/Api/
│   │   └── [Feature]/
│   │       └── [Feature]Controller.php   # HTTP request handling
│   ├── Requests/
│   │   └── [Feature]/
│   │       └── Store[Feature]Request.php # Form validation
│   ├── Resources/
│   │   └── [Feature]Resource.php         # API Response transformer
│   └── Middleware/
│       └── CheckRole.php                 # Custom middleware
├── Models/
│   └── [Feature].php                     # Eloquent Model
├── Services/
│   └── [Feature]/
│       └── [Feature]Service.php          # Business logic layer
├── Repositories/ (Optional)
│   └── [Feature]Repository.php           # Data access layer
└── Traits/
    └── [Feature]Trait.php                # Reusable methods
```

**Penjelasan Layer:**
- **Controller**: Hanya handle HTTP request/response dan panggil service/repository
- **Request (Form Request)**: Validation rules dengan Laravel validation
- **Service**: Business logic, orchestration, transaction management
- **Repository (Optional)**: Complex database queries, data mapping
- **Resource**: Transform Eloquent models ke JSON response
- **Model**: Eloquent ORM model dengan relationships

#### **Frontend Structure**
```
frontend/src/
├── features/
│   └── [feature]/
│       ├── components/          # Feature-specific components
│       ├── hooks/               # Custom hooks
│       ├── services/            # API calls
│       ├── store/               # Redux slices
│       ├── types/               # TypeScript types
│       └── utils/               # Helper functions
├── components/
│   ├── ui/                      # Reusable UI primitives
│   └── layout/                  # Layout components
├── services/
│   └── api.ts                   # API client configuration
└── store/
    └── store.ts                 # Redux store setup
```

### **5. Code Quality Standards**

#### **PHP/Laravel**
- **Type Hints**: Gunakan type hints untuk semua parameters dan return types (PHP 8.2+)
- **Strict Types**: Declare `declare(strict_types=1);` di setiap file PHP
- **No mixed types**: Hindari mixed type, gunakan union types jika perlu

```php
// ❌ BAD
function getUser($id) {
  return User::find($id);
}

// ✅ GOOD
declare(strict_types=1);

public function getUser(string $id): ?User {
  return User::find($id);
}
```

#### **Error Handling**
- Gunakan custom error classes
- Jangan expose internal error ke client
- Log semua errors dengan context

```php
// ✅ GOOD - Laravel Custom Exception
namespace App\Exceptions;

use Exception;

class AppException extends Exception {
  public function __construct(
    string $message,
    int $statusCode = 400,
    ?Exception $previous = null
  ) {
    parent::__construct($message, $statusCode, $previous);
  }
  
  public function render($request) {
    return response()->json([
      'success' => false,
      'message' => $this->getMessage()
    ], $this->getCode());
  }
}

// Usage
throw new AppException('User not found', 404);
```

#### **Naming Conventions (Laravel PSR-12)**
- **Files**: PascalCase untuk classes (`UserService.php`), kebab-case untuk views
- **Classes**: PascalCase (`UserService`, `MenuController`)
- **Methods/Variables**: camelCase (`getUserById`, `$userName`)
- **Constants**: UPPER_SNAKE_CASE (`MAX_RETRY_COUNT`)
- **Database Tables**: snake_case plural (`users`, `menu_items`, `order_items`)
- **Model Properties**: snake_case (`$table`, `$fillable`)
- **Routes**: kebab-case (`/api/menu-items`, `/api/user-profile`)

#### **Comments & Documentation**
- Gunakan JSDoc untuk public APIs
- Comment HANYA untuk "why", bukan "what"
- Code harus self-documenting

```php
/**
 * Validates and processes payment through Midtrans gateway
 * 
 * @param string $orderId The unique order identifier
 * @param int $amount Payment amount in IDR
 * @return PaymentResult Payment transaction result
 * @throws AppException When payment validation fails
 */
public function processPayment(string $orderId, int $amount): PaymentResult {
  // We use snap token instead of direct charge to support multiple payment methods
  $snapToken = $this->midtrans->createSnapToken($orderId, $amount);
  
  return new PaymentResult(['snapToken' => $snapToken]);
}
```

### **6. Git Workflow**

#### **Branching**
- Selalu buat branch dari `develop` (bukan `main`)
- Naming: `feature/[feature-name]`, `bugfix/IC-XXX-description`
- Satu branch = satu feature complete

#### **Commits**
- Format: `[IC-XXX] type: description`
- Atomic commits - satu commit = satu logical change
- Commit message harus descriptive

```bash
# ✅ GOOD
git commit -m "[IC-105] feat: Add JWT authentication middleware

- Implement token verification
- Add role-based access control
- Add token refresh logic"

# ❌ BAD
git commit -m "update code"
```

#### **Pull Request**
- PR title: `[IC-XXX] Feature: Description`
- Isi PR harus mencantumkan:
  - Task ID dan description
  - What changed
  - How to test
  - Screenshots (untuk UI changes)
  - Checklist DoD (Definition of Done)

### **7. Testing Requirements**

- **Unit tests**: Coverage minimal 70%
- **Integration tests**: Untuk semua API endpoints
- **E2E tests**: Untuk critical user flows (login, checkout, payment)

```typescript
// ✅ GOOD Test Structure
describe('AuthService', () => {
  describe('login', () => {
    it('should return JWT token when credentials are valid', async () => {
      // Arrange
      const email = 'test@example.com';
      const password = 'password123';
      
      // Act
      const result = await authService.login(email, password);
      
      // Assert
      expect(result).toHaveProperty('token');
      expect(result.token).toBeTruthy();
    });

    it('should throw error when password is incorrect', async () => {
      // Arrange
      const email = 'test@example.com';
      const password = 'wrongpassword';
      
      // Act & Assert
      await expect(authService.login(email, password))
        .rejects.toThrow('Invalid credentials');
    });
  });
});
```

### **8. Performance Guidelines**

- Images: Optimize dan lazy load
- API calls: Implement caching dengan Redis
- Database: Gunakan indexes untuk query yang sering
- Frontend: Code splitting dan lazy loading components
- API response: Pagination untuk list endpoints (max 50 items per page)

```php
// ✅ GOOD - Laravel Pagination
public function getOrders(int $perPage = 20): LengthAwarePaginator {
  return Order::with(['items', 'user'])
    ->latest()
    ->paginate($perPage);
}

// ✅ GOOD - Eager Loading (prevent N+1)
public function getOrdersWithItems(): Collection {
  return Order::with(['items.menu', 'user', 'payment'])->get();
}
```

### **9. Security Checklist**

- [ ] Input validation dengan Laravel Form Request
- [ ] SQL Injection prevention (gunakan Eloquent ORM)
- [ ] XSS prevention (sanitize user input dengan Laravel purifier)
- [ ] CSRF protection (Laravel built-in)
- [ ] Rate limiting pada authentication endpoints (Laravel Throttle)
- [ ] Password hashing dengan bcrypt/argon2 (Laravel Hash facade)
- [ ] Laravel Sanctum token dengan expiry time
- [ ] HTTPS only di production
- [ ] Environment variables untuk secrets (.env file)

### **10. Code Review Checklist**

Sebelum submit PR, pastikan:
- [ ] Code mengikuti SOLID principles
- [ ] Tidak ada hardcoded values (gunakan env variables)
- [ ] Error handling lengkap
- [ ] Tests ditulis dan passing
- [ ] TypeScript types complete (no `any`)
- [ ] Responsive design tested (mobile & desktop)
- [ ] No console.log di production code
- [ ] Comments dan documentation adequate
- [ ] Performance considerations addressed
- [ ] Security best practices followed

### **11. API Design Standards**

#### **RESTful Conventions**
```
GET    /api/v1/menus          # List all menus
GET    /api/v1/menus/:id      # Get menu detail
POST   /api/v1/menus          # Create menu
PUT    /api/v1/menus/:id      # Update menu
DELETE /api/v1/menus/:id      # Delete menu
```

#### **Response Format**
```typescript
// ✅ Success Response
{
  "success": true,
  "data": { /* actual data */ },
  "message": "Menu created successfully"
}

// ✅ Error Response
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input data",
    "details": [
      { "field": "price", "message": "Price must be positive" }
    ]
  }
}

// ✅ Paginated Response
{
  "success": true,
  "data": [ /* items */ ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "totalPages": 8
  }
}
```

### **12. Mobile-First CSS**

```css
/* ✅ GOOD - Mobile first, then desktop */
.menu-card {
  /* Mobile styles (default) */
  width: 100%;
  padding: 1rem;
}

@media (min-width: 768px) {
  .menu-card {
    /* Tablet & Desktop styles */
    width: 50%;
    padding: 1.5rem;
  }
}
```

---

## **🚨 Critical Reminders**

1. **NEVER mark task as done without Author approval**
2. **ALWAYS follow SOLID principles**
3. **ALWAYS use strict types in PHP** (`declare(strict_types=1)`)
4. **ALWAYS read PRD and Plan before coding**
5. **Write tests for every feature**
6. **Mobile-first responsive design**
7. **Security first mindset**
8. **Performance matters**

---

## **📚 Reference Documents**

- Product Requirements: `docs/prd.md`
- Development Plan: `docs/plan.md`
- API Documentation: `docs/api/` (will be created)

---

## **💬 Communication**

- Selalu komunikasikan blocker atau pertanyaan ke Author
- Jangan assume requirements - tanya jika tidak clear
- Update progress secara berkala
- Report bugs atau security issues immediately

---

**Last Updated:** 22 November 2025  
**Version:** 1.0
