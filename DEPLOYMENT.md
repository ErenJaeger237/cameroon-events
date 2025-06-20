# 🚀 Quick Railway Deployment Guide

## ⚡ 5-Minute Setup

### 1. GitHub Setup (2 minutes)
```bash
# Create new repository on GitHub
# Upload your OnlineEventBookingSystem_FULL folder
# Make sure these files are included:
✅ railway.json
✅ nixpacks.toml  
✅ init_database.php
✅ README.md
```

### 2. Railway Deployment (2 minutes)
1. Go to [railway.app](https://railway.app)
2. **Sign up** with GitHub
3. Click **"Deploy from GitHub repo"**
4. Select your **CameroonEvents** repository
5. Wait for deployment (auto-detects PHP)

### 3. Add Database (1 minute)
1. In Railway dashboard: **"Add Service"**
2. Select **"Database"** → **"MySQL"**
3. Railway auto-configures environment variables

### 4. Initialize Database (30 seconds)
1. Visit: `https://your-app.railway.app/init_database.php`
2. Wait for "Database Setup Complete!" message
3. Note the admin credentials

## 🎯 Your Live URLs

After deployment, you'll have:
- **Main Site**: `https://your-app.railway.app`
- **Admin Panel**: `https://your-app.railway.app/admin/dashboard.php`
- **Database Init**: `https://your-app.railway.app/init_database.php`

## 🔑 Default Login Credentials

### Admin Access
- **URL**: `/admin/dashboard.php`
- **Email**: `admin@cameroonevents.cm`
- **Password**: `admin123`

### Test User
- **Email**: `john@example.com`
- **Password**: `user123`

## ✅ Post-Deployment Testing

1. **Homepage** - Check African theme loads
2. **User Registration** - Create new account
3. **Event Booking** - Book a sample event
4. **QR Codes** - Test ticket generation
5. **Admin Panel** - Access dashboard
6. **Currency** - Verify FCFA display

## 🛠️ Troubleshooting

### Database Connection Issues
- Check MySQL service is running in Railway
- Verify environment variables are set
- Re-run `init_database.php`

### Build Failures
- Ensure `railway.json` and `nixpacks.toml` are in root
- Check Railway build logs
- Verify PHP syntax in all files

### Missing Features
- Run database initialization script
- Check file permissions
- Verify all assets uploaded

## 📱 Mobile Testing

Test on mobile devices:
- Responsive design
- Touch interactions
- QR code scanning
- PDF downloads

## 🎨 African Theme Verification

Confirm these elements display correctly:
- ✅ Cameroon flag colors (Green, Red, Yellow)
- ✅ African drum icons
- ✅ FCFA currency formatting
- ✅ Warm gradient backgrounds
- ✅ Cultural design elements

## 🔄 Updates & Maintenance

To update your live site:
1. Push changes to GitHub
2. Railway auto-deploys from main branch
3. No manual intervention needed

---

**🎉 Your CameroonEvents site will be live in under 5 minutes!** 🇨🇲
