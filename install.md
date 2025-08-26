# Claude AI Chat Module for PHP-Nuke Evo Extreme

## Overview
This module integrates **Anthropic's Claude AI assistant** into your PHP-Nuke Evo Extreme website, providing users with an intelligent chat interface.

## Features
- Real-time chat interface with Claude AI  
- User session management and conversation context  
- Admin configuration panel  
- Usage statistics and error logging  
- Rate limiting and content moderation  
- Responsive design for mobile and desktop  
- Multi-language support (English included)  

## Requirements
- PHP-Nuke Evo Extreme  
- PHP 7.4 or higher  
- MySQL 5.7 or higher  
- cURL extension enabled  
- Valid Anthropic API key  

---

## Installation Steps

### 1. File Structure
Upload the following files to your PHP-Nuke installation:

```
├── includes/
│   └── custom_files/
│       └── Claude/
│           └── claude_api.php          (API handler class)
│           └── css
│               └── claude.css
│           └── js                      (Not Used Yet)
├── modules
│   └── Claude/
│       └── admin/
│           └── language/
│               └── lang-english.php
│           └── case.php
│           └── index.php               (Admin interface)
│           └── links.php
│       └── language/
│           └── lang-english/
│       └── index.php                   (Main module file)
│       └── copyright.php
├── sql
│   └── install.sql
```

### 2. Database Setup
1. Access your MySQL database (via phpMyAdmin or command line)  
2. Import the `sql/install.sql` file  
3. Verify that these tables were created:
   - `nuke_claude_chat`  
   - `nuke_claude_config`  
   - `nuke_claude_logs`  
   - `nuke_claude_preferences`  

### 3. Get Anthropic API Key
1. Visit the [Anthropic Console](https://console.anthropic.com)  
2. Create an account or log in  
3. Generate a new API key  
4. Keep this key secure — you'll need it for configuration  

### 4. Module Configuration
1. Log into your PHP-Nuke admin panel  
2. Navigate to the Claude module administration  
3. Enter your Anthropic API key  
4. Configure the following settings:  
   - **Model**: Haiku (fast), Sonnet (balanced), or Opus (most capable)  
   - **Max Tokens**: Recommended 1000  
   - **Temperature**: 0.0–1.0 (recommended 0.7)  
   - **Rate Limit**: Messages per user per hour (recommended 30)  

### 5. Test Configuration
- Click **Test API Connection** in the admin panel  
- Verify the connection is successful  
- Check the module status indicators  

### 6. Enable Module
- Go to **Admin → Modules**  
- Ensure **Claude** module is activated  
- Set appropriate viewing permissions  

---

## Configuration Options

### API Settings
- **API Key**: Your Anthropic API key  
- **Model Options**:  
  - `claude-3-haiku-20240307` — Fastest, most economical  
  - `claude-3-sonnet-20240229` — Balanced performance  
  - `claude-3-opus-20240229` — Most capable, highest quality  
- **Max Tokens**: 100–4096 (affects response length and cost)  
- **Temperature**: 0.0 (focused) to 1.0 (creative)  
- **Rate Limit**: 30 to 100  

### Usage Controls
- **Rate Limit**: Messages per user per hour  
- **Context Messages**: Number of previous messages to include  
- **Content Moderation**: Enable/disable basic filtering  
- **Logging**: Enable/disable error and usage logging  

---

## User Guide

### For End Users
1. Navigate to the Claude module on your site  
2. Log in (required for chat access)  
3. Type your message in the chat box  
4. Press Enter or click **Send**  
5. Wait for Claude's response  
6. Use **Clear History** to start fresh  

**Chat Features:**  
- Context awareness (remembers conversation)  
- Real-time responses  
- Message history saved  
- Mobile friendly  

### For Administrators

#### Monitoring Usage
- View usage statistics in the admin panel  
- Monitor API costs and user activity  
- Check error logs for issues  

#### Managing Users
- Set rate limits to control usage  
- Monitor active users  
- Clear user chat histories if needed  

---

## Troubleshooting

### Common Issues

**"API Connection Failed"**  
- Verify API key is correct  
- Check internet connectivity  
- Ensure cURL is enabled  

**"Rate Limit Exceeded"**  
- User has sent too many messages  
- Wait for reset or adjust limits  

**"Database Error"**  
- Verify tables exist  
- Check DB permissions  
- Review error logs  

**Empty or Error Responses**  
- Check API key validity  
- Verify API credits  
- Review Anthropic API status  

### Error Logs
- Access logs via admin panel  
- Check for API errors  
- Monitor database issues  

---

## Performance Tips
- Set appropriate rate limits  
- Use Haiku model for faster responses  
- Limit max tokens for cost control  
- Clean logs regularly  

## Security Considerations
- Store API key securely in DB  
- Never expose key in client-side code  
- Rotate API keys periodically  
- Sanitize all input (SQL injection protection)  
- Enable content moderation if needed  

## Cost Management
- Costs vary by model and tokens used  
- Monitor usage in admin panel  
- Adjust rate limits to control costs  

**Approximate Model Costs:**  
- **Haiku**: Lowest cost, fastest  
- **Sonnet**: Medium cost, balanced  
- **Opus**: Highest cost, best quality  

---

## Support and Updates
- Check error logs first for issues  
- Review configuration settings  
- Verify API key and credits  
- Always backup before updates  
- Check module compatibility before upgrading  

---

## Customization

### Theming
- Modify CSS in `custom_files/Claude/css/claude.css`  
- Create custom themes  
- Adjust responsive breakpoints  

### Language Translation
1. Copy lang-english.php  
2. Rename for your language  
3. Translate all `define()` strings  
4. Update language constants

