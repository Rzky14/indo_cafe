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

```typescript
// ❌ BAD
class UserService {
  register() { /* register + send email + log */ }
}

// ✅ GOOD
class UserService {
  register() { /* only registration logic */ }
}
class EmailService {
  sendWelcomeEmail() { /* only email sending */ }
}
```

#### **O - Open/Closed Principle**
- Open for extension, closed for modification
- Gunakan interfaces dan abstract classes

```typescript
// ✅ GOOD
interface PaymentGateway {
  processPayment(amount: number): Promise<PaymentResult>;
}

class MidtransGateway implements PaymentGateway {
  processPayment(amount: number) { /* Midtrans specific */ }
}

class XenditGateway implements PaymentGateway {
  processPayment(amount: number) { /* Xendit specific */ }
}
```

#### **L - Liskov Substitution Principle**
- Subclass harus bisa menggantikan parent class tanpa breaking functionality
- Jangan override methods dengan behavior yang berbeda drastis

#### **I - Interface Segregation Principle**
- Jangan paksa class implement interface yang tidak digunakan
- Pecah interface besar menjadi interface-interface kecil yang spesifik

```typescript
// ❌ BAD
interface User {
  login();
  logout();
  generateReport(); // Admin only
  manageInventory(); // Admin only
}

// ✅ GOOD
interface Authenticatable {
  login();
  logout();
}

interface AdminCapabilities {
  generateReport();
  manageInventory();
}
```

#### **D - Dependency Inversion Principle**
- High-level modules tidak boleh depend on low-level modules
- Keduanya harus depend on abstractions

```typescript
// ✅ GOOD
class OrderService {
  constructor(
    private paymentGateway: PaymentGateway, // abstraction
    private notificationService: NotificationService // abstraction
  ) {}
}
```

### **4. Folder Structure Rules**

#### **Backend Structure**
```
backend/src/
├── modules/
│   └── [feature]/
│       ├── [feature].controller.ts   # HTTP request handling
│       ├── [feature].service.ts      # Business logic
│       ├── [feature].repository.ts   # Data access layer
│       ├── [feature].routes.ts       # Route definitions
│       ├── [feature].validation.ts   # Zod schemas
│       ├── [feature].types.ts        # TypeScript interfaces
│       └── [feature].test.ts         # Unit tests
├── common/
│   ├── middleware/
│   ├── utils/
│   ├── interfaces/
│   └── constants/
└── config/
```

**Penjelasan Layer:**
- **Controller**: Hanya handle HTTP request/response, validation, dan panggil service
- **Service**: Business logic, orchestration, transaction management
- **Repository**: Database queries, ORM operations, data mapping
- **Validation**: Zod schemas untuk input validation
- **Types**: TypeScript interfaces dan types

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

#### **TypeScript**
- **NO `any` type** - gunakan `unknown` jika tidak tahu type
- Semua function harus memiliki explicit return type
- Gunakan `strict: true` di tsconfig.json

```typescript
// ❌ BAD
function getUser(id: any): any {
  return db.user.findUnique({ where: { id } });
}

// ✅ GOOD
async function getUser(id: string): Promise<User | null> {
  return await db.user.findUnique({ where: { id } });
}
```

#### **Error Handling**
- Gunakan custom error classes
- Jangan expose internal error ke client
- Log semua errors dengan context

```typescript
// ✅ GOOD
class AppError extends Error {
  constructor(
    public statusCode: number,
    public message: string,
    public isOperational = true
  ) {
    super(message);
  }
}

throw new AppError(404, 'User not found');
```

#### **Naming Conventions**
- **Files**: kebab-case (`user-service.ts`)
- **Classes**: PascalCase (`UserService`)
- **Functions/Variables**: camelCase (`getUserById`)
- **Constants**: UPPER_SNAKE_CASE (`MAX_RETRY_COUNT`)
- **Interfaces**: PascalCase with 'I' prefix optional (`IUser` atau `User`)
- **Types**: PascalCase (`UserRole`)

#### **Comments & Documentation**
- Gunakan JSDoc untuk public APIs
- Comment HANYA untuk "why", bukan "what"
- Code harus self-documenting

```typescript
/**
 * Validates and processes payment through Midtrans gateway
 * @param orderId - The unique order identifier
 * @param amount - Payment amount in IDR
 * @returns Payment transaction result
 * @throws {AppError} When payment validation fails
 */
async function processPayment(orderId: string, amount: number): Promise<PaymentResult> {
  // We use snap token instead of direct charge to support multiple payment methods
  const snapToken = await midtrans.createSnapToken(orderId, amount);
  return { snapToken };
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

```typescript
// ✅ GOOD - Pagination
async function getOrders(page: number = 1, limit: number = 20) {
  return await db.order.findMany({
    skip: (page - 1) * limit,
    take: limit,
    orderBy: { createdAt: 'desc' }
  });
}
```

### **9. Security Checklist**

- [ ] Input validation dengan Zod
- [ ] SQL Injection prevention (gunakan Prisma ORM)
- [ ] XSS prevention (sanitize user input)
- [ ] CSRF protection
- [ ] Rate limiting pada authentication endpoints
- [ ] Password hashing dengan bcrypt (min cost factor 12)
- [ ] JWT dengan expiry time (access token: 15min, refresh token: 7 days)
- [ ] HTTPS only di production
- [ ] Environment variables untuk secrets

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
3. **NO `any` type in TypeScript**
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
