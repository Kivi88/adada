# Roblox Group Management System

## Overview

This is a web-based Roblox Group Management System built with PHP and vanilla JavaScript. The system appears to be designed for managing Roblox groups, including member lookup, player search functionality, and group administration features. The application uses a traditional client-server architecture with a PHP backend API and a responsive frontend using Bootstrap styling.

## System Architecture

### Frontend Architecture
- **Technology**: Vanilla JavaScript with HTML/CSS
- **Styling**: Bootstrap-based responsive design with custom CSS variables
- **UI Components**: Card-based layout with hover effects and modern button styling
- **JavaScript Pattern**: Event-driven architecture with DOM manipulation
- **Auto-refresh**: Built-in polling mechanism (60-second intervals) for real-time updates

### Backend Architecture
- **Technology**: PHP-based REST API
- **API Structure**: RESTful endpoints for group operations
- **Data Processing**: Server-side group and player data management
- **Response Format**: JSON-based API responses

### Client-Server Communication
- **Method**: Fetch API for asynchronous HTTP requests
- **Data Format**: JSON for request/response payloads
- **Error Handling**: Structured error responses with user-friendly messages

## Key Components

### Frontend Components
1. **Group Lookup System**
   - Form-based group ID input
   - Asynchronous group data retrieval
   - Dynamic results display

2. **Player Search Functionality**
   - Player search form with validation
   - Real-time search results
   - Member management capabilities

3. **UI/UX Features**
   - Responsive card-based layout
   - Hover animations and transitions
   - Loading states and error handling
   - Auto-refresh functionality

### Backend Components
1. **API Endpoints**
   - `api/group_lookup.php` - Group information retrieval
   - Additional endpoints for player and member management

2. **Data Management**
   - Group data processing and validation
   - Member information handling
   - Real-time data synchronization

## Data Flow

1. **User Input**: User enters group ID or player information through web forms
2. **Client Validation**: JavaScript validates input before sending requests
3. **API Request**: Frontend sends HTTP requests to PHP API endpoints
4. **Server Processing**: PHP backend processes requests and interacts with Roblox APIs
5. **Response Handling**: JSON responses are processed and displayed in the UI
6. **Auto-refresh**: System automatically checks for updates every 60 seconds

## External Dependencies

### Frontend Dependencies
- **Bootstrap**: CSS framework for responsive design
- **Custom Fonts**: Segoe UI font stack for consistent typography

### Backend Dependencies
- **PHP**: Server-side scripting language
- **Roblox API**: External API integration for group and player data

### Browser Requirements
- Modern browsers supporting ES6+ features
- Fetch API support for HTTP requests
- CSS Grid and Flexbox support

## Deployment Strategy

### File Structure
```
/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── api/
│   └── group_lookup.php
└── index.html (assumed)
```

### Hosting Requirements
- **Web Server**: Apache/Nginx with PHP support
- **PHP Version**: PHP 7.4+ recommended
- **SSL Certificate**: Required for secure API communication
- **Domain**: Custom domain or subdomain configuration

### Configuration
- CSS variables for easy theme customization
- Configurable refresh intervals
- Modular JavaScript architecture for easy maintenance

## Changelog

- July 06, 2025. Initial setup
- July 06, 2025. API düzeltmeleri tamamlandı - grup/oyuncu arama çalışıyor
- July 06, 2025. Grup sahibi paneli geliştirildi - rütbe yönetimi, istatistikler eklendi
- July 06, 2025. Proje TAR.GZ formatında arşivlendi (21KB) - Plesk'e hazır
- July 06, 2025. Replit ortamına taşındı - güvenli PostgreSQL veritabanı, PHP 8.3
- July 06, 2025. Grup sahibi paneli Roblox benzeri özelliklerle geliştirildi:
  - Grup adı düzenleme sistemi
  - Yardımcı yönetim sistemi (izinlerle)
  - Rütbe yönetimi
  - Helper tablosu veritabanına eklendi

## User Preferences

Preferred communication style: Simple, everyday language.