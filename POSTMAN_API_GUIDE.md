# 📬 L9 Fitness Gym - Postman API Collection

**Complete API documentation and testing collection for L9 Fitness Gym Management System**

---

## 🚀 Quick Start

### 1. Import into Postman

1. Open **Postman** application
2. Click **Import** button (top left)
3. Drag and drop these files:
   - `L9_Fitness_API_Collection.postman_collection.json`
   - `L9_Fitness_Local_Environment.postman_environment.json`
   - `L9_Fitness_Ngrok_Environment.postman_environment.json`
4. Select environment from dropdown (top right)

### 2. Set Your Environment

**For Local Testing:**
- Select: **L9 Fitness - Local Development**
- Base URL: `http://localhost/Capstone-latest/public`

**For Presentation (Ngrok):**
- Select: **L9 Fitness - Ngrok (Presentation)**
- Update `base_url` with your ngrok URL
- Example: `https://abc123.ngrok.io/Capstone-latest/public`

---

## 📚 API Categories

### 🔐 Authentication
- **Login** - User authentication
- **Register** - New user registration
- **Logout** - End user session

### 🤖 Chatbot APIs
- **Advanced Chatbot** - AI-powered with context awareness
- **Simple Chatbot** - Basic query handling
- **Main Chatbot** - Standard chatbot endpoint

### 💪 Fitness Profile
- **Get Profile** - Retrieve user fitness data
- **Update Fitness Profile** - Update height, weight, fitness level

### 🎯 Goals Management
- **Create Goal** - Set new fitness goals
- **Update Goal Progress** - Track progress
- **Delete Goal** - Remove goals

### 🏋️ Workout Tracking
- **Log Workout** - Record workout sessions
- **Log Weight** - Track weight measurements
- **Get Offline Plans** - Download workout plans

### 🥗 Nutrition
- **Update Nutrition Plan** - Manage daily nutrition targets

### 💳 Payments
- **PayPal Checkout** - Process membership payments
- **Download Invoice** - Get payment receipts as PDF

### 📅 Classes & Bookings
- **Get Classes** - View available classes
- **Book Class** - Reserve class spot

### 👨‍💼 Admin
- **Admin Dashboard** - Access admin panel
- **Manage Users** - User management
- **Chatbot Admin** - Configure chatbot settings

### 🔧 Health Check
- **Status Check** - System health monitoring
- **Debug API** - Testing endpoint

---

## 🔑 Authentication Flow

### Step 1: Login
```http
POST {{base_url}}/login.php
Content-Type: application/x-www-form-urlencoded

email=admin@l9fitness.com
password=Admin@123
csrf_token={{csrf_token}}
```

### Step 2: Use APIs
After login, your session cookie (`PHPSESSID`) is automatically stored.

### Step 3: Logout
```http
GET {{base_url}}/logout.php
```

---

## 📝 Example Requests

### Create Fitness Goal
```json
POST {{base_url}}/api/create_goal.php
Content-Type: application/json

{
    "goal_type": "weight_loss",
    "target_value": 70,
    "target_date": "2025-12-31",
    "description": "Lose 5kg by end of year"
}
```

### Log Workout
```json
POST {{base_url}}/api/log_workout.php
Content-Type: application/json

{
    "workout_type": "Cardio",
    "duration": 45,
    "calories_burned": 350,
    "intensity": "moderate",
    "notes": "30 min treadmill + 15 min cycling"
}
```

### Chat with AI Bot
```json
POST {{base_url}}/advanced_chatbot_api.php
Content-Type: application/json

{
    "message": "What classes are available today?",
    "context": []
}
```

---

## 🧪 Testing Features

### Auto-Tests Included
Each request includes automatic tests:
- ✅ Status code validation (200, 201, 302)
- ✅ Response time check (< 5 seconds)
- ✅ Session cookie verification

### Pre-request Scripts
- Auto-detects session cookies
- Logs debugging information

---

## 👥 Test User Accounts

### Admin Account
- **Email:** `admin@l9fitness.com`
- **Password:** `Admin@123`
- **Access:** Full system control

### Trainer Account
- **Email:** `trainer@l9fitness.com`
- **Password:** `Trainer@123`
- **Access:** Class management, member communication

### Member Account
- **Email:** `member@l9fitness.com`
- **Password:** `Member@123`
- **Access:** Book classes, track fitness

---

## 🌐 Using with Ngrok

### 1. Start Ngrok
```bash
ngrok http 80
```

### 2. Update Environment
In Postman:
1. Select **L9 Fitness - Ngrok (Presentation)**
2. Edit environment variables
3. Update `base_url` with your ngrok URL
4. Example: `https://abc123.ngrok.io/Capstone-latest/public`

### 3. Test APIs
All requests now use the public ngrok URL!

---

## 📊 Response Formats

### Success Response
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "success": false,
    "error": "Error message here",
    "code": 400
}
```

---

## 🔒 Security Notes

### CSRF Protection
- All POST requests require CSRF token
- Token automatically managed in session
- Include in form data or JSON body

### Session Management
- Sessions timeout after 30 minutes of inactivity
- Session regeneration every 5 minutes
- HttpOnly cookies for security

### Rate Limiting
- Consider implementing for production
- Currently unlimited for testing

---

## 🛠️ Troubleshooting

### "Invalid CSRF token"
**Solution:** Clear cookies and login again

### "Session expired"
**Solution:** Re-authenticate via login endpoint

### "Connection refused"
**Solution:** Ensure XAMPP/Apache is running

### 404 Errors
**Solution:** Verify `base_url` in environment settings

---

## 📱 Mobile Testing

Use Postman Mobile Agent to test APIs on mobile devices:
1. Download Postman app
2. Sync your collection
3. Test on actual devices

---

## 🎓 For Your Capstone Presentation

### Demo Flow
1. **Show Collection** - Display organized API structure
2. **Run Health Check** - Demonstrate system status
3. **Test Chatbot** - Live AI interaction
4. **Create Goal** - Real-time fitness tracking
5. **Book Class** - Booking system demo

### Presentation Tips
- Use **Collection Runner** for automated tests
- Enable **Postman Console** for debugging
- Share collection URL with reviewers
- Export results as report

---

## 📦 What's Included

```
📁 Postman Files
├── L9_Fitness_API_Collection.postman_collection.json (Main collection)
├── L9_Fitness_Local_Environment.postman_environment.json (Local dev)
├── L9_Fitness_Ngrok_Environment.postman_environment.json (Presentation)
└── POSTMAN_API_GUIDE.md (This guide)
```

---

## 🔄 Updating the Collection

### Adding New Endpoints
1. Open collection in Postman
2. Add new request to appropriate folder
3. Export updated collection
4. Replace JSON file

### Sharing with Team
1. Export collection
2. Share JSON files via email/GitHub
3. Team imports into their Postman

---

## 📈 API Statistics

- **Total Endpoints:** 25+
- **Categories:** 9
- **HTTP Methods:** GET, POST, PUT, DELETE
- **Authentication:** Session-based (PHPSESSID)
- **Response Format:** JSON, HTML, PDF

---

## 🎯 Best Practices

1. **Always check** environment before testing
2. **Login first** before testing protected endpoints
3. **Clear cookies** when switching users
4. **Monitor console** for debugging
5. **Save responses** for documentation

---

## 💡 Advanced Features

### Collection Variables
- `{{base_url}}` - Base API URL
- `{{csrf_token}}` - CSRF protection token
- `{{auth_token}}` - Authentication token
- `{{user_id}}` - Current user ID

### Scripts
- Pre-request: Auto-setup headers
- Post-response: Validate responses
- Tests: Automated assertions

---

## 📞 Support

For issues or questions:
- Check **POSTMAN_API_GUIDE.md** (this file)
- Review **README.md** in project root
- Test with **status_check.php** endpoint

---

## ✨ Features Highlight

✅ Complete API coverage  
✅ Auto-testing included  
✅ Multiple environments  
✅ Test user accounts  
✅ Ngrok-ready  
✅ Documentation included  
✅ Error handling  
✅ Security measures  

---

**Created for:** L9 Fitness Gym Management System  
**Version:** 1.0.0  
**Date:** October 2, 2025  
**Author:** Capstone Project Team  

**🚀 Happy Testing! 🚀**
