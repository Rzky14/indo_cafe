# **Development Plan - Indo Cafe Website**

**Project:** Indo Cafe | Rasa Nusantara, Gaya Masa Kini  
**Version:** 1.0  
**Date:** 22 November 2025

## **Overview**

Dokumen ini berisi rencana pengembangan lengkap untuk website Indo Cafe berdasarkan PRD yang telah disusun. Pembagian kerja dilakukan berdasarkan fitur utama dengan branch terpisah untuk memudahkan development paralel dan code review.

---

## **Tech Stack Decision**

### **Frontend**
- **Framework:** React.js 18+ dengan TypeScript
- **Styling:** Tailwind CSS
- **State Management:** Redux Toolkit + RTK Query
- **Form Handling:** React Hook Form + Zod
- **Routing:** React Router v6

### **Backend**
- **Framework:** Node.js dengan Express.js
- **Language:** TypeScript
- **ORM:** Prisma
- **Authentication:** JWT + bcrypt
- **Validation:** Zod
- **File Upload:** Multer + Sharp (image optimization)

### **Database**
- **Primary DB:** PostgreSQL 15+
- **Caching:** Redis (untuk session dan rate limiting)

### **Infrastructure**
- **Hosting:** DigitalOcean / VPS
- **CI/CD:** GitHub Actions
- **Storage:** Local storage atau S3-compatible (future)
- **Monitoring:** PM2 + Custom logging

### **Third-Party Services**
- **Payment Gateway:** Midtrans
- **Maps:** Google Maps API
- **Notification:** Email (Nodemailer) + WhatsApp Business API (future)

---

## **Project Structure (SOLID Principles)**

```
indo_cafe/
├── backend/
│   ├── src/
│   │   ├── config/          # Configuration files (database, env)
│   │   ├── modules/         # Feature modules
│   │   │   ├── auth/
│   │   │   │   ├── auth.controller.ts
│   │   │   │   ├── auth.service.ts
│   │   │   │   ├── auth.repository.ts
│   │   │   │   ├── auth.routes.ts
│   │   │   │   ├── auth.validation.ts
│   │   │   │   └── auth.types.ts
│   │   │   ├── users/
│   │   │   ├── menu/
│   │   │   ├── orders/
│   │   │   ├── payments/
│   │   │   ├── promotions/
│   │   │   ├── reviews/
│   │   │   └── reports/
│   │   ├── common/          # Shared utilities
│   │   │   ├── middleware/
│   │   │   ├── utils/
│   │   │   ├── interfaces/
│   │   │   └── constants/
│   │   ├── database/        # Prisma schema, migrations
│   │   └── app.ts           # Main application
│   ├── tests/
│   ├── prisma/
│   ├── package.json
│   └── tsconfig.json
├── frontend/
│   ├── src/
│   │   ├── features/        # Feature-based modules
│   │   │   ├── auth/
│   │   │   ├── menu/
│   │   │   ├── cart/
│   │   │   ├── orders/
│   │   │   ├── profile/
│   │   │   └── admin/
│   │   ├── components/      # Shared components
│   │   │   ├── ui/          # UI primitives
│   │   │   └── layout/      # Layout components
│   │   ├── services/        # API services
│   │   ├── store/           # Redux store
│   │   ├── hooks/           # Custom hooks
│   │   ├── utils/           # Utilities
│   │   ├── types/           # TypeScript types
│   │   ├── assets/          # Static assets
│   │   ├── App.tsx
│   │   └── main.tsx
│   ├── public/
│   ├── package.json
│   └── tsconfig.json
├── docs/
│   ├── prd.md
│   ├── plan.md
│   └── api/                 # API documentation
└── .github/
    ├── copilot-instructions.md
    └── workflows/
```

---

## **Development Phases & Branches**

### **Phase 0: Project Setup** `[SETUP]`
**Branch:** `feature/project-setup`

**Tasks:**
- IC-001: Initialize project repository dengan Git
- IC-002: Setup backend struktur folder (SOLID principles)
- IC-003: Setup frontend dengan Vite + React + TypeScript
- IC-004: Konfigurasi ESLint, Prettier, TypeScript
- IC-005: Setup Prisma dengan PostgreSQL
- IC-006: Setup environment variables (.env template)
- IC-007: Buat README.md dengan panduan setup development

---

### **Phase 1: Authentication & Authorization** `[AUTH]`
**Branch:** `feature/auth-system`

**Backend Tasks:**
- IC-101: Design database schema untuk users dan roles
- IC-102: Implement Prisma models (User, Role, Session)
- IC-103: Create auth middleware (JWT verification)
- IC-104: Implement register endpoint dengan validation
- IC-105: Implement login endpoint dengan password hashing
- IC-106: Implement logout endpoint
- IC-107: Implement refresh token mechanism
- IC-108: Implement role-based access control (RBAC)
- IC-109: Create password reset flow (email token)

**Frontend Tasks:**
- IC-111: Create auth slice (Redux)
- IC-112: Design login page UI (mobile-first)
- IC-113: Design register page UI
- IC-114: Implement login form dengan validation
- IC-115: Implement register form
- IC-116: Create protected route component
- IC-117: Implement token persistence (localStorage)
- IC-118: Create auth interceptor untuk API calls
- IC-119: Handle authentication errors

---

### **Phase 2: Menu Management System** `[MENU]`
**Branch:** `feature/menu-system`

**Backend Tasks:**
- IC-201: Design database schema (Menu, Category, MenuImage)
- IC-202: Implement Prisma models untuk menu
- IC-203: Create menu CRUD endpoints
- IC-204: Create category CRUD endpoints
- IC-205: Implement image upload middleware
- IC-206: Implement image optimization (Sharp)
- IC-207: Create menu search endpoint
- IC-208: Create menu filter endpoint (kategori, harga)
- IC-209: Implement stock availability toggle
- IC-210: Create "Best Seller" & "Seasonal" label logic

**Frontend Tasks:**
- IC-211: Create menu slice (Redux + RTK Query)
- IC-212: Design menu catalog page (grid layout, mobile-first)
- IC-213: Design menu detail page
- IC-214: Implement menu card component
- IC-215: Implement search functionality
- IC-216: Implement filter by category
- IC-217: Implement product detail view
- IC-218: Create loading skeleton untuk menu
- IC-219: Implement lazy loading untuk images

**Admin Tasks:**
- IC-221: Design admin menu management UI
- IC-222: Create menu form (add/edit)
- IC-223: Implement image upload component
- IC-224: Create category management interface
- IC-225: Implement stock toggle switch

---

### **Phase 3: Shopping Cart & Checkout** `[CART]`
**Branch:** `feature/cart-checkout`

**Backend Tasks:**
- IC-301: Design database schema (Cart, CartItem)
- IC-302: Implement Prisma models untuk cart
- IC-303: Create cart CRUD endpoints
- IC-304: Implement add to cart logic
- IC-305: Implement update cart item (quantity, notes)
- IC-306: Implement remove from cart
- IC-307: Create cart validation (stock availability)
- IC-308: Implement cart total calculation
- IC-309: Create checkout endpoint (pre-order validation)

**Frontend Tasks:**
- IC-311: Create cart slice (Redux)
- IC-312: Design cart page UI (mobile-first)
- IC-313: Design checkout page UI
- IC-314: Implement add to cart button & animation
- IC-315: Create cart item component (quantity controls)
- IC-316: Implement cart summary (subtotal, tax, total)
- IC-317: Create checkout form (alamat, metode layanan)
- IC-318: Implement address selection/creation
- IC-319: Create order summary component
- IC-320: Implement voucher input field

---

### **Phase 4: Order Management** `[ORDERS]`
**Branch:** `feature/order-management`

**Backend Tasks:**
- IC-401: Design database schema (Order, OrderItem, OrderStatus)
- IC-402: Implement Prisma models untuk orders
- IC-403: Create order creation endpoint
- IC-404: Implement order status machine (FSM pattern)
- IC-405: Create order listing endpoints (user & admin)
- IC-406: Implement order detail endpoint
- IC-407: Create order status update endpoint
- IC-408: Implement order cancellation logic
- IC-409: Create real-time notification system (WebSocket/SSE)
- IC-410: Implement order history dengan pagination

**Frontend Customer Tasks:**
- IC-411: Create order slice (Redux + RTK Query)
- IC-412: Design order history page
- IC-413: Design order detail page
- IC-414: Design order tracking component
- IC-415: Implement order status badges
- IC-416: Implement order cancellation dialog
- IC-417: Create real-time order updates (WebSocket client)

**Frontend Admin Tasks:**
- IC-421: Design admin order dashboard
- IC-422: Create order list dengan filters (status, tanggal)
- IC-423: Implement order detail view (admin)
- IC-424: Create quick action buttons (terima, proses, selesai)
- IC-425: Implement real-time notification sound/visual
- IC-426: Create order print view (kitchen receipt)

---

### **Phase 5: Payment Integration** `[PAYMENT]`
**Branch:** `feature/payment-system`

**Backend Tasks:**
- IC-501: Design database schema (Payment, PaymentMethod)
- IC-502: Implement Prisma models untuk payments
- IC-503: Setup Midtrans SDK integration
- IC-504: Create payment initialization endpoint
- IC-505: Implement payment callback/webhook handler
- IC-506: Create payment verification logic
- IC-507: Implement payment expiration handling
- IC-508: Create payment status sync endpoint
- IC-509: Implement payment retry logic
- IC-510: Create payment receipt generation

**Frontend Tasks:**
- IC-511: Create payment slice (Redux)
- IC-512: Design payment method selection UI
- IC-513: Implement Midtrans Snap integration
- IC-514: Create payment waiting page
- IC-515: Implement payment status polling
- IC-516: Design payment success page
- IC-517: Design payment failed page
- IC-518: Create payment receipt view
- IC-519: Implement payment timeout handling

---

### **Phase 6: Promotion & Voucher System** `[PROMO]`
**Branch:** `feature/promotions`

**Backend Tasks:**
- IC-601: Design database schema (Voucher, VoucherUsage)
- IC-602: Implement Prisma models untuk vouchers
- IC-603: Create voucher CRUD endpoints (admin)
- IC-604: Implement voucher validation logic
- IC-605: Create voucher apply endpoint
- IC-606: Implement discount calculation (%, nominal)
- IC-607: Create voucher usage tracking
- IC-608: Implement voucher expiration check
- IC-609: Create active voucher listing endpoint

**Frontend Customer Tasks:**
- IC-611: Create promotion slice (Redux)
- IC-612: Design voucher input component
- IC-613: Implement voucher apply functionality
- IC-614: Create voucher list modal
- IC-615: Display discount in cart summary
- IC-616: Create banner carousel untuk promo

**Frontend Admin Tasks:**
- IC-621: Design voucher management UI
- IC-622: Create voucher form (add/edit)
- IC-623: Implement voucher list with filters
- IC-624: Create voucher usage statistics
- IC-625: Design homepage banner management

---

### **Phase 7: Review & Rating System** `[REVIEW]`
**Branch:** `feature/reviews`

**Backend Tasks:**
- IC-701: Design database schema (Review, ReviewImage)
- IC-702: Implement Prisma models untuk reviews
- IC-703: Create review submission endpoint
- IC-704: Implement review validation (purchased item only)
- IC-705: Create review listing endpoint (per product)
- IC-706: Implement review moderation endpoint (admin)
- IC-707: Create review statistics calculation
- IC-708: Implement review image upload
- IC-709: Create helpful/report review endpoint

**Frontend Customer Tasks:**
- IC-711: Create review slice (Redux)
- IC-712: Design review form UI
- IC-713: Implement star rating component
- IC-714: Create review list component
- IC-715: Implement review image gallery
- IC-716: Create review submission modal
- IC-717: Display average rating on products

**Frontend Admin Tasks:**
- IC-721: Design review moderation dashboard
- IC-722: Implement review approve/reject actions
- IC-723: Create review statistics view

---

### **Phase 8: User Profile & Address Management** `[PROFILE]`
**Branch:** `feature/user-profile`

**Backend Tasks:**
- IC-801: Design database schema (UserProfile, Address)
- IC-802: Implement Prisma models untuk profile
- IC-803: Create profile CRUD endpoints
- IC-804: Create address CRUD endpoints
- IC-805: Implement address validation
- IC-806: Create default address logic
- IC-807: Implement profile image upload
- IC-808: Create user preferences endpoint

**Frontend Tasks:**
- IC-811: Create profile slice (Redux)
- IC-812: Design profile page UI
- IC-813: Design edit profile form
- IC-814: Design address management UI
- IC-815: Create address form component
- IC-816: Implement address selector component
- IC-817: Create profile image upload component
- IC-818: Implement password change form

---

### **Phase 9: Reports & Analytics** `[REPORTS]`
**Branch:** `feature/reports-analytics`

**Backend Tasks:**
- IC-901: Create daily sales report endpoint
- IC-902: Create monthly sales report endpoint
- IC-903: Implement revenue calculation logic
- IC-904: Create top selling items endpoint
- IC-905: Implement order statistics endpoint
- IC-906: Create customer analytics endpoint
- IC-907: Implement report export (PDF) service
- IC-908: Implement report export (Excel) service
- IC-909: Create dashboard summary endpoint
- IC-910: Implement date range filtering

**Frontend Admin Tasks:**
- IC-911: Create reports slice (Redux)
- IC-912: Design admin dashboard homepage
- IC-913: Create sales chart components (Chart.js)
- IC-914: Design report filters (date range, type)
- IC-915: Implement report preview
- IC-916: Create export buttons (PDF/Excel)
- IC-917: Design analytics cards (revenue, orders, customers)
- IC-918: Implement best selling products widget
- IC-919: Create real-time dashboard updates

---

### **Phase 10: CMS & Content Management** `[CMS]`
**Branch:** `feature/cms-content`

**Backend Tasks:**
- IC-1001: Design database schema (Page, Gallery, Settings)
- IC-1002: Implement Prisma models untuk CMS
- IC-1003: Create page content CRUD endpoints
- IC-1004: Create gallery CRUD endpoints
- IC-1005: Implement settings management endpoint
- IC-1006: Create SEO metadata endpoints
- IC-1007: Implement content versioning (optional)

**Frontend Public Tasks:**
- IC-1011: Create CMS slice (Redux)
- IC-1012: Design homepage layout
- IC-1013: Implement banner carousel
- IC-1014: Design "Tentang Kami" page
- IC-1015: Design "Kontak" page dengan Google Maps
- IC-1016: Create gallery view component
- IC-1017: Implement footer dengan informasi kafe

**Frontend Admin Tasks:**
- IC-1021: Design CMS management UI
- IC-1022: Create page editor (WYSIWYG optional)
- IC-1023: Implement banner upload & management
- IC-1024: Create gallery management UI
- IC-1025: Design settings page (jam buka, kontak, dll)

---

### **Phase 11: Notifications System** `[NOTIF]`
**Branch:** `feature/notifications`

**Backend Tasks:**
- IC-1101: Design database schema (Notification, NotificationPreference)
- IC-1102: Implement Prisma models untuk notifications
- IC-1103: Create notification service (observer pattern)
- IC-1104: Implement email notification dengan Nodemailer
- IC-1105: Create notification templates
- IC-1106: Implement notification queue system
- IC-1107: Create notification history endpoint
- IC-1108: Implement notification preferences endpoint
- IC-1109: Create real-time push notification (WebSocket)

**Frontend Tasks:**
- IC-1111: Create notification slice (Redux)
- IC-1112: Design notification bell component
- IC-1113: Create notification dropdown
- IC-1114: Implement notification list
- IC-1115: Create notification preferences UI
- IC-1116: Implement real-time notification updates
- IC-1117: Create notification sound toggle

---

### **Phase 12: Security & Performance** `[SEC-PERF]`
**Branch:** `feature/security-performance`

**Tasks:**
- IC-1201: Implement rate limiting middleware
- IC-1202: Setup Redis untuk caching
- IC-1203: Implement API response caching
- IC-1204: Create database query optimization
- IC-1205: Implement image lazy loading
- IC-1206: Setup CDN untuk static assets (optional)
- IC-1207: Implement CSRF protection
- IC-1208: Setup helmet.js untuk security headers
- IC-1209: Implement request validation sanitization
- IC-1210: Create audit log system
- IC-1211: Implement database backup automation
- IC-1212: Setup error monitoring (Sentry optional)
- IC-1213: Create performance monitoring
- IC-1214: Implement SQL injection prevention
- IC-1215: Setup HTTPS/SSL configuration

---

### **Phase 13: Testing** `[TEST]`
**Branch:** `feature/testing`

**Backend Tasks:**
- IC-1301: Setup Jest untuk unit testing
- IC-1302: Write auth service unit tests
- IC-1303: Write menu service unit tests
- IC-1304: Write order service unit tests
- IC-1305: Write payment service unit tests
- IC-1306: Create integration tests untuk API endpoints
- IC-1307: Implement API testing dengan Supertest
- IC-1308: Create database seeder untuk testing
- IC-1309: Write end-to-end tests untuk critical flows

**Frontend Tasks:**
- IC-1311: Setup Vitest untuk unit testing
- IC-1312: Write component unit tests
- IC-1313: Write Redux slice tests
- IC-1314: Create E2E tests dengan Playwright
- IC-1315: Write accessibility tests
- IC-1316: Implement visual regression testing (optional)

---

### **Phase 14: Deployment & DevOps** `[DEPLOY]`
**Branch:** `feature/deployment`

**Tasks:**
- IC-1401: Setup GitHub Actions CI/CD pipeline
- IC-1402: Create Docker configuration (Dockerfile)
- IC-1403: Create Docker Compose untuk development
- IC-1404: Setup production environment di VPS
- IC-1405: Configure Nginx reverse proxy
- IC-1406: Setup PM2 untuk process management
- IC-1407: Implement zero-downtime deployment
- IC-1408: Setup database migration workflow
- IC-1409: Configure automated backup
- IC-1410: Setup monitoring & logging (PM2 logs)
- IC-1411: Create deployment documentation
- IC-1412: Setup staging environment
- IC-1413: Implement rollback strategy
- IC-1414: Configure SSL certificate (Let's Encrypt)
- IC-1415: Setup domain & DNS configuration

---

## **Branch Naming Convention**

- **Feature branches:** `feature/[feature-name]` (e.g., `feature/auth-system`)
- **Bugfix branches:** `bugfix/[IC-XXX]-[description]` (e.g., `bugfix/IC-105-login-validation`)
- **Hotfix branches:** `hotfix/[description]` (e.g., `hotfix/payment-callback`)
- **Release branches:** `release/v[version]` (e.g., `release/v1.0.0`)

## **Commit Message Convention**

```
[IC-XXX] Type: Short description

Longer description if needed

Type options:
- feat: New feature
- fix: Bug fix
- refactor: Code refactoring
- style: Formatting, styling
- docs: Documentation
- test: Adding tests
- chore: Build tasks, configs

Example:
[IC-105] feat: Implement login endpoint dengan JWT

- Add JWT token generation
- Implement password verification
- Add rate limiting
```

---

## **Definition of Done (DoD)**

Setiap task dianggap selesai jika:
- [ ] Code ditulis sesuai dengan SOLID principles
- [ ] TypeScript types lengkap tanpa `any`
- [ ] Unit test ditulis dan passing (coverage > 70%)
- [ ] Code review approved minimal oleh 1 reviewer
- [ ] Dokumentasi API diupdate (jika backend)
- [ ] Responsive design tested (mobile & desktop)
- [ ] No console errors atau warnings
- [ ] Performance metrics terpenuhi (load time < 2s)
- [ ] Author menyetujui dan mengkonfirmasi task

---

## **Priority & Timeline Estimation**

| Phase | Priority | Estimated Duration | Dependencies |
|-------|----------|-------------------|--------------|
| Phase 0: Setup | P0 (Critical) | 2-3 hari | - |
| Phase 1: Auth | P0 (Critical) | 5-7 hari | Phase 0 |
| Phase 2: Menu | P0 (Critical) | 7-10 hari | Phase 0, 1 |
| Phase 3: Cart | P0 (Critical) | 5-7 hari | Phase 2 |
| Phase 4: Orders | P0 (Critical) | 7-10 hari | Phase 3 |
| Phase 5: Payment | P0 (Critical) | 5-7 hari | Phase 4 |
| Phase 6: Promo | P1 (High) | 4-6 hari | Phase 5 |
| Phase 7: Reviews | P2 (Medium) | 4-5 hari | Phase 4 |
| Phase 8: Profile | P1 (High) | 3-5 hari | Phase 1 |
| Phase 9: Reports | P1 (High) | 5-7 hari | Phase 4 |
| Phase 10: CMS | P2 (Medium) | 4-6 hari | Phase 0 |
| Phase 11: Notif | P1 (High) | 4-6 hari | Phase 4 |
| Phase 12: Security | P0 (Critical) | 3-5 hari | All phases |
| Phase 13: Testing | P0 (Critical) | 7-10 hari | All phases |
| Phase 14: Deploy | P0 (Critical) | 3-5 hari | Phase 13 |

**Total Estimated Duration:** 10-14 minggu (2.5-3.5 bulan)

---

## **Risk Management**

| Risk | Impact | Mitigation |
|------|--------|------------|
| Kompleksitas Midtrans integration | High | Buat sandbox testing environment, dokumentasi lengkap |
| Performance bottleneck saat traffic tinggi | High | Implement caching, load testing, horizontal scaling ready |
| Scope creep dari klien | Medium | Strict DoD, change request process |
| Keterlambatan third-party API | Medium | Implement fallback mechanisms, monitoring |
| Data breach / security vulnerability | Critical | Security audit, penetration testing, regular updates |

---

## **Next Steps**

1. ✅ Review dan approval plan.md ini oleh Author
2. ⏳ Setup development environment (Phase 0)
3. ⏳ Mulai implementasi Phase 1 (Auth System)
4. ⏳ Setup weekly progress review meeting

---

**Document Status:** ✅ Ready for Review  
**Last Updated:** 22 November 2025
