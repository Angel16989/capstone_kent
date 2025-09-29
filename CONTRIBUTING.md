# 🤝 Contributing to L9 Fitness Gym

Thank you for your interest in contributing to L9 Fitness Gym! This document provides guidelines and information for contributors.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [How to Contribute](#how-to-contribute)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Submitting Changes](#submitting-changes)
- [Reporting Issues](#reporting-issues)

## 🤟 Code of Conduct

This project follows a code of conduct to ensure a welcoming environment for all contributors. By participating, you agree to:

- Be respectful and inclusive
- Focus on constructive feedback
- Accept responsibility for mistakes
- Show empathy towards other contributors
- Help create a positive community

## 🚀 Getting Started

### Prerequisites

Before you begin, ensure you have:

- **PHP 8.0+** with PDO extension
- **MySQL 5.7+** or MariaDB 10.0+
- **Git** for version control
- **Composer** (optional, for dependency management)
- **XAMPP/LAMP/MAMP** or similar local development environment

### Quick Setup

1. **Fork the repository** on GitHub
2. **Clone your fork:**
   ```bash
   git clone https://github.com/your-username/Capstone.git
   cd Capstone
   ```
3. **Set up upstream remote:**
   ```bash
   git remote add upstream https://github.com/uniqstha/Capstone.git
   ```
4. **Follow the development setup** below

## 🛠️ Development Setup

### Local Development Environment

1. **Start your web server** (XAMPP/LAMP/MAMP)
2. **Database setup:**
   ```bash
   # Create database
   mysql -u root -p < database/schema.sql

   # Add seed data
   mysql -u root -p < database/seed.sql

   # Add dummy data for testing
   mysql -u root -p < database/additional_dummy_data.sql
   ```
3. **Configuration:**
   - Copy `config/db.php.example` to `config/db.php`
   - Update database credentials
4. **Access the application:**
   - Frontend: `http://localhost/Capstone/public/`
   - Admin: `http://localhost/Capstone/public/admin.php`

### Using Docker (Alternative)

```bash
# Build and run with Docker
docker-compose up -d

# Access the application
# http://localhost:8080
```

## 💡 How to Contribute

### Types of Contributions

- 🐛 **Bug fixes** - Fix existing issues
- ✨ **Features** - Add new functionality
- 📚 **Documentation** - Improve docs and guides
- 🎨 **UI/UX** - Enhance user interface and experience
- 🧪 **Testing** - Add or improve tests
- 🔧 **Tools** - Development tools and scripts

### Finding Issues to Work On

1. Check the [Issues](https://github.com/uniqstha/Capstone/issues) page
2. Look for issues labeled `good first issue` or `help wanted`
3. Comment on the issue to indicate you're working on it

## 🔄 Development Workflow

### 1. Choose an Issue

- Select an issue from the [Issues](https://github.com/uniqstha/Capstone/issues) page
- Comment to indicate you're working on it
- Wait for maintainer approval if required

### 2. Create a Branch

```bash
# Create and switch to a new branch
git checkout -b feature/your-feature-name
# or
git checkout -b fix/issue-number-description
```

### 3. Make Changes

- Write clear, concise commit messages
- Test your changes thoroughly
- Follow the coding standards below
- Update documentation if needed

### 4. Test Your Changes

```bash
# Run basic tests
php -l public/index.php  # Syntax check
# Test database operations
# Test user flows manually
```

### 5. Submit a Pull Request

- Push your branch to your fork
- Create a Pull Request from your branch to `main`
- Fill out the PR template completely
- Wait for review and address feedback

## 📝 Coding Standards

### PHP Standards

- **PSR-12** coding standard
- **PHP 8.0+** features and syntax
- **Meaningful variable names** (no abbreviations)
- **DocBlocks** for classes and methods
- **Type hints** where possible

### Example PHP Code:

```php
<?php

/**
 * User authentication helper class
 */
class AuthHelper
{
    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user data
     *
     * @return array|null
     */
    public static function getCurrentUser(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        // Implementation here
        return $_SESSION['user'] ?? null;
    }
}
```

### HTML/CSS/JavaScript Standards

- **Semantic HTML5** markup
- **BEM** methodology for CSS classes
- **ES6+** JavaScript features
- **Meaningful comments** for complex logic
- **Accessibility** considerations (alt tags, ARIA labels)

### Database Standards

- **InnoDB** engine for all tables
- **Foreign key constraints** where appropriate
- **Indexes** on frequently queried columns
- **Consistent naming** (snake_case for columns)
- **Data validation** at application level

## 🧪 Testing

### Manual Testing Checklist

Before submitting a PR, ensure:

- [ ] Code follows coding standards
- [ ] No syntax errors (run `php -l`)
- [ ] Database queries work correctly
- [ ] User authentication flows work
- [ ] Responsive design works on mobile
- [ ] No console errors in browser
- [ ] Forms validate properly
- [ ] Error handling works
- [ ] Security measures in place

### Automated Testing (Future)

```bash
# Run PHP tests (when implemented)
./vendor/bin/phpunit

# Run JavaScript tests (when implemented)
npm test
```

## 📤 Submitting Changes

### Pull Request Process

1. **Update your branch:**
   ```bash
   git checkout main
   git pull upstream main
   git checkout your-branch
   git rebase main
   ```

2. **Create Pull Request:**
   - Go to your fork on GitHub
   - Click "New Pull Request"
   - Select your branch
   - Fill out the PR template

3. **PR Template:**
   ```markdown
   ## Description
   Brief description of changes

   ## Type of Change
   - [ ] Bug fix
   - [ ] New feature
   - [ ] Documentation update
   - [ ] Other

   ## Testing
   - [ ] Tested locally
   - [ ] Tested on different browsers
   - [ ] Tested database operations

   ## Screenshots (if applicable)
   Add screenshots of UI changes

   ## Checklist
   - [ ] Code follows standards
   - [ ] Tests pass
   - [ ] Documentation updated
   - [ ] No breaking changes
   ```

### Commit Message Guidelines

```
type(scope): description

[optional body]

[optional footer]
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Testing
- `chore`: Maintenance

**Examples:**
```
feat(auth): add Google OAuth integration
fix(booking): resolve double booking issue
docs(readme): update deployment instructions
```

## 🐛 Reporting Issues

### Bug Reports

When reporting bugs, please include:

1. **Clear title** describing the issue
2. **Steps to reproduce** the problem
3. **Expected behavior** vs actual behavior
4. **Environment details:**
   - PHP version
   - MySQL version
   - Browser and version
   - Operating system
5. **Screenshots** if applicable
6. **Error messages** or logs

### Feature Requests

For new features, please include:

1. **Clear description** of the proposed feature
2. **Use case** - why is this needed?
3. **Implementation ideas** if you have any
4. **Mockups or examples** if applicable

## 📞 Getting Help

- **GitHub Issues:** For bugs and feature requests
- **GitHub Discussions:** For questions and general discussion
- **Documentation:** Check the README and deployment guides

## 🎉 Recognition

Contributors will be recognized in:
- Repository contributors list
- Release notes
- Special mentions in documentation

Thank you for contributing to L9 Fitness Gym! 🏋️‍♂️💪