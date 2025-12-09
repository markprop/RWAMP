# Commit Message Templates

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=24&duration=2000&pause=500&color=118AB2&center=true&vCenter=true&width=600&lines=Commit+Message+Templates" alt="Commit Messages Header" />
</p>

This directory contains commit message templates used for organizing project commits. These templates serve as reference for consistent, professional commit messages.

## 📄 Templates (17 Files)

### Feature Commits
- **commit-msg-game.txt** - Trading game system implementation
- **commit-msg-wallet.txt** - WalletConnect v2 integration
- **commit-msg-ulid.txt** - ULID URL obfuscation system
- **commit-msg-price.txt** - Price management system enhancements
- **commit-msg-commands.txt** - Artisan commands and scheduled tasks

### Refactoring Commits
- **commit-msg-models.txt** - Model improvements and ULID support
- **commit-msg-admin-controllers.txt** - Admin controller refactoring
- **commit-msg-auth-controllers.txt** - Auth controller improvements
- **commit-msg-dashboard-controllers.txt** - Dashboard controller updates
- **commit-msg-routes-middleware.txt** - Routes and middleware updates

### View & Frontend Commits
- **commit-msg-views-dashboard.txt** - Dashboard views with ULID support
- **commit-msg-views-frontend.txt** - Frontend views and components
- **commit-msg-views-emails.txt** - Email template updates
- **commit-msg-frontend-assets.txt** - Frontend assets and dependencies

### Documentation & Scripts
- **commit-msg-docs.txt** - Documentation updates
- **commit-msg-scripts.txt** - PowerShell scripts for Git operations
- **commit-msg-assets-config.txt** - Assets and configuration files

## 📝 Usage

These templates serve as reference for:
- ✅ Commit message format and structure
- ✅ Documentation standards
- ✅ Feature descriptions
- ✅ Technical implementation details
- ✅ Consistent commit history

### Using Templates

```bash
# Use template for commit
git commit -F docs/commit-messages/commit-msg-game.txt

# Or read template for reference
cat docs/commit-messages/commit-msg-game.txt
```

## 🎯 Commit Message Format

All commit messages follow **conventional commit format**:

### Types
- **`feat:`** - New features
- **`fix:`** - Bug fixes
- **`refactor:`** - Code refactoring
- **`docs:`** - Documentation changes
- **`chore:`** - Maintenance tasks
- **`style:`** - Code style changes
- **`test:`** - Adding or updating tests
- **`perf:`** - Performance improvements

### Structure
```
<type>: <subject>

<body>

<footer>
```

### Example
```
feat: implement trading game system with real-time price engine

This commit introduces a complete trading game system that allows
KYC-approved users to simulate token trading with dynamic pricing.

Features:
- Real-time price engine
- PIN-protected sessions
- Buy/sell functionality

Technical Implementation:
- GamePriceEngine service
- GameSession, GameTrade models
- Price history tracking

Closes #123
```

## 📊 Template Categories

### Feature Templates (5)
Focus on new functionality and implementations

### Refactoring Templates (5)
Focus on code improvements and restructuring

### View Templates (4)
Focus on UI/UX and frontend changes

### Documentation Templates (3)
Focus on documentation and tooling

## 🔗 Related Documentation

- **Git Workflow**: [`../git/COMMIT_PLAN.md`](../git/COMMIT_PLAN.md)
- **Commit Guidelines**: [`../git/COMMIT_MESSAGES.md`](../git/COMMIT_MESSAGES.md)
- **Main README**: [`../../README.md`](../../README.md)

## 🔗 Support

- **Website**: [rwamp.io](https://rwamp.io)
- **Email**: info@rwamp.io
- **Phone**: +92 370 1346038

---

**Last Updated:** January 27, 2025
