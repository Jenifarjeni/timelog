# Time Log System with Admin Dashboard

A comprehensive Laravel-based time logging system with role-based access control and advanced admin features.

##  Installation

1. **Clone and Setup**:
   ```bash
   git clone <repository>
   cd timelog
   composer install
   npm install
   ```

2. **Environment Configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**:
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Start Development Server**:
   ```bash
   php artisan serve
   ```

## User Accounts

### Admin User
- **Email**: `admin@example.com`
- **Password**: `password`
- **Access**: Full admin dashboard + regular user features

### Regular User
- **Email**: `test@example.com`
- **Password**: `password`
- **Access**: Personal time logging only

---

**Built with Laravel, Bootstrap 5, and modern web technologies**
