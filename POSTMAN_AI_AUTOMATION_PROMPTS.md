# 🤖 Postman AI Assistant Prompt - L9 Fitness API Automation

**Copy and paste this prompt into Postman's AI Assistant (Postbot) to automate your API testing**

---

## 📋 Master Prompt for Postbot

```
I need help automating and testing the L9 Fitness Gym Management System API. This is a comprehensive fitness center management platform with the following features:

### System Overview:
- PHP 8+ backend with MySQL database
- Session-based authentication with CSRF protection
- Role-based access (Admin, Trainer, Member)
- RESTful API architecture
- AI-powered chatbot integration

### API Categories to Automate:

1. **Authentication Flow**
   - Login endpoint with email/password
   - Registration with validation
   - Session management
   - CSRF token handling

2. **Chatbot APIs**
   - Advanced AI chatbot with context awareness
   - Simple chatbot for basic queries
   - Real-time gym data integration

3. **Fitness Profile Management**
   - Get user fitness profile (height, weight, goals)
   - Update fitness metrics
   - Track progress over time

4. **Goals & Tracking**
   - Create fitness goals (weight loss, muscle gain, etc.)
   - Update goal progress
   - Delete completed goals
   - Log workouts (type, duration, calories)
   - Log weight measurements

5. **Nutrition Management**
   - Update daily nutrition plans
   - Track macros (protein, carbs, fats)
   - Meal frequency management

6. **Payment Processing**
   - PayPal checkout integration
   - Download payment invoices (PDF)
   - Membership plan management

7. **Class Bookings**
   - View available classes
   - Book class spots
   - Check class capacity

8. **Admin Operations**
   - User management
   - Dashboard metrics
   - Chatbot configuration

### Required Automation:

1. **Create Pre-request Scripts to:**
   - Automatically extract and store CSRF tokens from responses
   - Manage session cookies (PHPSESSID)
   - Set up authentication headers
   - Generate dynamic timestamps for requests

2. **Create Test Scripts to:**
   - Validate HTTP status codes (200, 201, 302)
   - Check response time < 5 seconds
   - Verify JSON structure in responses
   - Assert success/error messages
   - Extract and store user IDs, goal IDs, booking IDs
   - Validate data types and required fields

3. **Create Collection-Level Scripts for:**
   - Global error handling
   - Session timeout detection
   - Automatic re-authentication
   - Response logging

4. **Set Up Environment Variables:**
   - base_url (localhost and ngrok versions)
   - csrf_token (dynamic)
   - user_id (after login)
   - auth_token (session)
   - Test account credentials (admin, trainer, member)

5. **Create Test Flows for:**
   - Complete user journey (register → login → create goal → log workout → book class)
   - Admin workflow (login → manage users → view dashboard)
   - Payment flow (select plan → checkout → download invoice)
   - Chatbot interaction (send message → receive AI response)

6. **Generate Newman CLI Commands for:**
   - Running full test suite
   - Generating HTML reports
   - CI/CD integration
   - Automated regression testing

### Expected Behavior:

- All requests should auto-authenticate
- CSRF tokens should be extracted and injected automatically
- Failed requests should retry with fresh tokens
- Environment should switch between local/ngrok seamlessly
- Test results should be comprehensive and readable

### Security Considerations:

- Session-based authentication (30-min timeout)
- CSRF protection on all POST requests
- HttpOnly cookies
- Password validation (8+ chars, mixed case, special chars)
- SQL injection prevention (PDO prepared statements)

### Sample Endpoints:

Base URL (Local): http://localhost/Capstone-latest/public
Base URL (Ngrok): https://[your-url].ngrok.io/Capstone-latest/public

Key Endpoints:
- POST /login.php
- POST /register.php
- POST /api/create_goal.php
- POST /api/log_workout.php
- POST /advanced_chatbot_api.php
- GET /api/download_invoice.php?payment_id=1
- POST /classes.php (booking)

### Test Data:

Admin: admin@l9fitness.com / Admin@123
Trainer: trainer@l9fitness.com / Trainer@123
Member: member@l9fitness.com / Member@123

Please help me create:
1. Automated test scripts for all endpoints
2. Pre-request scripts for auth/CSRF handling
3. Collection runner workflow
4. Newman commands for CI/CD
5. Response validation tests
6. Error handling logic
7. Data-driven testing setup

Make the automation production-ready for my capstone project presentation.
```

---

## 🎯 Specific Task Prompts

### For Pre-request Script Automation:
```
Create a pre-request script that:
1. Checks if PHPSESSID cookie exists
2. If not, automatically calls login endpoint
3. Extracts and stores CSRF token from login response
4. Injects CSRF token into request body for POST requests
5. Sets proper Content-Type headers based on request type
6. Adds timestamp to prevent caching
```

### For Test Script Automation:
```
Generate comprehensive test scripts for L9 Fitness API that:
1. Validate HTTP status codes (accept 200, 201, 302)
2. Check response contains 'success' or 'error' field
3. Verify response time < 5000ms
4. Parse JSON responses and validate structure
5. Extract IDs (user_id, goal_id, payment_id) and save to environment
6. Count specific elements in arrays (classes, bookings)
7. Assert specific field values match expected patterns
8. Handle different response formats (JSON, HTML redirect, PDF)
```

### For Collection Runner:
```
Set up Postman Collection Runner to:
1. Execute full authentication flow first
2. Run all API tests in logical order
3. Use test data from CSV/JSON file
4. Generate detailed HTML report
5. Stop on first failure vs continue testing
6. Export results to file
7. Integrate with Newman for CLI execution
```

### For Newman CLI:
```
Provide Newman command-line examples for:
1. Running collection with environment file
2. Generating HTML/JSON reports
3. Setting iteration count for load testing
4. Exporting results to file
5. Integration with CI/CD pipeline (GitHub Actions)
6. Running specific folders within collection
7. Setting custom timeout and delay values
```

---

## 🚀 Quick Start Commands

### Import Everything:
```
Hey Postbot, import my L9 Fitness API collection and set up automated testing with CSRF token extraction, session management, and comprehensive validation scripts.
```

### Create Auth Flow:
```
Create an automated authentication flow that logs in as admin, extracts the session cookie, gets CSRF token, and uses both for all subsequent requests.
```

### Generate Tests:
```
Generate test scripts for all endpoints in my collection that validate status codes, response structure, timing, and extract key data to environment variables.
```

### Setup Collection Runner:
```
Configure Collection Runner to execute complete user journey: register → login → create goal → log workout → book class → download invoice, with proper error handling.
```

### Create Newman Script:
```
Generate Newman CLI command to run my L9 Fitness collection with HTML report generation and fail on error behavior.
```

---

## 💡 Advanced Automation Prompts

### Data-Driven Testing:
```
Set up data-driven testing using CSV file with multiple test users, their goals, workout logs, and expected outcomes. Run collection with each data set and validate results.
```

### Load Testing:
```
Configure load testing scenario: 100 concurrent users logging in, creating goals, booking classes simultaneously. Monitor response times and error rates.
```

### CI/CD Integration:
```
Create GitHub Actions workflow that runs Newman tests on every push, generates reports, and fails build if any test fails. Include environment setup and cleanup.
```

### Mock Server:
```
Generate Postman mock server from my L9 Fitness collection for frontend development without backend dependency. Include realistic example responses.
```

### API Documentation:
```
Auto-generate comprehensive API documentation from my collection with request/response examples, authentication details, and error codes. Publish to public URL.
```

---

## 🔧 Troubleshooting Prompts

### Fix CSRF Issues:
```
My POST requests fail with "Invalid CSRF token". Help me create a pre-request script that extracts token from cookies/response and injects it into every POST request body.
```

### Session Timeout:
```
Detect when session expires (30 min timeout) and automatically re-authenticate before continuing test execution. Store new session and retry failed request.
```

### Environment Switching:
```
Create script to automatically detect if running on localhost vs ngrok and set base_url accordingly. Support seamless switching between environments.
```

---

## 📊 Reporting Prompts

### Generate Report:
```
After Collection Runner completes, generate detailed HTML report showing:
- Total requests executed
- Pass/fail rate
- Response time statistics
- Failed assertions with details
- Screenshots of responses
Export to 'test-results.html'
```

### Dashboard Metrics:
```
Create monitoring dashboard for L9 Fitness API showing:
- API uptime percentage
- Average response times per endpoint
- Error rate trends
- Most used endpoints
Update in real-time during testing
```

---

## 🎓 For Capstone Presentation

### Demo Automation:
```
Create impressive demo flow for capstone presentation:
1. Run full API test suite (all 25+ endpoints)
2. Show live results with pass/fail indicators
3. Display response time graphs
4. Demonstrate error handling
5. Generate professional PDF report
Make it visually appealing and easy to understand.
```

### Export Everything:
```
Package my complete Postman setup for sharing:
- Collection JSON
- Environment files (local + ngrok)
- Pre-request/test scripts
- Newman commands
- Documentation
- Sample test data
Create as distributable package for reviewers.
```

---

## 📝 Example Script Requests

### Login & Extract Session:
```javascript
// Pre-request Script
pm.sendRequest({
    url: pm.environment.get('base_url') + '/login.php',
    method: 'POST',
    body: {
        mode: 'urlencoded',
        urlencoded: [
            {key: 'email', value: pm.environment.get('admin_email')},
            {key: 'password', value: pm.environment.get('admin_password')}
        ]
    }
}, function(err, res) {
    // Extract CSRF token and save
    pm.environment.set('csrf_token', res.json().csrf_token);
});
```

### Comprehensive Test:
```javascript
// Test Script
pm.test("Status code is success", () => {
    pm.expect(pm.response.code).to.be.oneOf([200, 201, 302]);
});

pm.test("Response time is acceptable", () => {
    pm.expect(pm.response.responseTime).to.be.below(5000);
});

pm.test("Response has required structure", () => {
    const json = pm.response.json();
    pm.expect(json).to.have.property('success');
    pm.expect(json).to.have.property('data');
});

// Extract and save IDs
if (pm.response.json().data && pm.response.json().data.id) {
    pm.environment.set('last_created_id', pm.response.json().data.id);
}
```

---

## 🎯 Usage Instructions

1. **Open Postman** → Click Postbot icon (bottom right)
2. **Paste Master Prompt** → Get comprehensive automation setup
3. **Use Specific Prompts** → Fine-tune individual scripts
4. **Review & Apply** → Postbot generates scripts automatically
5. **Test & Iterate** → Run collection and refine as needed

---

**Pro Tip:** Start with the Master Prompt, then use specific task prompts to refine individual components. Postbot learns your collection structure and creates contextual automation!

---

**Created for:** L9 Fitness Gym Management System  
**Purpose:** Capstone Project API Automation  
**Date:** October 2, 2025  

**🤖 Let Postbot do the heavy lifting! 🚀**
