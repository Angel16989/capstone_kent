# L9 Fitness Chatbot 🤖

An intelligent chatbot assistant for the L9 Fitness gym website that provides instant customer support and information.

## Features

### 🎯 Core Functionality
- **24/7 Customer Support**: Always available to answer member questions
- **Personalized Experience**: Recognizes logged-in users and provides tailored responses
- **Real-time Responses**: Instant answers to common gym-related questions
- **Typing Indicators**: Visual feedback during response generation
- **Quick Reply Buttons**: Pre-defined quick actions for common queries

### 💬 Knowledge Base
The chatbot can answer questions about:
- **Gym Hours**: 24/7 access information and staff hours
- **Memberships**: Plans, pricing, and benefits
- **Classes**: Available classes, schedules, and booking
- **Location**: Address and directions
- **Contact**: Phone, email, and support information
- **Equipment**: Available gym facilities and machines
- **Personal Training**: Trainer availability and packages

### 🔧 Technical Features
- **API Integration**: Server-side processing for intelligent responses
- **Database Logging**: All conversations are logged for analytics
- **Fallback System**: Local responses if API is unavailable
- **Responsive Design**: Works on desktop and mobile devices
- **Admin Dashboard**: Monitor conversations and user interactions

## Files Structure

```
public/
├── assets/
│   ├── css/
│   │   └── chatbot.css          # Chatbot styling
│   └── js/
│       └── chatbot.js           # Chatbot functionality
├── chatbot_api.php              # Server-side API
├── chatbot_admin.php            # Admin dashboard
└── admin.php                    # Main admin (includes chatbot link)
```

## Setup

### 1. Database Setup
The chatbot automatically creates a `chatbot_logs` table to store conversations:
```sql
CREATE TABLE chatbot_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    message TEXT NOT NULL,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

### 2. Integration
The chatbot is automatically included in all pages through:
- CSS: `chatbot.css` included in header
- JavaScript: `chatbot.js` included in footer
- User info passed to chatbot when logged in

### 3. Admin Access
Admins can monitor chatbot activity at:
- URL: `/chatbot_admin.php`
- Access: Admin users only
- Features: View conversations, statistics, and user interactions

## Usage

### For Users
1. Click the chat button (bottom-right corner)
2. Type questions or use quick reply buttons
3. Get instant responses about gym services
4. Personalized experience if logged in

### For Admins
1. Go to Admin Dashboard
2. Click "Chatbot Analytics"
3. View conversation logs and statistics
4. Monitor user interactions and popular questions

## Customization

### Adding New Responses
Edit `chatbot_api.php` to add new question patterns and responses:

```php
// Add new pattern
if (preg_match('/\b(new topic|custom question)\b/i', $message_lower)) {
    return "Your custom response here!";
}
```

### Styling Changes
Modify `chatbot.css` to customize:
- Colors and themes
- Animations and effects
- Responsive behavior
- Button styles

### Enhanced Features
The chatbot can be extended with:
- AI/ML integration for smarter responses
- Multi-language support
- Voice recognition
- Advanced analytics
- Integration with booking system

## Analytics

The admin dashboard provides:
- **Total Conversations**: Number of chat sessions
- **Unique Users**: Different users who chatted
- **Anonymous Chats**: Conversations from non-logged users
- **Daily Activity**: Chat volume over time
- **Conversation History**: Recent messages and responses

## Best Practices

### For Content
- Keep responses friendly and energetic (matching gym brand)
- Use emojis to make conversations engaging
- Provide clear next steps (links to pages, contact info)
- Update responses based on user feedback

### For Performance
- API responses are cached locally
- Fallback to local responses if API fails
- Efficient database queries with limits
- Responsive design for all devices

## Troubleshooting

### Common Issues
1. **Chatbot not appearing**: Check CSS/JS includes in header/footer
2. **API errors**: Verify database connection and permissions
3. **No responses**: Check chatbot_api.php for PHP errors
4. **Admin access denied**: Ensure user has admin role

### Debug Mode
Enable error logging in `chatbot_api.php`:
```php
error_log("Chatbot debug: " . $message);
```

---

**Ready to dominate customer support! 💪🔥**

The L9 Fitness chatbot provides 24/7 assistance to help members crush their fitness goals with instant answers and personalized support.
