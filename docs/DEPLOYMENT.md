# 🚀 Deployment Guide

## Frontend Deployment (Vercel)

### **Prerequisites**
- GitHub account
- Vercel account (free tier available)
- Project pushed to GitHub

### **Step 1: Prepare for Deployment**

1. **Build the project locally to test**
   ```bash
   cd frontend
   npm run build
   ```

2. **Verify build output**
   - Check `frontend/public/build/` directory exists
   - Ensure no build errors

### **Step 2: Deploy to Vercel**

#### **Option A: Vercel Dashboard (Recommended)**
1. Go to [vercel.com](https://vercel.com)
2. Sign in with GitHub
3. Click "New Project"
4. Import your GitHub repository
5. Configure project settings:
   - **Framework Preset**: Other
   - **Root Directory**: `frontend`
   - **Build Command**: `npm run build`
   - **Output Directory**: `public`
   - **Install Command**: `npm install`

#### **Option B: Vercel CLI**
```bash
# Install Vercel CLI
npm i -g vercel

# Deploy from project root
cd c:\xampp\htdocs\ecommerceKanha
vercel

# Follow the prompts:
# - Set up and deploy? Yes
# - Which scope? Your account
# - Link to existing project? No
# - Project name: kanha-ecommerce
# - Directory: frontend
```

### **Step 3: Environment Variables**

Add these environment variables in Vercel dashboard:
```
APP_NAME=Kanha Ecommerce
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.vercel.app
```

### **Step 4: Custom Domain (Optional)**
1. Go to Vercel dashboard → Your project → Settings → Domains
2. Add your custom domain
3. Configure DNS records as instructed

## Backend Deployment (Coming Soon)

### **Planned Deployment Options**
- **Railway**: Laravel-friendly with MySQL
- **DigitalOcean**: VPS with full control
- **AWS**: Scalable cloud deployment
- **Heroku**: Simple deployment with add-ons

### **Backend Deployment Checklist**
- [ ] Environment variables configured
- [ ] Database migrations ready
- [ ] File storage configured
- [ ] Email service configured
- [ ] SSL certificate setup
- [ ] Domain configuration

## Database Setup

### **Production Database**
- **Recommended**: PlanetScale, Railway, or AWS RDS
- **Migration**: Use Laravel migrations
- **Seeding**: Run seeders for initial data

### **Environment Variables for Production**
```env
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
```

## Post-Deployment Checklist

### **Frontend**
- [ ] Site loads correctly
- [ ] All routes work
- [ ] Images and assets load
- [ ] API calls work (when backend is deployed)
- [ ] Mobile responsiveness
- [ ] Performance optimization

### **Backend (When Ready)**
- [ ] API endpoints respond
- [ ] Database connections work
- [ ] File uploads work
- [ ] Email notifications work
- [ ] SSL certificate active
- [ ] Error logging configured

## Monitoring & Analytics

### **Recommended Tools**
- **Vercel Analytics**: Built-in performance monitoring
- **Google Analytics**: User behavior tracking
- **Sentry**: Error tracking and monitoring
- **Uptime Robot**: Site availability monitoring

## Troubleshooting

### **Common Issues**

#### **Build Failures**
```bash
# Clear cache and rebuild
npm run clean
npm install
npm run build
```

#### **Environment Variables**
- Ensure all required variables are set in Vercel
- Check variable names match exactly
- Verify values are correct

#### **API Connection Issues**
- Update API base URL for production
- Check CORS settings
- Verify SSL certificates

### **Support**
- Check Vercel documentation
- Review build logs in Vercel dashboard
- Test locally before deploying

---

**Happy Deploying! 🚀**
